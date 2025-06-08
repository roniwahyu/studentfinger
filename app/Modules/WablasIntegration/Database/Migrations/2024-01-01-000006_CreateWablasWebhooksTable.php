<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasWebhooksTable extends Migration
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
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'endpoint' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['incoming', 'status', 'device'],
                'null' => false,
            ],
            'method' => [
                'type' => 'ENUM',
                'constraint' => ['GET', 'POST', 'PUT', 'DELETE'],
                'default' => 'POST',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'secret_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'headers' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional headers to send',
            ],
            'timeout' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 30,
            ],
            'retry_attempts' => [
                'type' => 'TINYINT',
                'constraint' => 2,
                'default' => 3,
            ],
            'retry_delay' => [
                'type' => 'INT',
                'constraint' => 5,
                'default' => 5,
                'comment' => 'Delay in seconds between retries',
            ],
            'last_called_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_response_code' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
            ],
            'last_response_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'success_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'failure_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addUniqueKey('endpoint');
        $this->forge->addKey('type');
        $this->forge->addKey('is_active');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('wablas_webhooks');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_webhooks');
    }
}
