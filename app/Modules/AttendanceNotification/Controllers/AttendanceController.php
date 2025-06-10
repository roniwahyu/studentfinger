<?php

namespace App\Modules\AttendanceNotification\Controllers;

use App\Controllers\BaseController;
use App\Modules\WablasIntegration\WablasIntegrationModule;
use CodeIgniter\I18n\Time;

class AttendanceController extends BaseController
{
    protected $finProDb;
    protected $studentDb;
    protected $wablasModule;
    
    public function __construct()
    {
        $this->finProDb = \Config\Database::connect('finpro');
        $this->studentDb = \Config\Database::connect('default');
        $this->wablasModule = new WablasIntegrationModule();
    }    /**
     * Process and sync new attendance records
     */
    public function syncAttendance()
    {
        try {
            $service = new \App\Modules\AttendanceNotification\Services\AttendanceNotificationService();
            $result = $service->syncAndNotify();            return $this->response->setJSON($result);

        } catch (\Exception $e) {
            log_message('error', 'Error syncing attendance: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error syncing attendance: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Format attendance message
     */
    protected function formatAttendanceMessage($student, $record, $parent)
    {
        $scanTime = Time::parse($record->scan_date);
        $isArrival = (int)$scanTime->format('H') < 12;
        
        if ($isArrival) {
            return "🏫 *PRESENSI MASUK SEKOLAH*\n\n" .
                   "Yth. {$parent->contact_name},\n\n" .
                   "Putra/Putri Anda:\n" .
                   "Nama: *{$student->firstname} {$student->lastname}*\n" .
                   "Telah hadir di sekolah pada:\n" .
                   "Tanggal: {$scanTime->format('d/m/Y')}\n" .
                   "Waktu: {$scanTime->format('H:i')} WIB\n\n" .
                   "Terima kasih.";
        } else {
            return "🏫 *PRESENSI PULANG SEKOLAH*\n\n" .
                   "Yth. {$parent->contact_name},\n\n" .
                   "Putra/Putri Anda:\n" .
                   "Nama: *{$student->firstname} {$student->lastname}*\n" .
                   "Telah meninggalkan sekolah pada:\n" .
                   "Tanggal: {$scanTime->format('d/m/Y')}\n" .
                   "Waktu: {$scanTime->format('H:i')} WIB\n\n" .
                   "Terima kasih.";
        }
    }

    /**
     * Log notification
     */
    protected function logNotification(array $data)
    {
        $this->studentDb->table('notification_logs')->insert($data);
    }
}
