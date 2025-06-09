<?php
/**
 * Create Workflow Management Tables
 */

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'studentfinger';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully<br>";
    
    // Workflow Table
    $sql = "CREATE TABLE IF NOT EXISTS `notification_workflows` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `workflow_name` VARCHAR(100) NOT NULL,
        `workflow_type` ENUM('session_notification', 'attendance_alert', 'custom_message', 'scheduled_reminder') NOT NULL DEFAULT 'session_notification',
        `trigger_event` ENUM('session_start', 'session_break', 'session_resume', 'session_finish', 'student_absent', 'manual', 'scheduled') NOT NULL,
        `conditions` JSON NULL COMMENT 'Conditions for workflow execution',
        `actions` JSON NOT NULL COMMENT 'Actions to execute',
        `notification_settings` JSON NULL COMMENT 'Notification specific settings',
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `priority` INT(11) NOT NULL DEFAULT 1,
        `description` TEXT NULL,
        `created_by` INT(11) UNSIGNED NULL,
        `last_executed` DATETIME NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_workflow_type` (`workflow_type`),
        KEY `idx_trigger_event` (`trigger_event`),
        KEY `idx_is_active` (`is_active`),
        KEY `idx_priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql);
    echo "Created notification_workflows table<br>";
    
    // Workflow Execution Log Table
    $sql2 = "CREATE TABLE IF NOT EXISTS `workflow_execution_logs` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `workflow_id` INT(11) UNSIGNED NOT NULL,
        `session_id` INT(11) UNSIGNED NULL,
        `trigger_context` JSON NULL COMMENT 'Context data when workflow was triggered',
        `execution_result` JSON NULL COMMENT 'Result of workflow execution',
        `status` ENUM('success', 'failed', 'partial') NOT NULL,
        `error_message` TEXT NULL,
        `execution_time_ms` INT(11) NULL,
        `executed_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_workflow_id` (`workflow_id`),
        KEY `idx_session_id` (`session_id`),
        KEY `idx_status` (`status`),
        KEY `idx_executed_at` (`executed_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql2);
    echo "Created workflow_execution_logs table<br>";
    
    // Insert default workflows
    $defaultWorkflows = [
        [
            'Auto Notify on Session Start',
            'session_notification',
            'session_start',
            '[]',
            '[{"type":"send_notification","event_type":"session_start"},{"type":"log_event","event_type":"session_start","message":"Session start notification sent"}]',
            '{"auto_send":true,"delay_seconds":0,"retry_attempts":3}',
            1,
            1,
            'Automatically send notification when session starts'
        ],
        [
            'Auto Notify on Session Finish',
            'session_notification',
            'session_finish',
            '[]',
            '[{"type":"send_notification","event_type":"session_finish"},{"type":"update_session","updates":{"notification_sent":1}}]',
            '{"auto_send":true,"delay_seconds":0,"retry_attempts":3}',
            1,
            1,
            'Automatically send notification when session finishes'
        ],
        [
            'Break Notification (Optional)',
            'session_notification',
            'session_break',
            '[{"field":"session_duration","operator":"greater_than","value":60}]',
            '[{"type":"send_notification","event_type":"session_break"}]',
            '{"auto_send":false,"delay_seconds":0,"retry_attempts":2}',
            0,
            2,
            'Send notification when session break occurs (for sessions longer than 60 minutes)'
        ],
        [
            'Resume Notification (Optional)',
            'session_notification',
            'session_resume',
            '[]',
            '[{"type":"send_notification","event_type":"session_resume"}]',
            '{"auto_send":false,"delay_seconds":0,"retry_attempts":2}',
            0,
            3,
            'Send notification when session resumes after break'
        ],
        [
            'Attendance Alert',
            'attendance_alert',
            'student_absent',
            '[{"field":"absence_count","operator":"greater_than","value":2}]',
            '[{"type":"send_notification","event_type":"attendance_alert"},{"type":"log_event","event_type":"attendance_alert","message":"Attendance alert sent"}]',
            '{"auto_send":true,"delay_seconds":300,"retry_attempts":3}',
            1,
            4,
            'Send alert when student has been absent for multiple sessions'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO notification_workflows (workflow_name, workflow_type, trigger_event, conditions, actions, notification_settings, is_active, priority, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($defaultWorkflows as $workflow) {
        $stmt->execute($workflow);
    }
    
    echo "Inserted default workflows<br>";
    
    // Business Process Rules Table
    $sql3 = "CREATE TABLE IF NOT EXISTS `business_process_rules` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `rule_name` VARCHAR(100) NOT NULL,
        `rule_type` ENUM('validation', 'automation', 'notification', 'escalation') NOT NULL,
        `entity_type` ENUM('session', 'student', 'contact', 'notification') NOT NULL,
        `conditions` JSON NOT NULL COMMENT 'Rule conditions',
        `actions` JSON NOT NULL COMMENT 'Actions to take when rule matches',
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `priority` INT(11) NOT NULL DEFAULT 1,
        `description` TEXT NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        `deleted_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `idx_rule_type` (`rule_type`),
        KEY `idx_entity_type` (`entity_type`),
        KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $pdo->exec($sql3);
    echo "Created business_process_rules table<br>";
    
    // Insert default business rules
    $defaultRules = [
        [
            'Validate Session Duration',
            'validation',
            'session',
            '[{"field":"duration_minutes","operator":"greater_than","value":15},{"field":"duration_minutes","operator":"less_than","value":480}]',
            '[{"type":"reject","message":"Session duration must be between 15 minutes and 8 hours"}]',
            1,
            1,
            'Validate that session duration is within acceptable limits'
        ],
        [
            'Require Parent Contact',
            'validation',
            'student',
            '[{"field":"parent_contacts_count","operator":"greater_than","value":0}]',
            '[{"type":"reject","message":"Student must have at least one parent contact"}]',
            1,
            1,
            'Ensure students have parent contact information before notifications'
        ],
        [
            'Auto-activate Primary Contact',
            'automation',
            'contact',
            '[{"field":"is_primary","operator":"equals","value":true}]',
            '[{"type":"update","field":"receive_notifications","value":true}]',
            1,
            1,
            'Automatically enable notifications for primary contacts'
        ],
        [
            'Escalate Failed Notifications',
            'escalation',
            'notification',
            '[{"field":"retry_count","operator":"greater_than","value":3},{"field":"status","operator":"equals","value":"failed"}]',
            '[{"type":"notify_admin","message":"Multiple notification failures detected"},{"type":"log_event","level":"warning"}]',
            1,
            1,
            'Escalate to admin when notifications fail multiple times'
        ]
    ];
    
    $stmt2 = $pdo->prepare("INSERT IGNORE INTO business_process_rules (rule_name, rule_type, entity_type, conditions, actions, is_active, priority, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    
    foreach ($defaultRules as $rule) {
        $stmt2->execute($rule);
    }
    
    echo "Inserted default business rules<br>";
    
    echo "<h2>Workflow Management System Created Successfully!</h2>";
    echo "<p>Created:</p>";
    echo "<ul>";
    echo "<li>notification_workflows table with " . count($defaultWorkflows) . " default workflows</li>";
    echo "<li>workflow_execution_logs table for tracking executions</li>";
    echo "<li>business_process_rules table with " . count($defaultRules) . " default rules</li>";
    echo "</ul>";
    
    echo "<h3>Default Workflows:</h3>";
    echo "<ol>";
    foreach ($defaultWorkflows as $workflow) {
        echo "<li><strong>" . $workflow[0] . "</strong> - " . $workflow[8] . " (" . ($workflow[6] ? 'Active' : 'Inactive') . ")</li>";
    }
    echo "</ol>";
    
    echo "<a href='/classroom-notifications'>Go to Classroom Notifications Dashboard</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
