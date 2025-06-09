<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * Workflow Model
 * 
 * Manages business process workflows for classroom notifications
 */
class WorkflowModel extends Model
{
    protected $table = 'notification_workflows';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'workflow_name',
        'workflow_type',
        'trigger_event',
        'conditions',
        'actions',
        'notification_settings',
        'is_active',
        'priority',
        'description',
        'created_by',
        'last_executed'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    // Workflow types
    const TYPE_SESSION_NOTIFICATION = 'session_notification';
    const TYPE_ATTENDANCE_ALERT = 'attendance_alert';
    const TYPE_CUSTOM_MESSAGE = 'custom_message';
    const TYPE_SCHEDULED_REMINDER = 'scheduled_reminder';
    
    // Trigger events
    const TRIGGER_SESSION_START = 'session_start';
    const TRIGGER_SESSION_BREAK = 'session_break';
    const TRIGGER_SESSION_RESUME = 'session_resume';
    const TRIGGER_SESSION_FINISH = 'session_finish';
    const TRIGGER_STUDENT_ABSENT = 'student_absent';
    const TRIGGER_MANUAL = 'manual';
    const TRIGGER_SCHEDULED = 'scheduled';
    
    /**
     * Get active workflows by trigger event
     */
    public function getActiveWorkflowsByTrigger(string $triggerEvent): array
    {
        return $this->where('trigger_event', $triggerEvent)
                    ->where('is_active', 1)
                    ->orderBy('priority', 'ASC')
                    ->findAll();
    }
    
    /**
     * Execute workflow
     */
    public function executeWorkflow(int $workflowId, array $context = []): array
    {
        $workflow = $this->find($workflowId);
        if (!$workflow || !$workflow['is_active']) {
            return [
                'success' => false,
                'message' => 'Workflow not found or inactive'
            ];
        }
        
        // Check conditions
        if (!$this->checkConditions($workflow, $context)) {
            return [
                'success' => false,
                'message' => 'Workflow conditions not met'
            ];
        }
        
        // Execute actions
        $result = $this->executeActions($workflow, $context);
        
        // Update last executed time
        $this->update($workflowId, ['last_executed' => date('Y-m-d H:i:s')]);
        
        return $result;
    }
    
    /**
     * Check workflow conditions
     */
    private function checkConditions(array $workflow, array $context): bool
    {
        $conditions = json_decode($workflow['conditions'], true);
        if (empty($conditions)) {
            return true; // No conditions means always execute
        }
        
        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $value = $condition['value'];
            $contextValue = $context[$field] ?? null;
            
            switch ($operator) {
                case 'equals':
                    if ($contextValue != $value) return false;
                    break;
                case 'not_equals':
                    if ($contextValue == $value) return false;
                    break;
                case 'greater_than':
                    if ($contextValue <= $value) return false;
                    break;
                case 'less_than':
                    if ($contextValue >= $value) return false;
                    break;
                case 'contains':
                    if (strpos($contextValue, $value) === false) return false;
                    break;
                case 'in_array':
                    if (!in_array($contextValue, $value)) return false;
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Execute workflow actions
     */
    private function executeActions(array $workflow, array $context): array
    {
        $actions = json_decode($workflow['actions'], true);
        $results = [];
        
        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'send_notification':
                    $results[] = $this->executeSendNotification($action, $context);
                    break;
                case 'log_event':
                    $results[] = $this->executeLogEvent($action, $context);
                    break;
                case 'update_session':
                    $results[] = $this->executeUpdateSession($action, $context);
                    break;
                case 'send_email':
                    $results[] = $this->executeSendEmail($action, $context);
                    break;
            }
        }
        
        $successCount = count(array_filter($results, function($r) { return $r['success']; }));
        
        return [
            'success' => $successCount > 0,
            'message' => "Executed {$successCount} of " . count($results) . " actions",
            'details' => $results
        ];
    }
    
    /**
     * Execute send notification action
     */
    private function executeSendNotification(array $action, array $context): array
    {
        $whatsappService = new \App\Modules\ClassroomNotifications\Services\WhatsAppService();
        
        $notificationData = [
            'session_id' => $context['session_id'] ?? 0,
            'student_id' => $context['student_id'] ?? 0,
            'parent_phone' => $context['parent_phone'] ?? '',
            'parent_name' => $context['parent_name'] ?? '',
            'event_type' => $action['event_type'] ?? 'session_start',
            'variables' => $context['variables'] ?? []
        ];
        
        return $whatsappService->sendClassroomNotification($notificationData);
    }
    
