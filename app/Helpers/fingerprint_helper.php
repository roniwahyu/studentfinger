<?php

/**
 * FingerprintBridge Helper Functions
 * 
 * Helper functions for integrating FingerprintBridge module with the main application
 */

if (!function_exists('get_fingerprint_dashboard_widget')) {
    /**
     * Get FingerprintBridge dashboard widget HTML
     * 
     * @return string HTML content for the dashboard widget
     */
    function get_fingerprint_dashboard_widget(): string
    {
        try {
            // Check if module is installed
            if (!class_exists('App\Modules\FingerprintBridge\Services\DashboardService')) {
                return '';
            }
            
            $dashboardService = new \App\Modules\FingerprintBridge\Services\DashboardService();
            
            // Check if module is properly installed
            if (!$dashboardService->isModuleInstalled()) {
                return get_fingerprint_install_widget();
            }
            
            // Get fingerprint statistics
            $fingerprintStats = $dashboardService->getDashboardStats();
            
            // Load the widget view
            $view = \Config\Services::renderer();
            return $view->setData(['fingerprint_stats' => $fingerprintStats])
                       ->render('App\Modules\FingerprintBridge\Views\dashboard_widget');
                       
        } catch (\Exception $e) {
            return get_fingerprint_error_widget($e->getMessage());
        }
    }
}

if (!function_exists('get_fingerprint_install_widget')) {
    /**
     * Get installation widget for FingerprintBridge
     * 
     * @return string HTML content for installation widget
     */
    function get_fingerprint_install_widget(): string
    {
        return '
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-fingerprint"></i> FingerprintBridge Module
                    </h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-download fa-3x text-warning mb-3"></i>
                    <h5 class="text-warning">Module Not Installed</h5>
                    <p class="text-muted">Install the FingerprintBridge module to import attendance data from fingerspot machines.</p>
                    <div class="mt-3">
                        <button class="btn btn-warning" onclick="installFingerprintModule()">
                            <i class="fas fa-download"></i> Install Module
                        </button>
                        <a href="' . base_url('fingerprint-bridge') . '" class="btn btn-outline-secondary">
                            <i class="fas fa-info-circle"></i> Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function installFingerprintModule() {
            if (confirm("This will install the FingerprintBridge module. Continue?")) {
                // You can implement AJAX installation here or redirect to installation page
                window.location.href = "' . base_url('fingerprint-bridge/install') . '";
            }
        }
        </script>';
    }
}

if (!function_exists('get_fingerprint_error_widget')) {
    /**
     * Get error widget for FingerprintBridge
     * 
     * @param string $error Error message
     * @return string HTML content for error widget
     */
    function get_fingerprint_error_widget(string $error): string
    {
        return '
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-fingerprint"></i> FingerprintBridge Module
                    </h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Module Error</h5>
                    <p class="text-muted">' . htmlspecialchars($error) . '</p>
                    <div class="mt-3">
                        <a href="' . base_url('fingerprint-bridge') . '" class="btn btn-danger">
                            <i class="fas fa-tools"></i> Troubleshoot
                        </a>
                    </div>
                </div>
            </div>
        </div>';
    }
}

if (!function_exists('get_fingerprint_menu_items')) {
    /**
     * Get FingerprintBridge menu items for main navigation
     * 
     * @return array Menu items array
     */
    function get_fingerprint_menu_items(): array
    {
        try {
            if (!class_exists('App\Modules\FingerprintBridge\Services\DashboardService')) {
                return [];
            }
            
            $dashboardService = new \App\Modules\FingerprintBridge\Services\DashboardService();
            
            if (!$dashboardService->isModuleInstalled()) {
                return [];
            }
            
            return $dashboardService->getMenuItems();
            
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('get_fingerprint_alerts')) {
    /**
     * Get FingerprintBridge alerts for dashboard
     * 
     * @return array Alerts array
     */
    function get_fingerprint_alerts(): array
    {
        try {
            if (!class_exists('App\Modules\FingerprintBridge\Services\DashboardService')) {
                return [];
            }
            
            $dashboardService = new \App\Modules\FingerprintBridge\Services\DashboardService();
            
            if (!$dashboardService->isModuleInstalled()) {
                return [];
            }
            
            return $dashboardService->getDashboardAlerts();
            
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('get_fingerprint_summary_stats')) {
    /**
     * Get FingerprintBridge summary statistics
     * 
     * @return array Summary statistics
     */
    function get_fingerprint_summary_stats(): array
    {
        try {
            if (!class_exists('App\Modules\FingerprintBridge\Services\DashboardService')) {
                return [];
            }
            
            $dashboardService = new \App\Modules\FingerprintBridge\Services\DashboardService();
            
            if (!$dashboardService->isModuleInstalled()) {
                return [];
            }
            
            return $dashboardService->getSummaryStats();
            
        } catch (\Exception $e) {
            return [];
        }
    }
}

if (!function_exists('is_fingerprint_module_available')) {
    /**
     * Check if FingerprintBridge module is available and working
     * 
     * @return bool True if module is available
     */
    function is_fingerprint_module_available(): bool
    {
        try {
            if (!class_exists('App\Modules\FingerprintBridge\Services\DashboardService')) {
                return false;
            }
            
            $dashboardService = new \App\Modules\FingerprintBridge\Services\DashboardService();
            return $dashboardService->isModuleInstalled();
            
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('get_fingerprint_module_status')) {
    /**
     * Get detailed module status for system health check
     * 
     * @return array Module status details
     */
    function get_fingerprint_module_status(): array
    {
        try {
            if (!class_exists('App\Modules\FingerprintBridge\Services\DashboardService')) {
                return ['available' => false, 'error' => 'Module class not found'];
            }
            
            $dashboardService = new \App\Modules\FingerprintBridge\Services\DashboardService();
            $status = $dashboardService->getModuleStatus();
            $status['available'] = true;
            
            return $status;
            
        } catch (\Exception $e) {
            return ['available' => false, 'error' => $e->getMessage()];
        }
    }
}
