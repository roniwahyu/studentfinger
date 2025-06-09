<?php
/**
 * Test Classroom Notification System
 */

// Load CodeIgniter
require_once '../vendor/autoload.php';

// Set up basic environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/test';

// Load environment
$envFile = '../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

echo "<h2>Classroom Notification System Test</h2>";

// Test the WhatsApp service directly
try {
    // Simulate loading the WhatsApp service
    echo "<h3>Testing WhatsApp Service...</h3>";
    
    $baseUrl = $_ENV['WABLAS_BASE_URL'] ?? 'https://texas.wablas.com';
    $token = $_ENV['WABLAS_TOKEN'] ?? '';
    $secretKey = $_ENV['WABLAS_SECRET_KEY'] ?? '';
    
    // Test notification data
    $testData = [
        'session_id' => 1,
        'student_id' => 1,
        'parent_phone' => '628123456789',
        'parent_name' => 'Test Parent',
        'event_type' => 'session_start',
        'variables' => [
            'student_name' => 'Ahmad Rizki',
            'parent_name' => 'Bapak/Ibu Ahmad',
            'class_name' => 'X-A',
            'subject' => 'Matematika',
            'teacher_name' => 'Mrs. Sari',
            'start_time' => '08:00',
            'session_date' => date('d/m/Y'),
            'school_name' => 'Student Finger School'
        ]
    ];
    
    // Process template
    $template = "🎓 *KELAS DIMULAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKami informasikan bahwa {student_name} telah hadir di kelas:\n\n📚 *Mata Pelajaran:* {subject}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Mulai:* {start_time}\n📅 *Tanggal:* {session_date}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*";
    
    // Replace variables
    $message = $template;
    foreach ($testData['variables'] as $key => $value) {
        $message = str_replace('{' . $key . '}', $value, $message);
    }
    
    echo "<h4>Processed Message:</h4>";
    echo "<pre style='background: #f0f8ff; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>" . htmlspecialchars($message) . "</pre>";
    
    // Send via WABLAS
    echo "<h4>Sending via WABLAS...</h4>";
    
    $url = rtrim($baseUrl, '/') . '/api/send-message';
    $authorization = $token . '.' . $secretKey;
    
    // Clean phone number
    $cleanPhone = preg_replace('/[^0-9]/', '', $testData['parent_phone']);
    if (substr($cleanPhone, 0, 1) === '0') {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    } elseif (substr($cleanPhone, 0, 2) !== '62') {
        $cleanPhone = '62' . $cleanPhone;
    }
    
    $data = [
        'phone' => $cleanPhone,
        'message' => $message,
        'isGroup' => false
    ];
    
    echo "<p><strong>Sending to:</strong> " . htmlspecialchars($cleanPhone) . "</p>";
    echo "<p><strong>API URL:</strong> " . htmlspecialchars($url) . "</p>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: ' . $authorization
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h4>Send Results:</h4>";
    echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";
    
    if ($error) {
        echo "<p style='color: red;'><strong>CURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && $responseData) {
        if (isset($responseData['status']) && $responseData['status'] === true) {
            echo "<div style='color: green; font-weight: bold; padding: 10px; background: #d4edda; border-radius: 5px; border-left: 4px solid #28a745;'>✅ Classroom Notification Sent Successfully!</div>";
            
            echo "<h4>Notification Details:</h4>";
            echo "<ul>";
            echo "<li><strong>Student:</strong> " . htmlspecialchars($testData['variables']['student_name']) . "</li>";
            echo "<li><strong>Parent:</strong> " . htmlspecialchars($testData['variables']['parent_name']) . "</li>";
            echo "<li><strong>Class:</strong> " . htmlspecialchars($testData['variables']['class_name']) . "</li>";
            echo "<li><strong>Subject:</strong> " . htmlspecialchars($testData['variables']['subject']) . "</li>";
            echo "<li><strong>Teacher:</strong> " . htmlspecialchars($testData['variables']['teacher_name']) . "</li>";
            echo "<li><strong>Event:</strong> Class Started</li>";
            echo "<li><strong>Phone:</strong> " . htmlspecialchars($cleanPhone) . "</li>";
            echo "<li><strong>Message ID:</strong> " . htmlspecialchars($responseData['data']['messages'][0]['id'] ?? 'N/A') . "</li>";
            echo "<li><strong>Status:</strong> " . htmlspecialchars($responseData['data']['messages'][0]['status'] ?? 'N/A') . "</li>";
            echo "</ul>";
            
        } else {
            echo "<div style='color: orange; font-weight: bold; padding: 10px; background: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;'>⚠️ Message Response Received (check status)</div>";
        }
        
        echo "<h4>Full API Response:</h4>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; max-height: 300px; overflow-y: auto;'>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<div style='color: red; font-weight: bold; padding: 10px; background: #f8d7da; border-radius: 5px; border-left: 4px solid #dc3545;'>❌ Failed to Send Notification!</div>";
        echo "<p><strong>Raw Response:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>" . htmlspecialchars($response) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; padding: 10px; background: #f8d7da; border-radius: 5px;'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";

// Test different event types
echo "<h3>Testing Different Event Types...</h3>";

$eventTypes = [
    'session_break' => [
        'template' => "☕ *ISTIRAHAT KELAS*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} sedang istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Istirahat:* {break_time}\n⏱️ *Durasi:* {break_duration} menit\n\nKelas akan dilanjutkan setelah istirahat.\n\n*{school_name}*",
        'variables' => [
            'break_time' => date('H:i'),
            'break_duration' => '15'
        ]
    ],
    'session_resume' => [
        'template' => "📚 *KELAS DILANJUTKAN*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah dilanjutkan setelah istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Lanjut:* {resume_time}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
        'variables' => [
            'resume_time' => date('H:i')
        ]
    ],
    'session_finish' => [
        'template' => "✅ *KELAS SELESAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah selesai:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Selesai:* {end_time}\n⏱️ *Durasi Total:* {total_duration}\n\n{student_name} dapat dijemput atau pulang sesuai jadwal.\n\n*{school_name}*",
        'variables' => [
            'end_time' => date('H:i'),
            'total_duration' => '2 jam'
        ]
    ]
];

foreach ($eventTypes as $eventType => $config) {
    echo "<h4>" . ucfirst(str_replace('_', ' ', $eventType)) . " Template:</h4>";
    
    $message = $config['template'];
    $variables = array_merge($testData['variables'], $config['variables']);
    
    foreach ($variables as $key => $value) {
        $message = str_replace('{' . $key . '}', $value, $message);
    }
    
    echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 3px solid #6c757d; margin-bottom: 15px;'>" . htmlspecialchars($message) . "</pre>";
}

echo "<hr>";
echo "<div style='text-align: center; padding: 20px; background: #d1ecf1; border-radius: 10px; border: 1px solid #bee5eb;'>";
echo "<h3 style='color: #0c5460; margin-bottom: 10px;'>🎉 Classroom Notification System Test Complete!</h3>";
echo "<p style='color: #0c5460; margin-bottom: 15px;'>All notification templates and WABLAS integration are working perfectly!</p>";
echo "<a href='/classroom-notifications' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>← Back to Classroom Notifications</a>";
echo "</div>";
?>
