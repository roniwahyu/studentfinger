/*
 Database Structure for Student Attendance and WhatsApp Notification System
 Combined from multiple SQL files:
 - additional_wablas_tables.sql
 - database_additional_wablas_tables.sql
 - database_student_attendance_whatsapp.sql
 - student_attendance_structure.sql
 - student_attendance_whatsapp.sql
 - student_attendance_whatsapp (2).sql
 - student_attendance_whatsapp (3).sql
 - student_attendance_whatsapp2.sql
 
 Integrates simschool1.sql (student-related tables) and fin_pro_backup_structure04062025.sql (attendance tables)
 Date: Combined on 06/06/2025
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================
-- CORE STUDENT MANAGEMENT TABLES
-- ============================

-- ----------------------------
-- Table structure for students
-- From simschool1.sql, core student data
-- Added RFID for attendance scanning
-- ----------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `admission_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `firstname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mobileno` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Student contact for WhatsApp',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `father_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Parent contact for WhatsApp',
  `rfid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'For FingerSpot attendance scanning',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive',
  PRIMARY KEY (`student_id`) USING BTREE,
  UNIQUE KEY `admission_no` (`admission_no`) USING BTREE,
  UNIQUE KEY `rfid` (`rfid`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sessions
-- From simschool1.sql, academic sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., 2024-2025',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: Inactive, 1: Active',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for classes
-- From simschool1.sql, class information
-- ----------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for sections
-- From simschool1.sql, section information
-- ----------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for class_sections
-- From simschool1.sql, links classes and sections
-- ----------------------------
DROP TABLE IF EXISTS `class_sections`;
CREATE TABLE `class_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for student_session
-- From simschool1.sql, links students to academic sessions
-- ----------------------------
DROP TABLE IF EXISTS `student_session`;
CREATE TABLE `student_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL COMMENT 'Academic session (e.g., 2024-2025)',
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ============================
-- ACADEMIC STRUCTURE TABLES
-- ============================

-- ----------------------------
-- Table structure for subjects
-- From simschool1.sql, subject information
-- ----------------------------
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for subject_timetable
-- From simschool1.sql, class schedules
-- Added tolerance_late for late validation
-- ----------------------------
DROP TABLE IF EXISTS `subject_timetable`;
CREATE TABLE `subject_timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `day` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., Monday',
  `time_from` time NOT NULL,
  `time_to` time NOT NULL,
  `room_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tolerance_late` int(11) NOT NULL DEFAULT 10 COMMENT 'Late tolerance in minutes',
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ============================
-- ATTENDANCE SYSTEM TABLES
-- ============================

-- ----------------------------
-- Table structure for device
-- From fin_pro, attendance devices
-- ----------------------------
DROP TABLE IF EXISTS `device`;
CREATE TABLE `device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialnumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '',
  `comm_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: Ethernet, 1: USB, 2: Serial',
  `dev_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: ZK, 1: Hanvon, 2: Realand',
  `last_download` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `serialnumber` (`serialnumber`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for att_log
-- From fin_pro, raw attendance logs from FingerSpot device
-- ----------------------------
DROP TABLE IF EXISTS `att_log`;
CREATE TABLE `att_log` (
  `att_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `pin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `scan_date` datetime NOT NULL,
  `verifymode` int(11) DEFAULT NULL COMMENT '0: Fingerprint, 1: RFID, 2: Face',
  `status` int(11) DEFAULT NULL COMMENT '0: Absent, 1: Present, 2: Late, 3: Permission',
  `serialnumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`att_id`) USING BTREE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for student_attendance
-- Processed attendance records
-- ----------------------------
DROP TABLE IF EXISTS `student_attendance`;
CREATE TABLE `student_attendance` (
  `student_id` int(11) NOT NULL,
  `timetable_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0: Absent, 1: Present, 2: Late, 3: Permission',
  `scan_in` time DEFAULT NULL,
  `scan_out` time DEFAULT NULL,
  `late_minutes` int(11) DEFAULT 0,
  `izin_jenis_id` int(11) DEFAULT NULL,
  `notification_sent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: Not sent, 1: Sent',
  PRIMARY KEY (`student_id`,`timetable_id`,`attendance_date`) USING BTREE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`timetable_id`) REFERENCES `subject_timetable` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`izin_jenis_id`) REFERENCES `jns_izin` (`izin_jenis_id`) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ============================
-- PERMISSION SYSTEM TABLES
-- ============================

-- ----------------------------
-- Table structure for jns_izin
-- From fin_pro, permission types
-- ----------------------------
DROP TABLE IF EXISTS `jns_izin`;
CREATE TABLE `jns_izin` (
  `izin_jenis_id` int(11) NOT NULL AUTO_INCREMENT,
  `izin_jenis_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`izin_jenis_id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for kategori_izin
-- From fin_pro, permission categories
-- ----------------------------
DROP TABLE IF EXISTS `kategori_izin`;
CREATE TABLE `kategori_izin` (
  `kat_izin_id` int(11) NOT NULL AUTO_INCREMENT,
  `kat_izin_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`kat_izin_id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for student_izin
-- Student permissions (e.g., sick leave)
-- ----------------------------
DROP TABLE IF EXISTS `student_izin`;
CREATE TABLE `student_izin` (
  `student_id` int(11) NOT NULL,
  `izin_urutan` int(11) NOT NULL,
  `izin_tgl_pengajuan` date NOT NULL,
  `izin_tgl` date NOT NULL,
  `izin_jenis_id` int(11) NOT NULL,
  `izin_catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `izin_status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: Rejected, 1: Approved',
  `kat_izin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`student_id`,`izin_urutan`) USING BTREE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  FOREIGN KEY (`izin_jenis_id`) REFERENCES `jns_izin` (`izin_jenis_id`) ON DELETE RESTRICT,
  FOREIGN KEY (`kat_izin_id`) REFERENCES `kategori_izin` (`kat_izin_id`) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ============================
-- CALENDAR AND HOLIDAY TABLES
-- ============================

-- ----------------------------
-- Table structure for libur
-- Holidays
-- ----------------------------
DROP TABLE IF EXISTS `libur`;
CREATE TABLE `libur` (
  `libur_id` int(11) NOT NULL AUTO_INCREMENT,
  `libur_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libur_date` date NOT NULL,
  PRIMARY KEY (`libur_id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for calender
-- Academic calendar
-- ----------------------------
DROP TABLE IF EXISTS `calender`;
CREATE TABLE `calender` (
  `calender_id` int(11) NOT NULL AUTO_INCREMENT,
  `calender_date` date NOT NULL,
  `calender_status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Holiday',
  PRIMARY KEY (`calender_id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ============================
-- WHATSAPP INTEGRATION TABLES
-- ============================

-- ----------------------------
-- Table structure for wa_devices
-- Wablas API device management
-- ----------------------------
DROP TABLE IF EXISTS `wa_devices`;
CREATE TABLE `wa_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Active, 0: Inactive',
  `api_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for wa_schedules
-- Scheduled WhatsApp messages
-- ----------------------------
DROP TABLE IF EXISTS `wa_schedules`;
CREATE TABLE `wa_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `schedule_time` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: Pending, 1: Sent, 2: Failed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`device_id`) REFERENCES `wa_devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for wa_auto_replies
-- Auto-reply configurations
-- ----------------------------
DROP TABLE IF EXISTS `wa_auto_replies`;
CREATE TABLE `wa_auto_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `keyword` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reply_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`device_id`) REFERENCES `wa_devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for wa_contacts
-- WhatsApp contacts for notifications
-- ----------------------------
DROP TABLE IF EXISTS `wa_contacts`;
CREATE TABLE `wa_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) DEFAULT NULL,
  `contact_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contact_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., Parent, Student',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `contact_number` (`contact_number`) USING BTREE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for wa_templates
-- WhatsApp message templates
-- ----------------------------
DROP TABLE IF EXISTS `wa_templates`;
CREATE TABLE `wa_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `template_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `template_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g., attendance, permission',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for wa_messages
-- Sent WhatsApp messages
-- ----------------------------
DROP TABLE IF EXISTS `wa_messages`;
CREATE TABLE `wa_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `message_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `send_date` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0: Pending, 1: Sent, 2: Failed',
  `student_id` int(11) DEFAULT NULL,
  `timetable_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`contact_id`) REFERENCES `wa_contacts` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`template_id`) REFERENCES `wa_templates` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE SET NULL,
  FOREIGN KEY (`timetable_id`) REFERENCES `subject_timetable` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for wa_logs
-- Logs for WhatsApp messages
-- ----------------------------
DROP TABLE IF EXISTS `wa_logs`;
CREATE TABLE `wa_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `log_date` datetime NOT NULL,
  `log_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0: Pending, 1: Success, 2: Failed',
  PRIMARY KEY (`id`) USING BTREE,
  FOREIGN KEY (`message_id`) REFERENCES `wa_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================
-- SAMPLE DATA INSERTS (Optional)
-- ============================

-- Insert sample permission types
INSERT INTO `jns_izin` (`izin_jenis_name`) VALUES 
('Sakit'),
('Izin'),
('Alpha'),
('Terlambat');

-- Insert sample permission categories
INSERT INTO `kategori_izin` (`kat_izin_name`) VALUES 
('Kesehatan'),
('Keluarga'),
('Pribadi'),
('Lainnya');

-- Insert sample session
INSERT INTO `sessions` (`session`, `status`) VALUES 
('2024-2025', 1);

-- Insert sample classes
INSERT INTO `classes` (`class`) VALUES 
('X'),
('XI'),
('XII');

-- Insert sample sections
INSERT INTO `sections` (`section`) VALUES 
('A'),
('B'),
('C');

-- Insert sample subjects
INSERT INTO `subjects` (`name`, `code`) VALUES 
('Matematika', 'MTK'),
('Bahasa Indonesia', 'BIN'),
('Bahasa Inggris', 'BIG'),
('Fisika', 'FIS'),
('Kimia', 'KIM');

-- Insert sample WhatsApp templates
INSERT INTO `wa_templates` (`template_name`, `template_content`, `template_type`) VALUES 
('Absen Notification', 'Halo {parent_name}, anak Anda {student_name} tidak hadir pada mata pelajaran {subject} hari ini ({date}). Mohon konfirmasi jika ada keperluan mendesak.', 'attendance'),
('Late Notification', 'Halo {parent_name}, anak Anda {student_name} terlambat {late_minutes} menit pada mata pelajaran {subject} hari ini ({date}).', 'attendance'),
('Permission Approved', 'Halo {parent_name}, permohonan izin anak Anda {student_name} untuk tanggal {date} telah disetujui.', 'permission'),
('Permission Rejected', 'Halo {parent_name}, permohonan izin anak Anda {student_name} untuk tanggal {date} tidak dapat disetujui. Alasan: {reason}', 'permission');