<?php

namespace App\Modules\StudentManagement\Models;

use CodeIgniter\Model;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $allowedFields = [
        'name',
        'description',
        'grade_level',
        'capacity',
        'status'
    ];
    
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[classes.name,id,{id}]',
        'description' => 'permit_empty|max_length[500]',
        'grade_level' => 'required|integer|greater_than[0]|less_than_equal_to[12]',
        'capacity' => 'permit_empty|integer|greater_than[0]',
        'status' => 'required|in_list[Active,Inactive]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Class name is required',
            'max_length' => 'Class name cannot exceed 100 characters',
            'is_unique' => 'Class name already exists'
        ],
        'grade_level' => [
            'required' => 'Grade level is required',
            'integer' => 'Grade level must be a number',
            'greater_than' => 'Grade level must be greater than 0',
            'less_than_equal_to' => 'Grade level cannot exceed 12'
        ],
        'capacity' => [
            'integer' => 'Capacity must be a number',
            'greater_than' => 'Capacity must be greater than 0'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be Active or Inactive'
        ]
    ];
    
    /**
     * Get all active classes
     */
    public function getActiveClasses()
    {
        return $this->where('status', 'Active')
                   ->orderBy('grade_level', 'ASC')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get classes with student count
     */
    public function getClassesWithStudentCount()
    {
        return $this->select('classes.*, COUNT(student_sessions.student_id) as student_count')
                   ->join('class_sections', 'classes.id = class_sections.class_id', 'left')
                   ->join('student_sessions', 'class_sections.id = student_sessions.class_section_id', 'left')
                   ->groupBy('classes.id')
                   ->orderBy('classes.grade_level', 'ASC')
                   ->orderBy('classes.name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get class with sections
     */
    public function getClassWithSections($classId)
    {
        $class = $this->find($classId);
        if (!$class) {
            return null;
        }
        
        $sectionModel = new SectionModel();
        $class['sections'] = $sectionModel->getSectionsByClass($classId);
        
        return $class;
    }
    
    /**
     * Get classes by grade level
     */
    public function getClassesByGradeLevel($gradeLevel)
    {
        return $this->where('grade_level', $gradeLevel)
                   ->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get class statistics
     */
    public function getClassStatistics()
    {
        $stats = [];
        
        // Total classes
        $stats['total_classes'] = $this->countAllResults();
        
        // Active classes
        $stats['active_classes'] = $this->where('status', 'Active')->countAllResults();
        
        // Inactive classes
        $stats['inactive_classes'] = $this->where('status', 'Inactive')->countAllResults();
        
        // Classes by grade level
        $gradeStats = $this->select('grade_level, COUNT(*) as count')
                          ->groupBy('grade_level')
                          ->orderBy('grade_level', 'ASC')
                          ->findAll();
        
        $stats['by_grade_level'] = [];
        foreach ($gradeStats as $grade) {
            $stats['by_grade_level'][$grade['grade_level']] = $grade['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get classes for dropdown
     */
    public function getClassesForDropdown()
    {
        $classes = $this->getActiveClasses();
        $dropdown = [];
        
        foreach ($classes as $class) {
            $dropdown[$class['id']] = $class['name'] . ' (Grade ' . $class['grade_level'] . ')';
        }
        
        return $dropdown;
    }
    
    /**
     * Check if class has students
     */
    public function hasStudents($classId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('student_sessions ss')
                     ->join('class_sections cs', 'ss.class_section_id = cs.id')
                     ->where('cs.class_id', $classId);
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Get available capacity for a class
     */
    public function getAvailableCapacity($classId)
    {
        $class = $this->find($classId);
        if (!$class || !$class['capacity']) {
            return null;
        }
        
        $db = \Config\Database::connect();
        $currentStudents = $db->table('student_sessions ss')
                             ->join('class_sections cs', 'ss.class_section_id = cs.id')
                             ->where('cs.class_id', $classId)
                             ->countAllResults();
        
        return max(0, $class['capacity'] - $currentStudents);
    }
    
    /**
     * Validate grade level range
     */
    public function validateGradeLevel($gradeLevel)
    {
        return is_numeric($gradeLevel) && $gradeLevel >= 1 && $gradeLevel <= 12;
    }
    
    /**
     * Get next grade level classes
     */
    public function getNextGradeLevelClasses($currentGradeLevel)
    {
        $nextGradeLevel = $currentGradeLevel + 1;
        
        if ($nextGradeLevel > 12) {
            return [];
        }
        
        return $this->getClassesByGradeLevel($nextGradeLevel);
    }
    
    /**
     * Search classes
     */
    public function searchClasses($searchTerm, $filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('name', $searchTerm)
                   ->orLike('description', $searchTerm)
                   ->groupEnd();
        }
        
        if (!empty($filters['grade_level'])) {
            $builder->where('grade_level', $filters['grade_level']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        return $builder->orderBy('grade_level', 'ASC')
                      ->orderBy('name', 'ASC')
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Get class capacity utilization
     */
    public function getCapacityUtilization($classId)
    {
        $class = $this->find($classId);
        if (!$class || !$class['capacity']) {
            return null;
        }
        
        $db = \Config\Database::connect();
        $currentStudents = $db->table('student_sessions ss')
                             ->join('class_sections cs', 'ss.class_section_id = cs.id')
                             ->where('cs.class_id', $classId)
                             ->countAllResults();
        
        return [
            'capacity' => $class['capacity'],
            'current_students' => $currentStudents,
            'available_spots' => max(0, $class['capacity'] - $currentStudents),
            'utilization_percentage' => round(($currentStudents / $class['capacity']) * 100, 2)
        ];
    }
}