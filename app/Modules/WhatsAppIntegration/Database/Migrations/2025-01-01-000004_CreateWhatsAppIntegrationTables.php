<?php

namespace App\Modules\WhatsAppIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsAppIntegrationTables extends Migration
{
    public function up()
    {
        // WhatsApp schedules table
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
                'null' => false,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'schedule_time' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
                'comment' => '0: Pending, 1: Sent, 2: Failed',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'sent_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('device_id', 'wa_devices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wa_schedules', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // WhatsApp auto replies table
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
            'keyword' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'reply_message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 1,
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
        $this->forge->addForeignKey('device_id', 'wa_devices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wa_auto_replies', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // WhatsApp contacts table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'contact_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'contact_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'contact_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'e.g., Parent, Student',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('contact_number');
        $this->forge->addForeignKey('student_id', 'students', 'student_id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('wa_contacts', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // WhatsApp templates table
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
                'null' => false,
            ],
            'template_content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'template_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'e.g., attendance, permission',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('wa_templates', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // WhatsApp messages table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'contact_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'template_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'message_content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'send_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'default' => 0,
                'comment' => '0: Pending, 1: Sent, 2: Failed',
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'timetable_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('contact_id', 'wa_contacts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('template_id', 'wa_templates', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('student_id', 'students', 'student_id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('timetable_id', 'subject_timetable', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('wa_messages', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // WhatsApp logs table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'message_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'log_date' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'log_description' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 4,
                'null' => false,
                'comment' => '0: Pending, 1: Success, 2: Failed',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('message_id', 'wa_messages', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('wa_logs', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('wa_logs', true);
        $this->forge->dropTable('wa_messages', true);
        $this->forge->dropTable('wa_templates', true);
        $this->forge->dropTable('wa_contacts', true);
        $this->forge->dropTable('wa_auto_replies', true);
        $this->forge->dropTable('wa_schedules', true);
    }
}