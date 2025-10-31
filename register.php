<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'student';

    if (empty($full_name) || empty($email) || empty($password)) {
        flash('error', 'All fields are required.');
        header('Location: register.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Invalid email format.');
        header('Location: register.php');
        exit;
    }

    $pdo = get_db();

    // Check for duplicate email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash('error', 'Email already exists. Please log in instead.');
        header('Location: register.php');
        exit;
    }

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$full_name, $email, $password_hash, $role]);

    flash('success', 'Account created successfully! You can now log in.');
    header('Location: index.php');
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create Account - EquiGrade</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    body {
      font-family: Inter, Arial, sans-serif;
      background: #f9fafc;
      margin: 0;
    }
    .container {
      max-width: 450px;
      margin: 80px auto;
      background: #fff;
      padding: 40px;
      border-radius: 14px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    h1 {
      font-size: 24px;
      margin-bottom: 10px;
      text-align: center;
    }
    p {
      color: #555;
      font-size: 14px;
      text-align: center;
      margin-bottom: 20px;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    input, select, button {
      padding: 10px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background: #2563eb;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: 600;
      border-radius: 8px;
      padding: 12px;
    }
    button:hover {
      background: #1e4ed8;
    }
    .flash-success {
      background: #dcfce7;
      color: #166534;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 10px;
      text-align: center;
    }
    .flash-error {
      background: #fee2e2;
      color: #991b1b;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 10px;
      text-align: center;
    }
    .footer-text {
      margin-top: 15px;
      text-align: center;
      font-size: 13px;
    }
    a {
      color: #2563eb;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <main class="container">
    <h1>Create account</h1>
    <p>Join EquiGrade and start fair, transparent grading.</p>

    <?php display_flash(); ?>

    <form method="POST" action="register.php">
      <input type="text" name="full_name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <select name="role" required>
        <option value="student">Student</option>
        <option value="teacher">Teacher</option>
      </select>
      <button type="submit">Create Account</button>
    </form>

    <div class="footer-text">
      Already have an account? <a href="index.php">Sign in</a>
    </div>
  </main>
</body>
</html>
