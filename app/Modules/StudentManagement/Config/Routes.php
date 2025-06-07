<?php

/**
 * Student Management Module Routes
 * 
 * This file defines all routes for the Student Management module.
 */

// Student Management Routes
$routes->group('students', ['namespace' => 'Modules\StudentManagement\Controllers'], function($routes) {
    // Student Routes
    $routes->get('/', 'Students::index');
    $routes->get('index', 'Students::index');
    $routes->get('create', 'Students::create');
    $routes->post('store', 'Students::store');
    $routes->get('show/(:num)', 'Students::show/$1');
    $routes->get('edit/(:num)', 'Students::edit/$1');
    $routes->post('update/(:num)', 'Students::update/$1');
    $routes->delete('delete/(:num)', 'Students::delete/$1');
    $routes->post('bulk-delete', 'Students::bulkDelete');
    $routes->get('export', 'Students::export');
    $routes->post('import', 'Students::import');
    $routes->get('search', 'Students::search');
    $routes->get('by-class/(:num)', 'Students::getByClass/$1');
    $routes->get('by-session/(:num)', 'Students::getBySession/$1');
    
    // Session Routes
    $routes->group('sessions', function($routes) {
        $routes->get('/', 'Sessions::index');
        $routes->get('create', 'Sessions::create');
        $routes->post('store', 'Sessions::store');
        $routes->get('show/(:num)', 'Sessions::show/$1');
        $routes->get('edit/(:num)', 'Sessions::edit/$1');
        $routes->post('update/(:num)', 'Sessions::update/$1');
        $routes->delete('delete/(:num)', 'Sessions::delete/$1');
        $routes->post('activate/(:num)', 'Sessions::activate/$1');
        $routes->get('active', 'Sessions::getActive');
    });
    
    // Class Routes
    $routes->group('classes', function($routes) {
        $routes->get('/', 'Classes::index');
        $routes->get('create', 'Classes::create');
        $routes->post('store', 'Classes::store');
        $routes->get('show/(:num)', 'Classes::show/$1');
        $routes->get('edit/(:num)', 'Classes::edit/$1');
        $routes->post('update/(:num)', 'Classes::update/$1');
        $routes->delete('delete/(:num)', 'Classes::delete/$1');
        $routes->get('with-sections', 'Classes::getWithSections');
        $routes->get('(:num)/sections', 'Classes::getSections/$1');
    });
    
    // Section Routes
    $routes->group('sections', function($routes) {
        $routes->get('/', 'Sections::index');
        $routes->get('create', 'Sections::create');
        $routes->post('store', 'Sections::store');
        $routes->get('show/(:num)', 'Sections::show/$1');
        $routes->get('edit/(:num)', 'Sections::edit/$1');
        $routes->post('update/(:num)', 'Sections::update/$1');
        $routes->delete('delete/(:num)', 'Sections::delete/$1');
        $routes->get('by-class/(:num)', 'Sections::getByClass/$1');
    });
    
    // Class-Section Assignment Routes
    $routes->group('class-sections', function($routes) {
        $routes->get('/', 'ClassSections::index');
        $routes->get('create', 'ClassSections::create');
        $routes->post('store', 'ClassSections::store');
        $routes->get('edit/(:num)', 'ClassSections::edit/$1');
        $routes->post('update/(:num)', 'ClassSections::update/$1');
        $routes->delete('delete/(:num)', 'ClassSections::delete/$1');
        $routes->get('class/(:num)', 'ClassSections::getByClass/$1');
        $routes->get('section/(:num)', 'ClassSections::getBySection/$1');
    });
    
    // Student-Session Assignment Routes
    $routes->group('student-sessions', function($routes) {
        $routes->get('/', 'StudentSessions::index');
        $routes->get('create', 'StudentSessions::create');
        $routes->post('store', 'StudentSessions::store');
        $routes->get('edit/(:num)', 'StudentSessions::edit/$1');
        $routes->post('update/(:num)', 'StudentSessions::update/$1');
        $routes->delete('delete/(:num)', 'StudentSessions::delete/$1');
        $routes->post('bulk-assign', 'StudentSessions::bulkAssign');
        $routes->get('student/(:num)', 'StudentSessions::getByStudent/$1');
        $routes->get('session/(:num)', 'StudentSessions::getBySession/$1');
    });
});

// API Routes for Student Management
$routes->group('api/students', ['namespace' => 'Modules\StudentManagement\Controllers\Api'], function($routes) {
    // Student API
    $routes->get('/', 'StudentsApi::index');
    $routes->get('(:num)', 'StudentsApi::show/$1');
    $routes->post('/', 'StudentsApi::create');
    $routes->put('(:num)', 'StudentsApi::update/$1');
    $routes->delete('(:num)', 'StudentsApi::delete/$1');
    $routes->get('search/(:any)', 'StudentsApi::search/$1');
    $routes->get('by-rfid/(:any)', 'StudentsApi::getByRfid/$1');
    $routes->get('by-student-id/(:any)', 'StudentsApi::getByStudentId/$1');
    
    // Session API
    $routes->group('sessions', function($routes) {
        $routes->get('/', 'SessionsApi::index');
        $routes->get('active', 'SessionsApi::getActive');
        $routes->get('(:num)', 'SessionsApi::show/$1');
        $routes->post('/', 'SessionsApi::create');
        $routes->put('(:num)', 'SessionsApi::update/$1');
        $routes->delete('(:num)', 'SessionsApi::delete/$1');
    });
    
    // Class API
    $routes->group('classes', function($routes) {
        $routes->get('/', 'ClassesApi::index');
        $routes->get('(:num)', 'ClassesApi::show/$1');
        $routes->post('/', 'ClassesApi::create');
        $routes->put('(:num)', 'ClassesApi::update/$1');
        $routes->delete('(:num)', 'ClassesApi::delete/$1');
        $routes->get('(:num)/sections', 'ClassesApi::getSections/$1');
    });
    
    // Section API
    $routes->group('sections', function($routes) {
        $routes->get('/', 'SectionsApi::index');
        $routes->get('(:num)', 'SectionsApi::show/$1');
        $routes->post('/', 'SectionsApi::create');
        $routes->put('(:num)', 'SectionsApi::update/$1');
        $routes->delete('(:num)', 'SectionsApi::delete/$1');
    });
});

// Widget Routes
$routes->group('widgets/students', ['namespace' => 'Modules\StudentManagement\Controllers'], function($routes) {
    $routes->get('stats', 'Widgets::studentStats');
    $routes->get('recent', 'Widgets::recentStudents');
    $routes->get('by-class', 'Widgets::studentsByClass');
});