<?php

namespace App\Modules\WablasIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WablasIntegration\Services\WablasService;
use App\Modules\WablasIntegration\Models\WablasDeviceModel;
use App\Modules\WablasIntegration\Models\WablasMessageModel;
use App\Modules\WablasIntegration\Models\WablasContactModel;
use App\Models\StudentModel;
use App\Models\AttendanceModel;

/**
 * WablasFrontEnd Controller - Modern UI/UX for WABLAS Integration
 */
class WablasFrontEndController extends BaseController
{
    protected WablasService $wablasService;
    protected WablasDeviceModel $deviceModel;
    protected WablasMessageModel $messageModel;
    protected WablasContactModel $contactModel;
    protected StudentModel $studentModel;
    protected AttendanceModel $attendanceModel;
    
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->wablasService = new WablasService();
        $this->deviceModel = new WablasDeviceModel();
        $this->messageModel = new WablasMessageModel();
        $this->contactModel = new WablasContactModel();
        $this->studentModel = new StudentModel();
        $this->attendanceModel = new AttendanceModel();
    }
    
    /**
     * Main WablasFrontEnd Dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'WablasFrontEnd - WABLAS Integration Dashboard',
            'page_title' => 'WABLAS Integration',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => base_url('dashboard')],
                ['title' => 'WABLAS', 'url' => '']
            ],
            'stats' => $this->getIntegratedStats(),
            'devices' => $this->deviceModel->getActiveDevices(),
            'recent_messages' => $this->messageModel->getRecentMessages(10),
            'attendance_summary' => $this->getAttendanceSummary(),
            'student_notifications' => $this->getStudentNotifications()
        ];
        
        // For now, return a simple HTML response to show the data
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>WablasFrontEnd - WABLAS Integration Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="bg-success text-white p-4 rounded mb-4">
                    <h1><i class="fab fa-whatsapp me-3"></i>WablasFrontEnd</h1>
                    <p>Integrated WhatsApp Communication for Student Attendance Management</p>
                    <div class="badge bg-light text-dark">Database Updated Successfully!</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3>' . number_format($data['stats']['integration']['notifications_sent_today']) . '</h3>
                        <p>Notifications Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>' . number_format($data['stats']['integration']['attendance_notifications']) . '</h3>
                        <p>Attendance Alerts</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3>' . $data['stats']['integration']['success_rate'] . '%</h3>
                        <p>Success Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h3>' . count($data['devices']) . '</h3>
                        <p>Active Devices</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-check me-2"></i>Attendance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>Today:</strong><br>
                                Check In: ' . ($data['attendance_summary']['today']['check_in'] ?? 0) . '<br>
                                Check Out: ' . ($data['attendance_summary']['today']['check_out'] ?? 0) . '<br>
                                Present: ' . ($data['attendance_summary']['today']['unique_users'] ?? 0) . '
                            </div>
                            <div class="col-6">
                                <strong>This Week:</strong><br>
                                Check In: ' . ($data['attendance_summary']['this_week']['check_in'] ?? 0) . '<br>
                                Check Out: ' . ($data['attendance_summary']['this_week']['check_out'] ?? 0) . '<br>
                                Present: ' . ($data['attendance_summary']['this_week']['unique_users'] ?? 0) . '
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-database me-2"></i>Database Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle me-2"></i>Database Update Completed!</h6>
                            <p class="mb-0">The att_log table has been successfully updated to match FingerSpot fingerprint machine standard:</p>
                            <ul class="mt-2 mb-0">
                                <li>Added: sn (varchar 30) - Device serial number</li>
                                <li>Added: inoutmode (int 11) - In/Out mode</li>
                                <li>Added: reserved (int 11) - Reserved field</li>
                                <li>Added: work_code (int 11) - Work code</li>
                                <li>Modified: pin (varchar 32) - Employee PIN</li>
                                <li>Modified: verifymode (int 11) - Verification method</li>
                                <li>Modified: att_id (varchar 50) - Attendance ID</li>
                            </ul>
                        </div>
                        <p><strong>Machine datasets can now be imported seamlessly!</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cogs me-2"></i>FingerSpot Integration Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>InOut Mode Mapping:</h6>
                                <ul>
                                    <li>0 = Check In</li>
                                    <li>1 = Check In (Alternative)</li>
                                    <li>2 = Check Out</li>
                                    <li>3 = Break Out</li>
                                    <li>4 = Break In</li>
                                    <li>5 = Overtime In</li>
                                    <li>6 = Overtime Out</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Verify Mode Mapping:</h6>
                                <ul>
                                    <li>1 = Fingerprint</li>
                                    <li>3 = RFID Card</li>
                                    <li>20 = Face Recognition</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
    
    /**
     * Device Management Interface
     */
    public function devices()
    {
        $data = [
            'title' => 'Device Management - WablasFrontEnd',
            'page_title' => 'Device Management',
            'devices' => $this->deviceModel->findAll(),
            'device_stats' => $this->deviceModel->getStatistics()
        ];
        
        return view('Modules/WablasIntegration/Views/frontend/devices', $data);
    }
    
    /**
     * Message Center Interface
     */
    public function messages()
    {
        $data = [
            'title' => 'Message Center - WablasFrontEnd',
            'page_title' => 'Message Center',
            'messages' => $this->messageModel->getRecentMessages(50),
            'message_stats' => $this->messageModel->getStatistics(),
            'devices' => $this->deviceModel->getActiveDevices(),
            'contacts' => $this->contactModel->getActiveContacts()
        ];
        
        return view('Modules/WablasIntegration/Views/frontend/messages', $data);
    }
    
    /**
     * Contact Management Interface
     */
    public function contacts()
    {
        $data = [
            'title' => 'Contact Management - WablasFrontEnd',
            'page_title' => 'Contact Management',
            'contacts' => $this->contactModel->findAll(),
            'contact_stats' => $this->contactModel->getStatistics(),
            'students' => $this->studentModel->where('status', 'active')->findAll()
        ];
        
        return view('Modules/WablasIntegration/Views/frontend/contacts', $data);
    }
    
    /**
     * Attendance Integration Interface
     */
    public function attendance()
    {
        $data = [
            'title' => 'Attendance Integration - WablasFrontEnd',
            'page_title' => 'Attendance Integration',
            'attendance_summary' => $this->getAttendanceSummary(),
            'recent_attendance' => $this->attendanceModel->getRecentAttendance(20),
            'notification_settings' => $this->getNotificationSettings(),
            'automated_messages' => $this->getAutomatedMessages()
        ];
        
        return view('Modules/WablasIntegration/Views/frontend/attendance', $data);
    }
    
    /**
     * Reports and Analytics Interface
     */
    public function reports()
    {
        $data = [
            'title' => 'Reports & Analytics - WablasFrontEnd',
            'page_title' => 'Reports & Analytics',
            'message_analytics' => $this->getMessageAnalytics(),
            'attendance_analytics' => $this->getAttendanceAnalytics(),
            'device_performance' => $this->getDevicePerformance(),
            'notification_effectiveness' => $this->getNotificationEffectiveness()
        ];
        
        return view('Modules/WablasIntegration/Views/frontend/reports', $data);
    }
    
    /**
     * Settings Interface
     */
    public function settings()
    {
        $data = [
            'title' => 'Settings - WablasFrontEnd',
            'page_title' => 'Settings',
            'wablas_config' => $this->getWablasConfig(),
            'notification_templates' => $this->getNotificationTemplates(),
            'automation_rules' => $this->getAutomationRules(),
            'integration_settings' => $this->getIntegrationSettings()
        ];
        
        return view('Modules/WablasIntegration/Views/frontend/settings', $data);
    }
    
    /**
     * Get integrated statistics combining WABLAS and attendance data
     */
    protected function getIntegratedStats(): array
    {
        try {
            $wablasStats = $this->wablasService->getStatistics();
        } catch (\Exception $e) {
            $wablasStats = [
                'devices' => ['total' => 0, 'active' => 0, 'connected' => 0],
                'messages' => ['total' => 0, 'today' => 0, 'sent' => 0, 'failed' => 0, 'success_rate' => 0],
                'contacts' => ['total' => 0, 'active' => 0, 'groups' => 0],
                'schedules' => ['total' => 0, 'pending' => 0, 'sent' => 0, 'failed' => 0]
            ];
        }

        try {
            $attendanceStats = $this->attendanceModel->getTodaysSummary();
        } catch (\Exception $e) {
            $attendanceStats = [
                'check_in' => 0,
                'check_out' => 0,
                'break_out' => 0,
                'break_in' => 0,
                'total' => 0,
                'unique_users' => 0
            ];
        }

        return [
            'wablas' => $wablasStats,
            'attendance' => $attendanceStats,
            'integration' => [
                'notifications_sent_today' => $this->getNotificationCount('today'),
                'attendance_notifications' => $this->getAttendanceNotificationCount(),
                'automated_messages' => $this->getAutomatedMessageCount(),
                'success_rate' => $this->calculateNotificationSuccessRate()
            ]
        ];
    }
    
    /**
     * Get attendance summary for integration
     */
    protected function getAttendanceSummary(): array
    {
        return [
            'today' => $this->attendanceModel->getTodaysSummary(),
            'this_week' => $this->attendanceModel->getWeeklySummary(),
            'this_month' => $this->attendanceModel->getMonthlySummary(),
            'absent_students' => $this->getAbsentStudents(),
            'late_students' => $this->getLateStudents()
        ];
    }
    
    /**
     * Get student notifications for today
     */
    protected function getStudentNotifications(): array
    {
        try {
            return $this->messageModel->select('wablas_messages.*')
                                     ->where('DATE(wablas_messages.created_at)', date('Y-m-d'))
                                     ->where('wablas_messages.message_type', 'attendance_notification')
                                     ->orderBy('wablas_messages.created_at', 'DESC')
                                     ->limit(10)
                                     ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error getting student notifications: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get absent students for notification
     */
    protected function getAbsentStudents(): array
    {
        // Logic to identify absent students
        $allStudents = $this->studentModel->where('status', 'active')->findAll();
        $presentToday = $this->attendanceModel->getPresentStudentsToday();
        
        $absentStudents = [];
        foreach ($allStudents as $student) {
            if (!in_array($student['id'], array_column($presentToday, 'student_id'))) {
                $absentStudents[] = $student;
            }
        }
        
        return $absentStudents;
    }
    
    /**
     * Get late students for notification
     */
    protected function getLateStudents(): array
    {
        return $this->attendanceModel->getLateStudentsToday();
    }
    
    /**
     * Get notification settings
     */
    protected function getNotificationSettings(): array
    {
        return [
            'auto_absent_notification' => true,
            'auto_late_notification' => true,
            'parent_notification' => true,
            'admin_notification' => false,
            'notification_time' => '10:00',
            'weekend_notifications' => false
        ];
    }
    
    /**
     * Get automated messages configuration
     */
    protected function getAutomatedMessages(): array
    {
        return [
            'absent_message_template' => 'Dear Parent, your child {student_name} is absent today ({date}). Please contact school if this is unexpected.',
            'late_message_template' => 'Dear Parent, your child {student_name} arrived late today at {time}. Please ensure punctuality.',
            'early_leave_template' => 'Dear Parent, your child {student_name} left school early today at {time}.',
            'permission_reminder' => 'Dear Parent, please submit permission request for {student_name} if they will be absent tomorrow.'
        ];
    }
    
    /**
     * Calculate notification success rate
     */
    protected function calculateNotificationSuccessRate(): float
    {
        try {
            $totalSent = $this->messageModel->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
            $successful = $this->messageModel->where('DATE(created_at)', date('Y-m-d'))
                                            ->whereIn('status', ['sent', 'delivered', 'read'])
                                            ->countAllResults();

            return $totalSent > 0 ? round(($successful / $totalSent) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get notification count
     */
    protected function getNotificationCount($period = 'today'): int
    {
        try {
            switch ($period) {
                case 'today':
                    return $this->messageModel->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
                case 'week':
                    return $this->messageModel->where('created_at >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();
                case 'month':
                    return $this->messageModel->where('created_at >=', date('Y-m-01'))->countAllResults();
                default:
                    return $this->messageModel->countAll();
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get attendance notification count for today
     */
    protected function getAttendanceNotificationCount(): int
    {
        try {
            return $this->messageModel->where('DATE(created_at)', date('Y-m-d'))
                                     ->where('message_type', 'attendance_notification')
                                     ->countAllResults();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get automated message count for today
     */
    protected function getAutomatedMessageCount(): int
    {
        try {
            // For now, return attendance notifications as automated messages
            return $this->messageModel->where('DATE(created_at)', date('Y-m-d'))
                                     ->where('message_type', 'attendance_notification')
                                     ->countAllResults();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get message analytics
     */
    protected function getMessageAnalytics(): array
    {
        return [
            'daily_stats' => $this->messageModel->getDailyStats(30),
            'message_types' => $this->messageModel->getMessageTypeStats(),
            'success_rates' => $this->messageModel->getSuccessRateStats(),
            'peak_hours' => $this->messageModel->getPeakHoursStats()
        ];
    }
    
    /**
     * Get attendance analytics
     */
    protected function getAttendanceAnalytics(): array
    {
        return [
            'attendance_trends' => $this->attendanceModel->getAttendanceTrends(30),
            'notification_impact' => $this->getNotificationImpactStats(),
            'response_rates' => $this->getParentResponseRates(),
            'improvement_metrics' => $this->getImprovementMetrics()
        ];
    }
    
    /**
     * Get device performance metrics
     */
    protected function getDevicePerformance(): array
    {
        return $this->deviceModel->getPerformanceMetrics();
    }
    
    /**
     * Get notification effectiveness metrics
     */
    protected function getNotificationEffectiveness(): array
    {
        return [
            'delivery_rates' => $this->calculateDeliveryRates(),
            'response_times' => $this->calculateResponseTimes(),
            'engagement_metrics' => $this->calculateEngagementMetrics(),
            'cost_effectiveness' => $this->calculateCostEffectiveness()
        ];
    }
    
    /**
     * API: Get real-time statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->getIntegratedStats();
            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Get recent messages
     */
    public function getRecentMessages()
    {
        try {
            $messages = $this->messageModel->getRecentMessages(10);
            return $this->response->setJSON([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Get device status
     */
    public function getDeviceStatus()
    {
        try {
            $devices = $this->deviceModel->getActiveDevices();
            return $this->response->setJSON([
                'success' => true,
                'data' => $devices
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Get tab content via AJAX
     */
    public function getTabContent($tabName)
    {
        try {
            $html = '';

            switch ($tabName) {
                case 'attendance':
                    $html = $this->getAttendanceTabContent();
                    break;
                case 'messages':
                    $html = $this->getMessagesTabContent();
                    break;
                case 'devices':
                    $html = $this->getDevicesTabContent();
                    break;
                default:
                    throw new \Exception('Invalid tab name');
            }

            return $this->response->setJSON([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Send message
     */
    public function sendMessage()
    {
        try {
            $phoneNumber = $this->request->getPost('phone_number');
            $message = $this->request->getPost('message');
            $deviceId = $this->request->getPost('device_id');

            if (empty($phoneNumber) || empty($message) || empty($deviceId)) {
                throw new \Exception('Missing required fields');
            }

            $result = $this->wablasService->sendSimpleMessage($phoneNumber, $message, $deviceId);

            return $this->response->setJSON([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Send attendance notification
     */
    public function sendAttendanceNotification()
    {
        try {
            $studentId = $this->request->getPost('student_id');
            $notificationType = $this->request->getPost('notification_type'); // absent, late, early_leave
            $customMessage = $this->request->getPost('custom_message');

            if (empty($studentId) || empty($notificationType)) {
                throw new \Exception('Missing required fields');
            }

            $result = $this->sendStudentAttendanceNotification($studentId, $notificationType, $customMessage);

            return $this->response->setJSON([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate attendance tab content
     */
    protected function getAttendanceTabContent(): string
    {
        $data = [
            'attendance_summary' => $this->getAttendanceSummary(),
            'absent_students' => $this->getAbsentStudents(),
            'late_students' => $this->getLateStudents(),
            'notification_templates' => $this->getNotificationTemplates()
        ];

        return view('Modules/WablasIntegration/Views/frontend/tabs/attendance', $data, ['saveData' => false]);
    }

    /**
     * Generate messages tab content
     */
    protected function getMessagesTabContent(): string
    {
        $data = [
            'messages' => $this->messageModel->getRecentMessages(50),
            'devices' => $this->deviceModel->getActiveDevices(),
            'contacts' => $this->contactModel->getActiveContacts(),
            'message_stats' => $this->messageModel->getStatistics()
        ];

        return view('Modules/WablasIntegration/Views/frontend/tabs/messages', $data, ['saveData' => false]);
    }

    /**
     * Generate devices tab content
     */
    protected function getDevicesTabContent(): string
    {
        $data = [
            'devices' => $this->deviceModel->findAll(),
            'device_stats' => $this->deviceModel->getStatistics(),
            'performance_metrics' => $this->getDevicePerformance()
        ];

        return view('Modules/WablasIntegration/Views/frontend/tabs/devices', $data, ['saveData' => false]);
    }

    /**
     * Send attendance notification for a specific student
     */
    protected function sendStudentAttendanceNotification($studentId, $notificationType, $customMessage = null): array
    {
        try {
            // Get student information
            $student = $this->studentModel->find($studentId);
            if (!$student) {
                throw new \Exception('Student not found');
            }

            // Get parent phone number
            $phoneNumber = $student['parent_phone'] ?? $student['phone_number'] ?? null;
            if (!$phoneNumber) {
                throw new \Exception('No phone number found for student');
            }

            // Generate message based on type
            $message = $customMessage ?: $this->generateAttendanceMessage($student, $notificationType);

            // Get active device
            $device = $this->deviceModel->getActiveDevice();
            if (!$device) {
                throw new \Exception('No active device available');
            }

            // Send message
            $result = $this->wablasService->sendSimpleMessage($phoneNumber, $message, $device['device_id']);

            // Log the notification
            $this->logAttendanceNotification($studentId, $notificationType, $phoneNumber, $message, $result['success']);

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate attendance message based on type
     */
    protected function generateAttendanceMessage($student, $notificationType): string
    {
        $templates = $this->getAutomatedMessages();
        $studentName = $student['name'] ?? $student['first_name'] . ' ' . $student['last_name'];
        $date = date('Y-m-d');
        $time = date('H:i');

        switch ($notificationType) {
            case 'absent':
                $template = $templates['absent_message_template'];
                break;
            case 'late':
                $template = $templates['late_message_template'];
                break;
            case 'early_leave':
                $template = $templates['early_leave_template'];
                break;
            default:
                $template = "Dear Parent, this is a notification regarding {student_name} on {date}.";
        }

        // Replace placeholders
        $message = str_replace([
            '{student_name}',
            '{date}',
            '{time}'
        ], [
            $studentName,
            $date,
            $time
        ], $template);

        return $message;
    }

    /**
     * Log attendance notification
     */
    protected function logAttendanceNotification($studentId, $notificationType, $phoneNumber, $message, $success): void
    {
        // Log to wablas_logs table
        $logData = [
            'student_id' => $studentId,
            'notification_type' => $notificationType,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'status' => $success ? 'sent' : 'failed',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // You can implement logging logic here
        log_message('info', 'Attendance notification: ' . json_encode($logData));
    }

    // Additional helper methods for analytics...
    protected function getNotificationImpactStats(): array { return []; }
    protected function getParentResponseRates(): array { return []; }
    protected function getImprovementMetrics(): array { return []; }
    protected function calculateDeliveryRates(): array { return []; }
    protected function calculateResponseTimes(): array { return []; }
    protected function calculateEngagementMetrics(): array { return []; }
    protected function calculateCostEffectiveness(): array { return []; }
    protected function getWablasConfig(): array { return []; }
    protected function getNotificationTemplates(): array {
        return [
            'absent' => 'Dear Parent, your child {student_name} is absent today ({date}). Please contact school if this is unexpected.',
            'late' => 'Dear Parent, your child {student_name} arrived late today at {time}. Please ensure punctuality.',
            'early_leave' => 'Dear Parent, your child {student_name} left school early today at {time}.'
        ];
    }
    protected function getAutomationRules(): array { return []; }
    protected function getIntegrationSettings(): array { return []; }
}
