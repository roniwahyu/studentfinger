<?php

namespace App\Modules\WhatsAppAttendance\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Modules\WhatsAppAttendance\WhatsAppAttendanceModule;
use App\Modules\WhatsAppAttendance\Services\AttendanceMonitorService;
use App\Modules\WhatsAppAttendance\Services\DataTransferService;
use App\Modules\WhatsAppAttendance\Services\NotificationService;
use App\Modules\WhatsAppAttendance\Models\NotificationLogModel;
use App\Modules\WhatsAppAttendance\Models\TransferLogModel;
use App\Modules\WhatsAppAttendance\Models\StudentParentModel;

/**
 * WhatsApp Attendance Controller
 * 
 * Handles web interface and API endpoints for WhatsApp attendance module
 */
class WhatsAppAttendanceController extends ResourceController
{
    protected $module;
    protected $monitorService;
    protected $transferService;
    protected $notificationService;
    protected $notificationLogModel;
    protected $transferLogModel;
    protected $studentParentModel;
    
    public function __construct()
    {
        $this->module = new WhatsAppAttendanceModule();
        $this->monitorService = new AttendanceMonitorService();
        $this->transferService = new DataTransferService();
        $this->notificationService = new NotificationService();
        $this->notificationLogModel = new NotificationLogModel();
        $this->transferLogModel = new TransferLogModel();
        $this->studentParentModel = new StudentParentModel();
    }
    
    /**
     * Dashboard view
     */
    public function index()
    {
        $data = [
            'title' => 'WhatsApp Attendance Dashboard',
            'statistics' => $this->getDashboardStatistics(),
            'recent_notifications' => $this->notificationLogModel->getRecentWithStudentInfo(10),
            'recent_transfers' => $this->transferLogModel->getTodayTransfers('attendance_data'),
            'config' => config('WhatsAppAttendance')
        ];
        
        return view('WhatsAppAttendance::dashboard', $data);
    }
    
