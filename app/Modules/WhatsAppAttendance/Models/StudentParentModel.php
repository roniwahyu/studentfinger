<?php

namespace App\Modules\WhatsAppAttendance\Models;

use CodeIgniter\Model;

/**
 * Student Parent Model
 * 
 * Handles custom student-parent phone number mapping
 */
class StudentParentModel extends Model
{
    protected $table = 'whatsapp_student_parents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    
    protected $allowedFields = [
        'student_id',
        'parent_name',
        'parent_type',
        'phone_number',
        'relationship',
        'is_primary',
        'status',
        'notes'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'student_id' => 'required|integer',
        'parent_name' => 'required|max_length[100]',
        'parent_type' => 'required|in_list[father,mother,guardian,emergency]',
        'phone_number' => 'required|max_length[20]',
        'relationship' => 'permit_empty|max_length[50]',
        'is_primary' => 'permit_empty|in_list[0,1]',
        'status' => 'required|in_list[active,inactive]'
    ];
    
    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required',
            'integer' => 'Student ID must be a valid integer'
        ],
        'parent_name' => [
            'required' => 'Parent name is required',
            'max_length' => 'Parent name cannot exceed 100 characters'
        ],
        'parent_type' => [
            'required' => 'Parent type is required',
            'in_list' => 'Parent type must be one of: father, mother, guardian, emergency'
        ],
        'phone_number' => [
            'required' => 'Phone number is required',
            'max_length' => 'Phone number cannot exceed 20 characters'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be either active or inactive'
        ]
    ];
    
    /**
     * Get parents by student ID
     */
    public function getByStudent($studentId, $activeOnly = true)
    {
        $builder = $this->where('student_id', $studentId);
        
        if ($activeOnly) {
            $builder->where('status', 'active');
        }
        
        return $builder->orderBy('is_primary', 'DESC')
            ->orderBy('parent_type', 'ASC')
            ->findAll();
    }
    
    /**
     * Get phone numbers by student ID
     */
    public function getPhonesByStudent($studentId, $activeOnly = true)
    {
        $builder = $this->select('phone_number')
            ->where('student_id', $studentId);
        
        if ($activeOnly) {
            $builder->where('status', 'active');
        }
        
        return $builder->orderBy('is_primary', 'DESC')
            ->findColumn('phone_number');
    }
    
    /**
     * Get primary parent for student
     */
    public function getPrimaryParent($studentId)
    {
        return $this->where('student_id', $studentId)
            ->where('is_primary', 1)
            ->where('status', 'active')
            ->first();
    }
    
    /**
     * Get parents by phone number
     */
    public function getByPhone($phoneNumber)
    {
        return $this->where('phone_number', $phoneNumber)
            ->where('status', 'active')
            ->findAll();
    }
    
    /**
     * Get all students for a parent phone
     */
    public function getStudentsByPhone($phoneNumber)
    {
        return $this->select('whatsapp_student_parents.*, students.firstname, students.lastname, students.class_id, students.section_id')
            ->join('students', 'students.student_id = whatsapp_student_parents.student_id', 'left')
            ->where('whatsapp_student_parents.phone_number', $phoneNumber)
            ->where('whatsapp_student_parents.status', 'active')
            ->where('students.status', 'Active')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Add or update parent mapping
     */
    public function addParentMapping($studentId, $parentData)
    {
        // Check if mapping already exists
        $existing = $this->where('student_id', $studentId)
            ->where('phone_number', $parentData['phone_number'])
            ->where('parent_type', $parentData['parent_type'])
            ->first();
        
        if ($existing) {
            // Update existing mapping
            return $this->update($existing['id'], $parentData);
        } else {
            // Create new mapping
            $parentData['student_id'] = $studentId;
            return $this->insert($parentData);
        }
    }
    
    /**
     * Set primary parent
     */
    public function setPrimaryParent($studentId, $parentId)
    {
        // Remove primary flag from all parents of this student
        $this->where('student_id', $studentId)
            ->set('is_primary', 0)
            ->update();
        
        // Set the specified parent as primary
        return $this->update($parentId, ['is_primary' => 1]);
    }
    
    /**
     * Deactivate parent mapping
     */
    public function deactivateParent($id)
    {
        return $this->update($id, ['status' => 'inactive']);
    }
    
    /**
     * Activate parent mapping
     */
    public function activateParent($id)
    {
        return $this->update($id, ['status' => 'active']);
    }
    
    /**
     * Get parent statistics
     */
    public function getStatistics()
    {
        // Total mappings
        $totalMappings = $this->countAllResults();
        
        // Active mappings
        $activeMappings = $this->where('status', 'active')
            ->countAllResults();
        
        // By parent type
        $byType = $this->select('parent_type, COUNT(*) as count')
            ->where('status', 'active')
            ->groupBy('parent_type')
            ->get()
            ->getResultArray();
        
        // Students with mappings
        $studentsWithMappings = $this->select('DISTINCT student_id')
            ->where('status', 'active')
            ->countAllResults();
        
        // Students with multiple parents
        $studentsWithMultipleParents = $this->select('student_id, COUNT(*) as parent_count')
            ->where('status', 'active')
            ->groupBy('student_id')
            ->having('parent_count > 1')
            ->countAllResults();
        
        return [
            'total_mappings' => $totalMappings,
            'active_mappings' => $activeMappings,
            'inactive_mappings' => $totalMappings - $activeMappings,
            'students_with_mappings' => $studentsWithMappings,
            'students_with_multiple_parents' => $studentsWithMultipleParents,
            'by_parent_type' => $byType
        ];
    }
    
    /**
     * Validate phone number format
     */
    public function validatePhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Check if it's a valid Indonesian phone number
        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
            return false;
        }
        
        // Check if it starts with valid Indonesian prefixes
        $validPrefixes = ['62', '08', '8'];
        $isValid = false;
        
        foreach ($validPrefixes as $prefix) {
            if (substr($cleanPhone, 0, strlen($prefix)) === $prefix) {
                $isValid = true;
                break;
            }
        }
        
        return $isValid;
    }
    
    /**
     * Format phone number
     */
    public function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleanPhone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present
        if (substr($cleanPhone, 0, 2) !== '62') {
            if (substr($cleanPhone, 0, 1) === '0') {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            } else {
                $cleanPhone = '62' . $cleanPhone;
            }
        }
        
        return $cleanPhone;
    }
    
    /**
     * Import parent mappings from students table
     */
    public function importFromStudentsTable()
    {
        $db = \Config\Database::connect();
        
        $students = $db->table('students')
            ->select('student_id, firstname, lastname, father_phone, mother_phone, mobileno')
            ->where('status', 'Active')
            ->get()
            ->getResultArray();
        
        $imported = 0;
        $errors = [];
        
        foreach ($students as $student) {
            try {
                // Import father phone
                if (!empty($student['father_phone'])) {
                    $this->addParentMapping($student['student_id'], [
                        'parent_name' => 'Father of ' . trim($student['firstname'] . ' ' . $student['lastname']),
                        'parent_type' => 'father',
                        'phone_number' => $this->formatPhoneNumber($student['father_phone']),
                        'relationship' => 'Father',
                        'is_primary' => 1,
                        'status' => 'active',
                        'notes' => 'Imported from students table'
                    ]);
                    $imported++;
                }
                
                // Import mother phone
                if (!empty($student['mother_phone'])) {
                    $this->addParentMapping($student['student_id'], [
                        'parent_name' => 'Mother of ' . trim($student['firstname'] . ' ' . $student['lastname']),
                        'parent_type' => 'mother',
                        'phone_number' => $this->formatPhoneNumber($student['mother_phone']),
                        'relationship' => 'Mother',
                        'is_primary' => empty($student['father_phone']) ? 1 : 0,
                        'status' => 'active',
                        'notes' => 'Imported from students table'
                    ]);
                    $imported++;
                }
                
                // Import mobile number as emergency contact
                if (!empty($student['mobileno']) && $student['mobileno'] !== $student['father_phone'] && $student['mobileno'] !== $student['mother_phone']) {
                    $this->addParentMapping($student['student_id'], [
                        'parent_name' => 'Emergency contact for ' . trim($student['firstname'] . ' ' . $student['lastname']),
                        'parent_type' => 'emergency',
                        'phone_number' => $this->formatPhoneNumber($student['mobileno']),
                        'relationship' => 'Emergency Contact',
                        'is_primary' => 0,
                        'status' => 'active',
                        'notes' => 'Imported from students table (mobile)'
                    ]);
                    $imported++;
                }
                
            } catch (\Exception $e) {
                $errors[] = 'Error importing student ' . $student['student_id'] . ': ' . $e->getMessage();
            }
        }
        
        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }
    
    /**
     * Get duplicate phone numbers
     */
    public function getDuplicatePhones()
    {
        return $this->select('phone_number, COUNT(*) as count')
            ->where('status', 'active')
            ->groupBy('phone_number')
            ->having('count > 1')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Clean invalid phone numbers
     */
    public function cleanInvalidPhones()
    {
        $parents = $this->where('status', 'active')
            ->findAll();
        
        $cleaned = 0;
        
        foreach ($parents as $parent) {
            if (!$this->validatePhoneNumber($parent['phone_number'])) {
                $this->update($parent['id'], ['status' => 'inactive', 'notes' => 'Deactivated due to invalid phone number']);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}