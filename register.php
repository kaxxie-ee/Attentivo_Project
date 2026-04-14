<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'teacher' ? 'teacher/dashboard.php' : 'learner/dashboard.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db_connect.php';
    
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || empty($email) || empty($role) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!in_array($role, ['teacher', 'learner'])) {
        $error = 'Invalid role selected';
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Create account
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (full_name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insert->bind_param("ssss", $full_name, $email, $hashed_password, $role);
            
            if ($insert->execute()) {
                $success = 'Account created successfully! Redirecting to login...';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
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
    <title>Create Account - Attentivo</title>
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
        <p class="auth-subtitle">Create your account via email</p>

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
            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input 
                    type="text" 
                    name="full_name" 
                    class="form-input" 
                    placeholder="Enter your full name"
                    required
                    value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>"
                >
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="Enter your email"
                    required
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                >
            </div>

            <!-- Select Role -->
            <div class="form-group">
                <label class="form-label">Select Role</label>
                <select name="role" class="form-select" required>
                    <option value="">Choose your role...</option>
                    <option value="teacher" <?= (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : '' ?>>Teacher</option>
                    <option value="learner" <?= (isset($_POST['role']) && $_POST['role'] === 'learner') ? 'selected' : '' ?>>Student/Learner</option>
                </select>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password</label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        class="form-input" 
                        placeholder="Enter password (min 6 characters)"
                        required
                        style="padding-right: 3rem;"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword('password')"
                        style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.25rem;"
                    >
                        👁️
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <div style="position: relative;">
                    <input 
                        type="password" 
                        name="confirm_password" 
                        id="confirm_password"
                        class="form-input" 
                        placeholder="Re-enter password"
                        required
                        style="padding-right: 3rem;"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword('confirm_password')"
                        style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.25rem;"
                    >
                        👁️
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 2rem;">
                Create Account
            </button>
        </form>

        <!-- Footer Link -->
        <div class="auth-footer">
            Already have an account? <a href="login.php" class="auth-link">Sign in here</a>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
