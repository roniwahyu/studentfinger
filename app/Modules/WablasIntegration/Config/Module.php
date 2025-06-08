<?php

return [
    'name' => 'Wablas Integration',
    'description' => 'Complete integration with Wablas.com WhatsApp API',
    'version' => '1.0.0',
    'author' => 'Student Attendance System',
    'namespace' => 'App\\Modules\\WablasIntegration',
    
    // Module settings
    'enabled' => true,
    'auto_discover' => true,
    
    // API Configuration
    'wablas' => [
        'base_url' => 'https://wablas.com',
        'token' => env('WABLAS_TOKEN', ''),
        'secret_key' => env('WABLAS_SECRET_KEY', ''),
        'timeout' => 30,
        'verify_ssl' => false,
        'max_file_size' => 2048, // KB (2MB as per Wablas limit)
    ],
    
    // Webhook settings
    'webhooks' => [
        'incoming_messages' => [
            'enabled' => true,
            'endpoint' => 'wablas/webhook/incoming',
            'verify_signature' => false
        ],
        'message_status' => [
            'enabled' => true,
            'endpoint' => 'wablas/webhook/status',
            'verify_signature' => false
        ],
        'device_status' => [
            'enabled' => true,
            'endpoint' => 'wablas/webhook/device',
            'verify_signature' => false
        ]
    ],
    
    // Message settings
    'messaging' => [
        'default_delay' => 1, // seconds between bulk messages
        'max_retries' => 3,
        'retry_delay' => 5, // seconds
        'enable_logging' => true,
        'enable_queue' => true,
        'queue_batch_size' => 10
    ],
    
    // Auto reply settings
    'auto_reply' => [
        'enabled' => false,
        'default_response' => 'Thank you for your message. We will get back to you soon.',
        'business_hours_only' => false,
        'business_hours' => [
            'start' => '09:00',
            'end' => '17:00',
            'timezone' => 'Asia/Jakarta'
        ]
    ],
    
    // Contact management
    'contacts' => [
        'auto_save_incoming' => true,
        'update_existing' => true,
        'default_group' => 'General'
    ],
    
    // Reporting settings
    'reporting' => [
        'enable_analytics' => true,
        'retention_days' => 90,
        'export_formats' => ['csv', 'excel', 'pdf']
    ],
    
    // Security settings
    'security' => [
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'time_window' => 3600 // 1 hour
        ],
        'ip_whitelist' => [],
        'webhook_secret' => env('WABLAS_WEBHOOK_SECRET', '')
    ],
    
    // Database tables
    'tables' => [
        'devices' => 'wablas_devices',
        'messages' => 'wablas_messages',
        'contacts' => 'wablas_contacts',
        'schedules' => 'wablas_schedules',
        'auto_replies' => 'wablas_auto_replies',
        'webhooks' => 'wablas_webhooks',
        'logs' => 'wablas_logs',
        'templates' => 'wablas_templates',
        'groups' => 'wablas_groups',
        'campaigns' => 'wablas_campaigns'
    ],
    
    // Menu configuration
    'menu' => [
        'parent' => 'Communications',
        'icon' => 'fab fa-whatsapp',
        'order' => 100,
        'items' => [
            [
                'title' => 'Dashboard',
                'url' => 'wablas/dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'permission' => 'wablas.dashboard'
            ],
            [
                'title' => 'Devices',
                'url' => 'wablas/devices',
                'icon' => 'fas fa-mobile-alt',
                'permission' => 'wablas.devices'
            ],
            [
                'title' => 'Messages',
                'url' => 'wablas/messages',
                'icon' => 'fas fa-comments',
                'permission' => 'wablas.messages',
                'submenu' => [
                    [
                        'title' => 'Send Message',
                        'url' => 'wablas/messages/send',
                        'permission' => 'wablas.messages.send'
                    ],
                    [
                        'title' => 'Bulk Messages',
                        'url' => 'wablas/messages/bulk',
                        'permission' => 'wablas.messages.bulk'
                    ],
                    [
                        'title' => 'Scheduled',
                        'url' => 'wablas/messages/scheduled',
                        'permission' => 'wablas.messages.scheduled'
                    ],
                    [
                        'title' => 'History',
                        'url' => 'wablas/messages/history',
                        'permission' => 'wablas.messages.history'
                    ]
                ]
            ],
            [
                'title' => 'Contacts',
                'url' => 'wablas/contacts',
                'icon' => 'fas fa-address-book',
                'permission' => 'wablas.contacts'
            ],
            [
                'title' => 'Auto Reply',
                'url' => 'wablas/auto-reply',
                'icon' => 'fas fa-robot',
                'permission' => 'wablas.auto_reply'
            ],
            [
                'title' => 'Templates',
                'url' => 'wablas/templates',
                'icon' => 'fas fa-file-alt',
                'permission' => 'wablas.templates'
            ],
            [
                'title' => 'Reports',
                'url' => 'wablas/reports',
                'icon' => 'fas fa-chart-bar',
                'permission' => 'wablas.reports'
            ],
            [
                'title' => 'Settings',
                'url' => 'wablas/settings',
                'icon' => 'fas fa-cog',
                'permission' => 'wablas.settings'
            ]
        ]
    ],
    
    // Permissions
    'permissions' => [
        'wablas.dashboard' => 'View Wablas Dashboard',
        'wablas.devices' => 'Manage Wablas Devices',
        'wablas.devices.create' => 'Create Wablas Devices',
        'wablas.devices.edit' => 'Edit Wablas Devices',
        'wablas.devices.delete' => 'Delete Wablas Devices',
        'wablas.messages' => 'View Messages',
        'wablas.messages.send' => 'Send Messages',
        'wablas.messages.bulk' => 'Send Bulk Messages',
        'wablas.messages.scheduled' => 'Manage Scheduled Messages',
        'wablas.messages.history' => 'View Message History',
        'wablas.contacts' => 'Manage Contacts',
        'wablas.contacts.import' => 'Import Contacts',
        'wablas.contacts.export' => 'Export Contacts',
        'wablas.auto_reply' => 'Manage Auto Reply',
        'wablas.templates' => 'Manage Templates',
        'wablas.reports' => 'View Reports',
        'wablas.settings' => 'Manage Settings',
        'wablas.webhooks' => 'Manage Webhooks'
    ],
    
    // Event listeners
    'events' => [
        'message_sent' => [
            'App\\Modules\\WablasIntegration\\Events\\MessageSentListener'
        ],
        'message_received' => [
            'App\\Modules\\WablasIntegration\\Events\\MessageReceivedListener'
        ],
        'device_connected' => [
            'App\\Modules\\WablasIntegration\\Events\\DeviceConnectedListener'
        ],
        'device_disconnected' => [
            'App\\Modules\\WablasIntegration\\Events\\DeviceDisconnectedListener'
        ]
    ],
    
    // Scheduled tasks
    'tasks' => [
        'process_scheduled_messages' => [
            'class' => 'App\\Modules\\WablasIntegration\\Tasks\\ProcessScheduledMessages',
            'schedule' => '* * * * *' // Every minute
        ],
        'sync_device_status' => [
            'class' => 'App\\Modules\\WablasIntegration\\Tasks\\SyncDeviceStatus',
            'schedule' => '*/5 * * * *' // Every 5 minutes
        ],
        'cleanup_old_logs' => [
            'class' => 'App\\Modules\\WablasIntegration\\Tasks\\CleanupOldLogs',
            'schedule' => '0 2 * * *' // Daily at 2 AM
        ]
    ]
];
