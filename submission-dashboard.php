<?php
session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'helpers.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

global $pdo;
$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("
    SELECT s.assignment_title, s.course, s.filename, g.ai_score, g.confidence, 
           g.final_score, g.feedback, s.submitted_at
    FROM submissions s
    LEFT JOIN grades g ON s.submission_id = g.submission_id
    WHERE s.user_id = ?
    ORDER BY s.submitted_at DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard — EquiGrade</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-indigo-700 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">🎓 Student Dashboard — <span class="text-indigo-200">EquiGrade</span></h1>
        <div class="flex items-center gap-3">
            <a href="submission.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                ➕ Submit Assignment
            </a>
           <a href="view-rubrics.php" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg transition">
    📘 View Rubrics
</a>

            </a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded-lg transition">
                🚪 Sign Out
            </a>
        </div>
    </div>
</header>

<!-- Main Section -->
<main class="max-w-7xl mx-auto my-10 px-6">
    <div class="bg-white shadow-md rounded-2xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-2 flex items-center gap-2">
            <span>📂 Your Submitted Assignments</span>
        </h2>
        <p class="text-gray-500 mb-6">All your submitted files, AI evaluation results, and feedback appear here.</p>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border-t border-gray-200">
                <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="py-3 px-4 text-left">Assignment</th>
                        <th class="py-3 px-4 text-left">Course</th>
                        <th class="py-3 px-4 text-left">File</th>
                        <th class="py-3 px-4 text-left">AI Score</th>
                        <th class="py-3 px-4 text-left">Confidence</th>
                        <th class="py-3 px-4 text-left">Feedback</th>
                        <th class="py-3 px-4 text-left">Final Score</th>
                        <th class="py-3 px-4 text-left">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (count($submissions) > 0): ?>
                        <?php foreach ($submissions as $s): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4 font-medium text-gray-800"><?= htmlspecialchars($s['assignment_title']) ?></td>
                                <td class="py-3 px-4 text-gray-700"><?= htmlspecialchars($s['course']) ?></td>
                                <td class="py-3 px-4 text-indigo-600 hover:underline">
                                    <a href="uploads/<?= htmlspecialchars($s['filename']) ?>" target="_blank">View file</a>
                                </td>
                                <td class="py-3 px-4"><?= $s['ai_score'] ? htmlspecialchars(number_format($s['ai_score'], 2)) . '%' : '—' ?></td>
                                <td class="py-3 px-4"><?= $s['confidence'] ? htmlspecialchars(number_format($s['confidence'], 2)) . '%' : '—' ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <?= htmlspecialchars($s['feedback'] ?: 'Pending evaluation') ?>
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-900">
                                    <?= $s['final_score'] ? htmlspecialchars(number_format($s['final_score'], 2)) . '%' : '—' ?>
                                </td>
                                <td class="py-3 px-4 text-gray-500 text-sm">
                                    <?= htmlspecialchars($s['submitted_at']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="py-4 text-center text-gray-500">No submissions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="text-center py-6 text-gray-400 text-sm">
    © 2025 EquiGrade — FAIR-EVAL AI Grading Assistant
</footer>

</body>
</html>
