<?php

/**
 * Wablas Integration Module Routes
 */

// Admin routes (protected)
$routes->group('wablas', ['namespace' => 'App\Modules\WablasIntegration\Controllers'], function($routes) {
    
    // Dashboard
    $routes->get('/', 'Dashboard::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('dashboard/stats', 'Dashboard::getStats');
    $routes->get('dashboard/recent-messages', 'Dashboard::getRecentMessages');
    $routes->get('dashboard/device-status', 'Dashboard::getDeviceStatus');
    
    // Device Management
    $routes->group('devices', function($routes) {
        $routes->get('/', 'DeviceController::index');
        $routes->get('create', 'DeviceController::create');
        $routes->post('store', 'DeviceController::store');
        $routes->get('edit/(:num)', 'DeviceController::edit/$1');
        $routes->post('update/(:num)', 'DeviceController::update/$1');
        $routes->delete('delete/(:num)', 'DeviceController::delete/$1');
        $routes->get('qr-code/(:num)', 'DeviceController::getQrCode/$1');
        $routes->post('test-connection/(:num)', 'DeviceController::testConnection/$1');
        $routes->post('restart/(:num)', 'DeviceController::restart/$1');
        $routes->post('disconnect/(:num)', 'DeviceController::disconnect/$1');
        $routes->get('status/(:num)', 'DeviceController::getStatus/$1');
        $routes->post('change-webhook/(:num)', 'DeviceController::changeWebhook/$1');
        $routes->post('change-tracking/(:num)', 'DeviceController::changeTracking/$1');
        $routes->post('generate-token/(:num)', 'DeviceController::generateToken/$1');
        $routes->post('set-speed/(:num)', 'DeviceController::setSpeed/$1');
    });
    
    // Message Management
    $routes->group('messages', function($routes) {
        $routes->get('/', 'MessageController::index');
        $routes->get('send', 'MessageController::sendForm');
        $routes->post('send', 'MessageController::send');
        $routes->get('bulk', 'MessageController::bulkForm');
        $routes->post('bulk', 'MessageController::sendBulk');
        $routes->get('scheduled', 'MessageController::scheduled');
        $routes->post('schedule', 'MessageController::schedule');
        $routes->get('history', 'MessageController::history');
        $routes->get('view/(:num)', 'MessageController::view/$1');
        $routes->post('resend/(:num)', 'MessageController::resend/$1');
        $routes->post('cancel/(:num)', 'MessageController::cancel/$1');
        $routes->delete('delete/(:num)', 'MessageController::delete/$1');
        $routes->get('export', 'MessageController::export');
        
        // Media messages
        $routes->post('send-image', 'MessageController::sendImage');
        $routes->post('send-document', 'MessageController::sendDocument');
        $routes->post('send-video', 'MessageController::sendVideo');
        $routes->post('send-audio', 'MessageController::sendAudio');
        $routes->post('send-location', 'MessageController::sendLocation');
        $routes->post('send-list', 'MessageController::sendList');
        
        // Group messages
        $routes->post('send-group', 'MessageController::sendGroup');
        $routes->get('groups', 'MessageController::groups');
    });
    
    // Contact Management
    $routes->group('contacts', function($routes) {
        $routes->get('/', 'ContactController::index');
        $routes->get('create', 'ContactController::create');
        $routes->post('store', 'ContactController::store');
        $routes->get('edit/(:num)', 'ContactController::edit/$1');
        $routes->post('update/(:num)', 'ContactController::update/$1');
        $routes->delete('delete/(:num)', 'ContactController::delete/$1');
        $routes->get('import', 'ContactController::importForm');
        $routes->post('import', 'ContactController::import');
        $routes->get('export', 'ContactController::export');
        $routes->get('groups', 'ContactController::groups');
        $routes->post('add-to-group', 'ContactController::addToGroup');
        $routes->post('remove-from-group', 'ContactController::removeFromGroup');
        $routes->get('search', 'ContactController::search');
        $routes->post('bulk-delete', 'ContactController::bulkDelete');
        $routes->post('bulk-update', 'ContactController::bulkUpdate');
    });
    
    // Auto Reply Management
    $routes->group('auto-reply', function($routes) {
        $routes->get('/', 'AutoReplyController::index');
        $routes->get('create', 'AutoReplyController::create');
        $routes->post('store', 'AutoReplyController::store');
        $routes->get('edit/(:num)', 'AutoReplyController::edit/$1');
        $routes->post('update/(:num)', 'AutoReplyController::update/$1');
        $routes->delete('delete/(:num)', 'AutoReplyController::delete/$1');
        $routes->post('toggle/(:num)', 'AutoReplyController::toggle/$1');
        $routes->get('test', 'AutoReplyController::test');
        $routes->post('test', 'AutoReplyController::processTest');
    });
    
    // Template Management
    $routes->group('templates', function($routes) {
        $routes->get('/', 'TemplateController::index');
        $routes->get('create', 'TemplateController::create');
        $routes->post('store', 'TemplateController::store');
        $routes->get('edit/(:num)', 'TemplateController::edit/$1');
        $routes->post('update/(:num)', 'TemplateController::update/$1');
        $routes->delete('delete/(:num)', 'TemplateController::delete/$1');
        $routes->get('preview/(:num)', 'TemplateController::preview/$1');
        $routes->post('duplicate/(:num)', 'TemplateController::duplicate/$1');
        $routes->get('categories', 'TemplateController::categories');
    });
    
    // Reports
    $routes->group('reports', function($routes) {
        $routes->get('/', 'ReportController::index');
        $routes->get('messages', 'ReportController::messages');
        $routes->get('devices', 'ReportController::devices');
        $routes->get('contacts', 'ReportController::contacts');
        $routes->get('analytics', 'ReportController::analytics');
        $routes->get('export/(:segment)', 'ReportController::export/$1');
        $routes->post('generate', 'ReportController::generate');
    });
    
    // Settings
    $routes->group('settings', function($routes) {
        $routes->get('/', 'SettingsController::index');
        $routes->post('update', 'SettingsController::update');
        $routes->get('webhooks', 'SettingsController::webhooks');
        $routes->post('webhooks/update', 'SettingsController::updateWebhooks');
        $routes->post('test-webhook', 'SettingsController::testWebhook');
        $routes->get('api-keys', 'SettingsController::apiKeys');
        $routes->post('api-keys/generate', 'SettingsController::generateApiKey');
        $routes->delete('api-keys/revoke/(:num)', 'SettingsController::revokeApiKey/$1');
    });
    
    // File Management
    $routes->group('files', function($routes) {
        $routes->post('upload', 'FileController::upload');
        $routes->get('download/(:segment)', 'FileController::download/$1');
        $routes->delete('delete/(:segment)', 'FileController::delete/$1');
        $routes->get('list', 'FileController::list');
    });
});

// API routes (for external integrations)
$routes->group('api/wablas', ['namespace' => 'App\Modules\WablasIntegration\Controllers'], function($routes) {
    
    // Authentication required routes
    $routes->group('', ['filter' => 'api_auth'], function($routes) {
        
        // Device API
        $routes->get('devices', 'ApiController::getDevices');
        $routes->get('devices/(:num)', 'ApiController::getDevice/$1');
        $routes->post('devices/(:num)/test', 'ApiController::testDevice/$1');
        
        // Message API
        $routes->post('messages/send', 'ApiController::sendMessage');
        $routes->post('messages/bulk', 'ApiController::sendBulkMessage');
        $routes->post('messages/schedule', 'ApiController::scheduleMessage');
        $routes->get('messages/status/(:segment)', 'ApiController::getMessageStatus/$1');
        $routes->post('messages/cancel/(:segment)', 'ApiController::cancelMessage/$1');
        
        // Contact API
        $routes->get('contacts', 'ApiController::getContacts');
        $routes->post('contacts', 'ApiController::createContact');
        $routes->put('contacts/(:num)', 'ApiController::updateContact/$1');
        $routes->delete('contacts/(:num)', 'ApiController::deleteContact/$1');
        
        // Reports API
        $routes->get('reports/messages', 'ApiController::getMessageReport');
        $routes->get('reports/devices', 'ApiController::getDeviceReport');
        
        // Phone verification
        $routes->post('verify-phone', 'ApiController::verifyPhone');
    });
});

// Webhook routes (public, but secured)
$routes->group('wablas/webhook', ['namespace' => 'App\Modules\WablasIntegration\Controllers'], function($routes) {
    $routes->post('incoming', 'WebhookController::incoming');
    $routes->post('status', 'WebhookController::messageStatus');
    $routes->post('device', 'WebhookController::deviceStatus');
    $routes->post('test', 'WebhookController::test');
});

// Public routes (for QR code display, etc.)
$routes->group('wablas/public', ['namespace' => 'App\Modules\WablasIntegration\Controllers'], function($routes) {
    $routes->get('qr/(:segment)', 'PublicController::qrCode/$1');
    $routes->get('status', 'PublicController::status');
});

// Installation routes (for module setup)
$routes->group('wablas/install', ['namespace' => 'App\Modules\WablasIntegration\Controllers'], function($routes) {
    $routes->get('/', 'InstallController::index');
    $routes->post('run', 'InstallController::install');
    $routes->post('uninstall', 'InstallController::uninstall');
    $routes->get('check', 'InstallController::checkRequirements');
    $routes->post('migrate', 'InstallController::migrate');
    $routes->post('seed', 'InstallController::seed');
});
