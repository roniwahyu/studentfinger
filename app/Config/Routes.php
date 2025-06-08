<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

// Load module routes
$moduleRoutes = [
    'StudentManagement',
    'Attendance',
    'Home',
    'PermissionCalendar',
    'WhatsAppIntegration'
];

foreach ($moduleRoutes as $module) {
    $routeFile = APPPATH . 'Modules/' . $module . '/Config/Routes.php';
    if (file_exists($routeFile)) {
        require_once $routeFile;
    }
}
