<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasTemplatesTable extends Migration
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
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'message_type' => [
                'type' => 'ENUM',
                'constraint' => ['text', 'image', 'document', 'video', 'audio', 'location', 'list', 'button', 'template'],
                'default' => 'text',
            ],
            'content' => [
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
            'variables' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Template variables like {name}, {phone}, etc.',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
            'tags' => [
                'type' => 'JSON',
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
        $this->forge->addKey('category');
        $this->forge->addKey('message_type');
        $this->forge->addKey('is_active');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('wablas_templates');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_templates');
    }
}
