<?php

namespace App\Modules\FingerprintBridge\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create Fingerprint Bridge Tables Migration
 * 
 * Creates tables required for fingerprint import functionality
 */
class CreateFingerprintBridgeTables extends Migration
{
    public function up()
    {
        // Create fingerprint_import_logs table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'import_type' => [
                'type' => 'ENUM',
                'constraint' => ['manual', 'auto', 'scheduled'],
                'default' => 'manual',
            ],
            'start_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'running', 'completed', 'failed', 'cancelled'],
                'default' => 'pending',
            ],
            'total_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'processed_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'inserted_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'updated_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'skipped_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'error_records' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'start_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'end_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'duration' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Duration in seconds',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'settings' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'user_id' => [
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
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->addKey('import_type');
        $this->forge->createTable('fingerprint_import_logs');
        
        // Create student_pin_mapping table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'pin' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'comment' => 'PIN from fingerprint machine',
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'Reference to students.id',
            ],
            'rfid_card' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'RFID card number if available',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1=Active, 0=Inactive',
            ],
            'notes' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey('pin');
        $this->forge->addUniqueKey('student_id');
        $this->forge->addKey('is_active');
        $this->forge->addKey('deleted_at');
        $this->forge->createTable('student_pin_mapping');
        
        // Create fingerprint_import_settings table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'setting_type' => [
                'type' => 'ENUM',
                'constraint' => ['string', 'integer', 'boolean', 'json'],
                'default' => 'string',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'is_system' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1=System setting, 0=User setting',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->addKey('is_system');
        $this->forge->createTable('fingerprint_import_settings');
        
        // Insert default settings
        $this->insertDefaultSettings();
    }
    
    public function down()
    {
        $this->forge->dropTable('fingerprint_import_settings', true);
        $this->forge->dropTable('student_pin_mapping', true);
        $this->forge->dropTable('fingerprint_import_logs', true);
    }
    
    /**
     * Insert default settings
     */
    private function insertDefaultSettings()
    {
        $defaultSettings = [
            [
                'setting_key' => 'auto_import_enabled',
                'setting_value' => '0',
                'setting_type' => 'boolean',
                'description' => 'Enable automatic import from fingerprint machine',
                'is_system' => 1
            ],
            [
                'setting_key' => 'auto_import_interval',
                'setting_value' => '300',
                'setting_type' => 'integer',
                'description' => 'Auto import interval in seconds (default: 5 minutes)',
                'is_system' => 1
            ],
            [
                'setting_key' => 'import_batch_size',
                'setting_value' => '1000',
                'setting_type' => 'integer',
                'description' => 'Number of records to process in each batch',
                'is_system' => 1
            ],
            [
                'setting_key' => 'duplicate_handling',
                'setting_value' => 'skip',
                'setting_type' => 'string',
                'description' => 'How to handle duplicate records (skip, update, error)',
                'is_system' => 1
            ],
            [
                'setting_key' => 'default_status',
                'setting_value' => '1',
                'setting_type' => 'integer',
                'description' => 'Default status for imported attendance records',
                'is_system' => 1
            ],
            [
                'setting_key' => 'log_retention_days',
                'setting_value' => '30',
                'setting_type' => 'integer',
                'description' => 'Number of days to keep import logs',
                'is_system' => 1
            ],
            [
                'setting_key' => 'verify_student_exists',
                'setting_value' => '1',
                'setting_type' => 'boolean',
                'description' => 'Verify that student exists before importing attendance',
                'is_system' => 1
            ],
            [
                'setting_key' => 'create_missing_students',
                'setting_value' => '0',
                'setting_type' => 'boolean',
                'description' => 'Automatically create missing students during import',
                'is_system' => 1
            ]
        ];
        
        $builder = $this->db->table('fingerprint_import_settings');
        
        foreach ($defaultSettings as $setting) {
            $setting['created_at'] = date('Y-m-d H:i:s');
            $setting['updated_at'] = date('Y-m-d H:i:s');
            $builder->insert($setting);
        }
    }
}
