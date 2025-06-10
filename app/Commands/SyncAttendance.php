<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SyncAttendance extends BaseCommand
{
    protected $group = 'Attendance';
    protected $name = 'attendance:sync';
    protected $description = 'Sync attendance records from fin_pro and send notifications';

    public function run(array $params)
    {
        $controller = new \App\Modules\AttendanceNotification\Controllers\AttendanceController();
        
        CLI::write('Starting attendance sync...', 'yellow');
        
        try {
            $result = $controller->syncAttendance();
            $data = json_decode($result->getJSON());
            
            if ($data->success) {
                CLI::write('✅ ' . $data->message, 'green');
                
                // Show notification logs
                $db = \Config\Database::connect();
                $logs = $db->table('notification_logs')
                          ->orderBy('created_at', 'DESC')
                          ->limit(5)
                          ->get()
                          ->getResult();
                
                if (!empty($logs)) {
                    CLI::write("\nRecent Notification Logs:", 'yellow');
                    foreach ($logs as $log) {
                        CLI::write("📱 Sent to: {$log->parent_phone}");
                        CLI::write("   Time: {$log->sent_at}");
                        CLI::write("   Status: {$log->status}");
                        CLI::write("------------------------");
                    }
                }
            } else {
                CLI::error($data->message);
            }
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
            CLI::write('❌ Sync failed', 'red');
        }
    }
}
