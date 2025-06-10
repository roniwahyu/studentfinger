<?php
/**
 * Update att_log table structure to match FingerSpot fingerprint machine standard
 * This script will add missing columns and ensure seamless import compatibility
 */

require_once 'vendor/autoload.php';

// Database configuration
$config = [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'studentfinger',
    'charset'  => 'utf8mb4'
];

try {
    $pdo = new PDO(
        "mysql:host={$config['hostname']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Connected to database successfully.\n";

    // Step 1: Check current table structure
    echo "\n=== Current att_log table structure ===\n";
    $stmt = $pdo->query("DESCRIBE att_log");
    $currentColumns = [];
    while ($row = $stmt->fetch()) {
        $currentColumns[] = $row['Field'];
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }

    // Step 2: Create backup table
    echo "\n=== Creating backup table ===\n";
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS att_log_backup AS SELECT * FROM att_log");
        echo "Backup table created successfully.\n";
    } catch (Exception $e) {
        echo "Backup creation failed (may already exist): " . $e->getMessage() . "\n";
    }

    // Step 3: Add missing columns
    echo "\n=== Adding missing columns ===\n";
    
    $columnsToAdd = [
        'sn' => "VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Serial number of device'",
        'inoutmode' => "INT(11) NOT NULL DEFAULT 0 COMMENT '0=Check In, 1=Check In, 2=Check Out, 3=Break Out, 4=Break In'",
        'reserved' => "INT(11) NOT NULL DEFAULT 0 COMMENT 'Reserved field for future use'",
        'work_code' => "INT(11) NOT NULL DEFAULT 0 COMMENT 'Work code for different work types'"
    ];

    foreach ($columnsToAdd as $column => $definition) {
        if (!in_array($column, $currentColumns)) {
            try {
                $sql = "ALTER TABLE att_log ADD COLUMN $column $definition";
                $pdo->exec($sql);
                echo "Added column: $column\n";
            } catch (Exception $e) {
                echo "Failed to add column $column: " . $e->getMessage() . "\n";
            }
        } else {
            echo "Column $column already exists.\n";
        }
    }

    // Step 4: Modify existing columns to match standard
    echo "\n=== Modifying existing columns ===\n";
    
    $columnsToModify = [
        'pin' => "VARCHAR(32) NOT NULL COMMENT 'Employee PIN/ID'",
        'verifymode' => "INT(11) NOT NULL COMMENT '1=Fingerprint, 3=RFID Card, 20=Face Recognition'",
        'att_id' => "VARCHAR(50) NOT NULL DEFAULT '0' COMMENT 'Attendance ID from device'"
    ];

    foreach ($columnsToModify as $column => $definition) {
        try {
            $sql = "ALTER TABLE att_log MODIFY COLUMN $column $definition";
            $pdo->exec($sql);
            echo "Modified column: $column\n";
        } catch (Exception $e) {
            echo "Failed to modify column $column: " . $e->getMessage() . "\n";
        }
    }

    // Step 5: Update existing records with default values
    echo "\n=== Updating existing records ===\n";
    try {
        // Update sn field from serialnumber if it exists
        $checkSerialNumber = $pdo->query("SHOW COLUMNS FROM att_log LIKE 'serialnumber'");
        if ($checkSerialNumber->rowCount() > 0) {
            $pdo->exec("UPDATE att_log SET sn = COALESCE(serialnumber, 'DEFAULT_DEVICE') WHERE sn = '' OR sn IS NULL");
            echo "Updated sn field from serialnumber.\n";
        } else {
            $pdo->exec("UPDATE att_log SET sn = 'DEFAULT_DEVICE' WHERE sn = '' OR sn IS NULL");
            echo "Set default sn values.\n";
        }

        // Update inoutmode from status if it exists
        $checkStatus = $pdo->query("SHOW COLUMNS FROM att_log LIKE 'status'");
        if ($checkStatus->rowCount() > 0) {
            $pdo->exec("UPDATE att_log SET inoutmode = COALESCE(status, 1) WHERE inoutmode = 0");
            echo "Updated inoutmode field from status.\n";
        }

        // Set default values for new fields
        $pdo->exec("UPDATE att_log SET reserved = 0, work_code = 0 WHERE reserved IS NULL OR work_code IS NULL");
        echo "Set default values for reserved and work_code fields.\n";

    } catch (Exception $e) {
        echo "Failed to update existing records: " . $e->getMessage() . "\n";
    }

    // Step 6: Create indexes for better performance
    echo "\n=== Creating indexes ===\n";
    
    $indexes = [
        'idx_pin' => 'pin',
        'idx_sn' => 'sn',
        'idx_scan_date' => 'scan_date',
        'idx_verifymode' => 'verifymode',
        'idx_inoutmode' => 'inoutmode'
    ];

    foreach ($indexes as $indexName => $column) {
        try {
            $pdo->exec("CREATE INDEX $indexName ON att_log ($column)");
            echo "Created index: $indexName on $column\n";
        } catch (Exception $e) {
            echo "Index $indexName may already exist: " . $e->getMessage() . "\n";
        }
    }

    // Step 7: Create mapping table for attendance modes
    echo "\n=== Creating attendance mode mapping table ===\n";
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS attendance_mode_mapping (
                inoutmode INT(11) PRIMARY KEY,
                status_name VARCHAR(50) NOT NULL,
                description VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert standard mappings
        $pdo->exec("
            INSERT IGNORE INTO attendance_mode_mapping (inoutmode, status_name, description) VALUES
            (0, 'Check In', 'Employee check in'),
            (1, 'Check In', 'Employee check in (alternative)'),
            (2, 'Check Out', 'Employee check out'),
            (3, 'Break Out', 'Employee going for break'),
            (4, 'Break In', 'Employee returning from break'),
            (5, 'Overtime In', 'Employee starting overtime'),
            (6, 'Overtime Out', 'Employee ending overtime')
        ");
        echo "Created attendance mode mapping table with standard values.\n";
    } catch (Exception $e) {
        echo "Failed to create mapping table: " . $e->getMessage() . "\n";
    }

    // Step 8: Show updated table structure
    echo "\n=== Updated att_log table structure ===\n";
    $stmt = $pdo->query("DESCRIBE att_log");
    while ($row = $stmt->fetch()) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
    }

    // Step 9: Show sample data
    echo "\n=== Sample data (first 3 records) ===\n";
    $stmt = $pdo->query("SELECT * FROM att_log LIMIT 3");
    $sampleData = $stmt->fetchAll();
    if (!empty($sampleData)) {
        // Print headers
        echo implode(" | ", array_keys($sampleData[0])) . "\n";
        echo str_repeat("-", 100) . "\n";
        
        // Print data
        foreach ($sampleData as $row) {
            echo implode(" | ", array_values($row)) . "\n";
        }
    } else {
        echo "No data found in att_log table.\n";
    }

    echo "\n=== Database update completed successfully! ===\n";
    echo "The att_log table now matches the FingerSpot fingerprint machine standard.\n";
    echo "Machine datasets can now be imported seamlessly.\n";

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
