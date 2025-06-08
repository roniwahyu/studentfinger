<?php

// PermissionCalendar Module Configuration
return [
    /**
     * Module Information
     */
    'name' => 'Permission Calendar',
    'description' => 'Manages student permissions and school calendar',
    'version' => '1.0.0',
    'author' => 'Student Management System',
    'dependencies' => ['StudentManagement'],
    'status' => 'active',
    
    /**
     * Module Routes Prefix
     */
    'routesPrefix' => 'permissioncalendar',
    
    /**
     * Default Controller
     */
    'defaultController' => 'Calendar',
    
    /**
     * Module Namespace
     */
    'namespace' => 'App\Modules\PermissionCalendar'
];