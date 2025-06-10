<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bullhorn text-success"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('wablas-frontend/broadcast/history') ?>" class="btn btn-info">
                <i class="fas fa-history"></i> Broadcast History
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
            <strong>WhatsApp Connected!</strong> Ready to send broadcast messages.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>WhatsApp Not Connected!</strong> Please connect your device before sending broadcasts.
            <a href="<?= base_url('wablas-frontend/devices') ?>" class="btn btn-sm btn-outline-warning ml-2">
                <i class="fas fa-qrcode"></i> Connect Device
            </a>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Broadcast Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-paper-plane"></i> Send Broadcast Message
                    </h6>
                </div>
                <div class="card-body">
                    <form id="broadcastForm">
                        <div class="form-group">
                            <label for="recipient_type">Recipients *</label>
                            <select class="form-control" id="recipient_type" name="recipient_type" required>
                                <option value="">Select Recipients</option>
                                <option value="all_contacts">All Contacts</option>
                                <option value="active_contacts">Active Contacts Only</option>
                                <option value="parents">Parents Only</option>
                                <option value="guardians">Guardians Only</option>
                                <option value="specific_group">Specific Group</option>
                                <option value="custom_list">Custom Phone List</option>
                            </select>
                        </div>

                        <div class="form-group" id="groupSelection" style="display: none;">
                            <label for="contact_group">Select Group</label>
                            <select class="form-control" id="contact_group" name="contact_group">
                                <option value="">Select Group</option>
                                <?php foreach ($contact_groups as $group): ?>
                                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" id="customPhoneList" style="display: none;">
                            <label for="phone_numbers">Phone Numbers</label>
                            <textarea class="form-control" id="phone_numbers" name="phone_numbers" rows="3"
                                      placeholder="Enter phone numbers separated by commas or new lines&#10;Example: 628123456789, 628987654321"></textarea>
                            <small class="form-text text-muted">Format: 628xxxxxxxxx (one per line or comma separated)</small>
                        </div>

                        <div class="form-group">
                            <label for="message_type">Message Type</label>
                            <select class="form-control" id="message_type" name="message_type">
                                <option value="custom">Custom Message</option>
                                <option value="template">Use Template</option>
                            </select>
                        </div>

                        <div class="form-group" id="templateSelection" style="display: none;">
                            <label for="template_id">Select Template</label>
                            <select class="form-control" id="template_id" name="template_id">
                                <option value="">Select Template</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?= $template['id'] ?>"><?= htmlspecialchars($template['template_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" id="customMessage">
                            <label for="message">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required
                                      placeholder="Enter your broadcast message here..."></textarea>
                            <div class="mt-2">
                                <small class="text-muted">Character count: <span id="charCount">0</span>/1000</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="send_option">Send Option</label>
                            <select class="form-control" id="send_option" name="send_option">
                                <option value="immediate">Send Immediately</option>
                                <option value="scheduled">Schedule for Later</option>
                            </select>
                        </div>

                        <div class="form-group" id="scheduleDateTime" style="display: none;">
                            <label for="scheduled_at">Schedule Date & Time</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at">
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="confirm_send" name="confirm_send" required>
                            <label class="form-check-label" for="confirm_send">
                                I confirm that I want to send this broadcast message to the selected recipients
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg btn-block" <?= $connection_status['current_status'] !== 'connected' ? 'disabled' : '' ?>>
                            <i class="fas fa-bullhorn"></i> Send Broadcast
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Broadcast Info & Recent Broadcasts -->
        <div class="col-lg-4">
            <!-- Broadcast Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-bar"></i> Broadcast Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <h4 class="text-primary"><?= count($contact_groups) ?></h4>
                            <small class="text-muted">Contact Groups</small>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-success"><?= count($templates) ?></h4>
                            <small class="text-muted">Available Templates</small>
                        </div>
                        <div class="mb-3">
                            <h4 class="text-info"><?= count($recent_broadcasts) ?></h4>
                            <small class="text-muted">Recent Broadcasts</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Broadcasts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Broadcasts
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_broadcasts)): ?>
                        <?php foreach ($recent_broadcasts as $broadcast): ?>
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-muted"><?= date('M d, H:i', strtotime($broadcast['sent_at'])) ?></small>
                                        <p class="mb-1"><?= htmlspecialchars(substr($broadcast['message'], 0, 50)) ?>...</p>
                                        <small class="text-info">Recipients: <?= $broadcast['recipient_count'] ?></small>
                                    </div>
                                    <span class="badge badge-<?= $broadcast['status'] === 'completed' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($broadcast['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="<?= base_url('wablas-frontend/broadcast/history') ?>" class="btn btn-outline-primary btn-sm">
                                View All History
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-bullhorn fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted">No recent broadcasts</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-lightbulb"></i> Quick Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <small>Keep messages under 1000 characters for best delivery</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <small>Use templates for consistent messaging</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <small>Test with a small group first</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            <small>Schedule broadcasts for optimal timing</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Handle recipient type change
$('#recipient_type').change(function() {
    const value = $(this).val();
    
    $('#groupSelection').hide();
    $('#customPhoneList').hide();
    
    if (value === 'specific_group') {
        $('#groupSelection').show();
    } else if (value === 'custom_list') {
        $('#customPhoneList').show();
    }
});

