<?php

// Create Classroom Notifications Tables
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'studentfinger';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully<br>";
    
    // Class Sessions Table
    $sql1 = "CREATE TABLE IF NOT EXISTS `class_sessions` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `class_id` INT(11) UNSIGNED NOT NULL,
        `session_name` VARCHAR(100) NOT NULL,
        `subject` VARCHAR(50) NOT NULL,
        `teacher_name` VARCHAR(100) NOT NULL,
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `break_duration` INT(11) NOT NULL DEFAULT 15,
        `status` ENUM('scheduled', 'started', 'break', 'resumed', 'finished', 'cancelled') NOT NULL DEFAULT 'scheduled',
        `actual_start_time` DATETIME NULL,
        `actual_break_time` DATETIME NULL,
        `actual_resume_time` DATETIME NULL,
        `actual_end_time` DATETIME NULL,
        `total_students` INT(11) NOT NULL DEFAULT 0,
        `present_students` INT(11) NOT NULL DEFAULT 0,
        `notifications_sent` INT(11) NOT NULL DEFAULT 0,
        `session_date` DATE NOT NULL,
        `notes` TEXT NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_class_date` (`class_id`, `session_date`),
        KEY `idx_status` (`status`),
        KEY `idx_session_date` (`session_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql1);
    echo "Created class_sessions table<br>";
    
    // Notification Templates Table
    $sql2 = "CREATE TABLE IF NOT EXISTS `notification_templates` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `template_name` VARCHAR(100) NOT NULL,
        `event_type` ENUM('session_start', 'session_break', 'session_resume', 'session_finish', 'attendance_marked') NOT NULL,
        `message_template` TEXT NOT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `language` ENUM('id', 'en') NOT NULL DEFAULT 'id',
        `variables` JSON NULL,
        `description` VARCHAR(255) NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_event_language` (`event_type`, `language`),
        KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql2);
    echo "Created notification_templates table<br>";
    
    // Notification Logs Table
    $sql3 = "CREATE TABLE IF NOT EXISTS `notification_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `session_id` INT(11) UNSIGNED NOT NULL,
        `student_id` INT(11) UNSIGNED NOT NULL,
        `parent_phone` VARCHAR(20) NOT NULL,
        `parent_name` VARCHAR(100) NULL,
        `event_type` ENUM('session_start', 'session_break', 'session_resume', 'session_finish', 'attendance_marked') NOT NULL,
        `template_id` INT(11) UNSIGNED NULL,
        `message_content` TEXT NOT NULL,
        `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed', 'retry') NOT NULL DEFAULT 'pending',
        `wablas_response` JSON NULL,
        `sent_at` DATETIME NULL,
        `delivered_at` DATETIME NULL,
        `read_at` DATETIME NULL,
        `failed_reason` TEXT NULL,
        `retry_count` INT(11) NOT NULL DEFAULT 0,
        `variables_used` JSON NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_session_id` (`session_id`),
        KEY `idx_student_id` (`student_id`),
        KEY `idx_event_status` (`event_type`, `status`),
        KEY `idx_status` (`status`),
        KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql3);
    echo "Created notification_logs table<br>";
    
    // Insert default templates
    $templates = [
        [
            'template_name' => 'Kelas Dimulai - Default',
            'event_type' => 'session_start',
            'message_template' => "🎓 *KELAS DIMULAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKami informasikan bahwa {student_name} telah hadir di kelas:\n\n📚 *Mata Pelajaran:* {subject}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Mulai:* {start_time}\n📅 *Tanggal:* {session_date}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
            'is_active' => 1,
            'language' => 'id',
            'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'start_time', 'session_date', 'school_name']),
            'description' => 'Template notifikasi saat kelas dimulai'
        ],
        [
            'template_name' => 'Istirahat Kelas - Default',
            'event_type' => 'session_break',
            'message_template' => "☕ *ISTIRAHAT KELAS*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} sedang istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Istirahat:* {break_time}\n⏱️ *Durasi:* {break_duration} menit\n\nKelas akan dilanjutkan setelah istirahat.\n\n*{school_name}*",
            'is_active' => 1,
            'language' => 'id',
            'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'break_time', 'break_duration', 'school_name']),
            'description' => 'Template notifikasi saat kelas istirahat'
        ],
        [
            'template_name' => 'Kelas Dilanjutkan - Default',
            'event_type' => 'session_resume',
            'message_template' => "📚 *KELAS DILANJUTKAN*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah dilanjutkan setelah istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Lanjut:* {resume_time}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
            'is_active' => 1,
            'language' => 'id',
            'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'resume_time', 'school_name']),
            'description' => 'Template notifikasi saat kelas dilanjutkan'
        ],
        [
            'template_name' => 'Kelas Selesai - Default',
            'event_type' => 'session_finish',
            'message_template' => "✅ *KELAS SELESAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah selesai:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Selesai:* {end_time}\n⏱️ *Durasi Total:* {total_duration}\n\n{student_name} dapat dijemput atau pulang sesuai jadwal.\n\n*{school_name}*",
            'is_active' => 1,
            'language' => 'id',
            'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'end_time', 'total_duration', 'school_name']),
            'description' => 'Template notifikasi saat kelas selesai'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO notification_templates (template_name, event_type, message_template, is_active, language, variables, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($templates as $template) {
        $stmt->execute([
            $template['template_name'],
            $template['event_type'],
            $template['message_template'],
            $template['is_active'],
            $template['language'],
            $template['variables'],
            $template['description']
        ]);
    }
    
    echo "Inserted default templates<br>";
    
    // Insert sample sessions
    $sessions = [
        [1, 'Matematika Pagi', 'Matematika', 'Mrs. Sari', '08:00:00', '10:00:00', 15, 'scheduled', date('Y-m-d'), 25, 0, 0, 'Sesi matematika untuk kelas pagi'],
        [1, 'Bahasa Indonesia', 'Bahasa Indonesia', 'Mr. Ahmad', '10:30:00', '12:00:00', 10, 'scheduled', date('Y-m-d'), 25, 0, 0, 'Sesi bahasa Indonesia'],
        [2, 'IPA Terpadu', 'IPA', 'Mrs. Dewi', '13:00:00', '15:00:00', 20, 'scheduled', date('Y-m-d'), 30, 0, 0, 'Sesi IPA terpadu siang']
    ];
    
    $stmt2 = $pdo->prepare("INSERT IGNORE INTO class_sessions (class_id, session_name, subject, teacher_name, start_time, end_time, break_duration, status, session_date, total_students, present_students, notifications_sent, notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($sessions as $session) {
        $stmt2->execute($session);
    }
    
    echo "Inserted sample sessions<br>";
    echo "<h2>All tables created successfully!</h2>";
    echo "<a href='/classroom-notifications'>Go to Classroom Notifications Dashboard</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
