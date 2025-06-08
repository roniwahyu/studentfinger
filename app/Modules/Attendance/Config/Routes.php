<?php

namespace App\Modules\Attendance\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Attendance Module Routes
$routes->group('attendance', ['namespace' => 'Modules\Attendance\Controllers'], function($routes) {
    
    // Main Attendance Routes
    $routes->get('/', 'Attendance::index', ['as' => 'attendance.index']);
    $routes->get('mark', 'Attendance::mark', ['as' => 'attendance.mark']);
    $routes->post('mark', 'Attendance::store', ['as' => 'attendance.store']);
    $routes->get('show/(:num)', 'Attendance::show/$1', ['as' => 'attendance.show']);
    $routes->get('edit/(:num)', 'Attendance::edit/$1', ['as' => 'attendance.edit']);
    $routes->put('update/(:num)', 'Attendance::update/$1', ['as' => 'attendance.update']);
    $routes->delete('delete/(:num)', 'Attendance::delete/$1', ['as' => 'attendance.delete']);
    $routes->post('bulk-delete', 'Attendance::bulkDelete', ['as' => 'attendance.bulk_delete']);
    $routes->post('bulk-mark', 'Attendance::bulkMark', ['as' => 'attendance.bulk_mark']);
    $routes->get('export', 'Attendance::export', ['as' => 'attendance.export']);
    $routes->post('import', 'Attendance::import', ['as' => 'attendance.import']);
    
    // Attendance Reports
    $routes->group('reports', function($routes) {
        $routes->get('/', 'Reports::index', ['as' => 'attendance.reports.index']);
        $routes->get('daily', 'Reports::daily', ['as' => 'attendance.reports.daily']);
        $routes->get('weekly', 'Reports::weekly', ['as' => 'attendance.reports.weekly']);
        $routes->get('monthly', 'Reports::monthly', ['as' => 'attendance.reports.monthly']);
        $routes->get('student/(:num)', 'Reports::student/$1', ['as' => 'attendance.reports.student']);
        $routes->get('class/(:num)', 'Reports::class/$1', ['as' => 'attendance.reports.class']);
        $routes->get('summary', 'Reports::summary', ['as' => 'attendance.reports.summary']);
        $routes->post('generate', 'Reports::generate', ['as' => 'attendance.reports.generate']);
        $routes->get('download/(:segment)', 'Reports::download/$1', ['as' => 'attendance.reports.download']);
    });
    
    // Device Management
    $routes->group('devices', function($routes) {
        $routes->get('/', 'Devices::index', ['as' => 'attendance.devices.index']);
        $routes->get('create', 'Devices::create', ['as' => 'attendance.devices.create']);
        $routes->post('store', 'Devices::store', ['as' => 'attendance.devices.store']);
        $routes->get('show/(:num)', 'Devices::show/$1', ['as' => 'attendance.devices.show']);
        $routes->get('edit/(:num)', 'Devices::edit/$1', ['as' => 'attendance.devices.edit']);
        $routes->put('update/(:num)', 'Devices::update/$1', ['as' => 'attendance.devices.update']);
        $routes->delete('delete/(:num)', 'Devices::delete/$1', ['as' => 'attendance.devices.delete']);
        $routes->post('sync/(:num)', 'Devices::sync/$1', ['as' => 'attendance.devices.sync']);
        $routes->post('sync-all', 'Devices::syncAll', ['as' => 'attendance.devices.sync_all']);
        $routes->get('status', 'Devices::status', ['as' => 'attendance.devices.status']);
        $routes->post('test-connection/(:num)', 'Devices::testConnection/$1', ['as' => 'attendance.devices.test']);
    });
    
    // Timetable Management
    $routes->group('timetable', function($routes) {
        $routes->get('/', 'Timetable::index', ['as' => 'attendance.timetable.index']);
        $routes->get('create', 'Timetable::create', ['as' => 'attendance.timetable.create']);
        $routes->post('store', 'Timetable::store', ['as' => 'attendance.timetable.store']);
        $routes->get('show/(:num)', 'Timetable::show/$1', ['as' => 'attendance.timetable.show']);
        $routes->get('edit/(:num)', 'Timetable::edit/$1', ['as' => 'attendance.timetable.edit']);
        $routes->put('update/(:num)', 'Timetable::update/$1', ['as' => 'attendance.timetable.update']);
        $routes->delete('delete/(:num)', 'Timetable::delete/$1', ['as' => 'attendance.timetable.delete']);
        $routes->get('class/(:num)', 'Timetable::byClass/$1', ['as' => 'attendance.timetable.class']);
        $routes->post('bulk-create', 'Timetable::bulkCreate', ['as' => 'attendance.timetable.bulk_create']);
        $routes->get('template', 'Timetable::template', ['as' => 'attendance.timetable.template']);
    });
    
    // Holiday Management
    $routes->group('holidays', function($routes) {
        $routes->get('/', 'Holidays::index', ['as' => 'attendance.holidays.index']);
        $routes->get('create', 'Holidays::create', ['as' => 'attendance.holidays.create']);
        $routes->post('store', 'Holidays::store', ['as' => 'attendance.holidays.store']);
        $routes->get('show/(:num)', 'Holidays::show/$1', ['as' => 'attendance.holidays.show']);
        $routes->get('edit/(:num)', 'Holidays::edit/$1', ['as' => 'attendance.holidays.edit']);
        $routes->put('update/(:num)', 'Holidays::update/$1', ['as' => 'attendance.holidays.update']);
        $routes->delete('delete/(:num)', 'Holidays::delete/$1', ['as' => 'attendance.holidays.delete']);
        $routes->get('calendar', 'Holidays::calendar', ['as' => 'attendance.holidays.calendar']);
    });
    
    // Settings
    $routes->group('settings', function($routes) {
        $routes->get('/', 'Settings::index', ['as' => 'attendance.settings.index']);
        $routes->post('update', 'Settings::update', ['as' => 'attendance.settings.update']);
        $routes->post('reset', 'Settings::reset', ['as' => 'attendance.settings.reset']);
    });
    
    // AJAX Routes
    $routes->group('ajax', function($routes) {
        $routes->post('mark-attendance', 'Ajax::markAttendance', ['as' => 'attendance.ajax.mark']);
        $routes->get('student-attendance/(:num)', 'Ajax::getStudentAttendance/$1', ['as' => 'attendance.ajax.student']);
        $routes->get('class-attendance/(:num)', 'Ajax::getClassAttendance/$1', ['as' => 'attendance.ajax.class']);
        $routes->get('attendance-stats', 'Ajax::getAttendanceStats', ['as' => 'attendance.ajax.stats']);
        $routes->get('device-status/(:num)', 'Ajax::getDeviceStatus/$1', ['as' => 'attendance.ajax.device_status']);
        $routes->post('sync-device/(:num)', 'Ajax::syncDevice/$1', ['as' => 'attendance.ajax.sync_device']);
        $routes->get('absent-students', 'Ajax::getAbsentStudents', ['as' => 'attendance.ajax.absent_students']);
        $routes->get('late-students', 'Ajax::getLateStudents', ['as' => 'attendance.ajax.late_students']);
    });
});

