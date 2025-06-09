<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * Parent Contact Model
 * 
 * Manages parent contact information for notifications
 */
class ParentContactModel extends Model
{
    protected $table = 'parent_contacts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'student_id',
        'contact_type',
        'contact_name',
        'phone_number',
        'whatsapp_number',
        'email',
        'relationship',
        'is_primary',
        'is_active',
        'receive_notifications',
        'notification_preferences',
        'notes'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'student_id' => 'required|integer',
        'contact_type' => 'required|in_list[father,mother,guardian,emergency]',
        'contact_name' => 'required|min_length[2]|max_length[100]',
        'phone_number' => 'required|min_length[10]|max_length[20]',
        'whatsapp_number' => 'permit_empty|min_length[10]|max_length[20]'
    ];
    
    // Contact type constants
    const TYPE_FATHER = 'father';
    const TYPE_MOTHER = 'mother';
    const TYPE_GUARDIAN = 'guardian';
    const TYPE_EMERGENCY = 'emergency';
    
    /**
     * Get contacts by student ID
     */
    public function getContactsByStudent(int $studentId): array
    {
        return $this->where('student_id', $studentId)
                    ->where('is_active', 1)
                    ->orderBy('is_primary', 'DESC')
                    ->orderBy('contact_type', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get primary contact for student
     */
    public function getPrimaryContact(int $studentId): ?array
    {
        return $this->where('student_id', $studentId)
                    ->where('is_primary', 1)
                    ->where('is_active', 1)
                    ->first();
    }
    
    /**
     * Get contacts for notification
     */
    public function getNotificationContacts(int $studentId, string $eventType = null): array
    {
        $builder = $this->builder();
        $builder->where('student_id', $studentId)
                ->where('is_active', 1)
                ->where('receive_notifications', 1);
        
        // Filter by notification preferences if event type is specified
        if ($eventType) {
            $builder->where("JSON_EXTRACT(notification_preferences, '$.{$eventType}') IS NULL OR JSON_EXTRACT(notification_preferences, '$.{$eventType}') = true");
        }
        
        return $builder->orderBy('is_primary', 'DESC')
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Get contacts with student information
     */
    public function getContactsWithStudents(int $limit = 50, int $offset = 0, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->select('parent_contacts.*, students.firstname, students.lastname, students.admission_no')
                ->join('students', 'students.student_id = parent_contacts.student_id', 'left')
                ->orderBy('parent_contacts.created_at', 'DESC');
        
        // Apply filters
        if (!empty($filters['contact_type'])) {
            $builder->where('parent_contacts.contact_type', $filters['contact_type']);
        }
        
        if (!empty($filters['is_active'])) {
            $builder->where('parent_contacts.is_active', $filters['is_active']);
        }
        
        if (!empty($filters['student_id'])) {
            $builder->where('parent_contacts.student_id', $filters['student_id']);
        }
        
        if (!empty($filters['search'])) {
            $builder->groupStart()
                    ->like('parent_contacts.contact_name', $filters['search'])
                    ->orLike('parent_contacts.phone_number', $filters['search'])
                    ->orLike('students.firstname', $filters['search'])
                    ->orLike('students.lastname', $filters['search'])
                    ->groupEnd();
        }
        
        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get contacts by class
     */
    public function getContactsByClass(int $classId): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('parent_contacts pc')
                 ->select('pc.*, s.firstname, s.lastname, s.student_number')
                 ->join('students s', 's.student_id = pc.student_id', 'inner')
                 ->where('pc.is_active', 1)
                 ->where('pc.receive_notifications', 1)
                 ->where('s.deleted_at', null)
                 ->orderBy('s.firstname', 'ASC')
                 ->orderBy('pc.is_primary', 'DESC')
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get contacts by phone number
     */
    public function getContactByPhone(string $phoneNumber): ?array
    {
        // Clean phone number
        $cleanPhone = $this->cleanPhoneNumber($phoneNumber);
        
        return $this->where('phone_number', $cleanPhone)
                    ->orWhere('whatsapp_number', $cleanPhone)
                    ->where('is_active', 1)
                    ->first();
    }
    
    /**
     * Set primary contact
     */
    public function setPrimaryContact(int $studentId, int $contactId): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Remove primary status from all contacts for this student
            $this->where('student_id', $studentId)
                 ->set('is_primary', 0)
                 ->update();
            
            // Set new primary contact
            $this->update($contactId, ['is_primary' => 1]);
            
            $db->transComplete();
            return $db->transStatus();
            
        } catch (\Exception $e) {
            $db->transRollback();
            return false;
        }
    }
    
    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(int $contactId, array $preferences): bool
    {
        return $this->update($contactId, [
            'notification_preferences' => json_encode($preferences)
        ]);
    }
    
    /**
     * Clean phone number to Indonesian format
     */
    public function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert to international format
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Validate phone number
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);
        
        // Indonesian phone number should be 10-15 digits starting with 62
        return preg_match('/^62[0-9]{8,13}$/', $cleanPhone);
    }
    
    /**
     * Get contact statistics
     */
    public function getContactStats(): array
    {
        $db = \Config\Database::connect();
        
        $stats = [
            'total_contacts' => $this->countAllResults(),
            'active_contacts' => $this->where('is_active', 1)->countAllResults(),
            'primary_contacts' => $this->where('is_primary', 1)->countAllResults(),
            'notification_enabled' => $this->where('receive_notifications', 1)->countAllResults()
        ];
        
        // Contact type breakdown
        $typeQuery = $db->query("
            SELECT contact_type, COUNT(*) as count 
            FROM parent_contacts 
            WHERE deleted_at IS NULL AND is_active = 1
            GROUP BY contact_type
        ");
        
        $typeBreakdown = [];
        foreach ($typeQuery->getResultArray() as $row) {
            $typeBreakdown[$row['contact_type']] = $row['count'];
        }
        
        $stats['type_breakdown'] = $typeBreakdown;
        
        // Reset builder for next queries
        $this->builder()->resetQuery();
        
        return $stats;
    }
    
    /**
     * Search contacts
     */
    public function searchContacts(string $query, int $limit = 20): array
    {
        return $this->select('parent_contacts.*, students.firstname, students.lastname')
                    ->join('students', 'students.student_id = parent_contacts.student_id', 'left')
                    ->groupStart()
                        ->like('parent_contacts.contact_name', $query)
                        ->orLike('parent_contacts.phone_number', $query)
                        ->orLike('parent_contacts.whatsapp_number', $query)
                        ->orLike('students.firstname', $query)
                        ->orLike('students.lastname', $query)
                    ->groupEnd()
                    ->where('parent_contacts.is_active', 1)
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Bulk update notification preferences
     */
    public function bulkUpdateNotificationPreferences(array $contactIds, array $preferences): bool
    {
        if (empty($contactIds)) {
            return false;
        }
        
        return $this->whereIn('id', $contactIds)
                    ->set('notification_preferences', json_encode($preferences))
                    ->update();
    }
    
    /**
     * Get contacts without WhatsApp numbers
     */
    public function getContactsWithoutWhatsApp(): array
    {
        return $this->select('parent_contacts.*, students.firstname, students.lastname')
                    ->join('students', 'students.student_id = parent_contacts.student_id', 'left')
                    ->where('parent_contacts.is_active', 1)
                    ->groupStart()
                        ->where('parent_contacts.whatsapp_number', null)
                        ->orWhere('parent_contacts.whatsapp_number', '')
                    ->groupEnd()
                    ->findAll();
    }
}
