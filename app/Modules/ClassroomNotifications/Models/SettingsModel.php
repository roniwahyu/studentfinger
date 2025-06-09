<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * Settings Model
 * 
 * Manages system settings and configuration
 */
class SettingsModel extends Model
{
    protected $table = 'notification_settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'setting_key',
        'setting_value',
        'setting_type',
        'description',
        'is_encrypted'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get setting value by key
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['setting_value'];
        
        // Decrypt if encrypted
        if ($setting['is_encrypted']) {
            $value = $this->decrypt($value);
        }
        
        // Cast to appropriate type
        switch ($setting['setting_type']) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Set setting value
     */
    public function setSetting(string $key, $value, string $type = 'string', bool $encrypt = false, string $description = ''): bool
    {
        // Convert value to string for storage
        if ($type === 'json') {
            $value = json_encode($value);
        } elseif ($type === 'boolean') {
            $value = $value ? '1' : '0';
        } else {
            $value = (string) $value;
        }
        
        // Encrypt if needed
        if ($encrypt) {
            $value = $this->encrypt($value);
        }
        
        $data = [
            'setting_key' => $key,
            'setting_value' => $value,
            'setting_type' => $type,
            'description' => $description,
            'is_encrypted' => $encrypt ? 1 : 0
        ];
        
        $existing = $this->where('setting_key', $key)->first();
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data) !== false;
        }
    }
    
    /**
     * Get all settings as key-value array
     */
    public function getAllSettings(): array
    {
        $settings = $this->findAll();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $this->getSetting($setting['setting_key']);
        }
        
        return $result;
    }
    
    /**
     * Get WABLAS configuration
     */
    public function getWablasConfig(): array
    {
        return [
            'base_url' => $this->getSetting('wablas_base_url', ''),
            'token' => $this->getSetting('wablas_token', ''),
            'secret_key' => $this->getSetting('wablas_secret_key', ''),
            'test_phone' => $this->getSetting('wablas_test_phone', ''),
            'timeout' => $this->getSetting('wablas_timeout', 30),
            'retry_attempts' => $this->getSetting('wablas_retry_attempts', 3),
            'auto_check_interval' => $this->getSetting('wablas_auto_check_interval', 5)
        ];
    }
    
    /**
     * Save WABLAS configuration
     */
    public function saveWablasConfig(array $config): bool
    {
        $success = true;
        
        $settings = [
            'wablas_base_url' => ['value' => $config['base_url'], 'type' => 'string', 'encrypt' => false, 'desc' => 'WABLAS API Base URL'],
            'wablas_token' => ['value' => $config['token'], 'type' => 'string', 'encrypt' => true, 'desc' => 'WABLAS API Token'],
            'wablas_secret_key' => ['value' => $config['secret_key'], 'type' => 'string', 'encrypt' => true, 'desc' => 'WABLAS API Secret Key'],
            'wablas_test_phone' => ['value' => $config['test_phone'], 'type' => 'string', 'encrypt' => false, 'desc' => 'Test Phone Number'],
            'wablas_timeout' => ['value' => $config['timeout'], 'type' => 'integer', 'encrypt' => false, 'desc' => 'API Timeout (seconds)'],
            'wablas_retry_attempts' => ['value' => $config['retry_attempts'], 'type' => 'integer', 'encrypt' => false, 'desc' => 'Retry Attempts'],
            'wablas_auto_check_interval' => ['value' => $config['auto_check_interval'], 'type' => 'integer', 'encrypt' => false, 'desc' => 'Auto Check Interval (minutes)']
        ];
        
        foreach ($settings as $key => $setting) {
            if (!$this->setSetting($key, $setting['value'], $setting['type'], $setting['encrypt'], $setting['desc'])) {
                $success = false;
            }
        }
        
        // Also update .env file
        if ($success) {
            $this->updateEnvFile($config);
        }
        
        return $success;
    }
    
    /**
     * Update .env file with WABLAS configuration
     */
    private function updateEnvFile(array $config): bool
    {
        $envFile = ROOTPATH . '.env';
        
        if (!file_exists($envFile)) {
            return false;
        }
        
        $envContent = file_get_contents($envFile);
        
        $envUpdates = [
            'WABLAS_BASE_URL' => $config['base_url'],
            'WABLAS_TOKEN' => $config['token'],
            'WABLAS_SECRET_KEY' => $config['secret_key']
        ];
        
        foreach ($envUpdates as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }
        
        return file_put_contents($envFile, $envContent) !== false;
    }
    
    /**
     * Get notification settings
     */
    public function getNotificationSettings(): array
    {
        return [
            'auto_send_on_session_start' => $this->getSetting('auto_send_on_session_start', true),
            'auto_send_on_session_break' => $this->getSetting('auto_send_on_session_break', false),
            'auto_send_on_session_resume' => $this->getSetting('auto_send_on_session_resume', false),
            'auto_send_on_session_finish' => $this->getSetting('auto_send_on_session_finish', true),
            'default_language' => $this->getSetting('default_language', 'id'),
            'school_name' => $this->getSetting('school_name', 'Student Finger School'),
            'notification_delay' => $this->getSetting('notification_delay', 0),
            'max_retry_attempts' => $this->getSetting('max_retry_attempts', 3),
            'retry_delay' => $this->getSetting('retry_delay', 300)
        ];
    }
    
    /**
     * Save notification settings
     */
    public function saveNotificationSettings(array $settings): bool
    {
        $success = true;
        
        $settingsMap = [
            'auto_send_on_session_start' => ['type' => 'boolean', 'desc' => 'Auto send notification on session start'],
            'auto_send_on_session_break' => ['type' => 'boolean', 'desc' => 'Auto send notification on session break'],
            'auto_send_on_session_resume' => ['type' => 'boolean', 'desc' => 'Auto send notification on session resume'],
            'auto_send_on_session_finish' => ['type' => 'boolean', 'desc' => 'Auto send notification on session finish'],
            'default_language' => ['type' => 'string', 'desc' => 'Default notification language'],
            'school_name' => ['type' => 'string', 'desc' => 'School name for notifications'],
            'notification_delay' => ['type' => 'integer', 'desc' => 'Delay before sending notifications (seconds)'],
            'max_retry_attempts' => ['type' => 'integer', 'desc' => 'Maximum retry attempts for failed notifications'],
            'retry_delay' => ['type' => 'integer', 'desc' => 'Delay between retry attempts (seconds)']
        ];
        
        foreach ($settingsMap as $key => $config) {
            if (isset($settings[$key])) {
                if (!$this->setSetting($key, $settings[$key], $config['type'], false, $config['desc'])) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Simple encryption for sensitive data
     */
    private function encrypt(string $data): string
    {
        $key = env('encryption.key', 'default-key-change-this');
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16)));
    }
    
    /**
     * Simple decryption for sensitive data
     */
    private function decrypt(string $data): string
    {
        $key = env('encryption.key', 'default-key-change-this');
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
    }
    
    /**
     * Initialize default settings
     */
    public function initializeDefaults(): bool
    {
        $defaults = [
            // WABLAS Settings
            'wablas_base_url' => ['value' => 'https://texas.wablas.com', 'type' => 'string', 'encrypt' => false, 'desc' => 'WABLAS API Base URL'],
            'wablas_timeout' => ['value' => 30, 'type' => 'integer', 'encrypt' => false, 'desc' => 'API Timeout (seconds)'],
            'wablas_retry_attempts' => ['value' => 3, 'type' => 'integer', 'encrypt' => false, 'desc' => 'Retry Attempts'],
            'wablas_auto_check_interval' => ['value' => 5, 'type' => 'integer', 'encrypt' => false, 'desc' => 'Auto Check Interval (minutes)'],
            
            // Notification Settings
            'auto_send_on_session_start' => ['value' => true, 'type' => 'boolean', 'encrypt' => false, 'desc' => 'Auto send notification on session start'],
            'auto_send_on_session_break' => ['value' => false, 'type' => 'boolean', 'encrypt' => false, 'desc' => 'Auto send notification on session break'],
            'auto_send_on_session_resume' => ['value' => false, 'type' => 'boolean', 'encrypt' => false, 'desc' => 'Auto send notification on session resume'],
            'auto_send_on_session_finish' => ['value' => true, 'type' => 'boolean', 'encrypt' => false, 'desc' => 'Auto send notification on session finish'],
            'default_language' => ['value' => 'id', 'type' => 'string', 'encrypt' => false, 'desc' => 'Default notification language'],
            'school_name' => ['value' => 'Student Finger School', 'type' => 'string', 'encrypt' => false, 'desc' => 'School name for notifications'],
            'notification_delay' => ['value' => 0, 'type' => 'integer', 'encrypt' => false, 'desc' => 'Delay before sending notifications (seconds)'],
            'max_retry_attempts' => ['value' => 3, 'type' => 'integer', 'encrypt' => false, 'desc' => 'Maximum retry attempts for failed notifications'],
            'retry_delay' => ['value' => 300, 'type' => 'integer', 'encrypt' => false, 'desc' => 'Delay between retry attempts (seconds)']
        ];
        
        $success = true;
        
        foreach ($defaults as $key => $setting) {
            // Only set if not already exists
            if (!$this->where('setting_key', $key)->first()) {
                if (!$this->setSetting($key, $setting['value'], $setting['type'], $setting['encrypt'], $setting['desc'])) {
                    $success = false;
                }
            }
        }
        
        return $success;
    }
}
