<?php
session_start();

// Check if logged in as teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

require_once '../config/db_connect.php';

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['full_name'];

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $question_id = (int)$_GET['delete'];
    
    // Verify ownership before deleting
    $verify = $conn->prepare("SELECT question_id FROM mcq_questions WHERE question_id = ? AND created_by = ?");
    $verify->bind_param("ii", $question_id, $teacher_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $delete = $conn->prepare("DELETE FROM mcq_questions WHERE question_id = ?");
        $delete->bind_param("i", $question_id);
        $delete->execute();
        header("Location: manage_questions.php?deleted=1");
        exit;
    }
}

// Fetch questions
$questionsSQL = "
    SELECT 
        q.question_id,
        q.question_text,
        q.correct_option,
        q.class_id,
        c.class_name,
        COUNT(DISTINCT r.response_id) as times_asked,
        COUNT(CASE WHEN r.is_correct = 1 THEN 1 END) as correct_answers,
        CASE 
            WHEN COUNT(DISTINCT r.response_id) > 0 
            THEN ROUND((COUNT(CASE WHEN r.is_correct = 1 THEN 1 END) * 100.0 / COUNT(DISTINCT r.response_id)), 0)
            ELSE 0 
        END as percent_correct
    FROM mcq_questions q
    JOIN classes c ON c.class_id = q.class_id
    LEFT JOIN mcq_responses r ON r.question_id = q.question_id
    WHERE q.created_by = ?
";

if (!empty($search)) {
    $questionsSQL .= " AND q.question_text LIKE ?";
}

$questionsSQL .= " GROUP BY q.question_id ORDER BY q.created_at DESC";

$stmt = $conn->prepare($questionsSQL);

if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("is", $teacher_id, $searchParam);
} else {
    $stmt->bind_param("i", $teacher_id);
}

$stmt->execute();
$questions = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Bank - Attentivo</title>
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
    <div class="sidebar-icon" style="background: white; border: 2px solid var(--primary); color: var(--primary); margin-top: 1rem; position: relative;">
        💬
        <span style="position: absolute; top: -5px; right: -5px; background: var(--danger); color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;">2</span>
    </div>
</div>

<!-- Main Content -->
<div class="dashboard-layout">
    <div class="dashboard-content">

        <!-- Header Card -->
        <div class="class-card" style="margin-bottom: 2rem;">
            <div class="class-card-header">
                <div>
                    <h2 class="class-card-title">Question Bank</h2>
                    <p class="class-card-meta">Manage your MCQ Questions</p>
                </div>
                <span class="class-card-chevron">▼</span>
            </div>
        </div>

        <!-- Create New Question Button -->
        <div style="text-align: right; margin-bottom: 2rem;">
            <a href="create_question.php" class="btn btn-primary" style="background: linear-gradient(135deg, var(--primary) 0%, #3B0A1A 100%);">
                + Create New Question
            </a>
        </div>

        <!-- Success Message -->
        <?php if (isset($_GET['deleted'])): ?>
            <div style="background: #D1FAE5; color: #059669; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                Question deleted successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['created'])): ?>
            <div style="background: #D1FAE5; color: #059669; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                Question created successfully!
            </div>
        <?php endif; ?>

        <!-- Search Section -->
        <div class="card" style="margin-bottom: 2rem;">
            <h4 style="color: var(--primary); margin-bottom: 1rem;">Search Questions</h4>
            <form method="GET" action="">
                <div style="display: flex; gap: 1rem;">
                    <div style="flex: 1; position: relative;">
                        <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.25rem; color: var(--text-gray);">🔍</span>
                        <input 
                            type="text" 
                            name="search" 
                            class="form-input" 
                            placeholder="Search by question text..."
                            value="<?= htmlspecialchars($search) ?>"
                            style="padding-left: 3rem;"
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="manage_questions.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Questions Table -->
        <div class="table-container">
            <?php if ($questions->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Questions</th>
                            <th>Correct</th>
                            <th>Asked</th>
                            <th>% Correct</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($q = $questions->fetch_assoc()): ?>
                            <tr>
                                <td><?= $q['question_id'] ?></td>
                                <td>
                                    <strong style="color: var(--primary);">
                                        <?= htmlspecialchars($q['question_text']) ?>
                                    </strong>
                                    <br>
                                    <small style="color: var(--text-gray);">
                                        Class: <?= htmlspecialchars($q['class_name']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= strtoupper($q['correct_option']) ?>
                                    </span>
                                </td>
                                <td><?= $q['times_asked'] ?></td>
                                <td><?= $q['percent_correct'] ?>%</td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button 
                                            class="btn btn-secondary btn-sm"
                                            onclick="viewQuestion(<?= $q['question_id'] ?>)"
                                        >
                                            View
                                        </button>
                                        <button 
                                            class="btn btn-sm"
                                            style="background: var(--danger); color: white;"
                                            onclick="if(confirm('Delete this question?')) window.location.href='manage_questions.php?delete=<?= $q['question_id'] ?>'"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-gray);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">❓</div>
                    <h5>No Questions Yet</h5>
                    <p>Create your first question to get started</p>
                    <a href="create_question.php" class="btn btn-primary" style="margin-top: 1rem;">
                        + Create Question
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
function viewQuestion(id) {
    alert('View question functionality - Question ID: ' + id);
    // This would open a modal or navigate to view page
}
</script>

</body>
</html>
