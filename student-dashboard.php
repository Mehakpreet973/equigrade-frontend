<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
session_start();

// Check if logged in and role = student
if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    flash('error', 'Access denied.');
    header('Location: index.php');
    exit;
}

$pdo = get_db();
$user_id = $_SESSION['user_id'];
$filter_course = $_GET['course'] ?? '';

// Fetch submissions with joined AI grades
if ($filter_course) {
    $stmt = $pdo->prepare("
        SELECT s.*, g.ai_score, g.confidence, g.feedback
        FROM submissions s
        LEFT JOIN grades g ON g.submission_id = s.submission_id
        WHERE s.user_id = ? AND s.course = ?
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$user_id, $filter_course]);
} else {
    $stmt = $pdo->prepare("
        SELECT s.*, g.ai_score, g.confidence, g.feedback
        FROM submissions s
        LEFT JOIN grades g ON g.submission_id = s.submission_id
        WHERE s.user_id = ?
        ORDER BY s.submitted_at DESC
    ");
    $stmt->execute([$user_id]);
}
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard - EquiGrade</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border-bottom: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }
    th {
      background-color: #f8f8f8;
    }
    tr:hover {
      background-color: #f5f5f5;
    }
    .feedback {
      font-size: 0.9em;
      color: #555;
    }
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <a class="brand" href="student-dashboard.php"><strong>EquiGrade</strong></a>
      <nav>
        <a href="submission.php">Submit Assignment</a>
        <a href="view-rubrics.php">View Rubrics</a>
        <a href="logout.php">Sign out</a>
      </nav>
    </header>

    <section class="card">
      <h2>🎓 Student Dashboard</h2>
      <p>Track your submissions, AI-generated grades, and feedback.</p>

      <form method="GET" class="form-row" style="margin-bottom: 15px;">
        <label for="filter_course">Filter by Course:</label>
        <select id="filter_course" name="course" onchange="this.form.submit()">
          <option value="">All Courses</option>
          <option value="BN204" <?= $filter_course==='BN204'?'selected':''; ?>>BN204 Database Technologies</option>
          <option value="BN206" <?= $filter_course==='BN206'?'selected':''; ?>>BN206 System Administration</option>
          <option value="NIT3213" <?= $filter_course==='NIT3213'?'selected':''; ?>>NIT3213 Android Development</option>
          <option value="NIT3222" <?= $filter_course==='NIT3222'?'selected':''; ?>>NIT3222 Virtualisation in Computing</option>
        </select>
      </form>

      <?php if (empty($submissions)): ?>
        <p>No submissions yet for this course.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Assignment</th>
              <th>Course</th>
              <th>File</th>
              <th>Submitted At</th>
              <th>AI Score</th>
              <th>Confidence</th>
              <th>Feedback</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($submissions as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s['assignment_title']); ?></td>
                <td><?= htmlspecialchars($s['course']); ?></td>
                <td><a href="uploads/<?= htmlspecialchars($s['filename']); ?>" target="_blank">View File</a></td>
                <td><?= htmlspecialchars($s['submitted_at']); ?></td>
                <td>
                  <?= $s['ai_score'] !== null 
                        ? '<strong>' . htmlspecialchars($s['ai_score']) . '%</strong>' 
                        : '<em>Pending</em>'; ?>
                </td>
                <td><?= $s['confidence'] !== null ? htmlspecialchars($s['confidence']) . '%' : '-'; ?></td>
                <td class="feedback"><?= htmlspecialchars($s['feedback'] ?? '—'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
