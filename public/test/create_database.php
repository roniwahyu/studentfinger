<?php
/**
 * Create Database Script
 */

// Load environment variables
$envFile = '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Database Configuration
$hostname = $_ENV['database.default.hostname'] ?? 'localhost';
$database = $_ENV['database.default.database'] ?? 'studentfinger';
$username = $_ENV['database.default.username'] ?? 'root';
$password = $_ENV['database.default.password'] ?? '';

echo "Creating database '{$database}'...\n";

try {
    // Connect without specifying database
    $dsn = "mysql:host={$hostname};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "✅ Database '{$database}' created successfully!\n";
    
    // Switch to the database
    $pdo->exec("USE `{$database}`");
    
    // Show existing tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($tables)) {
        echo "\nExisting tables in '{$database}':\n";
        foreach ($tables as $table) {
            echo "- {$table}\n";
        }
    } else {
        echo "\nNo tables found in '{$database}'. Ready for migrations.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDatabase setup complete!\n";
echo "You can now run: php spark migrate\n";
?>