// Handle message type change
$('#message_type').change(function() {
    const value = $(this).val();
    
    if (value === 'template') {
        $('#templateSelection').show();
        $('#customMessage').hide();
        $('#message').prop('required', false);
    } else {
        $('#templateSelection').hide();
        $('#customMessage').show();
        $('#message').prop('required', true);
    }
});

// Handle send option change
$('#send_option').change(function() {
    const value = $(this).val();
    
    if (value === 'scheduled') {
        $('#scheduleDateTime').show();
        $('#scheduled_at').prop('required', true);
    } else {
        $('#scheduleDateTime').hide();
        $('#scheduled_at').prop('required', false);
    }
});

// Character counter
$('#message').on('input', function() {
    const length = $(this).val().length;
    $('#charCount').text(length);
    
    if (length > 1000) {
        $('#charCount').addClass('text-danger');
    } else {
        $('#charCount').removeClass('text-danger');
    }
});

// Template selection change
$('#template_id').change(function() {
    const templateId = $(this).val();
    if (templateId) {
        // Load template content (would be implemented with AJAX)
        // For now, just show a placeholder
        $('#message').val('Template content would be loaded here...');
        $('#message').trigger('input');
    }
});

// Broadcast form submission
$('#broadcastForm').submit(function(e) {
    e.preventDefault();
    
    const recipientType = $('#recipient_type').val();
    const message = $('#message').val();
    
    if (!recipientType) {
        alert('Please select recipients');
        return;
    }
    
    if ($('#message_type').val() === 'custom' && !message) {
        alert('Please enter a message');
        return;
    }
    
    if (!$('#confirm_send').is(':checked')) {
        alert('Please confirm that you want to send this broadcast');
        return;
    }
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/broadcast/send') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Broadcast sent successfully!\n\nSent: ' + response.sent + '\nFailed: ' + response.failed);
                $('#broadcastForm')[0].reset();
                location.reload();
            } else {
                alert('❌ Failed to send broadcast: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error sending broadcast. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});

// Auto-format phone numbers in custom list
$('#phone_numbers').on('input', function() {
    let value = $(this).val();
    
    // Split by comma or newline and format each number
    let numbers = value.split(/[,\n]/).map(function(num) {
        num = num.trim().replace(/[^0-9]/g, '');
        
        if (num.startsWith('0')) {
            num = '62' + num.substring(1);
        } else if (!num.startsWith('62')) {
            num = '62' + num;
        }
        
        return num;
    }).filter(function(num) {
        return num.length > 5; // Filter out invalid numbers
    });
    
    $(this).val(numbers.join('\n'));
});
</script>
<?= $this->endSection() ?>
