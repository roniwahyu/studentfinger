<?php

/**
 * WablasFrontEnd Module Routes
 * Integrated with main Student Finger dashboard
 */

// WablasFrontEnd Routes - Integrated with main dashboard
$routes->group('wablas-frontend', ['namespace' => 'App\Modules\WablasFrontEnd\Controllers'], function($routes) {
    // Main dashboard integration
    $routes->get('/', 'WablasController::dashboard');
    $routes->get('dashboard', 'WablasController::dashboard');
    
    // Device management
    $routes->get('devices', 'WablasController::devices');
    $routes->post('devices/scan-qr', 'WablasController::scanQR');
    $routes->post('devices/logout', 'WablasController::logoutDevice');
    $routes->get('devices/status', 'WablasController::deviceStatus');
    
    // Message management
    $routes->get('messages', 'WablasController::messages');
    $routes->get('messages/sent', 'WablasController::sentMessages');
    $routes->get('messages/failed', 'WablasController::failedMessages');
    $routes->post('messages/send', 'WablasController::sendMessage');
    $routes->post('messages/send-bulk', 'WablasController::sendBulkMessage');
    $routes->post('messages/resend/(:num)', 'WablasController::resendMessage/$1');
    
    // Contact management integration
    $routes->get('contacts', 'WablasController::contacts');
    $routes->get('contacts/import', 'WablasController::importContacts');
    $routes->post('contacts/import-csv', 'WablasController::importContactsCSV');
    $routes->get('contacts/export', 'WablasController::exportContacts');
    
    // Templates integration
    $routes->get('templates', 'WablasController::templates');
    $routes->post('templates/save', 'WablasController::saveTemplate');
    $routes->get('templates/edit/(:num)', 'WablasController::editTemplate/$1');
    $routes->post('templates/delete/(:num)', 'WablasController::deleteTemplate/$1');
    
    // Broadcast management
    $routes->get('broadcast', 'WablasController::broadcast');
    $routes->post('broadcast/send', 'WablasController::sendBroadcast');
    $routes->get('broadcast/history', 'WablasController::broadcastHistory');
    $routes->get('broadcast/schedule', 'WablasController::scheduleBroadcast');
    
    // Analytics and reports
    $routes->get('analytics', 'WablasController::analytics');
    $routes->get('reports', 'WablasController::reports');
    $routes->get('reports/export', 'WablasController::exportReports');
    
    // Settings integration
    $routes->get('settings', 'WablasController::settings');
    $routes->post('settings/save', 'WablasController::saveSettings');
    $routes->post('settings/test-connection', 'WablasController::testConnection');
    
    // API endpoints
    $routes->post('api/webhook', 'WablasController::webhook');
    $routes->get('api/device-status', 'WablasController::getDeviceStatus');
    $routes->post('api/send-message', 'WablasController::apiSendMessage');
    $routes->get('api/message-status/(:segment)', 'WablasController::getMessageStatus/$1');
    
    // Integration with classroom notifications
    $routes->get('integration/classroom', 'WablasController::classroomIntegration');
    $routes->post('integration/sync-contacts', 'WablasController::syncContacts');
    $routes->post('integration/sync-templates', 'WablasController::syncTemplates');
    
    // AJAX endpoints
    $routes->post('ajax/get-device-info', 'WablasController::getDeviceInfo');
    $routes->post('ajax/refresh-status', 'WablasController::refreshStatus');
    $routes->post('ajax/send-test-message', 'WablasController::sendTestMessage');
    $routes->get('ajax/message-logs', 'WablasController::getMessageLogs');
});
