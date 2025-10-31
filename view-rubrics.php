<?php
require_once 'db.php';

// Fetch all unique courses for the filter dropdown
$courses = $pdo->query("SELECT DISTINCT course FROM rubrics WHERE course IS NOT NULL AND course != ''")->fetchAll(PDO::FETCH_COLUMN);

// Handle filtering by course
$selectedCourse = $_GET['course'] ?? 'All Courses';

if ($selectedCourse === 'All Courses' || $selectedCourse === '') {
    $stmt = $pdo->query("SELECT * FROM rubrics ORDER BY created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM rubrics WHERE course = ? ORDER BY created_at DESC");
    $stmt->execute([$selectedCourse]);
}

$rubrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Rubrics — EquiGrade</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 font-sans">
  <div class="max-w-6xl mx-auto py-10">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-bold flex items-center gap-2 text-gray-900">
        📘 View Rubrics
      </h1>
      <div class="space-x-4">
        <a href="submission-dashboard.php" class="text-indigo-600 hover:underline font-medium">Dashboard</a>
        <a href="signout.php" class="text-red-500 hover:underline font-medium">Sign Out</a>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
      <form method="GET" class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full md:w-1/2">
          <label for="course" class="font-medium text-gray-700">Filter by Course:</label>
          <select name="course" id="course" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring focus:ring-indigo-200">
            <option <?= ($selectedCourse === 'All Courses') ? 'selected' : '' ?>>All Courses</option>
            <?php foreach ($courses as $course): ?>
              <option value="<?= htmlspecialchars($course) ?>" <?= ($selectedCourse === $course) ? 'selected' : '' ?>>
                <?= htmlspecialchars($course) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="flex items-center gap-3 w-full md:w-1/2">
          <input type="text" name="search" placeholder="Search rubric title..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                 class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring focus:ring-indigo-200">
          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
            🔍 Search
          </button>
        </div>
      </form>
    </div>

    <!-- Rubric List -->
    <div class="bg-white rounded-xl shadow p-6">
      <p class="text-gray-600 mb-6">
        Below are all rubrics used for AI grading. Click a rubric name to view its details.
      </p>

      <?php if (count($rubrics) === 0): ?>
        <div class="text-center py-10 text-gray-500">
          No rubrics found for this course or search.
        </div>
      <?php endif; ?>

      <div class="space-y-4">
        <?php foreach ($rubrics as $rubric): ?>
          <div class="p-6 bg-gray-50 border border-gray-200 rounded-lg hover:bg-indigo-50 transition shadow-sm">
            <a href="rubric-details.php?id=<?= htmlspecialchars($rubric['rubric_id']) ?>"
               class="text-xl font-semibold text-indigo-700 hover:underline">
              <?= htmlspecialchars($rubric['title'] ?: 'Untitled Rubric') ?>
              (<?= htmlspecialchars($rubric['course'] ?: 'No Course') ?>)
            </a>

            <ul class="mt-3 text-gray-800">
              <?php
              $criteria = json_decode($rubric['criteria_json'], true);
              if ($criteria && is_array($criteria)) {
                foreach ($criteria as $criterion) {
                  echo "<li><strong>" . htmlspecialchars($criterion['criterion']) . "</strong> — " . htmlspecialchars($criterion['weight']) . "%</li>";
                }
              } else {
                echo "<li><em>No criteria defined for this rubric.</em></li>";
              }
              ?>
            </ul>

            <p class="text-sm text-gray-500 mt-3">
              Created on <?= htmlspecialchars($rubric['created_at']) ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</body>
</html>
