<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasDevicesTable extends Migration
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
            'device_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'device_serial' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'token' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'secret_key' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'api_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => 'https://wablas.com',
            ],
            'webhook_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tracking_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'device_type' => [
                'type' => 'ENUM',
                'constraint' => ['wablas', 'whatsapp-web', 'baileys'],
                'default' => 'wablas',
            ],
            'device_status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '0=Inactive, 1=Active, 2=Suspended',
            ],
            'connection_status' => [
                'type' => 'ENUM',
                'constraint' => ['connected', 'disconnected', 'connecting', 'error'],
                'default' => 'disconnected',
            ],
            'last_seen' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'quota_limit' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1000,
            ],
            'quota_used' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'quota_reset_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'expired_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'delay_seconds' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 10,
                'comment' => 'Delay between messages in seconds (10-120)',
            ],
            'max_retries' => [
                'type' => 'TINYINT',
                'constraint' => 2,
                'default' => 3,
            ],
            'auto_reply_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'incoming_webhook_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'status_webhook_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'device_webhook_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'settings' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional device settings in JSON format',
            ],
            'notes' => [
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
        $this->forge->addKey('device_serial');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('device_status');
        $this->forge->addKey('connection_status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('wablas_devices');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_devices');
    }
}
