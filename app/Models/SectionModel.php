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
        'name',
        'class_id',
        'capacity',
        'description',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[1]|max_length[50]',
        'class_id' => 'required|integer|is_not_unique[classes.id]',
        'capacity' => 'permit_empty|integer|greater_than[0]',
        'status' => 'required|in_list[Active,Inactive]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Section name is required'
        ],
        'class_id' => [
            'required' => 'Class is required',
            'is_not_unique' => 'Selected class does not exist'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Please select a valid status'
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