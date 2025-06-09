# FingerprintBridge Module

## Overview

The FingerprintBridge module provides a bridge/import application for transferring attendance data from the FinPro fingerspot machine database to the StudentFinger application database. This module follows the CodeIgniter 4 modular architecture and provides both web interface and API endpoints for import operations.

## Features

- **Database Bridge**: Connects to both FinPro (source) and StudentFinger (destination) databases
- **Manual Import**: Web interface for manual data import with date range selection
- **Automatic Import**: Scheduled import functionality (configurable)
- **Data Validation**: Validates and transforms data during import
- **Duplicate Handling**: Configurable handling of duplicate records (skip, update, error)
- **PIN Mapping**: Maps fingerprint machine PINs to student IDs
- **Import Logging**: Comprehensive logging of all import operations
- **Progress Tracking**: Real-time progress tracking for import operations
- **API Endpoints**: RESTful API for programmatic access

## Database Structure

### Source Database (fin_pro.att_log)
- `sn` (varchar 30) - Serial number of fingerprint device
- `scan_date` (datetime) - Date and time of scan
- `pin` (varchar 32) - Employee/Student PIN
- `verifymode` (int 11) - Verification method (1=Fingerprint, 3=RFID, 20=Face)
- `inoutmode` (int 11) - In/Out mode (0/1=Check In, 2=Check Out, 3=Break Out, 4=Break In)
- `reserved` (int 11) - Reserved field
- `work_code` (int 11) - Work code
- `att_id` (varchar 50) - Attendance ID

### Destination Database (studentfinger.att_log)
- All fields from source database plus:
- `status` (int 11) - Attendance status
- `serialnumber` (varchar 50) - Device serial number
- `student_id` (int 11) - Reference to students table
- `created_at`, `updated_at`, `deleted_at` - Timestamp fields

### Additional Tables
- `fingerprint_import_logs` - Import operation logs
- `student_pin_mapping` - Maps PINs to student IDs
- `fingerprint_import_settings` - Module settings

## Installation

1. **Database Configuration**: Add FinPro database connection to `app/Config/Database.php`:
```php
public array $fin_pro = [
    'hostname' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'fin_pro',
    'DBDriver' => 'MySQLi',
    'charset'  => 'latin1',
    'DBCollat' => 'latin1_swedish_ci',
    // ... other settings
];
```

2. **Run Migrations**: Execute the module migration to create required tables:
```bash
php spark migrate -n "App\Modules\FingerprintBridge"
```

3. **Configure Routes**: Module routes are automatically loaded via `app/Config/Routes.php`

## Usage

### Web Interface

1. **Dashboard**: Access at `/fingerprint-bridge`
   - View import statistics
   - Monitor running imports
   - Quick access to all features

2. **Manual Import**: Access at `/fingerprint-bridge/manual-import`
   - Select date range for import
   - Configure duplicate handling
   - Preview import data
   - Start import process

3. **PIN Mapping**: Access at `/fingerprint-bridge/pin-mapping`
   - Map fingerprint PINs to student IDs
   - Auto-create mappings from RFID cards
   - Manage existing mappings

4. **Import Logs**: Access at `/fingerprint-bridge/logs`
   - View all import operations
   - Filter by type, status, date
   - View detailed import results

### API Endpoints

- `POST /api/fingerprint-bridge/import` - Start import process
- `GET /api/fingerprint-bridge/status?log_id=123` - Get import status
- `GET /api/fingerprint-bridge/logs` - Get import logs
- `GET /api/fingerprint-bridge/stats` - Get import statistics
- `POST /api/fingerprint-bridge/test-connection` - Test database connection
- `POST /api/fingerprint-bridge/preview` - Preview import data

### Configuration

Module settings can be configured in `app/Modules/FingerprintBridge/Config/Module.php`:

```php
'settings' => [
    'auto_import_enabled' => false,
    'auto_import_interval' => 300, // seconds
    'import_batch_size' => 1000,
    'duplicate_handling' => 'skip', // skip, update, error
    'default_status' => 1,
    'log_retention_days' => 30,
    'verify_student_exists' => true,
    'create_missing_students' => false
]
```

## Data Flow

1. **Connection**: Module connects to FinPro database using separate connection
2. **Extraction**: Retrieves attendance records based on date range
3. **Transformation**: Maps fields and applies business rules
4. **Validation**: Validates data integrity and student mappings
5. **Loading**: Inserts/updates records in StudentFinger database
6. **Logging**: Records import results and statistics

## PIN Mapping

The module provides PIN mapping functionality to link fingerprint machine PINs with student records:

- **Manual Mapping**: Create mappings through web interface
- **Auto Mapping**: Automatically map based on RFID card numbers
- **Bulk Operations**: Import multiple mappings at once
- **Validation**: Ensures unique PIN-to-student relationships

## Error Handling

- **Connection Errors**: Graceful handling of database connection issues
- **Data Validation**: Comprehensive validation with detailed error messages
- **Duplicate Detection**: Configurable handling of duplicate records
- **Transaction Safety**: Uses database transactions for data integrity
- **Logging**: All errors are logged with context information

## Performance Considerations

- **Batch Processing**: Processes records in configurable batches
- **Memory Management**: Efficient memory usage for large datasets
- **Progress Tracking**: Real-time progress updates
- **Indexing**: Proper database indexing for optimal performance
- **Connection Pooling**: Efficient database connection management

## Security

- **Input Validation**: All inputs are validated and sanitized
- **SQL Injection Protection**: Uses prepared statements
- **Access Control**: Integration with application permission system
- **Audit Trail**: Complete audit trail of all operations

## Troubleshooting

### Common Issues

1. **Connection Failed**: Check database credentials and network connectivity
2. **Import Stuck**: Check for long-running queries or database locks
3. **Duplicate Errors**: Review duplicate handling settings
4. **Missing Students**: Configure PIN mappings or enable auto-creation

### Logs

Check import logs at `/fingerprint-bridge/logs` for detailed error information and import statistics.

## Development

### Adding New Features

1. Extend the `FingerprintImportService` class for business logic
2. Add new methods to models for data operations
3. Create new views for UI components
4. Update routes and controllers as needed

### Testing

Run tests using PHPUnit:
```bash
php spark test App\\Modules\\FingerprintBridge
```

## Support

For issues and feature requests, please refer to the main application documentation or contact the development team.
