<?php
/**
 * Database Migration Runner
 * 
 * Usage: php migrate.php              - Run all pending migrations
 *        php migrate.php rollback     - Rollback the last migration
 *        php migrate.php status       - Show migration status
 */

require_once __DIR__ . '/db.php';

class Migrator
{
    private $pdo;
    private $migrationsDir;

    public function __construct($pdo, $migrationsDir)
    {
        $this->pdo = $pdo;
        $this->migrationsDir = $migrationsDir;
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(191) NOT NULL UNIQUE,
                executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function getMigrations()
    {
        $files = glob($this->migrationsDir . '/*.php');
        // Filter out rollback files
        $files = array_filter($files, function($file) {
            return strpos($file, '.rollback.php') === false;
        });
        sort($files);
        return array_map('basename', $files);
    }

    public function getExecutedMigrations()
    {
        $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY executed_at");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPendingMigrations()
    {
        $all = $this->getMigrations();
        $executed = $this->getExecutedMigrations();
        return array_diff($all, $executed);
    }

    public function run()
    {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            echo "No pending migrations.\n";
            return;
        }

        foreach ($pending as $migration) {
            echo "Running: $migration\n";
            $this->executeMigration($migration);
            $this->recordMigration($migration);
        }

        echo "All migrations completed successfully.\n";
    }

    public function status()
    {
        $all = $this->getMigrations();
        $executed = $this->getExecutedMigrations();

        echo "Migration Status:\n";
        echo str_repeat("-", 60) . "\n";

        foreach ($all as $migration) {
            $status = in_array($migration, $executed) ? "✓" : "⊗";
            echo "[$status] $migration\n";
        }

        $pending = $this->getPendingMigrations();
        echo "\nPending: " . count($pending) . "\n";
    }

    private function executeMigration($name)
    {
        $file = $this->migrationsDir . '/' . $name;

        if (!file_exists($file)) {
            throw new Exception("Migration file not found: $file");
        }

        $content = file_get_contents($file);
        $this->pdo->exec($content);
    }

    private function recordMigration($name)
    {
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$name]);
    }

    public function rollback()
    {
        $executed = $this->getExecutedMigrations();

        if (empty($executed)) {
            echo "No migrations to rollback.\n";
            return;
        }

        $last = end($executed);
        $rollbackFile = $this->migrationsDir . '/' . preg_replace('/\.php$/', '.rollback.php', $last);

        if (!file_exists($rollbackFile)) {
            echo "No rollback script found for: $last\n";
            return;
        }

        echo "Rolling back: $last\n";
        $content = file_get_contents($rollbackFile);
        $this->pdo->exec($content);

        $stmt = $this->pdo->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$last]);

        echo "Rollback completed.\n";
    }
}

$migrationsDir = __DIR__ . '/migrations';

if (!is_dir($migrationsDir)) {
    mkdir($migrationsDir, 0755, true);
}

$migrator = new Migrator($pdo, $migrationsDir);

$action = $argv[1] ?? 'run';

switch ($action) {
    case 'run':
        $migrator->run();
        break;
    case 'status':
        $migrator->status();
        break;
    case 'rollback':
        $migrator->rollback();
        break;
    default:
        echo "Usage: php migrate.php [run|status|rollback]\n";
        exit(1);
}
