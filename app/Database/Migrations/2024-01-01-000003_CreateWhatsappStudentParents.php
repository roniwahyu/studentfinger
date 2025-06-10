<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration for WhatsApp Student Parents mapping table
 */
class CreateWhatsappStudentParents extends Migration
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
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'parent_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'parent_type' => [
                'type' => 'ENUM',
                'constraint' => ['father', 'mother', 'guardian', 'emergency'],
                'default' => 'father',
            ],
            'phone_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'relationship' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'is_primary' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 = Primary contact, 0 = Secondary contact',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
            ],
            'notes' => [
                'type' => 'TEXT',
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
        $this->forge->addKey('student_id');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('parent_type');
        $this->forge->addKey('status');
        $this->forge->addKey('is_primary');
        $this->forge->addKey('created_at');
        $this->forge->addKey('deleted_at');
        
        // Composite indexes for common queries
        $this->forge->addKey(['student_id', 'status']);
        $this->forge->addKey(['student_id', 'is_primary']);
        $this->forge->addKey(['phone_number', 'status']);
        $this->forge->addKey(['parent_type', 'status']);
        
        // Unique constraint to prevent duplicate mappings
        $this->forge->addUniqueKey(['student_id', 'phone_number', 'parent_type'], 'unique_student_phone_type');
        
        $this->forge->createTable('whatsapp_student_parents');
    }
    
    public function down()
    {
        $this->forge->dropTable('whatsapp_student_parents');
    }
}