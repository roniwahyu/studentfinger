<?php

namespace App\Libraries;

use CodeIgniter\Controller\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base Module Class for HMVC Architecture
 * 
 * This class provides the foundation for all modules in the system,
 * enabling inter-module communication and shared functionality.
 */
abstract class BaseModule
{
    /**
     * Module name
     */
    protected string $moduleName;
    
    /**
     * Module configuration
     */
    protected array $moduleConfig = [];
    
    /**
     * Request instance
     */
    protected RequestInterface $request;
    
    /**
     * Response instance
     */
    protected ResponseInterface $response;
    
    /**
     * Logger instance
     */
    protected LoggerInterface $logger;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->request = \Config\Services::request();
        $this->response = \Config\Services::response();
        $this->logger = \Config\Services::logger();
        
        $this->loadModuleConfig();
        $this->initialize();
    }
    
    /**
     * Initialize module-specific settings
     */
    protected function initialize(): void
    {
        // Override in child classes if needed
    }
    
    /**
     * Load module configuration
     */
    protected function loadModuleConfig(): void
    {
        $configPath = APPPATH . 'Modules/' . $this->getModuleName() . '/Config/Module.php';
        
        if (file_exists($configPath)) {
            $this->moduleConfig = include $configPath;
        }
    }
    
    /**
     * Get module name
     */
    public function getModuleName(): string
    {
        if (empty($this->moduleName)) {
            $className = get_class($this);
            $this->moduleName = str_replace(['Modules\\', '\\', 'Module'], '', $className);
        }
        
        return $this->moduleName;
    }
    
    /**
     * Call another module's controller method
     * 
     * @param string $module Module name
     * @param string $controller Controller name
     * @param string $method Method name
     * @param array $params Parameters to pass
     * @return mixed
     */
    public function callModule(string $module, string $controller, string $method, array $params = [])
    {
        $controllerClass = "Modules\\{$module}\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller {$controllerClass} not found");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new \RuntimeException("Method {$method} not found in {$controllerClass}");
        }
        
        return call_user_func_array([$controllerInstance, $method], $params);
    }
    
    /**
     * Get module configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        return $this->moduleConfig[$key] ?? $default;
    }
    
    /**
     * Set module configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     */
    public function setConfig(string $key, $value): void
    {
        $this->moduleConfig[$key] = $value;
    }
    
    /**
     * Load module model
     * 
     * @param string $modelName Model name
     * @return mixed
     */
    public function loadModel(string $modelName)
    {
        $modelClass = "Modules\\{$this->getModuleName()}\\Models\\{$modelName}";
        
        if (!class_exists($modelClass)) {
            throw new \RuntimeException("Model {$modelClass} not found");
        }
        
        return new $modelClass();
    }
    
    /**
     * Load module view
     * 
     * @param string $viewName View name
     * @param array $data Data to pass to view
     * @param array $options View options
     * @return string
     */
    public function loadView(string $viewName, array $data = [], array $options = []): string
    {
        $viewPath = 'Modules/' . $this->getModuleName() . '/Views/' . $viewName;
        
        return view($viewPath, $data, $options);
    }
    
    /**
     * Log module activity
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $context['module'] = $this->getModuleName();
        $this->logger->log($level, $message, $context);
    }
}