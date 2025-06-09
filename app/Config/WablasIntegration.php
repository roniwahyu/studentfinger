<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Wablas Integration Configuration
 */
class WablasIntegration extends BaseConfig
{
    /**
     * Default Wablas API Configuration
     */
    public array $wablas = [
        'base_url' => 'https://wablas.com',
        'token' => '',
        'secret_key' => '',
        'timeout' => 30,
        'verify_ssl' => false,
        'max_file_size' => 2048, // KB (2MB as per Wablas limit)
    ];
    
    /**
     * Webhook Configuration
     */
    public array $webhooks = [
        'incoming_url' => '',
        'status_url' => '',
        'device_url' => '',
        'verify_signature' => false,
        'secret_token' => ''
    ];
    
    /**
     * Message Configuration
     */
    public array $messages = [
        'default_delay' => 1, // seconds between messages
        'max_retries' => 3,
        'retry_delay' => 5, // seconds
        'enable_logging' => true,
        'enable_queue' => true,
        'queue_batch_size' => 10
    ];
    
    /**
     * Auto Reply Configuration
     */
    public array $autoReply = [
        'enabled' => false,
        'default_response' => 'Thank you for your message. We will get back to you soon.',
        'business_hours_only' => false,
        'business_hours' => [
            'start' => '09:00',
            'end' => '17:00',
            'timezone' => 'Asia/Jakarta'
        ]
    ];
    
    /**
     * Contact Management Configuration
     */
    public array $contacts = [
        'auto_save_incoming' => true,
        'update_existing' => true,
        'default_group' => 'General'
    ];
    
    /**
     * Reporting Configuration
     */
    public array $reporting = [
        'enable_analytics' => true,
        'retention_days' => 90,
        'export_formats' => ['csv', 'excel', 'pdf']
    ];
    
    /**
     * Security Configuration
     */
    public array $security = [
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'time_window' => 3600 // 1 hour
        ],
        'ip_whitelist' => [],
        'webhook_secret' => ''
    ];
}
