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

$error = '';
$success = '';

// Get teacher's classes for dropdown
$classesSQL = "SELECT class_id, class_name FROM classes WHERE teacher_id = ? ORDER BY class_name";
$stmt = $conn->prepare($classesSQL);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$classes = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = (int)$_POST['class_id'];
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = strtoupper($_POST['correct_option']);
    
    // Validation
    if (empty($class_id)) {
        $error = 'Please select a class';
    } elseif (empty($question_text) || strlen($question_text) < 10) {
        $error = 'Question text must be at least 10 characters';
    } elseif (strlen($question_text) > 500) {
        $error = 'Question text must not exceed 500 characters';
    } elseif (empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error = 'All four options are required';
    } elseif (!in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        $error = 'Please select the correct answer';
    } else {
        // Check for duplicate options
        $options = [$option_a, $option_b, $option_c, $option_d];
        if (count($options) !== count(array_unique($options))) {
            $error = 'Options must be unique';
        } else {
            // Verify class ownership
            $verify = $conn->prepare("SELECT class_id FROM classes WHERE class_id = ? AND teacher_id = ?");
            $verify->bind_param("ii", $class_id, $teacher_id);
            $verify->execute();
            
            if ($verify->get_result()->num_rows === 0) {
                $error = 'Invalid class selected';
            } else {
                // Insert question
                $insert = $conn->prepare("
                    INSERT INTO mcq_questions 
                    (class_id, question_text, option_a, option_b, option_c, option_d, correct_option, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $insert->bind_param("issssssi", $class_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $teacher_id);
                
                if ($insert->execute()) {
                    header("Location: manage_questions.php?created=1");
                    exit;
                } else {
                    $error = 'Failed to create question. Please try again.';
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
    <title>Create Question - Attentivo</title>
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
    <div class="dashboard-content">

        <div class="card" style="max-width: 900px; margin: 0 auto;">
            <h1 style="color: var(--primary); margin-bottom: 0.5rem;">Create New Question</h1>
            <p style="color: var(--text-gray); margin-bottom: 2rem;">
                Add a multiple-choice question to your question bank
            </p>

            <?php if ($error): ?>
                <div style="background: #FEE2E2; color: #DC2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                
                <!-- Select Class -->
                <div class="form-group">
                    <label class="form-label">Select Class *</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Choose a class --</option>
                        <?php 
                        $classes->data_seek(0);
                        while ($class = $classes->fetch_assoc()): 
                        ?>
                            <option value="<?= $class['class_id'] ?>" <?= (isset($_POST['class_id']) && $_POST['class_id'] == $class['class_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Question Text -->
                <div class="form-group">
                    <label class="form-label">Question Text * (10-500 characters)</label>
                    <textarea 
                        name="question_text" 
                        class="form-input" 
                        placeholder="Enter your question here..."
                        required
                        minlength="10"
                        maxlength="500"
                        rows="3"
                        style="resize: vertical;"
                    ><?= isset($_POST['question_text']) ? htmlspecialchars($_POST['question_text']) : '' ?></textarea>
                    <small style="color: var(--text-gray); display: block; margin-top: 0.5rem;">
                        Character count: <span id="charCount">0</span>/500
                    </small>
                </div>

                <!-- Options -->
                <div style="background: var(--bg-cream); padding: 1.5rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
                    <h4 style="color: var(--primary); margin-bottom: 1rem;">Answer Options</h4>

                    <!-- Option A -->
                    <div class="form-group">
                        <label class="form-label">Option A *</label>
                        <input 
                            type="text" 
                            name="option_a" 
                            class="form-input" 
                            placeholder="Enter option A"
                            required
                            maxlength="255"
                            value="<?= isset($_POST['option_a']) ? htmlspecialchars($_POST['option_a']) : '' ?>"
                        >
                    </div>

                    <!-- Option B -->
                    <div class="form-group">
                        <label class="form-label">Option B *</label>
                        <input 
                            type="text" 
                            name="option_b" 
                            class="form-input" 
                            placeholder="Enter option B"
                            required
                            maxlength="255"
                            value="<?= isset($_POST['option_b']) ? htmlspecialchars($_POST['option_b']) : '' ?>"
                        >
                    </div>

                    <!-- Option C -->
                    <div class="form-group">
                        <label class="form-label">Option C *</label>
                        <input 
                            type="text" 
                            name="option_c" 
                            class="form-input" 
                            placeholder="Enter option C"
                            required
                            maxlength="255"
                            value="<?= isset($_POST['option_c']) ? htmlspecialchars($_POST['option_c']) : '' ?>"
                        >
                    </div>

                    <!-- Option D -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Option D *</label>
                        <input 
                            type="text" 
                            name="option_d" 
                            class="form-input" 
                            placeholder="Enter option D"
                            required
                            maxlength="255"
                            value="<?= isset($_POST['option_d']) ? htmlspecialchars($_POST['option_d']) : '' ?>"
                        >
                    </div>
                </div>

                <!-- Correct Answer -->
                <div class="form-group">
                    <label class="form-label">Correct Answer *</label>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: 0.3s;">
                            <input 
                                type="radio" 
                                name="correct_option" 
                                value="A" 
                                required
                                <?= (isset($_POST['correct_option']) && $_POST['correct_option'] === 'A') ? 'checked' : '' ?>
                            >
                            <span style="font-weight: 600;">Option A</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: 0.3s;">
                            <input 
                                type="radio" 
                                name="correct_option" 
                                value="B" 
                                required
                                <?= (isset($_POST['correct_option']) && $_POST['correct_option'] === 'B') ? 'checked' : '' ?>
                            >
                            <span style="font-weight: 600;">Option B</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: 0.3s;">
                            <input 
                                type="radio" 
                                name="correct_option" 
                                value="C" 
                                required
                                <?= (isset($_POST['correct_option']) && $_POST['correct_option'] === 'C') ? 'checked' : '' ?>
                            >
                            <span style="font-weight: 600;">Option C</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 1rem; border: 2px solid var(--border); border-radius: var(--radius); cursor: pointer; transition: 0.3s;">
                            <input 
                                type="radio" 
                                name="correct_option" 
                                value="D" 
                                required
                                <?= (isset($_POST['correct_option']) && $_POST['correct_option'] === 'D') ? 'checked' : '' ?>
                            >
                            <span style="font-weight: 600;">Option D</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Create Question
                    </button>
                    <a href="manage_questions.php" class="btn btn-secondary" style="flex: 1; text-align: center; padding-top: 0.875rem;">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
// Character counter
const textarea = document.querySelector('textarea[name="question_text"]');
const charCount = document.getElementById('charCount');

function updateCharCount() {
    charCount.textContent = textarea.value.length;
}

textarea.addEventListener('input', updateCharCount);
updateCharCount();

// Highlight selected correct answer
document.querySelectorAll('input[name="correct_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="correct_option"]').forEach(r => {
            r.parentElement.style.borderColor = 'var(--border)';
            r.parentElement.style.background = 'white';
        });
        this.parentElement.style.borderColor = 'var(--success)';
        this.parentElement.style.background = '#D1FAE5';
    });
});
</script>

</body>
</html>
