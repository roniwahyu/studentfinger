<?php

namespace App\Modules\Attendance;

use App\Libraries\BaseModule;

class AttendanceModule extends BaseModule
{
    protected $moduleName = 'Attendance';
    protected $moduleVersion = '1.0.0';
    protected $dependencies = ['StudentManagement'];
    
    public function __construct()
    {
        parent::__construct();
        $this->loadConfig();
    }
    
    /**
     * Initialize the Attendance module
     */
    public function initialize()
    {
        // Load attendance-specific configurations
        $this->loadHelpers(['attendance', 'time']);
        
        // Register attendance events
        $this->registerEvents();
        
        // Initialize attendance services
        $this->initializeServices();
        
        $this->log('Attendance module initialized successfully');
        
        return true;
    }
    
    /**
     * Load attendance helpers
     */
    private function loadHelpers($helpers)
    {
        foreach ($helpers as $helper) {
            if (file_exists(APPPATH . 'Modules/Attendance/Helpers/' . $helper . '_helper.php')) {
                helper('Modules\\Attendance\\Helpers\\' . $helper);
            }
        }
    }
    
    /**
     * Register attendance-related events
     */
    private function registerEvents()
    {
        // Register event listeners for attendance
        \CodeIgniter\Events\Events::on('attendance_marked', [$this, 'onAttendanceMarked']);
        \CodeIgniter\Events\Events::on('student_absent', [$this, 'onStudentAbsent']);
        \CodeIgniter\Events\Events::on('late_arrival', [$this, 'onLateArrival']);
    }
    
    /**
     * Initialize attendance services
     */
    private function initializeServices()
    {
        // Initialize fingerprint service
        $this->services['fingerprint'] = new \App\Modules\Attendance\Services\FingerprintService();
        
        // Initialize RFID service
        $this->services['rfid'] = new \App\Modules\Attendance\Services\RfidService();
        
        // Initialize attendance calculation service
        $this->services['calculator'] = new \App\Modules\Attendance\Services\AttendanceCalculatorService();
        
        // Initialize notification service
        $this->services['notification'] = new \App\Modules\Attendance\Services\AttendanceNotificationService();
    }
    
    /**
     * Get attendance statistics
     */
    public function getAttendanceStatistics($filters = [])
    {
        $attendanceModel = new \App\Modules\Attendance\Models\AttendanceModel();
        return $attendanceModel->getStatistics($filters);
    }
    
