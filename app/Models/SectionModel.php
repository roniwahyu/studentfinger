<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'section'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'section' => 'required|min_length[1]|max_length[100]'
    ];
    
    protected $validationMessages = [
        'section' => [
            'required' => 'Section name is required',
            'min_length' => 'Section name must be at least 1 character'
        ]
    ];
    
    /**
     * Get sections with class information
     */
    public function getSectionsWithClass()
    {
        return $this->db->table($this->table)
            ->select('sections.*, classes.name as class_name')
            ->join('classes', 'classes.id = sections.class_id')
            ->where('sections.deleted_at', null)
            ->orderBy('classes.name', 'ASC')
            ->orderBy('sections.name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get sections by class
     */
    public function getSectionsByClass($classId)
    {
        return $this->where('class_id', $classId)
                   ->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get active sections
     */
    public function getActiveSections()
    {
        return $this->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
}