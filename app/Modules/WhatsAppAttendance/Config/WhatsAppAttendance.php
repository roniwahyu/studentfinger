<?php

namespace App\Modules\WhatsAppAttendance\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * WhatsApp Attendance Configuration
 */
class WhatsAppAttendance extends BaseConfig
{
    /**
     * Module Information
     */
    public $name = 'WhatsApp Attendance Monitor';
    public $version = '1.0.0';
    public $description = 'Monitors attendance from fin_pro database and sends WhatsApp notifications to parents';
    
    /**
     * School Hours Configuration
     */
    public $school_hours = [
        'entry_start' => '06:30',
        'entry_end' => '08:30',
        'exit_start' => '14:00',
        'exit_end' => '16:00'
    ];
    
    /**
     * Monitoring Configuration
     */
    public $monitoring = [
        'check_interval' => 300, // 5 minutes in seconds
        'max_records_per_batch' => 100,
        'enable_real_time' => true,
        'monitor_weekends' => false,
        'monitor_holidays' => false
    ];
    
    /**
     * Notification Configuration
     */
    public $notifications = [
        'enabled' => true,
        'send_entry_notifications' => true,
        'send_exit_notifications' => true,
        'send_late_notifications' => true,
        'send_absent_notifications' => true,
        'notification_delay' => 60, // seconds to wait before sending
        'max_retries' => 3,
        'retry_delay' => 300 // 5 minutes
    ];
    
    /**
     * Message Templates
     */
    public $message_templates = [
        'entry' => [
            'template' => "🏫 *MASUK SEKOLAH*\n\n📅 Tanggal: {date}\n⏰ Waktu: {time}\n👤 Nama: {student_name}\n🏛️ Kelas: {class_name}\n📍 Lokasi: {device_location}\n\n✅ Anak Anda telah tiba di sekolah dengan selamat.\n\n_Sistem Presensi Otomatis_",
            'variables' => ['date', 'time', 'student_name', 'class_name', 'device_location']
        ],
        'exit' => [
            'template' => "🏠 *PULANG SEKOLAH*\n\n📅 Tanggal: {date}\n⏰ Waktu: {time}\n👤 Nama: {student_name}\n🏛️ Kelas: {class_name}\n📍 Lokasi: {device_location}\n\n✅ Anak Anda telah selesai sekolah dan keluar dari area sekolah.\n\n_Sistem Presensi Otomatis_",
            'variables' => ['date', 'time', 'student_name', 'class_name', 'device_location']
        ],
        'late' => [
            'template' => "⚠️ *TERLAMBAT MASUK*\n\n📅 Tanggal: {date}\n⏰ Waktu: {time}\n👤 Nama: {student_name}\n🏛️ Kelas: {class_name}\n\n🕐 Anak Anda terlambat masuk sekolah.\nBatas waktu masuk: {entry_deadline}\n\n_Sistem Presensi Otomatis_",
            'variables' => ['date', 'time', 'student_name', 'class_name', 'entry_deadline']
        ],
        'absent' => [
            'template' => "❌ *TIDAK HADIR*\n\n📅 Tanggal: {date}\n👤 Nama: {student_name}\n🏛️ Kelas: {class_name}\n\n⚠️ Anak Anda belum tercatat hadir di sekolah hingga pukul {check_time}.\n\nJika berhalangan hadir, mohon konfirmasi ke sekolah.\n\n_Sistem Presensi Otomatis_",
            'variables' => ['date', 'student_name', 'class_name', 'check_time']
        ]
    ];
    
    /**
     * Data Transfer Configuration
     */
    public $data_transfer = [
        'enabled' => true,
        'batch_size' => 50,
        'duplicate_handling' => 'skip', // skip, update, error
        'validate_student_mapping' => true,
        'auto_create_students' => false
    ];
    
    /**
     * Logging Configuration
     */
    public $logging = [
        'enabled' => true,
        'log_level' => 'info', // debug, info, warning, error
        'log_notifications' => true,
        'log_data_transfer' => true,
        'log_monitoring' => true,
        'retention_days' => 30
    ];
    
    /**
     * Database Configuration
     */
    public $database = [
        'source_connection' => 'fin_pro',
        'destination_connection' => 'default',
        'source_table' => 'att_log',
        'destination_table' => 'att_log'
    ];
    
    /**
     * Wablas API Configuration
     */
    public $wablas = [
        'base_url' => 'https://texas.wablas.com',
        'token' => '', // Will be loaded from .env
        'secret_key' => '', // Will be loaded from .env
        'device_id' => 1, // Default device ID
        'timeout' => 30,
        'verify_ssl' => true
    ];
    
    /**
     * Student-Parent Mapping
     */
    public $parent_mapping = [
        'phone_field' => 'father_phone', // Primary parent phone field
        'backup_phone_field' => 'mother_phone', // Backup parent phone field
        'validate_phone' => true,
        'phone_format' => '62', // Country code prefix
        'min_phone_length' => 10,
        'max_phone_length' => 15
    ];
    
    /**
     * Error Handling
     */
    public $error_handling = [
        'continue_on_error' => true,
        'max_consecutive_errors' => 10,
        'error_notification' => true,
        'admin_phone' => '', // Admin phone for error notifications
        'error_email' => '' // Admin email for error notifications
    ];
    
    /**
     * Performance Configuration
     */
    public $performance = [
        'memory_limit' => '256M',
        'execution_time_limit' => 300, // 5 minutes
        'enable_caching' => true,
        'cache_duration' => 3600, // 1 hour
        'optimize_queries' => true
    ];
    
    public function __construct()
    {
        parent::__construct();
        
        // Load Wablas configuration from environment
        $this->wablas['base_url'] = env('WABLAS_BASE_URL', $this->wablas['base_url']);
        $this->wablas['token'] = env('WABLAS_TOKEN', '');
        $this->wablas['secret_key'] = env('WABLAS_SECRET_KEY', '');
        
        // Load admin contacts from environment
        $this->error_handling['admin_phone'] = env('ADMIN_PHONE', '');
        $this->error_handling['error_email'] = env('ADMIN_EMAIL', '');
    }
}