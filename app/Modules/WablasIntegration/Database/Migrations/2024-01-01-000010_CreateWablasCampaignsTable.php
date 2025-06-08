<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasCampaignsTable extends Migration
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
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'template_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'target_type' => [
                'type' => 'ENUM',
                'constraint' => ['all_contacts', 'group', 'custom_list'],
                'default' => 'all_contacts',
            ],
            'target_group_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'target_contacts' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Array of contact IDs for custom list',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'scheduled', 'running', 'paused', 'completed', 'cancelled', 'failed'],
                'default' => 'draft',
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'total_recipients' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'sent_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'delivered_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'read_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'failed_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'delay_between_messages' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 1,
                'comment' => 'Delay in seconds between messages',
            ],
            'settings' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Campaign settings and options',
            ],
            'error_log' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('name');
        $this->forge->addKey('device_id');
        $this->forge->addKey('status');
        $this->forge->addKey('scheduled_at');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        // Foreign key constraints
        $this->forge->addForeignKey('device_id', 'wablas_devices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('template_id', 'wablas_templates', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('target_group_id', 'wablas_groups', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('wablas_campaigns');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_campaigns');
    }
}
