<?php
session_start();

// Check if logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];

/* ==============================
   FETCH LEARNER DATA
   ============================== */

// Get enrolled classes with teacher info
$classesSQL = "
    SELECT 
        c.class_id,
        c.class_name,
        c.class_code,
        cp.joined_at,
        u.full_name as teacher_name
    FROM class_participants cp
    JOIN classes c ON c.class_id = cp.class_id
    JOIN users u ON u.user_id = c.teacher_id
    WHERE cp.user_id = ?
    ORDER BY cp.joined_at DESC
";
$stmt = $conn->prepare($classesSQL);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$classes = $stmt->get_result();

// Get overall stats
$statsSQL = "
    SELECT 
        COUNT(DISTINCT session_id) as total_sessions,
        COALESCE(AVG(score), 0) as avg_score,
        COALESCE(MAX(score), 0) as best_score,
        COUNT(CASE WHEN level = 'High' THEN 1 END) as high_scores
    FROM attention_scores
    WHERE user_id = ?
";
$stmt = $conn->prepare($statsSQL);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent session history
$historySQL = "
    SELECT 
        ats.score,
        ats.level,
        ats.computed_at,
        c.class_name,
        cs.started_at
    FROM attention_scores ats
    JOIN class_sessions cs ON cs.session_id = ats.session_id
    JOIN classes c ON c.class_id = ats.class_id
    WHERE ats.user_id = ?
    ORDER BY ats.computed_at DESC
    LIMIT 10
";
$stmt = $conn->prepare($historySQL);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Attentivo</title>
    <link rel="stylesheet" href="../assets/css/attentivo.css">
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar">
    <div class="navbar-content">
        <a href="dashboard.php" class="navbar-brand">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <circle cx="16" cy="16" r="14" fill="#8B1538"/>
                <circle cx="16" cy="10" r="3.5" fill="white"/>
                <rect x="13.5" y="14" width="5" height="9" rx="1" fill="white"/>
            </svg>
            Attentivo
        </a>
        <div style="margin-left: auto;">
            <a href="../logout.php" style="color: var(--primary); font-weight: 600;">Logout</a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar">
    <div class="hamburger">☰</div>
    <div class="sidebar-icon" style="background: var(--primary); margin-top: 2rem;">
        🏠
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="dashboard-layout">
    <div class="dashboard-content">

        <!-- Enrolled Classes -->
        <?php if ($classes->num_rows > 0): ?>
            <?php 
            $firstClass = $classes->fetch_assoc();
            $classes->data_seek(0); // Reset pointer
            ?>
            
            <!-- First Class Card (Expanded) -->
            <div class="class-card" style="margin-bottom: 2rem;">
                <div class="class-card-header">
                    <div>
                        <h2 class="class-card-title"><?= htmlspecialchars($firstClass['class_name']) ?></h2>
                        <p class="class-card-meta">
                            Teacher: <?= htmlspecialchars($firstClass['teacher_name']) ?> | 
                            Joined at <?= date('M d, Y', strtotime($firstClass['joined_at'])) ?>
                        </p>
                    </div>
                    <span class="class-card-chevron">▼</span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Join Another Class Button -->
        <div style="text-align: right; margin-bottom: 2rem;">
            <a href="join_class.php" class="btn btn-primary" style="background: linear-gradient(135deg, var(--primary) 0%, #3B0A1A 100%);">
                + Join Another Class
            </a>
        </div>

        <!-- Welcome Message -->
        <div style="text-align: center; margin: 2rem 0; color: var(--primary);">
            <h3>Hi, keep up the great focus! Your attention is being tracked to help you improve</h3>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid" style="margin-bottom: 3rem;">
            
            <!-- Sessions Attended -->
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <h2 class="stat-value"><?= $stats['total_sessions'] ?></h2>
                <p class="stat-label">Sessions Attended</p>
            </div>

            <!-- Average Score -->
            <div class="stat-card">
                <div class="stat-icon">🏅</div>
                <h2 class="stat-value"><?= round($stats['avg_score'], 1) ?>%</h2>
                <p class="stat-label">Average Score</p>
            </div>

            <!-- Best Score -->
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <h2 class="stat-value"><?= round($stats['best_score'], 1) ?>%</h2>
                <p class="stat-label">Best Score</p>
            </div>

            <!-- High Scores -->
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <h2 class="stat-value"><?= $stats['high_scores'] ?></h2>
                <p class="stat-label">High Score</p>
            </div>

        </div>

        <!-- Recent Activity -->
        <div class="card">
            <h3 style="color: var(--primary); margin-bottom: 1.5rem; font-size: 1.5rem;">Recent Activity</h3>
            
            <?php if ($history->num_rows > 0): ?>
                <div style="border: 2px solid var(--primary); border-radius: var(--radius); overflow: hidden;">
                    <?php while ($session = $history->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-info">
                                <h6><?= htmlspecialchars($session['class_name']) ?></h6>
                                <p style="color: #D1A5A5; font-size: 0.875rem;">
                                    <?= date('F d, Y • h:i A', strtotime($session['started_at'])) ?>
                                </p>
                            </div>
                            <div>
                                <?php
                                $badgeClass = $session['level'] === 'High' ? 'success' : ($session['level'] === 'Medium' ? 'warning' : 'danger');
                                ?>
                                <span class="badge badge-<?= $badgeClass ?>">
                                    <?= round($session['score'], 2) ?>% - <?= $session['level'] ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-gray);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                    <h5>No Session History Yet</h5>
                    <p>Your session history will appear here after you attend classes</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
