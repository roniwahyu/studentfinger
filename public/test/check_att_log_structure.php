<?php

echo "=== CHECKING ATT_LOG TABLE STRUCTURE ===\n\n";

// Check fin_pro.att_log structure
echo "1. fin_pro.att_log structure:\n";
try {
    $finProDb = new mysqli('localhost', 'root', '', 'fin_pro');
    
    if ($finProDb->connect_error) {
        echo "   ❌ fin_pro database connection failed: " . $finProDb->connect_error . "\n";
    } else {
        $result = $finProDb->query('DESCRIBE att_log');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "   - {$row['Field']} ({$row['Type']})\n";
            }
        } else {
            echo "   ❌ Failed to describe att_log table\n";
        }
        
        // Show sample data
        echo "\n   Sample data (first 5 records):\n";
        $result = $finProDb->query('SELECT * FROM att_log LIMIT 5');
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "   - ";
                foreach ($row as $key => $value) {
                    echo "$key: $value, ";
                }
                echo "\n";
            }
        } else {
            echo "   No data found\n";
        }
        
        $finProDb->close();
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check studentfinger.att_log structure
echo "2. studentfinger.att_log structure:\n";
try {
    $studentDb = new mysqli('localhost', 'root', '', 'studentfinger');
    
    if ($studentDb->connect_error) {
        echo "   ❌ studentfinger database connection failed: " . $studentDb->connect_error . "\n";
    } else {
        $result = $studentDb->query('DESCRIBE att_log');
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                echo "   - {$row['Field']} ({$row['Type']})\n";
            }
        } else {
            echo "   ❌ Failed to describe att_log table\n";
        }
        
        // Show sample data
        echo "\n   Sample data (first 5 records):\n";
        $result = $studentDb->query('SELECT * FROM att_log LIMIT 5');
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "   - ";
                foreach ($row as $key => $value) {
                    echo "$key: $value, ";
                }
                echo "\n";
            }
        } else {
            echo "   No data found\n";
        }
        
        $studentDb->close();
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== STRUCTURE CHECK COMPLETED ===\n";

?>