<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
verify_csrf();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (strlen($username) < 3 || strlen($password) < 6) {
        $error = 'Username must be 3+ chars and password 6+ chars.';
    } else {
        try {
            $user_id = register($pdo, $username, $password);
            $_SESSION['user_id'] = $user_id;
            header('Location: /dashboard.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Registration failed. Username may be taken.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create Character</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
  <div class="card">
    <h1>Create Character</h1>
    <?php if ($error): ?><div class="flash"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div class="grid">
        <label>Username <input class="input" name="username" required></label>
        <label>Password <input class="input" name="password" type="password" required></label>
      </div>
      <p><button class="btn">Create</button> <a class="btn secondary" href="/">Back</a></p>
    </form>
  </div>
</div>
</body>
</html>
