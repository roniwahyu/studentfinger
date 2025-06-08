<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasMessagesTable extends Migration
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
            'message_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'External message ID from Wablas API',
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
            'contact_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'message_type' => [
                'type' => 'ENUM',
                'constraint' => ['text', 'image', 'document', 'video', 'audio', 'location', 'list', 'button', 'template'],
                'default' => 'text',
            ],
            'message_content' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'media_url' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'URL for media files (image, document, video, audio)',
            ],
            'media_caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'media_filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'media_size' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'File size in bytes',
            ],
            'media_mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'is_group' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'group_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'group_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'direction' => [
                'type' => 'ENUM',
                'constraint' => ['outgoing', 'incoming'],
                'default' => 'outgoing',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'delivered', 'read', 'failed', 'cancelled'],
                'default' => 'pending',
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
            'scheduled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'api_response' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Full API response from Wablas',
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
            'ref_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Reference ID from client/sender',
            ],
            'priority' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=Normal, 1=High priority',
            ],
            'is_secret' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Delete after sending if true',
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Message source (blog, wordpress, fb, ig, etc.)',
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional message metadata',
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
        $this->forge->addKey('message_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('message_type');
        $this->forge->addKey('direction');
        $this->forge->addKey('status');
        $this->forge->addKey('scheduled_at');
        $this->forge->addKey('sent_at');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');
        $this->forge->addKey(['device_id', 'status']);
        $this->forge->addKey(['phone_number', 'direction']);

        // Foreign key constraint
        $this->forge->addForeignKey('device_id', 'wablas_devices', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('wablas_messages');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_messages');
    }
}
