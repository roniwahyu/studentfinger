<?php

namespace App\Modules\FingerprintBridge;

use CodeIgniter\Modules\ModuleInterface;

/**
 * FingerprintBridge Module
 * 
 * Handles importing attendance data from fingerspot machines (fin_pro database)
 * to the studentfinger application database
 */
class FingerprintBridgeModule implements ModuleInterface
{
    /**
     * Module name
     */
    public function getName(): string
    {
        return 'FingerprintBridge';
    }

    /**
     * Module version
     */
    public function getVersion(): string
    {
        return '1.0.0';
    }

    /**
     * Module description
     */
    public function getDescription(): string
    {
        return 'Bridge module for importing fingerspot machine attendance data';
    }

    /**
     * Module dependencies
     */
    public function getDependencies(): array
    {
        return ['StudentManagement'];
    }

    /**
     * Initialize module
     */
    public function init(): void
    {
        // Module initialization logic
    }

    /**
     * Get module routes
     */
    public function getRoutes(): array
    {
        return [
            'fingerprint-bridge' => 'FingerprintBridge\Controllers\ImportController::index',
            'fingerprint-bridge/import' => 'FingerprintBridge\Controllers\ImportController::import',
            'fingerprint-bridge/manual-import' => 'FingerprintBridge\Controllers\ImportController::manualImport',
            'fingerprint-bridge/logs' => 'FingerprintBridge\Controllers\ImportController::logs',
            'fingerprint-bridge/settings' => 'FingerprintBridge\Controllers\ImportController::settings',
        ];
    }

    /**
     * Get module permissions
     */
    public function getPermissions(): array
    {
        return [
            'fingerprint.import' => 'Import Fingerprint Data',
            'fingerprint.view_logs' => 'View Import Logs',
            'fingerprint.settings' => 'Manage Import Settings',
        ];
    }

    /**
     * Get module menu items
     */
    public function getMenuItems(): array
    {
        return [
            [
                'title' => 'Import Finger',
                'url' => 'fingerprint-bridge',
                'icon' => 'fas fa-fingerprint',
                'permission' => 'fingerprint.import',
                'order' => 50,
                'submenu' => [
                    [
                        'title' => 'Manual Import',
                        'url' => 'fingerprint-bridge/manual-import',
                        'icon' => 'fas fa-upload',
                        'permission' => 'fingerprint.import'
                    ],
                    [
                        'title' => 'Import Logs',
                        'url' => 'fingerprint-bridge/logs',
                        'icon' => 'fas fa-list',
                        'permission' => 'fingerprint.view_logs'
                    ],
                    [
                        'title' => 'Settings',
                        'url' => 'fingerprint-bridge/settings',
                        'icon' => 'fas fa-cog',
                        'permission' => 'fingerprint.settings'
                    ]
                ]
            ]
        ];
    }
}
