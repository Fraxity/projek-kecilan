<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/game.php';
require_login();
$user = current_user($pdo);
$user = regen_tick($pdo, $user);
$jail_mins = jail_check($user);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="container">
  <div class="card">
    <h1>🏰 The Kingdom</h1>
    <p>Hello, <strong><?= htmlspecialchars($user['username']) ?></strong> — Level <?= $user['level'] ?>, <?= $user['xp'] ?> XP</p>
    <div class="grid grid-2">
      <div>
        <p>HP: <?= $user['hp'] ?>/<?= $user['max_hp'] ?> • Energy: <?= $user['energy'] ?>/<?= $user['max_energy'] ?> • Nerve: <?= $user['nerve'] ?>/<?= $user['max_nerve'] ?></p>
        <p>Gold: 💰 <?= $user['gold'] ?></p>
        <p>STR <?= $user['strength'] ?> | AGI <?= $user['agility'] ?> | END <?= $user['endurance'] ?> | INT <?= $user['intellect'] ?> | CHA <?= $user['charisma'] ?></p>
      </div>
      <div style="text-align:right">
        <a class="btn secondary" href="/index.php">Home</a>
        <a class="btn secondary" href="/logout.php">Logout</a>
      </div>
    </div>
    <?php if ($jail_mins > 0): ?>
      <div class="flash">⛓ You are in the dungeon. Time remaining: about <?= $jail_mins ?> minute(s).</div>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>🏋️ Training Grounds</h2>
    <?php if ($jail_mins > 0): ?>
      <p class="muted">You cannot train while jailed.</p>
    <?php else: ?>
      <form method="post" action="/train.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <select class="input" name="place">
          <option value="barracks">Barracks (+STR)</option>
          <option value="arena">Arena (+AGI)</option>
          <option value="monastery">Monastery (+INT)</option>
          <option value="tavern">Tavern Brawls (+END)</option>
          <option value="market">Market Bargaining (+CHA)</option>
        </select>
        <p><button class="btn">Train (2 Energy)</button></p>
      </form>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>🗡 Crimes</h2>
    <?php if ($jail_mins > 0): ?>
      <p class="muted">You cannot commit crimes while jailed.</p>
    <?php else: ?>
      <form method="post" action="/crime.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <select class="input" name="crime">
          <option value="0">Pickpocket a merchant (2 Nerve)</option>
          <option value="1">Poach deer in royal forest (3 Nerve)</option>
          <option value="2">Smuggle contraband (4 Nerve)</option>
          <option value="3">Assassinate a minor noble (6 Nerve)</option>
        </select>
        <p><button class="btn">Attempt Crime</button></p>
      </form>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>🛏 Rest at the Inn</h2>
    <form method="post" action="/rest.php">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <p><button class="btn">Rest (-5 gold, heal)</button></p>
    </form>
  </div>

  <div class="card">
    <h2>📝 Recent Log</h2>
    <ul>
    <?php
      $stmt = $pdo->prepare('SELECT * FROM logs WHERE user_id = ? ORDER BY id DESC LIMIT 10');
      $stmt->execute([$user['id']]);
      foreach ($stmt as $row) {
          echo '<li class="muted">'.htmlspecialchars($row['created_at']).' — '.htmlspecialchars($row['message']).'</li>';
      }
    ?>
    </ul>
  </div>
</div>
</body>
</html>
