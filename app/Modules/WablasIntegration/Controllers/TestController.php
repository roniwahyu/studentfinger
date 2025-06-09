<?php

namespace App\Modules\WablasIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WablasIntegration\WablasIntegrationModule;
use App\Libraries\WablasApi;

/**
 * Test Controller to verify module system functionality
 */
class TestController extends BaseController
{
    /**
     * Test module basic functionality
     */
    public function index()
    {
        $tests = [];
        
        // Test 1: Module instantiation
        try {
            $module = new WablasIntegrationModule();
            $tests['module_instantiation'] = [
                'status' => 'success',
                'message' => 'Module instantiated successfully',
                'data' => $module->getModuleInfo()
            ];
        } catch (\Exception $e) {
            $tests['module_instantiation'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 2: WablasApi class loading
        try {
            $config = [
                'base_url' => 'https://wablas.com',
                'token' => 'test_token',
                'secret_key' => 'test_secret'
            ];
            $api = new WablasApi($config);
            $tests['wablas_api_loading'] = [
                'status' => 'success',
                'message' => 'WablasApi class loaded successfully'
            ];
        } catch (\Exception $e) {
            $tests['wablas_api_loading'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 3: Model loading
        try {
            $deviceModel = new \App\Modules\WablasIntegration\Models\WablasDeviceModel();
            $tests['model_loading'] = [
                'status' => 'success',
                'message' => 'Models loaded successfully'
            ];
        } catch (\Exception $e) {
            $tests['model_loading'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 4: Service loading
        try {
            $service = new \App\Modules\WablasIntegration\Services\WablasService();
            $tests['service_loading'] = [
                'status' => 'success',
                'message' => 'Service loaded successfully'
            ];
        } catch (\Exception $e) {
            $tests['service_loading'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 5: Database connection
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            $tests['database_connection'] = [
                'status' => 'success',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $tests['database_connection'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 6: Check if tables exist
        try {
            $db = \Config\Database::connect();
            $tables = [
                'wablas_devices',
                'wablas_messages',
                'wablas_contacts',
                'wablas_schedules',
                'wablas_auto_replies',
                'wablas_webhooks',
                'wablas_logs',
                'wablas_templates',
                'wablas_groups',
                'wablas_campaigns'
            ];
            
            $existingTables = [];
            $missingTables = [];
            
            foreach ($tables as $table) {
                if ($db->tableExists($table)) {
                    $existingTables[] = $table;
                } else {
                    $missingTables[] = $table;
                }
            }
            
            $tests['database_tables'] = [
                'status' => empty($missingTables) ? 'success' : 'warning',
                'message' => empty($missingTables) ? 'All tables exist' : 'Some tables are missing',
                'data' => [
                    'existing' => $existingTables,
                    'missing' => $missingTables
                ]
            ];
        } catch (\Exception $e) {
            $tests['database_tables'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 7: Configuration loading
        try {
            $config = new \Config\WablasIntegration();
            $tests['configuration_loading'] = [
                'status' => 'success',
                'message' => 'Configuration loaded successfully',
                'data' => [
                    'wablas_config' => $config->wablas,
                    'webhook_config' => $config->webhooks
                ]
            ];
        } catch (\Exception $e) {
            $tests['configuration_loading'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        // Test 8: Route accessibility
        $tests['route_accessibility'] = [
            'status' => 'success',
            'message' => 'Routes are accessible (you are seeing this page)',
            'data' => [
                'current_route' => uri_string(),
                'base_url' => base_url(),
                'module_routes' => [
                    'dashboard' => base_url('wablas'),
                    'devices' => base_url('wablas/devices'),
                    'messages' => base_url('wablas/messages'),
                    'install' => base_url('wablas/install')
                ]
            ]
        ];
        
        // Calculate overall status
        $errorCount = 0;
        $warningCount = 0;
        $successCount = 0;
        
        foreach ($tests as $test) {
            switch ($test['status']) {
                case 'error':
                    $errorCount++;
                    break;
                case 'warning':
                    $warningCount++;
                    break;
                case 'success':
                    $successCount++;
                    break;
            }
        }
        
        $overallStatus = 'success';
        if ($errorCount > 0) {
            $overallStatus = 'error';
        } elseif ($warningCount > 0) {
            $overallStatus = 'warning';
        }
        
        $data = [
            'title' => 'Wablas Integration Module - System Test',
            'overall_status' => $overallStatus,
            'summary' => [
                'total_tests' => count($tests),
                'success' => $successCount,
                'warnings' => $warningCount,
                'errors' => $errorCount
            ],
            'tests' => $tests
        ];
        
        return $this->response->setJSON($data);
    }
    
    /**
     * Test API functionality (requires valid credentials)
     */
    public function testApi()
    {
        try {
            // Get configuration
            $config = new \Config\WablasIntegration();
            
            if (empty($config->wablas['token'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No API token configured. Please set WABLAS_TOKEN in your .env file.'
                ]);
            }
            
            // Initialize API
            $api = new WablasApi($config->wablas);
            
            // Test device info
            $deviceInfo = $api->getDeviceInfo();
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'API test successful',
                'data' => $deviceInfo
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'API test failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Test database operations
     */
    public function testDatabase()
    {
        try {
            $results = [];
            
            // Test device model
            $deviceModel = new \App\Modules\WablasIntegration\Models\WablasDeviceModel();
            $results['device_model'] = [
                'table_exists' => $deviceModel->db->tableExists('wablas_devices'),
                'statistics' => $deviceModel->getStatistics()
            ];
            
            // Test message model
            $messageModel = new \App\Modules\WablasIntegration\Models\WablasMessageModel();
            $results['message_model'] = [
                'table_exists' => $messageModel->db->tableExists('wablas_messages'),
                'statistics' => $messageModel->getStatistics()
            ];
            
            // Test contact model
            $contactModel = new \App\Modules\WablasIntegration\Models\WablasContactModel();
            $results['contact_model'] = [
                'table_exists' => $contactModel->db->tableExists('wablas_contacts'),
                'statistics' => $contactModel->getStatistics()
            ];
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Database test successful',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database test failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Simple HTML view for browser testing
     */
    public function view()
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Wablas Integration Module Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .test-result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .btn { padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-info { background-color: #17a2b8; color: white; }
    </style>
</head>
<body>
    <h1>🚀 Wablas Integration Module Test</h1>
    <p>This page verifies that the Wablas Integration module is working properly.</p>
    
    <div class="test-result success">
        <h3>✅ Module System Working</h3>
        <p>If you can see this page, it means:</p>
        <ul>
            <li>Module auto-discovery is working</li>
            <li>Routes are properly loaded</li>
            <li>Controllers are accessible</li>
            <li>Namespaces are correctly configured</li>
        </ul>
    </div>
    
    <h3>🧪 Run Tests</h3>
    <a href="' . base_url('wablas/test') . '" class="btn btn-primary">JSON System Test</a>
    <a href="' . base_url('wablas/test/testApi') . '" class="btn btn-success">API Test</a>
    <a href="' . base_url('wablas/test/testDatabase') . '" class="btn btn-info">Database Test</a>
    
    <h3>📋 Module Links</h3>
    <a href="' . base_url('wablas') . '" class="btn btn-primary">Dashboard</a>
    <a href="' . base_url('wablas/install') . '" class="btn btn-success">Installation</a>
    <a href="' . base_url('wablas/examples') . '" class="btn btn-info">Examples</a>
    
    <h3>📖 Module Information</h3>
    <ul>
        <li><strong>Module Path:</strong> app/Modules/WablasIntegration/</li>
        <li><strong>Namespace:</strong> App\\Modules\\WablasIntegration</li>
        <li><strong>Routes File:</strong> app/Modules/WablasIntegration/Config/Routes.php</li>
        <li><strong>Configuration:</strong> app/Config/WablasIntegration.php</li>
    </ul>
</body>
</html>';
        
        return $this->response->setBody($html);
    }
}
