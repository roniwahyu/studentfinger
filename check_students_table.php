<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=studentfinger', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Students Table Structure ===\n";
    $stmt = $pdo->query('DESCRIBE students');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
    
    echo "\n=== Checking for audit trail columns ===\n";
    $auditColumns = ['created_at', 'updated_at', 'deleted_at'];
    
    foreach($auditColumns as $column) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'studentfinger' AND TABLE_NAME = 'students' AND COLUMN_NAME = ?");
        $stmt->execute([$column]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($result['count'] > 0) {
            echo "✓ $column column exists\n";
        } else {
            echo "❌ $column column missing\n";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}