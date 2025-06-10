<?php

namespace App\Modules\AttendanceNotification\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        // Create settings table if it doesn't exist
        if (!$this->db->tableExists('settings'))
        {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                ],
                'value' => [
                    'type' => 'TEXT',
                ],
                'description' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('key');
            $this->forge->createTable('settings', true);

            // Insert initial last sync time
            $this->db->table('settings')->insert([
                'key' => 'last_attendance_sync',
                'value' => date('Y-m-d 00:00:00'),
                'description' => 'Last time attendance records were synced from fin_pro',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function down()
    {
        // Not dropping the settings table since other modules might use it
    }
}
