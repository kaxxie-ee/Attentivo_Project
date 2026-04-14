<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'teacher' ? 'teacher/dashboard.php' : 'learner/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db_connect.php';
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: " . ($user['role'] === 'teacher' ? 'teacher/dashboard.php' : 'learner/dashboard.php'));
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Attentivo</title>
    <link rel="stylesheet" href="assets/css/attentivo.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-content">
        <a href="index.php" class="navbar-brand">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                <circle cx="16" cy="16" r="14" fill="#8B1538"/>
                <circle cx="16" cy="10" r="3.5" fill="white"/>
                <rect x="13.5" y="14" width="5" height="9" rx="1" fill="white"/>
            </svg>
            Attentivo
        </a>
        <ul class="navbar-menu">
            <li><a href="#about">About Us</a></li>
            <li><a href="#service">Service</a></li>
            <li><a href="#portfolio">Portfolio</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon">!</div>
        </div>
        
        <h1 class="auth-title">ATTENTIVO</h1>
        <p class="auth-subtitle">Sign in to your account via email</p>

        <?php if ($error): ?>
            <div style="background: #FEE2E2; color: #DC2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Enter your email</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.25rem; color: #9CA3AF;">📧</span>
                    <input type="email" name="email" class="form-input" placeholder="Enter your email" required style="padding-left: 3rem;" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Enter your password</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.25rem; color: #9CA3AF;">🔒</span>
                    <input type="password" name="password" class="form-input" placeholder="Enter your password" required style="padding-left: 3rem;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 2rem;">Sign in</button>
        </form>

        <div class="auth-footer">
            Not a member, <a href="register.php" class="auth-link">create your new account</a>
        </div>
    </div>
</div>

</body>
</html>
