<?php

namespace App\Modules\Attendance\Models;

use CodeIgniter\Model;

class TimetableModel extends Model
{
    protected $table = 'timetables';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $allowedFields = [
        'class_section_id',
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room_number',
        'session_id',
        'effective_from',
        'effective_to',
        'is_active',
        'notes'
    ];
    
    protected $validationRules = [
        'class_section_id' => 'required|integer|is_not_unique[class_sections.id]',
        'subject_id' => 'permit_empty|integer',
        'teacher_id' => 'permit_empty|integer',
        'day_of_week' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
        'start_time' => 'required|valid_date[H:i:s]',
        'end_time' => 'required|valid_date[H:i:s]',
        'room_number' => 'permit_empty|max_length[50]',
        'session_id' => 'required|integer|is_not_unique[sessions.id]',
        'effective_from' => 'required|valid_date[Y-m-d]',
        'effective_to' => 'permit_empty|valid_date[Y-m-d]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'notes' => 'permit_empty|max_length[500]'
    ];
    
    protected $validationMessages = [
        'class_section_id' => [
            'required' => 'Class section is required',
            'integer' => 'Invalid class section',
            'is_not_unique' => 'Selected class section does not exist'
        ],
        'day_of_week' => [
            'required' => 'Day of week is required',
            'integer' => 'Day of week must be a number',
            'greater_than_equal_to' => 'Day of week must be between 1-7',
            'less_than_equal_to' => 'Day of week must be between 1-7'
        ],
        'start_time' => [
            'required' => 'Start time is required',
            'valid_date' => 'Please enter a valid start time'
        ],
        'end_time' => [
            'required' => 'End time is required',
            'valid_date' => 'Please enter a valid end time'
        ],
        'session_id' => [
            'required' => 'Session is required',
            'integer' => 'Invalid session',
            'is_not_unique' => 'Selected session does not exist'
        ],
        'effective_from' => [
            'required' => 'Effective from date is required',
            'valid_date' => 'Please enter a valid effective from date'
        ]
    ];
    
