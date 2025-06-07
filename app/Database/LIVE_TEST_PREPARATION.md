# Live Test Preparation Guide
# Student Attendance WhatsApp Integration System

This guide provides comprehensive preparation steps for live testing the Student Attendance and WhatsApp Notification System.

## 🚀 Pre-Live Test Checklist

### 1. Database Setup

#### A. Run Migrations
```bash
# Navigate to project directory
cd c:\laragon\www\studentfinger

# Check current migration status
php spark migrate:status

# Run all migrations
php spark migrate

# Verify all tables are created
php spark migrate:status
```

#### B. Seed Sample Data
```bash
# Run the seeder
php spark db:seed StudentAttendanceSeeder

# Verify data insertion
php spark db:table students --limit 5
php spark db:table wa_templates
```

#### C. Database Verification
```sql
-- Check all tables exist
SHOW TABLES;

-- Verify foreign key constraints
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = 'your_database_name';

-- Check sample data
SELECT COUNT(*) as total_students FROM students;
SELECT COUNT(*) as total_templates FROM wa_templates;
SELECT COUNT(*) as total_classes FROM classes;
```

### 2. Environment Configuration

#### A. Update `.env` File
```env
# Database Configuration
database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_username
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_general_ci

# WhatsApp API Configuration (Wablas)
WABLAS_API_URL = https://console.wablas.com
WABLAS_TOKEN = your_wablas_token_here
WABLAS_DEVICE_ID = your_device_id_here

# FingerSpot Device Configuration
FINGERSPOT_IP = 192.168.1.100
FINGERSPOT_PORT = 4370
FINGERSPOT_COMM_KEY = 0

# Application Settings
CI_ENVIRONMENT = testing
app.baseURL = http://localhost/studentfinger/
app.timezone = Asia/Jakarta

# Logging
logger.threshold = 4
```

#### B. Create Additional Config Files

**app/Config/WhatsApp.php**
```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class WhatsApp extends BaseConfig
{
    public string $apiUrl = '';
    public string $token = '';
    public string $deviceId = '';
    public int $timeout = 30;
    public bool $enableLogging = true;
    public string $logLevel = 'info';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->apiUrl = env('WABLAS_API_URL', 'https://console.wablas.com');
        $this->token = env('WABLAS_TOKEN', '');
        $this->deviceId = env('WABLAS_DEVICE_ID', '');
    }
}
```

**app/Config/FingerSpot.php**
```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class FingerSpot extends BaseConfig
{
    public string $ip = '';
    public int $port = 4370;
    public int $commKey = 0;
    public int $timeout = 10;
    public bool $enableSync = true;
    public int $syncInterval = 300; // 5 minutes
    
    public function __construct()
    {
        parent::__construct();
        
        $this->ip = env('FINGERSPOT_IP', '192.168.1.100');
        $this->port = (int) env('FINGERSPOT_PORT', 4370);
        $this->commKey = (int) env('FINGERSPOT_COMM_KEY', 0);
    }
}
```

### 3. Test Data Preparation

#### A. Create Test Students
```sql
-- Insert test students
INSERT INTO students (admission_no, firstname, lastname, mobileno, father_phone, rfid, status) VALUES
('2024001', 'Ahmad', 'Rizki', '081234567890', '081234567891', 'RFID001', 1),
('2024002', 'Siti', 'Nurhaliza', '081234567892', '081234567893', 'RFID002', 1),
('2024003', 'Budi', 'Santoso', '081234567894', '081234567895', 'RFID003', 1);

-- Link students to current session and class
INSERT INTO student_session (student_id, session_id, class_id, section_id) VALUES
(1, 1, 1, 1), -- Ahmad in X-A
(2, 1, 1, 1), -- Siti in X-A  
(3, 1, 1, 2); -- Budi in X-B
```

#### B. Create Test Timetable
```sql
-- Insert test timetable
INSERT INTO subject_timetable (class_id, section_id, subject_id, day, time_from, time_to, room_no, tolerance_late) VALUES
(1, 1, 1, 'Monday', '07:00:00', '08:30:00', 'R101', 15),    -- Math X-A
(1, 1, 2, 'Monday', '08:30:00', '10:00:00', 'R102', 15),    -- Indonesian X-A
(1, 2, 1, 'Monday', '07:00:00', '08:30:00', 'R103', 15);    -- Math X-B
```