    /**
     * Mark attendance for a student
     */
    public function markAttendance($studentId, $type = 'fingerprint', $deviceId = null, $timestamp = null)
    {
        try {
            $attendanceModel = new \App\Modules\Attendance\Models\AttendanceModel();
            
            $data = [
                'student_id' => $studentId,
                'attendance_date' => date('Y-m-d', $timestamp ?: time()),
                'check_in_time' => date('H:i:s', $timestamp ?: time()),
                'attendance_type' => $type,
                'device_id' => $deviceId,
                'status' => 'Present'
            ];
            
            $result = $attendanceModel->insert($data);
            
            if ($result) {
                // Trigger attendance marked event
                \CodeIgniter\Events\Events::trigger('attendance_marked', $studentId, $data);
                
                $this->log("Attendance marked for student ID: {$studentId}");
                return ['success' => true, 'attendance_id' => $result];
            }
            
            return ['success' => false, 'message' => 'Failed to mark attendance'];
            
        } catch (\Exception $e) {
            $this->log("Error marking attendance: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get today's attendance summary
     */
    public function getTodayAttendanceSummary()
    {
        $attendanceModel = new \App\Modules\Attendance\Models\AttendanceModel();
        return $attendanceModel->getTodayAttendanceSummary();
    }
    
    /**
     * Get attendance report for a date range
     */
    public function getAttendanceReport($startDate, $endDate, $filters = [])
    {
        $attendanceModel = new \App\Modules\Attendance\Models\AttendanceModel();
        return $attendanceModel->getAttendanceReport($startDate, $endDate, $filters);
    }
    
    /**
     * Calculate attendance percentage for a student
     */
    public function calculateAttendancePercentage($studentId, $startDate = null, $endDate = null)
    {
        return $this->services['calculator']->calculatePercentage($studentId, $startDate, $endDate);
    }
    
    /**
     * Get absent students for a specific date
     */
    public function getAbsentStudents($date = null)
    {
        $date = $date ?: date('Y-m-d');
        $attendanceModel = new \App\Modules\Attendance\Models\AttendanceModel();
        return $attendanceModel->getAbsentStudents($date);
    }
    
    /**
     * Get late arrivals for a specific date
     */
    public function getLateArrivals($date = null)
    {
        $date = $date ?: date('Y-m-d');
        $attendanceModel = new \App\Modules\Attendance\Models\AttendanceModel();
        return $attendanceModel->getLateArrivals($date);
    }
    
    /**
     * Event handler for attendance marked
     */
    public function onAttendanceMarked($studentId, $attendanceData)
    {
        // Check if student is late
        $timetableModel = new \App\Modules\Attendance\Models\TimetableModel();
        $schedule = $timetableModel->getTodayScheduleForStudent($studentId);
        
        if ($schedule && $attendanceData['check_in_time'] > $schedule['start_time']) {
            // Trigger late arrival event
            \CodeIgniter\Events\Events::trigger('late_arrival', $studentId, $attendanceData);
        }
        
        // Send notification to parents if enabled
        if ($this->config['send_attendance_notifications']) {
            $this->services['notification']->sendAttendanceNotification($studentId, $attendanceData);
        }
    }
    
    /**
     * Event handler for student absent
     */
    public function onStudentAbsent($studentId, $date)
    {
        // Send absence notification to parents
        if ($this->config['send_absence_notifications']) {
            $this->services['notification']->sendAbsenceNotification($studentId, $date);
        }
        
        $this->log("Student absent notification sent for student ID: {$studentId}");
    }
    
    /**
     * Event handler for late arrival
     */
    public function onLateArrival($studentId, $attendanceData)
    {
        // Send late arrival notification
        if ($this->config['send_late_notifications']) {
            $this->services['notification']->sendLateArrivalNotification($studentId, $attendanceData);
        }
        
        $this->log("Late arrival notification sent for student ID: {$studentId}");
    }
    
    /**
     * Process fingerprint attendance
     */
    public function processFingerprint($fingerprintData, $deviceId)
    {
        return $this->services['fingerprint']->processAttendance($fingerprintData, $deviceId);
    }
    
    /**
     * Process RFID attendance
     */
    public function processRfid($rfidCard, $deviceId)
    {
        return $this->services['rfid']->processAttendance($rfidCard, $deviceId);
    }
    
    /**
     * Get attendance devices status
     */
    public function getDevicesStatus()
    {
        $deviceModel = new \App\Modules\Attendance\Models\DeviceModel();
        return $deviceModel->getDevicesStatus();
    }
    
    /**
     * Sync attendance data from devices
     */
    public function syncAttendanceData($deviceId = null)
    {
        try {
            $syncService = new \App\Modules\Attendance\Services\AttendanceSyncService();
            return $syncService->syncFromDevices($deviceId);
        } catch (\Exception $e) {
            $this->log("Error syncing attendance data: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Generate attendance reports
     */
    public function generateReport($type, $parameters = [])
    {
        $reportService = new \App\Modules\Attendance\Services\AttendanceReportService();
        return $reportService->generateReport($type, $parameters);
    }
    
    /**
     * Get module information
     */
    public function getModuleInfo()
    {
        return [
            'name' => $this->moduleName,
            'version' => $this->moduleVersion,
            'dependencies' => $this->dependencies,
            'status' => 'active',
            'features' => [
                'fingerprint_attendance',
                'rfid_attendance',
                'manual_attendance',
                'attendance_reports',
                'real_time_notifications',
                'device_management',
                'attendance_analytics'
            ],
            'endpoints' => [
                'mark_attendance' => '/attendance/mark',
                'get_report' => '/attendance/report',
                'sync_devices' => '/attendance/sync',
                'get_statistics' => '/attendance/statistics'
            ]
        ];
    }
}