<?php

namespace App\Modules\WhatsAppIntegration\Models;

use CodeIgniter\Model;

class WaContactModel extends Model
{
    protected $table = 'wa_contacts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'student_id',
        'contact_name',
        'contact_number',
        'contact_type'
    ];
    
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    
    protected $validationRules = [
        'contact_name' => 'required|min_length[2]|max_length[100]',
        'contact_number' => 'required|min_length[10]|is_unique[wa_contacts.contact_number,id,{id}]',
        'contact_type' => 'required|in_list[Parent,Student,Guardian,Teacher,Staff]'
    ];

    /**
     * Get all contacts with student information
     */
    public function getAllContacts()
    {
        return $this->select('wa_contacts.*, students.firstname, students.lastname')
                   ->join('students', 'students.student_id = wa_contacts.student_id', 'left')
                   ->orderBy('wa_contacts.contact_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get contacts by type
     */
    public function getContactsByType($type)
    {
        return $this->where('contact_type', $type)
                   ->orderBy('contact_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get contacts for a student
     */
    public function getStudentContacts($studentId)
    {
        return $this->where('student_id', $studentId)
                   ->orderBy('contact_type', 'ASC')
                   ->findAll();
    }

    /**
     * Get contact groups for bulk messaging
     */
    public function getContactGroups()
    {
        return $this->select('contact_type, COUNT(*) as contact_count')
                   ->groupBy('contact_type')
                   ->orderBy('contact_type', 'ASC')
                   ->findAll();
    }

    /**
     * Search contacts
     */
    public function searchContacts($query)
    {
        return $this->groupStart()
                   ->like('contact_name', $query)
                   ->orLike('contact_number', $query)
                   ->groupEnd()
                   ->orderBy('contact_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get parent contacts for attendance notifications
     */
    public function getParentContacts()
    {
        return $this->select('wa_contacts.*, students.firstname, students.lastname, students.student_id as student_code')
                   ->join('students', 'students.student_id = wa_contacts.student_id', 'inner')
                   ->where('wa_contacts.contact_type', 'Parent')
                   ->where('students.status', 1)
                   ->orderBy('wa_contacts.contact_name', 'ASC')
                   ->findAll();
    }

    /**
     * Sync contacts from students table
     */
    public function syncFromStudents()
    {
        $db = \Config\Database::connect();
        
        // Get students with phone numbers that don't have contacts yet
        $students = $db->table('students s')
                      ->select('s.student_id, s.firstname, s.lastname, s.mobileno, s.father_phone')
                      ->where('s.status', 1)
                      ->where('(s.mobileno IS NOT NULL OR s.father_phone IS NOT NULL)')
                      ->where('s.mobileno !=', '')
                      ->where('s.father_phone !=', '')
                      ->get()
                      ->getResultArray();

        $syncedCount = 0;

        foreach ($students as $student) {
            // Add student contact if mobile number exists
            if (!empty($student['mobileno'])) {
                $existingStudent = $this->where('student_id', $student['student_id'])
                                      ->where('contact_type', 'Student')
                                      ->first();

                if (!$existingStudent) {
                    $this->insert([
                        'student_id' => $student['student_id'],
                        'contact_name' => trim($student['firstname'] . ' ' . $student['lastname']),
                        'contact_number' => $this->cleanPhoneNumber($student['mobileno']),
                        'contact_type' => 'Student'
                    ]);
                    $syncedCount++;
                }
            }

            // Add parent contact if father phone exists
            if (!empty($student['father_phone'])) {
                $existingParent = $this->where('student_id', $student['student_id'])
                                     ->where('contact_type', 'Parent')
                                     ->first();

                if (!$existingParent) {
                    $this->insert([
                        'student_id' => $student['student_id'],
                        'contact_name' => 'Parent of ' . trim($student['firstname'] . ' ' . $student['lastname']),
                        'contact_number' => $this->cleanPhoneNumber($student['father_phone']),
                        'contact_type' => 'Parent'
                    ]);
                    $syncedCount++;
                }
            }
        }

        return $syncedCount;
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present
        if (substr($cleaned, 0, 2) !== '62') {
            if (substr($cleaned, 0, 1) === '0') {
                $cleaned = '62' . substr($cleaned, 1);
            } else {
                $cleaned = '62' . $cleaned;
            }
        }
        
        return $cleaned;
    }

    /**
     * Import contacts from CSV
     */
    public function importFromCSV($csvData)
    {
        $importedCount = 0;
        $errors = [];

        foreach ($csvData as $row) {
            try {
                $contactData = [
                    'student_id' => $row['student_id'] ?? null,
                    'contact_name' => $row['contact_name'],
                    'contact_number' => $this->cleanPhoneNumber($row['contact_number']),
                    'contact_type' => $row['contact_type']
                ];

                if ($this->insert($contactData)) {
                    $importedCount++;
                } else {
                    $errors[] = 'Failed to import: ' . $row['contact_name'];
                }
            } catch (\Exception $e) {
                $errors[] = 'Error importing ' . $row['contact_name'] . ': ' . $e->getMessage();
            }
        }

        return [
            'imported_count' => $importedCount,
            'errors' => $errors
        ];
    }

    /**
     * Get contacts for bulk messaging by criteria
     */
    public function getContactsForBulk($criteria)
    {
        $builder = $this->builder();

        if (isset($criteria['contact_type']) && !empty($criteria['contact_type'])) {
            $builder->where('contact_type', $criteria['contact_type']);
        }

        if (isset($criteria['student_ids']) && !empty($criteria['student_ids'])) {
            $builder->whereIn('student_id', $criteria['student_ids']);
        }

        if (isset($criteria['class_id']) && !empty($criteria['class_id'])) {
            $builder->join('students', 'students.student_id = wa_contacts.student_id')
                   ->join('student_sessions', 'student_sessions.student_id = students.id')
                   ->join('class_sections', 'class_sections.id = student_sessions.class_section_id')
                   ->where('class_sections.class_id', $criteria['class_id']);
        }

        return $builder->select('wa_contacts.*')
                      ->distinct()
                      ->orderBy('contact_name', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Update contact information
     */
    public function updateContact($id, $data)
    {
        if (isset($data['contact_number'])) {
            $data['contact_number'] = $this->cleanPhoneNumber($data['contact_number']);
        }

        return $this->update($id, $data);
    }

    /**
     * Check if contact number exists
     */
    public function contactNumberExists($contactNumber, $excludeId = null)
    {
        $cleanedNumber = $this->cleanPhoneNumber($contactNumber);
        $builder = $this->where('contact_number', $cleanedNumber);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Get contact statistics
     */
    public function getContactStats()
    {
        $total = $this->countAllResults();
        
        $byType = $this->select('contact_type, COUNT(*) as count')
                      ->groupBy('contact_type')
                      ->findAll();

        $withStudents = $this->where('student_id IS NOT NULL')->countAllResults();
        $withoutStudents = $total - $withStudents;

        return [
            'total' => $total,
            'by_type' => $byType,
            'with_students' => $withStudents,
            'without_students' => $withoutStudents
        ];
    }
}