// API Routes for Attendance Module
$routes->group('api/attendance', ['namespace' => 'Modules\Attendance\Controllers\Api'], function($routes) {
    
    // Authentication required for all API routes
    $routes->group('', ['filter' => 'api_auth'], function($routes) {
        
        // Attendance CRUD
        $routes->get('/', 'AttendanceApi::index');
        $routes->post('/', 'AttendanceApi::create');
        $routes->get('(:num)', 'AttendanceApi::show/$1');
        $routes->put('(:num)', 'AttendanceApi::update/$1');
        $routes->delete('(:num)', 'AttendanceApi::delete/$1');
        
        // Attendance Marking
        $routes->post('mark', 'AttendanceApi::mark');
        $routes->post('bulk-mark', 'AttendanceApi::bulkMark');
        $routes->post('fingerprint', 'AttendanceApi::markByFingerprint');
        $routes->post('rfid', 'AttendanceApi::markByRfid');
        $routes->post('facial', 'AttendanceApi::markByFacial');
        
        // Reports
        $routes->get('report/daily', 'AttendanceApi::dailyReport');
        $routes->get('report/weekly', 'AttendanceApi::weeklyReport');
        $routes->get('report/monthly', 'AttendanceApi::monthlyReport');
        $routes->get('report/student/(:num)', 'AttendanceApi::studentReport/$1');
        $routes->get('report/class/(:num)', 'AttendanceApi::classReport/$1');
        
        // Statistics
        $routes->get('stats', 'AttendanceApi::getStatistics');
        $routes->get('stats/today', 'AttendanceApi::getTodayStats');
        $routes->get('stats/student/(:num)', 'AttendanceApi::getStudentStats/$1');
        $routes->get('stats/class/(:num)', 'AttendanceApi::getClassStats/$1');
        
        // Device Management
        $routes->get('devices', 'DeviceApi::index');
        $routes->post('devices', 'DeviceApi::create');
        $routes->get('devices/(:num)', 'DeviceApi::show/$1');
        $routes->put('devices/(:num)', 'DeviceApi::update/$1');
        $routes->delete('devices/(:num)', 'DeviceApi::delete/$1');
        $routes->post('devices/(:num)/sync', 'DeviceApi::sync/$1');
        $routes->get('devices/(:num)/status', 'DeviceApi::getStatus/$1');
        $routes->post('devices/(:num)/test', 'DeviceApi::testConnection/$1');
        
        // Timetable
        $routes->get('timetable', 'TimetableApi::index');
        $routes->post('timetable', 'TimetableApi::create');
        $routes->get('timetable/(:num)', 'TimetableApi::show/$1');
        $routes->put('timetable/(:num)', 'TimetableApi::update/$1');
        $routes->delete('timetable/(:num)', 'TimetableApi::delete/$1');
        $routes->get('timetable/class/(:num)', 'TimetableApi::getByClass/$1');
        $routes->get('timetable/today', 'TimetableApi::getToday');
        
        // Holidays
        $routes->get('holidays', 'HolidayApi::index');
        $routes->post('holidays', 'HolidayApi::create');
        $routes->get('holidays/(:num)', 'HolidayApi::show/$1');
        $routes->put('holidays/(:num)', 'HolidayApi::update/$1');
        $routes->delete('holidays/(:num)', 'HolidayApi::delete/$1');
        $routes->get('holidays/check/(:segment)', 'HolidayApi::checkDate/$1');
    });
    
    // Device-specific routes (different authentication)
    $routes->group('device', ['filter' => 'device_auth'], function($routes) {
        $routes->post('sync', 'DeviceApi::syncData');
        $routes->post('heartbeat', 'DeviceApi::heartbeat');
        $routes->post('attendance', 'DeviceApi::receiveAttendance');
        $routes->get('config', 'DeviceApi::getConfig');
        $routes->post('log', 'DeviceApi::logEvent');
    });
});

