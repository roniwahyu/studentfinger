<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog text-secondary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="testConnection()">
                <i class="fas fa-wifi"></i> Test Connection
            </button>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-wifi"></i> Connection Status
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($connection_status['current_status'] === 'connected'): ?>
                        <div class="alert alert-success" role="alert">
                            <h5 class="alert-heading">
                                <i class="fas fa-check-circle"></i> Connected
                            </h5>
                            <p class="mb-0">
                                WhatsApp connection is active and working properly.
                                <br><strong>Last Check:</strong> <?= $connection_status['last_check'] ? date('Y-m-d H:i:s', strtotime($connection_status['last_check'])) : 'Never' ?>
                                <br><strong>Uptime:</strong> <?= $connection_status['uptime_percentage'] ?? 0 ?>%
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle"></i> Not Connected
                            </h5>
                            <p class="mb-0">
                                WhatsApp connection is not active. Please check your configuration and device connection.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- WABLAS Configuration -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server"></i> WABLAS Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <form id="wablasConfigForm">
                        <div class="form-group">
                            <label for="wablas_url">WABLAS API URL *</label>
                            <input type="url" class="form-control" id="wablas_url" name="wablas_url" 
                                   value="<?= htmlspecialchars($wablas_config['api_url'] ?? 'https://wablas.com') ?>" required>
                            <small class="form-text text-muted">Base URL for WABLAS API</small>
                        </div>

                        <div class="form-group">
                            <label for="wablas_token">API Token *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="wablas_token" name="wablas_token" 
                                       value="<?= htmlspecialchars($wablas_config['token'] ?? '') ?>" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('wablas_token')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Your WABLAS API token</small>
                        </div>

                        <div class="form-group">
                            <label for="wablas_secret">Secret Key</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="wablas_secret" name="wablas_secret" 
                                       value="<?= htmlspecialchars($wablas_config['secret_key'] ?? '') ?>">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('wablas_secret')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Optional secret key for webhook verification</small>
                        </div>

                        <div class="form-group">
                            <label for="timeout">Request Timeout (seconds)</label>
                            <input type="number" class="form-control" id="timeout" name="timeout" 
                                   value="<?= $wablas_config['timeout'] ?? 30 ?>" min="5" max="120">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save WABLAS Config
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-bell"></i> Notification Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form id="notificationSettingsForm">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="auto_send_session_start" name="auto_send_session_start" 
                                   <?= ($notification_settings['auto_send_session_start'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="auto_send_session_start">
                                Auto-send notifications when class session starts
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="auto_send_session_end" name="auto_send_session_end" 
                                   <?= ($notification_settings['auto_send_session_end'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="auto_send_session_end">
                                Auto-send notifications when class session ends
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="send_attendance_alerts" name="send_attendance_alerts" 
                                   <?= ($notification_settings['send_attendance_alerts'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="send_attendance_alerts">
                                Send attendance alerts for absent students
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="default_language">Default Language</label>
                            <select class="form-control" id="default_language" name="default_language">
                                <option value="id" <?= ($notification_settings['default_language'] ?? 'id') === 'id' ? 'selected' : '' ?>>Indonesian</option>
                                <option value="en" <?= ($notification_settings['default_language'] ?? 'id') === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="retry_attempts">Retry Attempts for Failed Messages</label>
                            <input type="number" class="form-control" id="retry_attempts" name="retry_attempts" 
                                   value="<?= $notification_settings['retry_attempts'] ?? 3 ?>" min="0" max="10">
                        </div>

                        <div class="form-group">
                            <label for="retry_delay">Retry Delay (minutes)</label>
                            <input type="number" class="form-control" id="retry_delay" name="retry_delay" 
                                   value="<?= $notification_settings['retry_delay'] ?? 5 ?>" min="1" max="60">
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Notification Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Webhook Configuration -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-link"></i> Webhook Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="webhook_url">Webhook URL</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="webhook_url" value="<?= $webhook_url ?>" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('webhook_url')">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Use this URL in your WABLAS webhook configuration</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Webhook Status</label>
                                <div class="form-control-plaintext">
                                    <span class="badge badge-info">
                                        <i class="fas fa-info-circle"></i> Ready to receive webhooks
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle"></i> Webhook Setup Instructions
                        </h6>
                        <ol class="mb-0">
                            <li>Copy the webhook URL above</li>
                            <li>Go to your WABLAS dashboard</li>
                            <li>Navigate to Webhook settings</li>
                            <li>Paste the URL in the webhook configuration</li>
                            <li>Enable webhooks for message status updates</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-info-circle"></i> System Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Module Version:</strong><br>
                            <span class="text-muted">WablasFrontEnd v1.0.0</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Last Configuration Update:</strong><br>
                            <span class="text-muted"><?= date('Y-m-d H:i:s') ?></span>
                        </div>
                        <div class="col-md-4">
                            <strong>Integration Status:</strong><br>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Copy to clipboard
function copyToClipboard(fieldId) {
    const field = document.getElementById(fieldId);
    field.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = field.nextElementSibling.querySelector('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check text-success"></i>';
    
    setTimeout(function() {
        btn.innerHTML = originalHtml;
    }, 2000);
}

// Test connection
function testConnection() {
    const btn = $('button:contains("Test Connection")');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/settings/test-connection') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Connection test successful!\n\n' + response.message);
            } else {
                alert('❌ Connection test failed:\n\n' + response.message);
            }
        },
        error: function() {
            alert('❌ Error testing connection. Please check your settings.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

// WABLAS config form submission
$('#wablasConfigForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/settings/save') ?>',
        type: 'POST',
        data: $(this).serialize() + '&config_type=wablas',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ WABLAS configuration saved successfully!');
            } else {
                alert('❌ Failed to save configuration: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error saving configuration. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});

// Notification settings form submission
$('#notificationSettingsForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/settings/save') ?>',
        type: 'POST',
        data: $(this).serialize() + '&config_type=notifications',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Notification settings saved successfully!');
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
</script>
<?= $this->endSection() ?>
