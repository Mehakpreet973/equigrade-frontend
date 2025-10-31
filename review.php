<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

session_start();

// ✅ Only teachers can review
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    flash('error', 'Access denied. Teachers only.');
    header('Location: index.php');
    exit;
}

global $pdo;
$submission_id = $_GET['submission_id'] ?? null;

if (!$submission_id) {
    flash('error', 'Invalid submission ID.');
    header('Location: teacher-dashboard.php');
    exit;
}

// ✅ Fetch submission details (join users + grades)
$stmt = $pdo->prepare("
    SELECT 
        s.submission_id,
        s.assignment_title,
        s.course,
        s.filename,
        s.submitted_at,
        u.full_name AS student_name,
        g.ai_score,
        g.confidence,
        g.feedback
    FROM submissions s
    INNER JOIN users u ON u.user_id = s.user_id
    LEFT JOIN grades g ON g.submission_id = s.submission_id
    WHERE s.submission_id = ?
");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    flash('error', 'Submission not found.');
    header('Location: teacher-dashboard.php');
    exit;
}

// ✅ Handle manual override
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manual_score = trim($_POST['manual_score'] ?? '');
    $manual_feedback = trim($_POST['manual_feedback'] ?? '');

    if ($manual_score === '') {
        flash('error', 'Please enter a manual score.');
    } else {
        // Insert or update grade
        $stmt = $pdo->prepare("
            INSERT INTO grades (submission_id, ai_score, confidence, feedback)
            VALUES (?, ?, 1.00, ?)
            ON DUPLICATE KEY UPDATE ai_score = VALUES(ai_score), feedback = VALUES(feedback)
        ");
        $stmt->execute([$submission_id, $manual_score, $manual_feedback]);

        flash('success', 'Manual override saved successfully.');
        header("Location: review.php?submission_id=" . urlencode($submission_id));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Review Submission - EquiGrade</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <a class="brand" href="teacher-dashboard.php"><strong>EquiGrade</strong></a>
      <nav>
        <a href="teacher-dashboard.php">Dashboard</a>
        <a href="rubric-builder.php">Create Rubric</a>
        <a href="logout.php">Sign out</a>
      </nav>
    </header>

    <section class="card">
      <h2>📝 Review Submission</h2>
      <?php display_flash(); ?>

      <p><strong>Student:</strong> <?= htmlspecialchars($submission['student_name']) ?></p>
      <p><strong>Assignment:</strong> <?= htmlspecialchars($submission['assignment_title']) ?></p>
      <p><strong>Course:</strong> <?= htmlspecialchars($submission['course']) ?></p>
      <p><strong>File:</strong> <a href="uploads/<?= htmlspecialchars($submission['filename']) ?>" target="_blank">View File</a></p>
      <p><strong>Submitted At:</strong> <?= htmlspecialchars($submission['submitted_at']) ?></p>

      <hr>

      <h3>AI Evaluation</h3>
      <p><strong>AI Score:</strong> <?= $submission['ai_score'] ?? 'Not graded yet' ?></p>
      <p><strong>Confidence:</strong> <?= $submission['confidence'] ?? '-' ?></p>
      <p><strong>Feedback:</strong> <?= $submission['feedback'] ?? 'No AI feedback yet.' ?></p>

      <hr>

      <h3>Manual Override</h3>
      <form method="post">
        <div class="form-row column">
          <label for="manual_score">Manual Score</label>
          <input type="number" step="0.01" name="manual_score" id="manual_score" required>
        </div>

        <div class="form-row column">
          <label for="manual_feedback">Manual Feedback</label>
          <textarea name="manual_feedback" id="manual_feedback" rows="4" placeholder="Enter custom feedback..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Override</button>
        <a href="teacher-dashboard.php" class="btn btn-ghost">← Back</a>
      </form>
    </section>
  </main>
</body>
</html>