// Widget Routes for Dashboard
$routes->group('widgets/attendance', ['namespace' => 'Modules\Attendance\Controllers\Widgets'], function($routes) {
    $routes->get('today-summary', 'AttendanceWidgets::todaySummary', ['as' => 'widgets.attendance.today_summary']);
    $routes->get('attendance-chart', 'AttendanceWidgets::attendanceChart', ['as' => 'widgets.attendance.chart']);
    $routes->get('absent-students', 'AttendanceWidgets::absentStudents', ['as' => 'widgets.attendance.absent_students']);
    $routes->get('device-status', 'AttendanceWidgets::deviceStatus', ['as' => 'widgets.attendance.device_status']);
    $routes->get('late-arrivals', 'AttendanceWidgets::lateArrivals', ['as' => 'widgets.attendance.late_arrivals']);
    $routes->get('attendance-trends', 'AttendanceWidgets::attendanceTrends', ['as' => 'widgets.attendance.trends']);
});

// Webhook Routes (for external integrations)
$routes->group('webhooks/attendance', ['namespace' => 'Modules\Attendance\Controllers\Webhooks'], function($routes) {
    $routes->post('fingerspot', 'AttendanceWebhooks::fingerspot', ['as' => 'webhooks.attendance.fingerspot']);
    $routes->post('rfid-reader', 'AttendanceWebhooks::rfidReader', ['as' => 'webhooks.attendance.rfid']);
    $routes->post('facial-recognition', 'AttendanceWebhooks::facialRecognition', ['as' => 'webhooks.attendance.facial']);
    $routes->post('third-party', 'AttendanceWebhooks::thirdParty', ['as' => 'webhooks.attendance.third_party']);
});

// CLI Routes (for scheduled tasks)
$routes->cli('attendance/sync-devices', 'Modules\Attendance\Commands\SyncDevices::run');
$routes->cli('attendance/mark-absent', 'Modules\Attendance\Commands\MarkAbsentStudents::run');
$routes->cli('attendance/daily-report', 'Modules\Attendance\Commands\SendDailyReport::run');
$routes->cli('attendance/cleanup-logs', 'Modules\Attendance\Commands\CleanupLogs::run');
$routes->cli('attendance/backup-data', 'Modules\Attendance\Commands\BackupAttendanceData::run');