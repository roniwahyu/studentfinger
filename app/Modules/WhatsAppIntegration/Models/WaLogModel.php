<?php

namespace App\Modules\WhatsAppIntegration\Models;

use CodeIgniter\Model;

class WaLogModel extends Model
{
    protected $table = 'wa_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_id',
        'action',
        'data',
        'ip_address',
        'user_agent',
        'created_at'
    ];
    
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    /**
     * Log activity
     */
    public function logActivity($deviceId, $action, $data = [], $ipAddress = null, $userAgent = null)
    {
        $logData = [
            'device_id' => $deviceId,
            'action' => $action,
            'data' => json_encode($data),
            'ip_address' => $ipAddress ?: $this->getClientIP(),
            'user_agent' => $userAgent ?: $this->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($logData);
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($limit = 50)
    {
        return $this->select('wa_logs.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_logs.device_id', 'left')
                   ->orderBy('wa_logs.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get logs by device
     */
    public function getLogsByDevice($deviceId, $limit = 50)
    {
        return $this->where('device_id', $deviceId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get logs by action
     */
    public function getLogsByAction($action, $limit = 50)
    {
        return $this->select('wa_logs.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_logs.device_id', 'left')
                   ->where('wa_logs.action', $action)
                   ->orderBy('wa_logs.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get logs by date range
     */
    public function getLogsByDateRange($startDate, $endDate, $deviceId = null)
    {
        $builder = $this->select('wa_logs.*, wa_devices.device_name')
                       ->join('wa_devices', 'wa_devices.id = wa_logs.device_id', 'left')
                       ->where('DATE(wa_logs.created_at) >=', $startDate)
                       ->where('DATE(wa_logs.created_at) <=', $endDate);

        if ($deviceId) {
            $builder->where('wa_logs.device_id', $deviceId);
        }

        return $builder->orderBy('wa_logs.created_at', 'DESC')->findAll();
    }

    /**
     * Get activity summary
     */
    public function getActivitySummary($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('action, COUNT(*) as count')
                   ->where('DATE(created_at) >=', $startDate)
                   ->groupBy('action')
                   ->orderBy('count', 'DESC')
                   ->findAll();
    }

    /**
     * Get daily activity report
     */
    public function getDailyActivityReport($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('DATE(created_at) as date, action, COUNT(*) as count')
                   ->where('DATE(created_at) >=', $startDate)
                   ->groupBy(['DATE(created_at)', 'action'])
                   ->orderBy('date', 'DESC')
                   ->findAll();
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs($days = 90)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->where('DATE(created_at) <', $cutoffDate)->delete();
    }

    /**
     * Get error logs
     */
    public function getErrorLogs($limit = 50)
    {
        return $this->select('wa_logs.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_logs.device_id', 'left')
                   ->where('wa_logs.action', 'error')
                   ->orderBy('wa_logs.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get device activity statistics
     */
    public function getDeviceActivityStats($deviceId, $days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $totalActivity = $this->where('device_id', $deviceId)
                             ->where('DATE(created_at) >=', $startDate)
                             ->countAllResults();

        $byAction = $this->select('action, COUNT(*) as count')
                        ->where('device_id', $deviceId)
                        ->where('DATE(created_at) >=', $startDate)
                        ->groupBy('action')
                        ->orderBy('count', 'DESC')
                        ->findAll();

        $dailyActivity = $this->select('DATE(created_at) as date, COUNT(*) as count')
                             ->where('device_id', $deviceId)
                             ->where('DATE(created_at) >=', $startDate)
                             ->groupBy('DATE(created_at)')
                             ->orderBy('date', 'ASC')
                             ->findAll();

        return [
            'total_activity' => $totalActivity,
            'by_action' => $byAction,
            'daily_activity' => $dailyActivity
        ];
    }

    /**
     * Search logs
     */
    public function searchLogs($query, $limit = 50)
    {
        return $this->select('wa_logs.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_logs.device_id', 'left')
                   ->groupStart()
                   ->like('wa_logs.action', $query)
                   ->orLike('wa_logs.data', $query)
                   ->orLike('wa_devices.device_name', $query)
                   ->groupEnd()
                   ->orderBy('wa_logs.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get client IP address
     */
    private function getClientIP()
    {
        $request = \Config\Services::request();
        
        if ($request->getServer('HTTP_CLIENT_IP')) {
            return $request->getServer('HTTP_CLIENT_IP');
        } elseif ($request->getServer('HTTP_X_FORWARDED_FOR')) {
            return $request->getServer('HTTP_X_FORWARDED_FOR');
        } elseif ($request->getServer('HTTP_X_FORWARDED')) {
            return $request->getServer('HTTP_X_FORWARDED');
        } elseif ($request->getServer('HTTP_FORWARDED_FOR')) {
            return $request->getServer('HTTP_FORWARDED_FOR');
        } elseif ($request->getServer('HTTP_FORWARDED')) {
            return $request->getServer('HTTP_FORWARDED');
        } elseif ($request->getServer('REMOTE_ADDR')) {
            return $request->getServer('REMOTE_ADDR');
        }
        
        return 'UNKNOWN';
    }

    /**
     * Get user agent
     */
    private function getUserAgent()
    {
        $request = \Config\Services::request();
        return $request->getServer('HTTP_USER_AGENT') ?: 'UNKNOWN';
    }

    /**
     * Log message sent
     */
    public function logMessageSent($deviceId, $phoneNumber, $messageId, $success = true)
    {
        return $this->logActivity($deviceId, 'message_sent', [
            'phone_number' => $phoneNumber,
            'message_id' => $messageId,
            'success' => $success
        ]);
    }

    /**
     * Log message failed
     */
    public function logMessageFailed($deviceId, $phoneNumber, $error)
    {
        return $this->logActivity($deviceId, 'message_failed', [
            'phone_number' => $phoneNumber,
            'error' => $error
        ]);
    }

    /**
     * Log device connection test
     */
    public function logConnectionTest($deviceId, $success, $response = null)
    {
        return $this->logActivity($deviceId, 'connection_test', [
            'success' => $success,
            'response' => $response
        ]);
    }

    /**
     * Log webhook received
     */
    public function logWebhookReceived($deviceId, $webhookData)
    {
        return $this->logActivity($deviceId, 'webhook_received', [
            'webhook_data' => $webhookData
        ]);
    }

    /**
     * Log bulk message operation
     */
    public function logBulkMessage($deviceId, $totalContacts, $successCount, $failCount)
    {
        return $this->logActivity($deviceId, 'bulk_message', [
            'total_contacts' => $totalContacts,
            'success_count' => $successCount,
            'fail_count' => $failCount
        ]);
    }

    /**
     * Get log statistics
     */
    public function getLogStats($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $total = $this->where('DATE(created_at) >=', $startDate)->countAllResults();
        
        $byAction = $this->select('action, COUNT(*) as count')
                        ->where('DATE(created_at) >=', $startDate)
                        ->groupBy('action')
                        ->orderBy('count', 'DESC')
                        ->findAll();

        $byDevice = $this->select('wa_logs.device_id, wa_devices.device_name, COUNT(*) as count')
                        ->join('wa_devices', 'wa_devices.id = wa_logs.device_id', 'left')
                        ->where('DATE(wa_logs.created_at) >=', $startDate)
                        ->groupBy('wa_logs.device_id')
                        ->orderBy('count', 'DESC')
                        ->findAll();

        return [
            'total' => $total,
            'by_action' => $byAction,
            'by_device' => $byDevice
        ];
    }
}
