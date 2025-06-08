<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasScheduleModel extends Model
{
    protected $table = 'wablas_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'schedule_id',
        'device_id',
        'phone_number',
        'message_type',
        'message_content',
        'media_url',
        'media_caption',
        'is_group',
        'scheduled_at',
        'status',
        'sent_at',
        'error_message',
        'retry_count',
        'max_retries',
        'template_id',
        'campaign_id',
        'api_response',
        'created_by'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'device_id' => 'required|integer',
        'phone_number' => 'required|max_length[20]',
        'message_type' => 'in_list[text,image,document,video,audio,location,list]',
        'message_content' => 'required',
        'scheduled_at' => 'required|valid_date',
        'status' => 'in_list[pending,processing,sent,failed,cancelled]',
        'retry_count' => 'integer|greater_than_equal_to[0]',
        'max_retries' => 'integer|greater_than_equal_to[0]',
        'is_group' => 'in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'device_id' => [
            'required' => 'Device ID is required'
        ],
        'phone_number' => [
            'required' => 'Phone number is required'
        ],
        'message_content' => [
            'required' => 'Message content is required'
        ],
        'scheduled_at' => [
            'required' => 'Scheduled time is required',
            'valid_date' => 'Please enter a valid date and time'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['beforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['afterFind'];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Get due messages (ready to be sent)
     */
    public function getDueMessages(): array
    {
        return $this->where('status', 'pending')
                   ->where('scheduled_at <=', date('Y-m-d H:i:s'))
                   ->where('deleted_at', null)
                   ->orderBy('scheduled_at', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get scheduled messages by device
     */
    public function getByDevice(int $deviceId, string $status = null): array
    {
        $builder = $this->where('device_id', $deviceId)
                       ->where('deleted_at', null);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('scheduled_at', 'DESC')->findAll();
    }
    
    /**
     * Get scheduled messages by phone number
     */
    public function getByPhoneNumber(string $phoneNumber, string $status = null): array
    {
        $builder = $this->where('phone_number', $phoneNumber)
                       ->where('deleted_at', null);
        
        if ($status) {
            $builder->where('status', $status);
        }
        
        return $builder->orderBy('scheduled_at', 'DESC')->findAll();
    }
    
    /**
     * Get pending schedules
     */
    public function getPendingSchedules(int $deviceId = null): array
    {
        $builder = $this->where('status', 'pending')
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('scheduled_at', 'ASC')->findAll();
    }
    
    /**
     * Get failed schedules that can be retried
     */
    public function getRetryableSchedules(int $deviceId = null): array
    {
        $builder = $this->where('status', 'failed')
                       ->where('retry_count <', 'max_retries', false)
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('scheduled_at', 'ASC')->findAll();
    }
    
    /**
     * Get schedules by campaign
     */
    public function getByCampaign(int $campaignId): array
    {
        return $this->where('campaign_id', $campaignId)
                   ->where('deleted_at', null)
                   ->orderBy('scheduled_at', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get schedule statistics
     */
    public function getStatistics(int $deviceId = null): array
    {
        $builder = $this->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        $total = $builder->countAllResults(false);
        $pending = $builder->where('status', 'pending')->countAllResults(false);
        $processing = $builder->where('status', 'processing')->countAllResults(false);
        $sent = $builder->where('status', 'sent')->countAllResults(false);
        $failed = $builder->where('status', 'failed')->countAllResults(false);
        $cancelled = $builder->where('status', 'cancelled')->countAllResults();
        
        return [
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'sent' => $sent,
            'failed' => $failed,
            'cancelled' => $cancelled,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0
        ];
    }
    
    /**
     * Cancel scheduled message
     */
    public function cancelSchedule(int $scheduleId): bool
    {
        $schedule = $this->find($scheduleId);
        
        if (!$schedule || $schedule['status'] !== 'pending') {
            return false;
        }
        
        return $this->update($scheduleId, [
            'status' => 'cancelled',
            'error_message' => 'Cancelled by user'
        ]);
    }
    
    /**
     * Reschedule message
     */
    public function rescheduleMessage(int $scheduleId, string $newScheduledAt): bool
    {
        $schedule = $this->find($scheduleId);
        
        if (!$schedule || !in_array($schedule['status'], ['pending', 'failed'])) {
            return false;
        }
        
        return $this->update($scheduleId, [
            'scheduled_at' => $newScheduledAt,
            'status' => 'pending',
            'error_message' => null
        ]);
    }
    
    /**
     * Mark as processing
     */
    public function markAsProcessing(int $scheduleId): bool
    {
        return $this->update($scheduleId, [
            'status' => 'processing'
        ]);
    }
    
    /**
     * Mark as sent
     */
    public function markAsSent(int $scheduleId, array $apiResponse = []): bool
    {
        return $this->update($scheduleId, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
            'api_response' => json_encode($apiResponse)
        ]);
    }
    
    /**
     * Mark as failed
     */
    public function markAsFailed(int $scheduleId, string $errorMessage, array $apiResponse = []): bool
    {
        $schedule = $this->find($scheduleId);
        $retryCount = $schedule ? $schedule['retry_count'] + 1 : 1;
        
        return $this->update($scheduleId, [
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => $retryCount,
            'api_response' => json_encode($apiResponse)
        ]);
    }
    
    /**
     * Get upcoming schedules
     */
    public function getUpcomingSchedules(int $hours = 24, int $deviceId = null): array
    {
        $endTime = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
        
        $builder = $this->where('status', 'pending')
                       ->where('scheduled_at <=', $endTime)
                       ->where('scheduled_at >', date('Y-m-d H:i:s'))
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('scheduled_at', 'ASC')->findAll();
    }
    
    /**
     * Clean old completed schedules
     */
    public function cleanOldSchedules(int $daysOld = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        $oldSchedules = $this->where('status', 'sent')
                            ->where('sent_at <', $cutoffDate)
                            ->findAll();
        
        $deletedCount = 0;
        foreach ($oldSchedules as $schedule) {
            if ($this->delete($schedule['id'])) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['api_response']) && is_array($data['data']['api_response'])) {
            $data['data']['api_response'] = json_encode($data['data']['api_response']);
        }
        
        // Set default values
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'pending';
        }
        
        if (!isset($data['data']['retry_count'])) {
            $data['data']['retry_count'] = 0;
        }
        
        if (!isset($data['data']['max_retries'])) {
            $data['data']['max_retries'] = 3;
        }
        
        if (!isset($data['data']['message_type'])) {
            $data['data']['message_type'] = 'text';
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data): array
    {
        if (isset($data['data']['api_response']) && is_array($data['data']['api_response'])) {
            $data['data']['api_response'] = json_encode($data['data']['api_response']);
        }
        
        return $data;
    }
    
    /**
     * After find callback
     */
    protected function afterFind(array $data): array
    {
        if (isset($data['data'])) {
            // Single record
            if (isset($data['data']['api_response']) && is_string($data['data']['api_response'])) {
                $data['data']['api_response'] = json_decode($data['data']['api_response'], true);
            }
        } else {
            // Multiple records
            foreach ($data as &$record) {
                if (isset($record['api_response']) && is_string($record['api_response'])) {
                    $record['api_response'] = json_decode($record['api_response'], true);
                }
            }
        }
        
        return $data;
    }
}
