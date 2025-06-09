<?php

namespace App\Modules\WablasIntegration;

use App\Libraries\BaseModule;

/**
 * Wablas Integration Module
 * 
 * A comprehensive, pluggable module for integrating with Wablas.com WhatsApp API
 * Features:
 * - Complete API coverage for Wablas.com
 * - Device management
 * - Message sending (text, image, document, video, audio)
 * - Bulk messaging
 * - Scheduled messaging
 * - Contact management
 * - Auto-reply system
 * - Webhook handling
 * - Reporting and analytics
 * - Easy installation/uninstallation
 */
class WablasIntegrationModule extends BaseModule
{
    protected string $moduleName = 'WablasIntegration';
    
    /**
     * Module version
     */
    protected string $version = '1.0.0';
    
    /**
     * Module dependencies
     */
    protected array $dependencies = [
        'guzzlehttp/guzzle' => '^7.0'
    ];
    
    /**
     * Initialize module-specific settings
     */
    protected function initialize(): void
    {
        // Load module configuration
        $this->loadModuleConfig();
        
        // Register module services
        $this->registerServices();
        
        // Set up module routes
        $this->setupRoutes();
        
        // Initialize database if needed
        $this->initializeDatabase();
    }
    
    /**
     * Install module
     */
    public function install(): array
    {
        try {
            // Run database migrations
            $this->runMigrations();
            
            // Create default configuration
            $this->createDefaultConfig();
            
            // Set up default menu items
            $this->setupMenuItems();
            
            // Create default webhook endpoints
            $this->setupWebhooks();
            
            return [
                'success' => true,
                'message' => 'Wablas Integration module installed successfully',
                'version' => $this->version
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to install Wablas Integration module: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Uninstall module
     */
    public function uninstall(): array
    {
        try {
            // Remove database tables (optional - ask user)
            $this->removeDatabaseTables();
            
            // Remove configuration files
            $this->removeConfiguration();
            
            // Remove menu items
            $this->removeMenuItems();
            
            // Clean up webhooks
            $this->cleanupWebhooks();
            
            return [
                'success' => true,
                'message' => 'Wablas Integration module uninstalled successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to uninstall Wablas Integration module: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update module
     */
    public function update(string $fromVersion): array
    {
        try {
            // Run migration updates
            $this->runMigrationUpdates($fromVersion);
            
            // Update configuration if needed
            $this->updateConfiguration($fromVersion);
            
            return [
                'success' => true,
                'message' => "Wablas Integration module updated from {$fromVersion} to {$this->version}"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update Wablas Integration module: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get module information
     */
    public function getModuleInfo(): array
    {
        return [
            'name' => 'Wablas Integration',
            'description' => 'Complete integration with Wablas.com WhatsApp API',
            'version' => $this->version,
            'author' => 'Student Attendance System',
            'dependencies' => $this->dependencies,
            'features' => [
                'Device Management',
                'Message Sending (All Types)',
                'Bulk Messaging',
                'Scheduled Messaging',
                'Contact Management',
                'Auto-Reply System',
                'Webhook Handling',
                'Reporting & Analytics',
                'Group Messaging',
                'File Upload Support'
            ],
            'api_coverage' => [
                'API v1' => 'Complete',
                'API v2' => 'Complete',
                'Webhooks' => 'Complete',
                'Device Management' => 'Complete'
            ]
        ];
    }
    
    /**
     * Register module services
     */
    protected function registerServices(): void
    {
        // Register Wablas API service
        \Config\Services::set('wablasApi', function() {
            $config = $this->getConfig('wablas', []);
            return new \App\Libraries\WablasApi($config);
        });

        // Register Wablas service
        \Config\Services::set('wablasService', function() {
            return new \App\Modules\WablasIntegration\Services\WablasService();
        });
    }
    
    /**
     * Setup module routes
     */
    protected function setupRoutes(): void
    {
        // Routes are defined in Config/Routes.php
        // This method can be used for dynamic route registration if needed
    }
    
    /**
     * Initialize database
     */
    protected function initializeDatabase(): void
    {
        // Check if tables exist, create if not
        $db = \Config\Database::connect();
        
        $tables = [
            'wablas_devices',
            'wablas_messages',
            'wablas_contacts',
            'wablas_schedules',
            'wablas_auto_replies',
            'wablas_webhooks',
            'wablas_logs'
        ];
        
        foreach ($tables as $table) {
            if (!$db->tableExists($table)) {
                // Table doesn't exist, might need to run migrations
                $this->log('warning', "Table {$table} does not exist. Please run migrations.");
            }
        }
    }
    
    /**
     * Run database migrations
     */
    protected function runMigrations(): void
    {
        $migrate = \Config\Services::migrations();
        $migrate->setNamespace('App\Modules\WablasIntegration');
        $migrate->latest();
    }
    
    /**
     * Create default configuration
     */
    protected function createDefaultConfig(): void
    {
        $configPath = APPPATH . 'Config/WablasIntegration.php';
        
        if (!file_exists($configPath)) {
            $configContent = $this->getDefaultConfigContent();
            file_put_contents($configPath, $configContent);
        }
    }
    
    /**
     * Setup menu items
     */
    protected function setupMenuItems(): void
    {
        // This would integrate with your existing menu system
        // Implementation depends on your menu structure
    }
    
    /**
     * Setup webhooks
     */
    protected function setupWebhooks(): void
    {
        // Create default webhook endpoints
        $webhookModel = new \App\Modules\WablasIntegration\Models\WablasWebhookModel();
        
        $defaultWebhooks = [
            [
                'name' => 'Incoming Messages',
                'endpoint' => 'wablas/webhook/incoming',
                'type' => 'incoming',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Message Status',
                'endpoint' => 'wablas/webhook/status',
                'type' => 'status',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Device Status',
                'endpoint' => 'wablas/webhook/device',
                'type' => 'device',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        foreach ($defaultWebhooks as $webhook) {
            $existing = $webhookModel->where('endpoint', $webhook['endpoint'])->first();
            if (!$existing) {
                $webhookModel->insert($webhook);
            }
        }
    }
    
    /**
     * Get default configuration content
     */
    protected function getDefaultConfigContent(): string
    {
        return '<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WablasIntegration extends BaseConfig
{
    /**
     * Default Wablas API Configuration
     */
    public array $wablas = [
        "base_url" => "https://wablas.com",
        "token" => "",
        "secret_key" => "",
        "timeout" => 30,
        "verify_ssl" => false
    ];
    
    /**
     * Webhook Configuration
     */
    public array $webhooks = [
        "incoming_url" => "",
        "status_url" => "",
        "device_url" => "",
        "verify_signature" => true
    ];
    
    /**
     * Message Configuration
     */
    public array $messages = [
        "default_delay" => 1, // seconds between messages
        "max_retries" => 3,
        "retry_delay" => 5, // seconds
        "enable_logging" => true
    ];
    
    /**
     * Auto Reply Configuration
     */
    public array $autoReply = [
        "enabled" => false,
        "default_response" => "Thank you for your message. We will get back to you soon.",
        "keywords" => []
    ];
}
';
    }
    
    /**
     * Remove database tables
     */
    protected function removeDatabaseTables(): void
    {
        // This is optional - usually we keep data
        // Implementation would drop tables if user confirms
    }
    
    /**
     * Remove configuration
     */
    protected function removeConfiguration(): void
    {
        $configPath = APPPATH . 'Config/WablasIntegration.php';
        if (file_exists($configPath)) {
            unlink($configPath);
        }
    }
    
    /**
     * Remove menu items
     */
    protected function removeMenuItems(): void
    {
        // Remove menu items from your menu system
    }
    
    /**
     * Cleanup webhooks
     */
    protected function cleanupWebhooks(): void
    {
        // Remove webhook configurations
    }
    
    /**
     * Run migration updates
     */
    protected function runMigrationUpdates(string $fromVersion): void
    {
        // Run specific migrations based on version
    }
    
    /**
     * Update configuration
     */
    protected function updateConfiguration(string $fromVersion): void
    {
        // Update configuration files if needed
    }
}
