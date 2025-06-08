<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasDeviceModel extends Model
{
    protected $table = 'wablas_devices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_name',
        'device_serial',
        'phone_number',
        'token',
        'secret_key',
        'api_url',
        'webhook_url',
        'tracking_url',
        'device_type',
        'device_status',
        'connection_status',
        'last_seen',
        'quota_limit',
        'quota_used',
        'quota_reset_date',
        'expired_date',
        'delay_seconds',
        'max_retries',
        'auto_reply_enabled',
        'incoming_webhook_enabled',
        'status_webhook_enabled',
        'device_webhook_enabled',
        'settings',
        'notes',
        'created_by',
        'updated_by'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'device_name' => 'required|max_length[100]',
        'phone_number' => 'required|max_length[20]',
        'token' => 'required',
        'api_url' => 'valid_url',
        'device_type' => 'in_list[wablas,whatsapp-web,baileys]',
        'device_status' => 'in_list[0,1,2]',
        'connection_status' => 'in_list[connected,disconnected,connecting,error]',
        'quota_limit' => 'integer|greater_than[0]',
        'quota_used' => 'integer|greater_than_equal_to[0]',
        'delay_seconds' => 'integer|greater_than_equal_to[10]|less_than_equal_to[120]',
        'max_retries' => 'integer|greater_than_equal_to[0]|less_than_equal_to[10]'
    ];
    
    protected $validationMessages = [
        'device_name' => [
            'required' => 'Device name is required',
            'max_length' => 'Device name cannot exceed 100 characters'
        ],
        'phone_number' => [
            'required' => 'Phone number is required',
            'max_length' => 'Phone number cannot exceed 20 characters'
        ],
        'token' => [
            'required' => 'API token is required'
        ],
        'delay_seconds' => [
            'greater_than_equal_to' => 'Delay must be at least 10 seconds',
            'less_than_equal_to' => 'Delay cannot exceed 120 seconds'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['beforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['afterFind'];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Get active devices
     */
    public function getActiveDevices(): array
    {
        return $this->where('device_status', 1)
                   ->where('deleted_at', null)
                   ->findAll();
    }
    
    /**
     * Get connected devices
     */
    public function getConnectedDevices(): array
    {
        return $this->where('device_status', 1)
                   ->where('connection_status', 'connected')
                   ->where('deleted_at', null)
                   ->findAll();
    }
    
    /**
     * Get device by phone number
     */
    public function getByPhoneNumber(string $phoneNumber): ?array
    {
        return $this->where('phone_number', $phoneNumber)
                   ->where('deleted_at', null)
                   ->first();
    }
    
    /**
     * Get device by serial
     */
    public function getBySerial(string $serial): ?array
    {
        return $this->where('device_serial', $serial)
                   ->where('deleted_at', null)
                   ->first();
    }
    
    /**
     * Update device status
     */
    public function updateStatus(int $deviceId, string $status): bool
    {
        return $this->update($deviceId, [
            'connection_status' => $status,
            'last_seen' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update quota usage
     */
    public function updateQuota(int $deviceId, int $used): bool
    {
        return $this->update($deviceId, [
            'quota_used' => $used,
            'last_seen' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Reset quota for all devices
     */
    public function resetQuota(): bool
    {
        return $this->set('quota_used', 0)
                   ->set('quota_reset_date', date('Y-m-d'))
                   ->where('quota_reset_date <', date('Y-m-d'))
                   ->update();
    }
    
    /**
     * Get devices with low quota
     */
    public function getLowQuotaDevices(int $threshold = 90): array
    {
        return $this->select('*, (quota_used / quota_limit * 100) as quota_percentage')
                   ->having('quota_percentage >=', $threshold)
                   ->where('device_status', 1)
                   ->where('deleted_at', null)
                   ->findAll();
    }
    
    /**
     * Get expired devices
     */
    public function getExpiredDevices(): array
    {
        return $this->where('expired_date <', date('Y-m-d'))
                   ->where('device_status', 1)
                   ->where('deleted_at', null)
                   ->findAll();
    }
    
    /**
     * Get device statistics
     */
    public function getStatistics(): array
    {
        $total = $this->where('deleted_at', null)->countAllResults();
        $active = $this->where('device_status', 1)->where('deleted_at', null)->countAllResults();
        $connected = $this->where('connection_status', 'connected')->where('deleted_at', null)->countAllResults();
        $expired = $this->where('expired_date <', date('Y-m-d'))->where('deleted_at', null)->countAllResults();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'connected' => $connected,
            'disconnected' => $active - $connected,
            'expired' => $expired
        ];
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['settings']) && is_array($data['data']['settings'])) {
            $data['data']['settings'] = json_encode($data['data']['settings']);
        }
        
        // Set default values
        if (!isset($data['data']['api_url'])) {
            $data['data']['api_url'] = 'https://wablas.com';
        }
        
        if (!isset($data['data']['device_type'])) {
            $data['data']['device_type'] = 'wablas';
        }
        
        if (!isset($data['data']['delay_seconds'])) {
            $data['data']['delay_seconds'] = 10;
        }
        
        if (!isset($data['data']['max_retries'])) {
            $data['data']['max_retries'] = 3;
        }
        
        if (!isset($data['data']['quota_limit'])) {
            $data['data']['quota_limit'] = 1000;
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data): array
    {
        if (isset($data['data']['settings']) && is_array($data['data']['settings'])) {
            $data['data']['settings'] = json_encode($data['data']['settings']);
        }
        
        return $data;
    }
    
    /**
     * After find callback
     */
    protected function afterFind(array $data): array
    {
        if (isset($data['data'])) {
            // Single record
            if (isset($data['data']['settings']) && is_string($data['data']['settings'])) {
                $data['data']['settings'] = json_decode($data['data']['settings'], true);
            }
        } else {
            // Multiple records
            foreach ($data as &$record) {
                if (isset($record['settings']) && is_string($record['settings'])) {
                    $record['settings'] = json_decode($record['settings'], true);
                }
            }
        }
        
        return $data;
    }
}
