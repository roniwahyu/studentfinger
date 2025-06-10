<?php

namespace App\Modules\AttendanceNotification\Services;

class AttendanceNotificationService 
{
    protected $finproAttLogModel;
    protected $wablasService;
    protected $db;
    protected $config;

    public function __construct()
    {
        $this->finproAttLogModel = new \App\Models\FinProAttendanceLogModel();
        $this->wablasService = new \App\Modules\WhatsAppIntegration\Services\WhatsAppGatewayService();
        $this->db = \Config\Database::connect();
        $this->config = config('AttendanceNotification');
    }

    /**
     * Sync attendance and send notifications
     */
    public function syncAndNotify()
    {
        try {
            // Get last sync time
            $lastSync = $this->getLastSyncTime();
            
            // Get new attendance records
            $newRecords = $this->finproAttLogModel->getNewRecords($lastSync);
            
            foreach ($newRecords as $record) {
                // Skip if student or parent phone not found
                if (empty($record['student_id']) || empty($record['parent_phone'])) {
                    continue;
                }
                
                // Prepare notification message
                $message = $this->formatAttendanceMessage($record);
                
                // Send WhatsApp notification
                $result = $this->wablasService->sendMessage($record['parent_phone'], $message);
                
                // Log notification
                $this->logNotification([
                    'student_id' => $record['student_id'],
                    'scan_date' => $record['scan_date'],
                    'message' => $message,
                    'status' => $result['status'] ?? 'failed',
                    'response' => json_encode($result)
                ]);
            }

            // Update last sync time
            $this->updateLastSyncTime();

            return [
                'success' => true,
                'processed' => count($newRecords),
                'message' => 'Attendance sync and notifications completed'
            ];

        } catch (\Exception $e) {
            log_message('error', 'Attendance sync error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Format attendance message
     */
    protected function formatAttendanceMessage($record)
    {
        $studentName = $record['firstname'] . ' ' . $record['lastname'];
        $time = date('H:i', strtotime($record['scan_date']));
        $date = date('d F Y', strtotime($record['scan_date']));
        $type = $record['inoutmode'] == 0 ? 'masuk' : 'pulang';

        return "Assalamualaikum Wr. Wb.\n\n"
             . "Diberitahukan bahwa ananda *{$studentName}* telah {$type} sekolah pada:\n"
             . "Tanggal: {$date}\n"
             . "Pukul: {$time} WIB\n\n"
             . "Terima kasih.\n"
             . "Wassalamualaikum Wr. Wb.";
    }

    /**
     * Get last sync time from settings
     */
    protected function getLastSyncTime()
    {
        $query = $this->db->table('settings')
            ->where('key', 'last_attendance_sync')
            ->get();
        
        $result = $query->getRow();
        
        return $result ? $result->value : date('Y-m-d 00:00:00');
    }

    /**
     * Update last sync time
     */
    protected function updateLastSyncTime()
    {
        $this->db->table('settings')
            ->where('key', 'last_attendance_sync')
            ->set(['value' => date('Y-m-d H:i:s')])
            ->update();
    }

    /**
     * Log notification in database
     */    protected function logNotification($data)
    {
        $this->db->table('notification_logs')->insert([
            'session_id' => 0, // No specific session for fingerprint attendance
            'student_id' => $data['student_id'],
            'parent_phone' => $data['parent_phone'],
            'event_type' => 'attendance_marked',
            'message_content' => $data['message'],
            'status' => $data['status'],
            'wablas_response' => $data['response'],
            'sent_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
