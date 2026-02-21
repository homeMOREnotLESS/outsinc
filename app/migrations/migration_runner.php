<?php
/**
 * Database Migration Runner
 * Executes SQL migration files in sequence
 */

require_once __DIR__ . '/../../config/bootstrap.php';

class MigrationRunner {
    private $db;
    private $migrationsPath;

    public function __construct($migrationsPath) {
        $this->migrationsPath = $migrationsPath;
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Run all pending migrations
     */
    public function runUp() {
        $this->ensureMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();

        $files = glob($this->migrationsPath . '/*.sql');
        asort($files);

        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $appliedMigrations)) {
                echo "Skipping already-applied migration: $filename\n";
                continue;
            }

            echo "Running migration: $filename... ";
            try {
                $this->executeSqlFile($file);
                $this->recordMigration($filename);
                echo "OK\n";
            } catch (Exception $e) {
                echo "FAILED\n";
                echo "Error: " . $e->getMessage() . "\n";
                exit(1);
            }
        }

        echo "\nAll migrations completed successfully!\n";
    }

    /**
     * Execute SQL file
     */
    private function executeSqlFile($filepath) {
        $sql = file_get_contents($filepath);

        // Split by semicolons and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => !empty($s) && substr($s, 0, 2) !== '--'
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $this->db->query($statement);
            }
        }
    }

    /**
     * Ensure migrations table exists
     */
    private function ensureMigrationsTable() {
        try {
            $this->db->query("SELECT 1 FROM schema_migrations LIMIT 1");
        } catch (Exception $e) {
            $this->db->query("
                CREATE TABLE schema_migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) UNIQUE NOT NULL,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
    }

    /**
     * Get list of applied migrations
     */
    private function getAppliedMigrations() {
        $this->db->query("SELECT filename FROM schema_migrations ORDER BY applied_at");
        $result = $this->db->fetchAll();
        return array_column($result, 'filename');
    }

    /**
     * Record applied migration
     */
    private function recordMigration($filename) {
        $this->db->insert('schema_migrations', ['filename' => $filename]);
    }
}

// Run migrations
if (php_sapi_name() === 'cli') {
    try {
        $runner = new MigrationRunner(__DIR__);
        $runner->runUp();
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
