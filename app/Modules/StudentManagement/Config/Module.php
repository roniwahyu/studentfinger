<?php

/**
 * Student Management Module Configuration
 * 
 * This file contains configuration settings specific to the Student Management module.
 */

return [
    /**
     * Module Information
     */
    'name' => 'StudentManagement',
    'version' => '1.0.0',
    'description' => 'Manages students, sessions, classes, and sections',
    'author' => 'Student Attendance System',
    'status' => 'active',
    
    /**
     * Module Dependencies
     */
    'dependencies' => [],
    
    /**
     * Module Routes Prefix
     */
    'routes_prefix' => 'students',
    
    /**
     * Default Controller
     */
    'default_controller' => 'Students',
    
    /**
     * Module Permissions
     */
    'permissions' => [
        'student.view' => 'View Students',
        'student.create' => 'Create Students',
        'student.edit' => 'Edit Students',
        'student.delete' => 'Delete Students',
        'session.manage' => 'Manage Sessions',
        'class.manage' => 'Manage Classes',
        'section.manage' => 'Manage Sections'
    ],
    
    /**
     * Module Settings
     */
    'settings' => [
        'student_id_format' => 'STD{year}{month}{increment}',
        'rfid_required' => true,
        'photo_upload' => true,
        'max_photo_size' => 2048, // KB
        'allowed_photo_types' => ['jpg', 'jpeg', 'png'],
        'student_per_page' => 25,
        'auto_generate_student_id' => true
    ],
    
    /**
     * Database Tables
     */
    'tables' => [
        'students' => 'students',
        'sessions' => 'sessions',
        'classes' => 'classes',
        'sections' => 'sections',
        'class_sections' => 'class_sections',
        'student_session' => 'student_session'
    ],
    
    /**
     * Validation Rules
     */
    'validation' => [
        'student' => [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'phone' => 'permit_empty|min_length[10]|max_length[15]',
            'rfid_card' => 'permit_empty|max_length[50]',
            'student_id' => 'required|max_length[20]|is_unique[students.student_id,id,{id}]',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer'
        ],
        'session' => [
            'name' => 'required|min_length[3]|max_length[100]',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'is_active' => 'permit_empty|in_list[0,1]'
        ],
        'class' => [
            'name' => 'required|min_length[1]|max_length[50]',
            'description' => 'permit_empty|max_length[255]'
        ],
        'section' => [
            'name' => 'required|min_length[1]|max_length[50]',
            'description' => 'permit_empty|max_length[255]'
        ]
    ],
    
    /**
     * Menu Items
     */
    'menu' => [
        [
            'title' => 'Students',
            'url' => 'students',
            'icon' => 'fas fa-users',
            'permission' => 'student.view',
            'submenu' => [
                [
                    'title' => 'All Students',
                    'url' => 'students',
                    'permission' => 'student.view'
                ],
                [
                    'title' => 'Add Student',
                    'url' => 'students/create',
                    'permission' => 'student.create'
                ]
            ]
        ],
        [
            'title' => 'Academic',
            'url' => '#',
            'icon' => 'fas fa-graduation-cap',
            'submenu' => [
                [
                    'title' => 'Sessions',
                    'url' => 'students/sessions',
                    'permission' => 'session.manage'
                ],
                [
                    'title' => 'Classes',
                    'url' => 'students/classes',
                    'permission' => 'class.manage'
                ],
                [
                    'title' => 'Sections',
                    'url' => 'students/sections',
                    'permission' => 'section.manage'
                ]
            ]
        ]
    ],
    
    /**
     * Widgets
     */
    'widgets' => [
        'student_stats' => [
            'title' => 'Student Statistics',
            'description' => 'Shows total and active students',
            'cache_time' => 300 // 5 minutes
        ]
    ],
    
    /**
     * API Endpoints
     */
    'api_endpoints' => [
        'students' => [
            'list' => 'GET /api/students',
            'show' => 'GET /api/students/{id}',
            'create' => 'POST /api/students',
            'update' => 'PUT /api/students/{id}',
            'delete' => 'DELETE /api/students/{id}'
        ],
        'sessions' => [
            'list' => 'GET /api/sessions',
            'active' => 'GET /api/sessions/active'
        ]
    ]
];