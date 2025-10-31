<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        flash('error', 'Please enter both email and password.');
        header('Location: index.php');
        exit;
    }

    require_once __DIR__ . '/db.php';
global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        if ($user['role'] === 'teacher') {
            header('Location: teacher-dashboard.php');
        } else {
            header('Location: submission-dashboard.php');
        }
        exit;
    } else {
        flash('error', 'Invalid email or password.');
        header('Location: index.php');
        exit;
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>EquiGrade — Sign In</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <a class="brand" href="index.php"><img src="assets/logo.svg" alt=""><strong>EquiGrade</strong></a>
    </header>

    <section class="card">
      <h2>Sign in</h2>
      <p>Enter your credentials to access your dashboard.</p>

      <?php display_flash(); ?>

      <form method="post" action="">
        <div class="form-row column">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required placeholder="you@university.edu">
        </div>

        <div class="form-row column">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required placeholder="••••••••">
        </div>

        <div style="margin-top:12px;">
          <button class="btn btn-primary" type="submit">Sign In</button>
        </div>
      </form>

      <p class="small" style="margin-top:10px;">Don't have an account? <a href="register.php">Create one</a></p>
    </section>
  </main>
</body>
</html>
