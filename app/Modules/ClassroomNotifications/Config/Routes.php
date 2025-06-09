<?php

/**
 * Classroom Notifications Module Routes
 */

$routes->group('classroom-notifications', ['namespace' => 'App\Modules\ClassroomNotifications\Controllers'], function($routes) {
    // Main dashboard
    $routes->get('/', 'NotificationController::index');
    $routes->get('dashboard', 'NotificationController::index');
    
    // Notification management
    $routes->get('templates', 'NotificationController::templates');
    $routes->get('templates/edit/(:num)', 'NotificationController::editTemplate/$1');
    $routes->post('templates/save', 'NotificationController::saveTemplate');
    $routes->post('templates/delete/(:num)', 'NotificationController::deleteTemplate/$1');
    
    // Class session management
    $routes->get('sessions', 'SessionController::index');
    $routes->get('sessions/create', 'SessionController::create');
    $routes->post('sessions/save', 'SessionController::save');
    $routes->get('sessions/edit/(:num)', 'SessionController::edit/$1');
    $routes->post('sessions/update/(:num)', 'SessionController::update/$1');
    $routes->post('sessions/delete/(:num)', 'SessionController::delete/$1');
    
    // Session control
    $routes->post('sessions/start/(:num)', 'SessionController::startSession/$1');
    $routes->post('sessions/break/(:num)', 'SessionController::breakSession/$1');
    $routes->post('sessions/resume/(:num)', 'SessionController::resumeSession/$1');
    $routes->post('sessions/finish/(:num)', 'SessionController::finishSession/$1');

    // Contact management
    $routes->get('contacts', 'ContactController::index');
    $routes->get('contacts/create', 'ContactController::create');
    $routes->post('contacts/save', 'ContactController::save');
    $routes->get('contacts/edit/(:num)', 'ContactController::edit/$1');
    $routes->post('contacts/update/(:num)', 'ContactController::update/$1');
    $routes->post('contacts/delete/(:num)', 'ContactController::delete/$1');
    $routes->post('contacts/set-primary', 'ContactController::setPrimary');
    $routes->post('contacts/bulk-update-preferences', 'ContactController::bulkUpdatePreferences');
    
    // Attendance tracking
    $routes->get('attendance/(:num)', 'AttendanceController::index/$1');
    $routes->post('attendance/mark', 'AttendanceController::markAttendance');
    $routes->post('attendance/bulk-mark', 'AttendanceController::bulkMarkAttendance');
    
    // Notification logs
    $routes->get('logs', 'NotificationController::logs');
    $routes->get('logs/session/(:num)', 'NotificationController::sessionLogs/$1');
    
    // Settings
    $routes->get('settings', 'NotificationController::settings');
    $routes->post('settings/save', 'NotificationController::saveSettings');
    $routes->post('settings/test', 'NotificationController::testNotification');
    
    // AJAX endpoints
    $routes->post('ajax/send-notification', 'NotificationController::sendNotification');
    $routes->post('ajax/get-students', 'SessionController::getStudents');
    $routes->get('ajax/session-status/(:num)', 'SessionController::getSessionStatus/$1');
    $routes->post('ajax/resend-notification', 'NotificationController::resendNotification');
    $routes->post('ajax/send-bulk-message', 'ContactController::sendBulkMessage');
    $routes->post('ajax/send-test-message', 'ContactController::sendTestMessage');
    $routes->get('ajax/search-contacts', 'ContactController::searchContacts');
    $routes->post('ajax/get-contacts-by-student', 'ContactController::getContactsByStudent');
    $routes->post('ajax/test-connection', 'NotificationController::testConnection');
});

// API routes
$routes->group('api/classroom-notifications', ['namespace' => 'App\Modules\ClassroomNotifications\Controllers'], function($routes) {
    $routes->get('stats', 'ApiController::getStats');
    $routes->get('sessions/active', 'ApiController::getActiveSessions');
    $routes->get('notifications/recent', 'ApiController::getRecentNotifications');
    $routes->post('webhook/attendance', 'ApiController::attendanceWebhook');
});
