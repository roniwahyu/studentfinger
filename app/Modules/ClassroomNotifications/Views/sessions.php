<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Session
            </a>
            <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_sessions']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                Today's Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['today_sessions']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                                Active Sessions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['active_sessions']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
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
                                Completed Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['completed_today']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('classroom-notifications/sessions') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="scheduled" <?= $filters['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="started" <?= $filters['status'] === 'started' ? 'selected' : '' ?>>Started</option>
                                <option value="break" <?= $filters['status'] === 'break' ? 'selected' : '' ?>>Break</option>
                                <option value="resumed" <?= $filters['status'] === 'resumed' ? 'selected' : '' ?>>Resumed</option>
                                <option value="finished" <?= $filters['status'] === 'finished' ? 'selected' : '' ?>>Finished</option>
                                <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="class_id">Class</label>
                            <select name="class_id" id="class_id" class="form-control">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" <?= $filters['class_id'] == $class['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($class['class_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $filters['date_from'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $filters['date_to'] ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="<?= base_url('classroom-notifications/sessions') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Class Sessions</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($sessions)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Session</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Class</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Students</th>
                                <th>Notifications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">
                                            <?= date('d/m/Y', strtotime($session['session_date'])) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('l', strtotime($session['session_date'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">
                                            <?= htmlspecialchars($session['session_name']) ?>
                                        </div>
                                        <?php if (!empty($session['notes'])): ?>
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($session['notes'], 0, 50)) ?>...
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($session['subject']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($session['teacher_name']) ?></td>
                                    <td><?= htmlspecialchars($session['class_name'] ?? 'Unknown') ?></td>
                                    <td>
                                        <div>
                                            <?= date('H:i', strtotime($session['start_time'])) ?> - 
                                            <?= date('H:i', strtotime($session['end_time'])) ?>
                                        </div>
                                        <small class="text-muted">
                                            Break: <?= $session['break_duration'] ?> min
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'scheduled' => 'secondary',
                                            'started' => 'success',
                                            'break' => 'warning',
                                            'resumed' => 'info',
                                            'finished' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$session['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?>">
                                            <?= ucfirst($session['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= $session['present_students'] ?> / <?= $session['total_students'] ?></div>
                                        <?php if ($session['total_students'] > 0): ?>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: <?= ($session['present_students'] / $session['total_students']) * 100 ?>%"></div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?= $session['notifications_sent'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($session['status'] === 'scheduled'): ?>
                                                <button class="btn btn-sm btn-success" onclick="startSession(<?= $session['id'] ?>)" title="Start Session">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <a href="<?= base_url('classroom-notifications/sessions/edit/' . $session['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php elseif ($session['status'] === 'started' || $session['status'] === 'resumed'): ?>
                                                <button class="btn btn-sm btn-warning" onclick="breakSession(<?= $session['id'] ?>)" title="Break">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="finishSession(<?= $session['id'] ?>)" title="Finish">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            <?php elseif ($session['status'] === 'break'): ?>
                                                <button class="btn btn-sm btn-info" onclick="resumeSession(<?= $session['id'] ?>)" title="Resume">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="finishSession(<?= $session['id'] ?>)" title="Finish">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="<?= base_url('classroom-notifications/logs/session/' . $session['id']) ?>" class="btn btn-sm btn-info" title="View Logs">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            
                                            <?php if ($session['status'] === 'scheduled'): ?>
                                                <button class="btn btn-sm btn-danger" onclick="deleteSession(<?= $session['id'] ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No sessions found</h5>
                    <p class="text-muted">Create your first classroom session to get started.</p>
                    <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Session
                    </a>
                </div>
            <?php endif; ?>
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

function deleteSession(sessionId) {
    if (confirm('Are you sure you want to delete this session?')) {
        $.ajax({
            url: `<?= base_url('classroom-notifications/sessions/delete') ?>/${sessionId}`,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    }
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
</script>
<?= $this->endSection() ?>
