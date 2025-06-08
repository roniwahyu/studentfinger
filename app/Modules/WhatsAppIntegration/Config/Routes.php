<?php

namespace App\Modules\WhatsAppIntegration\Config;

$routes = service('routes');

$routes->group('whatsappintegration', ['namespace' => 'App\Modules\WhatsAppIntegration\Controllers'], function ($routes) {

    // Main WhatsApp Gateway Routes
    $routes->get('/', 'WhatsApp::index');
    $routes->get('dashboard', 'WhatsApp::index');

    // Message Management
    $routes->get('send', 'WhatsApp::sendMessage');
    $routes->post('send', 'WhatsApp::sendMessage');
    $routes->get('bulk', 'WhatsApp::sendBulkMessage');
    $routes->post('bulk', 'WhatsApp::sendBulkMessage');
    $routes->get('queue', 'WhatsApp::messageQueue');
    $routes->post('process-queue', 'WhatsApp::processQueue');

    // Device Management Routes
    $routes->group('devices', function($routes) {
        $routes->get('/', 'DeviceController::index');
        $routes->get('create', 'DeviceController::create');
        $routes->post('create', 'DeviceController::create');
        $routes->get('edit/(:num)', 'DeviceController::edit/$1');
        $routes->post('edit/(:num)', 'DeviceController::edit/$1');
        $routes->get('delete/(:num)', 'DeviceController::delete/$1');
        $routes->post('test-connection/(:num)', 'DeviceController::testConnection/$1');
        $routes->post('toggle-status/(:num)', 'DeviceController::toggleStatus/$1');
        $routes->get('stats/(:num)', 'DeviceController::getStats/$1');
        $routes->post('sync-contacts/(:num)', 'DeviceController::syncContacts/$1');
        $routes->get('qr-code/(:num)', 'DeviceController::getQRCode/$1');
    });

    // Webhook Routes (public endpoints)
    $routes->group('webhook', function($routes) {
        $routes->post('(:segment)', 'WebhookController::handle/$1');
        $routes->get('(:segment)', 'WebhookController::verify/$1');
        $routes->get('test', 'WebhookController::test');
        $routes->get('logs', 'WebhookController::logs');
        $routes->get('logs/(:num)', 'WebhookController::logs/$1');
    });

    // API Routes for external integration
    $routes->group('api', function($routes) {
        $routes->post('send-message', 'ApiController::sendMessage');
        $routes->post('send-bulk', 'ApiController::sendBulk');
        $routes->get('device-status/(:num)', 'ApiController::deviceStatus/$1');
        $routes->get('message-status/(:num)', 'ApiController::messageStatus/$1');
        $routes->get('contacts', 'ApiController::contacts');
        $routes->get('templates', 'ApiController::templates');
        $routes->post('schedule-message', 'ApiController::scheduleMessage');
        $routes->get('queue-status', 'ApiController::queueStatus');
    });
});

// Additional routes for integration with main application
$routes->group('wa', ['namespace' => 'App\Modules\WhatsAppIntegration\Controllers'], function($routes) {
    // Quick access routes
    $routes->post('send', 'ApiController::sendMessage');
    $routes->post('notify-attendance', 'AttendanceIntegrationController::notifyAttendance');
    $routes->get('status', 'ApiController::systemStatus');
});