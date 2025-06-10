<?php

// Database configuration
$hostname = 'localhost';
$database = 'studentfinger';
$username = 'root';
$password = '';

echo "=== Applying Audit Trail Columns ===\n";
echo "Connecting to database: {$database} on {$hostname}\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host={$hostname};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Successfully connected to MySQL database\n\n";
    
    // SQL statements to add audit trail columns
    $alterStatements = [
        "ALTER TABLE `att_log` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `calender` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `class_sections` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `classes` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `device` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `jns_izin` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `kategori_izin` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `libur` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `sections` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `sessions` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `student_attendance` ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `student_izin` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `student_session` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `students` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `subject_timetable` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `subjects` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_auto_replies` ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_contacts` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_devices` ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_logs` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_messages` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_schedules` ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;",
        "ALTER TABLE `wa_templates` ADD COLUMN `created_at` DATETIME NULL, ADD COLUMN `updated_at` DATETIME NULL, ADD COLUMN `deleted_at` DATETIME NULL;"
    ];
    
    $successCount = 0;
    $errorCount = 0;
    
    echo "Applying audit trail columns to tables...\n\n";
    
    foreach ($alterStatements as $sql) {
        try {
            // Extract table name from SQL for better logging
            preg_match('/ALTER TABLE `([^`]+)`/', $sql, $matches);
            $tableName = $matches[1] ?? 'unknown';
            
            $pdo->exec($sql);
            echo "✓ Updated table: {$tableName}\n";
            $successCount++;
        } catch (PDOException $e) {
            // Check if error is about column already existing
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "⚠ Table {$tableName}: Columns already exist\n";
            } else {
                echo "❌ Error updating table {$tableName}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "✓ Successfully updated: {$successCount} tables\n";
    if ($errorCount > 0) {
        echo "❌ Errors encountered: {$errorCount} tables\n";
    }
    echo "\n✓ Audit trail update completed!\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nScript completed.\n";
?>