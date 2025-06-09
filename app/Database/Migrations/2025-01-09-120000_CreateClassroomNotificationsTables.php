<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClassroomNotificationsTables extends Migration
{
    public function up()
    {
        // Class Sessions Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'class_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'session_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'teacher_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'break_duration' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 15,
                'comment' => 'Break duration in minutes',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['scheduled', 'started', 'break', 'resumed', 'finished', 'cancelled'],
                'default' => 'scheduled',
            ],
            'actual_start_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'actual_break_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'actual_resume_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'actual_end_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'total_students' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'present_students' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'notifications_sent' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'session_date' => [
                'type' => 'DATE',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['class_id', 'session_date']);
        $this->forge->addKey('status');
        $this->forge->addKey('session_date');
        $this->forge->createTable('class_sessions');
        
        // Notification Templates Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'template_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'event_type' => [
                'type' => 'ENUM',
                'constraint' => ['session_start', 'session_break', 'session_resume', 'session_finish', 'attendance_marked'],
            ],
            'message_template' => [
                'type' => 'TEXT',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'language' => [
                'type' => 'ENUM',
                'constraint' => ['id', 'en'],
                'default' => 'id',
            ],
            'variables' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Available variables for this template',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['event_type', 'language']);
        $this->forge->addKey('is_active');
        $this->forge->createTable('notification_templates');
        
        // Notification Logs Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'session_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'student_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'parent_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'parent_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'event_type' => [
                'type' => 'ENUM',
                'constraint' => ['session_start', 'session_break', 'session_resume', 'session_finish', 'attendance_marked'],
            ],
            'template_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'message_content' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'delivered', 'read', 'failed', 'retry'],
                'default' => 'pending',
            ],
            'wablas_response' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Response from WABLAS API',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'failed_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'retry_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'variables_used' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Variables used in the message',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id');
        $this->forge->addKey('student_id');
        $this->forge->addKey(['event_type', 'status']);
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('notification_logs');
        
        // Insert default templates
        $this->insertDefaultTemplates();
    }
    
    public function down()
    {
        $this->forge->dropTable('notification_logs');
        $this->forge->dropTable('notification_templates');
        $this->forge->dropTable('class_sessions');
    }
    
    /**
     * Insert default notification templates
     */
    private function insertDefaultTemplates()
    {
        $db = \Config\Database::connect();
        
        $templates = [
            // Session Start Templates
            [
                'template_name' => 'Kelas Dimulai - Default',
                'event_type' => 'session_start',
                'message_template' => "🎓 *KELAS DIMULAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKami informasikan bahwa {student_name} telah hadir di kelas:\n\n📚 *Mata Pelajaran:* {subject}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Mulai:* {start_time}\n📅 *Tanggal:* {session_date}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
                'is_active' => 1,
                'language' => 'id',
                'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'start_time', 'session_date', 'school_name']),
                'description' => 'Template notifikasi saat kelas dimulai',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'template_name' => 'Class Started - Default',
                'event_type' => 'session_start',
                'message_template' => "🎓 *CLASS STARTED*\n\nDear Parent/Guardian {parent_name},\n\nWe inform you that {student_name} is attending class:\n\n📚 *Subject:* {subject}\n🏫 *Class:* {class_name}\n👨‍🏫 *Teacher:* {teacher_name}\n⏰ *Start Time:* {start_time}\n📅 *Date:* {session_date}\n\nThank you for your attention.\n\n*{school_name}*",
                'is_active' => 1,
                'language' => 'en',
                'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'start_time', 'session_date', 'school_name']),
                'description' => 'Notification template when class starts',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            
            // Session Break Templates
            [
                'template_name' => 'Istirahat Kelas - Default',
                'event_type' => 'session_break',
                'message_template' => "☕ *ISTIRAHAT KELAS*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} sedang istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Istirahat:* {break_time}\n⏱️ *Durasi:* {break_duration} menit\n\nKelas akan dilanjutkan setelah istirahat.\n\n*{school_name}*",
                'is_active' => 1,
                'language' => 'id',
                'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'break_time', 'break_duration', 'school_name']),
                'description' => 'Template notifikasi saat kelas istirahat',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            
            // Session Resume Templates
            [
                'template_name' => 'Kelas Dilanjutkan - Default',
                'event_type' => 'session_resume',
                'message_template' => "📚 *KELAS DILANJUTKAN*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah dilanjutkan setelah istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Lanjut:* {resume_time}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
                'is_active' => 1,
                'language' => 'id',
                'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'resume_time', 'school_name']),
                'description' => 'Template notifikasi saat kelas dilanjutkan',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            
            // Session Finish Templates
            [
                'template_name' => 'Kelas Selesai - Default',
                'event_type' => 'session_finish',
                'message_template' => "✅ *KELAS SELESAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah selesai:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Selesai:* {end_time}\n⏱️ *Durasi Total:* {total_duration}\n\n{student_name} dapat dijemput atau pulang sesuai jadwal.\n\n*{school_name}*",
                'is_active' => 1,
                'language' => 'id',
                'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'end_time', 'total_duration', 'school_name']),
                'description' => 'Template notifikasi saat kelas selesai',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $db->table('notification_templates')->insertBatch($templates);
    }
}
