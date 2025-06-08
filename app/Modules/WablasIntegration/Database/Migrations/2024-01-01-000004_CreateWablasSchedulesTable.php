<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasSchedulesTable extends Migration
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
            'schedule_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'External schedule ID from Wablas API',
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'message_type' => [
                'type' => 'ENUM',
                'constraint' => ['text', 'image', 'document', 'video', 'audio', 'location', 'list'],
                'default' => 'text',
            ],
            'message_content' => [
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
            'is_group' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'processing', 'sent', 'failed', 'cancelled'],
                'default' => 'pending',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'retry_count' => [
                'type' => 'TINYINT',
                'constraint' => 2,
                'default' => 0,
            ],
            'max_retries' => [
                'type' => 'TINYINT',
                'constraint' => 2,
                'default' => 3,
            ],
            'template_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'campaign_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'api_response' => [
                'type' => 'JSON',
                'null' => true,
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
        $this->forge->addKey('schedule_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('scheduled_at');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey(['status', 'scheduled_at']);

        // Foreign key constraint
        $this->forge->addForeignKey('device_id', 'wablas_devices', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('wablas_schedules');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_schedules');
    }
}
