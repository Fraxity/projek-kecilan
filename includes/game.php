<?php
function regen_tick(PDO $pdo, array $user) {
    // Basic regen on each page load (very simplified)
    $energy = min($user['max_energy'], $user['energy'] + 1);
    $nerve = min($user['max_nerve'], $user['nerve'] + 1);
    $hp = min($user['max_hp'], $user['hp'] + 1);
    if ($energy != $user['energy'] || $nerve != $user['nerve'] || $hp != $user['hp']) {
        $stmt = $pdo->prepare('UPDATE users SET energy=?, nerve=?, hp=? WHERE id=?');
        $stmt->execute([$energy, $nerve, $hp, $user['id']]);
        $user['energy'] = $energy;
        $user['nerve'] = $nerve;
        $user['hp'] = $hp;
    }
    return $user;
}

function jail_check(array $user) {
    if (!empty($user['in_jail_until'])) {
        $until = strtotime($user['in_jail_until']);
        if (time() < $until) {
            $remaining = $until - time();
            $mins = ceil($remaining / 60);
            return $mins;
        }
    }
    return 0;
}

function log_event(PDO $pdo, int $user_id, string $type, string $message) {
    $stmt = $pdo->prepare('INSERT INTO logs (user_id, type, message) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $type, $message]);
}

function attempt_crime(PDO $pdo, array $user): array {
    // Simple crime table (could be DB-driven)
    $crimes = [
        ['name' => 'Pickpocket a merchant', 'nerve' => 2, 'success' => 0.65, 'gold_min' => 10, 'gold_max' => 40, 'jail_min' => 1, 'jail_max' => 5],
        ['name' => 'Poach deer in royal forest', 'nerve' => 3, 'success' => 0.5, 'gold_min' => 20, 'gold_max' => 70, 'jail_min' => 2, 'jail_max' => 10],
        ['name' => 'Smuggle contraband', 'nerve' => 4, 'success' => 0.4, 'gold_min' => 40, 'gold_max' => 120, 'jail_min' => 5, 'jail_max' => 20],
        ['name' => 'Assassinate a minor noble', 'nerve' => 6, 'success' => 0.25, 'gold_min' => 120, 'gold_max' => 300, 'jail_min' => 10, 'jail_max' => 45],
    ];

    $choice = $_POST['crime'] ?? '0';
    $idx = intval($choice);
    if (!isset($crimes[$idx])) return ['error' => 'Invalid crime selected.'];

    $crime = $crimes[$idx];
    if ($user['nerve'] < $crime['nerve']) return ['error' => 'Not enough nerve.'];

    // Success probability influenced slightly by stats
    $bonus = min(0.2, ($user['agility'] + $user['charisma'] + $user['intellect']) * 0.002);
    $successRate = $crime['success'] + $bonus;

    $rand = mt_rand() / mt_getrandmax();
    $stmt = $pdo->prepare('UPDATE users SET nerve = nerve - ? WHERE id = ?');
    $stmt->execute([$crime['nerve'], $user['id']]);

    if ($rand <= $successRate) {
        $gold = mt_rand($crime['gold_min'], $crime['gold_max']);
        $xp = mt_rand(1, 4);
        $stmt = $pdo->prepare('UPDATE users SET gold = gold + ?, xp = xp + ? WHERE id = ?');
        $stmt->execute([$gold, $xp, $user['id']]);
        log_event($pdo, $user['id'], 'crime', $crime['name'] . ' succeeded. +' . $gold . ' gold, +' . $xp . ' xp.');
        return ['ok' => $crime['name'] . " succeeded! You gain {$gold} gold and {$xp} xp."];
    } else {
        $mins = mt_rand($crime['jail_min'], $crime['jail_max']);
        $until = date('Y-m-d H:i:s', time() + ($mins * 60));
        $stmt = $pdo->prepare('UPDATE users SET in_jail_until = ? WHERE id = ?');
        $stmt->execute([$until, $user['id']]);
        log_event($pdo, $user['id'], 'crime', $crime['name'] . " failed. You were jailed for {$mins} minutes.");
        return ['ok' => $crime['name'] . " failed. You were jailed for {$mins} minutes."];
    }
}

function train(PDO $pdo, array $user): array {
    if ($user['energy'] < 2) return ['error' => 'Not enough energy to train.'];
    $places = [
        'barracks' => ['stat' => 'strength'],
        'arena' => ['stat' => 'agility'],
        'monastery' => ['stat' => 'intellect'],
        'tavern' => ['stat' => 'endurance'],
        'market' => ['stat' => 'charisma'],
    ];
    $place = $_POST['place'] ?? 'barracks';
    if (!isset($places[$place])) return ['error' => 'Invalid training place.'];
    $stat = $places[$place]['stat'];

    $gain = mt_rand(1, 3);
    $stmt = $pdo->prepare("UPDATE users SET {$stat} = {$stat} + ?, energy = energy - 2, xp = xp + 1 WHERE id = ?");
    $stmt->execute([$gain, $user['id']]);
    log_event($pdo, $user['id'], 'train', "Trained at {$place}. +{$gain} {$stat}.");
    return ['ok' => "You trained at the {$place}. +{$gain} {$stat}."];
}

function rest(PDO $pdo, array $user): array {
    $cost = 5;
    if ($user['gold'] < $cost) return ['error' => 'Not enough gold to rest at the inn.'];
    $heal = mt_rand(5, 15);
    $stmt = $pdo->prepare('UPDATE users SET gold = gold - ?, hp = LEAST(max_hp, hp + ?) WHERE id = ?');
    $stmt->execute([$cost, $heal, $user['id']]);
    log_event($pdo, $user['id'], 'rest', "Rested at the inn. -{$cost} gold, +{$heal} hp.");
    return ['ok' => "You rested at the inn. -{$cost} gold, +{$heal} hp."];
}
