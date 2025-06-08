<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasAutoRepliesTable extends Migration
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
            'autoreply_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'External auto reply ID from Wablas API',
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'keyword' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'response_type' => [
                'type' => 'ENUM',
                'constraint' => ['text', 'image', 'document', 'video', 'audio', 'template'],
                'default' => 'text',
            ],
            'response_content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'media_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'media_caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_exact_match' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=Contains keyword, 1=Exact match',
            ],
            'is_case_sensitive' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'priority' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 100,
                'comment' => 'Lower number = higher priority',
            ],
            'usage_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'business_hours_only' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'business_hours_start' => [
                'type' => 'TIME',
                'null' => true,
                'default' => '09:00:00',
            ],
            'business_hours_end' => [
                'type' => 'TIME',
                'null' => true,
                'default' => '17:00:00',
            ],
            'allowed_days' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Days of week when auto reply is active (1=Monday, 7=Sunday)',
            ],
            'conditions' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional conditions for triggering auto reply',
            ],
            'variables' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Variables that can be used in response',
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
        $this->forge->addKey('autoreply_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('keyword');
        $this->forge->addKey('is_active');
        $this->forge->addKey('priority');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey(['device_id', 'is_active', 'priority']);

        // Foreign key constraint
        $this->forge->addForeignKey('device_id', 'wablas_devices', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('wablas_auto_replies');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_auto_replies');
    }
}
