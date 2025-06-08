<?php

namespace App\Modules\PermissionCalendar\Config;

$routes = service('routes');

$routes->group('permissioncalendar', ['namespace' => 'App\Modules\PermissionCalendar\Controllers'], function ($routes) {
    $routes->get('/', 'Calendar::index');
    $routes->get('calendar', 'Calendar::index');
});