<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-list text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('fingerprint-bridge') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button type="button" class="btn btn-info" onclick="refreshLogs()">
                <i class="fas fa-sync"></i> Refresh
            </button>
            <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Import
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('fingerprint-bridge/logs') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="import_type">Import Type</label>
                            <select class="form-control" id="import_type" name="import_type">
                                <option value="">All Types</option>
                                <option value="manual" <?= $filters['import_type'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                                <option value="auto" <?= $filters['import_type'] === 'auto' ? 'selected' : '' ?>>Auto</option>
                                <option value="scheduled" <?= $filters['import_type'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="running" <?= $filters['status'] === 'running' ? 'selected' : '' ?>>Running</option>
                                <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="failed" <?= $filters['status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $filters['start_date'] ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $filters['end_date'] ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="<?= base_url('fingerprint-bridge/logs') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Logs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Import History</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="logsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Date Range</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Records</th>
                                <th>Duration</th>
                                <th>Started</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><strong>#<?= $log['id'] ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= $log['import_type'] === 'manual' ? 'primary' : ($log['import_type'] === 'auto' ? 'success' : 'info') ?>">
                                            <?= ucfirst($log['import_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log['start_date'] && $log['end_date']): ?>
                                            <small>
                                                <?= date('M j', strtotime($log['start_date'])) ?> - 
                                                <?= date('M j, Y', strtotime($log['end_date'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'secondary',
                                            'running' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $statusColor = $statusColors[$log['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?>">
                                            <?= ucfirst($log['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($log['status'] === 'running' && $log['total_records'] > 0): ?>
                                            <?php 
                                            $progress = ($log['processed_records'] / $log['total_records']) * 100;
                                            ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" style="width: <?= $progress ?>%">
                                                    <?= number_format($progress, 1) ?>%
                                                </div>
                                            </div>
                                        <?php elseif ($log['status'] === 'completed'): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check"></i> 100%
                                            </span>
                                        <?php elseif ($log['status'] === 'failed'): ?>
                                            <span class="text-danger">
                                                <i class="fas fa-times"></i> Failed
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Total:</strong> <?= number_format($log['total_records']) ?><br>
                                            <span class="text-success">Inserted:</span> <?= number_format($log['inserted_records']) ?><br>
                                            <span class="text-info">Updated:</span> <?= number_format($log['updated_records']) ?><br>
                                            <span class="text-warning">Skipped:</span> <?= number_format($log['skipped_records']) ?><br>
                                            <?php if ($log['error_records'] > 0): ?>
                                                <span class="text-danger">Errors:</span> <?= number_format($log['error_records']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($log['duration']): ?>
                                            <?= gmdate('H:i:s', $log['duration']) ?>
                                        <?php elseif ($log['status'] === 'running' && $log['start_time']): ?>
                                            <span class="text-warning">
                                                <?= gmdate('H:i:s', time() - strtotime($log['start_time'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('M j, Y H:i', strtotime($log['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-info" onclick="viewLogDetails(<?= $log['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($log['status'] === 'running'): ?>
                                                <button class="btn btn-sm btn-danger" onclick="cancelImport(<?= $log['id'] ?>)">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (in_array($log['status'], ['completed', 'failed', 'cancelled'])): ?>
                                                <button class="btn btn-sm btn-danger" onclick="deleteLog(<?= $log['id'] ?>)">
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
                <div class="text-center py-4">
                    <i class="fas fa-list fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No import logs found</h5>
                    <p class="text-muted">Start your first import to see logs here.</p>
                    <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Start First Import
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Log Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Content will be loaded via AJAX -->
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
$(document).ready(function() {
    // Initialize DataTable
    $('#logsTable').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]], // Sort by ID descending
        "columnDefs": [
            { "orderable": false, "targets": [8] } // Disable sorting on actions column
        ]
    });
    
    // Auto-refresh running imports every 30 seconds
    <?php if (!empty($logs) && array_filter($logs, function($log) { return $log['status'] === 'running'; })): ?>
    setInterval(function() {
        refreshLogs();
    }, 30000);
    <?php endif; ?>
});

function refreshLogs() {
    location.reload();
}

function viewLogDetails(logId) {
    $('#logDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $('#logDetailsModal').modal('show');
    
    $.ajax({
        url: '<?= base_url('fingerprint-bridge/ajax/log-details') ?>',
        type: 'GET',
        data: { id: logId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<div class="row">';
                
                // Basic Information
                html += '<div class="col-md-6">';
                html += '<h6>Basic Information</h6>';
                html += '<table class="table table-sm">';
                html += `<tr><td><strong>ID:</strong></td><td>#${response.data.id}</td></tr>`;
                html += `<tr><td><strong>Type:</strong></td><td>${response.data.import_type}</td></tr>`;
                html += `<tr><td><strong>Status:</strong></td><td><span class="badge badge-${getStatusColor(response.data.status)}">${response.data.status}</span></td></tr>`;
                html += `<tr><td><strong>Date Range:</strong></td><td>${response.data.start_date || '-'} to ${response.data.end_date || '-'}</td></tr>`;
                html += `<tr><td><strong>Created:</strong></td><td>${response.data.created_at}</td></tr>`;
                html += '</table>';
                html += '</div>';
                
                // Statistics
                html += '<div class="col-md-6">';
                html += '<h6>Statistics</h6>';
                html += '<table class="table table-sm">';
                html += `<tr><td><strong>Total Records:</strong></td><td>${response.data.total_records.toLocaleString()}</td></tr>`;
                html += `<tr><td><strong>Processed:</strong></td><td>${response.data.processed_records.toLocaleString()}</td></tr>`;
                html += `<tr><td><strong>Inserted:</strong></td><td class="text-success">${response.data.inserted_records.toLocaleString()}</td></tr>`;
                html += `<tr><td><strong>Updated:</strong></td><td class="text-info">${response.data.updated_records.toLocaleString()}</td></tr>`;
                html += `<tr><td><strong>Skipped:</strong></td><td class="text-warning">${response.data.skipped_records.toLocaleString()}</td></tr>`;
                html += `<tr><td><strong>Errors:</strong></td><td class="text-danger">${response.data.error_records.toLocaleString()}</td></tr>`;
                html += '</table>';
                html += '</div>';
                
                html += '</div>';
                
                // Error Message
                if (response.data.error_message) {
                    html += '<div class="row mt-3">';
                    html += '<div class="col-12">';
                    html += '<h6>Error Message</h6>';
                    html += `<div class="alert alert-danger">${response.data.error_message}</div>`;
                    html += '</div>';
                    html += '</div>';
                }
                
                // Settings
                if (response.data.settings) {
                    html += '<div class="row mt-3">';
                    html += '<div class="col-12">';
                    html += '<h6>Import Settings</h6>';
                    html += `<pre class="bg-light p-3">${JSON.stringify(JSON.parse(response.data.settings), null, 2)}</pre>`;
                    html += '</div>';
                    html += '</div>';
                }
                
                $('#logDetailsContent').html(html);
            } else {
                $('#logDetailsContent').html('<div class="alert alert-danger">Failed to load log details.</div>');
            }
        },
        error: function() {
            $('#logDetailsContent').html('<div class="alert alert-danger">Failed to load log details.</div>');
        }
    });
}

function cancelImport(logId) {
    if (confirm('Are you sure you want to cancel this import? This action cannot be undone.')) {
        $.ajax({
            url: '<?= base_url('fingerprint-bridge/ajax/cancel-import') ?>',
            type: 'POST',
            data: { id: logId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Import cancelled successfully.');
                    location.reload();
                } else {
                    alert('Failed to cancel import: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to cancel import. Please try again.');
            }
        });
    }
}

function deleteLog(logId) {
    if (confirm('Are you sure you want to delete this log? This action cannot be undone.')) {
        $.ajax({
            url: '<?= base_url('fingerprint-bridge/ajax/delete-log') ?>',
            type: 'POST',
            data: { id: logId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Log deleted successfully.');
                    location.reload();
                } else {
                    alert('Failed to delete log: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to delete log. Please try again.');
            }
        });
    }
}

function getStatusColor(status) {
    const colors = {
        'pending': 'secondary',
        'running': 'warning',
        'completed': 'success',
        'failed': 'danger',
        'cancelled': 'dark'
    };
    return colors[status] || 'secondary';
}
</script>
<?= $this->endSection() ?>
