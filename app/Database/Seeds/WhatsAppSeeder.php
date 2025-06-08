<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WhatsAppSeeder extends Seeder
{
    public function run()
    {
        // Create default templates (using existing table structure)
        $templates = [
            [
                'template_name' => 'Student Check-in Notification',
                'template_content' => 'Hello {parent_name}, your child {student_name} has arrived at school at {time} on {date}. Have a great day!',
                'template_type' => 'attendance'
            ],
            [
                'template_name' => 'Student Check-out Notification',
                'template_content' => 'Hello {parent_name}, your child {student_name} has left school at {time} on {date}. Please ensure safe arrival home.',
                'template_type' => 'attendance'
            ],
            [
                'template_name' => 'Absence Notification',
                'template_content' => 'Dear {parent_name}, we noticed that {student_name} is absent today ({date}). Please contact the school if this is an emergency.',
                'template_type' => 'attendance'
            ],
            [
                'template_name' => 'General Announcement',
                'template_content' => 'Dear Parents/Students, {announcement_text}. Thank you for your attention. - {school_name}',
                'template_type' => 'notification'
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
            'api_url' => 'https://api.wablas.com'
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

        // Skip settings creation for now since wa_settings table might not exist

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
