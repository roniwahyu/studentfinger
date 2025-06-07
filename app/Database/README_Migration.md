# Student Attendance WhatsApp Integration - Database Migration

This directory contains CodeIgniter 4 migration files generated from the combined SQL structure for the Student Attendance and WhatsApp Notification System.

## Migration Files

### 1. `2025-01-01-000001_CreateStudentManagementTables.php`
Creates core student management tables:
- `students` - Student information with RFID support
- `sessions` - Academic sessions (e.g., 2024-2025)
- `classes` - Class information (X, XI, XII)
- `sections` - Section information (A, B, C)
- `class_sections` - Links classes and sections
- `student_session` - Links students to academic sessions

### 2. `2025-01-01-000002_CreateAcademicAndAttendanceTables.php`
Creates academic structure and attendance system tables:
- `subjects` - Subject information
- `subject_timetable` - Class schedules with late tolerance
- `device` - FingerSpot attendance devices
- `att_log` - Raw attendance logs from devices
- `student_attendance` - Processed attendance records

### 3. `2025-01-01-000003_CreatePermissionCalendarTables.php`
Creates permission system and calendar tables:
- `jns_izin` - Permission types (Sakit, Izin, Alpha, Terlambat)
- `kategori_izin` - Permission categories
- `student_izin` - Student permission requests
- `libur` - Holiday information
- `calender` - Academic calendar
- `wa_devices` - WhatsApp device management

### 4. `2025-01-01-000004_CreateWhatsAppIntegrationTables.php`
Creates WhatsApp integration tables:
- `wa_schedules` - Scheduled WhatsApp messages
- `wa_auto_replies` - Auto-reply configurations
- `wa_contacts` - WhatsApp contacts for notifications
- `wa_templates` - Message templates
- `wa_messages` - Sent WhatsApp messages
- `wa_logs` - WhatsApp message logs

## Seeder File

### `StudentAttendanceSeeder.php`
Populates the database with sample data:
- Permission types and categories
- Academic session (2024-2025)
- Sample classes, sections, and subjects
- WhatsApp message templates

## How to Run

### 1. Run Migrations
```bash
# Navigate to your CodeIgniter project root
cd c:\laragon\www\studentfinger

# Run all migrations
php spark migrate

# Or run specific migration
php spark migrate -g default
```

### 2. Run Seeder
```bash
# Run the seeder to populate sample data
php spark db:seed StudentAttendanceSeeder
```

### 3. Check Migration Status
```bash
# Check which migrations have been run
php spark migrate:status
```

### 4. Rollback Migrations (if needed)
```bash
# Rollback last batch
php spark migrate:rollback

# Rollback to specific batch
php spark migrate:rollback -b 1

# Reset all migrations
php spark migrate:reset
```

## Database Configuration

Make sure your database configuration is set up in `app/Config/Database.php`:

```php
public array $default = [
    'DSN'          => '',
    'hostname'     => 'localhost',
    'username'     => 'your_username',
    'password'     => 'your_password',
    'database'     => 'your_database_name',
    'DBDriver'     => 'MySQLi',
    'DBPrefix'     => '',
    'pConnect'     => false,
    'DBDebug'      => true,
    'charset'      => 'utf8mb4',
    'DBCollat'     => 'utf8mb4_general_ci',
    'swapPre'      => '',
    'encrypt'      => false,
    'compress'     => false,
    'strictOn'     => false,
    'failover'     => [],
    'port'         => 3306,
    'numberNative' => false,
    'dateFormat'   => [
        'date'     => 'Y-m-d',
        'datetime' => 'Y-m-d H:i:s',
        'time'     => 'H:i:s',
    ],
];
```

## Features

### Student Management
- Complete student information with RFID support
- Academic session management
- Class and section organization

### Attendance System
- Integration with FingerSpot devices
- Raw attendance log processing
- Late tolerance configuration
- Attendance status tracking

### Permission System
- Multiple permission types (Sick, Permission, Absent, Late)
- Permission categories
- Approval workflow

### WhatsApp Integration
- Wablas API integration
- Scheduled messaging
- Auto-reply functionality
- Message templates
- Comprehensive logging

### Calendar System
- Holiday management
- Academic calendar
- Date-based attendance validation

## Notes

1. **Foreign Key Constraints**: All tables include proper foreign key relationships for data integrity.

2. **UTF8MB4 Support**: All tables use UTF8MB4 charset for full Unicode support including emojis.

3. **Timestamps**: WhatsApp related tables include created_at and updated_at timestamps.

4. **Indexes**: Proper indexing is applied for performance optimization.

5. **Comments**: Database fields include comments for better documentation.

6. **Rollback Support**: All migrations include proper down() methods for rollback functionality.

## Troubleshooting

### Common Issues

1. **Foreign Key Constraint Errors**
   - Make sure to run migrations in the correct order
   - Check if referenced tables exist before creating foreign keys

2. **Character Set Issues**
   - Ensure your MySQL server supports UTF8MB4
   - Check database configuration charset settings

3. **Permission Errors**
   - Verify database user has CREATE, ALTER, DROP privileges
   - Check if database exists and is accessible

### Getting Help

If you encounter issues:
1. Check the CodeIgniter 4 migration documentation
2. Verify your database configuration
3. Check migration logs in `writable/logs/`
4. Use `php spark migrate:status` to check migration state