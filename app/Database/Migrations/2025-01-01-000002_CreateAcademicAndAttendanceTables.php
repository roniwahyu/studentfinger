<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcademicAndAttendanceTables extends Migration
{
    public function up()
    {
        // Subjects table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('subjects', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Subject timetable table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'class_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'section_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'subject_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'day' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'comment' => 'e.g., Monday',
            ],
            'time_from' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'time_to' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'room_no' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'tolerance_late' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 10,
                'comment' => 'Late tolerance in minutes',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('class_id', 'classes', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('section_id', 'sections', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('subject_id', 'subjects', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('subject_timetable', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Device table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'serialnumber' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
            ],
            'device_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => '',
            ],
            'comm_type' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
                'comment' => '0: Ethernet, 1: USB, 2: Serial',
            ],
            'dev_type' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
                'comment' => '0: ZK, 1: Hanvon, 2: Realand',
            ],
            'last_download' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('serialnumber');
        $this->forge->createTable('device', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Attendance log table
        $this->forge->addField([
            'att_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pin' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'scan_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'verifymode' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => '0: Fingerprint, 1: RFID, 2: Face',
            ],
            'status' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => '0: Absent, 1: Present, 2: Late, 3: Permission',
            ],
            'serialnumber' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('att_id', true);
        $this->forge->addForeignKey('student_id', 'students', 'student_id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('att_log', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Student attendance table
        $this->forge->addField([
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'timetable_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'attendance_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'null' => false,
                'comment' => '0: Absent, 1: Present, 2: Late, 3: Permission',
            ],
            'scan_in' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'scan_out' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'late_minutes' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey(['student_id', 'timetable_id', 'attendance_date'], true);
        $this->forge->addForeignKey('student_id', 'students', 'student_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('timetable_id', 'subject_timetable', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('student_attendance', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('student_attendance', true);
        $this->forge->dropTable('att_log', true);
        $this->forge->dropTable('device', true);
        $this->forge->dropTable('subject_timetable', true);
        $this->forge->dropTable('subjects', true);
    }
}