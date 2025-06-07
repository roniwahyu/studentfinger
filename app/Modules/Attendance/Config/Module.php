<?php

namespace App\Modules\Attendance\Config;

class Module
{
    /**
     * Module Information
     */
    public static $info = [
        'name' => 'Attendance Management',
        'description' => 'Comprehensive attendance tracking system with fingerprint, RFID, and manual attendance marking',
        'version' => '1.0.0',
        'author' => 'Student Management System',
        'dependencies' => ['StudentManagement'],
        'status' => 'active'
    ];
    
    /**
     * Module Routes Prefix
     */
    public static $routesPrefix = 'attendance';
    
    /**
     * Default Controller
     */
    public static $defaultController = 'Attendance';
    
    /**
     * Module Permissions
     */
    public static $permissions = [
        'attendance.view' => 'View Attendance Records',
        'attendance.mark' => 'Mark Attendance',
        'attendance.edit' => 'Edit Attendance Records',
        'attendance.delete' => 'Delete Attendance Records',
        'attendance.reports' => 'Generate Attendance Reports',
        'attendance.devices' => 'Manage Attendance Devices',
        'attendance.settings' => 'Manage Attendance Settings',
        'attendance.bulk_operations' => 'Perform Bulk Attendance Operations'
    ];
    
    /**
     * Module Settings
     */
    public static $settings = [
        // Attendance timing settings
        'school_start_time' => '08:00:00',
        'school_end_time' => '15:30:00',
        'late_threshold_minutes' => 15,
        'early_departure_threshold_minutes' => 30,
        
        // Attendance marking settings
        'allow_manual_attendance' => true,
        'require_approval_for_manual' => true,
        'allow_retroactive_marking' => true,
        'retroactive_days_limit' => 7,
        
        // Device settings
        'fingerprint_enabled' => true,
        'rfid_enabled' => true,
        'facial_recognition_enabled' => false,
        'device_sync_interval' => 300, // 5 minutes
        
        // Notification settings
        'send_attendance_notifications' => true,
        'send_absence_notifications' => true,
        'send_late_notifications' => true,
        'notification_delay_minutes' => 30,
        
        // Report settings
        'default_report_format' => 'pdf',
        'include_photos_in_reports' => true,
        'attendance_percentage_threshold' => 75,
        
        // Grace period settings
        'morning_grace_period' => 10, // minutes
        'afternoon_grace_period' => 10, // minutes
        
        // Holiday and weekend handling
        'mark_attendance_on_weekends' => false,
        'mark_attendance_on_holidays' => false,
        
        // Data retention
        'attendance_data_retention_years' => 5,
        'auto_archive_old_records' => true
    ];
    
    /**
     * Database Tables
     */
    public static $tables = [
        'attendance',
        'devices',
        'timetable',
        'holidays',
        'attendance_logs'
    ];
    
    /**
     * Validation Rules
     */
    public static $validationRules = [
        'attendance' => [
            'student_id' => 'required|integer|is_not_unique[students.id]',
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'check_in_time' => 'permit_empty|valid_date[H:i:s]',
            'check_out_time' => 'permit_empty|valid_date[H:i:s]',
            'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
            'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]',
            'notes' => 'permit_empty|max_length[500]'
        ],
        'device' => [
            'name' => 'required|max_length[100]',
            'type' => 'required|in_list[Fingerprint,RFID,Facial,Hybrid]',
            'location' => 'required|max_length[200]',
            'ip_address' => 'required|valid_ip',
            'port' => 'required|integer|greater_than[0]|less_than[65536]',
            'status' => 'required|in_list[Active,Inactive,Maintenance]'
        ],
        'timetable' => [
            'class_section_id' => 'required|integer',
            'day_of_week' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
            'start_time' => 'required|valid_date[H:i:s]',
            'end_time' => 'required|valid_date[H:i:s]',
            'subject' => 'permit_empty|max_length[100]'
        ]
    ];
    
    /**
     * Menu Items
     */
    public static $menuItems = [
        [
            'title' => 'Attendance',
            'icon' => 'fas fa-calendar-check',
            'route' => 'attendance.index',
            'permission' => 'attendance.view',
            'order' => 20,
            'children' => [
                [
                    'title' => 'Mark Attendance',
                    'icon' => 'fas fa-plus-circle',
                    'route' => 'attendance.mark',
                    'permission' => 'attendance.mark'
                ],
                [
                    'title' => 'View Records',
                    'icon' => 'fas fa-list',
                    'route' => 'attendance.index',
                    'permission' => 'attendance.view'
                ],
                [
                    'title' => 'Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'attendance.reports',
                    'permission' => 'attendance.reports'
                ],
                [
                    'title' => 'Devices',
                    'icon' => 'fas fa-fingerprint',
                    'route' => 'attendance.devices',
                    'permission' => 'attendance.devices'
                ],
                [
                    'title' => 'Timetable',
                    'icon' => 'fas fa-clock',
                    'route' => 'attendance.timetable',
                    'permission' => 'attendance.view'
                ]
            ]
        ]
    ];
    
