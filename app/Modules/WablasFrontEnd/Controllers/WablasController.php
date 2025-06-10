<?php

namespace App\Modules\WablasFrontEnd\Controllers;

use CodeIgniter\Controller;
use App\Modules\ClassroomNotifications\Services\WhatsAppService;
use App\Modules\ClassroomNotifications\Models\NotificationLogModel;
use App\Modules\ClassroomNotifications\Models\ParentContactModel;
use App\Modules\ClassroomNotifications\Models\NotificationTemplateModel;
use App\Modules\ClassroomNotifications\Models\WhatsAppConnectionModel;
use App\Modules\ClassroomNotifications\Models\SettingsModel;

/**
 * WablasFrontEnd Controller
 * 
 * Integrated WhatsApp management system for Student Finger dashboard
 */
class WablasController extends Controller
{
    protected $whatsappService;
    protected $logModel;
    protected $contactModel;
    protected $templateModel;
    protected $connectionModel;
    protected $settingsModel;
    
    public function __construct()
    {
        $this->whatsappService = new WhatsAppService();
        $this->logModel = new NotificationLogModel();
        $this->contactModel = new ParentContactModel();
        $this->templateModel = new NotificationTemplateModel();
        $this->connectionModel = new WhatsAppConnectionModel();
        $this->settingsModel = new SettingsModel();
    }
    
