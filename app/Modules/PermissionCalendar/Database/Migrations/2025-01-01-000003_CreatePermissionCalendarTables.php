<?php

namespace App\Modules\PermissionCalendar\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissionCalendarTables extends Migration
{
    public function up()
    {
        // Permission types table
        $this->forge->addField([
            'izin_jenis_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'izin_jenis_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
        ]);
        $this->forge->addKey('izin_jenis_id', true);
        $this->forge->createTable('jns_izin', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Permission categories table
        $this->forge->addField([
            'kat_izin_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'kat_izin_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
        ]);
        $this->forge->addKey('kat_izin_id', true);
        $this->forge->createTable('kategori_izin', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Student permissions table
        $this->forge->addField([
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'izin_urutan' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'izin_tgl_pengajuan' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'izin_tgl' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'izin_jenis_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'izin_catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'izin_status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
                'comment' => '0: Rejected, 1: Approved',
            ],
            'kat_izin_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ]);
        $this->forge->addKey(['student_id', 'izin_urutan'], true);
        $this->forge->addForeignKey('student_id', 'students', 'student_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('izin_jenis_id', 'jns_izin', 'izin_jenis_id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('kat_izin_id', 'kategori_izin', 'kat_izin_id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('student_izin', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Holidays table
        $this->forge->addField([
            'libur_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'libur_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'libur_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('libur_id', true);
        $this->forge->createTable('libur', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Academic calendar table
        $this->forge->addField([
            'calender_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'calender_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'calender_status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 1,
                'comment' => '1: Active, 0: Holiday',
            ],
        ]);
        $this->forge->addKey('calender_id', true);
        $this->forge->createTable('calender', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // WhatsApp devices table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'device_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'device_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'device_status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 1,
                'comment' => '1: Active, 0: Inactive',
            ],
            'api_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('wa_devices', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // Update student_attendance table to add missing fields
        $fields = [
            'izin_jenis_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'late_minutes'
            ],
            'notification_sent' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
                'comment' => '0: Not sent, 1: Sent',
                'after' => 'izin_jenis_id'
            ]
        ];
        $this->forge->addColumn('student_attendance', $fields);
        
        // Add foreign key for izin_jenis_id
        $this->forge->addForeignKey('izin_jenis_id', 'jns_izin', 'izin_jenis_id', 'SET NULL', 'SET NULL', 'student_attendance');
    }

    public function down()
    {
        // Drop foreign key and columns from student_attendance
        $this->forge->dropForeignKey('student_attendance', 'student_attendance_izin_jenis_id_foreign');
        $this->forge->dropColumn('student_attendance', ['izin_jenis_id', 'notification_sent']);
        
        $this->forge->dropTable('wa_devices', true);
        $this->forge->dropTable('calender', true);
        $this->forge->dropTable('libur', true);
        $this->forge->dropTable('student_izin', true);
        $this->forge->dropTable('kategori_izin', true);
        $this->forge->dropTable('jns_izin', true);
    }
}