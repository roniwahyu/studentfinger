<?php

namespace App\Modules\FingerprintBridge\Config;

return [
    /**
     * Module Information
     */
    'name' => 'FingerprintBridge',
    'version' => '1.0.0',
    'description' => 'Bridge module for importing fingerspot machine attendance data from fin_pro database',
    'author' => 'Student Attendance System',
    'status' => 'active',
    
    /**
     * Module Dependencies
     */
    'dependencies' => ['StudentManagement'],
    
    /**
     * Module Routes Prefix
     */
    'routes_prefix' => 'fingerprint-bridge',
    
    /**
     * Default Controller
     */
    'default_controller' => 'ImportController',
    
    /**
     * Module Permissions
     */
    'permissions' => [
        'fingerprint.import' => 'Import Fingerprint Data',
        'fingerprint.view_logs' => 'View Import Logs',
        'fingerprint.settings' => 'Manage Import Settings',
        'fingerprint.manual_import' => 'Manual Import Data',
        'fingerprint.auto_import' => 'Auto Import Data'
    ],
    
    /**
     * Module Settings
     */
    'settings' => [
        'auto_import_enabled' => false,
        'auto_import_interval' => 300, // seconds (5 minutes)
        'import_batch_size' => 1000,
        'duplicate_handling' => 'skip', // skip, update, error
        'default_status' => 1, // Default status for imported records
        'log_retention_days' => 30,
        'timezone' => 'Asia/Jakarta',
        'verify_student_exists' => true,
        'create_missing_students' => false
    ],
    
    /**
     * Database Tables
     */
    'tables' => [
        'import_logs' => 'fingerprint_import_logs',
        'import_settings' => 'fingerprint_import_settings',
        'student_pin_mapping' => 'student_pin_mapping'
    ],
    
    /**
     * Import Field Mapping
     * Maps fin_pro.att_log fields to studentfinger.att_log fields
     */
    'field_mapping' => [
        'sn' => 'sn',
        'scan_date' => 'scan_date',
        'pin' => 'pin',
        'verifymode' => 'verifymode',
        'inoutmode' => 'inoutmode',
        'reserved' => 'reserved',
        'work_code' => 'work_code',
        'att_id' => 'att_id'
    ],
    
    /**
     * Verify Mode Mapping
     */
    'verify_modes' => [
        1 => 'Fingerprint',
        3 => 'RFID Card',
        20 => 'Face Recognition'
    ],
    
    /**
     * In/Out Mode Mapping
     */
    'inout_modes' => [
        0 => 'Check In',
        1 => 'Check In',
        2 => 'Check Out',
        3 => 'Break Out',
        4 => 'Break In'
    ],
    
    /**
     * Menu Configuration
     */
    'menu' => [
        'title' => 'Import Finger',
        'icon' => 'fas fa-fingerprint',
        'order' => 50,
        'items' => [
            [
                'title' => 'Dashboard',
                'url' => 'fingerprint-bridge',
                'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'title' => 'Manual Import',
                'url' => 'fingerprint-bridge/manual-import',
                'icon' => 'fas fa-upload'
            ],
            [
                'title' => 'Import Logs',
                'url' => 'fingerprint-bridge/logs',
                'icon' => 'fas fa-list'
            ],
            [
                'title' => 'Settings',
                'url' => 'fingerprint-bridge/settings',
                'icon' => 'fas fa-cog'
            ]
        ]
    ],
    
    /**
     * API Endpoints
     */
    'api_endpoints' => [
        'import' => [
            'manual' => 'POST /api/fingerprint-bridge/import',
            'status' => 'GET /api/fingerprint-bridge/status',
            'logs' => 'GET /api/fingerprint-bridge/logs'
        ]
    ]
];
