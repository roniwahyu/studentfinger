<?php
/**
 * Create Settings Table for Classroom Notifications
 */

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'studentfinger';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully<br>";
    
    // Settings Table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_settings` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(100) NOT NULL,
        `setting_value` TEXT NULL,
        `setting_type` ENUM('string', 'integer', 'float', 'boolean', 'json') NOT NULL DEFAULT 'string',
        `description` TEXT NULL,
        `is_encrypted` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_setting_key` (`setting_key`),
        KEY `idx_setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "Created notification_settings table<br>";
    
    // Insert default settings
    $defaultSettings = [
        ['wablas_base_url', 'https://texas.wablas.com', 'string', 'WABLAS API Base URL', 0],
        ['wablas_token', '', 'string', 'WABLAS API Token', 1],
        ['wablas_secret_key', '', 'string', 'WABLAS API Secret Key', 1],
        ['wablas_test_phone', '628123456789', 'string', 'Test Phone Number for WABLAS', 0],
        ['wablas_timeout', '30', 'integer', 'API Timeout in seconds', 0],
        ['wablas_retry_attempts', '3', 'integer', 'Number of retry attempts', 0],
        ['wablas_auto_check_interval', '5', 'integer', 'Auto check interval in minutes', 0],
        ['auto_send_on_session_start', '1', 'boolean', 'Auto send notification on session start', 0],
        ['auto_send_on_session_break', '0', 'boolean', 'Auto send notification on session break', 0],
        ['auto_send_on_session_resume', '0', 'boolean', 'Auto send notification on session resume', 0],
        ['auto_send_on_session_finish', '1', 'boolean', 'Auto send notification on session finish', 0],
        ['default_language', 'id', 'string', 'Default notification language', 0],
        ['school_name', 'Student Finger School', 'string', 'School name for notifications', 0],
        ['notification_delay', '0', 'integer', 'Delay before sending notifications (seconds)', 0],
        ['max_retry_attempts', '3', 'integer', 'Maximum retry attempts for failed notifications', 0],
        ['retry_delay', '300', 'integer', 'Delay between retry attempts (seconds)', 0]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO notification_settings (setting_key, setting_value, setting_type, description, is_encrypted, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }
    
    echo "Inserted default settings<br>";
    
    echo "<h2>Settings Table Created Successfully!</h2>";
    echo "<a href='/classroom-notifications/settings'>Go to Settings Page</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
