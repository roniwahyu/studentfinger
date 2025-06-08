<?php

namespace App\Modules\WhatsAppIntegration\Models;

use CodeIgniter\Model;

class WaMessageModel extends Model
{
    protected $table = 'wa_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_id',
        'phone_number',
        'message',
        'status',
        'api_response',
        'error_message',
        'sent_at',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'device_id' => 'required|integer',
        'phone_number' => 'required|min_length[10]',
        'message' => 'required|min_length[1]'
    ];

    /**
     * Get recent messages
     */
    public function getRecentMessages($limit = 10)
    {
        return $this->select('wa_messages.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_messages.device_id', 'left')
                   ->orderBy('wa_messages.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get messages sent today count
     */
    public function getSentTodayCount()
    {
        return $this->where('DATE(created_at)', date('Y-m-d'))
                   ->where('status', 1)
                   ->countAllResults();
    }

    /**
     * Get pending messages count
     */
    public function getPendingCount()
    {
        return $this->where('status', 0)->countAllResults();
    }

    /**
     * Get failed messages count
     */
    public function getFailedCount()
    {
        return $this->where('status', 2)->countAllResults();
    }

    /**
     * Get pending messages
     */
    public function getPendingMessages()
    {
        return $this->select('wa_messages.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_messages.device_id', 'left')
                   ->where('wa_messages.status', 0)
                   ->orderBy('wa_messages.created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get failed messages
     */
    public function getFailedMessages()
    {
        return $this->select('wa_messages.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_messages.device_id', 'left')
                   ->where('wa_messages.status', 2)
                   ->orderBy('wa_messages.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Get scheduled messages
     */
    public function getScheduledMessages()
    {
        $db = \Config\Database::connect();
        
        return $db->table('wa_schedules s')
                 ->select('s.*, d.device_name')
                 ->join('wa_devices d', 'd.id = s.device_id', 'left')
                 ->where('s.status', 0)
                 ->where('s.schedule_time >', date('Y-m-d H:i:s'))
                 ->orderBy('s.schedule_time', 'ASC')
                 ->get()
                 ->getResultArray();
    }

    /**
     * Get messages by device
     */
    public function getMessagesByDevice($deviceId, $limit = 50)
    {
        return $this->where('device_id', $deviceId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get messages by phone number
     */
    public function getMessagesByPhone($phoneNumber, $limit = 20)
    {
        return $this->select('wa_messages.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_messages.device_id', 'left')
                   ->where('wa_messages.phone_number', $phoneNumber)
                   ->orderBy('wa_messages.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get message statistics
     */
    public function getMessageStats($startDate = null, $endDate = null)
    {
        $builder = $this->builder();
        
        if ($startDate) {
            $builder->where('DATE(created_at) >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('DATE(created_at) <=', $endDate);
        }

        $total = $builder->countAllResults(false);
        $sent = $builder->where('status', 1)->countAllResults(false);
        $failed = $builder->where('status', 2)->countAllResults(false);
        $pending = $builder->where('status', 0)->countAllResults();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get daily message report
     */
    public function getDailyMessageReport($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('DATE(created_at) as date, 
                             COUNT(*) as total_messages,
                             SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as sent_messages,
                             SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as failed_messages')
                   ->where('DATE(created_at) >=', $startDate)
                   ->groupBy('DATE(created_at)')
                   ->orderBy('date', 'ASC')
                   ->findAll();
    }

    /**
     * Get hourly message distribution
     */
    public function getHourlyMessageDistribution($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }

        return $this->select('HOUR(created_at) as hour, COUNT(*) as message_count')
                   ->where('DATE(created_at)', $date)
                   ->groupBy('HOUR(created_at)')
                   ->orderBy('hour', 'ASC')
                   ->findAll();
    }

    /**
     * Get top recipients
     */
    public function getTopRecipients($limit = 10, $days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('phone_number, COUNT(*) as message_count')
                   ->where('DATE(created_at) >=', $startDate)
                   ->groupBy('phone_number')
                   ->orderBy('message_count', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Retry failed message
     */
    public function retryFailedMessage($messageId)
    {
        $message = $this->find($messageId);
        if (!$message || $message['status'] != 2) {
            return false;
        }

        return $this->update($messageId, [
            'status' => 0, // Reset to pending
            'error_message' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark message as sent
     */
    public function markAsSent($messageId, $apiResponse = null)
    {
        $updateData = [
            'status' => 1,
            'sent_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($apiResponse) {
            $updateData['api_response'] = json_encode($apiResponse);
        }

        return $this->update($messageId, $updateData);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed($messageId, $errorMessage = null)
    {
        $updateData = [
            'status' => 2,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($errorMessage) {
            $updateData['error_message'] = $errorMessage;
        }

        return $this->update($messageId, $updateData);
    }

    /**
     * Get message delivery report
     */
    public function getDeliveryReport($deviceId = null, $startDate = null, $endDate = null)
    {
        $builder = $this->select('wa_messages.*, wa_devices.device_name')
                       ->join('wa_devices', 'wa_devices.id = wa_messages.device_id', 'left');

        if ($deviceId) {
            $builder->where('wa_messages.device_id', $deviceId);
        }

        if ($startDate) {
            $builder->where('DATE(wa_messages.created_at) >=', $startDate);
        }

        if ($endDate) {
            $builder->where('DATE(wa_messages.created_at) <=', $endDate);
        }

        return $builder->orderBy('wa_messages.created_at', 'DESC')->findAll();
    }

    /**
     * Clean old messages
     */
    public function cleanOldMessages($days = 90)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->where('DATE(created_at) <', $cutoffDate)
                   ->where('status !=', 0) // Don't delete pending messages
                   ->delete();
    }

    /**
     * Get message by external ID (from API response)
     */
    public function getByExternalId($externalId)
    {
        return $this->where('JSON_EXTRACT(api_response, "$.id")', $externalId)->first();
    }

    /**
     * Update message status from webhook
     */
    public function updateFromWebhook($externalId, $status, $webhookData = null)
    {
        $message = $this->getByExternalId($externalId);
        if (!$message) {
            return false;
        }

        $updateData = [
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Map webhook status to internal status
        switch (strtolower($status)) {
            case 'sent':
            case 'delivered':
                $updateData['status'] = 1;
                if (!$message['sent_at']) {
                    $updateData['sent_at'] = date('Y-m-d H:i:s');
                }
                break;
            case 'failed':
            case 'error':
                $updateData['status'] = 2;
                if ($webhookData && isset($webhookData['error'])) {
                    $updateData['error_message'] = $webhookData['error'];
                }
                break;
        }

        if ($webhookData) {
            $currentResponse = json_decode($message['api_response'] ?? '{}', true);
            $currentResponse['webhook'] = $webhookData;
            $updateData['api_response'] = json_encode($currentResponse);
        }

        return $this->update($message['id'], $updateData);
    }
}
