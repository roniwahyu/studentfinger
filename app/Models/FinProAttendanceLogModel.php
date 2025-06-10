<?php

namespace App\Models;

use CodeIgniter\Model;

class FinProAttendanceLogModel extends Model
{
    protected $DBGroup = 'fin_pro';
    protected $table = 'att_log';
    protected $primaryKey = ['sn', 'scan_date', 'pin'];
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'sn',
        'scan_date', 
        'pin',
        'verifymode',
        'inoutmode',
        'reserved',
        'work_code',
        'att_id'
    ];

    protected $useTimestamps = false;

    /**
     * Get new attendance records since last sync
     */
    public function getNewRecords($lastSyncTime = null)
    {
        $query = $this->select('att_log.*, students.id as student_id, students.firstname, students.lastname, parent_contacts.phone_number as parent_phone')
            ->join('studentfinger.students', 'att_log.pin = students.student_id', 'left')
            ->join('studentfinger.parent_contacts', 'students.id = parent_contacts.student_id AND parent_contacts.is_primary = 1', 'left')
            ->where('att_log.scan_date >=', $lastSyncTime ?? date('Y-m-d 00:00:00'))
            ->where('TIME(att_log.scan_date) BETWEEN "07:00:00" AND "16:00:00"')
            ->where('DAYOFWEEK(att_log.scan_date) BETWEEN 2 AND 6'); // Monday to Friday

        return $query->findAll();
    }
}
