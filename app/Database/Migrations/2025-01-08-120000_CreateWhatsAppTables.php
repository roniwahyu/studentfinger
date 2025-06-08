<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsAppTables extends Migration
{
    public function up()
    {
        // Create wa_devices table
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
            ],
            'device_token' => [
                'type' => 'TEXT',
            ],
            'device_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '0=Inactive, 1=Active',
            ],
            'api_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'device_type' => [
                'type' => 'ENUM',
                'constraint' => ['wablas', 'whatsapp-web', 'baileys', 'other'],
                'default' => 'wablas',
            ],
            'webhook_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'last_activity' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'settings' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_status');
        $this->forge->addKey('device_type');
        $this->forge->createTable('wa_devices');

        // Create wa_messages table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=Pending, 1=Sent, 2=Failed, 3=Received',
            ],
            'api_response' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_id');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('device_id', 'wa_devices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wa_messages');

        // Create wa_schedules table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'schedule_time' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=Pending, 1=Sent, 2=Failed, 3=Cancelled',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_id');
        $this->forge->addKey('schedule_time');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('device_id', 'wa_devices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wa_schedules');

        // Create wa_contacts table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'student_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'contact_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'contact_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'contact_type' => [
                'type' => 'ENUM',
                'constraint' => ['Parent', 'Student', 'Guardian', 'Teacher', 'Staff'],
                'default' => 'Parent',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('student_id');
        $this->forge->addKey('contact_type');
        $this->forge->addUniqueKey('contact_number');
        $this->forge->createTable('wa_contacts');

        // Create wa_templates table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'template_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'template_content' => [
                'type' => 'TEXT',
            ],
            'template_type' => [
                'type' => 'ENUM',
                'constraint' => ['attendance', 'notification', 'reminder', 'general'],
                'default' => 'general',
            ],
            'variables' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('template_type');
        $this->forge->addKey('is_active');
        $this->forge->createTable('wa_templates');

        // Create wa_logs table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('device_id');
        $this->forge->addKey('action');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('device_id', 'wa_devices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wa_logs');

        // Create wa_settings table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('wa_settings');

        // Create wa_attendance_logs table for attendance integration
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'student_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => ['checkin', 'checkout', 'absent', 'late'],
            ],
            'attendance_time' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['sent', 'failed', 'partial'],
                'default' => 'sent',
            ],
            'results' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('student_id');
        $this->forge->addKey('notification_type');
        $this->forge->addKey('created_at');
        $this->forge->createTable('wa_attendance_logs');
    }

    public function down()
    {
        $this->forge->dropTable('wa_attendance_logs', true);
        $this->forge->dropTable('wa_settings', true);
        $this->forge->dropTable('wa_logs', true);
        $this->forge->dropTable('wa_templates', true);
        $this->forge->dropTable('wa_contacts', true);
        $this->forge->dropTable('wa_schedules', true);
        $this->forge->dropTable('wa_messages', true);
        $this->forge->dropTable('wa_devices', true);
    }
}
