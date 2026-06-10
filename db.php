<?php
// Database connection - update credentials as needed
$DB_HOST = 'localhost';
$DB_NAME = 'unfpa_mis';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

session_start();

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function redirect($u){ header("Location: $u"); exit; }

require_once __DIR__ . '/auth.php';
