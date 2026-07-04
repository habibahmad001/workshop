<?php
// Script to find participants with duplicate names
require_once __DIR__ . '/db.php';

echo "=== Participants with Duplicate Names ===\n\n";

// First, find names that appear more than once
$duplicateNamesQuery = "
    SELECT name, COUNT(*) as count
    FROM participants
    GROUP BY name
    HAVING COUNT(*) > 1
    ORDER BY count DESC, name
";

$duplicateNames = $pdo->query($duplicateNamesQuery)->fetchAll();

if (empty($duplicateNames)) {
    echo "No duplicate names found.\n";
    exit;
}

echo "Found " . count($duplicateNames) . " duplicate name(s):\n";
echo str_repeat('=', 60) . "\n\n";

$totalDuplicateParticipants = 0;

foreach ($duplicateNames as $row) {
    $name = $row['name'];
    $count = $row['count'];

    echo "NAME: " . $name . " (appears " . $count . " times)\n";
    echo str_repeat('-', 60) . "\n";

    // Get all participants with this name
    $participantsQuery = "
        SELECT id, name, email, phone, organization, designation, created_at
        FROM participants
        WHERE name = :name
        ORDER BY id
    ";

    $stmt = $pdo->prepare($participantsQuery);
    $stmt->execute(['name' => $name]);
    $participants = $stmt->fetchAll();

    foreach ($participants as $p) {
        echo "  ID: {$p['id']}\n";
        echo "  Name: {$p['name']}\n";
        echo "  Email: " . ($p['email'] ?? 'N/A') . "\n";
        echo "  Phone: " . ($p['phone'] ?? 'N/A') . "\n";
        echo "  Organization: " . ($p['organization'] ?? 'N/A') . "\n";
        echo "  Designation: " . ($p['designation'] ?? 'N/A') . "\n";
        echo "  Created: " . ($p['created_at'] ?? 'N/A') . "\n";
        echo "  " . str_repeat('-', 58) . "\n";
    }

    $totalDuplicateParticipants += $count;
    echo "\n";
}

echo str_repeat('=', 60) . "\n";
echo "SUMMARY:\n";
echo "- Unique duplicate names: " . count($duplicateNames) . "\n";
echo "- Total participants involved: " . $totalDuplicateParticipants . "\n";
?>
