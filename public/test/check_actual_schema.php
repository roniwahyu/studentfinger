<?php
// Check actual database schema

echo "=== ACTUAL DATABASE SCHEMA CHECK ===\n\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=studentfinger', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database\n\n";
    
    // Check all tables
    echo "1. All Tables in Database:\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = [];
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
        echo "   - {$row[0]}\n";
    }
    
    // Check students table structure
    echo "\n2. Students Table Structure:\n";
    $stmt = $pdo->query("DESCRIBE students");
    echo "   Field | Type | Null | Key | Default | Extra\n";
    echo "   " . str_repeat("-", 60) . "\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
    }
    
    // Check classes table structure
    echo "\n3. Classes Table Structure:\n";
    $stmt = $pdo->query("DESCRIBE classes");
    echo "   Field | Type | Null | Key | Default | Extra\n";
    echo "   " . str_repeat("-", 60) . "\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Default']} | {$row['Extra']}\n";
    }
    
    // Check att_log table structure
    echo "\n4. Att_Log Table Structure:\n";
    $stmt = $pdo->query("DESCRIBE att_log");
    echo "   Field | Type | Null | Key | Default | Extra\n";
    echo "   " . str_repeat("-", 60) . "\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
    }
    
    // Check sections table if it exists
    if (in_array('sections', $tables)) {
        echo "\n5. Sections Table Structure:\n";
        $stmt = $pdo->query("DESCRIBE sections");
        echo "   Field | Type | Null | Key | Default | Extra\n";
        echo "   " . str_repeat("-", 60) . "\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   {$row['Field']} | {$row['Type']} | {$row['Null']} | {$row['Key']} | {$row['Default']} | {$row['Extra']}\n";
        }
    }
    
    // Sample data check
    echo "\n6. Sample Data Check:\n";
    
    // Students sample
    $stmt = $pdo->query("SELECT * FROM students LIMIT 3");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($students)) {
        echo "   Students sample data:\n";
        foreach ($students as $student) {
            echo "   - Student ID: " . ($student['student_id'] ?? 'N/A') . "\n";
            echo "     Name: " . ($student['firstname'] ?? $student['name'] ?? 'N/A') . " " . ($student['lastname'] ?? '') . "\n";
            echo "     Status: " . ($student['status'] ?? 'N/A') . "\n";
            echo "\n";
        }
    } else {
        echo "   No students data found\n";
    }
    
    // Classes sample
    $stmt = $pdo->query("SELECT * FROM classes LIMIT 3");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($classes)) {
        echo "   Classes sample data:\n";
        foreach ($classes as $class) {
            echo "   - ID: {$class['id']}, Class: {$class['class']}\n";
        }
    } else {
        echo "   No classes data found\n";
    }
    
    echo "\n=== SCHEMA CHECK COMPLETED ===\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
?>
