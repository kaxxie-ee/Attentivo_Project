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

$error = '';
$success = '';

// Handle class join
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_code = strtoupper(trim($_POST['class_code']));
    
    if (empty($class_code)) {
        $error = 'Please enter a class code';
    } else {
        // Check if class exists
        $stmt = $conn->prepare("SELECT class_id, class_name FROM classes WHERE class_code = ?");
        $stmt->bind_param("s", $class_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Invalid class code';
        } else {
            $class = $result->fetch_assoc();
            $class_id = $class['class_id'];
            
            // Check if already enrolled
            $check = $conn->prepare("SELECT participant_id FROM class_participants WHERE user_id = ? AND class_id = ?");
            $check->bind_param("ii", $user_id, $class_id);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $error = 'You are already enrolled in this class';
            } else {
                // Enroll student
                $insert = $conn->prepare("INSERT INTO class_participants (class_id, user_id, joined_at) VALUES (?, ?, NOW())");
                $insert->bind_param("ii", $class_id, $user_id);
                
                if ($insert->execute()) {
                    $success = 'Successfully joined ' . htmlspecialchars($class['class_name']) . '!';
                    header("refresh:2;url=dashboard.php");
                } else {
                    $error = 'Failed to join class. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Class - Attentivo</title>
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

<!-- Main Content -->
<div class="dashboard-layout">
    <div class="dashboard-content" style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 80px);">
        
        <div class="card" style="max-width: 600px; width: 100%; padding: 3rem;">
            <h1 style="color: var(--primary); text-align: center; margin-bottom: 1.5rem;">
                Hello! Enter your code to join a class
            </h1>

            <?php if ($error): ?>
                <div style="background: #FEE2E2; color: #DC2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div style="background: #D1FAE5; color: #059669; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group" style="margin-bottom: 2rem;">
                    <input 
                        type="text" 
                        name="class_code" 
                        class="form-input" 
                        placeholder="ex. 2CC2FF"
                        required
                        maxlength="6"
                        style="text-align: center; font-size: 1.5rem; padding: 1.5rem; text-transform: uppercase; letter-spacing: 0.2em;"
                        value="<?= isset($_POST['class_code']) ? htmlspecialchars($_POST['class_code']) : '' ?>"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    Join Class
                </button>
            </form>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="dashboard.php" style="color: var(--text-gray);">← Back to Dashboard</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>
