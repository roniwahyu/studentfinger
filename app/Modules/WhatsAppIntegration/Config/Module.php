<?php

// WhatsAppIntegration Module Configuration
return [
    /**
     * Module Information
     */
    'name' => 'WhatsApp Integration',
    'description' => 'Integrates WhatsApp messaging for attendance and permission notifications',
    'version' => '1.0.0',
    'author' => 'Student Management System',
    'dependencies' => ['StudentManagement', 'Attendance', 'PermissionCalendar'],
    'status' => 'active',
    
    /**
     * Module Routes Prefix
     */
    'routesPrefix' => 'whatsappintegration',
    
    /**
     * Default Controller
     */
    'defaultController' => 'WhatsApp',
    
    /**
     * Module Namespace
     */
    'namespace' => 'App\Modules\WhatsAppIntegration'
];