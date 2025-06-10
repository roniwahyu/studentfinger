<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestFullAttendanceSystem extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'system:test';
    protected $description = 'Run a comprehensive test of the attendance notification system';

    public function run(array $params)
    {
        CLI::write('=== RUNNING COMPREHENSIVE SYSTEM TEST ===', 'green');
        CLI::newLine();

        try {
            // Test each component
            $this->testModuleLoading()
                 ->testDatabaseConnections()
                 ->testNotificationTemplates()
                 ->testAttendanceProcessing()
                 ->testNotificationSending();

            CLI::newLine();
            CLI::write('=== TEST COMPLETED SUCCESSFULLY ===', 'green');

        } catch (\Exception $e) {
            CLI::error($e->getMessage());
            CLI::write('Test failed. Please check the error above.', 'red');
        }
    }

    private function testModuleLoading()
    {
        CLI::write('1. Testing Module Loading...', 'yellow');

        // Load required modules
        $attendanceModule = new \App\Modules\Attendance\AttendanceModule();
        $attendanceModule->initialize();
        CLI::write('✓ Attendance module loaded', 'green');

        // Test module configuration
        $config = config('Modules/Attendance/Config/Module');
        if (!$config) {
            throw new \Exception('Failed to load attendance module configuration');
        }
        CLI::write('✓ Module configuration loaded', 'green');

        CLI::newLine();
        return $this;
    }

    private function testDatabaseConnections()
    {
        CLI::write('2. Testing Database Connections...', 'yellow');

        $defaultDb = \Config\Database::connect('default');
        CLI::write('✓ Connected to default database', 'green');

        $finproDb = \Config\Database::connect('fin_pro');
        CLI::write('✓ Connected to fin_pro database', 'green');

        // Test basic query to verify access
        $result = $defaultDb->table('notification_templates')->countAllResults();
        CLI::write('✓ Notification templates table accessible (found ' . $result . ' templates)', 'green');

        $result = $finproDb->table('att_log')->countAllResults();
        CLI::write('✓ Attendance log table accessible (found ' . $result . ' records)', 'green');

        CLI::newLine();
        return $this;
    }

    private function testNotificationTemplates()
    {
        CLI::write('3. Testing Notification Templates...', 'yellow');

        $templateModel = new \App\Modules\ClassroomNotifications\Models\NotificationTemplateModel();
        
        // Check default templates
        $templates = $templateModel->findAll();
        CLI::write('✓ Found ' . count($templates) . ' notification templates', 'green');

        // Test template processing
        $testTemplate = $templateModel->getDefaultTemplate('session_start', 'id');
        if (!$testTemplate) {
            throw new \Exception('Failed to get default session start template');
        }
        CLI::write('✓ Default templates available', 'green');

        CLI::newLine();
        return $this;
    }

    private function testAttendanceProcessing()
    {
        CLI::write('4. Testing Attendance Processing...', 'yellow');

        // Initialize attendance service
        $attendanceModule = new \App\Modules\Attendance\AttendanceModule();
        
        // Get today's absences
        $absentStudents = $attendanceModule->getAbsentStudents(date('Y-m-d'));
        CLI::write('✓ Found ' . count($absentStudents) . ' absent students today', 'green');

        // Get today's late arrivals
        $lateArrivals = $attendanceModule->getLateArrivals(date('Y-m-d'));
        CLI::write('✓ Found ' . count($lateArrivals) . ' late arrivals today', 'green');

        CLI::newLine();
        return $this;
    }

    private function testNotificationSending()
    {
        CLI::write('5. Testing Notification System...', 'yellow');

        // Get Wablas integration controller
        $notificationController = new \App\Modules\WhatsAppIntegration\Controllers\AttendanceIntegrationController();
        
        // Check Wablas settings
        $settings = $notificationController->getAttendanceSettings();
        CLI::write('✓ WhatsApp integration settings loaded', 'green');

        // Get notification stats
        $stats = $notificationController->getAttendanceNotificationStats();
        CLI::write('✓ Notifications sent today: ' . ($stats['sent_today'] ?? 0), 'green');
        CLI::write('✓ Failed notifications today: ' . ($stats['failed_today'] ?? 0), 'green');
        CLI::write('✓ Total notifications this month: ' . ($stats['total_this_month'] ?? 0), 'green');

        // Check notification history
        $recentNotifications = $notificationController->getRecentAttendanceNotifications();
        CLI::write('✓ Found ' . count($recentNotifications) . ' recent notifications', 'green');

        CLI::newLine();
        return $this;
    }
}
