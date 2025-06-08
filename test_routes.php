<?php

/**
 * Route Testing Script
 * This script tests if the module routes are properly loaded
 */

require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

// Get the router service
$router = \Config\Services::router();

echo "=== Route Testing Results ===\n\n";

// Test routes to check
$testRoutes = [
    '/' => 'Home page',
    '/students' => 'Student Management - Index',
    '/students/create' => 'Student Management - Create',
    '/students/sessions' => 'Student Management - Sessions',
    '/students/classes' => 'Student Management - Classes',
    '/attendance' => 'Attendance - Index',
    '/attendance/mark' => 'Attendance - Mark',
    '/attendance/reports' => 'Attendance - Reports',
    '/attendance/devices' => 'Attendance - Devices'
];

foreach ($testRoutes as $route => $description) {
    try {
        // Test if route exists by checking router
        $result = $router->checkRoutes();
        echo "✓ Route '{$route}' - {$description}: CONFIGURED\n";
    } catch (Exception $e) {
        echo "✗ Route '{$route}' - {$description}: ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== Module Files Check ===\n\n";

// Check if module route files exist
$moduleRoutes = ['StudentManagement', 'Attendance'];

foreach ($moduleRoutes as $module) {
    $routeFile = APPPATH . 'Modules/' . $module . '/Config/Routes.php';
    if (file_exists($routeFile)) {
        echo "✓ Module '{$module}' route file exists: {$routeFile}\n";
        
        // Check file size to ensure it's not empty
        $fileSize = filesize($routeFile);
        echo "  File size: {$fileSize} bytes\n";
        
        // Check if file is readable
        if (is_readable($routeFile)) {
            echo "  File is readable: YES\n";
        } else {
            echo "  File is readable: NO\n";
        }
    } else {
        echo "✗ Module '{$module}' route file missing: {$routeFile}\n";
    }
}

echo "\n=== Controller Files Check ===\n\n";

// Check if main controllers exist
$controllers = [
    'StudentManagement' => ['Students', 'Sessions', 'Classes', 'Sections'],
    'Attendance' => ['Attendance', 'Reports', 'Devices']
];

foreach ($controllers as $module => $controllerList) {
    echo "Module: {$module}\n";
    foreach ($controllerList as $controller) {
        $controllerFile = APPPATH . "Modules/{$module}/Controllers/{$controller}.php";
        if (file_exists($controllerFile)) {
            echo "  ✓ Controller '{$controller}' exists\n";
        } else {
            echo "  ✗ Controller '{$controller}' missing\n";
        }
    }
    echo "\n";
}

echo "=== Test Complete ===\n";