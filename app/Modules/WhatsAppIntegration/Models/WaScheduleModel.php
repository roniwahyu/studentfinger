<?php

namespace App\Modules\WhatsAppIntegration\Models;

use CodeIgniter\Model;

class WaScheduleModel extends Model
{
    protected $table = 'wa_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_id',
        'phone_number',
        'message',
        'schedule_time',
        'status',
        'sent_at',
        'error_message',
        'created_at'
    ];
    
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    
    protected $validationRules = [
        'device_id' => 'required|integer',
        'phone_number' => 'required|min_length[10]',
        'message' => 'required|min_length[1]',
        'schedule_time' => 'required|valid_date[Y-m-d H:i:s]'
    ];

    /**
     * Get due messages for processing
     */
    public function getDueMessages()
    {
        return $this->where('status', 0)
                   ->where('schedule_time <=', date('Y-m-d H:i:s'))
                   ->orderBy('schedule_time', 'ASC')
                   ->findAll();
    }

    /**
     * Get scheduled messages for a device
     */
    public function getScheduledByDevice($deviceId)
    {
        return $this->where('device_id', $deviceId)
                   ->where('status', 0)
                   ->where('schedule_time >', date('Y-m-d H:i:s'))
                   ->orderBy('schedule_time', 'ASC')
                   ->findAll();
    }

    /**
     * Get upcoming scheduled messages
     */
    public function getUpcomingMessages($hours = 24)
    {
        $endTime = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
        
        return $this->select('wa_schedules.*, wa_devices.device_name')
                   ->join('wa_devices', 'wa_devices.id = wa_schedules.device_id', 'left')
                   ->where('wa_schedules.status', 0)
                   ->where('wa_schedules.schedule_time >', date('Y-m-d H:i:s'))
                   ->where('wa_schedules.schedule_time <=', $endTime)
                   ->orderBy('wa_schedules.schedule_time', 'ASC')
                   ->findAll();
    }

    /**
     * Cancel scheduled message
     */
    public function cancelScheduled($id)
    {
        return $this->update($id, [
            'status' => 3, // Cancelled
            'error_message' => 'Cancelled by user'
        ]);
    }

    /**
     * Reschedule message
     */
    public function rescheduleMessage($id, $newScheduleTime)
    {
        return $this->update($id, [
            'schedule_time' => $newScheduleTime,
            'status' => 0 // Reset to pending
        ]);
    }

    /**
     * Get schedule statistics
     */
    public function getScheduleStats()
    {
        $total = $this->countAllResults();
        $pending = $this->where('status', 0)->countAllResults();
        $sent = $this->where('status', 1)->countAllResults();
        $failed = $this->where('status', 2)->countAllResults();
        $cancelled = $this->where('status', 3)->countAllResults();

        return [
            'total' => $total,
            'pending' => $pending,
            'sent' => $sent,
            'failed' => $failed,
            'cancelled' => $cancelled
        ];
    }
}
