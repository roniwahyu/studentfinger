<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'sent_at' => [
                'type' => 'DATETIME'
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true
            ]
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('student_id', 'students', 'student_id');
        $this->forge->createTable('notification_logs');
    }

    public function down()
    {
        $this->forge->dropTable('notification_logs');
    }
}
