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
        'name',
        'description',
        'grade_level',
        'capacity',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]|is_unique[classes.name,id,{id}]',
        'grade_level' => 'permit_empty|integer|greater_than[0]',
        'capacity' => 'permit_empty|integer|greater_than[0]',
        'status' => 'required|in_list[Active,Inactive]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Class name is required',
            'is_unique' => 'Class name already exists'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Please select a valid status'
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