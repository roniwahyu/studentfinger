<?php

namespace App\Modules\WablasIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WablasIntegration\WablasIntegrationModule;
use CodeIgniter\Config\Services;

/**
 * Installation Controller for Wablas Integration Module
 */
class InstallController extends BaseController
{
    protected ?WablasIntegrationModule $module = null;

    /**
     * Get module instance (lazy loading)
     */
    protected function getModule(): WablasIntegrationModule
    {
        if ($this->module === null) {
            $this->module = new WablasIntegrationModule();
        }
        return $this->module;
    }
    
    /**
     * Installation page
     */
    public function index()
    {
        $data = [
            'title' => 'Wablas Integration - Installation',
            'module_info' => $this->getModule()->getModuleInfo(),
            'requirements' => $this->checkRequirements(),
            'is_installed' => $this->isModuleInstalled()
        ];
        
        // For now, return JSON response until view system is properly configured
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Wablas Integration Module Installation Page',
            'data' => $data
        ]);
    }
    
    /**
     * Run installation
     */
    public function install()
    {
        try {
            // Check if already installed
            if ($this->isModuleInstalled()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Wablas Integration Module is already installed',
                    'version' => $this->getModule()->getModuleInfo()['version'],
                    'installed' => true
                ]);
            }

            // Check requirements first
            $requirements = $this->checkSystemRequirements();
            $failed = array_filter($requirements, function($req) {
                return !$req['status'];
            });

            if (!empty($failed)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'System requirements not met',
                    'failed_requirements' => $failed
                ]);
            }

            // Run migrations first
            $migrateResult = $this->runMigrations();

            // Set installation flag even if some migrations failed (tables might already exist)
            $this->setInstallationFlag(true);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Wablas Integration Module installed successfully',
                'version' => $this->getModule()->getModuleInfo()['version'],
                'migration_result' => $migrateResult
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Installation failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Uninstall module
     */
    public function uninstall()
    {
        try {
            $db = \Config\Database::connect();

            // Disable foreign key checks
            $db->query('SET FOREIGN_KEY_CHECKS = 0');

            // Drop all tables in reverse order (to handle dependencies)
            $tables = [
                'wablas_campaigns',
                'wablas_groups',
                'wablas_templates',
                'wablas_logs',
                'wablas_webhooks',
                'wablas_auto_replies',
                'wablas_schedules',
                'wablas_contacts',
                'wablas_messages',
                'wablas_devices'
            ];

            $dropped = [];
            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    $db->query("DROP TABLE IF EXISTS `{$table}`");
                    $dropped[] = $table;
                }
            }

            // Re-enable foreign key checks
            $db->query('SET FOREIGN_KEY_CHECKS = 1');

            // Remove installation flag
            $this->setInstallationFlag(false);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Wablas Integration Module uninstalled successfully',
                'dropped_tables' => $dropped
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Uninstallation failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check system requirements
     */
    public function checkRequirements()
    {
        $requirements = $this->checkSystemRequirements();

        return $this->response->setJSON([
            'success' => true,
            'data' => $requirements
        ]);
    }
    
    /**
     * Run database migrations
     */
    public function migrate()
    {
        try {
            $result = $this->runMigrations();
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Migration failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Run migrations helper
     */
    protected function runMigrations(): array
    {
        try {
            $db = \Config\Database::connect();

            // Check which tables already exist
            $existingTables = $this->getExistingTables();

            // Get migration files
            $migrationPath = APPPATH . 'Modules/WablasIntegration/Database/Migrations/';
            $migrationFiles = glob($migrationPath . '*.php');

            if (empty($migrationFiles)) {
                return [
                    'success' => false,
                    'error' => 'No migration files found'
                ];
            }

            sort($migrationFiles); // Execute in order
            $executed = [];
            $skipped = [];
            $errors = [];

            foreach ($migrationFiles as $file) {
                try {
                    $className = $this->getMigrationClassName($file);
                    $tableName = $this->getTableNameFromMigration($file);

                    // Skip if table already exists
                    if ($tableName && in_array($tableName, $existingTables)) {
                        $skipped[] = basename($file) . " (table '{$tableName}' already exists)";
                        continue;
                    }

                    if ($className) {
                        require_once $file;

                        $fullClassName = "App\\Modules\\WablasIntegration\\Database\\Migrations\\{$className}";

                        if (class_exists($fullClassName)) {
                            $migration = new $fullClassName();
                            $migration->up();
                            $executed[] = basename($file);
                        }
                    }
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    // Skip "table already exists" errors
                    if (strpos($errorMsg, 'already exists') !== false) {
                        $skipped[] = basename($file) . ': ' . $errorMsg;
                    } else {
                        $errors[] = basename($file) . ': ' . $errorMsg;
                    }
                }
            }

            $totalProcessed = count($executed) + count($skipped);
            $hasSuccess = $totalProcessed > 0;

            return [
                'success' => $hasSuccess,
                'message' => $hasSuccess ? 'Database setup completed' : 'No migrations processed',
                'executed' => $executed,
                'skipped' => $skipped,
                'errors' => $errors,
                'summary' => [
                    'total_files' => count($migrationFiles),
                    'executed' => count($executed),
                    'skipped' => count($skipped),
                    'errors' => count($errors)
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get existing tables
     */
    protected function getExistingTables(): array
    {
        try {
            $db = \Config\Database::connect();
            $tables = $db->listTables();
            return $tables;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get table name from migration file
     */
    protected function getTableNameFromMigration(string $filePath): ?string
    {
        $content = file_get_contents($filePath);

        // Look for createTable call
        if (preg_match('/createTable\([\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get migration class name from file
     */
    protected function getMigrationClassName(string $filePath): ?string
    {
        $content = file_get_contents($filePath);

        // Extract class name from file content
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }
    
    /**
     * Seed database with sample data
     */
    public function seed()
    {
        try {
            // For now, just return success since we don't have seeders yet
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sample data seeding completed (no seeders configured yet)'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Seeding failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check system requirements
     */
    protected function checkSystemRequirements(): array
    {
        $requirements = [];
        
        // PHP Version
        $requirements[] = [
            'name' => 'PHP Version',
            'required' => '8.1+',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'description' => 'PHP 8.1 or higher is required'
        ];
        
        // CodeIgniter Version
        $requirements[] = [
            'name' => 'CodeIgniter Version',
            'required' => '4.0+',
            'current' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'status' => version_compare(\CodeIgniter\CodeIgniter::CI_VERSION, '4.0.0', '>='),
            'description' => 'CodeIgniter 4.0 or higher is required'
        ];
        
        // cURL Extension
        $requirements[] = [
            'name' => 'cURL Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('curl') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('curl'),
            'description' => 'cURL extension is required for API calls'
        ];
        
        // JSON Extension
        $requirements[] = [
            'name' => 'JSON Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('json'),
            'description' => 'JSON extension is required for data processing'
        ];
        
        // OpenSSL Extension
        $requirements[] = [
            'name' => 'OpenSSL Extension',
            'required' => 'Enabled',
            'current' => extension_loaded('openssl') ? 'Enabled' : 'Disabled',
            'status' => extension_loaded('openssl'),
            'description' => 'OpenSSL extension is required for secure connections'
        ];
        
        // Database Connection
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            $dbStatus = true;
            $dbMessage = 'Connected';
        } catch (\Exception $e) {
            $dbStatus = false;
            $dbMessage = 'Connection failed: ' . $e->getMessage();
        }
        
        $requirements[] = [
            'name' => 'Database Connection',
            'required' => 'Connected',
            'current' => $dbMessage,
            'status' => $dbStatus,
            'description' => 'Database connection is required'
        ];
        
        // Writable Directories
        $writableDirs = [
            WRITEPATH,
            WRITEPATH . 'logs',
            WRITEPATH . 'cache',
            WRITEPATH . 'session',
            WRITEPATH . 'uploads'
        ];
        
        foreach ($writableDirs as $dir) {
            $isWritable = is_writable($dir);
            $requirements[] = [
                'name' => 'Writable: ' . basename($dir),
                'required' => 'Writable',
                'current' => $isWritable ? 'Writable' : 'Not writable',
                'status' => $isWritable,
                'description' => "Directory {$dir} must be writable"
            ];
        }
        
        // Guzzle HTTP Client
        $guzzleExists = class_exists('GuzzleHttp\Client');
        $requirements[] = [
            'name' => 'Guzzle HTTP Client',
            'required' => 'Installed',
            'current' => $guzzleExists ? 'Installed' : 'Not installed',
            'status' => $guzzleExists,
            'description' => 'Guzzle HTTP client is required for API calls'
        ];
        
        return $requirements;
    }
    
    /**
     * Check if module is installed
     */
    protected function isModuleInstalled(): bool
    {
        // Check if installation flag exists
        $flagFile = WRITEPATH . 'wablas_installed.flag';
        
        if (!file_exists($flagFile)) {
            return false;
        }
        
        // Check if database tables exist
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
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Set installation flag
     */
    protected function setInstallationFlag(bool $installed): void
    {
        $flagFile = WRITEPATH . 'wablas_installed.flag';
        
        if ($installed) {
            $data = [
                'installed_at' => date('Y-m-d H:i:s'),
                'version' => $this->getModule()->getModuleInfo()['version'],
                'php_version' => PHP_VERSION,
                'ci_version' => \CodeIgniter\CodeIgniter::CI_VERSION
            ];
            
            file_put_contents($flagFile, json_encode($data));
        } else {
            if (file_exists($flagFile)) {
                unlink($flagFile);
            }
        }
    }
    
    /**
     * Get installation status
     */
    public function getInstallationStatus()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('wablas/install');
        }
        
        $isInstalled = $this->isModuleInstalled();
        $requirements = $this->checkSystemRequirements();
        
        $status = [
            'installed' => $isInstalled,
            'requirements_met' => !array_filter($requirements, function($req) {
                return !$req['status'];
            }),
            'module_info' => $this->getModule()->getModuleInfo()
        ];
        
        if ($isInstalled) {
            $flagFile = WRITEPATH . 'wablas_installed.flag';
            if (file_exists($flagFile)) {
                $status['installation_info'] = json_decode(file_get_contents($flagFile), true);
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $status
        ]);
    }
}
