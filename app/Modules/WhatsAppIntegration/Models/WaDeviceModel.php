<?php

namespace App\Modules\WhatsAppIntegration\Models;

use CodeIgniter\Model;

class WaDeviceModel extends Model
{
    protected $table = 'wa_devices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_name',
        'device_token',
        'device_status',
        'api_url',
        'device_type',
        'webhook_url',
        'last_activity',
        'settings',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'device_name' => 'required|min_length[3]|max_length[100]',
        'device_token' => 'required|min_length[10]',
        'api_url' => 'required|valid_url',
        'device_type' => 'required|in_list[wablas,whatsapp-web,baileys,other]'
    ];
    
    protected $validationMessages = [
        'device_name' => [
            'required' => 'Device name is required',
            'min_length' => 'Device name must be at least 3 characters'
        ],
        'device_token' => [
            'required' => 'Device token is required',
            'min_length' => 'Device token must be at least 10 characters'
        ],
        'api_url' => [
            'required' => 'API URL is required',
            'valid_url' => 'Please enter a valid URL'
        ]
    ];

    /**
     * Get active devices
     */
    public function getActiveDevices()
    {
        return $this->where('device_status', 1)
                   ->where('deleted_at', null)
                   ->orderBy('device_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get devices with statistics
     */
    public function getDevicesWithStats()
    {
        $devices = $this->where('deleted_at', null)
                       ->orderBy('device_name', 'ASC')
                       ->findAll();

        foreach ($devices as &$device) {
            $device['stats'] = $this->getDeviceStats($device['id']);
        }

        return $devices;
    }

    /**
     * Get device statistics
     */
    public function getDeviceStats($deviceId)
    {
        $db = \Config\Database::connect();
        
        // Total messages sent
        $totalMessages = $db->table('wa_messages')
                           ->where('device_id', $deviceId)
                           ->countAllResults();

        // Messages sent today
        $messagesToday = $db->table('wa_messages')
                           ->where('device_id', $deviceId)
                           ->where('DATE(created_at)', date('Y-m-d'))
                           ->countAllResults();

        // Success rate (last 100 messages)
        $recentMessages = $db->table('wa_messages')
                            ->where('device_id', $deviceId)
                            ->orderBy('id', 'DESC')
                            ->limit(100)
                            ->get()
                            ->getResultArray();

        $successCount = 0;
        foreach ($recentMessages as $message) {
            if ($message['status'] == 1) { // Sent successfully
                $successCount++;
            }
        }

        $successRate = count($recentMessages) > 0 ? ($successCount / count($recentMessages)) * 100 : 0;

        // Pending messages
        $pendingMessages = $db->table('wa_schedules')
                             ->where('device_id', $deviceId)
                             ->where('status', 0)
                             ->countAllResults();

        return [
            'total_messages' => $totalMessages,
            'messages_today' => $messagesToday,
            'success_rate' => round($successRate, 2),
            'pending_messages' => $pendingMessages
        ];
    }

    /**
     * Get device types
     */
    public function getDeviceTypes()
    {
        return [
            'wablas' => 'Wablas API',
            'whatsapp-web' => 'WhatsApp Web',
            'baileys' => 'Baileys (Multi-Device)',
            'other' => 'Other API'
        ];
    }

    /**
     * Update device last activity
     */
    public function updateLastActivity($deviceId)
    {
        return $this->update($deviceId, [
            'last_activity' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get device by webhook URL
     */
    public function getByWebhookUrl($webhookUrl)
    {
        return $this->where('webhook_url', $webhookUrl)
                   ->where('device_status', 1)
                   ->where('deleted_at', null)
                   ->first();
    }

    /**
     * Get device settings
     */
    public function getDeviceSettings($deviceId)
    {
        $device = $this->find($deviceId);
        if (!$device) {
            return [];
        }

        return json_decode($device['settings'] ?? '{}', true);
    }

    /**
     * Update device settings
     */
    public function updateDeviceSettings($deviceId, $settings)
    {
        return $this->update($deviceId, [
            'settings' => json_encode($settings),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get devices by type
     */
    public function getDevicesByType($type)
    {
        return $this->where('device_type', $type)
                   ->where('device_status', 1)
                   ->where('deleted_at', null)
                   ->findAll();
    }

    /**
     * Check if device name exists
     */
    public function deviceNameExists($deviceName, $excludeId = null)
    {
        $builder = $this->where('device_name', $deviceName)
                       ->where('deleted_at', null);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get device usage report
     */
    public function getDeviceUsageReport($deviceId, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('wa_messages')
                   ->select('DATE(created_at) as date, COUNT(*) as message_count, 
                            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as success_count,
                            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as failed_count')
                   ->where('device_id', $deviceId)
                   ->where('DATE(created_at) >=', $startDate)
                   ->where('DATE(created_at) <=', $endDate)
                   ->groupBy('DATE(created_at)')
                   ->orderBy('date', 'ASC')
                   ->get();

        return $query->getResultArray();
    }

    /**
     * Get most active devices
     */
    public function getMostActiveDevices($limit = 5)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table($this->table . ' d')
                   ->select('d.*, COUNT(m.id) as message_count')
                   ->join('wa_messages m', 'd.id = m.device_id', 'left')
                   ->where('d.deleted_at', null)
                   ->where('DATE(m.created_at) >=', date('Y-m-d', strtotime('-30 days')))
                   ->groupBy('d.id')
                   ->orderBy('message_count', 'DESC')
                   ->limit($limit)
                   ->get();

        return $query->getResultArray();
    }

    /**
     * Soft delete device
     */
    public function softDelete($id)
    {
        return $this->update($id, [
            'deleted_at' => date('Y-m-d H:i:s'),
            'device_status' => 0
        ]);
    }

    /**
     * Restore soft deleted device
     */
    public function restore($id)
    {
        return $this->update($id, [
            'deleted_at' => null,
            'device_status' => 1
        ]);
    }
}
