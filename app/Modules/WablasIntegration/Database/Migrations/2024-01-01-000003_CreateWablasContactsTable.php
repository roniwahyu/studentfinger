<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasContactsTable extends Migration
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
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'nickname' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'birthday' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'gender' => [
                'type' => 'ENUM',
                'constraint' => ['male', 'female', 'other'],
                'null' => true,
            ],
            'profile_image' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive', 'blocked'],
                'default' => 'active',
            ],
            'is_whatsapp_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'last_seen' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_message_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'message_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'group_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tags' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Contact tags for categorization',
            ],
            'custom_fields' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional custom fields',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'How contact was added (manual, import, webhook, etc.)',
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
        $this->forge->addUniqueKey('phone_number');
        $this->forge->addKey('name');
        $this->forge->addKey('email');
        $this->forge->addKey('status');
        $this->forge->addKey('group_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('wablas_contacts');
    }

    public function down()
    {
        $this->forge->dropTable('wablas_contacts');
    }
}
