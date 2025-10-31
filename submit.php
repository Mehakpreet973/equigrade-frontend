<?php
// submit.php
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/config.php';
require_once __DIR__.'/grade_simulator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: submission.php'); 
    exit;
}

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php'); 
    exit;
}

if (!check_csrf($_POST['csrf'] ?? '')) { 
    http_response_code(400); 
    echo "Invalid CSRF"; 
    exit; 
}

$pdo = get_db();

// Get form data
$title = trim($_POST['title'] ?? '');
$course = trim($_POST['course'] ?? '');
$assignment_id = intval($_POST['assignment_id'] ?? 0);

// Validate file upload
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'File upload failed'); 
    header('Location: submission.php'); 
    exit;
}

$file = $_FILES['file'];
if ($file['size'] > MAX_UPLOAD_BYTES) { 
    flash('error', 'File too large'); 
    header('Location: submission.php'); 
    exit; 
}

global $ALLOWED_MIME;
if (!is_allowed_file($file['tmp_name'], $ALLOWED_MIME)) { 
    flash('error', 'Invalid file type'); 
    header('Location: submission.php'); 
    exit; 
}

// Save file with unique name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$basename = bin2hex(random_bytes(8));
$target = UPLOAD_DIR . "/{$basename}." . $ext;
if (!move_uploaded_file($file['tmp_name'], $target)) {
    flash('error', 'Unable to save file'); 
    header('Location: submission.php'); 
    exit;
}

// Insert submission record
$stmt = $pdo->prepare("
    INSERT INTO submissions (assignment_id, student_id, filename, file_path, submitted_at, status)
    VALUES (?, ?, ?, ?, NOW(), 'graded')
");
$stmt->execute([$assignment_id ?: null, $_SESSION['user_id'], $file['name'], $target]);
$submission_id = $pdo->lastInsertId();

// 🔹 Fetch the latest rubric automatically
$rubric_id = $pdo->query("SELECT rubric_id FROM rubrics ORDER BY created_at DESC LIMIT 1")->fetchColumn();

// 🔹 Simulate rubric-based grading
list($ai_score, $confidence, $feedback) = simulate_grade($target, $rubric_id);

// Insert grade record
$g = $pdo->prepare("
    INSERT INTO grades (submission_id, rubric_id, ai_score, final_score, confidence, feedback, graded_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$g->execute([$submission_id, $rubric_id, $ai_score, $ai_score, $confidence, $feedback]);

flash('success', 'Submission uploaded and graded using rubric.');
header('Location: submission-dashboard.php');
exit;
?>
