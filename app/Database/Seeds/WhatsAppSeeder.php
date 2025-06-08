<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WhatsAppSeeder extends Seeder
{
    public function run()
    {
        // Create default templates
        $templates = [
            [
                'template_name' => 'Student Check-in Notification',
                'template_content' => 'Hello {parent_name}, your child {student_name} has arrived at school at {time} on {date}. Have a great day!',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{time}', '{date}']),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'template_name' => 'Student Check-out Notification',
                'template_content' => 'Hello {parent_name}, your child {student_name} has left school at {time} on {date}. Please ensure safe arrival home.',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{time}', '{date}']),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'template_name' => 'Absence Notification',
                'template_content' => 'Dear {parent_name}, we noticed that {student_name} is absent today ({date}). Please contact the school if this is an emergency.',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{date}']),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'template_name' => 'Late Arrival Warning',
                'template_content' => 'Hello {parent_name}, {student_name} arrived late at {time} on {date}. Please ensure punctual arrival to avoid missing important lessons.',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{time}', '{date}']),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'template_name' => 'General Announcement',
                'template_content' => 'Dear Parents/Students, {announcement_text}. Thank you for your attention. - {school_name}',
                'template_type' => 'notification',
                'variables' => json_encode(['{announcement_text}', '{school_name}']),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert templates
        foreach ($templates as $template) {
            $existing = $this->db->table('wa_templates')
                                ->where('template_name', $template['template_name'])
                                ->get()
                                ->getRowArray();
            
            if (!$existing) {
                $this->db->table('wa_templates')->insert($template);
            }
        }

        // Create sample device (you can modify this with real API credentials)
        $sampleDevice = [
            'device_name' => 'Sample WhatsApp Device',
            'device_token' => 'your_api_token_here',
            'device_status' => 0, // Inactive by default
            'api_url' => 'https://api.wablas.com',
            'device_type' => 'wablas',
            'webhook_url' => base_url('whatsappintegration/webhook/' . uniqid()),
            'settings' => json_encode([
                'auto_reply_enabled' => false,
                'rate_limit' => 30, // messages per minute
                'retry_attempts' => 3
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $existingDevice = $this->db->table('wa_devices')
                                  ->where('device_name', $sampleDevice['device_name'])
                                  ->get()
                                  ->getRowArray();

        if (!$existingDevice) {
            $this->db->table('wa_devices')->insert($sampleDevice);
        }

        // Sync contacts from students table if exists
        $this->syncContactsFromStudents();

        // Create default settings
        $defaultSettings = [
            [
                'setting_key' => 'attendance_integration',
                'setting_value' => json_encode([
                    'enabled' => false,
                    'device_id' => null,
                    'checkin_template_id' => 1,
                    'checkout_template_id' => 2,
                    'absent_template_id' => 3,
                    'late_template_id' => 4,
                    'auto_send_checkin' => false,
                    'auto_send_checkout' => false,
                    'auto_send_absent' => false,
                    'send_time_checkin' => '08:00',
                    'send_time_checkout' => '15:00',
                    'send_time_absent' => '09:00'
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'setting_key' => 'general_settings',
                'setting_value' => json_encode([
                    'school_name' => 'Student Attendance System',
                    'timezone' => 'Asia/Jakarta',
                    'date_format' => 'Y-m-d',
                    'time_format' => 'H:i',
                    'max_retry_attempts' => 3,
                    'queue_processing_interval' => 60, // seconds
                    'log_retention_days' => 90
                ]),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($defaultSettings as $setting) {
            $existing = $this->db->table('wa_settings')
                                ->where('setting_key', $setting['setting_key'])
                                ->get()
                                ->getRowArray();
            
            if (!$existing) {
                $this->db->table('wa_settings')->insert($setting);
            }
        }

        echo "WhatsApp Gateway seeder completed successfully!\n";
        echo "- Created default message templates\n";
        echo "- Created sample device configuration\n";
        echo "- Synced contacts from students table\n";
        echo "- Created default settings\n";
        echo "\nNext steps:\n";
        echo "1. Configure your WhatsApp API credentials in the device settings\n";
        echo "2. Enable attendance integration in settings\n";
        echo "3. Test the gateway by sending a message\n";
    }

    /**
     * Sync contacts from students table
     */
    private function syncContactsFromStudents()
    {
        // Check if students table exists
        if (!$this->db->tableExists('students')) {
            echo "Students table not found, skipping contact sync\n";
            return;
        }

        $students = $this->db->table('students')
                            ->select('student_id, firstname, lastname, mobileno, father_phone')
                            ->where('status', 1)
                            ->get()
                            ->getResultArray();

        $syncedCount = 0;

        foreach ($students as $student) {
            // Add student contact if mobile number exists
            if (!empty($student['mobileno'])) {
                $existingStudent = $this->db->table('wa_contacts')
                                           ->where('student_id', $student['student_id'])
                                           ->where('contact_type', 'Student')
                                           ->get()
                                           ->getRowArray();

                if (!$existingStudent) {
                    $this->db->table('wa_contacts')->insert([
                        'student_id' => $student['student_id'],
                        'contact_name' => trim($student['firstname'] . ' ' . $student['lastname']),
                        'contact_number' => $this->cleanPhoneNumber($student['mobileno']),
                        'contact_type' => 'Student'
                    ]);
                    $syncedCount++;
                }
            }

            // Add parent contact if father phone exists
            if (!empty($student['father_phone'])) {
                $existingParent = $this->db->table('wa_contacts')
                                          ->where('student_id', $student['student_id'])
                                          ->where('contact_type', 'Parent')
                                          ->get()
                                          ->getRowArray();

                if (!$existingParent) {
                    $this->db->table('wa_contacts')->insert([
                        'student_id' => $student['student_id'],
                        'contact_name' => 'Parent of ' . trim($student['firstname'] . ' ' . $student['lastname']),
                        'contact_number' => $this->cleanPhoneNumber($student['father_phone']),
                        'contact_type' => 'Parent'
                    ]);
                    $syncedCount++;
                }
            }
        }

        echo "Synced {$syncedCount} contacts from students table\n";
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present
        if (substr($cleaned, 0, 2) !== '62') {
            if (substr($cleaned, 0, 1) === '0') {
                $cleaned = '62' . substr($cleaned, 1);
            } else {
                $cleaned = '62' . $cleaned;
            }
        }
        
        return $cleaned;
    }
}
