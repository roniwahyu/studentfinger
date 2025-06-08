<?php

namespace App\Modules\WablasIntegration\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWablasGroupsTable extends Migration
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
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
                'default' => '#007bff',
                'comment' => 'Hex color code for group identification',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'contact_count' => [
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
        $this->forge->addKey('name');
        $this->forge->addKey('is_active');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('wablas_groups');

        // Add foreign key to contacts table
        $this->forge->addColumn('wablas_contacts', [
            'group_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'message_count'
            ]
        ]);

        $this->forge->addForeignKey('group_id', 'wablas_groups', 'id', 'SET NULL', 'CASCADE', 'wablas_contacts');
    }

    public function down()
    {
        // Remove foreign key and column from contacts table
        $this->forge->dropForeignKey('wablas_contacts', 'wablas_contacts_group_id_foreign');
        $this->forge->dropColumn('wablas_contacts', 'group_id');
        
        $this->forge->dropTable('wablas_groups');
    }
}
