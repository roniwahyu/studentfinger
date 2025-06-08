<?php

namespace App\Modules\WablasIntegration\Controllers;

use App\Libraries\BaseController;
use App\Modules\WablasIntegration\WablasIntegrationModule;
use CodeIgniter\Config\Services;

/**
 * Installation Controller for Wablas Integration Module
 */
class InstallController extends BaseController
{
    protected WablasIntegrationModule $module;
    
    public function __construct()
    {
        parent::__construct();
        $this->module = new WablasIntegrationModule();
    }
    
    /**
     * Installation page
     */
    public function index()
    {
        $data = [
            'title' => 'Wablas Integration - Installation',
            'module_info' => $this->module->getModuleInfo(),
            'requirements' => $this->checkRequirements(),
            'is_installed' => $this->isModuleInstalled()
        ];
        
        return view('Modules/WablasIntegration/Views/install/index', $data);
    }
    
    /**
     * Run installation
     */
    public function install()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('wablas/install');
        }
        
        try {
            // Check requirements first
            $requirements = $this->checkRequirements();
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
            
            // Install the module
            $result = $this->module->install();
            
            if ($result['success']) {
                // Set installation flag
                $this->setInstallationFlag(true);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $result['message'],
                    'version' => $result['version']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => $result['message']
                ]);
            }
            
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
        if (!$this->request->isAJAX()) {
            return redirect()->to('wablas/install');
        }
        
        try {
            $result = $this->module->uninstall();
            
            if ($result['success']) {
                // Remove installation flag
                $this->setInstallationFlag(false);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => $result['message']
                ]);
            }
            
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
        if ($this->request->isAJAX()) {
            $requirements = $this->checkRequirements();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $requirements
            ]);
        }
        
        return $this->checkSystemRequirements();
    }
    
    /**
     * Run database migrations
     */
    public function migrate()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('wablas/install');
        }
        
        try {
            $migrate = Services::migrations();
            $migrate->setNamespace('App\Modules\WablasIntegration');
            
            // Run migrations
            $migrate->latest();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Database migrations completed successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Migration failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Seed database with sample data
     */
    public function seed()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('wablas/install');
        }
        
        try {
            $seeder = \Config\Database::seeder();
            $seeder->call('WablasIntegrationSeeder');
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Sample data seeded successfully'
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
                'version' => $this->module->getModuleInfo()['version'],
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
            'module_info' => $this->module->getModuleInfo()
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
