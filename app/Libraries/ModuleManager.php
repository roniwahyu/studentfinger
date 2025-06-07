<?php

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Module Manager for HMVC Architecture
 * 
 * This class manages module loading, inter-module communication,
 * and provides a centralized way to access modules.
 */
class ModuleManager
{
    /**
     * Loaded modules cache
     */
    private static array $modules = [];
    
    /**
     * Module configurations
     */
    private static array $moduleConfigs = [];
    
    /**
     * Load a module
     * 
     * @param string $moduleName Module name
     * @return BaseModule
     * @throws \RuntimeException
     */
    public static function load(string $moduleName): BaseModule
    {
        if (!isset(self::$modules[$moduleName])) {
            $moduleClass = "Modules\\{$moduleName}\\{$moduleName}Module";
            
            if (!class_exists($moduleClass)) {
                throw new \RuntimeException("Module {$moduleName} not found. Class {$moduleClass} does not exist.");
            }
            
            self::$modules[$moduleName] = new $moduleClass();
        }
        
        return self::$modules[$moduleName];
    }
    
    /**
     * Call a module's controller method
     * 
     * @param string $module Module name
     * @param string $controller Controller name
     * @param string $method Method name
     * @param array $params Parameters
     * @return mixed
     */
    public static function call(string $module, string $controller, string $method, array $params = [])
    {
        $moduleInstance = self::load($module);
        return $moduleInstance->callModule($module, $controller, $method, $params);
    }
    
    /**
     * Get all loaded modules
     * 
     * @return array
     */
    public static function getLoadedModules(): array
    {
        return array_keys(self::$modules);
    }
    
    /**
     * Check if a module is loaded
     * 
     * @param string $moduleName Module name
     * @return bool
     */
    public static function isLoaded(string $moduleName): bool
    {
        return isset(self::$modules[$moduleName]);
    }
    
    /**
     * Unload a module
     * 
     * @param string $moduleName Module name
     */
    public static function unload(string $moduleName): void
    {
        unset(self::$modules[$moduleName]);
    }
    
    /**
     * Get available modules by scanning the Modules directory
     * 
     * @return array
     */
    public static function getAvailableModules(): array
    {
        $modulesPath = APPPATH . 'Modules';
        $modules = [];
        
        if (is_dir($modulesPath)) {
            $directories = scandir($modulesPath);
            
            foreach ($directories as $directory) {
                if ($directory !== '.' && $directory !== '..' && is_dir($modulesPath . '/' . $directory)) {
                    $moduleFile = $modulesPath . '/' . $directory . '/' . $directory . 'Module.php';
                    if (file_exists($moduleFile)) {
                        $modules[] = $directory;
                    }
                }
            }
        }
        
        return $modules;
    }
    
    /**
     * Load module configuration
     * 
     * @param string $moduleName Module name
     * @return array
     */
    public static function getModuleConfig(string $moduleName): array
    {
        if (!isset(self::$moduleConfigs[$moduleName])) {
            $configPath = APPPATH . 'Modules/' . $moduleName . '/Config/Module.php';
            
            if (file_exists($configPath)) {
                self::$moduleConfigs[$moduleName] = include $configPath;
            } else {
                self::$moduleConfigs[$moduleName] = [];
            }
        }
        
        return self::$moduleConfigs[$moduleName];
    }
    
    /**
     * Execute a module widget/component
     * 
     * @param string $module Module name
     * @param string $widget Widget name
     * @param array $params Parameters
     * @return string
     */
    public static function widget(string $module, string $widget, array $params = []): string
    {
        $widgetClass = "Modules\\{$module}\\Widgets\\{$widget}";
        
        if (!class_exists($widgetClass)) {
            throw new \RuntimeException("Widget {$widget} not found in module {$module}");
        }
        
        $widgetInstance = new $widgetClass();
        
        if (method_exists($widgetInstance, 'render')) {
            return $widgetInstance->render($params);
        }
        
        throw new \RuntimeException("Widget {$widget} does not have a render method");
    }
    
    /**
     * Get module routes
     * 
     * @param string $moduleName Module name
     * @return array
     */
    public static function getModuleRoutes(string $moduleName): array
    {
        $routesPath = APPPATH . 'Modules/' . $moduleName . '/Config/Routes.php';
        
        if (file_exists($routesPath)) {
            // Capture routes defined in the module
            ob_start();
            $routes = \Config\Services::routes();
            include $routesPath;
            ob_end_clean();
            
            return $routes->getRoutes();
        }
        
        return [];
    }
    
    /**
     * Initialize all available modules
     */
    public static function initializeModules(): void
    {
        $availableModules = self::getAvailableModules();
        
        foreach ($availableModules as $moduleName) {
            try {
                self::load($moduleName);
            } catch (\Exception $e) {
                log_message('error', 'Failed to initialize module ' . $moduleName . ': ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Clear all loaded modules
     */
    public static function clearAll(): void
    {
        self::$modules = [];
        self::$moduleConfigs = [];
    }
}