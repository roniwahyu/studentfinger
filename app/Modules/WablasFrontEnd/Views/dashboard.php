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
            <a href="<?= base_url('wablas-frontend/broadcast') ?>" class="btn btn-success">
                <i class="fas fa-bullhorn"></i> Send Broadcast
            </a>
            <a href="<?= base_url('wablas-frontend/messages') ?>" class="btn btn-primary">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-chalkboard-teacher"></i> Classroom Notifications
            </a>
        </div>
    </div>

    <!-- Connection Status Alert -->
    <?php if ($connection_status['current_status'] === 'connected'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <strong>WhatsApp Connected!</strong> 
            Device: <?= htmlspecialchars($device_info['device_name']) ?>
            | Device ID: <?= htmlspecialchars($device_info['device_id']) ?>
            | Quota: <?= number_format($quota_info['remaining']) ?> messages
            | Uptime: <?= $connection_status['uptime_percentage'] ?>%
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>WhatsApp Not Connected!</strong> 
            <?= htmlspecialchars($connection_status['error_message'] ?? 'Please check your device connection.') ?>
            <a href="<?= base_url('wablas-frontend/devices') ?>" class="btn btn-sm btn-outline-warning ml-2">
                <i class="fas fa-qrcode"></i> Scan QR Code
            </a>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Device Status -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-<?= $connection_status['current_status'] === 'connected' ? 'success' : 'danger' ?> shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-<?= $connection_status['current_status'] === 'connected' ? 'success' : 'danger' ?> text-uppercase mb-1">
                                Device Status
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= ucfirst($connection_status['current_status']) ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= $device_info['device_name'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Messages -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($message_stats['today']['total']) ?>
                            </div>
                            <div class="text-xs text-muted">
                                Sent: <?= $message_stats['today']['sent'] ?> | Failed: <?= $message_stats['today']['failed'] ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
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
                                        <?= $message_stats['success_rate'] ?>%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= $message_stats['success_rate'] ?>%" 
                                             aria-valuenow="<?= $message_stats['success_rate'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100"></div>
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

        <!-- Quota Remaining -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Quota Remaining
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($quota_info['remaining']) ?>
                            </div>
                            <div class="text-xs text-muted">
                                Messages available
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-battery-three-quarters fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="<?= base_url('wablas-frontend/broadcast') ?>" class="btn btn-success btn-block">
                                <i class="fas fa-bullhorn"></i><br>
                                Send Broadcast
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#quickMessageModal">
                                <i class="fas fa-paper-plane"></i><br>
                                Quick Message
                            </button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= base_url('wablas-frontend/contacts') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-address-book"></i><br>
                                Manage Contacts
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?= base_url('wablas-frontend/templates') ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-file-alt"></i><br>
                                Templates
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integration Status -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plug"></i> Integration Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chalkboard-teacher text-primary"></i>
                                Classroom Notifications
                            </div>
                            <span class="badge badge-<?= $integration_status['classroom_notifications'] ? 'success' : 'secondary' ?> badge-pill">
                                <?= $integration_status['classroom_notifications'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-sync text-info"></i>
                                Contact Sync
                            </div>
                            <span class="badge badge-<?= $integration_status['contact_sync'] ? 'success' : 'secondary' ?> badge-pill">
                                <?= $integration_status['contact_sync'] ? 'Synced' : 'Not Synced' ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file-alt text-warning"></i>
                                Template Sync
                            </div>
                            <span class="badge badge-<?= $integration_status['template_sync'] ? 'success' : 'secondary' ?> badge-pill">
                                <?= $integration_status['template_sync'] ? 'Synced' : 'Not Synced' ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-bell text-success"></i>
                                Auto Notifications
                            </div>
                            <span class="badge badge-<?= $integration_status['auto_notifications'] ? 'success' : 'secondary' ?> badge-pill">
                                <?= $integration_status['auto_notifications'] ? 'Enabled' : 'Disabled' ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="<?= base_url('wablas-frontend/integration/classroom') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-cog"></i> Manage Integration
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Messages
                    </h6>
                    <a href="<?= base_url('wablas-frontend/messages') ?>" class="btn btn-sm btn-primary">
                        View All Messages
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_messages)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Recipient</th>
                                        <th>Student</th>
                                        <th>Event Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_messages as $message): ?>
                                        <tr>
                                            <td>
                                                <small><?= date('H:i:s', strtotime($message['sent_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($message['parent_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($message['parent_phone']) ?></small>
                                            </td>
                                            <td>
                                                <?php if (!empty($message['firstname'])): ?>
                                                    <?= htmlspecialchars($message['firstname'] . ' ' . $message['lastname']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Manual Send</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?= ucfirst(str_replace('_', ' ', $message['event_type'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'sent' => 'success',
                                                    'failed' => 'danger',
                                                    'pending' => 'warning'
                                                ];
                                                $statusColor = $statusColors[$message['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $statusColor ?>">
                                                    <?= ucfirst($message['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewMessage(<?= $message['id'] ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($message['status'] === 'failed'): ?>
                                                    <button class="btn btn-sm btn-outline-warning" onclick="resendMessage(<?= $message['id'] ?>)" title="Resend">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">No recent messages</h5>
                            <p class="text-muted">Start sending messages to see them here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Message Modal -->
<div class="modal fade" id="quickMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane"></i> Send Quick Message
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="quickMessageForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="quick_phone">Phone Number *</label>
                        <input type="text" class="form-control" id="quick_phone" name="phone" required
                               placeholder="628123456789">
                        <small class="form-text text-muted">Format: 628xxxxxxxxx</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="quick_message">Message *</label>
                        <textarea class="form-control" id="quick_message" name="message" rows="6" required
                                  placeholder="Enter your WhatsApp message here..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="save_to_log" name="save_to_log" checked>
                        <label class="form-check-label" for="save_to_log">
                            Save to message log
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fab fa-whatsapp"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-refresh connection status every 30 seconds
setInterval(function() {
    $.ajax({
        url: '<?= base_url('wablas-frontend/ajax/refresh-status') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update status indicators
                updateConnectionStatus(response.data);
            }
        }
    });
}, 30000);

// Quick message form submission
$('#quickMessageForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/messages/send') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Message sent successfully!');
                $('#quickMessageModal').modal('hide');
                $('#quickMessageForm')[0].reset();
                location.reload(); // Refresh to show new message
            } else {
                alert('❌ Failed to send message: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error sending message. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});

// Auto-format phone number
$('#quick_phone').on('input', function() {
    let value = $(this).val().replace(/[^0-9]/g, '');
    
    // Convert to Indonesian format
    if (value.startsWith('0')) {
        value = '62' + value.substring(1);
    } else if (!value.startsWith('62')) {
        value = '62' + value;
    }
    
    $(this).val(value);
});

function viewMessage(messageId) {
    // Implementation for viewing message details
    window.location.href = '<?= base_url('wablas-frontend/messages') ?>?id=' + messageId;
}

function resendMessage(messageId) {
    if (confirm('Resend this message?')) {
        $.ajax({
            url: '<?= base_url('wablas-frontend/messages/resend') ?>/' + messageId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✅ Message resent successfully!');
                    location.reload();
                } else {
                    alert('❌ Failed to resend message: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error resending message.');
            }
        });
    }
}

function updateConnectionStatus(data) {
    // Update connection status indicators
    // Implementation would update the UI elements based on new status
}
</script>
<?= $this->endSection() ?>
