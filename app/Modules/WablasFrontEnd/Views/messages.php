<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-comments text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#sendMessageModal">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
            <a href="<?= base_url('wablas-frontend/broadcast') ?>" class="btn btn-primary">
                <i class="fas fa-bullhorn"></i> Broadcast
            </a>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Connection Status Alert -->
    <?php if ($connection_status['current_status'] === 'connected'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <strong>WhatsApp Connected!</strong> 
            Device: <?= htmlspecialchars($connection_status['device_name'] ?? 'Unknown') ?>
            | Status: Active
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>WhatsApp Not Connected!</strong> 
            Please check your device connection.
            <a href="<?= base_url('wablas-frontend/devices') ?>" class="btn btn-sm btn-outline-warning ml-2">
                <i class="fas fa-qrcode"></i> Connect Device
            </a>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['today']['total'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
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
                                Sent Successfully
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['today']['sent'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Failed Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['today']['failed'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['success_rate'] ?? 0 ?>%
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

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Message Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('wablas-frontend/messages') ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="sent" <?= ($filters['status'] === 'sent') ? 'selected' : '' ?>>Sent</option>
                            <option value="failed" <?= ($filters['status'] === 'failed') ? 'selected' : '' ?>>Failed</option>
                            <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $filters['date_from'] ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $filters['date_to'] ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Phone, name, message..." value="<?= $filters['search'] ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="<?= base_url('wablas-frontend/messages') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Messages
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($messages)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="messagesTable">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Recipient</th>
                                <th>Student</th>
                                <th>Event Type</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td>
                                        <small><?= date('Y-m-d H:i:s', strtotime($message['created_at'])) ?></small>
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
                                        <div class="message-preview">
                                            <?= htmlspecialchars(substr($message['message_content'], 0, 50)) ?>
                                            <?php if (strlen($message['message_content']) > 50): ?>...<?php endif; ?>
                                        </div>
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
                    <h5 class="text-muted">No messages found</h5>
                    <p class="text-muted">Try adjusting your filters or send your first message.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#sendMessageModal">
                        <i class="fas fa-paper-plane"></i> Send First Message
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane"></i> Send Message
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="sendMessageForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="text" class="form-control" id="phone" name="phone" required
                               placeholder="628123456789">
                        <small class="form-text text-muted">Format: 628xxxxxxxxx</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea class="form-control" id="message" name="message" rows="6" required
                                  placeholder="Enter your WhatsApp message here..."></textarea>
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
// Send message form submission
$('#sendMessageForm').submit(function(e) {
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
                $('#sendMessageModal').modal('hide');
                $('#sendMessageForm')[0].reset();
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
$('#phone').on('input', function() {
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
    alert('View message details for ID: ' + messageId);
    // You can implement a modal or redirect to details page
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

// Initialize DataTable for better table functionality
$(document).ready(function() {
    if ($('#messagesTable').length) {
        $('#messagesTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "desc" ]], // Sort by time descending
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sorting on Actions column
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
