<?php

namespace App\Modules\StudentManagement\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table = 'sections';
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
        'capacity',
        'status'
    ];
    
    protected $validationRules = [
        'name' => 'required|max_length[50]|is_unique[sections.name,id,{id}]',
        'description' => 'permit_empty|max_length[500]',
        'capacity' => 'permit_empty|integer|greater_than[0]',
        'status' => 'required|in_list[Active,Inactive]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Section name is required',
            'max_length' => 'Section name cannot exceed 50 characters',
            'is_unique' => 'Section name already exists'
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
     * Get all active sections
     */
    public function getActiveSections()
    {
        return $this->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get sections by class
     */
    public function getSectionsByClass($classId)
    {
        return $this->select('sections.*')
                   ->join('class_sections', 'sections.id = class_sections.section_id')
                   ->where('class_sections.class_id', $classId)
                   ->where('sections.status', 'Active')
                   ->orderBy('sections.name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get sections with student count
     */
    public function getSectionsWithStudentCount()
    {
        return $this->select('sections.*, COUNT(student_sessions.student_id) as student_count')
                   ->join('class_sections', 'sections.id = class_sections.section_id', 'left')
                   ->join('student_sessions', 'class_sections.id = student_sessions.class_section_id', 'left')
                   ->groupBy('sections.id')
                   ->orderBy('sections.name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get section with classes
     */
    public function getSectionWithClasses($sectionId)
    {
        $section = $this->find($sectionId);
        if (!$section) {
            return null;
        }
        
        $classModel = new ClassModel();
        $section['classes'] = $classModel->select('classes.*')
                                        ->join('class_sections', 'classes.id = class_sections.class_id')
                                        ->where('class_sections.section_id', $sectionId)
                                        ->findAll();
        
        return $section;
    }
    
    /**
     * Get section statistics
     */
    public function getSectionStatistics()
    {
        $stats = [];
        
        // Total sections
        $stats['total_sections'] = $this->countAllResults();
        
        // Active sections
        $stats['active_sections'] = $this->where('status', 'Active')->countAllResults();
        
        // Inactive sections
        $stats['inactive_sections'] = $this->where('status', 'Inactive')->countAllResults();
        
        // Sections with students
        $db = \Config\Database::connect();
        $sectionsWithStudents = $db->table('sections s')
                                  ->join('class_sections cs', 's.id = cs.section_id')
                                  ->join('student_sessions ss', 'cs.id = ss.class_section_id')
                                  ->distinct()
                                  ->select('s.id')
                                  ->get()
                                  ->getNumRows();
        
        $stats['sections_with_students'] = $sectionsWithStudents;
        $stats['empty_sections'] = $stats['total_sections'] - $sectionsWithStudents;
        
        return $stats;
    }
    
    /**
     * Get sections for dropdown
     */
    public function getSectionsForDropdown($classId = null)
    {
        $builder = $this->builder();
        
        if ($classId) {
            $builder->select('sections.*')
                   ->join('class_sections', 'sections.id = class_sections.section_id')
                   ->where('class_sections.class_id', $classId);
        }
        
        $sections = $builder->where('sections.status', 'Active')
                          ->orderBy('sections.name', 'ASC')
                          ->get()
                          ->getResultArray();
        
        $dropdown = [];
        foreach ($sections as $section) {
            $dropdown[$section['id']] = $section['name'];
        }
        
        return $dropdown;
    }
    
    /**
     * Check if section has students
     */
    public function hasStudents($sectionId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('student_sessions ss')
                     ->join('class_sections cs', 'ss.class_section_id = cs.id')
                     ->where('cs.section_id', $sectionId);
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Get available capacity for a section in a specific class
     */
    public function getAvailableCapacity($sectionId, $classId = null)
    {
        $section = $this->find($sectionId);
        if (!$section || !$section['capacity']) {
            return null;
        }
        
        $db = \Config\Database::connect();
        $builder = $db->table('student_sessions ss')
                     ->join('class_sections cs', 'ss.class_section_id = cs.id')
                     ->where('cs.section_id', $sectionId);
        
        if ($classId) {
            $builder->where('cs.class_id', $classId);
        }
        
        $currentStudents = $builder->countAllResults();
        
        return max(0, $section['capacity'] - $currentStudents);
    }
    
    /**
     * Search sections
     */
    public function searchSections($searchTerm, $filters = [])
    {
        $builder = $this->builder();
        
        if (!empty($searchTerm)) {
            $builder->groupStart()
                   ->like('name', $searchTerm)
                   ->orLike('description', $searchTerm)
                   ->groupEnd();
        }
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (!empty($filters['class_id'])) {
            $builder->join('class_sections', 'sections.id = class_sections.section_id')
                   ->where('class_sections.class_id', $filters['class_id']);
        }
        
        return $builder->orderBy('name', 'ASC')
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Get section capacity utilization
     */
    public function getCapacityUtilization($sectionId, $classId = null)
    {
        $section = $this->find($sectionId);
        if (!$section || !$section['capacity']) {
            return null;
        }
        
        $db = \Config\Database::connect();
        $builder = $db->table('student_sessions ss')
                     ->join('class_sections cs', 'ss.class_section_id = cs.id')
                     ->where('cs.section_id', $sectionId);
        
        if ($classId) {
            $builder->where('cs.class_id', $classId);
        }
        
        $currentStudents = $builder->countAllResults();
        
        return [
            'capacity' => $section['capacity'],
            'current_students' => $currentStudents,
            'available_spots' => max(0, $section['capacity'] - $currentStudents),
            'utilization_percentage' => round(($currentStudents / $section['capacity']) * 100, 2)
        ];
    }
    
    /**
     * Get sections by multiple classes
     */
    public function getSectionsByClasses($classIds)
    {
        if (empty($classIds)) {
            return [];
        }
        
        return $this->select('sections.*, class_sections.class_id')
                   ->join('class_sections', 'sections.id = class_sections.section_id')
                   ->whereIn('class_sections.class_id', $classIds)
                   ->where('sections.status', 'Active')
                   ->orderBy('sections.name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Check if section is assigned to any class
     */
    public function isAssignedToClass($sectionId)
    {
        $db = \Config\Database::connect();
        return $db->table('class_sections')
                 ->where('section_id', $sectionId)
                 ->countAllResults() > 0;
    }
    
    /**
     * Get unassigned sections
     */
    public function getUnassignedSections()
    {
        return $this->select('sections.*')
                   ->join('class_sections', 'sections.id = class_sections.section_id', 'left')
                   ->where('class_sections.section_id IS NULL')
                   ->where('sections.status', 'Active')
                   ->orderBy('sections.name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get most populated sections
     */
    public function getMostPopulatedSections($limit = 10)
    {
        return $this->select('sections.*, COUNT(student_sessions.student_id) as student_count')
                   ->join('class_sections', 'sections.id = class_sections.section_id', 'left')
                   ->join('student_sessions', 'class_sections.id = student_sessions.class_section_id', 'left')
                   ->groupBy('sections.id')
                   ->orderBy('student_count', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get sections with low utilization
     */
    public function getLowUtilizationSections($threshold = 50)
    {
        $sections = $this->getSectionsWithStudentCount();
        $lowUtilization = [];
        
        foreach ($sections as $section) {
            if ($section['capacity'] > 0) {
                $utilization = ($section['student_count'] / $section['capacity']) * 100;
                if ($utilization < $threshold) {
                    $section['utilization_percentage'] = round($utilization, 2);
                    $lowUtilization[] = $section;
                }
            }
        }
        
        return $lowUtilization;
    }
}