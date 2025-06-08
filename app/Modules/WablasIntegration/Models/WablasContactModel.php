<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasContactModel extends Model
{
    protected $table = 'wablas_contacts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'phone_number',
        'name',
        'nickname',
        'email',
        'address',
        'birthday',
        'gender',
        'profile_image',
        'status',
        'is_whatsapp_active',
        'last_seen',
        'last_message_at',
        'message_count',
        'group_id',
        'tags',
        'custom_fields',
        'notes',
        'source',
        'created_by',
        'updated_by'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'phone_number' => 'required|max_length[20]|is_unique[wablas_contacts.phone_number,id,{id}]',
        'name' => 'required|max_length[100]',
        'nickname' => 'max_length[50]',
        'email' => 'valid_email|max_length[100]',
        'gender' => 'in_list[male,female,other]',
        'status' => 'in_list[active,inactive,blocked]',
        'is_whatsapp_active' => 'in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'phone_number' => [
            'required' => 'Phone number is required',
            'is_unique' => 'Phone number already exists'
        ],
        'name' => [
            'required' => 'Name is required'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address'
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
     * Get contact by phone number
     */
    public function getByPhoneNumber(string $phoneNumber): ?array
    {
        return $this->where('phone_number', $phoneNumber)
                   ->where('deleted_at', null)
                   ->first();
    }
    
    /**
     * Get active contacts
     */
    public function getActiveContacts(): array
    {
        return $this->where('status', 'active')
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get contacts by group
     */
    public function getByGroup(int $groupId): array
    {
        return $this->where('group_id', $groupId)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Search contacts
     */
    public function searchContacts(string $query, int $limit = 50): array
    {
        return $this->groupStart()
                   ->like('name', $query)
                   ->orLike('nickname', $query)
                   ->orLike('phone_number', $query)
                   ->orLike('email', $query)
                   ->groupEnd()
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get contacts with WhatsApp active
     */
    public function getWhatsAppActiveContacts(): array
    {
        return $this->where('is_whatsapp_active', 1)
                   ->where('status', 'active')
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get contacts by tags
     */
    public function getByTags(array $tags): array
    {
        $builder = $this->where('deleted_at', null);
        
        foreach ($tags as $tag) {
            $builder->like('tags', '"' . $tag . '"');
        }
        
        return $builder->orderBy('name', 'ASC')->findAll();
    }
    
    /**
     * Get birthday contacts for today
     */
    public function getTodayBirthdays(): array
    {
        return $this->where('DAY(birthday)', date('d'))
                   ->where('MONTH(birthday)', date('m'))
                   ->where('status', 'active')
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get upcoming birthdays
     */
    public function getUpcomingBirthdays(int $days = 7): array
    {
        $startDate = date('m-d');
        $endDate = date('m-d', strtotime("+{$days} days"));
        
        return $this->where("DATE_FORMAT(birthday, '%m-%d') BETWEEN '{$startDate}' AND '{$endDate}'")
                   ->where('status', 'active')
                   ->where('deleted_at', null)
                   ->orderBy('birthday', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get contact statistics
     */
    public function getStatistics(): array
    {
        $total = $this->where('deleted_at', null)->countAllResults();
        $active = $this->where('status', 'active')->where('deleted_at', null)->countAllResults();
        $whatsappActive = $this->where('is_whatsapp_active', 1)->where('deleted_at', null)->countAllResults();
        $withGroups = $this->where('group_id IS NOT NULL')->where('deleted_at', null)->countAllResults();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'whatsapp_active' => $whatsappActive,
            'with_groups' => $withGroups,
            'without_groups' => $total - $withGroups
        ];
    }
    
    /**
     * Import contacts from array
     */
    public function importContacts(array $contacts): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'updated' => 0,
            'errors' => []
        ];
        
        foreach ($contacts as $contactData) {
            try {
                // Check if contact exists
                $existing = $this->getByPhoneNumber($contactData['phone_number']);
                
                if ($existing) {
                    // Update existing contact
                    if ($this->update($existing['id'], $contactData)) {
                        $results['updated']++;
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to update contact: {$contactData['phone_number']}";
                    }
                } else {
                    // Create new contact
                    if ($this->insert($contactData)) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to create contact: {$contactData['phone_number']}";
                    }
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error processing {$contactData['phone_number']}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Export contacts to array
     */
    public function exportContacts(array $filters = []): array
    {
        $builder = $this->select('phone_number, name, nickname, email, address, birthday, gender, status')
                       ->where('deleted_at', null);
        
        // Apply filters
        if (isset($filters['group_id'])) {
            $builder->where('group_id', $filters['group_id']);
        }
        
        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (isset($filters['is_whatsapp_active'])) {
            $builder->where('is_whatsapp_active', $filters['is_whatsapp_active']);
        }
        
        return $builder->orderBy('name', 'ASC')->findAll();
    }
    
    /**
     * Add tag to contact
     */
    public function addTag(int $contactId, string $tag): bool
    {
        $contact = $this->find($contactId);
        if (!$contact) {
            return false;
        }
        
        $tags = $contact['tags'] ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            return $this->update($contactId, ['tags' => $tags]);
        }
        
        return true;
    }
    
    /**
     * Remove tag from contact
     */
    public function removeTag(int $contactId, string $tag): bool
    {
        $contact = $this->find($contactId);
        if (!$contact) {
            return false;
        }
        
        $tags = $contact['tags'] ?? [];
        $tags = array_filter($tags, function($t) use ($tag) {
            return $t !== $tag;
        });
        
        return $this->update($contactId, ['tags' => array_values($tags)]);
    }
    
    /**
     * Update last seen
     */
    public function updateLastSeen(string $phoneNumber): bool
    {
        return $this->where('phone_number', $phoneNumber)
                   ->set('last_seen', date('Y-m-d H:i:s'))
                   ->update();
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['tags']) && is_array($data['data']['tags'])) {
            $data['data']['tags'] = json_encode($data['data']['tags']);
        }
        
        if (isset($data['data']['custom_fields']) && is_array($data['data']['custom_fields'])) {
            $data['data']['custom_fields'] = json_encode($data['data']['custom_fields']);
        }
        
        // Set default values
        if (!isset($data['data']['status'])) {
            $data['data']['status'] = 'active';
        }
        
        if (!isset($data['data']['is_whatsapp_active'])) {
            $data['data']['is_whatsapp_active'] = 1;
        }
        
        if (!isset($data['data']['message_count'])) {
            $data['data']['message_count'] = 0;
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data): array
    {
        if (isset($data['data']['tags']) && is_array($data['data']['tags'])) {
            $data['data']['tags'] = json_encode($data['data']['tags']);
        }
        
        if (isset($data['data']['custom_fields']) && is_array($data['data']['custom_fields'])) {
            $data['data']['custom_fields'] = json_encode($data['data']['custom_fields']);
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
            if (isset($data['data']['tags']) && is_string($data['data']['tags'])) {
                $data['data']['tags'] = json_decode($data['data']['tags'], true) ?? [];
            }
            if (isset($data['data']['custom_fields']) && is_string($data['data']['custom_fields'])) {
                $data['data']['custom_fields'] = json_decode($data['data']['custom_fields'], true) ?? [];
            }
        } else {
            // Multiple records
            foreach ($data as &$record) {
                if (isset($record['tags']) && is_string($record['tags'])) {
                    $record['tags'] = json_decode($record['tags'], true) ?? [];
                }
                if (isset($record['custom_fields']) && is_string($record['custom_fields'])) {
                    $record['custom_fields'] = json_decode($record['custom_fields'], true) ?? [];
                }
            }
        }
        
        return $data;
    }
}
