<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=studentfinger', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define models and their expected configurations
    $models = [
        'students' => [
            'expected_fields' => ['student_id', 'name', 'email', 'phone', 'rfid_card'],
            'model_path' => 'app/Models/StudentModel.php'
        ],
        'classes' => [
            'expected_fields' => ['name', 'description', 'grade_level'],
            'model_path' => 'app/Models/ClassModel.php'
        ],
        'sections' => [
            'expected_fields' => ['name', 'description', 'capacity'],
            'model_path' => 'app/Models/SectionModel.php'
        ],
        'sessions' => [
            'expected_fields' => ['name', 'start_date', 'end_date'],
            'model_path' => 'app/Models/SessionModel.php'
        ]
    ];
    
    foreach($models as $tableName => $config) {
        echo "\n=== Checking table: $tableName ===\n";
        
        // Get actual table structure
        $stmt = $pdo->query("DESCRIBE $tableName");
        $actualFields = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actualFields[] = $row['Field'];
        }
        
        echo "Actual fields: " . implode(', ', $actualFields) . "\n";
        echo "Expected fields: " . implode(', ', $config['expected_fields']) . "\n";
        
        // Check for mismatches
        $missing = array_diff($config['expected_fields'], $actualFields);
        $extra = array_diff($actualFields, $config['expected_fields']);
        
        if(!empty($missing)) {
            echo "❌ Missing fields in table: " . implode(', ', $missing) . "\n";
        }
        
        if(!empty($extra)) {
            echo "ℹ️  Extra fields in table: " . implode(', ', $extra) . "\n";
        }
        
        if(empty($missing) && empty($extra)) {
            echo "✓ Table structure matches model expectations\n";
        }
        
        // Check audit trail columns
        $auditColumns = ['created_at', 'updated_at', 'deleted_at'];
        $missingAudit = array_diff($auditColumns, $actualFields);
        
        if(!empty($missingAudit)) {
            echo "❌ Missing audit columns: " . implode(', ', $missingAudit) . "\n";
        } else {
            echo "✓ All audit trail columns present\n";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}