<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * Notification Log Model
 * 
 * Tracks all WhatsApp notifications sent for classroom events
 */
class NotificationLogModel extends Model
{
    protected $table = 'notification_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'session_id',
        'student_id',
        'parent_phone',
        'parent_name',
        'event_type',
        'template_id',
        'message_content',
        'status',
        'wablas_response',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_reason',
        'retry_count',
        'variables_used'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_FAILED = 'failed';
    const STATUS_RETRY = 'retry';
    
    /**
     * Log a notification
     */
    public function logNotification(array $data): int
    {
        $logData = [
            'session_id' => $data['session_id'],
            'student_id' => $data['student_id'],
            'parent_phone' => $data['parent_phone'],
            'parent_name' => $data['parent_name'] ?? '',
            'event_type' => $data['event_type'],
            'template_id' => $data['template_id'] ?? null,
            'message_content' => $data['message_content'],
            'status' => self::STATUS_PENDING,
            'variables_used' => json_encode($data['variables'] ?? []),
            'sent_at' => null
        ];
        
        return $this->insert($logData);
    }
    
    /**
     * Update notification status
     */
    public function updateStatus(int $logId, string $status, array $additionalData = []): bool
    {
        $updateData = ['status' => $status];
        
        switch ($status) {
            case self::STATUS_SENT:
                $updateData['sent_at'] = date('Y-m-d H:i:s');
                if (isset($additionalData['wablas_response'])) {
                    $updateData['wablas_response'] = json_encode($additionalData['wablas_response']);
                }
                break;
                
            case self::STATUS_DELIVERED:
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                break;
                
            case self::STATUS_READ:
                $updateData['read_at'] = date('Y-m-d H:i:s');
                break;
                
            case self::STATUS_FAILED:
                if (isset($additionalData['failed_reason'])) {
                    $updateData['failed_reason'] = $additionalData['failed_reason'];
                }
                if (isset($additionalData['retry_count'])) {
                    $updateData['retry_count'] = $additionalData['retry_count'];
                }
                break;
        }
        
        return $this->update($logId, $updateData);
    }
    
    /**
     * Get notifications by session
     */
    public function getSessionNotifications(int $sessionId): array
    {
        return $this->select('notification_logs.*, students.firstname, students.lastname')
                    ->join('students', 'students.student_id = notification_logs.student_id', 'left')
                    ->where('notification_logs.session_id', $sessionId)
                    ->orderBy('notification_logs.created_at', 'DESC')
                    ->findAll();
    }
    
