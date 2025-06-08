<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Home routes
$routes->get('/', 'HomeController::index');
$routes->get('home', 'HomeController::index');

// Student Management routes
$routes->group('students', function($routes) {
    $routes->get('/', 'StudentsController::index');
    $routes->get('create', 'StudentsController::create');
    $routes->post('store', 'StudentsController::store');
    $routes->get('show/(:num)', 'StudentsController::show/$1');
    $routes->get('edit/(:num)', 'StudentsController::edit/$1');
    $routes->post('update/(:num)', 'StudentsController::update/$1');
    $routes->delete('delete/(:num)', 'StudentsController::delete/$1');
    $routes->get('search', 'StudentsController::search');
    $routes->get('filter', 'StudentsController::filter');
});

// Attendance routes
$routes->group('attendance', function($routes) {
    $routes->get('/', 'AttendanceController::index');
    $routes->get('mark', 'AttendanceController::mark');
    $routes->post('store', 'AttendanceController::store');
    $routes->get('report', 'AttendanceController::report');
    $routes->get('student/(:num)', 'AttendanceController::studentAttendance/$1');
    $routes->get('class/(:num)', 'AttendanceController::classAttendance/$1');
    $routes->get('export', 'AttendanceController::export');
});
