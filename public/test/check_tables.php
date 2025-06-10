<?php
$mysqli = new mysqli('localhost', 'root', '', 'studentfinger');

if ($mysqli->connect_error) {
    echo 'Connection failed: ' . $mysqli->connect_error . "\n";
    exit(1);
}

echo "Checking for WhatsApp tables...\n";
$result = $mysqli->query('SHOW TABLES LIKE "whatsapp_%"');

if ($result && $result->num_rows > 0) {
    echo "Found WhatsApp tables:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "No WhatsApp tables found\n";
}

echo "\nChecking for att_log table...\n";
$result = $mysqli->query('SHOW TABLES LIKE "att_log"');
if ($result && $result->num_rows > 0) {
    echo "att_log table exists\n";
} else {
    echo "att_log table not found\n";
}

$mysqli->close();
?>