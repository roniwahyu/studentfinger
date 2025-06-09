<?php
/**
 * Create Contact Management Tables for Classroom Notifications
 */

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'studentfinger';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully<br>";
    
    // Parent Contacts Table
    $sql1 = "CREATE TABLE IF NOT EXISTS `parent_contacts` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `student_id` INT(11) UNSIGNED NOT NULL,
        `contact_type` ENUM('father', 'mother', 'guardian', 'emergency') NOT NULL DEFAULT 'father',
        `contact_name` VARCHAR(100) NOT NULL,
        `phone_number` VARCHAR(20) NOT NULL,
        `whatsapp_number` VARCHAR(20) NULL,
        `email` VARCHAR(100) NULL,
        `relationship` VARCHAR(50) NULL,
        `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `receive_notifications` TINYINT(1) NOT NULL DEFAULT 1,
        `notification_preferences` JSON NULL COMMENT 'Notification preferences for different events',
        `notes` TEXT NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_student_id` (`student_id`),
        KEY `idx_contact_type` (`contact_type`),
        KEY `idx_is_primary` (`is_primary`),
        KEY `idx_is_active` (`is_active`),
        KEY `idx_phone_number` (`phone_number`),
        UNIQUE KEY `unique_student_primary` (`student_id`, `is_primary`, `deleted_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql1);
    echo "Created parent_contacts table<br>";
    
    // Contact Groups Table
    $sql2 = "CREATE TABLE IF NOT EXISTS `contact_groups` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_name` VARCHAR(100) NOT NULL,
        `description` TEXT NULL,
        `group_type` ENUM('class', 'custom', 'grade', 'subject') NOT NULL DEFAULT 'custom',
        `class_id` INT(11) UNSIGNED NULL,
        `grade_level` VARCHAR(10) NULL,
        `subject` VARCHAR(50) NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) UNSIGNED NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_group_type` (`group_type`),
        KEY `idx_class_id` (`class_id`),
        KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql2);
    echo "Created contact_groups table<br>";
    
    // Contact Group Members Table
    $sql3 = "CREATE TABLE IF NOT EXISTS `contact_group_members` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` INT(11) UNSIGNED NOT NULL,
        `contact_id` INT(11) UNSIGNED NOT NULL,
        `added_at` DATETIME NULL,
        `added_by` INT(11) UNSIGNED NULL,
        PRIMARY KEY (`id`),
        KEY `idx_group_id` (`group_id`),
        KEY `idx_contact_id` (`contact_id`),
        UNIQUE KEY `unique_group_contact` (`group_id`, `contact_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql3);
    echo "Created contact_group_members table<br>";
    
    // WhatsApp Connection Status Table
    $sql4 = "CREATE TABLE IF NOT EXISTS `whatsapp_connection_status` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `device_id` VARCHAR(50) NULL,
        `device_name` VARCHAR(100) NULL,
        `connection_status` ENUM('connected', 'disconnected', 'connecting', 'error') NOT NULL DEFAULT 'disconnected',
        `last_check` DATETIME NULL,
        `last_connected` DATETIME NULL,
        `error_message` TEXT NULL,
        `api_response` JSON NULL,
        `quota_remaining` INT(11) NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql4);
    echo "Created whatsapp_connection_status table<br>";
    
    // Insert sample parent contacts
    $sampleContacts = [
        [1, 'father', 'Bapak Ahmad Rizki', '081234567890', '081234567890', 'ahmad.rizki@email.com', 'Ayah', 1, 1, 1],
        [1, 'mother', 'Ibu Siti Rizki', '081234567891', '081234567891', 'siti.rizki@email.com', 'Ibu', 0, 1, 1],
        [2, 'father', 'Bapak Budi Santoso', '081234567892', '081234567892', 'budi.santoso@email.com', 'Ayah', 1, 1, 1],
        [2, 'mother', 'Ibu Dewi Santoso', '081234567893', '081234567893', 'dewi.santoso@email.com', 'Ibu', 0, 1, 1],
        [3, 'guardian', 'Kakek Hasan', '081234567894', '081234567894', 'hasan@email.com', 'Kakek', 1, 1, 1],
        [4, 'father', 'Bapak Andi Wijaya', '081234567895', '081234567895', 'andi.wijaya@email.com', 'Ayah', 1, 1, 1],
        [5, 'mother', 'Ibu Rina Sari', '081234567896', '081234567896', 'rina.sari@email.com', 'Ibu', 1, 1, 1]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO parent_contacts (student_id, contact_type, contact_name, phone_number, whatsapp_number, email, relationship, is_primary, is_active, receive_notifications, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($sampleContacts as $contact) {
        $stmt->execute($contact);
    }
    
    echo "Inserted sample parent contacts<br>";
    
    // Insert sample contact groups
    $sampleGroups = [
        ['Kelas X-A Parents', 'Grup orang tua kelas X-A', 'class', 1, 'X', null],
        ['Kelas X-B Parents', 'Grup orang tua kelas X-B', 'class', 2, 'X', null],
        ['Grade X Parents', 'Semua orang tua kelas X', 'grade', null, 'X', null],
        ['Math Parents', 'Orang tua siswa mata pelajaran Matematika', 'subject', null, null, 'Matematika'],
        ['Emergency Contacts', 'Kontak darurat sekolah', 'custom', null, null, null]
    ];
    
    $stmt2 = $pdo->prepare("INSERT IGNORE INTO contact_groups (group_name, description, group_type, class_id, grade_level, subject, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($sampleGroups as $group) {
        $stmt2->execute($group);
    }
    
    echo "Inserted sample contact groups<br>";
    
    // Insert initial WhatsApp connection status
    $pdo->exec("INSERT IGNORE INTO whatsapp_connection_status (device_id, device_name, connection_status, last_check, created_at, updated_at) VALUES ('YXP7D0', 'Student Finger Device', 'disconnected', NOW(), NOW(), NOW())");
    
    echo "Inserted initial WhatsApp connection status<br>";
    
    echo "<h2>Contact Management Tables Created Successfully!</h2>";
    echo "<a href='/classroom-notifications'>Go to Classroom Notifications Dashboard</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
