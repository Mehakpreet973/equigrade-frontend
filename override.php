<?php
// override.php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Ensure teacher is logged in
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    flash('error', 'Access denied. Please log in as a teacher.');
    header('Location: login.php');
    exit;
}

global $pdo;
$user_id = (int) $_SESSION['user_id'];

// Validate submission ID
$submission_id = isset($_GET['submission_id']) ? (int) $_GET['submission_id'] : 0;
if ($submission_id <= 0) {
    flash('error', 'Invalid submission ID.');
    header('Location: teacher-dashboard.php');
    exit;
}

// Fetch submission + student details
$stmt = $pdo->prepare("
    SELECT s.submission_id, s.assignment_title, s.course, s.filename, u.full_name AS student_name
    FROM submissions s
    JOIN users u ON u.user_id = s.user_id
    WHERE s.submission_id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    flash('error', 'Submission not found.');
    header('Location: teacher-dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $final_score = isset($_POST['final_score']) ? (float) $_POST['final_score'] : null;
    $feedback = trim($_POST['feedback'] ?? '');

    if ($final_score === null || $final_score < 0 || $final_score > 100) {
        flash('error', 'Please enter a valid score between 0 and 100.');
    } else {
        // Check if grade record exists
        $stmt = $pdo->prepare("SELECT grade_id FROM grades WHERE submission_id = ?");
        $stmt->execute([$submission_id]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            // Update existing grade
            $stmt = $pdo->prepare("
                UPDATE grades
                SET final_score = :score,
                    feedback = :feedback,
                    graded_by = :grader,
                    graded_at = NOW()
                WHERE submission_id = :sid
            ");
        } else {
            // Insert new grade record
            $stmt = $pdo->prepare("
                INSERT INTO grades (submission_id, final_score, feedback, graded_by, graded_at)
                VALUES (:sid, :score, :feedback, :grader, NOW())
            ");
        }

        $stmt->execute([
            ':sid' => $submission_id,
            ':score' => $final_score,
            ':feedback' => $feedback,
            ':grader' => $user_id
        ]);

        flash('success', 'Score successfully overridden for this submission.');
        header("Location: review.php?submission_id={$submission_id}");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Override Score — EquiGrade</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

    <div class="max-w-3xl mx-auto my-10 bg-white rounded-xl shadow-lg border border-slate-200 p-6">
        <h1 class="text-2xl font-bold text-indigo-700 mb-6">Override Score</h1>

        <?php display_flash(); ?>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-1"><?= htmlspecialchars($submission['assignment_title']) ?></h2>
            <p class="text-slate-600">
                <strong>Student:</strong> <?= htmlspecialchars($submission['student_name']) ?><br>
                <strong>Course:</strong> <?= htmlspecialchars($submission['course']) ?><br>
                <?php if (!empty($submission['filename'])): ?>
                    <strong>File:</strong>
                    <a href="<?= htmlspecialchars('uploads/' . $submission['filename']) ?>"
                       target="_blank"
                       class="text-indigo-600 hover:underline">
                       View submission
                    </a>
                <?php endif; ?>
            </p>
        </div>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Final Score (0–100)</label>
                <input type="number" name="final_score" step="0.1" min="0" max="100"
                       required
                       class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Feedback</label>
                <textarea name="feedback" rows="4"
                          class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2"
                          placeholder="Enter any feedback for this override (optional)"></textarea>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="review.php?submission_id=<?= (int)$submission_id ?>"
                   class="text-slate-600 hover:text-slate-900 underline underline-offset-2">⬅ Back to Review</a>
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg">
                    Save Override
                </button>
            </div>
        </form>
    </div>

</body>
</html>