#### C. Create Test WhatsApp Contacts
```sql
-- Insert WhatsApp contacts
INSERT INTO wa_contacts (student_id, contact_name, contact_number, contact_type) VALUES
(1, 'Pak Ahmad (Ayah)', '081234567891', 'Parent'),
(1, 'Ahmad Rizki', '081234567890', 'Student'),
(2, 'Bu Siti (Ibu)', '081234567893', 'Parent'),
(2, 'Siti Nurhaliza', '081234567892', 'Student'),
(3, 'Pak Budi (Ayah)', '081234567895', 'Parent'),
(3, 'Budi Santoso', '081234567894', 'Student');
```

#### D. Setup Test Device
```sql
-- Insert test FingerSpot device
INSERT INTO device (serialnumber, device_name, location, ip_address, comm_type, dev_type) VALUES
('FS001', 'Main Gate Scanner', 'School Main Entrance', '192.168.1.100', 0, 0);

-- Insert test WhatsApp device
INSERT INTO wa_devices (device_name, device_token, device_status, api_url, created_at, updated_at) VALUES
('School WhatsApp Bot', 'your_test_token_here', 1, 'https://console.wablas.com', NOW(), NOW());
```

### 4. API Testing

#### A. WhatsApp API Test
```bash
# Test Wablas API connection
curl -X POST "https://console.wablas.com/api/send-message" \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "081234567890",
    "message": "Test message from Student Attendance System"
  }'
```

#### B. FingerSpot Device Test
```php
// Create test script: test_fingerspot.php
<?php
require_once 'vendor/autoload.php';

// Test FingerSpot connection
$ip = '192.168.1.100';
$port = 4370;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (socket_connect($socket, $ip, $port)) {
    echo "FingerSpot device connected successfully\n";
    socket_close($socket);
} else {
    echo "Failed to connect to FingerSpot device\n";
}
?>
```

### 5. Live Test Scenarios

#### A. Attendance Scanning Test
1. **Normal Attendance**
   - Student scans RFID at correct time
   - Verify attendance recorded as "Present"
   - Check no WhatsApp notification sent

2. **Late Attendance**
   - Student scans RFID after tolerance time
   - Verify attendance recorded as "Late"
   - Check WhatsApp notification sent to parent

3. **Absent Student**
   - Student doesn't scan RFID
   - Run attendance processing job
   - Verify attendance recorded as "Absent"
   - Check WhatsApp notification sent to parent

#### B. Permission System Test
1. **Permission Request**
   - Submit permission request for student
   - Verify record in `student_izin` table

2. **Permission Approval**
   - Approve permission request
   - Verify attendance marked as "Permission"
   - Check WhatsApp notification sent

#### C. WhatsApp Integration Test
1. **Template Message**
   - Send template-based message
   - Verify message logged in `wa_messages`
   - Check delivery status in `wa_logs`

2. **Scheduled Message**
   - Create scheduled message
   - Wait for scheduled time
   - Verify message sent automatically

### 6. Performance Testing

#### A. Database Performance
```sql
-- Test query performance
EXPLAIN SELECT s.firstname, s.lastname, sa.status, sa.attendance_date 
FROM students s 
JOIN student_attendance sa ON s.student_id = sa.student_id 
WHERE sa.attendance_date = CURDATE();

-- Check index usage
SHOW INDEX FROM student_attendance;
SHOW INDEX FROM students;
```

#### B. Load Testing
```bash
# Install Apache Bench for load testing
# Test attendance endpoint
ab -n 100 -c 10 http://localhost/studentfinger/attendance/process

# Test WhatsApp sending endpoint
ab -n 50 -c 5 http://localhost/studentfinger/whatsapp/send
```

### 7. Security Checklist

#### A. Database Security
- [ ] Database user has minimal required privileges
- [ ] Database password is strong and secure
- [ ] Foreign key constraints are properly set
- [ ] Sensitive data is properly encrypted

