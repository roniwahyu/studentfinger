<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration for WhatsApp Notification Logs table
 */
class CreateWhatsappNotificationLogs extends Migration
{
    public function up()
    {
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
            ],
            'parent_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => ['entry', 'exit', 'late', 'absent', 'test'],
                'default' => 'entry',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'scan_date' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['sent', 'failed', 'pending', 'retry'],
                'default' => 'pending',
            ],
            'wablas_response' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'retry_count' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
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
        $this->forge->addKey('student_id');
        $this->forge->addKey('parent_phone');
        $this->forge->addKey('notification_type');
        $this->forge->addKey('status');
        $this->forge->addKey('scan_date');
        $this->forge->addKey('created_at');
        
        // Composite indexes for common queries
        $this->forge->addKey(['student_id', 'scan_date']);
        $this->forge->addKey(['parent_phone', 'notification_type']);
        $this->forge->addKey(['status', 'created_at']);
        
        $this->forge->createTable('whatsapp_notification_logs');
    }
    
    public function down()
    {
        $this->forge->dropTable('whatsapp_notification_logs');
    }
}