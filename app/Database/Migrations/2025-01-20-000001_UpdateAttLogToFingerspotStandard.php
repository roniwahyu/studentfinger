<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAttLogToFingerspotStandard extends Migration
{
    public function up()
    {
        // Create backup table first
        $this->db->query("CREATE TABLE IF NOT EXISTS att_log_backup AS SELECT * FROM att_log");
        
        // Check if columns exist before adding them
        $fields = $this->db->getFieldData('att_log');
        $existingColumns = array_column($fields, 'name');
        
        // Add missing columns if they don't exist
        if (!in_array('sn', $existingColumns)) {
            $this->forge->addColumn('att_log', [
                'sn' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => false,
                    'default' => '',
                    'comment' => 'Serial number of device'
                ]
            ]);
        }
        
        if (!in_array('inoutmode', $existingColumns)) {
            $this->forge->addColumn('att_log', [
                'inoutmode' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'default' => 0,
                    'comment' => '0=Check In, 1=Check In, 2=Check Out, 3=Break Out, 4=Break In'
                ]
            ]);
        }
        
        if (!in_array('reserved', $existingColumns)) {
            $this->forge->addColumn('att_log', [
                'reserved' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Reserved field for future use'
                ]
            ]);
        }
        
        if (!in_array('work_code', $existingColumns)) {
            $this->forge->addColumn('att_log', [
                'work_code' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                    'default' => 0,
                    'comment' => 'Work code for different work types'
                ]
            ]);
        }
        
        // Modify existing columns to match FingerSpot standard
        $this->forge->modifyColumn('att_log', [
            'pin' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => false,
                'comment' => 'Employee PIN/ID'
            ],
            'verifymode' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'comment' => '1=Fingerprint, 3=RFID Card, 20=Face Recognition'
            ],
            'att_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'default' => '0',
                'comment' => 'Attendance ID from device'
            ]
        ]);
        
        // Update existing records with default values
        $this->updateExistingRecords();
        
        // Create indexes for better performance
        $this->createIndexes();
        
        // Create attendance mode mapping table
        $this->createAttendanceModeMapping();
    }
    
    public function down()
    {
        // Remove added columns
        $this->forge->dropColumn('att_log', ['sn', 'inoutmode', 'reserved', 'work_code']);
        
        // Drop created tables
        $this->forge->dropTable('attendance_mode_mapping', true);
        
        // Restore from backup if needed
        // Note: This is a simplified rollback - in production you might want more sophisticated rollback
    }
    
    private function updateExistingRecords()
    {
        // Check if serialnumber column exists and update sn from it
        $fields = $this->db->getFieldData('att_log');
        $existingColumns = array_column($fields, 'name');
        
        if (in_array('serialnumber', $existingColumns)) {
            $this->db->query("UPDATE att_log SET sn = COALESCE(serialnumber, 'DEFAULT_DEVICE') WHERE sn = '' OR sn IS NULL");
        } else {
            $this->db->query("UPDATE att_log SET sn = 'DEFAULT_DEVICE' WHERE sn = '' OR sn IS NULL");
        }
        
        // Update inoutmode from status if it exists
        if (in_array('status', $existingColumns)) {
            $this->db->query("UPDATE att_log SET inoutmode = COALESCE(status, 1) WHERE inoutmode = 0");
        }
        
        // Set default values for new fields
        $this->db->query("UPDATE att_log SET reserved = 0, work_code = 0 WHERE reserved IS NULL OR work_code IS NULL");
    }
    
    private function createIndexes()
    {
        // Create indexes for better performance
        try {
            $this->db->query("CREATE INDEX idx_att_log_pin ON att_log (pin)");
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        try {
            $this->db->query("CREATE INDEX idx_att_log_sn ON att_log (sn)");
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        try {
            $this->db->query("CREATE INDEX idx_att_log_scan_date ON att_log (scan_date)");
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        try {
            $this->db->query("CREATE INDEX idx_att_log_verifymode ON att_log (verifymode)");
        } catch (\Exception $e) {
            // Index might already exist
        }
        
        try {
            $this->db->query("CREATE INDEX idx_att_log_inoutmode ON att_log (inoutmode)");
        } catch (\Exception $e) {
            // Index might already exist
        }
    }
    
    private function createAttendanceModeMapping()
    {
        // Create attendance mode mapping table
        $this->forge->addField([
            'inoutmode' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false
            ],
            'status_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP'
            ]
        ]);
        
        $this->forge->addKey('inoutmode', true);
        $this->forge->createTable('attendance_mode_mapping', true);
        
        // Insert standard FingerSpot mode mappings
        $data = [
            ['inoutmode' => 0, 'status_name' => 'Check In', 'description' => 'Employee check in'],
            ['inoutmode' => 1, 'status_name' => 'Check In', 'description' => 'Employee check in (alternative)'],
            ['inoutmode' => 2, 'status_name' => 'Check Out', 'description' => 'Employee check out'],
            ['inoutmode' => 3, 'status_name' => 'Break Out', 'description' => 'Employee going for break'],
            ['inoutmode' => 4, 'status_name' => 'Break In', 'description' => 'Employee returning from break'],
            ['inoutmode' => 5, 'status_name' => 'Overtime In', 'description' => 'Employee starting overtime'],
            ['inoutmode' => 6, 'status_name' => 'Overtime Out', 'description' => 'Employee ending overtime']
        ];
        
        $this->db->table('attendance_mode_mapping')->insertBatch($data);
    }
}