#### B. API Security
- [ ] WhatsApp API token is secure and not exposed
- [ ] API endpoints have proper authentication
- [ ] Rate limiting is implemented
- [ ] Input validation is in place

#### C. Application Security
- [ ] CSRF protection enabled
- [ ] XSS protection enabled
- [ ] SQL injection prevention
- [ ] File upload restrictions

### 8. Monitoring Setup

#### A. Log Monitoring
```bash
# Monitor application logs
tail -f writable/logs/log-$(date +%Y-%m-%d).log

# Monitor error logs
tail -f writable/logs/error-$(date +%Y-%m-%d).log
```

#### B. Database Monitoring
```sql
-- Monitor database performance
SHOW PROCESSLIST;
SHOW STATUS LIKE 'Threads_connected';
SHOW STATUS LIKE 'Queries';
```

### 9. Backup Strategy

#### A. Database Backup
```bash
# Create database backup before live test
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Automated backup script
#!/bin/bash
BACKUP_DIR="/path/to/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p database_name > "$BACKUP_DIR/backup_$DATE.sql"
echo "Backup created: backup_$DATE.sql"
```

#### B. Application Backup
```bash
# Backup application files
tar -czf app_backup_$(date +%Y%m%d_%H%M%S).tar.gz /path/to/studentfinger/
```

### 10. Rollback Plan

#### A. Database Rollback
```bash
# If issues occur, rollback migrations
php spark migrate:rollback

# Restore from backup
mysql -u username -p database_name < backup_file.sql
```

#### B. Application Rollback
```bash
# Restore application from backup
tar -xzf app_backup_file.tar.gz -C /path/to/restore/
```

### 11. Go-Live Checklist

#### Pre-Launch (1 week before)
- [ ] All migrations tested and verified
- [ ] Sample data loaded and validated
- [ ] API integrations tested
- [ ] Performance testing completed
- [ ] Security audit completed
- [ ] Backup strategy implemented
- [ ] Monitoring tools configured

#### Launch Day
- [ ] Final database backup created
- [ ] All services started and verified
- [ ] API endpoints tested
- [ ] WhatsApp integration verified
- [ ] FingerSpot devices connected
- [ ] Test attendance scenarios executed
- [ ] Monitoring dashboards active

#### Post-Launch (First 24 hours)
- [ ] Monitor system performance
- [ ] Check error logs regularly
- [ ] Verify WhatsApp message delivery
- [ ] Monitor database performance
- [ ] Validate attendance data accuracy
- [ ] Check user feedback

### 12. Troubleshooting Guide

#### Common Issues

**Database Connection Issues**
```bash
# Check database connection
php spark db:table students --limit 1

# Verify database credentials
php -r "echo 'DB Test: ' . (new mysqli('localhost', 'user', 'pass', 'db'))->connect_error ?? 'Connected'; echo PHP_EOL;"
```

**WhatsApp API Issues**
```bash
# Test API connectivity
curl -I https://console.wablas.com/api/device-status

# Check token validity
curl -H "Authorization: Bearer your_token" https://console.wablas.com/api/device-status
```

**FingerSpot Device Issues**
```bash
# Test device connectivity
ping 192.168.1.100

# Test port connectivity
telnet 192.168.1.100 4370
```

### 13. Support Contacts

- **Database Issues**: DBA Team
- **WhatsApp API**: Wablas Support
- **FingerSpot Device**: FingerSpot Technical Support
- **Application Issues**: Development Team

---

## 📋 Final Pre-Live Test Command Sequence

```bash
# 1. Navigate to project
cd c:\laragon\www\studentfinger

# 2. Run migrations
php spark migrate

# 3. Seed data
php spark db:seed StudentAttendanceSeeder

# 4. Check migration status
php spark migrate:status

# 5. Test database connection
php spark db:table students --limit 5

# 6. Start application
php spark serve --host=0.0.0.0 --port=8080

# 7. Test endpoints
curl http://localhost:8080/
```

**System is ready for live testing when all checklist items are completed and all tests pass successfully.**