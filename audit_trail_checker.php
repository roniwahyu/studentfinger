<?php

// Simple .env parser
function loadEnv($path) {
    if (!file_exists($path)) {
        return [];
    }
    
    $vars = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B'\"");
            $vars[$key] = $value;
        }
    }
    
    return $vars;
}

// Load environment variables
$envVars = loadEnv('.env');

// Database configuration from .env with fallbacks
$hostname = $envVars['database.default.hostname'] ?? 'localhost';
$database = $envVars['database.default.database'] ?? 'studentfinger';
$username = $envVars['database.default.username'] ?? 'root';
$password = $envVars['database.default.password'] ?? '';

echo "=== MySQL Database Audit Trail Checker ===\n";
echo "Connecting to database: {$database} on {$hostname}\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host={$hostname};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Successfully connected to MySQL database\n\n";
    
    // Get all tables in the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "No tables found in database '{$database}'\n";
        exit(1);
    }
    
    echo "Found " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "- {$table}\n";
    }
    echo "\n";
    
    // Required audit trail columns
    $auditColumns = [
        'created_at' => 'DATETIME NULL',
        'updated_at' => 'DATETIME NULL', 
        'deleted_at' => 'DATETIME NULL'
    ];
    
    $tablesNeedingAudit = [];
    
    // Check each table for audit trail columns
    foreach ($tables as $table) {
        echo "Checking table: {$table}\n";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE `{$table}`");
        $columns = $stmt->fetchAll();
        
        $existingColumns = array_column($columns, 'Field');
        $missingColumns = [];
        
        foreach ($auditColumns as $auditCol => $definition) {
            if (!in_array($auditCol, $existingColumns)) {
                $missingColumns[] = $auditCol;
            }
        }
        
        if (!empty($missingColumns)) {
            $tablesNeedingAudit[$table] = $missingColumns;
            echo "  ❌ Missing audit columns: " . implode(', ', $missingColumns) . "\n";
        } else {
            echo "  ✓ All audit trail columns present\n";
        }
        
        // Show existing audit columns
        $existingAuditCols = array_intersect($existingColumns, array_keys($auditColumns));
        if (!empty($existingAuditCols)) {
            echo "  ✓ Existing audit columns: " . implode(', ', $existingAuditCols) . "\n";
        }
        
        echo "\n";
    }
    
    // Summary
    echo "=== SUMMARY ===\n";
    if (empty($tablesNeedingAudit)) {
        echo "✓ All tables have complete audit trail columns!\n";
    } else {
        echo "❌ Tables needing audit trail columns: " . count($tablesNeedingAudit) . "\n\n";
        
        // Generate ALTER TABLE statements
        echo "=== SQL STATEMENTS TO ADD MISSING AUDIT COLUMNS ===\n\n";
        
        foreach ($tablesNeedingAudit as $table => $missingCols) {
            echo "-- Table: {$table}\n";
            $alterStatements = [];
            
            foreach ($missingCols as $col) {
                $alterStatements[] = "ADD COLUMN `{$col}` {$auditColumns[$col]}";
            }
            
            $sql = "ALTER TABLE `{$table}` " . implode(', ', $alterStatements) . ";";
            echo $sql . "\n\n";
        }
        
        // Ask user if they want to apply changes
        echo "Do you want to apply these changes? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            echo "\nApplying changes...\n\n";
            
            foreach ($tablesNeedingAudit as $table => $missingCols) {
                try {
                    $alterStatements = [];
                    foreach ($missingCols as $col) {
                        $alterStatements[] = "ADD COLUMN `{$col}` {$auditColumns[$col]}";
                    }
                    
                    $sql = "ALTER TABLE `{$table}` " . implode(', ', $alterStatements);
                    $pdo->exec($sql);
                    
                    echo "✓ Updated table: {$table}\n";
                } catch (PDOException $e) {
                    echo "❌ Error updating table {$table}: " . $e->getMessage() . "\n";
                }
            }
            
            echo "\n✓ Audit trail update completed!\n";
        } else {
            echo "\nNo changes applied.\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nScript completed.\n";
?>