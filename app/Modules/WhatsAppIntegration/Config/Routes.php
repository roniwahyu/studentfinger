<?php

namespace App\Modules\WhatsAppIntegration\Config;

$routes = service('routes');

$routes->group('whatsappintegration', ['namespace' => 'App\Modules\WhatsAppIntegration\Controllers'], function ($routes) {
    $routes->get('/', 'WhatsApp::index');
    $routes->get('whatsapp', 'WhatsApp::index');
});