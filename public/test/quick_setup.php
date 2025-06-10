<?php

/**
 * Quick setup script for FingerprintBridge module
 */

echo "FingerprintBridge Quick Setup\n";
echo "============================\n\n";

try {
    // Connect to MySQL
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Creating databases...\n";
    
    // Create databases
    $pdo->exec("CREATE DATABASE IF NOT EXISTS fin_pro");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS studentfinger");
    echo "   ✓ Databases created\n";
    
    echo "\n2. Creating fin_pro.att_log table and test data...\n";
    
    // Switch to fin_pro database
    $pdo->exec("USE fin_pro");
    
    // Create att_log table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `att_log` (
            `sn` VARCHAR(30) NOT NULL,
            `scan_date` DATETIME NOT NULL,
            `pin` VARCHAR(32) NOT NULL,
            `verifymode` INT(11) NOT NULL,
            `inoutmode` INT(11) NOT NULL DEFAULT 0,
            `reserved` INT(11) NOT NULL DEFAULT 0,
            `work_code` INT(11) NOT NULL DEFAULT 0,
            `att_id` VARCHAR(50) NOT NULL DEFAULT '0',
            PRIMARY KEY (`sn`, `scan_date`, `pin`),
            KEY `pin` (`pin`),
            KEY `sn` (`sn`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
    ");
    
    // Insert test data
    $pdo->exec("
        INSERT IGNORE INTO `att_log` VALUES
        ('FIO66205020150662', '2025-01-09 07:15:00', '1001', 1, 1, 0, 0, '0'),
        ('FIO66205020150662', '2025-01-09 07:16:00', '1002', 20, 1, 0, 0, '0'),
        ('FIO66205020150662', '2025-01-09 07:17:00', '1003', 3, 1, 0, 0, '0'),
        ('FIO66205020150662', '2025-01-09 12:00:00', '1001', 1, 2, 0, 0, '0'),
        ('FIO66205020150662', '2025-01-09 12:01:00', '1002', 20, 2, 0, 0, '0'),
        ('FIO66205020150662', '2025-01-09 12:02:00', '1003', 3, 2, 0, 0, '0'),
        ('66208023321907', '2025-01-09 08:00:00', '2001', 20, 1, 0, 0, '0'),
        ('66208023321907', '2025-01-09 08:01:00', '2002', 1, 1, 0, 0, '0')
    ");
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM att_log");
    $count = $stmt->fetchColumn();
    echo "   ✓ fin_pro.att_log created with {$count} test records\n";
    
    echo "\n3. Creating studentfinger tables...\n";
    
    // Switch to studentfinger database
    $pdo->exec("USE studentfinger");
    
    // Create students table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `students` (
            `student_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `admission_no` VARCHAR(50) NOT NULL,
            `firstname` VARCHAR(100) NOT NULL,
            `lastname` VARCHAR(100) NULL,
            `mobileno` VARCHAR(20) NULL,
            `email` VARCHAR(100) NULL,
            `father_phone` VARCHAR(20) NULL,
            `rfid` VARCHAR(32) NULL,
            `status` TINYINT(4) NOT NULL DEFAULT 1,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            `deleted_at` DATETIME NULL,
            PRIMARY KEY (`student_id`),
            UNIQUE KEY `admission_no` (`admission_no`),
            UNIQUE KEY `rfid` (`rfid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // Insert test students
    $pdo->exec("
        INSERT IGNORE INTO `students` VALUES
        (1, 'STD001', 'John', 'Doe', '081234567890', 'john.doe@email.com', '081234567891', '1001', 1, NOW(), NOW(), NULL),
        (2, 'STD002', 'Jane', 'Smith', '081234567892', 'jane.smith@email.com', '081234567893', '1002', 1, NOW(), NOW(), NULL),
        (3, 'STD003', 'Bob', 'Johnson', '081234567894', 'bob.johnson@email.com', '081234567895', '1003', 1, NOW(), NOW(), NULL),
        (4, 'STD004', 'Alice', 'Brown', '081234567896', 'alice.brown@email.com', '081234567897', '2001', 1, NOW(), NOW(), NULL),
        (5, 'STD005', 'Charlie', 'Wilson', '081234567898', 'charlie.wilson@email.com', '081234567899', '2002', 1, NOW(), NOW(), NULL)
    ");
    
    echo "   ✓ Students table created with test data\n";
    
    echo "\n4. Creating FingerprintBridge tables...\n";
    
    // Create fingerprint_import_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `fingerprint_import_logs` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `import_type` ENUM('manual', 'auto', 'scheduled') NOT NULL DEFAULT 'manual',
            `start_date` DATETIME NULL,
            `end_date` DATETIME NULL,
            `status` ENUM('pending', 'running', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
            `total_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `processed_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `inserted_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `updated_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `skipped_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `error_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `start_time` DATETIME NULL,
            `end_time` DATETIME NULL,
            `duration` INT(11) UNSIGNED NULL,
            `error_message` TEXT NULL,
            `settings` JSON NULL,
            `user_id` INT(11) UNSIGNED NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            KEY `idx_status_created` (`status`, `created_at`),
            KEY `idx_import_type` (`import_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // Create student_pin_mapping table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `student_pin_mapping` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `pin` VARCHAR(32) NOT NULL,
            `student_id` INT(11) UNSIGNED NOT NULL,
            `rfid_card` VARCHAR(50) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `notes` VARCHAR(255) NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            `deleted_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_pin` (`pin`),
            UNIQUE KEY `uk_student_id` (`student_id`),
            KEY `idx_is_active` (`is_active`),
            KEY `idx_deleted_at` (`deleted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    // Create fingerprint_import_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `fingerprint_import_settings` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `setting_key` VARCHAR(100) NOT NULL,
            `setting_value` TEXT NULL,
            `setting_type` ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
            `description` VARCHAR(255) NULL,
            `is_system` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_setting_key` (`setting_key`),
            KEY `idx_is_system` (`is_system`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    
    echo "   ✓ FingerprintBridge tables created\n";
    
    echo "\n5. Inserting default settings...\n";
    
    // Insert default settings
    $settings = [
        ['auto_import_enabled', '0', 'boolean', 'Enable automatic import from fingerprint machine', 1],
        ['auto_import_interval', '300', 'integer', 'Auto import interval in seconds', 1],
        ['import_batch_size', '1000', 'integer', 'Number of records to process in each batch', 1],
        ['duplicate_handling', 'skip', 'string', 'How to handle duplicate records', 1],
        ['default_status', '1', 'integer', 'Default status for imported attendance records', 1],
        ['log_retention_days', '30', 'integer', 'Number of days to keep import logs', 1],
        ['verify_student_exists', '1', 'boolean', 'Verify that student exists before importing', 1],
        ['create_missing_students', '0', 'boolean', 'Automatically create missing students', 1]
    ];
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO `fingerprint_import_settings` 
        (`setting_key`, `setting_value`, `setting_type`, `description`, `is_system`, `created_at`, `updated_at`) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    
    echo "   ✓ Default settings inserted\n";
    
    echo "\n6. Creating initial PIN mappings...\n";
    
    // Create PIN mappings based on RFID
    $pdo->exec("
        INSERT IGNORE INTO `student_pin_mapping` (`pin`, `student_id`, `rfid_card`, `is_active`, `notes`, `created_at`, `updated_at`)
        SELECT s.rfid, s.student_id, s.rfid, 1, 'Auto-created from RFID', NOW(), NOW()
        FROM students s 
        WHERE s.rfid IS NOT NULL AND s.rfid != ''
    ");
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM student_pin_mapping");
    $mappingCount = $stmt->fetchColumn();
    echo "   ✓ {$mappingCount} PIN mappings created\n";
    
    echo "\n7. Verifying setup...\n";
    
    // Verify fin_pro
    $pdo->exec("USE fin_pro");
    $stmt = $pdo->query("SELECT COUNT(*) FROM att_log");
    $finProCount = $stmt->fetchColumn();
    echo "   ✓ fin_pro.att_log: {$finProCount} records\n";
    
    // Verify studentfinger
    $pdo->exec("USE studentfinger");
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $studentCount = $stmt->fetchColumn();
    echo "   ✓ studentfinger.students: {$studentCount} records\n";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'fingerprint%'");
    $tables = $stmt->fetchAll();
    echo "   ✓ FingerprintBridge tables: " . count($tables) . " tables\n";
    
    echo "\n============================\n";
    echo "Setup completed successfully!\n";
    echo "============================\n\n";
    
    echo "You can now access:\n";
    echo "- Dashboard: http://studentfinger.me/fingerprint-bridge\n";
    echo "- Manual Import: http://studentfinger.me/fingerprint-bridge/manual-import\n";
    echo "- Test Page: http://studentfinger.me/test_fingerprint_bridge.php\n\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
