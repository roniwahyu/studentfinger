<?php

namespace App\Modules\Attendance\Controllers;

use App\Modules\Attendance\Models\DeviceModel;
use App\Modules\Attendance\Models\AttendanceModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Devices extends Controller
{
    protected $deviceModel;
    protected $attendanceModel;
    
    public function __construct()
    {
        $this->deviceModel = new DeviceModel();
        $this->attendanceModel = new AttendanceModel();
    }
    
    /**
     * Display devices list
     */
    public function index()
    {
        $request = service('request');
        $pager = service('pager');
        
        // Get filters from request
        $filters = [
            'search' => $request->getGet('search'),
            'device_type' => $request->getGet('device_type'),
            'status' => $request->getGet('status'),
            'location' => $request->getGet('location')
        ];
        
        // Build query
        $builder = $this->deviceModel;
        
        // Apply filters
        if (!empty($filters['search'])) {
            $builder = $builder->groupStart()
                             ->like('name', $filters['search'])
                             ->orLike('ip_address', $filters['search'])
                             ->orLike('serial_number', $filters['search'])
                             ->orLike('location', $filters['search'])
                             ->groupEnd();
        }
        
        if (!empty($filters['device_type'])) {
            $builder = $builder->where('device_type', $filters['device_type']);
        }
        
        if (!empty($filters['status'])) {
            $builder = $builder->where('status', $filters['status']);
        }
        
        if (!empty($filters['location'])) {
            $builder = $builder->like('location', $filters['location']);
        }
        
        // Get paginated results
        $devices = $builder->orderBy('name', 'ASC')
                          ->paginate(20);
        
        // Get device statistics
        $statistics = $this->deviceModel->getDeviceStatistics();
        
        $data = [
            'title' => 'Attendance Devices',
            'devices' => $devices,
            'pager' => $this->deviceModel->pager,
            'filters' => $filters,
            'statistics' => $statistics,
            'deviceTypes' => ['Fingerprint', 'RFID', 'Facial', 'Hybrid'],
            'deviceStatuses' => ['Online', 'Offline', 'Error', 'Maintenance']
        ];
        
        return view('App\Modules\Attendance\Views\devices\index', $data);
    }
    
    /**
     * Show create device form
     */
    public function create()
    {
        $data = [
            'title' => 'Add New Device',
            'device' => $this->deviceModel->getDeviceTemplate(),
            'deviceTypes' => ['Fingerprint', 'RFID', 'Facial', 'Hybrid'],
            'deviceStatuses' => ['Online', 'Offline', 'Error', 'Maintenance']
        ];
        
        return view('App\Modules\Attendance\Views\devices\create', $data);
    }
    
    /**
     * Store new device
     */
    public function store()
    {
        $request = service('request');
        $validation = service('validation');
        
        // Custom validation rules
        $rules = $this->deviceModel->getValidationRules();
        $rules['name'] .= '|is_unique[attendance_devices.name]';
        $rules['ip_address'] .= '|is_unique[attendance_devices.ip_address]';
        
        if (!$validation->setRules($rules)->run($request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'name' => $request->getPost('name'),
            'device_type' => $request->getPost('device_type'),
            'ip_address' => $request->getPost('ip_address'),
            'port' => $request->getPost('port'),
            'location' => $request->getPost('location'),
            'serial_number' => $request->getPost('serial_number'),
            'model' => $request->getPost('model'),
            'manufacturer' => $request->getPost('manufacturer'),
            'firmware_version' => $request->getPost('firmware_version'),
            'status' => $request->getPost('status') ?? 'Offline',
            'sync_interval' => $request->getPost('sync_interval') ?? 30,
            'connection_timeout' => $request->getPost('connection_timeout') ?? 10,
            'max_users' => $request->getPost('max_users'),
            'storage_capacity' => $request->getPost('storage_capacity'),
            'notes' => $request->getPost('notes'),
            'is_active' => $request->getPost('is_active') ?? 1
        ];
        
        // Set configuration
        $configuration = [
            'sync_interval' => $data['sync_interval'],
            'connection_timeout' => $data['connection_timeout'],
            'auto_sync' => $request->getPost('auto_sync') === '1',
            'log_level' => $request->getPost('log_level') ?? 'INFO',
            'max_retries' => $request->getPost('max_retries') ?? 3,
            'retry_delay' => $request->getPost('retry_delay') ?? 5,
            'heartbeat_interval' => $request->getPost('heartbeat_interval') ?? 60,
            'data_compression' => $request->getPost('data_compression') === '1',
            'encryption_enabled' => $request->getPost('encryption_enabled') === '1'
        ];
        
        $data['configuration'] = json_encode($configuration);
        
        try {
            $deviceId = $this->deviceModel->insert($data);
            
            if ($deviceId) {
                // Test connection if device is set to online
                if ($data['status'] === 'Online') {
                    $this->deviceModel->testConnection($deviceId);
                }
                
                return redirect()->to(base_url('attendance/devices'))
                               ->with('success', 'Device added successfully.');
            } else {
                return redirect()->back()->withInput()
                               ->with('error', 'Failed to add device.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                           ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Show device details
     */
    public function show($id)
    {
        $device = $this->deviceModel->find($id);
        
        if (!$device) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Device not found');
        }
        
        // Get device configuration
        $configuration = $this->deviceModel->getDeviceConfiguration($id);
        
        // Get recent attendance records from this device
        $recentAttendance = $this->attendanceModel->select('attendance.*, students.name as student_name, students.student_id')
                                                 ->join('students', 'attendance.student_id = students.id')
                                                 ->where('attendance.device_id', $id)
                                                 ->orderBy('attendance.created_at', 'DESC')
                                                 ->limit(10)
                                                 ->findAll();
        
        // Get device statistics
        $stats = [
            'total_attendance_today' => $this->attendanceModel->where('device_id', $id)
                                                             ->where('attendance_date', date('Y-m-d'))
                                                             ->countAllResults(),
            'total_attendance_week' => $this->attendanceModel->where('device_id', $id)
                                                            ->where('attendance_date >=', date('Y-m-d', strtotime('monday this week')))
                                                            ->countAllResults(),
            'total_attendance_month' => $this->attendanceModel->where('device_id', $id)
                                                             ->where('attendance_date >=', date('Y-m-01'))
                                                             ->countAllResults(),
            'last_attendance' => $this->attendanceModel->where('device_id', $id)
                                                      ->orderBy('created_at', 'DESC')
                                                      ->first()
        ];
        
        // Get uptime statistics
        $uptimeStats = $this->deviceModel->getUptimeStatistics($id);
        
        $data = [
            'title' => 'Device Details',
            'device' => $device,
            'configuration' => $configuration,
            'recentAttendance' => $recentAttendance,
            'stats' => $stats,
            'uptimeStats' => $uptimeStats
        ];
        
        return view('App\Modules\Attendance\Views\devices\show', $data);
    }
    
    /**
     * Show edit device form
     */
    public function edit($id)
    {
        $device = $this->deviceModel->find($id);
        
        if (!$device) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Device not found');
        }
        
        // Get device configuration
        $configuration = $this->deviceModel->getDeviceConfiguration($id);
        
        $data = [
            'title' => 'Edit Device',
            'device' => $device,
            'configuration' => $configuration,
            'deviceTypes' => ['Fingerprint', 'RFID', 'Facial', 'Hybrid'],
            'deviceStatuses' => ['Online', 'Offline', 'Error', 'Maintenance']
        ];
        
        return view('App\Modules\Attendance\Views\devices\edit', $data);
    }
    
    /**
     * Update device
     */
    public function update($id)
    {
        $request = service('request');
        $validation = service('validation');
        
        $device = $this->deviceModel->find($id);
        if (!$device) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Device not found');
        }
        
        // Custom validation rules
        $rules = $this->deviceModel->getValidationRules();
        $rules['name'] .= '|is_unique[attendance_devices.name,id,' . $id . ']';
        $rules['ip_address'] .= '|is_unique[attendance_devices.ip_address,id,' . $id . ']';
        
        if (!$validation->setRules($rules)->run($request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'name' => $request->getPost('name'),
            'device_type' => $request->getPost('device_type'),
            'ip_address' => $request->getPost('ip_address'),
            'port' => $request->getPost('port'),
            'location' => $request->getPost('location'),
            'serial_number' => $request->getPost('serial_number'),
            'model' => $request->getPost('model'),
            'manufacturer' => $request->getPost('manufacturer'),
            'firmware_version' => $request->getPost('firmware_version'),
            'status' => $request->getPost('status'),
            'sync_interval' => $request->getPost('sync_interval'),
            'connection_timeout' => $request->getPost('connection_timeout'),
            'max_users' => $request->getPost('max_users'),
            'storage_capacity' => $request->getPost('storage_capacity'),
            'notes' => $request->getPost('notes'),
            'is_active' => $request->getPost('is_active') ?? 0
        ];
        
        // Update configuration
        $configuration = [
            'sync_interval' => $data['sync_interval'],
            'connection_timeout' => $data['connection_timeout'],
            'auto_sync' => $request->getPost('auto_sync') === '1',
            'log_level' => $request->getPost('log_level') ?? 'INFO',
            'max_retries' => $request->getPost('max_retries') ?? 3,
            'retry_delay' => $request->getPost('retry_delay') ?? 5,
            'heartbeat_interval' => $request->getPost('heartbeat_interval') ?? 60,
            'data_compression' => $request->getPost('data_compression') === '1',
            'encryption_enabled' => $request->getPost('encryption_enabled') === '1'
        ];
        
        $data['configuration'] = json_encode($configuration);
        
        try {
            $result = $this->deviceModel->update($id, $data);
            
            if ($result) {
                // Test connection if device is set to online
                if ($data['status'] === 'Online') {
                    $this->deviceModel->testConnection($id);
                }
                
                return redirect()->to(base_url('attendance/devices'))
                               ->with('success', 'Device updated successfully.');
            } else {
                return redirect()->back()->withInput()
                               ->with('error', 'Failed to update device.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                           ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete device
     */
    public function delete($id)
    {
        $request = service('request');
        
        if ($request->getMethod() !== 'delete') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }
        
        $device = $this->deviceModel->find($id);
        if (!$device) {
            return $this->response->setJSON(['success' => false, 'message' => 'Device not found']);
        }
        
        // Check if device has attendance records
        $attendanceCount = $this->attendanceModel->where('device_id', $id)->countAllResults();
        
        if ($attendanceCount > 0) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Cannot delete device with existing attendance records. Please archive it instead.'
            ]);
        }
        
        try {
            $result = $this->deviceModel->delete($id);
            
            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => 'Device deleted successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete device']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test device connection
     */
    public function testConnection($id)
    {
        $device = $this->deviceModel->find($id);
        
        if (!$device) {
            return $this->response->setJSON(['success' => false, 'message' => 'Device not found']);
        }
        
        try {
            $result = $this->deviceModel->testConnection($id);
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Connection test failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Sync device data
     */
    public function sync($id)
    {
        $device = $this->deviceModel->find($id);
        
        if (!$device) {
            return $this->response->setJSON(['success' => false, 'message' => 'Device not found']);
        }
        
        try {
            $syncService = new \App\Modules\Attendance\Services\DeviceSyncService();
            $result = $syncService->syncDevice($id);
            
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Sync failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get device status
     */
    public function status($id)
    {
        $device = $this->deviceModel->find($id);
        
        if (!$device) {
            return $this->response->setJSON(['success' => false, 'message' => 'Device not found']);
        }
        
        // Get real-time status
        $connectionTest = $this->deviceModel->testConnection($id);
        
        // Get device health metrics
        $healthMetrics = [
            'battery_level' => $device['battery_level'],
            'temperature' => $device['temperature'],
            'humidity' => $device['humidity'],
            'network_status' => $device['network_status'],
            'storage_usage' => $device['storage_capacity'] > 0 ? 
                              round(($device['used_storage'] / $device['storage_capacity']) * 100, 2) : 0,
            'error_count' => $device['error_count'],
            'last_sync' => $device['last_sync']
        ];
        
        return $this->response->setJSON([
            'success' => true,
            'device' => $device,
            'connection' => $connectionTest,
            'health' => $healthMetrics
        ]);
    }
    
    /**
     * Update device health metrics
     */
    public function updateHealth($id)
    {
        $request = service('request');
        $device = $this->deviceModel->find($id);
        
        if (!$device) {
            return $this->response->setJSON(['success' => false, 'message' => 'Device not found']);
        }
        
        $metrics = $request->getJSON(true);
        
        try {
            $result = $this->deviceModel->updateHealthMetrics($id, $metrics);
            
            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => 'Health metrics updated']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to update metrics']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Bulk sync devices
     */
    public function bulkSync()
    {
        $request = service('request');
        $deviceIds = $request->getPost('device_ids');
        
        if (empty($deviceIds) || !is_array($deviceIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No devices selected']);
        }
        
        try {
            $syncService = new \App\Modules\Attendance\Services\DeviceSyncService();
            $results = [];
            $successCount = 0;
            
            foreach ($deviceIds as $deviceId) {
                $result = $syncService->syncDevice($deviceId);
                $results[$deviceId] = $result;
                
                if ($result['success']) {
                    $successCount++;
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Successfully synced {$successCount} out of " . count($deviceIds) . " devices",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Bulk sync failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get devices needing maintenance
     */
    public function maintenance()
    {
        $devicesNeedingSync = $this->deviceModel->getDevicesNeedingSync();
        $devicesWithErrors = $this->deviceModel->getDevicesWithErrors();
        $devicesLowBattery = $this->deviceModel->getDevicesWithLowBattery();
        $devicesHighStorage = $this->deviceModel->getDevicesWithHighStorageUsage();
        $maintenanceSchedule = $this->deviceModel->getMaintenanceSchedule();
        
        $data = [
            'title' => 'Device Maintenance',
            'devicesNeedingSync' => $devicesNeedingSync,
            'devicesWithErrors' => $devicesWithErrors,
            'devicesLowBattery' => $devicesLowBattery,
            'devicesHighStorage' => $devicesHighStorage,
            'maintenanceSchedule' => $maintenanceSchedule
        ];
        
        return view('App\Modules\Attendance\Views\devices\maintenance', $data);
    }
    
    /**
     * Export devices data
     */
    public function export()
    {
        $request = service('request');
        $format = $request->getGet('format') ?? 'csv';
        $filters = $request->getGet();
        
        try {
            $exportService = new \App\Modules\Attendance\Services\DeviceExportService();
            $result = $exportService->export($format, $filters);
            
            if ($result['success']) {
                return $this->response->download($result['file_path'], null)
                                     ->setFileName($result['filename']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}