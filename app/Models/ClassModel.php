<?php

namespace App\Models;

use CodeIgniter\Model;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'class'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'class' => 'required|min_length[1]|max_length[100]'
    ];
    
    protected $validationMessages = [
        'class' => [
            'required' => 'Class name is required',
            'min_length' => 'Class name must be at least 1 character'
        ]
    ];
    
    /**
     * Get classes with sections
     */
    public function getClassesWithSections()
    {
        return $this->db->table($this->table)
            ->select('classes.*, COUNT(sections.id) as section_count')
            ->join('sections', 'sections.class_id = classes.id', 'left')
            ->where('classes.deleted_at', null)
            ->groupBy('classes.id')
            ->orderBy('classes.name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get active classes
     */
    public function getActiveClasses()
    {
        return $this->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get class with student count
     */
    public function getClassWithStudentCount($classId)
    {
        return $this->db->table($this->table)
            ->select('classes.*, COUNT(students.id) as student_count')
            ->join('students', 'students.class_id = classes.id AND students.deleted_at IS NULL', 'left')
            ->where('classes.id', $classId)
            ->where('classes.deleted_at', null)
            ->groupBy('classes.id')
            ->get()
            ->getRowArray();
    }
}