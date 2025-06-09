<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fab fa-whatsapp text-success"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Session
            </a>
            <a href="<?= base_url('classroom-notifications/contacts') ?>" class="btn btn-success">
                <i class="fas fa-address-book"></i> Contacts
            </a>
            <a href="<?= base_url('classroom-notifications/templates') ?>" class="btn btn-info">
                <i class="fas fa-edit"></i> Templates
            </a>
            <a href="<?= base_url('classroom-notifications/settings') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>

    <!-- WABLAS Connection Status -->
    <div class="row mb-4">
        <div class="col-12">
            <?php if ($wablas_status['success']): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <strong>WhatsApp Connected!</strong> WABLAS API is working properly.
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php else: ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>WhatsApp Connection Failed!</strong> <?= htmlspecialchars($wablas_status['message']) ?>
                    <a href="<?= base_url('classroom-notifications/settings') ?>" class="btn btn-sm btn-outline-danger ml-2">
                        Configure
                    </a>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Today's Sessions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($session_stats['today_sessions']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($session_stats['active_sessions']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Notifications -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($notification_stats['today_notifications']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fab fa-whatsapp fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Success Rate
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        <?= number_format($notification_stats['success_rate'], 1) ?>%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= $notification_stats['success_rate'] ?>%"></div>
                                    </div>
                                </div>
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
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Active Sessions</h6>
                    <a href="<?= base_url('classroom-notifications/sessions') ?>" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($active_sessions)): ?>
                        <?php foreach ($active_sessions as $session): ?>
                            <div class="d-flex align-items-center border-bottom py-3">
                                <div class="mr-3">
                                    <?php
                                    $statusColors = [
                                        'started' => 'success',
                                        'break' => 'warning',
                                        'resumed' => 'info'
                                    ];
                                    $statusColor = $statusColors[$session['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $statusColor ?> badge-pill">
                                        <?= ucfirst($session['status']) ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold"><?= htmlspecialchars($session['subject']) ?></div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($session['teacher_name']) ?> • 
                                        <?= date('H:i', strtotime($session['start_time'])) ?> - 
                                        <?= date('H:i', strtotime($session['end_time'])) ?>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="btn-group" role="group">
                                        <?php if ($session['status'] === 'started' || $session['status'] === 'resumed'): ?>
                                            <button class="btn btn-sm btn-warning" onclick="breakSession(<?= $session['id'] ?>)">
                                                <i class="fas fa-pause"></i> Break
                                            </button>
                                            <button class="btn btn-sm btn-success" onclick="finishSession(<?= $session['id'] ?>)">
                                                <i class="fas fa-stop"></i> Finish
                                            </button>
                                        <?php elseif ($session['status'] === 'break'): ?>
                                            <button class="btn btn-sm btn-info" onclick="resumeSession(<?= $session['id'] ?>)">
                                                <i class="fas fa-play"></i> Resume
                                            </button>
                                            <button class="btn btn-sm btn-success" onclick="finishSession(<?= $session['id'] ?>)">
                                                <i class="fas fa-stop"></i> Finish
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-clock fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No active sessions</p>
                            <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Session
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Sessions</h6>
                    <a href="<?= base_url('classroom-notifications/sessions') ?>" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcoming_sessions)): ?>
                        <?php foreach ($upcoming_sessions as $session): ?>
                            <div class="d-flex align-items-center border-bottom py-3">
                                <div class="mr-3">
                                    <div class="text-center">
                                        <div class="font-weight-bold text-primary">
                                            <?= date('d', strtotime($session['session_date'])) ?>
                                        </div>
                                        <div class="text-muted small">
                                            <?= date('M', strtotime($session['session_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold"><?= htmlspecialchars($session['subject']) ?></div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($session['teacher_name']) ?> • 
                                        <?= htmlspecialchars($session['class_name']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <?= date('H:i', strtotime($session['start_time'])) ?> - 
                                        <?= date('H:i', strtotime($session['end_time'])) ?>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <?php if ($session['session_date'] === date('Y-m-d')): ?>
                                        <button class="btn btn-sm btn-success" onclick="startSession(<?= $session['id'] ?>)">
                                            <i class="fas fa-play"></i> Start
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Scheduled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No upcoming sessions</p>
                            <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Schedule Session
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notifications -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Notifications</h6>
                    <a href="<?= base_url('classroom-notifications/logs') ?>" class="btn btn-sm btn-primary">
                        View All Logs
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_notifications)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Student</th>
                                        <th>Event</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_notifications as $notification): ?>
                                        <tr>
                                            <td>
                                                <small><?= date('H:i', strtotime($notification['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($notification['firstname'] . ' ' . $notification['lastname']) ?>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($notification['parent_phone']) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $eventLabels = [
                                                    'session_start' => 'Class Started',
                                                    'session_break' => 'Class Break',
                                                    'session_resume' => 'Class Resumed',
                                                    'session_finish' => 'Class Finished'
                                                ];
                                                ?>
                                                <span class="badge badge-info">
                                                    <?= $eventLabels[$notification['event_type']] ?? ucfirst($notification['event_type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($notification['subject']) ?></td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'secondary',
                                                    'sent' => 'success',
                                                    'delivered' => 'success',
                                                    'read' => 'success',
                                                    'failed' => 'danger'
                                                ];
                                                $statusColor = $statusColors[$notification['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $statusColor ?>">
                                                    <?= ucfirst($notification['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($notification['status'] === 'failed'): ?>
                                                    <button class="btn btn-sm btn-warning" onclick="resendNotification(<?= $notification['id'] ?>)">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-info" onclick="viewMessage(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fab fa-whatsapp fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No notifications sent yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Session Control Modal -->
<div class="modal fade" id="sessionControlModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Control</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sendNotifications" checked>
                    <label class="form-check-label" for="sendNotifications">
                        Send WhatsApp notifications to parents
                    </label>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        This will send notifications to all parents of students in this class.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSessionAction">Confirm</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentSessionId = null;
let currentAction = null;

function startSession(sessionId) {
    currentSessionId = sessionId;
    currentAction = 'start';
    $('#sessionControlModal .modal-title').text('Start Session');
    $('#confirmSessionAction').text('Start Session').removeClass().addClass('btn btn-success');
    $('#sessionControlModal').modal('show');
}

function breakSession(sessionId) {
    currentSessionId = sessionId;
    currentAction = 'break';
    $('#sessionControlModal .modal-title').text('Break Session');
    $('#confirmSessionAction').text('Set Break').removeClass().addClass('btn btn-warning');
    $('#sessionControlModal').modal('show');
}

function resumeSession(sessionId) {
    currentSessionId = sessionId;
    currentAction = 'resume';
    $('#sessionControlModal .modal-title').text('Resume Session');
    $('#confirmSessionAction').text('Resume Session').removeClass().addClass('btn btn-info');
    $('#sessionControlModal').modal('show');
}

function finishSession(sessionId) {
    currentSessionId = sessionId;
    currentAction = 'finish';
    $('#sessionControlModal .modal-title').text('Finish Session');
    $('#confirmSessionAction').text('Finish Session').removeClass().addClass('btn btn-success');
    $('#sessionControlModal').modal('show');
}

$('#confirmSessionAction').click(function() {
    if (!currentSessionId || !currentAction) return;
    
    const sendNotifications = $('#sendNotifications').is(':checked');
    const btn = $(this);
    const originalText = btn.text();
    
    btn.text('Processing...').prop('disabled', true);
    
    $.ajax({
        url: `<?= base_url('classroom-notifications/sessions') ?>/${currentAction}/${currentSessionId}`,
        type: 'POST',
        data: { send_notifications: sendNotifications },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#sessionControlModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        },
        complete: function() {
            btn.text(originalText).prop('disabled', false);
        }
    });
});

function resendNotification(logId) {
    if (confirm('Resend this notification?')) {
        $.ajax({
            url: '<?= base_url('classroom-notifications/ajax/resend-notification') ?>',
            type: 'POST',
            data: { log_id: logId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Notification resent successfully');
                    location.reload();
                } else {
                    alert('Failed to resend: ' + response.message);
                }
            }
        });
    }
}

function viewMessage(logId) {
    // Implementation for viewing message details
    alert('View message feature will be implemented');
}

// Auto-refresh every 30 seconds if there are active sessions
<?php if (!empty($active_sessions)): ?>
setInterval(function() {
    location.reload();
}, 30000);
<?php endif; ?>
</script>
<?= $this->endSection() ?>
