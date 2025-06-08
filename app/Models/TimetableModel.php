<?php

namespace App\Models;

use CodeIgniter\Model;

class TimetableModel extends Model
{
    protected $table = 'timetables';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'class_id',
        'section_id',
        'subject',
        'teacher',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'class_id' => 'required|integer|is_not_unique[classes.id]',
        'section_id' => 'required|integer|is_not_unique[sections.id]',
        'subject' => 'required|min_length[2]|max_length[100]',
        'teacher' => 'permit_empty|max_length[100]',
        'day_of_week' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
        'start_time' => 'required|valid_date[H:i]',
        'end_time' => 'required|valid_date[H:i]',
        'room' => 'permit_empty|max_length[50]',
        'status' => 'required|in_list[Active,Inactive]'
    ];
    
    protected $validationMessages = [
        'class_id' => [
            'required' => 'Class is required',
            'is_not_unique' => 'Selected class does not exist'
        ],
        'section_id' => [
            'required' => 'Section is required',
            'is_not_unique' => 'Selected section does not exist'
        ],
        'subject' => [
            'required' => 'Subject is required'
        ],
        'day_of_week' => [
            'required' => 'Day of week is required',
            'in_list' => 'Please select a valid day'
        ],
        'start_time' => [
            'required' => 'Start time is required',
            'valid_date' => 'Please provide a valid start time'
        ],
        'end_time' => [
            'required' => 'End time is required',
            'valid_date' => 'Please provide a valid end time'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Please select a valid status'
        ]
    ];
    
    /**
     * Get timetable for a class and section
     */
    public function getTimetableByClassSection($classId, $sectionId)
    {
        return $this->where('class_id', $classId)
                   ->where('section_id', $sectionId)
                   ->where('status', 'Active')
                   ->orderBy('day_of_week', 'ASC')
                   ->orderBy('start_time', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get timetable for a specific day
     */
    public function getTimetableByDay($classId, $sectionId, $dayOfWeek)
    {
        return $this->where('class_id', $classId)
                   ->where('section_id', $sectionId)
                   ->where('day_of_week', $dayOfWeek)
                   ->where('status', 'Active')
                   ->orderBy('start_time', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get current period
     */
    public function getCurrentPeriod($classId, $sectionId, $currentTime = null)
    {
        if (!$currentTime) {
            $currentTime = date('H:i');
        }
        
        $dayOfWeek = date('l');
        
        return $this->where('class_id', $classId)
                   ->where('section_id', $sectionId)
                   ->where('day_of_week', $dayOfWeek)
                   ->where('start_time <=', $currentTime)
                   ->where('end_time >=', $currentTime)
                   ->where('status', 'Active')
                   ->first();
    }
    
    /**
     * Get next period
     */
    public function getNextPeriod($classId, $sectionId, $currentTime = null)
    {
        if (!$currentTime) {
            $currentTime = date('H:i');
        }
        
        $dayOfWeek = date('l');
        
        return $this->where('class_id', $classId)
                   ->where('section_id', $sectionId)
                   ->where('day_of_week', $dayOfWeek)
                   ->where('start_time >', $currentTime)
                   ->where('status', 'Active')
                   ->orderBy('start_time', 'ASC')
                   ->first();
    }
    
    /**
     * Check for time conflicts
     */
    public function hasTimeConflict($classId, $sectionId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        $builder = $this->where('class_id', $classId)
                       ->where('section_id', $sectionId)
                       ->where('day_of_week', $dayOfWeek)
                       ->where('status', 'Active')
                       ->groupStart()
                           ->groupStart()
                               ->where('start_time <=', $startTime)
                               ->where('end_time >', $startTime)
                           ->groupEnd()
                           ->orGroupStart()
                               ->where('start_time <', $endTime)
                               ->where('end_time >=', $endTime)
                           ->groupEnd()
                           ->orGroupStart()
                               ->where('start_time >=', $startTime)
                               ->where('end_time <=', $endTime)
                           ->groupEnd()
                       ->groupEnd();
                       
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }
}