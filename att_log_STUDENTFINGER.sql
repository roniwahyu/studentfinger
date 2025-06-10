/*
 Navicat Premium Data Transfer

 Source Server         : LOCALHOST
 Source Server Type    : MySQL
 Source Server Version : 50742 (5.7.42)
 Source Host           : localhost:3306
 Source Schema         : studentfinger

 Target Server Type    : MySQL
 Target Server Version : 50742 (5.7.42)
 File Encoding         : 65001

 Date: 09/06/2025 12:06:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for att_log
-- ----------------------------
DROP TABLE IF EXISTS `att_log`;
CREATE TABLE `att_log`  (
  `att_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT 'Attendance ID from device',
  `pin` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Employee PIN/ID',
  `scan_date` datetime NOT NULL,
  `verifymode` int(11) NOT NULL COMMENT '1=Fingerprint, 3=RFID Card, 20=Face Recognition',
  `status` int(11) NULL DEFAULT NULL COMMENT '0: Absent, 1: Present, 2: Late, 3: Permission',
  `serialnumber` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `student_id` int(11) UNSIGNED NULL DEFAULT NULL,
  `created_at` datetime NULL DEFAULT NULL,
  `updated_at` datetime NULL DEFAULT NULL,
  `deleted_at` datetime NULL DEFAULT NULL,
  `sn` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'Serial number of device',
  `inoutmode` int(11) NOT NULL DEFAULT 0 COMMENT '0=Check In, 1=Check In, 2=Check Out, 3=Break Out, 4=Break In',
  `reserved` int(11) NOT NULL DEFAULT 0 COMMENT 'Reserved field for future use',
  `work_code` int(11) NOT NULL DEFAULT 0 COMMENT 'Work code for different work types',
  PRIMARY KEY (`att_id`) USING BTREE,
  INDEX `att_log_student_id_foreign`(`student_id`) USING BTREE,
  INDEX `idx_pin`(`pin`) USING BTREE,
  INDEX `idx_sn`(`sn`) USING BTREE,
  INDEX `idx_scan_date`(`scan_date`) USING BTREE,
  INDEX `idx_verifymode`(`verifymode`) USING BTREE,
  INDEX `idx_inoutmode`(`inoutmode`) USING BTREE,
  CONSTRAINT `att_log_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
