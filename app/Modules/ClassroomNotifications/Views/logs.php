<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-list-alt text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-info" onclick="refreshLogs()">
                <i class="fas fa-sync"></i> Refresh
            </button>
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
                                Total Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_notifications']) ?>
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['today_notifications']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fab fa-whatsapp fa-2x text-gray-300"></i>
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
                                Success Rate
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        <?= number_format($stats['success_rate'], 1) ?>%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= $stats['success_rate'] ?>%"></div>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Failed Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['failed_notifications']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
            <form method="GET" action="<?= base_url('classroom-notifications/logs') ?>">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="sent" <?= $filters['status'] === 'sent' ? 'selected' : '' ?>>Sent</option>
                                <option value="delivered" <?= $filters['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="read" <?= $filters['status'] === 'read' ? 'selected' : '' ?>>Read</option>
                                <option value="failed" <?= $filters['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="event_type">Event Type</label>
                            <select name="event_type" id="event_type" class="form-control">
                                <option value="">All Events</option>
                                <option value="session_start" <?= $filters['event_type'] === 'session_start' ? 'selected' : '' ?>>Class Started</option>
                                <option value="session_break" <?= $filters['event_type'] === 'session_break' ? 'selected' : '' ?>>Class Break</option>
                                <option value="session_resume" <?= $filters['event_type'] === 'session_resume' ? 'selected' : '' ?>>Class Resumed</option>
                                <option value="session_finish" <?= $filters['event_type'] === 'session_finish' ? 'selected' : '' ?>>Class Finished</option>
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="<?= base_url('classroom-notifications/logs') ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                                <button type="button" class="btn btn-info" onclick="exportLogs()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Notification Logs</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered datatable">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Student</th>
                                <th>Parent</th>
                                <th>Event</th>
                                <th>Session</th>
                                <th>Status</th>
                                <th>Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">
                                            <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($log['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">
                                            <?= htmlspecialchars($log['firstname'] . ' ' . $log['lastname']) ?>
                                        </div>
                                        <small class="text-muted">ID: <?= $log['student_id'] ?></small>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($log['parent_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($log['parent_phone']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $eventLabels = [
                                            'session_start' => 'Class Started',
                                            'session_break' => 'Class Break',
                                            'session_resume' => 'Class Resumed',
                                            'session_finish' => 'Class Finished'
                                        ];
                                        $eventColors = [
                                            'session_start' => 'success',
                                            'session_break' => 'warning',
                                            'session_resume' => 'info',
                                            'session_finish' => 'primary'
                                        ];
                                        ?>
                                        <span class="badge badge-<?= $eventColors[$log['event_type']] ?? 'secondary' ?>">
                                            <?= $eventLabels[$log['event_type']] ?? ucfirst($log['event_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($log['subject']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($log['session_name']) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'secondary',
                                            'sent' => 'primary',
                                            'delivered' => 'success',
                                            'read' => 'success',
                                            'failed' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$log['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?>">
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                        <?php if ($log['retry_count'] > 0): ?>
                                            <br><small class="text-muted">Retry: <?= $log['retry_count'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['sent_at']): ?>
                                            <small>
                                                <strong>Sent:</strong> <?= date('H:i:s', strtotime($log['sent_at'])) ?><br>
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($log['delivered_at']): ?>
                                            <small>
                                                <strong>Delivered:</strong> <?= date('H:i:s', strtotime($log['delivered_at'])) ?><br>
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($log['read_at']): ?>
                                            <small>
                                                <strong>Read:</strong> <?= date('H:i:s', strtotime($log['read_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" onclick="viewMessage(<?= $log['id'] ?>)" title="View Message">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($log['status'] === 'failed'): ?>
                                                <button class="btn btn-sm btn-warning" onclick="resendNotification(<?= $log['id'] ?>)" title="Resend">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!empty($log['wablas_response'])): ?>
                                                <button class="btn btn-sm btn-secondary" onclick="viewResponse(<?= $log['id'] ?>)" title="View Response">
                                                    <i class="fas fa-code"></i>
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
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No notification logs found</h5>
                    <p class="text-muted">Start a classroom session to see notification logs here.</p>
                    <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Session
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Content</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="fab fa-whatsapp"></i> WhatsApp Message
                    </div>
                    <div class="card-body">
                        <div id="message_content" style="white-space: pre-line; font-family: monospace; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <!-- Message content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Response Modal -->
<div class="modal fade" id="responseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">WABLAS API Response</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="response_content" style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto;">
                    <!-- Response content will be loaded here -->
                </pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function refreshLogs() {
    location.reload();
}

function viewMessage(logId) {
    // In a real implementation, fetch message content via AJAX
    $('#message_content').html('Loading message content...');
    $('#messageModal').modal('show');
    
    // Simulate loading message content
    setTimeout(() => {
        $('#message_content').html('🎓 *KELAS DIMULAI*\n\nYth. Orang Tua/Wali Test Parent,\n\nKami informasikan bahwa Test Student telah hadir di kelas:\n\n📚 *Mata Pelajaran:* Matematika\n🏫 *Kelas:* X\n👨‍🏫 *Guru:* Mrs. Sari\n⏰ *Waktu Mulai:* 08:00\n📅 *Tanggal:* 09/06/2025\n\nTerima kasih atas perhatiannya.\n\n*Student Finger School*');
    }, 500);
}

function viewResponse(logId) {
    // In a real implementation, fetch response data via AJAX
    $('#response_content').html('Loading API response...');
    $('#responseModal').modal('show');
    
    // Simulate loading response content
    setTimeout(() => {
        $('#response_content').html('{\n  "status": true,\n  "message": "Message sent successfully",\n  "data": {\n    "id": "msg_123456",\n    "phone": "628123456789",\n    "status": "sent",\n    "timestamp": "2025-06-09T14:30:00Z"\n  }\n}');
    }, 500);
}

function resendNotification(logId) {
    if (confirm('Resend this notification?')) {
        $.ajax({
            url: '<?= base_url('classroom-notifications/ajax/resend-notification') ?>',
            type: 'POST',
            data: { log_id: logId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('Notification resent successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Failed to resend: ' + response.message, 'error');
                }
            },
            error: function() {
                showToast('Error resending notification', 'error');
            }
        });
    }
}

function exportLogs() {
    // In a real implementation, this would export logs to CSV/Excel
    alert('Export functionality will be implemented');
}

function showToast(message, type = 'success') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}
</script>
<?= $this->endSection() ?>
