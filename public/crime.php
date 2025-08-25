<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/game.php';
verify_csrf();
require_login();
$user = current_user($pdo);
if ($user) {
    $jail_mins = jail_check($user);
    if ($jail_mins > 0) {
        $_SESSION['flash'] = ['error' => 'You are jailed.'];
    } else {
        $result = attempt_crime($pdo, $user);
        $_SESSION['flash'] = $result;
    }
}
header('Location: /dashboard.php');
exit;
