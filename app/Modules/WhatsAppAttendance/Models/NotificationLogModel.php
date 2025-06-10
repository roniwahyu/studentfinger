<?php

namespace App\Modules\WhatsAppAttendance\Models;

use CodeIgniter\Model;

/**
 * Notification Log Model
 * 
 * Handles notification history and logging
 */
class NotificationLogModel extends Model
{
    protected $table = 'whatsapp_notification_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'student_id',
        'parent_phone',
        'notification_type',
        'message',
        'scan_date',
        'status',
        'wablas_response',
        'error_message',
        'retry_count',
        'sent_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'student_id' => 'required|integer',
        'parent_phone' => 'required|max_length[20]',
        'notification_type' => 'required|in_list[entry,exit,late,absent,test]',
        'message' => 'required',
        'scan_date' => 'required|valid_date',
        'status' => 'required|in_list[sent,failed,pending,retry]',
        'retry_count' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required',
            'integer' => 'Student ID must be a valid integer'
        ],
        'parent_phone' => [
            'required' => 'Parent phone is required',
            'max_length' => 'Parent phone cannot exceed 20 characters'
        ],
        'notification_type' => [
            'required' => 'Notification type is required',
            'in_list' => 'Notification type must be one of: entry, exit, late, absent, test'
        ],
        'message' => [
            'required' => 'Message is required'
        ],
        'scan_date' => [
            'required' => 'Scan date is required',
            'valid_date' => 'Scan date must be a valid date'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be one of: sent, failed, pending, retry'
        ]
    ];
    
    /**
     * Get notifications by student
     */
    public function getByStudent($studentId, $limit = 50)
    {
        return $this->where('student_id', $studentId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
    
    /**
     * Get notifications by date range
     */
    public function getByDateRange($startDate, $endDate, $status = null)
    {
        $builder = $this->builder()
            ->where('scan_date >=', $startDate)
            ->where('scan_date <=', $endDate)
            ->orderBy('created_at', 'DESC');
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get today's notifications
     */
    public function getTodayNotifications($status = null)
    {
        $today = date('Y-m-d');
        
        $builder = $this->builder()
            ->where('DATE(scan_date)', $today)
            ->orderBy('created_at', 'DESC');
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get failed notifications for retry
     */
    public function getFailedNotifications($maxRetries = 3)
    {
        return $this->where('status', 'failed')
            ->where('retry_count <', $maxRetries)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
    
    /**
     * Get notification statistics
     */
    public function getStatistics($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        // Total notifications
        $total = $this->where('created_at >=', $startDate)
            ->countAllResults();
        
        // By status
        $byStatus = $this->select('status, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy('status')
            ->get()
            ->getResultArray();
        
        // By type
        $byType = $this->select('notification_type, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy('notification_type')
            ->get()
            ->getResultArray();
        
        // Daily breakdown
        $daily = $this->select('DATE(created_at) as date, status, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy(['DATE(created_at)', 'status'])
            ->orderBy('date', 'DESC')
            ->get()
            ->getResultArray();
        
        // Success rate
        $sent = $this->where('created_at >=', $startDate)
            ->where('status', 'sent')
            ->countAllResults();
        
        $successRate = $total > 0 ? round(($sent / $total) * 100, 2) : 0;
        
        return [
            'total_notifications' => $total,
            'sent_notifications' => $sent,
            'success_rate' => $successRate,
            'by_status' => $byStatus,
            'by_type' => $byType,
            'daily_breakdown' => $daily,
            'period_days' => $days
        ];
    }
    
    /**
     * Get notification summary by date
     */
    public function getDailySummary($date)
    {
        $result = $this->select('notification_type, status, COUNT(*) as count')
            ->where('DATE(scan_date)', $date)
            ->groupBy(['notification_type', 'status'])
            ->get()
            ->getResultArray();
        
        $summary = [
            'entry' => ['sent' => 0, 'failed' => 0],
            'exit' => ['sent' => 0, 'failed' => 0],
            'late' => ['sent' => 0, 'failed' => 0],
            'absent' => ['sent' => 0, 'failed' => 0],
            'total' => ['sent' => 0, 'failed' => 0]
        ];
        
        foreach ($result as $row) {
            $type = $row['notification_type'];
            $status = $row['status'];
            $count = $row['count'];
            
            if (isset($summary[$type][$status])) {
                $summary[$type][$status] = $count;
            }
            
            if ($status === 'sent' || $status === 'failed') {
                $summary['total'][$status] += $count;
            }
        }
        
        return $summary;
    }
    
    /**
     * Check if notification exists
     */
    public function notificationExists($studentId, $scanDate, $phone, $type)
    {
        return $this->where('student_id', $studentId)
            ->where('parent_phone', $phone)
            ->where('notification_type', $type)
            ->where('DATE(scan_date)', date('Y-m-d', strtotime($scanDate)))
            ->where('status', 'sent')
            ->countAllResults() > 0;
    }
    
    /**
     * Update notification status
     */
    public function updateNotificationStatus($id, $status, $errorMessage = null, $wablasResponse = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($status === 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        
        if ($errorMessage) {
            $data['error_message'] = $errorMessage;
        }
        
        if ($wablasResponse) {
            $data['wablas_response'] = is_array($wablasResponse) ? json_encode($wablasResponse) : $wablasResponse;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Increment retry count
     */
    public function incrementRetryCount($id)
    {
        $notification = $this->find($id);
        
        if ($notification) {
            $retryCount = ($notification['retry_count'] ?? 0) + 1;
            
            return $this->update($id, [
                'retry_count' => $retryCount,
                'status' => 'retry',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return false;
    }
    
    /**
     * Get recent notifications with student info
     */
    public function getRecentWithStudentInfo($limit = 100)
    {
        return $this->select('whatsapp_notification_logs.*, students.firstname, students.lastname, students.class_id, students.section_id')
            ->join('students', 'students.student_id = whatsapp_notification_logs.student_id', 'left')
            ->orderBy('whatsapp_notification_logs.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
    
    /**
     * Clean old notification logs
     */
    public function cleanOldLogs($days = 90)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $cutoffDate)
            ->delete();
    }
    
    /**
     * Get notifications by phone number
     */
    public function getByPhone($phone, $limit = 50)
    {
        return $this->where('parent_phone', $phone)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
    
    /**
     * Get error analysis
     */
    public function getErrorAnalysis($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $errors = $this->select('error_message, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->where('status', 'failed')
            ->where('error_message IS NOT NULL')
            ->groupBy('error_message')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();
        
        return $errors;
    }
    
    /**
     * Get hourly distribution
     */
    public function getHourlyDistribution($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        return $this->select('HOUR(created_at) as hour, COUNT(*) as count')
            ->where('DATE(created_at)', $date)
            ->groupBy('HOUR(created_at)')
            ->orderBy('hour', 'ASC')
            ->get()
            ->getResultArray();
    }
}