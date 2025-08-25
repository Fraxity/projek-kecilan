<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/game.php';
verify_csrf();
require_login();
$user = current_user($pdo);
if ($user) {
    $result = rest($pdo, $user);
    $_SESSION['flash'] = $result;
}
header('Location: /dashboard.php');
exit;