    /**
     * Start monitoring attendance
     */
    public function startMonitoring()
    {
        try {
            $result = $this->module->startMonitoring();
            
            return $this->respond([
                'success' => true,
                'message' => 'Attendance monitoring started successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to start monitoring: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Stop monitoring attendance
     */
    public function stopMonitoring()
    {
        try {
            $result = $this->module->stopMonitoring();
            
            return $this->respond([
                'success' => true,
                'message' => 'Attendance monitoring stopped successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to stop monitoring: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get monitoring status
     */
    public function getStatus()
    {
        try {
            $status = $this->module->getMonitoringStatus();
            
            return $this->respond([
                'success' => true,
                'data' => $status
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Manual data transfer
     */
    public function transferData()
    {
        try {
            $result = $this->module->transferNewData();
            
            return $this->respond([
                'success' => true,
                'message' => 'Data transfer completed successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Data transfer failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send test notification
     */
    public function sendTestNotification()
    {
        $studentId = $this->request->getPost('student_id');
        $phone = $this->request->getPost('phone');
        
        if (!$studentId) {
            return $this->respond([
                'success' => false,
                'message' => 'Student ID is required'
            ], 400);
        }
        
        try {
            $result = $this->notificationService->testNotification($studentId, $phone);
            
            return $this->respond([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Test notification sent successfully' : 'Test notification failed',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Test notification failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Run system test
     */
    public function runTest()
    {
        try {
            $result = $this->module->runTest();
            
            return $this->respond([
                'success' => true,
                'message' => 'System test completed',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'System test failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get notification logs
     */
    public function getNotificationLogs()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 50;
        $status = $this->request->getGet('status');
        $studentId = $this->request->getGet('student_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        try {
            $builder = $this->notificationLogModel->builder()
                ->select('whatsapp_notification_logs.*, students.firstname, students.lastname')
                ->join('students', 'students.student_id = whatsapp_notification_logs.student_id', 'left')
                ->orderBy('whatsapp_notification_logs.created_at', 'DESC');
            
            if ($status) {
                $builder->where('whatsapp_notification_logs.status', $status);
            }
            
            if ($studentId) {
                $builder->where('whatsapp_notification_logs.student_id', $studentId);
            }
            
            if ($startDate && $endDate) {
                $builder->where('whatsapp_notification_logs.created_at >=', $startDate)
                       ->where('whatsapp_notification_logs.created_at <=', $endDate);
            }
            
            $total = $builder->countAllResults(false);
            $logs = $builder->limit($limit, ($page - 1) * $limit)->get()->getResultArray();
            
            return $this->respond([
                'success' => true,
                'data' => [
                    'logs' => $logs,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to get notification logs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get transfer logs
     */
    public function getTransferLogs()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 50;
        $status = $this->request->getGet('status');
        $transferType = $this->request->getGet('transfer_type');
        
        try {
            $builder = $this->transferLogModel->builder()
                ->orderBy('created_at', 'DESC');
            
            if ($status) {
                $builder->where('status', $status);
            }
            
            if ($transferType) {
                $builder->where('transfer_type', $transferType);
            }
            
            $total = $builder->countAllResults(false);
            $logs = $builder->limit($limit, ($page - 1) * $limit)->get()->getResultArray();
            
            return $this->respond([
                'success' => true,
                'data' => [
                    'logs' => $logs,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'pages' => ceil($total / $limit)
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to get transfer logs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $days = $this->request->getGet('days') ?? 7;
        
        try {
            $statistics = [
                'notifications' => $this->notificationLogModel->getStatistics($days),
                'transfers' => $this->transferLogModel->getStatistics($days),
                'parents' => $this->studentParentModel->getStatistics()
            ];
            
            return $this->respond([
                'success' => true,
                'data' => $statistics
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Parent management view
     */
    public function parentManagement()
    {
        $data = [
            'title' => 'Parent Management',
            'statistics' => $this->studentParentModel->getStatistics()
        ];
        
        return view('WhatsAppAttendance::parent_management', $data);
    }
    
    /**
     * Get student parents
     */
    public function getStudentParents($studentId)
    {
        try {
            $parents = $this->studentParentModel->getByStudent($studentId);
            
            return $this->respond([
                'success' => true,
                'data' => $parents
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to get student parents: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Add parent mapping
     */
    public function addParent()
    {
        $data = $this->request->getJSON(true);
        
        try {
            $result = $this->studentParentModel->addParentMapping($data['student_id'], $data);
            
            return $this->respond([
                'success' => true,
                'message' => 'Parent mapping added successfully',
                'data' => ['id' => $result]
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to add parent mapping: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update parent mapping
     */
    public function updateParent($id)
    {
        $data = $this->request->getJSON(true);
        
        try {
            $result = $this->studentParentModel->update($id, $data);
            
            return $this->respond([
                'success' => true,
                'message' => 'Parent mapping updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to update parent mapping: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete parent mapping
     */
    public function deleteParent($id)
    {
        try {
            $result = $this->studentParentModel->delete($id);
            
            return $this->respond([
                'success' => true,
                'message' => 'Parent mapping deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to delete parent mapping: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import parents from students table
     */
    public function importParents()
    {
        try {
            $result = $this->studentParentModel->importFromStudentsTable();
            
            return $this->respond([
                'success' => true,
                'message' => 'Parent import completed',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Parent import failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get dashboard statistics
     */
    protected function getDashboardStatistics()
    {
        return [
            'today_notifications' => $this->notificationLogModel->getTodayNotifications(),
            'today_transfers' => $this->transferLogModel->getTodayTransfers(),
            'notification_stats' => $this->notificationLogModel->getStatistics(7),
            'transfer_stats' => $this->transferLogModel->getStatistics(7),
            'parent_stats' => $this->studentParentModel->getStatistics(),
            'monitoring_status' => $this->module->getMonitoringStatus()
        ];
    }
    
    /**
     * Configuration view
     */
    public function configuration()
    {
        $data = [
            'title' => 'WhatsApp Attendance Configuration',
            'config' => config('WhatsAppAttendance')
        ];
        
        return view('WhatsAppAttendance::configuration', $data);
    }
    
    /**
     * Update configuration
     */
    public function updateConfiguration()
    {
        $data = $this->request->getJSON(true);
        
        try {
            // Save configuration to file or database
            // This is a simplified implementation
            
            return $this->respond([
                'success' => true,
                'message' => 'Configuration updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->respond([
                'success' => false,
                'message' => 'Failed to update configuration: ' . $e->getMessage()
            ], 500);
        }
    }
}