    /**
     * Get timetable for a class section
     */
    public function getTimetableByClassSection($classSectionId, $sessionId = null)
    {
        $builder = $this->select('timetables.*, subjects.name as subject_name, 
                                 teachers.name as teacher_name, classes.name as class_name, 
                                 sections.name as section_name')
                       ->join('class_sections', 'timetables.class_section_id = class_sections.id')
                       ->join('classes', 'class_sections.class_id = classes.id')
                       ->join('sections', 'class_sections.section_id = sections.id')
                       ->join('subjects', 'timetables.subject_id = subjects.id', 'left')
                       ->join('teachers', 'timetables.teacher_id = teachers.id', 'left')
                       ->where('timetables.class_section_id', $classSectionId)
                       ->where('timetables.is_active', 1)
                       ->where('timetables.deleted_at', null);
        
        if ($sessionId) {
            $builder->where('timetables.session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('timetables.effective_from <=', $currentDate)
               ->groupStart()
                   ->where('timetables.effective_to >=', $currentDate)
                   ->orWhere('timetables.effective_to IS NULL')
               ->groupEnd();
        
        return $builder->orderBy('timetables.day_of_week', 'ASC')
                      ->orderBy('timetables.start_time', 'ASC')
                      ->findAll();
    }
    
    /**
     * Get today's timetable for a class section
     */
    public function getTodayTimetableByClassSection($classSectionId, $sessionId = null)
    {
        $dayOfWeek = date('N'); // 1 (Monday) to 7 (Sunday)
        
        $builder = $this->select('timetables.*, subjects.name as subject_name, 
                                 teachers.name as teacher_name')
                       ->join('subjects', 'timetables.subject_id = subjects.id', 'left')
                       ->join('teachers', 'timetables.teacher_id = teachers.id', 'left')
                       ->where('timetables.class_section_id', $classSectionId)
                       ->where('timetables.day_of_week', $dayOfWeek)
                       ->where('timetables.is_active', 1)
                       ->where('timetables.deleted_at', null);
        
        if ($sessionId) {
            $builder->where('timetables.session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('timetables.effective_from <=', $currentDate)
               ->groupStart()
                   ->where('timetables.effective_to >=', $currentDate)
                   ->orWhere('timetables.effective_to IS NULL')
               ->groupEnd();
        
        return $builder->orderBy('timetables.start_time', 'ASC')
                      ->findAll();
    }
    
    /**
     * Get current period for a class section
     */
    public function getCurrentPeriod($classSectionId, $sessionId = null)
    {
        $currentTime = date('H:i:s');
        $dayOfWeek = date('N');
        
        $builder = $this->select('timetables.*, subjects.name as subject_name, 
                                 teachers.name as teacher_name')
                       ->join('subjects', 'timetables.subject_id = subjects.id', 'left')
                       ->join('teachers', 'timetables.teacher_id = teachers.id', 'left')
                       ->where('timetables.class_section_id', $classSectionId)
                       ->where('timetables.day_of_week', $dayOfWeek)
                       ->where('timetables.start_time <=', $currentTime)
                       ->where('timetables.end_time >=', $currentTime)
                       ->where('timetables.is_active', 1)
                       ->where('timetables.deleted_at', null);
        
        if ($sessionId) {
            $builder->where('timetables.session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('timetables.effective_from <=', $currentDate)
               ->groupStart()
                   ->where('timetables.effective_to >=', $currentDate)
                   ->orWhere('timetables.effective_to IS NULL')
               ->groupEnd();
        
        return $builder->first();
    }
    
    /**
     * Get next period for a class section
     */
    public function getNextPeriod($classSectionId, $sessionId = null)
    {
        $currentTime = date('H:i:s');
        $dayOfWeek = date('N');
        
        $builder = $this->select('timetables.*, subjects.name as subject_name, 
                                 teachers.name as teacher_name')
                       ->join('subjects', 'timetables.subject_id = subjects.id', 'left')
                       ->join('teachers', 'timetables.teacher_id = teachers.id', 'left')
                       ->where('timetables.class_section_id', $classSectionId)
                       ->where('timetables.day_of_week', $dayOfWeek)
                       ->where('timetables.start_time >', $currentTime)
                       ->where('timetables.is_active', 1)
                       ->where('timetables.deleted_at', null);
        
        if ($sessionId) {
            $builder->where('timetables.session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('timetables.effective_from <=', $currentDate)
               ->groupStart()
                   ->where('timetables.effective_to >=', $currentDate)
                   ->orWhere('timetables.effective_to IS NULL')
               ->groupEnd();
        
        return $builder->orderBy('timetables.start_time', 'ASC')
                      ->first();
    }
    
    /**
     * Get today's schedule for a student
     */
    public function getTodayScheduleForStudent($studentId)
    {
        $dayOfWeek = date('N');
        $currentDate = date('Y-m-d');
        
        return $this->select('timetables.*, subjects.name as subject_name, 
                             teachers.name as teacher_name, classes.name as class_name, 
                             sections.name as section_name')
                   ->join('class_sections', 'timetables.class_section_id = class_sections.id')
                   ->join('student_sessions', 'class_sections.id = student_sessions.class_section_id')
                   ->join('classes', 'class_sections.class_id = classes.id')
                   ->join('sections', 'class_sections.section_id = sections.id')
                   ->join('subjects', 'timetables.subject_id = subjects.id', 'left')
                   ->join('teachers', 'timetables.teacher_id = teachers.id', 'left')
                   ->where('student_sessions.student_id', $studentId)
                   ->where('timetables.day_of_week', $dayOfWeek)
                   ->where('timetables.is_active', 1)
                   ->where('timetables.deleted_at', null)
                   ->where('timetables.effective_from <=', $currentDate)
                   ->groupStart()
                       ->where('timetables.effective_to >=', $currentDate)
                       ->orWhere('timetables.effective_to IS NULL')
                   ->groupEnd()
                   ->orderBy('timetables.start_time', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get timetable by teacher
     */
    public function getTimetableByTeacher($teacherId, $sessionId = null)
    {
        $builder = $this->select('timetables.*, subjects.name as subject_name, 
                                 classes.name as class_name, sections.name as section_name')
                       ->join('class_sections', 'timetables.class_section_id = class_sections.id')
                       ->join('classes', 'class_sections.class_id = classes.id')
                       ->join('sections', 'class_sections.section_id = sections.id')
                       ->join('subjects', 'timetables.subject_id = subjects.id', 'left')
                       ->where('timetables.teacher_id', $teacherId)
                       ->where('timetables.is_active', 1)
                       ->where('timetables.deleted_at', null);
        
        if ($sessionId) {
            $builder->where('timetables.session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('timetables.effective_from <=', $currentDate)
               ->groupStart()
                   ->where('timetables.effective_to >=', $currentDate)
                   ->orWhere('timetables.effective_to IS NULL')
               ->groupEnd();
        
        return $builder->orderBy('timetables.day_of_week', 'ASC')
                      ->orderBy('timetables.start_time', 'ASC')
                      ->findAll();
    }
    
    /**
     * Check for time conflicts
     */
    public function checkTimeConflict($classSectionId, $dayOfWeek, $startTime, $endTime, $sessionId, $excludeId = null)
    {
        $builder = $this->where('class_section_id', $classSectionId)
                       ->where('day_of_week', $dayOfWeek)
                       ->where('session_id', $sessionId)
                       ->where('is_active', 1)
                       ->where('deleted_at', null)
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
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('effective_from <=', $currentDate)
               ->groupStart()
                   ->where('effective_to >=', $currentDate)
                   ->orWhere('effective_to IS NULL')
               ->groupEnd();
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Check teacher availability
     */
    public function checkTeacherAvailability($teacherId, $dayOfWeek, $startTime, $endTime, $sessionId, $excludeId = null)
    {
        $builder = $this->where('teacher_id', $teacherId)
                       ->where('day_of_week', $dayOfWeek)
                       ->where('session_id', $sessionId)
                       ->where('is_active', 1)
                       ->where('deleted_at', null)
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
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('effective_from <=', $currentDate)
               ->groupStart()
                   ->where('effective_to >=', $currentDate)
                   ->orWhere('effective_to IS NULL')
               ->groupEnd();
        
        return $builder->countAllResults() === 0;
    }
    
    /**
     * Get timetable statistics
     */
    public function getTimetableStatistics($sessionId = null)
    {
        $builder = $this->where('is_active', 1)
                       ->where('deleted_at', null);
        
        if ($sessionId) {
            $builder->where('session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('effective_from <=', $currentDate)
               ->groupStart()
                   ->where('effective_to >=', $currentDate)
                   ->orWhere('effective_to IS NULL')
               ->groupEnd();
        
        $stats = [
            'total_periods' => $builder->countAllResults(),
            'classes_with_timetable' => $this->select('DISTINCT class_section_id')
                                           ->where('is_active', 1)
                                           ->where('deleted_at', null)
                                           ->countAllResults(),
            'teachers_assigned' => $this->select('DISTINCT teacher_id')
                                      ->where('teacher_id IS NOT NULL')
                                      ->where('is_active', 1)
                                      ->where('deleted_at', null)
                                      ->countAllResults()
        ];
        
        // Get periods by day
        $periodsByDay = $this->select('day_of_week, COUNT(*) as count')
                           ->where('is_active', 1)
                           ->where('deleted_at', null)
                           ->groupBy('day_of_week')
                           ->findAll();
        
        $stats['by_day'] = [];
        $dayNames = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($periodsByDay as $day) {
            $stats['by_day'][$dayNames[$day['day_of_week']]] = $day['count'];
        }
        
        return $stats;
    }
    
    /**
     * Get free periods for a class section
     */
    public function getFreePeriods($classSectionId, $dayOfWeek, $sessionId = null)
    {
        // Define school hours (this could be configurable)
        $schoolStart = '08:00:00';
        $schoolEnd = '16:00:00';
        $periodDuration = 45; // minutes
        $breakDuration = 15; // minutes
        
        // Get existing periods
        $builder = $this->select('start_time, end_time')
                       ->where('class_section_id', $classSectionId)
                       ->where('day_of_week', $dayOfWeek)
                       ->where('is_active', 1)
                       ->where('deleted_at', null);
        
        if ($sessionId) {
            $builder->where('session_id', $sessionId);
        }
        
        // Filter by effective dates
        $currentDate = date('Y-m-d');
        $builder->where('effective_from <=', $currentDate)
               ->groupStart()
                   ->where('effective_to >=', $currentDate)
                   ->orWhere('effective_to IS NULL')
               ->groupEnd();
        
        $existingPeriods = $builder->orderBy('start_time', 'ASC')
                                 ->findAll();
        
        // Calculate free periods
        $freePeriods = [];
        $currentTime = $schoolStart;
        
        foreach ($existingPeriods as $period) {
            if ($currentTime < $period['start_time']) {
                $freePeriods[] = [
                    'start_time' => $currentTime,
                    'end_time' => $period['start_time']
                ];
            }
            $currentTime = $period['end_time'];
        }
        
        // Check if there's time after the last period
        if ($currentTime < $schoolEnd) {
            $freePeriods[] = [
                'start_time' => $currentTime,
                'end_time' => $schoolEnd
            ];
        }
        
        return $freePeriods;
    }
    
    /**
     * Bulk create timetable
     */
    public function bulkCreate($timetableData)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            $created = 0;
            foreach ($timetableData as $data) {
                // Validate each entry
                if ($this->validate($data)) {
                    // Check for conflicts
                    if (!$this->checkTimeConflict(
                        $data['class_section_id'],
                        $data['day_of_week'],
                        $data['start_time'],
                        $data['end_time'],
                        $data['session_id']
                    )) {
                        if ($this->insert($data)) {
                            $created++;
                        }
                    }
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return ['success' => false, 'message' => 'Transaction failed'];
            }
            
            return [
                'success' => true, 
                'message' => "Successfully created {$created} timetable entries",
                'created' => $created
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get timetable template
     */
    public function getTimetableTemplate()
    {
        return [
            'class_section_id' => '',
            'subject_id' => '',
            'teacher_id' => '',
            'day_of_week' => '',
            'start_time' => '',
            'end_time' => '',
            'room_number' => '',
            'session_id' => '',
            'effective_from' => date('Y-m-d'),
            'effective_to' => '',
            'is_active' => 1,
            'notes' => ''
        ];
    }
    
    /**
     * Copy timetable from another session
     */
    public function copyFromSession($fromSessionId, $toSessionId, $classSectionIds = null)
    {
        $builder = $this->select('class_section_id, subject_id, teacher_id, day_of_week, 
                                 start_time, end_time, room_number, notes')
                       ->where('session_id', $fromSessionId)
                       ->where('is_active', 1)
                       ->where('deleted_at', null);
        
        if ($classSectionIds && is_array($classSectionIds)) {
            $builder->whereIn('class_section_id', $classSectionIds);
        }
        
        $sourceTimetable = $builder->findAll();
        
        if (empty($sourceTimetable)) {
            return ['success' => false, 'message' => 'No timetable found to copy'];
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            $copied = 0;
            foreach ($sourceTimetable as $entry) {
                $entry['session_id'] = $toSessionId;
                $entry['effective_from'] = date('Y-m-d');
                $entry['is_active'] = 1;
                
                if ($this->insert($entry)) {
                    $copied++;
                }
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                return ['success' => false, 'message' => 'Transaction failed'];
            }
            
            return [
                'success' => true, 
                'message' => "Successfully copied {$copied} timetable entries",
                'copied' => $copied
            ];
        } catch (\Exception $e) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}