    /**
     * Execute log event action
     */
    private function executeLogEvent(array $action, array $context): array
    {
        $logModel = new \App\Modules\ClassroomNotifications\Models\NotificationLogModel();
        
        $logData = [
            'session_id' => $context['session_id'] ?? 0,
            'student_id' => $context['student_id'] ?? 0,
            'parent_phone' => $context['parent_phone'] ?? '',
            'parent_name' => $context['parent_name'] ?? '',
            'event_type' => $action['event_type'] ?? 'workflow_log',
            'message_content' => $action['message'] ?? 'Workflow executed',
            'variables' => json_encode($context['variables'] ?? []),
            'status' => 'success'
        ];
        
        if ($logModel->insert($logData)) {
            return ['success' => true, 'message' => 'Event logged'];
        } else {
            return ['success' => false, 'message' => 'Failed to log event'];
        }
    }
    
    /**
     * Execute update session action
     */
    private function executeUpdateSession(array $action, array $context): array
    {
        if (empty($context['session_id'])) {
            return ['success' => false, 'message' => 'No session ID provided'];
        }
        
        $sessionModel = new \App\Modules\ClassroomNotifications\Models\SessionModel();
        
        $updateData = [];
        foreach ($action['updates'] as $field => $value) {
            $updateData[$field] = $value;
        }
        
        if ($sessionModel->update($context['session_id'], $updateData)) {
            return ['success' => true, 'message' => 'Session updated'];
        } else {
            return ['success' => false, 'message' => 'Failed to update session'];
        }
    }
    
    /**
     * Execute send email action
     */
    private function executeSendEmail(array $action, array $context): array
    {
        // Email functionality would be implemented here
        return ['success' => true, 'message' => 'Email functionality not implemented'];
    }
    
    /**
     * Create default workflows
     */
    public function createDefaultWorkflows(): bool
    {
        $defaultWorkflows = [
            [
                'workflow_name' => 'Auto Notify on Session Start',
                'workflow_type' => self::TYPE_SESSION_NOTIFICATION,
                'trigger_event' => self::TRIGGER_SESSION_START,
                'conditions' => json_encode([]),
                'actions' => json_encode([
                    [
                        'type' => 'send_notification',
                        'event_type' => 'session_start'
                    ],
                    [
                        'type' => 'log_event',
                        'event_type' => 'session_start',
                        'message' => 'Session start notification sent'
                    ]
                ]),
                'notification_settings' => json_encode([
                    'auto_send' => true,
                    'delay_seconds' => 0,
                    'retry_attempts' => 3
                ]),
                'is_active' => 1,
                'priority' => 1,
                'description' => 'Automatically send notification when session starts'
            ],
            [
                'workflow_name' => 'Auto Notify on Session Finish',
                'workflow_type' => self::TYPE_SESSION_NOTIFICATION,
                'trigger_event' => self::TRIGGER_SESSION_FINISH,
                'conditions' => json_encode([]),
                'actions' => json_encode([
                    [
                        'type' => 'send_notification',
                        'event_type' => 'session_finish'
                    ],
                    [
                        'type' => 'update_session',
                        'updates' => ['notification_sent' => 1]
                    ]
                ]),
                'notification_settings' => json_encode([
                    'auto_send' => true,
                    'delay_seconds' => 0,
                    'retry_attempts' => 3
                ]),
                'is_active' => 1,
                'priority' => 1,
                'description' => 'Automatically send notification when session finishes'
            ]
        ];
        
        $success = true;
        foreach ($defaultWorkflows as $workflow) {
            if (!$this->insert($workflow)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Get workflow statistics
     */
    public function getWorkflowStats(): array
    {
        $db = \Config\Database::connect();
        
        $stats = [
            'total_workflows' => $this->countAllResults(),
            'active_workflows' => $this->where('is_active', 1)->countAllResults(),
            'executed_today' => 0,
            'success_rate' => 0
        ];
        
        // Get executions today
        $todayQuery = $db->query("
            SELECT COUNT(*) as count 
            FROM notification_workflows 
            WHERE DATE(last_executed) = CURDATE() 
            AND deleted_at IS NULL
        ");
        $todayResult = $todayQuery->getRowArray();
        $stats['executed_today'] = $todayResult['count'] ?? 0;
        
        // Reset builder
        $this->builder()->resetQuery();
        
        return $stats;
    }
}
