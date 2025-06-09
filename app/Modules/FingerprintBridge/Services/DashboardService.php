<?php

namespace App\Modules\FingerprintBridge\Services;

use App\Modules\FingerprintBridge\Services\FingerprintImportService;
use App\Modules\FingerprintBridge\Models\ImportLogModel;

/**
 * Dashboard Service for FingerprintBridge
 * 
 * Provides data for dashboard widgets and main dashboard integration
 */
class DashboardService
{
    protected $importService;
    protected $importLogModel;
    
    public function __construct()
    {
        $this->importService = new FingerprintImportService();
        $this->importLogModel = new ImportLogModel();
    }
    
    /**
     * Get fingerprint statistics for dashboard widget
     */
    public function getDashboardStats(): array
    {
        try {
            // Test connection first
            $connectionTest = $this->importService->testFinProConnection();
            
            // Get basic statistics
            $stats = $this->importService->getImportStats();
            
            // Get recent imports
            $recentImports = $this->importLogModel->getImportLogs(5, 0, []);
            
            // Get running imports
            $runningImports = $this->importLogModel->getRunningImports();
            
            return [
                'connection' => $connectionTest,
                'fin_pro' => $stats['fin_pro'] ?? [],
                'student_finger' => $stats['student_finger'] ?? [],
                'pin_mapping' => $stats['pin_mapping'] ?? [],
                'import_logs' => $stats['import_logs'] ?? [],
                'recent_imports' => $recentImports,
                'running_imports' => $runningImports,
                'last_updated' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            return [
                'connection' => [
                    'success' => false,
                    'message' => 'Module not available: ' . $e->getMessage()
                ],
                'fin_pro' => ['total_records' => 0, 'unique_pins' => 0],
                'student_finger' => ['total_records' => 0],
                'pin_mapping' => ['active_mappings' => 0],
                'import_logs' => ['total_imports' => 0],
                'recent_imports' => [],
                'running_imports' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get summary statistics for main dashboard
     */
    public function getSummaryStats(): array
    {
        try {
            $stats = $this->importService->getImportStats();
            
            return [
                'total_fingerprint_records' => $stats['student_finger']['total_records'] ?? 0,
                'total_pin_mappings' => $stats['pin_mapping']['active_mappings'] ?? 0,
                'unmapped_pins' => $stats['pin_mapping']['unmapped_pins'] ?? 0,
                'total_imports' => $stats['import_logs']['total_imports'] ?? 0,
                'successful_imports' => $stats['import_logs']['successful_imports'] ?? 0,
                'failed_imports' => $stats['import_logs']['failed_imports'] ?? 0,
                'running_imports' => count($this->importLogModel->getRunningImports()),
                'connection_status' => $this->importService->testFinProConnection()['success'] ?? false
            ];
        } catch (\Exception $e) {
            return [
                'total_fingerprint_records' => 0,
                'total_pin_mappings' => 0,
                'unmapped_pins' => 0,
                'total_imports' => 0,
                'successful_imports' => 0,
                'failed_imports' => 0,
                'running_imports' => 0,
                'connection_status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get alerts and notifications for dashboard
     */
    public function getDashboardAlerts(): array
    {
        $alerts = [];
        
        try {
            // Check connection status
            $connectionTest = $this->importService->testFinProConnection();
            if (!$connectionTest['success']) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'FinPro Database Connection',
                    'message' => 'Cannot connect to FinPro database. Check configuration.',
                    'action_url' => base_url('fingerprint-bridge/settings'),
                    'action_text' => 'Configure'
                ];
            }
            
            // Check for unmapped PINs
            $stats = $this->importService->getImportStats();
            $unmappedPins = $stats['pin_mapping']['unmapped_pins'] ?? 0;
            if ($unmappedPins > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Unmapped PINs',
                    'message' => "{$unmappedPins} fingerprint PINs are not mapped to students.",
                    'action_url' => base_url('fingerprint-bridge/pin-mapping'),
                    'action_text' => 'Map PINs'
                ];
            }
            
            // Check for failed imports
            $failedImports = $stats['import_logs']['failed_imports'] ?? 0;
            if ($failedImports > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Failed Imports',
                    'message' => "{$failedImports} import(s) have failed. Check logs for details.",
                    'action_url' => base_url('fingerprint-bridge/logs?status=failed'),
                    'action_text' => 'View Logs'
                ];
            }
            
            // Check for running imports
            $runningImports = $this->importLogModel->getRunningImports();
            if (!empty($runningImports)) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Import in Progress',
                    'message' => count($runningImports) . " import(s) currently running.",
                    'action_url' => base_url('fingerprint-bridge/logs'),
                    'action_text' => 'Monitor'
                ];
            }
            
        } catch (\Exception $e) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'FingerprintBridge Error',
                'message' => 'Module error: ' . $e->getMessage(),
                'action_url' => base_url('fingerprint-bridge'),
                'action_text' => 'Check Module'
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Get menu items for main navigation
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
                'badge' => $this->getMenuBadge(),
                'submenu' => [
                    [
                        'title' => 'Dashboard',
                        'url' => 'fingerprint-bridge',
                        'icon' => 'fas fa-tachometer-alt',
                        'permission' => 'fingerprint.import'
                    ],
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
                        'title' => 'PIN Mapping',
                        'url' => 'fingerprint-bridge/pin-mapping',
                        'icon' => 'fas fa-link',
                        'permission' => 'fingerprint.import'
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
    
    /**
     * Get badge for menu item
     */
    protected function getMenuBadge(): ?array
    {
        try {
            $runningImports = $this->importLogModel->getRunningImports();
            if (!empty($runningImports)) {
                return [
                    'text' => count($runningImports),
                    'class' => 'badge-warning',
                    'title' => 'Running imports'
                ];
            }
            
            $stats = $this->importService->getImportStats();
            $unmappedPins = $stats['pin_mapping']['unmapped_pins'] ?? 0;
            if ($unmappedPins > 0) {
                return [
                    'text' => $unmappedPins,
                    'class' => 'badge-info',
                    'title' => 'Unmapped PINs'
                ];
            }
            
        } catch (\Exception $e) {
            // Ignore errors for badge
        }
        
        return null;
    }
    
    /**
     * Check if module is properly installed
     */
    public function isModuleInstalled(): bool
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if required tables exist
            $tables = ['fingerprint_import_logs', 'student_pin_mapping', 'fingerprint_import_settings'];
            foreach ($tables as $table) {
                $result = $db->query("SHOW TABLES LIKE '{$table}'");
                if ($result->getNumRows() === 0) {
                    return false;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get module status for system health check
     */
    public function getModuleStatus(): array
    {
        $status = [
            'installed' => $this->isModuleInstalled(),
            'database_connection' => false,
            'finpro_connection' => false,
            'tables_exist' => false,
            'settings_configured' => false
        ];
        
        try {
            // Check database connection
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            $status['database_connection'] = true;
            
            // Check if tables exist
            $status['tables_exist'] = $this->isModuleInstalled();
            
            // Check FinPro connection
            $connectionTest = $this->importService->testFinProConnection();
            $status['finpro_connection'] = $connectionTest['success'];
            
            // Check if settings are configured
            if ($status['tables_exist']) {
                $result = $db->query("SELECT COUNT(*) as count FROM fingerprint_import_settings");
                $settingsCount = $result->getRowArray()['count'] ?? 0;
                $status['settings_configured'] = $settingsCount > 0;
            }
            
        } catch (\Exception $e) {
            $status['error'] = $e->getMessage();
        }
        
        return $status;
    }
}