    /**
     * Get recent notifications
     */
    public function getRecentNotifications(int $limit = 50): array
    {
        return $this->select('notification_logs.*, students.firstname, students.lastname, class_sessions.session_name, class_sessions.subject')
                    ->join('students', 'students.student_id = notification_logs.student_id', 'left')
                    ->join('class_sessions', 'class_sessions.id = notification_logs.session_id', 'left')
                    ->orderBy('notification_logs.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats(): array
    {
        $db = $this->db;
        
        // Total notifications
        $totalQuery = $db->query("SELECT COUNT(*) as total FROM notification_logs");
        $total = $totalQuery->getRowArray()['total'] ?? 0;
        
        // Today's notifications
        $todayQuery = $db->query("SELECT COUNT(*) as today FROM notification_logs WHERE DATE(created_at) = CURDATE()");
        $today = $todayQuery->getRowArray()['today'] ?? 0;
        
        // Status breakdown
        $statusQuery = $db->query("
            SELECT 
                status,
                COUNT(*) as count
            FROM notification_logs 
            WHERE DATE(created_at) = CURDATE()
            GROUP BY status
        ");
        $statusBreakdown = [];
        foreach ($statusQuery->getResultArray() as $row) {
            $statusBreakdown[$row['status']] = $row['count'];
        }
        
        // Success rate (sent + delivered + read)
        $successCount = ($statusBreakdown[self::STATUS_SENT] ?? 0) + 
                       ($statusBreakdown[self::STATUS_DELIVERED] ?? 0) + 
                       ($statusBreakdown[self::STATUS_READ] ?? 0);
        $successRate = $today > 0 ? ($successCount / $today) * 100 : 0;
        
        // Event type breakdown
        $eventQuery = $db->query("
            SELECT 
                event_type,
                COUNT(*) as count
            FROM notification_logs 
            WHERE DATE(created_at) = CURDATE()
            GROUP BY event_type
        ");
        $eventBreakdown = [];
        foreach ($eventQuery->getResultArray() as $row) {
            $eventBreakdown[$row['event_type']] = $row['count'];
        }
        
        return [
            'total_notifications' => $total,
            'today_notifications' => $today,
            'success_rate' => round($successRate, 2),
            'status_breakdown' => $statusBreakdown,
            'event_breakdown' => $eventBreakdown,
            'pending_notifications' => $statusBreakdown[self::STATUS_PENDING] ?? 0,
            'failed_notifications' => $statusBreakdown[self::STATUS_FAILED] ?? 0
        ];
    }
    
    /**
     * Get failed notifications for retry
     */
    public function getFailedNotifications(int $maxRetries = 3): array
    {
        return $this->where('status', self::STATUS_FAILED)
                    ->where('retry_count <', $maxRetries)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get notifications by student
     */
    public function getStudentNotifications(int $studentId, int $limit = 20): array
    {
        return $this->select('notification_logs.*, class_sessions.session_name, class_sessions.subject')
                    ->join('class_sessions', 'class_sessions.id = notification_logs.session_id', 'left')
                    ->where('notification_logs.student_id', $studentId)
                    ->orderBy('notification_logs.created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get filtered notification logs with pagination
     */
    public function getFilteredLogs(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $builder = $this->select('notification_logs.*, students.firstname, students.lastname, class_sessions.session_name, class_sessions.subject')
                        ->join('students', 'students.student_id = notification_logs.student_id', 'left')
                        ->join('class_sessions', 'class_sessions.id = notification_logs.session_id', 'left');

        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('notification_logs.status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('DATE(notification_logs.created_at) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(notification_logs.created_at) <=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('notification_logs.parent_name', $filters['search'])
                    ->orLike('notification_logs.parent_phone', $filters['search'])
                    ->orLike('notification_logs.message_content', $filters['search'])
                    ->orLike('students.firstname', $filters['search'])
                    ->orLike('students.lastname', $filters['search'])
                    ->orLike('class_sessions.session_name', $filters['search'])
                    ->orLike('class_sessions.subject', $filters['search'])
                    ->groupEnd();
        }

        return $builder->orderBy('notification_logs.created_at', 'DESC')
                       ->limit($limit, $offset)
                       ->findAll();
    }
    
    /**
     * Check if notification already sent
     */
    public function isNotificationSent(int $sessionId, int $studentId, string $eventType): bool
    {
        $result = $this->where('session_id', $sessionId)
                       ->where('student_id', $studentId)
                       ->where('event_type', $eventType)
                       ->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED, self::STATUS_READ])
                       ->first();
        
        return !empty($result);
    }
    
    /**
     * Get delivery report
     */
    public function getDeliveryReport(int $sessionId): array
    {
        $query = $this->db->query("
            SELECT 
                event_type,
                status,
                COUNT(*) as count
            FROM notification_logs 
            WHERE session_id = ?
            GROUP BY event_type, status
            ORDER BY event_type, status
        ", [$sessionId]);
        
        $report = [];
        foreach ($query->getResultArray() as $row) {
            $report[$row['event_type']][$row['status']] = $row['count'];
        }
        
        return $report;
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $daysToKeep = 30): int
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$daysToKeep} days"));
        
        return $this->where('created_at <', $cutoffDate)
                    ->delete();
    }
}
