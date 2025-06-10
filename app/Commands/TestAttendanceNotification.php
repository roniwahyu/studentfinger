<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\ModuleManager;

class TestAttendanceNotification extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'attendance:test';
    protected $description = 'Test attendance notification system';
    
    public function run(array $params)
    {
        CLI::write('Testing Attendance Notification System...', 'green');
        
        try {
            $module = ModuleManager::load('AttendanceNotification');
            $module->processNewAttendances();
            
            CLI::write('✅ Attendance processing completed', 'green');
            
            // Show notification logs
            $db = \Config\Database::connect();
            $logs = $db->table('notification_logs')
                      ->orderBy('sent_at', 'DESC')
                      ->limit(10)
                      ->get()
                      ->getResult();
            
            if (!empty($logs)) {
                CLI::write("\nRecent Notification Logs:", 'yellow');
                foreach ($logs as $log) {
                    CLI::write("📱 Student ID: {$log->student_id}");
                    CLI::write("   Type: {$log->type}");
                    CLI::write("   Phone: {$log->phone}");
                    CLI::write("   Sent At: {$log->sent_at}");
                    CLI::write("   Status: {$log->status}");
                    CLI::write("------------------------");
                }
            } else {
                CLI::write("\nNo recent notifications found", 'yellow');
            }
            
        } catch (\Exception $e) {
            CLI::error($e->getMessage());
            CLI::write('❌ Test failed', 'red');
        }
    }
}
