<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasLogsTable extends Migration
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
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'message_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'log_type' => [
                'type' => 'ENUM',
                'constraint' => ['api_call', 'webhook', 'error', 'info', 'warning', 'debug'],
                'default' => 'info',
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'request_data' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Request data sent to API',
            ],
            'response_data' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Response data from API',
            ],
            'http_status_code' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
            ],
            'execution_time' => [
                'type' => 'DECIMAL',
                'constraint' => '8,3',
                'null' => true,
                'comment' => 'Execution time in seconds',
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
            'error_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'stack_trace' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'context' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional context data',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('device_id');
        $this->forge->addKey('message_id');
        $this->forge->addKey('log_type');
        $this->forge->addKey('action');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['log_type', 'created_at']);
        $this->forge->addKey(['device_id', 'created_at']);

        // Foreign key constraints
        $this->forge->addForeignKey('device_id', 'wablas_devices', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('message_id', 'wablas_messages', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('wablas_logs');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_logs');
    }
}