    /**
     * Dashboard Widgets
     */
    public static $widgets = [
        [
            'name' => 'today_attendance',
            'title' => 'Today\'s Attendance',
            'description' => 'Current day attendance summary',
            'size' => 'col-md-3',
            'permission' => 'attendance.view',
            'refresh_interval' => 300 // 5 minutes
        ],
        [
            'name' => 'attendance_chart',
            'title' => 'Attendance Trend',
            'description' => 'Weekly attendance trend chart',
            'size' => 'col-md-6',
            'permission' => 'attendance.view',
            'refresh_interval' => 900 // 15 minutes
        ],
        [
            'name' => 'absent_students',
            'title' => 'Absent Students',
            'description' => 'List of students absent today',
            'size' => 'col-md-3',
            'permission' => 'attendance.view',
            'refresh_interval' => 600 // 10 minutes
        ],
        [
            'name' => 'device_status',
            'title' => 'Device Status',
            'description' => 'Attendance devices status overview',
            'size' => 'col-md-4',
            'permission' => 'attendance.devices',
            'refresh_interval' => 120 // 2 minutes
        ]
    ];
    
    /**
     * API Endpoints
     */
    public static $apiEndpoints = [
        'mark_attendance' => [
            'method' => 'POST',
            'route' => 'api/attendance/mark',
            'controller' => 'Api\\AttendanceController::mark',
            'middleware' => ['api_auth'],
            'rate_limit' => '60,1' // 60 requests per minute
        ],
        'get_attendance' => [
            'method' => 'GET',
            'route' => 'api/attendance/(:num)',
            'controller' => 'Api\\AttendanceController::show',
            'middleware' => ['api_auth']
        ],
        'attendance_report' => [
            'method' => 'GET',
            'route' => 'api/attendance/report',
            'controller' => 'Api\\AttendanceController::report',
            'middleware' => ['api_auth']
        ],
        'device_sync' => [
            'method' => 'POST',
            'route' => 'api/attendance/sync',
            'controller' => 'Api\\AttendanceController::sync',
            'middleware' => ['api_auth', 'device_auth']
        ],
        'bulk_attendance' => [
            'method' => 'POST',
            'route' => 'api/attendance/bulk',
            'controller' => 'Api\\AttendanceController::bulkMark',
            'middleware' => ['api_auth'],
            'rate_limit' => '10,1' // 10 requests per minute
        ]
    ];
    
    /**
     * Event Listeners
     */
    public static $eventListeners = [
        'attendance_marked' => [
            'App\\Modules\\Attendance\\Listeners\\AttendanceMarkedListener',
            'App\\Modules\\WhatsApp\\Listeners\\SendAttendanceNotification'
        ],
        'student_absent' => [
            'App\\Modules\\Attendance\\Listeners\\StudentAbsentListener',
            'App\\Modules\\WhatsApp\\Listeners\\SendAbsenceNotification'
        ],
        'late_arrival' => [
            'App\\Modules\\Attendance\\Listeners\\LateArrivalListener'
        ],
        'device_offline' => [
            'App\\Modules\\Attendance\\Listeners\\DeviceOfflineListener'
        ]
    ];
    
    /**
     * Scheduled Tasks
     */
    public static $scheduledTasks = [
        'sync_devices' => [
            'schedule' => '*/5 * * * *', // Every 5 minutes
            'command' => 'attendance:sync-devices',
            'description' => 'Sync attendance data from devices'
        ],
        'mark_absent_students' => [
            'schedule' => '0 10 * * 1-5', // 10 AM on weekdays
            'command' => 'attendance:mark-absent',
            'description' => 'Mark students as absent who haven\'t checked in'
        ],
        'send_daily_reports' => [
            'schedule' => '0 16 * * 1-5', // 4 PM on weekdays
            'command' => 'attendance:daily-report',
            'description' => 'Send daily attendance reports'
        ],
        'cleanup_old_logs' => [
            'schedule' => '0 2 1 * *', // 2 AM on first day of month
            'command' => 'attendance:cleanup-logs',
            'description' => 'Clean up old attendance logs'
        ]
    ];
    
    /**
     * Module Constants
     */
    public static $constants = [
        'ATTENDANCE_STATUSES' => [
            'PRESENT' => 'Present',
            'ABSENT' => 'Absent',
            'LATE' => 'Late',
            'EXCUSED' => 'Excused',
            'SICK' => 'Sick'
        ],
        'ATTENDANCE_TYPES' => [
            'FINGERPRINT' => 'Fingerprint',
            'RFID' => 'RFID',
            'MANUAL' => 'Manual',
            'FACIAL' => 'Facial'
        ],
        'DEVICE_TYPES' => [
            'FINGERPRINT' => 'Fingerprint',
            'RFID' => 'RFID',
            'FACIAL' => 'Facial',
            'HYBRID' => 'Hybrid'
        ],
        'DEVICE_STATUSES' => [
            'ACTIVE' => 'Active',
            'INACTIVE' => 'Inactive',
            'MAINTENANCE' => 'Maintenance'
        ]
    ];
}