<?php

namespace App\Modules\WhatsAppIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WhatsAppIntegration\Models\WaDeviceModel;
use App\Modules\WhatsAppIntegration\Models\WaContactModel;
use App\Modules\WhatsAppIntegration\Models\WaTemplateModel;
use App\Modules\WhatsAppIntegration\Services\WhatsAppGatewayService;
use App\Models\AttendanceModel;
use App\Models\StudentModel;

class AttendanceIntegrationController extends BaseController
{
    protected $deviceModel;
    protected $contactModel;
    protected $templateModel;
    protected $gatewayService;
    protected $attendanceModel;
    protected $studentModel;

    public function __construct()
    {
        $this->deviceModel = new WaDeviceModel();
        $this->contactModel = new WaContactModel();
        $this->templateModel = new WaTemplateModel();
        $this->gatewayService = new WhatsAppGatewayService();
        $this->attendanceModel = new AttendanceModel();
        $this->studentModel = new StudentModel();
    }

    /**
     * Attendance integration dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'Attendance Integration',
            'settings' => $this->getAttendanceSettings(),
            'stats' => $this->getAttendanceNotificationStats(),
            'recentNotifications' => $this->getRecentAttendanceNotifications()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\attendance\index', $data);
    }

    /**
     * Attendance integration settings
     */
    public function settings()
    {
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            
            try {
                $this->saveAttendanceSettings($data);
                return redirect()->back()->with('success', 'Settings saved successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        $data = [
            'title' => 'Attendance Settings',
            'settings' => $this->getAttendanceSettings(),
            'devices' => $this->deviceModel->getActiveDevices(),
            'templates' => $this->templateModel->getAttendanceTemplates()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\attendance\settings', $data);
    }

    /**
     * Send attendance notifications
     */
    public function sendNotifications()
    {
        try {
            $data = $this->request->getPost();
            $settings = $this->getAttendanceSettings();

            if (!$settings['enabled']) {
                throw new \Exception('Attendance notifications are disabled');
            }

            $notificationType = $data['type'] ?? 'checkin';
            $studentIds = $data['student_ids'] ?? [];

            $result = $this->processAttendanceNotifications($notificationType, $studentIds);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notifications sent successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify attendance (called from attendance system)
     */
    public function notifyAttendance()
    {
        try {
            $data = $this->request->getJSON(true);
            $settings = $this->getAttendanceSettings();

            if (!$settings['enabled']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Attendance notifications are disabled'
                ]);
            }

            // Validate required data
            if (!isset($data['student_id']) || !isset($data['type'])) {
                throw new \Exception('Missing required data: student_id and type');
            }

            $result = $this->sendAttendanceNotification(
                $data['student_id'],
                $data['type'],
                $data['timestamp'] ?? date('Y-m-d H:i:s'),
                $data['additional_data'] ?? []
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Notification sent',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send single attendance notification
     */
    private function sendAttendanceNotification($studentId, $type, $timestamp, $additionalData = [])
    {
        $settings = $this->getAttendanceSettings();
        
        // Get student information
        $student = $this->studentModel->find($studentId);
        if (!$student) {
            throw new \Exception('Student not found');
        }

        // Get parent contacts
        $contacts = $this->contactModel->getStudentContacts($studentId);
        $parentContacts = array_filter($contacts, function($contact) {
            return $contact['contact_type'] === 'Parent';
        });

        if (empty($parentContacts)) {
            throw new \Exception('No parent contacts found for student');
        }

        // Get appropriate template
        $templateId = $this->getTemplateForNotificationType($type, $settings);
        if (!$templateId) {
            throw new \Exception('No template configured for notification type: ' . $type);
        }

        $template = $this->templateModel->find($templateId);
        if (!$template) {
            throw new \Exception('Template not found');
        }

        // Prepare template variables
        $variables = [
            '{student_name}' => trim($student['firstname'] . ' ' . $student['lastname']),
            '{student_id}' => $student['student_id'],
            '{time}' => date('H:i', strtotime($timestamp)),
            '{date}' => date('Y-m-d', strtotime($timestamp)),
            '{day}' => date('l', strtotime($timestamp))
        ];

        // Add additional variables from attendance data
        foreach ($additionalData as $key => $value) {
            $variables['{' . $key . '}'] = $value;
        }

        $results = [];
        
        foreach ($parentContacts as $contact) {
            try {
                // Add parent-specific variables
                $parentVariables = array_merge($variables, [
                    '{parent_name}' => $contact['contact_name']
                ]);

                // Process template
                $message = $this->templateModel->processTemplate($templateId, $parentVariables);

                // Send message
                $result = $this->gatewayService->sendMessage(
                    $settings['device_id'],
                    $contact['contact_number'],
                    $message
                );

                $results[] = [
                    'contact_id' => $contact['id'],
                    'phone_number' => $contact['contact_number'],
                    'success' => $result['success'],
                    'message_id' => $result['message_id'] ?? null
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'contact_id' => $contact['id'],
                    'phone_number' => $contact['contact_number'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Log notification
        $this->logAttendanceNotification($studentId, $type, $timestamp, $results);

        return $results;
    }

    /**
     * Process bulk attendance notifications
     */
    private function processAttendanceNotifications($type, $studentIds = [])
    {
        $settings = $this->getAttendanceSettings();
        
        if (empty($studentIds)) {
            // Get all students with attendance today
            $studentIds = $this->getStudentsWithAttendanceToday($type);
        }

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($studentIds as $studentId) {
            try {
                $result = $this->sendAttendanceNotification(
                    $studentId,
                    $type,
                    date('Y-m-d H:i:s')
                );

                $success = array_filter($result, function($r) { return $r['success']; });
                $successCount += count($success);
                $failCount += count($result) - count($success);

                $results[$studentId] = $result;

            } catch (\Exception $e) {
                $failCount++;
                $results[$studentId] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'total_students' => count($studentIds),
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'results' => $results
        ];
    }

    /**
     * Get students with attendance today
     */
    private function getStudentsWithAttendanceToday($type)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('att_log a')
                   ->select('DISTINCT a.pin as student_id')
                   ->where('DATE(a.scan_date)', date('Y-m-d'));

        if ($type === 'checkin') {
            $query->where('a.inoutmode', 0); // Check-in
        } elseif ($type === 'checkout') {
            $query->where('a.inoutmode', 1); // Check-out
        }

        $results = $query->get()->getResultArray();
        
        return array_column($results, 'student_id');
    }

    /**
     * Get template for notification type
     */
    private function getTemplateForNotificationType($type, $settings)
    {
        $templateMap = [
            'checkin' => $settings['checkin_template_id'] ?? null,
            'checkout' => $settings['checkout_template_id'] ?? null,
            'absent' => $settings['absent_template_id'] ?? null,
            'late' => $settings['late_template_id'] ?? null
        ];

        return $templateMap[$type] ?? null;
    }

    /**
     * Get attendance settings
     */
    private function getAttendanceSettings()
    {
        $db = \Config\Database::connect();
        
        $settings = $db->table('wa_settings')
                      ->where('setting_key', 'attendance_integration')
                      ->get()
                      ->getRowArray();

        if ($settings) {
            return json_decode($settings['setting_value'], true);
        }

        // Default settings
        return [
            'enabled' => false,
            'device_id' => null,
            'checkin_template_id' => null,
            'checkout_template_id' => null,
            'absent_template_id' => null,
            'late_template_id' => null,
            'auto_send_checkin' => false,
            'auto_send_checkout' => false,
            'auto_send_absent' => false,
            'send_time_checkin' => '08:00',
            'send_time_checkout' => '15:00',
            'send_time_absent' => '09:00'
        ];
    }

    /**
     * Save attendance settings
     */
    private function saveAttendanceSettings($data)
    {
        $db = \Config\Database::connect();
        
        $settingsData = [
            'setting_key' => 'attendance_integration',
            'setting_value' => json_encode($data),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $existing = $db->table('wa_settings')
                      ->where('setting_key', 'attendance_integration')
                      ->get()
                      ->getRowArray();

        if ($existing) {
            $db->table('wa_settings')
               ->where('setting_key', 'attendance_integration')
               ->update($settingsData);
        } else {
            $settingsData['created_at'] = date('Y-m-d H:i:s');
            $db->table('wa_settings')->insert($settingsData);
        }
    }

    /**
     * Get attendance notification statistics
     */
    private function getAttendanceNotificationStats()
    {
        $db = \Config\Database::connect();
        
        $today = date('Y-m-d');
        
        $stats = [
            'sent_today' => $db->table('wa_attendance_logs')
                              ->where('DATE(created_at)', $today)
                              ->where('status', 'sent')
                              ->countAllResults(),
            'failed_today' => $db->table('wa_attendance_logs')
                                ->where('DATE(created_at)', $today)
                                ->where('status', 'failed')
                                ->countAllResults(),
            'total_this_month' => $db->table('wa_attendance_logs')
                                    ->where('MONTH(created_at)', date('m'))
                                    ->where('YEAR(created_at)', date('Y'))
                                    ->countAllResults()
        ];

        return $stats;
    }

    /**
     * Get recent attendance notifications
     */
    private function getRecentAttendanceNotifications()
    {
        $db = \Config\Database::connect();
        
        return $db->table('wa_attendance_logs l')
                 ->select('l.*, s.firstname, s.lastname')
                 ->join('students s', 's.student_id = l.student_id', 'left')
                 ->orderBy('l.created_at', 'DESC')
                 ->limit(10)
                 ->get()
                 ->getResultArray();
    }

    /**
     * Log attendance notification
     */
    private function logAttendanceNotification($studentId, $type, $timestamp, $results)
    {
        $db = \Config\Database::connect();
        
        $logData = [
            'student_id' => $studentId,
            'notification_type' => $type,
            'attendance_time' => $timestamp,
            'status' => 'sent',
            'results' => json_encode($results),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Check if any notification failed
        $hasFailure = false;
        foreach ($results as $result) {
            if (!$result['success']) {
                $hasFailure = true;
                break;
            }
        }

        if ($hasFailure) {
            $logData['status'] = 'partial';
        }

        if (count(array_filter($results, function($r) { return $r['success']; })) === 0) {
            $logData['status'] = 'failed';
        }

        $db->table('wa_attendance_logs')->insert($logData);
    }

    /**
     * Test attendance notification
     */
    public function testNotification()
    {
        try {
            $data = $this->request->getPost();
            
            if (!isset($data['student_id']) || !isset($data['type'])) {
                throw new \Exception('Missing student_id or type');
            }

            $result = $this->sendAttendanceNotification(
                $data['student_id'],
                $data['type'],
                date('Y-m-d H:i:s'),
                ['test_mode' => true]
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Test notification sent',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get notification history
     */
    public function notificationHistory()
    {
        $data = [
            'title' => 'Notification History',
            'logs' => $this->getRecentAttendanceNotifications(),
            'stats' => $this->getAttendanceNotificationStats()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\attendance\history', $data);
    }
}
