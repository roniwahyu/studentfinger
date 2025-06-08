<?php

// Define path constants
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('ROOTPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', ROOTPATH . 'vendor/codeigniter4/framework/system' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);

// Define application namespace
define('APP_NAMESPACE', 'App');

// Define composer path
define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

// Define environment
define('ENVIRONMENT', 'development');

// Define exit constants
define('EXIT_SUCCESS', 0);
define('EXIT_ERROR', 1);
define('EXIT_CONFIG', 3);
define('EXIT_UNKNOWN_FILE', 4);
define('EXIT_UNKNOWN_CLASS', 5);
define('EXIT_UNKNOWN_METHOD', 6);
define('EXIT_USER_INPUT', 7);
define('EXIT_DATABASE', 8);
define('EXIT_FRAMEWORK', 9);
define('EXIT_VALIDATION', 10);

// Load Composer's autoloader
require ROOTPATH . 'vendor/autoload.php';

// Load CodeIgniter's Common functions
require SYSTEMPATH . 'Common.php';
require APPPATH . 'Common.php';

// Set up autoloader
require SYSTEMPATH . 'Config/AutoloadConfig.php';
require APPPATH . 'Config/Autoload.php';
require SYSTEMPATH . 'Modules/Modules.php';
require APPPATH . 'Config/Modules.php';
require SYSTEMPATH . 'Autoloader/Autoloader.php';
require SYSTEMPATH . 'Config/BaseService.php';
require SYSTEMPATH . 'Config/Services.php';
require APPPATH . 'Config/Services.php';

// Initialize the autoloader
$autoloader = \Config\Services::autoloader();
$autoloader->initialize(new \Config\Autoload(), new \Config\Modules());
$autoloader->register();

// Load helpers
$autoloader->loadHelpers();

// Set up exception handling
\Config\Services::exceptions()->initialize();

// Create a temporary override for the ModuleManager's load method
// to fix the namespace issue
class TestModuleManager extends \App\Libraries\ModuleManager
{
    public static function load(string $moduleName): \App\Libraries\BaseModule
    {
        if (!isset(self::$modules[$moduleName])) {
            $moduleClass = "App\\Modules\\{$moduleName}\\{$moduleName}Module";
            
            if (!class_exists($moduleClass)) {
                throw new \RuntimeException("Module {$moduleName} not found. Class {$moduleClass} does not exist.");
            }
            
            self::$modules[$moduleName] = new $moduleClass();
        }
        
        return self::$modules[$moduleName];
    }
}

// Replace the original ModuleManager with our test version
\App\Libraries\ModuleManager::clearAll();

// Initialize the ModuleManager
$moduleManager = new TestModuleManager();

// Get available modules
$availableModules = TestModuleManager::getAvailableModules();

echo "<h1>HMVC Test</h1>";
echo "<h2>Available Modules:</h2>";
echo "<ul>";
foreach ($availableModules as $module) {
    echo "<li>{$module}</li>";
}
echo "</ul>";

// Test loading modules
echo "<h2>Module Loading Test:</h2>";
echo "<ul>";
foreach ($availableModules as $module) {
    try {
        $moduleInstance = TestModuleManager::load($module);
        echo "<li>{$module}: Successfully loaded</li>";
    } catch (\Exception $e) {
        echo "<li>{$module}: Failed to load - " . $e->getMessage() . "</li>";
    }
}
echo "</ul>";

// Test module configurations
echo "<h2>Module Configurations:</h2>";
echo "<ul>";
foreach ($availableModules as $module) {
    $config = TestModuleManager::getModuleConfig($module);
    echo "<li>{$module}: " . (is_array($config) ? "Configuration loaded successfully" : "Failed to load configuration") . "</li>";
}
echo "</ul>";