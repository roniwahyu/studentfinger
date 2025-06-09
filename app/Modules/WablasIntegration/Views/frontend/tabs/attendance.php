<!-- Attendance Integration Tab -->
<div class="row">
    <!-- Attendance Summary Cards -->
    <div class="col-lg-8 mb-4">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="wablas-card text-center">
                    <div class="card-body">
                        <div class="stat-number text-success"><?= $attendance_summary['today']['unique_users'] ?? 0 ?></div>
                        <div class="stat-label">Present Today</div>
                        <button class="btn btn-sm btn-outline-success mt-2" onclick="sendBulkNotification('present')">
                            <i class="fas fa-bell me-1"></i> Notify Parents
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="wablas-card text-center">
                    <div class="card-body">
                        <div class="stat-number text-danger"><?= count($absent_students ?? []) ?></div>
                        <div class="stat-label">Absent Today</div>
                        <button class="btn btn-sm btn-outline-danger mt-2" onclick="sendBulkNotification('absent')">
                            <i class="fas fa-exclamation-triangle me-1"></i> Send Alerts
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="wablas-card text-center">
                    <div class="card-body">
                        <div class="stat-number text-warning"><?= count($late_students ?? []) ?></div>
                        <div class="stat-label">Late Today</div>
                        <button class="btn btn-sm btn-outline-warning mt-2" onclick="sendBulkNotification('late')">
                            <i class="fas fa-clock me-1"></i> Notify Late
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="wablas-card text-center">
                    <div class="card-body">
                        <div class="stat-number text-info"><?= $attendance_summary['this_week']['total'] ?? 0 ?></div>
                        <div class="stat-label">This Week</div>
                        <button class="btn btn-sm btn-outline-info mt-2" onclick="generateWeeklyReport()">
                            <i class="fas fa-chart-bar me-1"></i> Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="wablas-card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-bolt me-2"></i>
                    Attendance Actions
                </h5>
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="sendDailyReport()">
                        <i class="fas fa-paper-plane me-2"></i>Send Daily Report
                    </button>
                    <button class="btn btn-warning" onclick="sendAbsentAlerts()">
                        <i class="fas fa-exclamation-triangle me-2"></i>Send Absent Alerts
                    </button>
                    <button class="btn btn-info" onclick="sendLateNotifications()">
                        <i class="fas fa-clock me-2"></i>Send Late Notifications
                    </button>
                    <button class="btn btn-primary" onclick="scheduleReminders()">
                        <i class="fas fa-calendar-plus me-2"></i>Schedule Reminders
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Absent Students List -->
<?php if (!empty($absent_students)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="wablas-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-times me-2 text-danger"></i>
                    Absent Students (<?= count($absent_students) ?>)
                </h5>
                <button class="btn btn-sm btn-danger" onclick="sendAllAbsentNotifications()">
                    <i class="fas fa-bell me-1"></i> Notify All Parents
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($absent_students as $student): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?= esc($student['name'] ?? $student['first_name'] . ' ' . $student['last_name']) ?></h6>
                                    <small class="text-muted">ID: <?= esc($student['student_id'] ?? $student['id']) ?></small>
                                </div>
                                <span class="badge bg-danger">Absent</span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    <?= esc($student['parent_phone'] ?? $student['phone_number'] ?? 'No phone') ?>
                                </small>
                            </div>
                            <div class="d-grid gap-1">
                                <button class="btn btn-sm btn-outline-danger" onclick="sendIndividualNotification(<?= $student['id'] ?>, 'absent')">
                                    <i class="fas fa-paper-plane me-1"></i> Send Alert
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick="callParent('<?= esc($student['parent_phone'] ?? $student['phone_number'] ?? '') ?>')">
                                    <i class="fas fa-phone me-1"></i> Call Parent
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Late Students List -->
<?php if (!empty($late_students)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="wablas-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2 text-warning"></i>
                    Late Students (<?= count($late_students) ?>)
                </h5>
                <button class="btn btn-sm btn-warning" onclick="sendAllLateNotifications()">
                    <i class="fas fa-bell me-1"></i> Notify All Parents
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($late_students as $student): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?= esc($student['name'] ?? $student['first_name'] . ' ' . $student['last_name']) ?></h6>
                                    <small class="text-muted">Arrived: <?= date('H:i A', strtotime($student['arrival_time'] ?? '')) ?></small>
                                </div>
                                <span class="badge bg-warning">Late</span>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>
                                    <?= esc($student['parent_phone'] ?? $student['phone_number'] ?? 'No phone') ?>
                                </small>
                            </div>
                            <div class="d-grid gap-1">
                                <button class="btn btn-sm btn-outline-warning" onclick="sendIndividualNotification(<?= $student['id'] ?>, 'late')">
                                    <i class="fas fa-paper-plane me-1"></i> Send Notice
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Notification Templates -->
<div class="row">
    <div class="col-12">
        <div class="wablas-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Notification Templates
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Absent Message Template</label>
                        <textarea class="form-control" id="absentTemplate" rows="3"><?= esc($notification_templates['absent'] ?? 'Dear Parent, your child {student_name} is absent today ({date}). Please contact school if this is unexpected.') ?></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Late Message Template</label>
                        <textarea class="form-control" id="lateTemplate" rows="3"><?= esc($notification_templates['late'] ?? 'Dear Parent, your child {student_name} arrived late today at {time}. Please ensure punctuality.') ?></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Early Leave Template</label>
                        <textarea class="form-control" id="earlyLeaveTemplate" rows="3"><?= esc($notification_templates['early_leave'] ?? 'Dear Parent, your child {student_name} left school early today at {time}.') ?></textarea>
                    </div>
                </div>
                <div class="text-end">
                    <button class="btn btn-primary" onclick="saveTemplates()">
                        <i class="fas fa-save me-2"></i>Save Templates
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Integration JavaScript -->
<script>
function sendBulkNotification(type) {
    Swal.fire({
        title: `Send ${type.charAt(0).toUpperCase() + type.slice(1)} Notifications`,
        text: `This will send notifications to all ${type} students' parents. Continue?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#25D366',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, send notifications!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Sending Notifications...',
                text: 'Please wait while we send the notifications.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send bulk notification
            fetch('<?= base_url('wablas-frontend/api/send-bulk-attendance-notification') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_type: type,
                    template: document.getElementById(type + 'Template')?.value
                })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire('Success!', `${data.sent_count} notifications sent successfully.`, 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.close();
                Swal.fire('Error!', 'Failed to send notifications.', 'error');
            });
        }
    });
}

function sendIndividualNotification(studentId, type) {
    const template = document.getElementById(type + 'Template')?.value;
    
    fetch('<?= base_url('wablas-frontend/api/send-attendance-notification') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            student_id: studentId,
            notification_type: type,
            custom_message: template
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Notification sent successfully!', 'success');
        } else {
            showToast('Failed to send notification: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error sending notification', 'error');
    });
}

function sendAllAbsentNotifications() {
    sendBulkNotification('absent');
}

function sendAllLateNotifications() {
    sendBulkNotification('late');
}

function sendDailyReport() {
    showToast('Sending daily attendance report...', 'info');
    // Implementation for daily report
}

function sendAbsentAlerts() {
    sendBulkNotification('absent');
}

function sendLateNotifications() {
    sendBulkNotification('late');
}

function scheduleReminders() {
    showToast('Opening reminder scheduler...', 'info');
    // Implementation for scheduling reminders
}

function generateWeeklyReport() {
    showToast('Generating weekly report...', 'info');
    // Implementation for weekly report
}

function callParent(phoneNumber) {
    if (phoneNumber) {
        window.open(`tel:${phoneNumber}`, '_self');
    } else {
        showToast('No phone number available', 'warning');
    }
}

function saveTemplates() {
    const templates = {
        absent: document.getElementById('absentTemplate').value,
        late: document.getElementById('lateTemplate').value,
        early_leave: document.getElementById('earlyLeaveTemplate').value
    };
    
    fetch('<?= base_url('wablas-frontend/api/save-templates') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(templates)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Templates saved successfully!', 'success');
        } else {
            showToast('Failed to save templates', 'error');
        }
    })
    .catch(error => {
        showToast('Error saving templates', 'error');
    });
}
</script>
