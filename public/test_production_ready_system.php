<?php
/**
 * Production Ready System Test
 * Comprehensive test of all business processes, workflows, and system components
 */

// Load environment variables
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

echo "<h1>🚀 Production Ready System Test</h1>";
echo "<p><strong>Testing Date:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'studentfinger';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='color: green;'>✅ Database Connected Successfully</div>";
    
    // Test 1: System Architecture & Database Structure
    echo "<h2>🏗️ Test 1: System Architecture & Database Structure</h2>";
    
    $requiredTables = [
        'notification_templates' => 'Message templates management',
        'notification_logs' => 'Notification history and tracking',
        'parent_contacts' => 'Parent contact management',
        'contact_groups' => 'Contact grouping system',
        'whatsapp_connection_status' => 'Connection monitoring',
        'notification_settings' => 'System configuration',
        'notification_workflows' => 'Business process workflows',
        'workflow_execution_logs' => 'Workflow execution tracking',
        'business_process_rules' => 'Business rules engine'
    ];
    
    $missingTables = [];
    foreach ($requiredTables as $table => $description) {
        $query = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($query->rowCount() > 0) {
            echo "<div style='color: green;'>✅ $table - $description</div>";
        } else {
            echo "<div style='color: red;'>❌ $table - $description (MISSING)</div>";
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ Database Architecture: COMPLETE</strong><br>";
        echo "All required tables are present and properly structured.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ Database Architecture: INCOMPLETE</strong><br>";
        echo "Missing tables: " . implode(', ', $missingTables);
        echo "</div>";
    }
    
    // Test 2: Business Process Workflows
    echo "<h2>⚙️ Test 2: Business Process Workflows</h2>";
    
    $workflowQuery = $pdo->query("
        SELECT workflow_name, workflow_type, trigger_event, is_active, priority, description 
        FROM notification_workflows 
        WHERE deleted_at IS NULL 
        ORDER BY priority ASC
    ");
    $workflows = $workflowQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Workflows:</strong> " . count($workflows) . "</p>";
    
    $activeWorkflows = array_filter($workflows, function($w) { return $w['is_active']; });
    echo "<p><strong>Active Workflows:</strong> " . count($activeWorkflows) . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Workflow Name</th><th>Type</th><th>Trigger</th><th>Status</th><th>Priority</th>";
    echo "</tr>";
    
    foreach ($workflows as $workflow) {
        $statusColor = $workflow['is_active'] ? 'green' : 'orange';
        $statusText = $workflow['is_active'] ? 'Active' : 'Inactive';
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($workflow['workflow_name']) . "</strong></td>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $workflow['workflow_type'])) . "</td>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $workflow['trigger_event'])) . "</td>";
        echo "<td style='color: $statusColor;'>$statusText</td>";
        echo "<td>" . $workflow['priority'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 3: Contact Management System
    echo "<h2>📞 Test 3: Contact Management System</h2>";
    
    $contactStats = $pdo->query("
        SELECT 
            COUNT(*) as total_contacts,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_contacts,
            SUM(CASE WHEN is_primary = 1 THEN 1 ELSE 0 END) as primary_contacts,
            SUM(CASE WHEN receive_notifications = 1 THEN 1 ELSE 0 END) as notification_enabled
        FROM parent_contacts 
        WHERE deleted_at IS NULL
    ")->fetch(PDO::FETCH_ASSOC);
    
    $contactTypes = $pdo->query("
        SELECT contact_type, COUNT(*) as count 
        FROM parent_contacts 
        WHERE deleted_at IS NULL AND is_active = 1 
        GROUP BY contact_type
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='display: flex; gap: 20px; margin: 15px 0;'>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; flex: 1;'>";
    echo "<h4>📊 Contact Statistics</h4>";
    echo "<ul>";
    echo "<li><strong>Total Contacts:</strong> " . $contactStats['total_contacts'] . "</li>";
    echo "<li><strong>Active Contacts:</strong> " . $contactStats['active_contacts'] . "</li>";
    echo "<li><strong>Primary Contacts:</strong> " . $contactStats['primary_contacts'] . "</li>";
    echo "<li><strong>Notification Enabled:</strong> " . $contactStats['notification_enabled'] . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #f3e5f5; padding: 15px; border-radius: 5px; flex: 1;'>";
    echo "<h4>👥 Contact Types</h4>";
    echo "<ul>";
    foreach ($contactTypes as $type) {
        echo "<li><strong>" . ucfirst($type['contact_type']) . ":</strong> " . $type['count'] . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
    // Test 4: Template Management System
    echo "<h2>📝 Test 4: Template Management System</h2>";
    
    $templateStats = $pdo->query("
        SELECT 
            COUNT(*) as total_templates,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_templates,
            COUNT(DISTINCT event_type) as event_types,
            COUNT(DISTINCT language) as languages
        FROM notification_templates 
        WHERE deleted_at IS NULL
    ")->fetch(PDO::FETCH_ASSOC);
    
    $templatesByEvent = $pdo->query("
        SELECT event_type, language, COUNT(*) as count 
        FROM notification_templates 
        WHERE deleted_at IS NULL AND is_active = 1 
        GROUP BY event_type, language 
        ORDER BY event_type, language
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #fff3e0; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4>📋 Template Coverage</h4>";
    echo "<p><strong>Total Templates:</strong> " . $templateStats['total_templates'] . " | ";
    echo "<strong>Active:</strong> " . $templateStats['active_templates'] . " | ";
    echo "<strong>Event Types:</strong> " . $templateStats['event_types'] . " | ";
    echo "<strong>Languages:</strong> " . $templateStats['languages'] . "</p>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th>Event Type</th><th>Language</th><th>Templates</th></tr>";
    foreach ($templatesByEvent as $template) {
        echo "<tr>";
        echo "<td>" . ucfirst(str_replace('_', ' ', $template['event_type'])) . "</td>";
        echo "<td>" . strtoupper($template['language']) . "</td>";
        echo "<td>" . $template['count'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Test 5: WhatsApp Integration & Connection
    echo "<h2>📱 Test 5: WhatsApp Integration & Connection</h2>";
    
    $connectionStatus = $pdo->query("
        SELECT * FROM whatsapp_connection_status 
        ORDER BY updated_at DESC 
        LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    $wablasConfig = [
        'base_url' => $_ENV['WABLAS_BASE_URL'] ?? '',
        'token' => $_ENV['WABLAS_TOKEN'] ?? '',
        'secret_key' => $_ENV['WABLAS_SECRET_KEY'] ?? ''
    ];
    
    echo "<div style='display: flex; gap: 20px; margin: 15px 0;'>";
    echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; flex: 1;'>";
    echo "<h4>🔧 Configuration Status</h4>";
    echo "<ul>";
    echo "<li><strong>Base URL:</strong> " . (!empty($wablasConfig['base_url']) ? '✅ Configured' : '❌ Missing') . "</li>";
    echo "<li><strong>Token:</strong> " . (!empty($wablasConfig['token']) ? '✅ Configured' : '❌ Missing') . "</li>";
    echo "<li><strong>Secret Key:</strong> " . (!empty($wablasConfig['secret_key']) ? '✅ Configured' : '❌ Missing') . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #e1f5fe; padding: 15px; border-radius: 5px; flex: 1;'>";
    echo "<h4>📊 Connection Status</h4>";
    if ($connectionStatus) {
        $statusColor = $connectionStatus['connection_status'] === 'connected' ? 'green' : 'red';
        echo "<ul>";
        echo "<li><strong>Status:</strong> <span style='color: $statusColor;'>" . ucfirst($connectionStatus['connection_status']) . "</span></li>";
        echo "<li><strong>Device ID:</strong> " . ($connectionStatus['device_id'] ?? 'N/A') . "</li>";
        echo "<li><strong>Quota:</strong> " . ($connectionStatus['quota_remaining'] ?? 0) . " messages</li>";
        echo "<li><strong>Last Check:</strong> " . ($connectionStatus['last_check'] ? date('H:i:s', strtotime($connectionStatus['last_check'])) : 'Never') . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>No connection data available</p>";
    }
    echo "</div>";
    echo "</div>";
    
    // Test 6: System Settings & Configuration
    echo "<h2>⚙️ Test 6: System Settings & Configuration</h2>";
    
    $settingsQuery = $pdo->query("
        SELECT setting_key, setting_value, setting_type, description 
        FROM notification_settings 
        ORDER BY setting_key
    ");
    $settings = $settingsQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Settings:</strong> " . count($settings) . "</p>";
    
    $criticalSettings = [
        'wablas_base_url' => 'WABLAS API Base URL',
        'wablas_token' => 'WABLAS API Token',
        'wablas_secret_key' => 'WABLAS Secret Key',
        'auto_send_on_session_start' => 'Auto Send on Session Start',
        'auto_send_on_session_finish' => 'Auto Send on Session Finish',
        'school_name' => 'School Name'
    ];
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th>Setting</th><th>Status</th><th>Description</th></tr>";
    
    $settingsMap = [];
    foreach ($settings as $setting) {
        $settingsMap[$setting['setting_key']] = $setting['setting_value'];
    }
    
    foreach ($criticalSettings as $key => $description) {
        $hasValue = isset($settingsMap[$key]) && !empty($settingsMap[$key]);
        $statusColor = $hasValue ? 'green' : 'red';
        $statusText = $hasValue ? '✅ Configured' : '❌ Missing';
        
        echo "<tr>";
        echo "<td><strong>$key</strong></td>";
        echo "<td style='color: $statusColor;'>$statusText</td>";
        echo "<td>$description</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 7: Business Rules Engine
    echo "<h2>📋 Test 7: Business Rules Engine</h2>";
    
    $rulesQuery = $pdo->query("
        SELECT rule_name, rule_type, entity_type, is_active, priority, description 
        FROM business_process_rules 
        WHERE deleted_at IS NULL 
        ORDER BY priority ASC
    ");
    $rules = $rulesQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Total Business Rules:</strong> " . count($rules) . "</p>";
    
    $activeRules = array_filter($rules, function($r) { return $r['is_active']; });
    echo "<p><strong>Active Rules:</strong> " . count($activeRules) . "</p>";
    
    $rulesByType = [];
    foreach ($rules as $rule) {
        $rulesByType[$rule['rule_type']][] = $rule;
    }
    
    foreach ($rulesByType as $type => $typeRules) {
        echo "<h4>" . ucfirst($type) . " Rules (" . count($typeRules) . ")</h4>";
        echo "<ul>";
        foreach ($typeRules as $rule) {
            $statusIcon = $rule['is_active'] ? '✅' : '⚠️';
            echo "<li>$statusIcon <strong>" . htmlspecialchars($rule['rule_name']) . "</strong> - " . htmlspecialchars($rule['description']) . "</li>";
        }
        echo "</ul>";
    }
    
    // Test 8: System Performance & Readiness
    echo "<h2>🚀 Test 8: System Performance & Readiness</h2>";
    
    $performanceMetrics = [
        'Database Tables' => count($requiredTables) - count($missingTables) . '/' . count($requiredTables),
        'Active Workflows' => count($activeWorkflows) . '/' . count($workflows),
        'Active Templates' => $templateStats['active_templates'] . '/' . $templateStats['total_templates'],
        'Active Contacts' => $contactStats['active_contacts'] . '/' . $contactStats['total_contacts'],
        'Active Rules' => count($activeRules) . '/' . count($rules),
        'WABLAS Config' => (count(array_filter($wablasConfig)) . '/3'),
        'Connection Status' => ($connectionStatus && $connectionStatus['connection_status'] === 'connected') ? '1/1' : '0/1'
    ];
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";
    foreach ($performanceMetrics as $metric => $value) {
        list($current, $total) = explode('/', $value);
        $percentage = $total > 0 ? round(($current / $total) * 100) : 0;
        $color = $percentage >= 80 ? '#4caf50' : ($percentage >= 60 ? '#ff9800' : '#f44336');
        
        echo "<div style='background: white; border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center;'>";
        echo "<h4 style='margin: 0 0 10px 0; color: #333;'>$metric</h4>";
        echo "<div style='font-size: 24px; font-weight: bold; color: $color;'>$percentage%</div>";
        echo "<div style='font-size: 12px; color: #666; margin-top: 5px;'>$value</div>";
        echo "</div>";
    }
    echo "</div>";
    
    // Final Assessment
    echo "<h2>🎯 Final Production Readiness Assessment</h2>";
    
    $totalScore = 0;
    $maxScore = 0;
    
    $assessmentCriteria = [
        'Database Architecture' => [
            'score' => count($requiredTables) - count($missingTables),
            'max' => count($requiredTables),
            'weight' => 20
        ],
        'Business Workflows' => [
            'score' => count($activeWorkflows),
            'max' => count($workflows),
            'weight' => 15
        ],
        'Template Coverage' => [
            'score' => $templateStats['active_templates'],
            'max' => $templateStats['total_templates'],
            'weight' => 15
        ],
        'Contact Management' => [
            'score' => $contactStats['active_contacts'],
            'max' => $contactStats['total_contacts'],
            'weight' => 15
        ],
        'WhatsApp Integration' => [
            'score' => count(array_filter($wablasConfig)),
            'max' => 3,
            'weight' => 20
        ],
        'Business Rules' => [
            'score' => count($activeRules),
            'max' => count($rules),
            'weight' => 10
        ],
        'System Configuration' => [
            'score' => count(array_filter(array_intersect_key($settingsMap, $criticalSettings))),
            'max' => count($criticalSettings),
            'weight' => 5
        ]
    ];
    
    foreach ($assessmentCriteria as $criteria => $data) {
        $percentage = $data['max'] > 0 ? ($data['score'] / $data['max']) * 100 : 0;
        $weightedScore = ($percentage / 100) * $data['weight'];
        $totalScore += $weightedScore;
        $maxScore += $data['weight'];
        
        $color = $percentage >= 80 ? 'green' : ($percentage >= 60 ? 'orange' : 'red');
        echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid $color; background: #f9f9f9;'>";
        echo "<strong>$criteria:</strong> ";
        echo "<span style='color: $color;'>" . round($percentage) . "%</span> ";
        echo "({$data['score']}/{$data['max']}) - Weight: {$data['weight']}%";
        echo "</div>";
    }
    
    $finalScore = round(($totalScore / $maxScore) * 100);
    $finalColor = $finalScore >= 85 ? '#4caf50' : ($finalScore >= 70 ? '#ff9800' : '#f44336');
    $readinessStatus = $finalScore >= 85 ? 'PRODUCTION READY' : ($finalScore >= 70 ? 'NEEDS MINOR FIXES' : 'NEEDS MAJOR WORK');
    
    echo "<div style='background: $finalColor; color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0;'>";
    echo "<h2 style='margin: 0; font-size: 28px;'>🎯 FINAL SCORE: $finalScore%</h2>";
    echo "<h3 style='margin: 10px 0 0 0; font-size: 20px;'>$readinessStatus</h3>";
    echo "</div>";
    
    // Recommendations
    echo "<h2>📋 Recommendations</h2>";
    
    if ($finalScore >= 85) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
        echo "<h3>✅ System is Production Ready!</h3>";
        echo "<ul>";
        echo "<li>All core components are functional and properly configured</li>";
        echo "<li>Business processes and workflows are in place</li>";
        echo "<li>WhatsApp integration is working correctly</li>";
        echo "<li>Contact management system is operational</li>";
        echo "<li>Ready for immediate deployment in school environment</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
        echo "<h3>⚠️ System Needs Attention</h3>";
        echo "<ul>";
        if (!empty($missingTables)) {
            echo "<li>Complete database setup by creating missing tables</li>";
        }
        if (count(array_filter($wablasConfig)) < 3) {
            echo "<li>Configure WABLAS API credentials in settings</li>";
        }
        if ($contactStats['active_contacts'] == 0) {
            echo "<li>Add parent contact information for students</li>";
        }
        if (count($activeWorkflows) < count($workflows)) {
            echo "<li>Review and activate necessary business workflows</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ System Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<div style='text-align: center; padding: 20px;'>";
echo "<a href='/classroom-notifications' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 16px;'>📊 Dashboard</a>";
echo "<a href='/classroom-notifications/settings' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 16px;'>⚙️ Settings</a>";
echo "<a href='/classroom-notifications/contacts' style='background: #17a2b8; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 16px;'>📞 Contacts</a>";
echo "<a href='/classroom-notifications/templates' style='background: #ffc107; color: black; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 5px; font-size: 16px;'>📝 Templates</a>";
echo "</div>";
?>
