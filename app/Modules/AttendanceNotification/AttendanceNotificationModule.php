<?php

namespace App\Modules\AttendanceNotification;

use App\Libraries\BaseModule;
use CodeIgniter\I18n\Time;

class AttendanceNotificationModule extends BaseModule
{
    protected string $moduleName = 'AttendanceNotification';
    protected array $dependencies = ['WablasIntegration'];
    
    protected function initialize(): void
    {
        helper(['date']);
        $this->setupScheduler();
    }

    public function processNewAttendances()
    {
        $finProDb = \Config\Database::connect('finpro');
        $studentDb = \Config\Database::connect('default');
        
        // Get today's date in Y-m-d format
        $today = date('Y-m-d');
        
        // Get school hours from settings
        $schoolStart = '07:00:00';
        $schoolEnd = '16:00:00';
        
        // Query new attendance records from fin_pro
        $query = $finProDb->query(
            "SELECT * FROM att_log 
             WHERE DATE(scan_date) = ? 
             AND TIME(scan_date) BETWEEN ? AND ?
             ORDER BY scan_date ASC",
            [$today, $schoolStart, $schoolEnd]
        );
        
        $attendances = $query->getResult();
        
        foreach ($attendances as $attendance) {
            // Check if already processed
            $exists = $studentDb->table('att_log')
                              ->where('sn', $attendance->sn)
                              ->where('scan_date', $attendance->scan_date)
                              ->where('pin', $attendance->pin)
                              ->get()
                              ->getRow();
            
            if (!$exists) {
                // Insert into studentfinger.att_log
                $studentDb->table('att_log')->insert([
                    'sn' => $attendance->sn,
                    'scan_date' => $attendance->scan_date,
                    'pin' => $attendance->pin,
                    'verifymode' => $attendance->verifymode,
                    'inoutmode' => $attendance->inoutmode,
                    'reserved' => $attendance->reserved,
                    'work_code' => $attendance->work_code,
                    'created_at' => Time::now(),
                    'status' => 1
                ]);
                
                // Get student info for notification
                $student = $studentDb->table('students')
                                   ->where('pin', $attendance->pin)
                                   ->get()
                                   ->getRow();
                
                if ($student) {
                    $this->sendAttendanceNotification($student, $attendance);
                }
            }
        }
    }
    
    protected function sendAttendanceNotification($student, $attendance)
    {
        // Get parent contact
        $db = \Config\Database::connect();
        $contact = $db->table('parent_contacts')
                     ->where('student_id', $student->student_id)
                     ->where('is_primary', 1)
                     ->get()
                     ->getRow();
                     
        if (!$contact) {
            log_message('error', "No primary parent contact found for student {$student->student_id}");
            return;
        }
        
        // Format message
        $scanTime = Time::parse($attendance->scan_date);
        $messageType = $this->determineAttendanceType($scanTime);
        
        // Get message template
        $template = $this->getMessageTemplate($messageType);
        
        // Replace placeholders
        $message = $this->formatMessage($template, [
            'parent_name' => $contact->name,
            'student_name' => $student->firstname . ' ' . $student->lastname,
            'time' => $scanTime->format('H:i'),
            'date' => $scanTime->format('d/m/Y')
        ]);
        
        // Send WhatsApp notification via Wablas
        $wablasModule = \App\Libraries\ModuleManager::load('WablasIntegration');
        $wablasModule->sendMessage($contact->phone, $message);
        
        // Log notification
        $this->logNotification($student->student_id, $messageType, $contact->phone);
    }
    
    protected function determineAttendanceType(Time $scanTime): string
    {
        $hour = (int)$scanTime->format('H');
        
        if ($hour < 12) {
            return 'arrival';
        }
        return 'departure';
    }
    
    protected function getMessageTemplate(string $type): string
    {
        $templates = [
            'arrival' => "Yth. {parent_name},\n\nPutra/Putri Anda *{student_name}* telah hadir di sekolah pada:\nWaktu: {time}\nTanggal: {date}\n\nTerima kasih.",
            'departure' => "Yth. {parent_name},\n\nPutra/Putri Anda *{student_name}* telah meninggalkan sekolah pada:\nWaktu: {time}\nTanggal: {date}\n\nTerima kasih."
        ];
        
        return $templates[$type];
    }
    
    protected function formatMessage(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
    
    protected function logNotification($studentId, $type, $phone)
    {
        $db = \Config\Database::connect();
        $db->table('notification_logs')->insert([
            'student_id' => $studentId,
            'type' => $type,
            'phone' => $phone,
            'sent_at' => Time::now(),
            'status' => 'sent'
        ]);
    }
    
    protected function setupScheduler()
    {
        // Set up scheduled task to run every 5 minutes during school hours
        \Config\Services::scheduler()->addTask()
            ->daily()
            ->between('07:00', '16:00')
            ->every('5 minutes')
            ->call(function() {
                $this->processNewAttendances();
            });
    }
}
