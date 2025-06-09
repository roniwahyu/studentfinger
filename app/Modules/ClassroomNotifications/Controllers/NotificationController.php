<?php

namespace App\Modules\ClassroomNotifications\Controllers;

use App\Controllers\BaseController;
use App\Modules\ClassroomNotifications\Models\ClassSessionModel;
use App\Modules\ClassroomNotifications\Models\NotificationTemplateModel;
use App\Modules\ClassroomNotifications\Models\NotificationLogModel;
use App\Modules\ClassroomNotifications\Services\WhatsAppService;

/**
 * Notification Controller
 * 
 * Main controller for classroom notifications dashboard and management
 */
class NotificationController extends BaseController
{
    protected $sessionModel;
    protected $templateModel;
    protected $logModel;
    protected $whatsappService;
    
    public function __construct()
    {
        $this->sessionModel = new ClassSessionModel();
        $this->templateModel = new NotificationTemplateModel();
        $this->logModel = new NotificationLogModel();
        $this->whatsappService = new WhatsAppService();
    }
    
    /**
     * Main dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'Classroom Notifications Dashboard',
            'session_stats' => $this->sessionModel->getSessionStats(),
            'notification_stats' => $this->logModel->getNotificationStats(),
            'active_sessions' => $this->sessionModel->getActiveSessions(),
            'upcoming_sessions' => $this->sessionModel->getUpcomingSessions(5),
            'recent_notifications' => $this->logModel->getRecentNotifications(10),
            'wablas_status' => $this->whatsappService->testConnection()
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\dashboard', $data);
    }
    
    /**
     * Notification templates management
     */
    public function templates()
    {
        $data = [
            'title' => 'Notification Templates',
            'templates' => $this->templateModel->orderBy('event_type', 'ASC')
                                              ->orderBy('language', 'ASC')
                                              ->findAll(),
            'event_types' => [
                NotificationTemplateModel::EVENT_SESSION_START => 'Class Started',
                NotificationTemplateModel::EVENT_SESSION_BREAK => 'Class Break',
                NotificationTemplateModel::EVENT_SESSION_RESUME => 'Class Resumed',
                NotificationTemplateModel::EVENT_SESSION_FINISH => 'Class Finished'
            ]
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\templates', $data);
    }

    /**
     * Edit template (AJAX)
     */
    public function editTemplate(int $templateId)
    {
        $template = $this->templateModel->find($templateId);
        if (!$template) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Template not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $template
        ]);
    }

    /**
     * Save notification template
     */
    public function saveTemplate()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'template_name' => 'required|min_length[3]|max_length[100]',
            'event_type' => 'required|in_list[session_start,session_break,session_resume,session_finish]',
            'message_template' => 'required|min_length[10]',
            'language' => 'required|in_list[id,en]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        // Validate template variables
        $templateValidation = $this->templateModel->validateTemplate(
            $this->request->getPost('message_template'),
            $this->request->getPost('event_type')
        );
        
        if (!$templateValidation['valid']) {
            return redirect()->back()->withInput()->with('error', 
                'Invalid variables in template: ' . implode(', ', $templateValidation['invalid_variables'])
            );
        }
        
        $data = [
            'template_name' => $this->request->getPost('template_name'),
            'event_type' => $this->request->getPost('event_type'),
            'message_template' => $this->request->getPost('message_template'),
            'language' => $this->request->getPost('language'),
            'description' => $this->request->getPost('description'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'variables' => json_encode($templateValidation['used_variables'])
        ];
        
        $templateId = $this->request->getPost('template_id');
        
        if ($templateId) {
            // Update existing template
            if ($this->templateModel->update($templateId, $data)) {
                return redirect()->back()->with('success', 'Template updated successfully');
            } else {
                return redirect()->back()->with('error', 'Failed to update template');
            }
        } else {
            // Create new template
            if ($this->templateModel->insert($data)) {
                return redirect()->back()->with('success', 'Template created successfully');
            } else {
                return redirect()->back()->with('error', 'Failed to create template');
            }
        }
    }
    
    /**
     * Delete notification template
     */
    public function deleteTemplate(int $templateId)
    {
        if ($this->templateModel->delete($templateId)) {
            return redirect()->back()->with('success', 'Template deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to delete template');
        }
    }
    
    /**
     * Notification logs
     */
    public function logs()
    {
        $filters = [
            'status' => $this->request->getGet('status'),
            'event_type' => $this->request->getGet('event_type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to')
        ];
        
        $data = [
            'title' => 'Notification Logs',
            'logs' => $this->logModel->getRecentNotifications(100),
            'filters' => $filters,
            'stats' => $this->logModel->getNotificationStats()
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\logs', $data);
    }
    
    /**
     * Session-specific notification logs
     */
    public function sessionLogs(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Session not found');
        }
        
        $data = [
            'title' => 'Session Notification Logs',
            'session' => $session,
            'logs' => $this->logModel->getSessionNotifications($sessionId),
            'delivery_report' => $this->logModel->getDeliveryReport($sessionId)
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\session_logs', $data);
    }
    
    /**
     * Settings page
     */
    public function settings()
    {
        $settingsModel = new \App\Modules\ClassroomNotifications\Models\SettingsModel();

        $data = [
            'title' => 'Notification Settings',
            'wablas_config' => $settingsModel->getWablasConfig(),
            'notification_settings' => $settingsModel->getNotificationSettings(),
            'connection_status' => $this->whatsappService->getConnectionStatus()
        ];

        return view('App\Modules\ClassroomNotifications\Views\settings', $data);
    }
    
    /**
     * Save settings
     */
    public function saveSettings()
    {
        $settingsModel = new \App\Modules\ClassroomNotifications\Models\SettingsModel();

        $validation = \Config\Services::validation();

        $rules = [
            'wablas_base_url' => 'required|valid_url',
            'wablas_token' => 'required|min_length[10]',
            'wablas_secret_key' => 'required|min_length[5]',
            'wablas_test_phone' => 'required|min_length[10]',
            'wablas_timeout' => 'required|integer|greater_than[0]',
            'school_name' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Save WABLAS configuration
        $wablasConfig = [
            'base_url' => $this->request->getPost('wablas_base_url'),
            'token' => $this->request->getPost('wablas_token'),
            'secret_key' => $this->request->getPost('wablas_secret_key'),
            'test_phone' => $this->request->getPost('wablas_test_phone'),
            'timeout' => $this->request->getPost('wablas_timeout'),
            'retry_attempts' => $this->request->getPost('wablas_retry_attempts'),
            'auto_check_interval' => $this->request->getPost('wablas_auto_check_interval')
        ];

        // Save notification settings
        $notificationSettings = [
            'auto_send_on_session_start' => $this->request->getPost('auto_send_on_session_start') ? true : false,
            'auto_send_on_session_break' => $this->request->getPost('auto_send_on_session_break') ? true : false,
            'auto_send_on_session_resume' => $this->request->getPost('auto_send_on_session_resume') ? true : false,
            'auto_send_on_session_finish' => $this->request->getPost('auto_send_on_session_finish') ? true : false,
            'default_language' => $this->request->getPost('default_language'),
            'school_name' => $this->request->getPost('school_name'),
            'notification_delay' => $this->request->getPost('notification_delay'),
            'max_retry_attempts' => $this->request->getPost('max_retry_attempts'),
            'retry_delay' => $this->request->getPost('retry_delay')
        ];

        $success = true;

        if (!$settingsModel->saveWablasConfig($wablasConfig)) {
            $success = false;
        }

        if (!$settingsModel->saveNotificationSettings($notificationSettings)) {
            $success = false;
        }

        if ($success) {
            return redirect()->back()->with('success', 'Settings saved successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to save some settings');
        }
    }
    
    /**
     * Test notification
     */
    public function testNotification()
    {
        $phone = $this->request->getPost('test_phone');
        $customMessage = $this->request->getPost('test_message');
        $eventType = $this->request->getPost('event_type') ?? NotificationTemplateModel::EVENT_SESSION_START;

        if (empty($phone)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Phone number is required'
            ]);
        }

        // If custom message is provided, use it directly
        if (!empty($customMessage)) {
            $result = $this->whatsappService->sendMessage($phone, $customMessage);
            return $this->response->setJSON($result);
        }

        // Otherwise, use template-based notification
        $testData = [
            'session_id' => 0,
            'student_id' => 0,
            'parent_phone' => $phone,
            'parent_name' => 'Test Parent',
            'event_type' => $eventType,
            'variables' => [
                'student_name' => 'Test Student',
                'parent_name' => 'Test Parent',
                'class_name' => 'Test Class',
                'subject' => 'Test Subject',
                'teacher_name' => 'Test Teacher',
                'start_time' => date('H:i'),
                'session_date' => date('d/m/Y'),
                'school_name' => 'Student Finger School',
                'break_time' => date('H:i'),
                'break_duration' => '15',
                'resume_time' => date('H:i'),
                'end_time' => date('H:i'),
                'total_duration' => '2 jam'
            ]
        ];

        $result = $this->whatsappService->sendClassroomNotification($testData);

        return $this->response->setJSON($result);
    }
    
    /**
     * Send notification via AJAX
     */
    public function sendNotification()
    {
        $sessionId = $this->request->getPost('session_id');
        $eventType = $this->request->getPost('event_type');
        $studentIds = $this->request->getPost('student_ids');
        
        if (empty($sessionId) || empty($eventType)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session ID and event type are required'
            ]);
        }
        
        // Get session details
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session not found'
            ]);
        }
        
        // Get students for the session
        $db = \Config\Database::connect();
        $studentsQuery = $db->table('students')
                           ->select('students.student_id, students.firstname, students.lastname, students.father_phone')
                           ->where('students.deleted_at', null);
        
        if (!empty($studentIds)) {
            $studentsQuery->whereIn('students.student_id', $studentIds);
        }
        
        $students = $studentsQuery->get()->getResultArray();
        
        if (empty($students)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No students found'
            ]);
        }
        
        // Prepare notifications
        $notifications = [];
        foreach ($students as $student) {
            if (empty($student['father_phone'])) {
                continue; // Skip students without parent phone
            }
            
            $variables = [
                'student_name' => $student['firstname'] . ' ' . $student['lastname'],
                'parent_name' => 'Orang Tua/Wali',
                'class_name' => $session['class_name'] ?? 'Unknown Class',
                'subject' => $session['subject'],
                'teacher_name' => $session['teacher_name'],
                'session_date' => date('d/m/Y', strtotime($session['session_date'])),
                'school_name' => 'Student Finger School'
            ];
            
            // Add event-specific variables
            switch ($eventType) {
                case NotificationTemplateModel::EVENT_SESSION_START:
                    $variables['start_time'] = date('H:i', strtotime($session['actual_start_time'] ?? $session['start_time']));
                    break;
                case NotificationTemplateModel::EVENT_SESSION_BREAK:
                    $variables['break_time'] = date('H:i', strtotime($session['actual_break_time']));
                    $variables['break_duration'] = $session['break_duration'] ?? '15';
                    break;
                case NotificationTemplateModel::EVENT_SESSION_RESUME:
                    $variables['resume_time'] = date('H:i', strtotime($session['actual_resume_time']));
                    break;
                case NotificationTemplateModel::EVENT_SESSION_FINISH:
                    $variables['end_time'] = date('H:i', strtotime($session['actual_end_time'] ?? $session['end_time']));
                    $variables['total_duration'] = $this->calculateDuration($session);
                    break;
            }
            
            $notifications[] = [
                'session_id' => $sessionId,
                'student_id' => $student['student_id'],
                'parent_phone' => $student['father_phone'],
                'parent_name' => 'Orang Tua/Wali',
                'event_type' => $eventType,
                'variables' => $variables
            ];
        }
        
        if (empty($notifications)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No valid parent phone numbers found'
            ]);
        }
        
        // Send bulk notifications
        $result = $this->whatsappService->sendBulkNotifications($notifications);
        
        // Update session notification count
        $this->sessionModel->incrementNotificationCount($sessionId, $result['sent']);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => "Sent {$result['sent']} notifications, {$result['failed']} failed",
            'details' => $result
        ]);
    }
    
    /**
     * Resend failed notification
     */
    public function resendNotification()
    {
        $logId = $this->request->getPost('log_id');
        
        $notification = $this->logModel->find($logId);
        if (!$notification) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Notification log not found'
            ]);
        }
        
        // Reconstruct notification data
        $variables = json_decode($notification['variables_used'], true) ?? [];
        
        $notificationData = [
            'session_id' => $notification['session_id'],
            'student_id' => $notification['student_id'],
            'parent_phone' => $notification['parent_phone'],
            'parent_name' => $notification['parent_name'],
            'event_type' => $notification['event_type'],
            'variables' => $variables
        ];
        
        $result = $this->whatsappService->sendClassroomNotification($notificationData);
        
        return $this->response->setJSON($result);
    }
    
    /**
     * Calculate session duration
     */
    protected function calculateDuration(array $session): string
    {
        $startTime = strtotime($session['actual_start_time'] ?? $session['start_time']);
        $endTime = strtotime($session['actual_end_time'] ?? $session['end_time']);
        
        if ($startTime && $endTime) {
            $duration = $endTime - $startTime;
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            
            if ($hours > 0) {
                return "{$hours} jam {$minutes} menit";
            } else {
                return "{$minutes} menit";
            }
        }
        
        return 'Unknown';
    }

    /**
     * Test WABLAS connection (AJAX)
     */
    public function testConnection()
    {
        $baseUrl = $this->request->getPost('base_url');
        $token = $this->request->getPost('token');
        $secretKey = $this->request->getPost('secret_key');
        $testPhone = $this->request->getPost('test_phone');

        if (empty($baseUrl) || empty($token) || empty($secretKey) || empty($testPhone)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'All fields are required for testing'
            ]);
        }

        try {
            // Clean phone number
            $cleanPhone = preg_replace('/[^0-9]/', '', $testPhone);
            if (substr($cleanPhone, 0, 1) === '0') {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            } elseif (substr($cleanPhone, 0, 2) !== '62') {
                $cleanPhone = '62' . $cleanPhone;
            }

            $url = rtrim($baseUrl, '/') . '/api/send-message';
            $authorization = $token . '.' . $secretKey;

            $testMessage = "🧪 *CONNECTION TEST*\n\nHalo! Ini adalah test koneksi WABLAS dari sistem Student Finger.\n\n✅ Konfigurasi: Berhasil\n✅ Koneksi: Terhubung\n✅ Pengiriman: Sukses\n\nSistem siap digunakan!\n\n*Student Finger School*\n\n" . date('d/m/Y H:i:s');

            $data = [
                'phone' => $cleanPhone,
                'message' => $testMessage,
                'isGroup' => false
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: ' . $authorization
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'CURL Error: ' . $error
                ]);
            }

            if ($httpCode === 200) {
                $responseData = json_decode($response, true);

                if ($responseData && isset($responseData['status']) && $responseData['status'] === true) {
                    // Update connection status
                    $connectionModel = new \App\Modules\ClassroomNotifications\Models\WhatsAppConnectionModel();
                    $connectionModel->updateStatus(
                        \App\Modules\ClassroomNotifications\Models\WhatsAppConnectionModel::STATUS_CONNECTED,
                        [
                            'device_id' => $responseData['data']['device_id'] ?? null,
                            'quota_remaining' => $responseData['data']['quota'] ?? null,
                            'api_response' => $responseData
                        ]
                    );

                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Connection test successful',
                        'data' => $responseData
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'API returned error: ' . ($responseData['message'] ?? 'Unknown error')
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'HTTP Error: ' . $httpCode
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ]);
        }
    }
}
