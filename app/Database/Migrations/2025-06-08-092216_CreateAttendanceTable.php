<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceTable extends Migration
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
            'date' => [
                'type' => 'DATE',
            ],
            'time_in' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'time_out' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Present', 'Absent', 'Late', 'Excused'],
                'default' => 'Present',
            ],
            'device_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'method' => [
                'type' => 'ENUM',
                'constraint' => ['Fingerprint', 'Manual', 'Card', 'Face'],
                'default' => 'Manual',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'marked_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
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
        $this->forge->addKey('date');
        $this->forge->addKey('status');
        $this->forge->addKey('device_id');
        $this->forge->addKey('deleted_at');
        $this->forge->addUniqueKey(['student_id', 'date']);
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('device_id', 'devices', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('attendance');
    }

    public function down()
    {
        $this->forge->dropTable('attendance');
    }
}
