<?php
require_once __DIR__ . '/helpers.php';
$csrf = generate_csrf();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>EquiGrade — Login</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <a class="brand" href="index.php" aria-label="EquiGrade home">
        <img src="assets/logo.svg" alt="EquiGrade logo">
        <div>
          <div style="font-weight:700;font-size:1.1rem;">EquiGrade</div>
          <div class="small">FAIR-EVAL AI Grading Assistant</div>
        </div>
      </a>
      <nav>
        <a class="small" href="register.php">Create account</a>
      </nav>
    </header>

    <section class="card" aria-labelledby="login-heading">
      <h1 id="login-heading" style="margin-bottom:8px;">Sign in</h1>

      <form id="loginForm" method="post" action="auth.php" novalidate>
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="action" value="login">

        <div class="form-row column">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required autocomplete="email" placeholder="you@university.edu">
        </div>

        <div class="form-row column">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••">
        </div>

        <div style="display:flex;gap:10px;align-items:center;margin-top:8px;">
          <button class="btn btn-primary" type="submit">Sign in</button>
          <a class="btn btn-ghost" href="register.php">Create account</a>
        </div>
      </form>
    </section>

    <div class="footer">EquiGrade — Fair, transparent, and rubric-driven grading.</div>
  </main>
</body>
</html>
