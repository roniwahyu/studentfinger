<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-graduation-cap text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" onclick="syncClassroomData()">
                <i class="fas fa-sync"></i> Sync Classroom Data
            </button>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Integration Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link"></i> Integration Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success" role="alert">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="alert-heading">
                                    <i class="fas fa-check-circle"></i> Classroom Integration Active
                                </h5>
                                <p class="mb-0">
                                    WhatsApp notifications are successfully integrated with the classroom management system.
                                    <br><strong>Last Sync:</strong> <?= date('Y-m-d H:i:s') ?>
                                    <br><strong>Active Sessions:</strong> <?= count($active_sessions) ?>
                                    <br><strong>Total Students:</strong> <?= $integration_stats['total_students'] ?? 0 ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-graduation-cap fa-4x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Integration Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Active Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($active_sessions) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Connected Parents
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $integration_stats['connected_parents'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Today's Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $integration_stats['today_notifications'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Notification Success Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($integration_stats['success_rate'] ?? 0, 1) ?>%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Sessions -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chalkboard-teacher"></i> Active Class Sessions
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($active_sessions)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Session</th>
                                        <th>Subject</th>
                                        <th>Teacher</th>
                                        <th>Students</th>
                                        <th>Start Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_sessions as $session): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($session['session_name']) ?></div>
                                                <small class="text-muted">ID: <?= $session['id'] ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($session['subject']) ?></span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($session['teacher_name'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?= $session['student_count'] ?? 0 ?></span>
                                            </td>
                                            <td>
                                                <?= date('H:i', strtotime($session['start_time'])) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'scheduled' => 'warning',
                                                    'completed' => 'secondary'
                                                ];
                                                $statusColor = $statusColors[$session['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $statusColor ?>">
                                                    <?= ucfirst($session['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewSessionDetails(<?= $session['id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="sendSessionNotification(<?= $session['id'] ?>)" title="Send Notification">
                                                    <i class="fab fa-whatsapp"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chalkboard-teacher fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">No active sessions</h5>
                            <p class="text-muted">Active class sessions will appear here when they are running.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Integration Settings -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-cogs"></i> Integration Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form id="integrationSettingsForm">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="auto_session_start" name="auto_session_start" 
                                   <?= ($integration_settings['auto_session_start'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="auto_session_start">
                                Auto-notify on session start
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="auto_session_end" name="auto_session_end" 
                                   <?= ($integration_settings['auto_session_end'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="auto_session_end">
                                Auto-notify on session end
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="attendance_alerts" name="attendance_alerts" 
                                   <?= ($integration_settings['attendance_alerts'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="attendance_alerts">
                                Send attendance alerts
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="notification_delay">Notification Delay (minutes)</label>
                            <input type="number" class="form-control" id="notification_delay" name="notification_delay" 
                                   value="<?= $integration_settings['notification_delay'] ?? 0 ?>" min="0" max="60">
                            <small class="form-text text-muted">Delay before sending notifications</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-bell"></i> Recent Notifications
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_notifications)): ?>
                        <?php foreach ($recent_notifications as $notification): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted"><?= date('H:i', strtotime($notification['created_at'])) ?></small>
                                        <p class="mb-1"><?= htmlspecialchars($notification['parent_name']) ?></p>
                                        <small class="text-info"><?= ucfirst(str_replace('_', ' ', $notification['event_type'])) ?></small>
                                    </div>
                                    <span class="badge badge-<?= $notification['status'] === 'sent' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($notification['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="<?= base_url('wablas-frontend/messages') ?>" class="btn btn-outline-primary btn-sm">
                                View All Messages
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-bell fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted">No recent notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Student-Parent Mapping -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link"></i> Student-Parent Mapping Status
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($student_parent_mapping)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="mappingTable">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Parent/Guardian</th>
                                        <th>Phone Number</th>
                                        <th>WhatsApp Status</th>
                                        <th>Last Notification</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($student_parent_mapping as $mapping): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($mapping['student_name']) ?></div>
                                                <small class="text-muted">ID: <?= $mapping['student_id'] ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary"><?= htmlspecialchars($mapping['class_name'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($mapping['parent_name']) ?>
                                                <br><small class="text-muted"><?= ucfirst($mapping['contact_type']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($mapping['phone_number']) ?>
                                            </td>
                                            <td>
                                                <?php if ($mapping['whatsapp_verified']): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fab fa-whatsapp"></i> Verified
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-question"></i> Unverified
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($mapping['last_notification']): ?>
                                                    <?= date('Y-m-d H:i', strtotime($mapping['last_notification'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editMapping(<?= $mapping['id'] ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="testNotification('<?= $mapping['phone_number'] ?>')" title="Test Notification">
                                                    <i class="fab fa-whatsapp"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-link fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">No student-parent mappings found</h5>
                            <p class="text-muted">Student-parent relationships will appear here once they are configured.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Sync classroom data
function syncClassroomData() {
    const btn = $('button:contains("Sync Classroom Data")');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Syncing...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/classroom-integration/sync') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Classroom data synced successfully!\n\nSynced: ' + response.synced + ' records');
                location.reload();
            } else {
                alert('❌ Failed to sync classroom data: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error syncing classroom data. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

// View session details
function viewSessionDetails(sessionId) {
    // Implementation for viewing session details
    window.location.href = '<?= base_url('classroom/sessions/view') ?>/' + sessionId;
}

// Send session notification
function sendSessionNotification(sessionId) {
    if (confirm('Send notification to all parents for this session?')) {
        $.ajax({
            url: '<?= base_url('wablas-frontend/classroom-integration/notify-session') ?>/' + sessionId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✅ Notifications sent successfully!\n\nSent: ' + response.sent + '\nFailed: ' + response.failed);
                } else {
                    alert('❌ Failed to send notifications: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error sending notifications.');
            }
        });
    }
}

// Test notification
function testNotification(phoneNumber) {
    if (confirm('Send a test notification to ' + phoneNumber + '?')) {
        $.ajax({
            url: '<?= base_url('wablas-frontend/classroom-integration/test-notification') ?>',
            type: 'POST',
            data: { phone: phoneNumber },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✅ Test notification sent successfully!');
                } else {
                    alert('❌ Failed to send test notification: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error sending test notification.');
            }
        });
    }
}

// Edit mapping
function editMapping(mappingId) {
    // Implementation for editing student-parent mapping
    alert('Edit mapping functionality would be implemented here for ID: ' + mappingId);
}

// Integration settings form submission
$('#integrationSettingsForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/classroom-integration/save-settings') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Integration settings saved successfully!');
            } else {
                alert('❌ Failed to save settings: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error saving settings. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});

// Initialize DataTable
$(document).ready(function() {
    if ($('#mappingTable').length) {
        $('#mappingTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
