<?php
require_once __DIR__ . '/db.php';

echo "=== All Participants ===\n\n";

$participants = $pdo->query("SELECT id, name, email FROM participants ORDER BY name")->fetchAll();

echo "Total participants: " . count($participants) . "\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($participants as $p) {
    echo sprintf("ID: %3d | Name: %-30s | Email: %s\n",
        $p['id'],
        $p['name'],
        $p['email'] ?? 'N/A'
    );
}

echo "\n" . str_repeat('=', 60) . "\n";
?>
