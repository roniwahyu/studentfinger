<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasMessageModel extends Model
{
    protected $table = 'wablas_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'message_id',
        'device_id',
        'phone_number',
        'contact_name',
        'message_type',
        'message_content',
        'media_url',
        'media_caption',
        'media_filename',
        'media_size',
        'media_mime_type',
        'is_group',
        'group_id',
        'group_name',
        'direction',
        'status',
        'error_message',
        'retry_count',
        'scheduled_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'api_response',
        'template_id',
        'campaign_id',
        'ref_id',
        'priority',
        'is_secret',
        'source',
        'metadata',
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
        'message_type' => 'in_list[text,image,document,video,audio,location,list,button,template]',
        'message_content' => 'required',
        'direction' => 'in_list[outgoing,incoming]',
        'status' => 'in_list[pending,sent,delivered,read,failed,cancelled]',
        'retry_count' => 'integer|greater_than_equal_to[0]',
        'priority' => 'in_list[0,1]',
        'is_secret' => 'in_list[0,1]',
        'is_group' => 'in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'device_id' => [
            'required' => 'Device ID is required',
            'integer' => 'Device ID must be an integer'
        ],
        'phone_number' => [
            'required' => 'Phone number is required'
        ],
        'message_content' => [
            'required' => 'Message content is required'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = ['afterInsert'];
    protected $beforeUpdate = ['beforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['afterFind'];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Get messages by device
     */
    public function getByDevice(int $deviceId, int $limit = 50, int $offset = 0): array
    {
        return $this->where('device_id', $deviceId)
                   ->where('deleted_at', null)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit, $offset)
                   ->findAll();
    }
    
    /**
     * Get messages by phone number
     */
    public function getByPhoneNumber(string $phoneNumber, int $limit = 50, int $offset = 0): array
    {
        return $this->where('phone_number', $phoneNumber)
                   ->where('deleted_at', null)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit, $offset)
                   ->findAll();
    }
    
    /**
     * Get conversation between device and phone number
     */
    public function getConversation(int $deviceId, string $phoneNumber, int $limit = 50): array
    {
        return $this->where('device_id', $deviceId)
                   ->where('phone_number', $phoneNumber)
                   ->where('deleted_at', null)
                   ->orderBy('created_at', 'ASC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get pending messages
     */
    public function getPendingMessages(int $deviceId = null): array
    {
        $builder = $this->where('status', 'pending')
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('priority', 'DESC')
                      ->orderBy('created_at', 'ASC')
                      ->findAll();
    }
    
    /**
     * Get failed messages that can be retried
     */
    public function getRetryableMessages(int $deviceId = null): array
    {
        $builder = $this->where('status', 'failed')
                       ->where('retry_count <', 3)
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('created_at', 'ASC')
                      ->findAll();
    }
    
    /**
     * Get messages by status
     */
    public function getByStatus(string $status, int $deviceId = null, int $limit = 50): array
    {
        $builder = $this->where('status', $status)
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('created_at', 'DESC')
                      ->limit($limit)
                      ->findAll();
    }
    
    /**
     * Get recent messages
     */
    public function getRecentMessages(int $limit = 20): array
    {
        return $this->select('wablas_messages.*, wablas_devices.device_name')
                   ->join('wablas_devices', 'wablas_devices.id = wablas_messages.device_id')
                   ->where('wablas_messages.deleted_at', null)
                   ->orderBy('wablas_messages.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get message statistics
     */
    public function getStatistics(int $deviceId = null, string $period = 'today'): array
    {
        $builder = $this->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        // Apply date filter
        switch ($period) {
            case 'today':
                $builder->where('DATE(created_at)', date('Y-m-d'));
                break;
            case 'yesterday':
                $builder->where('DATE(created_at)', date('Y-m-d', strtotime('-1 day')));
                break;
            case 'this_week':
                $builder->where('WEEK(created_at)', date('W'));
                break;
            case 'this_month':
                $builder->where('MONTH(created_at)', date('m'))
                       ->where('YEAR(created_at)', date('Y'));
                break;
        }
        
        $total = $builder->countAllResults(false);
        $sent = $builder->where('status', 'sent')->countAllResults(false);
        $delivered = $builder->where('status', 'delivered')->countAllResults(false);
        $read = $builder->where('status', 'read')->countAllResults(false);
        $failed = $builder->where('status', 'failed')->countAllResults(false);
        $pending = $builder->where('status', 'pending')->countAllResults();
        
        return [
            'total' => $total,
            'sent' => $sent,
            'delivered' => $delivered,
            'read' => $read,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0
        ];
    }
    
    /**
     * Update message status
     */
    public function updateStatus(int $messageId, string $status, array $additionalData = []): bool
    {
        $updateData = array_merge(['status' => $status], $additionalData);
        
        // Set timestamp based on status
        switch ($status) {
            case 'sent':
                $updateData['sent_at'] = date('Y-m-d H:i:s');
                break;
            case 'delivered':
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                break;
            case 'read':
                $updateData['read_at'] = date('Y-m-d H:i:s');
                break;
        }
        
        return $this->update($messageId, $updateData);
    }
    
    /**
     * Mark message as secret (to be deleted after sending)
     */
    public function markAsSecret(int $messageId): bool
    {
        return $this->update($messageId, ['is_secret' => 1]);
    }
    
    /**
     * Delete secret messages that have been sent
     */
    public function deleteSecretMessages(): int
    {
        $secretMessages = $this->where('is_secret', 1)
                              ->where('status', 'sent')
                              ->where('sent_at <', date('Y-m-d H:i:s', strtotime('-1 hour')))
                              ->findAll();
        
        $deletedCount = 0;
        foreach ($secretMessages as $message) {
            if ($this->delete($message['id'])) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
    
    /**
     * Get message by external ID
     */
    public function getByExternalId(string $messageId): ?array
    {
        return $this->where('message_id', $messageId)
                   ->where('deleted_at', null)
                   ->first();
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['api_response']) && is_array($data['data']['api_response'])) {
            $data['data']['api_response'] = json_encode($data['data']['api_response']);
        }
        
        if (isset($data['data']['metadata']) && is_array($data['data']['metadata'])) {
            $data['data']['metadata'] = json_encode($data['data']['metadata']);
        }
        
        return $data;
    }
    
    /**
     * After insert callback
     */
    protected function afterInsert(array $data): array
    {
        // Update contact's last message time
        if (isset($data['data']['phone_number'])) {
            $contactModel = new WablasContactModel();
            $contact = $contactModel->getByPhoneNumber($data['data']['phone_number']);
            
            if ($contact) {
                $contactModel->update($contact['id'], [
                    'last_message_at' => date('Y-m-d H:i:s'),
                    'message_count' => $contact['message_count'] + 1
                ]);
            }
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
        
        if (isset($data['data']['metadata']) && is_array($data['data']['metadata'])) {
            $data['data']['metadata'] = json_encode($data['data']['metadata']);
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
            if (isset($data['data']['metadata']) && is_string($data['data']['metadata'])) {
                $data['data']['metadata'] = json_decode($data['data']['metadata'], true);
            }
        } else {
            // Multiple records
            foreach ($data as &$record) {
                if (isset($record['api_response']) && is_string($record['api_response'])) {
                    $record['api_response'] = json_decode($record['api_response'], true);
                }
                if (isset($record['metadata']) && is_string($record['metadata'])) {
                    $record['metadata'] = json_decode($record['metadata'], true);
                }
            }
        }
        
        return $data;
    }
}
