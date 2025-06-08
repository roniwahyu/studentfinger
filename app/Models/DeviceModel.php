<?php

namespace App\Models;

use CodeIgniter\Model;

class DeviceModel extends Model
{
    protected $table = 'devices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name',
        'device_type',
        'ip_address',
        'port',
        'location',
        'serial_number',
        'model',
        'status',
        'last_sync',
        'notes'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'device_type' => 'required|in_list[Fingerprint,RFID,Facial,Manual]',
        'ip_address' => 'permit_empty|valid_ip',
        'port' => 'permit_empty|integer|greater_than[0]|less_than[65536]',
        'serial_number' => 'permit_empty|max_length[100]|is_unique[devices.serial_number,id,{id}]',
        'status' => 'required|in_list[Active,Inactive,Maintenance,Error]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Device name is required'
        ],
        'device_type' => [
            'required' => 'Device type is required',
            'in_list' => 'Please select a valid device type'
        ],
        'ip_address' => [
            'valid_ip' => 'Please provide a valid IP address'
        ],
        'port' => [
            'integer' => 'Port must be a number',
            'greater_than' => 'Port must be greater than 0',
            'less_than' => 'Port must be less than 65536'
        ],
        'serial_number' => [
            'is_unique' => 'Serial number already exists'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Please select a valid status'
        ]
    ];
    
    /**
     * Get active devices
     */
    public function getActiveDevices()
    {
        return $this->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get devices by type
     */
    public function getDevicesByType($deviceType)
    {
        return $this->where('device_type', $deviceType)
                   ->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Update last sync time
     */
    public function updateLastSync($deviceId)
    {
        return $this->update($deviceId, ['last_sync' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStats()
    {
        $stats = [];
        
        // Total devices
        $stats['total'] = $this->where('deleted_at', null)->countAllResults();
        
        // Active devices
        $stats['active'] = $this->where('status', 'Active')
                               ->where('deleted_at', null)
                               ->countAllResults();
        
        // Devices by type
        $typeStats = $this->db->table($this->table)
            ->select('device_type, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('device_type')
            ->get()
            ->getResultArray();
            
        $stats['by_type'] = [];
        foreach ($typeStats as $type) {
            $stats['by_type'][$type['device_type']] = $type['count'];
        }
        
        // Devices by status
        $statusStats = $this->db->table($this->table)
            ->select('status, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('status')
            ->get()
            ->getResultArray();
            
        $stats['by_status'] = [];
        foreach ($statusStats as $status) {
            $stats['by_status'][$status['status']] = $status['count'];
        }
        
        return $stats;
    }
}