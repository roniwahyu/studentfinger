<?php

namespace App\Modules\FingerprintBridge\Config;

$routes = service('routes');

// FingerprintBridge Module Routes
$routes->group('fingerprint-bridge', ['namespace' => 'App\Modules\FingerprintBridge\Controllers'], function ($routes) {
    // Dashboard
    $routes->get('/', 'ImportController::index');
    $routes->get('dashboard', 'ImportController::index');
    
    // Manual Import
    $routes->get('manual-import', 'ImportController::manualImport');
    $routes->post('manual-import', 'ImportController::processManualImport');
    $routes->post('import', 'ImportController::import');
    
    // Import Logs
    $routes->get('logs', 'ImportController::logs');
    $routes->get('logs/(:num)', 'ImportController::logDetail/$1');
    $routes->delete('logs/(:num)', 'ImportController::deleteLog/$1');
    
    // Settings
    $routes->get('settings', 'ImportController::settings');
    $routes->post('settings', 'ImportController::saveSettings');
    
    // Student PIN Mapping
    $routes->get('pin-mapping', 'ImportController::pinMapping');
    $routes->post('pin-mapping', 'ImportController::savePinMapping');
    $routes->delete('pin-mapping/(:num)', 'ImportController::deletePinMapping/$1');
    
    // AJAX endpoints
    $routes->post('ajax/test-connection', 'ImportController::testConnection');
    $routes->post('ajax/preview-import', 'ImportController::previewImport');
    $routes->get('ajax/import-status', 'ImportController::importStatus');
    $routes->post('ajax/stop-import', 'ImportController::stopImport');
});

// API Routes
$routes->group('api/fingerprint-bridge', ['namespace' => 'App\Modules\FingerprintBridge\Controllers'], function ($routes) {
    $routes->post('import', 'ApiController::import');
    $routes->get('status', 'ApiController::status');
    $routes->get('logs', 'ApiController::logs');
    $routes->get('stats', 'ApiController::stats');
});
