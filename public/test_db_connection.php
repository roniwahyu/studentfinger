<?php
/**
 * Database Connection Test
 */

// Load environment variables
$envFile = '../.env';
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

echo "<h2>Database Connection Test</h2>";
echo "<p><strong>Host:</strong> " . htmlspecialchars($hostname) . "</p>";
echo "<p><strong>Database:</strong> " . htmlspecialchars($database) . "</p>";
echo "<p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>";
echo "<p><strong>Password:</strong> " . (empty($password) ? 'Empty' : 'Set') . "</p>";

echo "<hr>";
echo "<h3>Testing Connection...</h3>";

try {
    $dsn = "mysql:host={$hostname};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<div style='color: green; font-weight: bold;'>✅ Database Connection Successful!</div>";
    
    // Test if database exists and show tables
    echo "<h4>Available Tables:</h4>";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ No tables found in database. Migrations need to be run.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        // Check if students table exists and has status column
        if (in_array('students', $tables)) {
            echo "<h4>Students Table Structure:</h4>";
            $stmt = $pdo->query("DESCRIBE students");
            $columns = $stmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Database Connection Failed!</div>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Check if it's a database not found error
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<p style='color: orange;'>⚠️ Database '{$database}' does not exist. You need to create it first.</p>";
        echo "<p><strong>Solution:</strong> Create the database using:</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px;'>CREATE DATABASE {$database};</pre>";
    }
}

echo "<hr>";
echo "<p><a href='/'>← Back to Home</a></p>";
?>
