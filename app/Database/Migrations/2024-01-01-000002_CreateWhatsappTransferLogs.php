<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration for WhatsApp Transfer Logs table
 */
class CreateWhatsappTransferLogs extends Migration
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
            'transfer_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'source_table' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'destination_table' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'records_processed' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'records_transferred' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'records_skipped' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'records_failed' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'failed', 'partial', 'running'],
                'default' => 'running',
            ],
            'error_details' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processing_time' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'comment' => 'Processing time in minutes',
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
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
        $this->forge->addKey('transfer_type');
        $this->forge->addKey('status');
        $this->forge->addKey('started_at');
        $this->forge->addKey('completed_at');
        $this->forge->addKey('created_at');
        
        // Composite indexes for common queries
        $this->forge->addKey(['transfer_type', 'status']);
        $this->forge->addKey(['status', 'started_at']);
        $this->forge->addKey(['created_at', 'status']);
        
        $this->forge->createTable('whatsapp_transfer_logs');
    }
    
    public function down()
    {
        $this->forge->dropTable('whatsapp_transfer_logs');
    }
}