<?php

namespace App\Modules\Home\Config;

$routes = service('routes');

$routes->group('home', ['namespace' => 'App\Modules\Home\Controllers'], function ($routes) {
    $routes->get('/', 'Home::index');
});