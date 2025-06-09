<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasAutoReplyModel extends Model
{
    protected $table = 'wablas_auto_replies';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'autoreply_id',
        'device_id',
        'keyword',
        'response_type',
        'response_content',
        'media_url',
        'media_caption',
        'is_exact_match',
        'is_case_sensitive',
        'is_active',
        'priority',
        'usage_count',
        'last_used_at',
        'business_hours_only',
        'business_hours_start',
        'business_hours_end',
        'allowed_days',
        'conditions',
        'variables',
        'created_by',
        'updated_by'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'device_id' => 'required|integer',
        'keyword' => 'required|max_length[255]',
        'response_type' => 'in_list[text,image,document,video,audio,template]',
        'response_content' => 'required',
        'is_exact_match' => 'in_list[0,1]',
        'is_case_sensitive' => 'in_list[0,1]',
        'is_active' => 'in_list[0,1]',
        'priority' => 'integer',
        'business_hours_only' => 'in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'device_id' => [
            'required' => 'Device ID is required'
        ],
        'keyword' => [
            'required' => 'Keyword is required'
        ],
        'response_content' => [
            'required' => 'Response content is required'
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
     * Get active auto-replies by device
     */
    public function getActiveByDevice(int $deviceId): array
    {
        return $this->where('device_id', $deviceId)
                   ->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('priority', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get auto-replies by keyword
     */
    public function getByKeyword(string $keyword, int $deviceId = null): array
    {
        $builder = $this->where('keyword', $keyword)
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('priority', 'ASC')->findAll();
    }
    
    /**
     * Search auto-replies by keyword pattern
     */
    public function searchByKeyword(string $pattern, int $deviceId = null): array
    {
        $builder = $this->like('keyword', $pattern)
                       ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('priority', 'ASC')->findAll();
    }
    
    /**
     * Get auto-reply statistics
     */
    public function getStatistics(int $deviceId = null): array
    {
        $builder = $this->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        $total = $builder->countAllResults(false);
        $active = $builder->where('is_active', 1)->countAllResults(false);
        $used = $builder->where('usage_count >', 0)->countAllResults();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'used' => $used,
            'unused' => $total - $used
        ];
    }
    
    /**
     * Increment usage count
     */
    public function incrementUsage(int $autoReplyId): bool
    {
        $autoReply = $this->find($autoReplyId);
        if (!$autoReply) {
            return false;
        }
        
        return $this->update($autoReplyId, [
            'usage_count' => $autoReply['usage_count'] + 1,
            'last_used_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Check if auto-reply should be active based on business hours
     */
    public function isActiveNow(array $autoReply): bool
    {
        if (!$autoReply['business_hours_only']) {
            return true;
        }
        
        $now = date('H:i:s');
        $start = $autoReply['business_hours_start'] ?? '09:00:00';
        $end = $autoReply['business_hours_end'] ?? '17:00:00';
        
        // Check if current time is within business hours
        if ($now >= $start && $now <= $end) {
            // Check allowed days if specified
            if ($autoReply['allowed_days']) {
                $allowedDays = is_string($autoReply['allowed_days']) 
                    ? json_decode($autoReply['allowed_days'], true) 
                    : $autoReply['allowed_days'];
                
                $currentDay = date('N'); // 1 = Monday, 7 = Sunday
                return in_array($currentDay, $allowedDays);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Find matching auto-reply for message
     */
    public function findMatchingReply(int $deviceId, string $message): ?array
    {
        $autoReplies = $this->getActiveByDevice($deviceId);
        
        foreach ($autoReplies as $autoReply) {
            if (!$this->isActiveNow($autoReply)) {
                continue;
            }
            
            if ($this->matchesKeyword($message, $autoReply)) {
                return $autoReply;
            }
        }
        
        return null;
    }
    
    /**
     * Check if message matches auto-reply keyword
     */
    protected function matchesKeyword(string $message, array $autoReply): bool
    {
        $keyword = $autoReply['keyword'];
        $isExactMatch = $autoReply['is_exact_match'];
        $isCaseSensitive = $autoReply['is_case_sensitive'];
        
        if (!$isCaseSensitive) {
            $message = strtolower($message);
            $keyword = strtolower($keyword);
        }
        
        if ($isExactMatch) {
            return trim($message) === $keyword;
        } else {
            return strpos($message, $keyword) !== false;
        }
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['allowed_days']) && is_array($data['data']['allowed_days'])) {
            $data['data']['allowed_days'] = json_encode($data['data']['allowed_days']);
        }
        
        if (isset($data['data']['conditions']) && is_array($data['data']['conditions'])) {
            $data['data']['conditions'] = json_encode($data['data']['conditions']);
        }
        
        if (isset($data['data']['variables']) && is_array($data['data']['variables'])) {
            $data['data']['variables'] = json_encode($data['data']['variables']);
        }
        
        // Set default values
        if (!isset($data['data']['response_type'])) {
            $data['data']['response_type'] = 'text';
        }
        
        if (!isset($data['data']['is_active'])) {
            $data['data']['is_active'] = 1;
        }
        
        if (!isset($data['data']['priority'])) {
            $data['data']['priority'] = 100;
        }
        
        if (!isset($data['data']['usage_count'])) {
            $data['data']['usage_count'] = 0;
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data): array
    {
        if (isset($data['data']['allowed_days']) && is_array($data['data']['allowed_days'])) {
            $data['data']['allowed_days'] = json_encode($data['data']['allowed_days']);
        }
        
        if (isset($data['data']['conditions']) && is_array($data['data']['conditions'])) {
            $data['data']['conditions'] = json_encode($data['data']['conditions']);
        }
        
        if (isset($data['data']['variables']) && is_array($data['data']['variables'])) {
            $data['data']['variables'] = json_encode($data['data']['variables']);
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
            $this->decodeJsonFields($data['data']);
        } else {
            // Multiple records
            foreach ($data as &$record) {
                $this->decodeJsonFields($record);
            }
        }
        
        return $data;
    }
    
    /**
     * Decode JSON fields
     */
    protected function decodeJsonFields(array &$record): void
    {
        $jsonFields = ['allowed_days', 'conditions', 'variables'];
        
        foreach ($jsonFields as $field) {
            if (isset($record[$field]) && is_string($record[$field])) {
                $record[$field] = json_decode($record[$field], true) ?? [];
            }
        }
    }
}
