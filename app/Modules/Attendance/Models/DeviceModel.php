<?php

namespace App\Modules\Attendance\Models;

use CodeIgniter\Model;

class DeviceModel extends Model
{
    protected $table = 'attendance_devices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $allowedFields = [
        'name',
        'device_type',
        'ip_address',
        'port',
        'location',
        'serial_number',
        'model',
        'manufacturer',
        'firmware_version',
        'status',
        'last_sync',
        'sync_interval',
        'connection_timeout',
        'max_users',
        'current_users',
        'storage_capacity',
        'used_storage',
        'battery_level',
        'temperature',
        'humidity',
        'network_status',
        'error_count',
        'last_error',
        'configuration',
        'notes',
        'is_active'
    ];
    
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'device_type' => 'required|in_list[Fingerprint,RFID,Facial,Hybrid]',
        'ip_address' => 'required|valid_ip',
        'port' => 'required|integer|greater_than[0]|less_than[65536]',
        'location' => 'required|max_length[200]',
        'serial_number' => 'permit_empty|max_length[50]',
        'model' => 'permit_empty|max_length[100]',
        'manufacturer' => 'permit_empty|max_length[100]',
        'firmware_version' => 'permit_empty|max_length[50]',
        'status' => 'required|in_list[Online,Offline,Error,Maintenance]',
        'sync_interval' => 'permit_empty|integer|greater_than[0]',
        'connection_timeout' => 'permit_empty|integer|greater_than[0]',
        'max_users' => 'permit_empty|integer|greater_than[0]',
        'current_users' => 'permit_empty|integer|greater_than_equal_to[0]',
        'storage_capacity' => 'permit_empty|integer|greater_than[0]',
        'used_storage' => 'permit_empty|integer|greater_than_equal_to[0]',
        'battery_level' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
        'temperature' => 'permit_empty|decimal',
        'humidity' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'network_status' => 'permit_empty|in_list[Connected,Disconnected,Weak,Strong]',
        'error_count' => 'permit_empty|integer|greater_than_equal_to[0]',
        'last_error' => 'permit_empty|max_length[500]',
        'configuration' => 'permit_empty',
        'notes' => 'permit_empty|max_length[1000]',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Device name is required',
            'max_length' => 'Device name cannot exceed 100 characters'
        ],
        'device_type' => [
            'required' => 'Device type is required',
            'in_list' => 'Device type must be one of: Fingerprint, RFID, Facial, Hybrid'
        ],
        'ip_address' => [
            'required' => 'IP address is required',
            'valid_ip' => 'Please enter a valid IP address'
        ],
        'port' => [
            'required' => 'Port is required',
            'integer' => 'Port must be a number',
            'greater_than' => 'Port must be greater than 0',
            'less_than' => 'Port must be less than 65536'
        ],
        'location' => [
            'required' => 'Device location is required',
            'max_length' => 'Location cannot exceed 200 characters'
        ]
    ];
    
    /**
     * Get active devices
     */
    public function getActiveDevices()
    {
        return $this->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get online devices
     */
    public function getOnlineDevices()
    {
        return $this->where('status', 'Online')
                   ->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get devices by type
     */
    public function getDevicesByType($type)
    {
        return $this->where('device_type', $type)
                   ->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStatistics()
    {
        $stats = [
            'total' => $this->where('deleted_at', null)->countAllResults(),
            'active' => $this->where('is_active', 1)->where('deleted_at', null)->countAllResults(),
            'online' => $this->where('status', 'Online')->where('deleted_at', null)->countAllResults(),
            'offline' => $this->where('status', 'Offline')->where('deleted_at', null)->countAllResults(),
            'error' => $this->where('status', 'Error')->where('deleted_at', null)->countAllResults(),
            'maintenance' => $this->where('status', 'Maintenance')->where('deleted_at', null)->countAllResults()
        ];
        
        // Get devices by type
        $deviceTypes = $this->select('device_type, COUNT(*) as count')
                           ->where('deleted_at', null)
                           ->groupBy('device_type')
                           ->findAll();
        
        $stats['by_type'] = [];
        foreach ($deviceTypes as $type) {
            $stats['by_type'][$type['device_type']] = $type['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get devices for dropdown
     */
    public function getDevicesForDropdown()
    {
        $devices = $this->select('id, name, device_type, location')
                       ->where('is_active', 1)
                       ->where('deleted_at', null)
                       ->orderBy('name', 'ASC')
                       ->findAll();
        
        $options = [];
        foreach ($devices as $device) {
            $options[$device['id']] = $device['name'] . ' (' . $device['device_type'] . ') - ' . $device['location'];
        }
        
        return $options;
    }
    
    /**
     * Update device status
     */
    public function updateDeviceStatus($deviceId, $status, $lastSync = null)
    {
        $data = ['status' => $status];
        
        if ($lastSync !== null) {
            $data['last_sync'] = $lastSync;
        }
        
        return $this->update($deviceId, $data);
    }
    
    /**
     * Update device health metrics
     */
    public function updateHealthMetrics($deviceId, $metrics)
    {
        $allowedMetrics = [
            'battery_level',
            'temperature',
            'humidity',
            'network_status',
            'current_users',
            'used_storage',
            'error_count',
            'last_error'
        ];
        
        $data = [];
        foreach ($metrics as $key => $value) {
            if (in_array($key, $allowedMetrics)) {
                $data[$key] = $value;
            }
        }
        
        if (!empty($data)) {
            return $this->update($deviceId, $data);
        }
        
        return false;
    }
    
    /**
     * Get devices needing sync
     */
    public function getDevicesNeedingSync()
    {
        $currentTime = date('Y-m-d H:i:s');
        
        return $this->where('is_active', 1)
                   ->where('status', 'Online')
                   ->where('deleted_at', null)
                   ->groupStart()
                       ->where('last_sync IS NULL')
                       ->orWhere('DATE_ADD(last_sync, INTERVAL sync_interval MINUTE) <=', $currentTime)
                   ->groupEnd()
                   ->orderBy('last_sync', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get devices with errors
     */
    public function getDevicesWithErrors()
    {
        return $this->where('status', 'Error')
                   ->orWhere('error_count >', 0)
                   ->where('deleted_at', null)
                   ->orderBy('error_count', 'DESC')
                   ->findAll();
    }
    
    /**
     * Get device by IP and port
     */
    public function getDeviceByConnection($ipAddress, $port)
    {
        return $this->where('ip_address', $ipAddress)
                   ->where('port', $port)
                   ->where('deleted_at', null)
                   ->first();
    }
    
    /**
     * Get device configuration
     */
    public function getDeviceConfiguration($deviceId)
    {
        $device = $this->find($deviceId);
        
        if (!$device) {
            return null;
        }
        
        $config = [];
        if (!empty($device['configuration'])) {
            $config = json_decode($device['configuration'], true) ?? [];
        }
        
        // Default configuration
        $defaultConfig = [
            'sync_interval' => $device['sync_interval'] ?? 30,
            'connection_timeout' => $device['connection_timeout'] ?? 10,
            'auto_sync' => true,
            'log_level' => 'INFO',
            'max_retries' => 3,
            'retry_delay' => 5,
            'heartbeat_interval' => 60,
            'data_compression' => true,
            'encryption_enabled' => false
        ];
        
        return array_merge($defaultConfig, $config);
    }
    
    /**
     * Update device configuration
     */
    public function updateDeviceConfiguration($deviceId, $config)
    {
        $data = [
            'configuration' => json_encode($config)
        ];
        
        // Update specific fields if provided
        if (isset($config['sync_interval'])) {
            $data['sync_interval'] = $config['sync_interval'];
        }
        
        if (isset($config['connection_timeout'])) {
            $data['connection_timeout'] = $config['connection_timeout'];
        }
        
        return $this->update($deviceId, $data);
    }
    
    /**
     * Test device connection
     */
    public function testConnection($deviceId)
    {
        $device = $this->find($deviceId);
        
        if (!$device) {
            return ['success' => false, 'message' => 'Device not found'];
        }
        
        try {
            // Attempt to connect to device
            $connection = @fsockopen(
                $device['ip_address'], 
                $device['port'], 
                $errno, 
                $errstr, 
                $device['connection_timeout'] ?? 10
            );
            
            if ($connection) {
                fclose($connection);
                
                // Update device status
                $this->updateDeviceStatus($deviceId, 'Online', date('Y-m-d H:i:s'));
                
                return ['success' => true, 'message' => 'Connection successful'];
            } else {
                // Update device status
                $this->updateDeviceStatus($deviceId, 'Offline');
                
                return [
                    'success' => false, 
                    'message' => "Connection failed: {$errstr} ({$errno})"
                ];
            }
        } catch (\Exception $e) {
            // Update device status
            $this->updateDeviceStatus($deviceId, 'Error');
            
            return [
                'success' => false, 
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get device uptime statistics
     */
    public function getUptimeStatistics($deviceId, $days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        // This would typically query a device_logs table
        // For now, we'll return mock data
        return [
            'uptime_percentage' => 95.5,
            'total_downtime_minutes' => 194,
            'average_response_time' => 150, // milliseconds
            'error_rate' => 2.1,
            'last_24h_uptime' => 98.2
        ];
    }
    
    /**
     * Search devices
     */
    public function searchDevices($query)
    {
        return $this->groupStart()
                       ->like('name', $query)
                       ->orLike('location', $query)
                       ->orLike('ip_address', $query)
                       ->orLike('serial_number', $query)
                       ->orLike('model', $query)
                   ->groupEnd()
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get devices with low battery
     */
    public function getDevicesWithLowBattery($threshold = 20)
    {
        return $this->where('battery_level <', $threshold)
                   ->where('battery_level IS NOT NULL')
                   ->where('deleted_at', null)
                   ->orderBy('battery_level', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get devices with high storage usage
     */
    public function getDevicesWithHighStorageUsage($threshold = 80)
    {
        return $this->where('(used_storage / storage_capacity) * 100 >', $threshold)
                   ->where('storage_capacity IS NOT NULL')
                   ->where('used_storage IS NOT NULL')
                   ->where('deleted_at', null)
                   ->orderBy('(used_storage / storage_capacity)', 'DESC')
                   ->findAll();
    }
    
    /**
     * Get device maintenance schedule
     */
    public function getMaintenanceSchedule()
    {
        // This would typically involve a separate maintenance_schedules table
        // For now, return devices that haven't been synced in a while
        $threshold = date('Y-m-d H:i:s', strtotime('-7 days'));
        
        return $this->where('last_sync <', $threshold)
                   ->orWhere('status', 'Maintenance')
                   ->where('deleted_at', null)
                   ->orderBy('last_sync', 'ASC')
                   ->findAll();
    }
}