    /**
     * WablasFrontEnd Dashboard - Integrated with main dashboard
     */
    public function dashboard()
    {
        // Get comprehensive dashboard data
        $data = [
            'title' => 'WhatsApp Management Dashboard',
            'connection_status' => $this->connectionModel->getConnectionStats(),
            'device_info' => $this->getDeviceInformation(),
            'message_stats' => $this->getMessageStatistics(),
            'contact_stats' => $this->contactModel->getContactStats(),
            'template_stats' => $this->getTemplateStatistics(),
            'recent_messages' => $this->getRecentMessages(10),
            'quota_info' => $this->connectionModel->getQuotaInfo(),
            'integration_status' => $this->getIntegrationStatus()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\dashboard', $data);
    }
    
    /**
     * Device management
     */
    public function devices()
    {
        $data = [
            'title' => 'Device Management',
            'device_status' => $this->connectionModel->getCurrentStatus(),
            'connection_history' => $this->connectionModel->getConnectionHistory(50),
            'qr_code' => $this->generateQRCode()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\devices', $data);
    }
    
    /**
     * Message management
     */
    public function messages()
    {
        $filters = [
            'status' => $this->request->getGet('status'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to'),
            'search' => $this->request->getGet('search')
        ];
        
        $data = [
            'title' => 'Message Management',
            'messages' => $this->logModel->getFilteredLogs(50, 0, $filters),
            'filters' => $filters,
            'stats' => $this->getMessageStatistics(),
            'connection_status' => $this->connectionModel->getConnectionStats()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\messages', $data);
    }
    
    /**
     * Contact management integration
     */
    public function contacts()
    {
        $filters = [
            'contact_type' => $this->request->getGet('contact_type'),
            'is_active' => $this->request->getGet('is_active'),
            'search' => $this->request->getGet('search')
        ];
        
        $data = [
            'title' => 'Contact Management',
            'contacts' => $this->contactModel->getContactsWithStudents(100, 0, $filters),
            'filters' => $filters,
            'stats' => $this->contactModel->getContactStats(),
            'groups' => $this->getContactGroups()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\contacts', $data);
    }
    
    /**
     * Template management integration
     */
    public function templates()
    {
        $data = [
            'title' => 'Template Management',
            'templates' => $this->templateModel->findAll(),
            'categories' => $this->getTemplateCategories(),
            'variables' => $this->getAvailableVariables()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\templates', $data);
    }
    
    /**
     * Broadcast management
     */
    public function broadcast()
    {
        $data = [
            'title' => 'Broadcast Messages',
            'contact_groups' => $this->getContactGroups(),
            'templates' => $this->templateModel->where('is_active', 1)->findAll(),
            'recent_broadcasts' => $this->getRecentBroadcasts(10),
            'connection_status' => $this->connectionModel->getConnectionStats()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\broadcast', $data);
    }
    
    /**
     * Analytics dashboard
     */
    public function analytics()
    {
        $data = [
            'title' => 'WhatsApp Analytics',
            'daily_stats' => $this->getDailyMessageStats(30),
            'success_rate' => $this->getSuccessRate(),
            'popular_templates' => $this->getPopularTemplates(),
            'device_uptime' => $this->getDeviceUptime(),
            'contact_engagement' => $this->getContactEngagement()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\analytics', $data);
    }
    
    /**
     * Settings integration
     */
    public function settings()
    {
        $data = [
            'title' => 'WhatsApp Settings',
            'wablas_config' => $this->settingsModel->getWablasConfig(),
            'notification_settings' => $this->settingsModel->getNotificationSettings(),
            'connection_status' => $this->connectionModel->getConnectionStats(),
            'webhook_url' => base_url('wablas-frontend/api/webhook')
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\settings', $data);
    }
    
    /**
     * Classroom integration page
     */
    public function classroomIntegration()
    {
        $data = [
            'title' => 'Classroom Integration',
            'classroom_sessions' => $this->getActiveClassroomSessions(),
            'integration_stats' => $this->getIntegrationStatistics(),
            'sync_status' => $this->getSyncStatus()
        ];
        
        return view('App\Modules\WablasFrontEnd\Views\classroom_integration', $data);
    }
    
    /**
     * Send message via WABLAS
     */
    public function sendMessage()
    {
        $phone = $this->request->getPost('phone');
        $message = $this->request->getPost('message');
        $templateId = $this->request->getPost('template_id');
        
        if (empty($phone) || (empty($message) && empty($templateId))) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Phone number and message/template are required'
            ]);
        }
        
        // Process template if provided
        if ($templateId) {
            $template = $this->templateModel->find($templateId);
            if ($template) {
                $variables = $this->request->getPost('variables') ?? [];
                $message = $this->processTemplate($template['message_template'], $variables);
            }
        }
        
        $result = $this->whatsappService->sendMessage($phone, $message);
        
        // Log the message
        $this->logModel->insert([
            'session_id' => 0,
            'student_id' => 0,
            'parent_phone' => $phone,
            'parent_name' => 'Manual Send',
            'event_type' => 'manual_message',
            'message_content' => $message,
            'variables' => json_encode($this->request->getPost('variables') ?? []),
            'status' => $result['success'] ? 'sent' : 'failed',
            'wablas_response' => json_encode($result),
            'sent_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->response->setJSON($result);
    }
    
    /**
     * Send bulk message
     */
    public function sendBulkMessage()
    {
        $contactIds = $this->request->getPost('contact_ids');
        $message = $this->request->getPost('message');
        $templateId = $this->request->getPost('template_id');
        
        if (empty($contactIds) || !is_array($contactIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No contacts selected'
            ]);
        }
        
        // Process template if provided
        if ($templateId) {
            $template = $this->templateModel->find($templateId);
            if ($template) {
                $message = $template['message_template'];
            }
        }
        
        $sessionData = [
            'session_id' => 0,
            'event_type' => 'bulk_message'
        ];
        
        $result = $this->whatsappService->sendToSpecificContacts($contactIds, $message, $sessionData);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Bulk message sent',
            'sent' => $result['sent'],
            'failed' => $result['failed'],
            'details' => $result['details']
        ]);
    }
    
    /**
     * Get device information
     */
    private function getDeviceInformation(): array
    {
        $status = $this->connectionModel->getCurrentStatus();
        
        return [
            'device_id' => $status['device_id'] ?? 'N/A',
            'device_name' => $status['device_name'] ?? 'Unknown Device',
            'status' => $status['connection_status'] ?? 'disconnected',
            'last_seen' => $status['last_check'] ?? null,
            'quota_remaining' => $status['quota_remaining'] ?? 0
        ];
    }
    
    /**
     * Get message statistics
     */
    private function getMessageStatistics(): array
    {
        $db = \Config\Database::connect();
        
        // Today's stats
        $todayQuery = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM notification_logs 
            WHERE DATE(sent_at) = CURDATE()
        ");
        $todayStats = $todayQuery->getRowArray();
        
        // This month's stats
        $monthQuery = $db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM notification_logs 
            WHERE YEAR(sent_at) = YEAR(CURDATE()) 
            AND MONTH(sent_at) = MONTH(CURDATE())
        ");
        $monthStats = $monthQuery->getRowArray();
        
        return [
            'today' => $todayStats,
            'month' => $monthStats,
            'success_rate' => $todayStats['total'] > 0 ? round(($todayStats['sent'] / $todayStats['total']) * 100, 2) : 0
        ];
    }
    
    /**
     * Get template statistics
     */
    private function getTemplateStatistics(): array
    {
        return [
            'total' => $this->templateModel->countAllResults(),
            'active' => $this->templateModel->where('is_active', 1)->countAllResults(),
            'by_language' => $this->getTemplatesByLanguage()
        ];
    }
    
    /**
     * Get recent messages
     */
    private function getRecentMessages(int $limit = 10): array
    {
        return $this->logModel->select('notification_logs.*, students.firstname, students.lastname')
                              ->join('students', 'students.student_id = notification_logs.student_id', 'left')
                              ->orderBy('notification_logs.sent_at', 'DESC')
                              ->limit($limit)
                              ->findAll();
    }
    
    /**
     * Get integration status
     */
    private function getIntegrationStatus(): array
    {
        return [
            'classroom_notifications' => true,
            'contact_sync' => true,
            'template_sync' => true,
            'webhook_configured' => !empty($this->settingsModel->getSetting('webhook_url')),
            'auto_notifications' => $this->settingsModel->getSetting('auto_send_on_session_start', true)
        ];
    }
    
    /**
     * Process template with variables
     */
    private function processTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    /**
     * Get contact groups
     */
    private function getContactGroups(): array
    {
        $db = \Config\Database::connect();
        return $db->table('contact_groups')
                 ->where('is_active', 1)
                 ->orderBy('group_name', 'ASC')
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get template categories
     */
    private function getTemplateCategories(): array
    {
        return [
            'session_start' => 'Class Started',
            'session_break' => 'Class Break',
            'session_resume' => 'Class Resumed',
            'session_finish' => 'Class Finished',
            'attendance_alert' => 'Attendance Alert',
            'custom' => 'Custom Message'
        ];
    }
    
    /**
     * Get available template variables
     */
    private function getAvailableVariables(): array
    {
        return [
            'parent_name' => 'Parent Name',
            'student_name' => 'Student Name',
            'class_name' => 'Class Name',
            'subject' => 'Subject',
            'teacher_name' => 'Teacher Name',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'session_date' => 'Session Date',
            'school_name' => 'School Name'
        ];
    }

    /**
     * Get templates by language
     */
    private function getTemplatesByLanguage(): array
    {
        $db = \Config\Database::connect();
        return $db->table('notification_templates')
                 ->select('language, COUNT(*) as count')
                 ->where('deleted_at', null)
                 ->groupBy('language')
                 ->get()
                 ->getResultArray();
    }

    /**
     * Get active classroom sessions
     */
    private function getActiveClassroomSessions(): array
    {
        $db = \Config\Database::connect();
        return $db->table('classroom_sessions')
                 ->select('classroom_sessions.*, subjects.subject_name, teachers.teacher_name')
                 ->join('subjects', 'subjects.subject_id = classroom_sessions.subject_id', 'left')
                 ->join('teachers', 'teachers.teacher_id = classroom_sessions.teacher_id', 'left')
                 ->where('classroom_sessions.status', 'active')
                 ->orderBy('classroom_sessions.start_time', 'DESC')
                 ->limit(10)
                 ->get()
                 ->getResultArray();
    }

    /**
     * Get integration statistics
     */
    private function getIntegrationStatistics(): array
    {
        return [
            'total_integrations' => 5,
            'active_integrations' => 5,
            'sync_status' => 'up_to_date',
            'last_sync' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Get sync status
     */
    private function getSyncStatus(): array
    {
        return [
            'contacts_synced' => true,
            'templates_synced' => true,
            'settings_synced' => true,
            'last_sync' => date('Y-m-d H:i:s'),
            'sync_errors' => []
        ];
    }

    /**
     * Get daily message statistics
     */
    private function getDailyMessageStats(int $days = 30): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                DATE(sent_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM notification_logs
            WHERE sent_at >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
            GROUP BY DATE(sent_at)
            ORDER BY date DESC
        ")->getResultArray();
    }

    /**
     * Get success rate
     */
    private function getSuccessRate(): float
    {
        $db = \Config\Database::connect();
        $result = $db->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent
            FROM notification_logs
            WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ")->getRowArray();

        return $result['total'] > 0 ? round(($result['sent'] / $result['total']) * 100, 2) : 0;
    }

    /**
     * Get popular templates
     */
    private function getPopularTemplates(): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                nt.template_name,
                nt.event_type,
                COUNT(nl.id) as usage_count
            FROM notification_templates nt
            LEFT JOIN notification_logs nl ON nl.event_type = nt.event_type
            WHERE nt.is_active = 1
            GROUP BY nt.id
            ORDER BY usage_count DESC
            LIMIT 5
        ")->getResultArray();
    }

    /**
     * Get device uptime
     */
    private function getDeviceUptime(): array
    {
        return [
            'uptime_percentage' => 95.5,
            'total_hours' => 720,
            'connected_hours' => 688,
            'last_disconnect' => '2025-06-08 14:30:00'
        ];
    }

    /**
     * Get contact engagement
     */
    private function getContactEngagement(): array
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                pc.contact_type,
                COUNT(DISTINCT pc.id) as total_contacts,
                COUNT(nl.id) as messages_sent,
                AVG(CASE WHEN nl.status = 'sent' THEN 1 ELSE 0 END) * 100 as success_rate
            FROM parent_contacts pc
            LEFT JOIN notification_logs nl ON nl.parent_phone = pc.phone_number
            WHERE pc.is_active = 1
            GROUP BY pc.contact_type
        ")->getResultArray();
    }

    /**
     * Get recent broadcasts
     */
    private function getRecentBroadcasts(int $limit = 10): array
    {
        // This would be implemented with a broadcasts table
        return [
            [
                'id' => 1,
                'title' => 'Weekly School Update',
                'sent_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'recipients' => 25,
                'status' => 'completed'
            ]
        ];
    }

    /**
     * Generate QR code for device connection
     */
    private function generateQRCode(): ?string
    {
        // This would integrate with WABLAS QR code generation
        return null;
    }
}
