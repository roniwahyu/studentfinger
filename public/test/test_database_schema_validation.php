<?php
// Comprehensive database schema validation test

echo "=== DATABASE SCHEMA VALIDATION TEST ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=studentfinger', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Successfully connected to MySQL database\n\n";
    
    // Test 1: Check critical tables exist
    echo "1. Checking Critical Tables...\n";
    $criticalTables = ['students', 'classes', 'sections', 'att_log'];
    
    foreach ($criticalTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' missing\n";
        }
    }
    
    // Test 2: Check students table structure
    echo "\n2. Validating Students Table Structure...\n";
    $stmt = $pdo->query("DESCRIBE students");
    $studentFields = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $studentFields[] = $row['Field'];
    }
    
    $expectedStudentFields = ['id', 'student_id', 'name', 'status', 'class_id', 'section_id'];
    foreach ($expectedStudentFields as $field) {
        if (in_array($field, $studentFields)) {
            echo "   ✅ Students.$field exists\n";
        } else {
            echo "   ❌ Students.$field missing\n";
        }
    }
    
    // Test 3: Check classes table structure
    echo "\n3. Validating Classes Table Structure...\n";
    $stmt = $pdo->query("DESCRIBE classes");
    $classFields = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $classFields[] = $row['Field'];
    }
    
    $expectedClassFields = ['id', 'class'];
    foreach ($expectedClassFields as $field) {
        if (in_array($field, $classFields)) {
            echo "   ✅ Classes.$field exists\n";
        } else {
            echo "   ❌ Classes.$field missing\n";
        }
    }
    
    // Check if classes has status column (should not exist based on our fixes)
    if (in_array('status', $classFields)) {
        echo "   ⚠️  Classes.status exists (unexpected)\n";
    } else {
        echo "   ✅ Classes.status correctly absent\n";
    }
    
    // Test 4: Check att_log table structure
    echo "\n4. Validating Att_Log Table Structure...\n";
    $stmt = $pdo->query("DESCRIBE att_log");
    $attLogFields = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $attLogFields[] = $row['Field'];
    }
    
    $expectedAttLogFields = ['sn', 'scan_date', 'pin', 'verifymode', 'inoutmode'];
    foreach ($expectedAttLogFields as $field) {
        if (in_array($field, $attLogFields)) {
            echo "   ✅ Att_log.$field exists\n";
        } else {
            echo "   ❌ Att_log.$field missing\n";
        }
    }
    
    // Test 5: Check foreign key relationships
    echo "\n5. Testing Foreign Key Relationships...\n";
    
    // Check if students.class_id references classes.id
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.class_id IS NOT NULL AND c.id IS NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] == 0) {
        echo "   ✅ Students.class_id -> Classes.id relationship valid\n";
    } else {
        echo "   ❌ Found {$result['count']} orphaned students.class_id references\n";
    }
    
    // Test 6: Check data consistency
    echo "\n6. Testing Data Consistency...\n";
    
    // Check students table data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 Students table has {$result['count']} records\n";
    
    // Check classes table data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM classes");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 Classes table has {$result['count']} records\n";
    
    // Check att_log table data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM att_log");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   📊 Att_log table has {$result['count']} records\n";
    
    // Test 7: Check for common schema issues
    echo "\n7. Checking for Common Schema Issues...\n";
    
    // Check for duplicate student_ids
    $stmt = $pdo->query("SELECT student_id, COUNT(*) as count FROM students GROUP BY student_id HAVING count > 1");
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($duplicates)) {
        echo "   ✅ No duplicate student_ids found\n";
    } else {
        echo "   ❌ Found " . count($duplicates) . " duplicate student_ids\n";
    }
    
    // Check for NULL required fields
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students WHERE student_id IS NULL OR student_id = ''");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] == 0) {
        echo "   ✅ No NULL/empty student_ids found\n";
    } else {
        echo "   ❌ Found {$result['count']} students with NULL/empty student_id\n";
    }
    
    // Test 8: Test join compatibility
    echo "\n8. Testing Join Compatibility...\n";
    
    // Test the join used in HomeController
    try {
        $stmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM att_log 
            LEFT JOIN students ON students.student_id = att_log.student_id 
            WHERE DATE(att_log.scan_date) = CURDATE()
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   ✅ Att_log -> Students join works (found {$result['count']} today's records)\n";
    } catch (Exception $e) {
        echo "   ❌ Att_log -> Students join failed: " . $e->getMessage() . "\n";
    }
    
    // Test students -> classes join
    try {
        $stmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM students s 
            LEFT JOIN classes c ON s.class_id = c.id 
            WHERE s.status = 'Active'
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   ✅ Students -> Classes join works (found {$result['count']} active students)\n";
    } catch (Exception $e) {
        echo "   ❌ Students -> Classes join failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== SCHEMA VALIDATION COMPLETED ===\n";
    echo "✅ Database schema validation finished successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n";
}
?>
