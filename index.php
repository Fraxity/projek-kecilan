<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
$user = current_user($pdo);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Kingdoms of Steel & Shadows</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
  <div class="card">
    <h1>🏰 Kingdoms of Steel & Shadows</h1>
    <p class="muted">A minimal medieval text RPG starter.</p>
    <?php if ($user): ?>
      <p>Welcome back, <strong><?= htmlspecialchars($user['username']) ?></strong>!</p>
      <a class="btn" href="/dashboard.php">Enter the Kingdom</a>
      <a class="btn secondary" href="/logout.php">Logout</a>
    <?php else: ?>
      <a class="btn" href="/register.php">Create Character</a>
      <a class="btn secondary" href="/login.php">Login</a>
    <?php endif; ?>
  </div>
  <div class="card">
    <h2>About</h2>
    <p>Train at the barracks, attempt daring crimes like poaching in the royal forest, rest at the inn, or end up in the dungeon if the guards catch you.</p>
  </div>
</div>
</body>
</html>
