<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class StudentAttendanceSeeder extends Seeder
{
    public function run()
    {
        // Insert sample permission types
        $permissionTypes = [
            ['izin_jenis_name' => 'Sakit'],
            ['izin_jenis_name' => 'Izin'],
            ['izin_jenis_name' => 'Alpha'],
            ['izin_jenis_name' => 'Terlambat'],
        ];
        $this->db->table('jns_izin')->insertBatch($permissionTypes);

        // Insert sample permission categories
        $permissionCategories = [
            ['kat_izin_name' => 'Kesehatan'],
            ['kat_izin_name' => 'Keluarga'],
            ['kat_izin_name' => 'Pribadi'],
            ['kat_izin_name' => 'Lainnya'],
        ];
        $this->db->table('kategori_izin')->insertBatch($permissionCategories);

        // Insert sample session
        $sessions = [
            ['session' => '2024-2025', 'status' => 1],
        ];
        $this->db->table('sessions')->insertBatch($sessions);

        // Insert sample classes
        $classes = [
            ['class' => 'X'],
            ['class' => 'XI'],
            ['class' => 'XII'],
        ];
        $this->db->table('classes')->insertBatch($classes);

        // Insert sample sections
        $sections = [
            ['section' => 'A'],
            ['section' => 'B'],
            ['section' => 'C'],
        ];
        $this->db->table('sections')->insertBatch($sections);

        // Insert sample subjects
        $subjects = [
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIN'],
            ['name' => 'Bahasa Inggris', 'code' => 'BIG'],
            ['name' => 'Fisika', 'code' => 'FIS'],
            ['name' => 'Kimia', 'code' => 'KIM'],
        ];
        $this->db->table('subjects')->insertBatch($subjects);

        // Insert sample WhatsApp templates
        $waTemplates = [
            [
                'template_name' => 'Absen Notification',
                'template_content' => 'Halo {parent_name}, anak Anda {student_name} tidak hadir pada mata pelajaran {subject} hari ini ({date}). Mohon konfirmasi jika ada keperluan mendesak.',
                'template_type' => 'attendance'
            ],
            [
                'template_name' => 'Late Notification',
                'template_content' => 'Halo {parent_name}, anak Anda {student_name} terlambat {late_minutes} menit pada mata pelajaran {subject} hari ini ({date}).',
                'template_type' => 'attendance'
            ],
            [
                'template_name' => 'Permission Approved',
                'template_content' => 'Halo {parent_name}, permohonan izin anak Anda {student_name} untuk tanggal {date} telah disetujui.',
                'template_type' => 'permission'
            ],
            [
                'template_name' => 'Permission Rejected',
                'template_content' => 'Halo {parent_name}, permohonan izin anak Anda {student_name} untuk tanggal {date} tidak dapat disetujui. Alasan: {reason}',
                'template_type' => 'permission'
            ],
        ];
        $this->db->table('wa_templates')->insertBatch($waTemplates);

        echo "Sample data has been seeded successfully!\n";
    }
}