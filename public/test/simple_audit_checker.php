<?php

// Database configuration
$hostname = 'localhost';
$database = 'studentfinger';
$username = 'root';
$password = '';

$output = "=== MySQL Database Audit Trail Checker ===\n";
$output .= "Connecting to database: {$database} on {$hostname}\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host={$hostname};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    $output .= "✓ Successfully connected to MySQL database\n\n";
    
    // Get all tables in the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        $output .= "No tables found in database '{$database}'\n";
    } else {
        $output .= "Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            $output .= "- {$table}\n";
        }
        $output .= "\n";
        
        // Required audit trail columns
        $auditColumns = [
            'created_at' => 'DATETIME NULL',
            'updated_at' => 'DATETIME NULL', 
            'deleted_at' => 'DATETIME NULL'
        ];
        
        $tablesNeedingAudit = [];
        
        // Check each table for audit trail columns
        foreach ($tables as $table) {
            $output .= "Checking table: {$table}\n";
            
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
                $output .= "  ❌ Missing audit columns: " . implode(', ', $missingColumns) . "\n";
            } else {
                $output .= "  ✓ All audit trail columns present\n";
            }
            
            // Show existing audit columns
            $existingAuditCols = array_intersect($existingColumns, array_keys($auditColumns));
            if (!empty($existingAuditCols)) {
                $output .= "  ✓ Existing audit columns: " . implode(', ', $existingAuditCols) . "\n";
            }
            
            $output .= "\n";
        }
        
        // Summary
        $output .= "=== SUMMARY ===\n";
        if (empty($tablesNeedingAudit)) {
            $output .= "✓ All tables have complete audit trail columns!\n";
        } else {
            $output .= "❌ Tables needing audit trail columns: " . count($tablesNeedingAudit) . "\n\n";
            
            // Generate ALTER TABLE statements
            $output .= "=== SQL STATEMENTS TO ADD MISSING AUDIT COLUMNS ===\n\n";
            
            foreach ($tablesNeedingAudit as $table => $missingCols) {
                $output .= "-- Table: {$table}\n";
                $alterStatements = [];
                
                foreach ($missingCols as $col) {
                    $alterStatements[] = "ADD COLUMN `{$col}` {$auditColumns[$col]}";
                }
                
                $sql = "ALTER TABLE `{$table}` " . implode(', ', $alterStatements) . ";";
                $output .= $sql . "\n\n";
            }
        }
    }
    
} catch (PDOException $e) {
    $output .= "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    $output .= "❌ Error: " . $e->getMessage() . "\n";
}

$output .= "\nScript completed.\n";

// Write output to file
file_put_contents('audit_check_results.txt', $output);
echo $output;

?>