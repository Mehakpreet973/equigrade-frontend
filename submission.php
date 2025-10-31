<?php
session_start();
require_once 'config.php';
require_once 'db.php';
require_once 'helpers.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Assignment — EquiGrade</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header -->
<header class="bg-indigo-700 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">📤 Submit Assignment — <span class="text-indigo-200">EquiGrade</span></h1>
        <div class="flex items-center gap-3">
            <a href="submission-dashboard.php" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-lg text-white font-medium transition">🏠 Dashboard</a>
            <a href="view_rubrics.php" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded-lg text-white font-medium transition">📘 View Rubrics</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-white font-medium transition">🚪 Sign Out</a>
        </div>
    </div>
</header>

<!-- Main Section -->
<main class="max-w-3xl mx-auto mt-12 bg-white rounded-2xl shadow-lg p-10">
    <h2 class="text-2xl font-semibold text-gray-800 mb-2 flex items-center gap-2">📚 Submit Assignment</h2>
    <p class="text-gray-500 mb-6">Upload your assignment file — EquiGrade AI will grade it instantly using the rubric.</p>

    <form action="submit_assignment_handler.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label for="assignment_title" class="block text-sm font-medium text-gray-700 mb-2">Assignment Title</label>
            <input type="text" id="assignment_title" name="assignment_title"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="e.g., Essay 1: Emerging Technologies" required>
        </div>

        <div>
            <label for="course" class="block text-sm font-medium text-gray-700 mb-2">Course</label>
            <select id="course" name="course" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                <option value="">Select Course</option>
                <option value="BN204">BN204</option>
                <option value="BN206">BN206</option>
                <option value="NIT3213">NIT3213</option>
                <option value="NIT3222">NIT3222</option>
            </select>
        </div>

        <div>
            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">File (PDF / DOCX / TXT)</label>
            <input type="file" id="file" name="file" accept=".pdf,.docx,.txt"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50 p-2" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition">
            🚀 Upload & Auto-Grade
        </button>
    </form>
</main>

<!-- Footer -->
<footer class="text-center py-6 text-gray-400 text-sm mt-8">
    © 2025 EquiGrade — FAIR-EVAL AI Grading Assistant
</footer>

</body>
</html>
