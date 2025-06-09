<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cog text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" onclick="testConnection()">
                <i class="fas fa-wifi"></i> Test Connection
            </button>
            <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Connection Status Alert -->
    <?php if (isset($connection_status['current_status']) && $connection_status['current_status'] === 'connected'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <strong>WhatsApp Connected!</strong> 
            Device: <?= htmlspecialchars($connection_status['device_name'] ?? 'Unknown') ?>
            | Quota: <?= number_format($connection_status['quota_remaining'] ?? 0) ?> messages
            | Uptime: <?= $connection_status['uptime_percentage'] ?? 0 ?>%
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>WhatsApp Not Connected!</strong> 
            Please configure your WABLAS settings below.
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('classroom-notifications/settings/save') ?>">
        <div class="row">
            <!-- WABLAS Configuration -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fab fa-whatsapp"></i> WABLAS Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="wablas_base_url">Base URL *</label>
                            <input type="url" class="form-control" id="wablas_base_url" name="wablas_base_url" 
                                   value="<?= htmlspecialchars($wablas_config['base_url'] ?? 'https://texas.wablas.com') ?>" required
                                   placeholder="https://texas.wablas.com">
                            <small class="form-text text-muted">WABLAS API base URL (changes based on server)</small>
                        </div>

                        <div class="form-group">
                            <label for="wablas_token">API Token *</label>
                            <input type="text" class="form-control" id="wablas_token" name="wablas_token" 
                                   value="<?= htmlspecialchars($wablas_config['token'] ?? '') ?>" required
                                   placeholder="Your WABLAS API token">
                            <small class="form-text text-muted">Get this from your WABLAS dashboard</small>
                        </div>

                        <div class="form-group">
                            <label for="wablas_secret_key">Secret Key *</label>
                            <input type="text" class="form-control" id="wablas_secret_key" name="wablas_secret_key" 
                                   value="<?= htmlspecialchars($wablas_config['secret_key'] ?? '') ?>" required
                                   placeholder="Your WABLAS secret key">
                            <small class="form-text text-muted">Secret key for API authentication</small>
                        </div>

                        <div class="form-group">
                            <label for="wablas_test_phone">Test Phone Number *</label>
                            <input type="text" class="form-control" id="wablas_test_phone" name="wablas_test_phone" 
                                   value="<?= htmlspecialchars($wablas_config['test_phone'] ?? '628123456789') ?>" required
                                   placeholder="628123456789">
                            <small class="form-text text-muted">Phone number for testing connections (format: 628xxxxxxxxx)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="wablas_timeout">Timeout (seconds)</label>
                                    <input type="number" class="form-control" id="wablas_timeout" name="wablas_timeout" 
                                           value="<?= $wablas_config['timeout'] ?? 30 ?>" min="5" max="120">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="wablas_retry_attempts">Retry Attempts</label>
                                    <input type="number" class="form-control" id="wablas_retry_attempts" name="wablas_retry_attempts" 
                                           value="<?= $wablas_config['retry_attempts'] ?? 3 ?>" min="1" max="10">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="wablas_auto_check_interval">Auto Check Interval (minutes)</label>
                            <input type="number" class="form-control" id="wablas_auto_check_interval" name="wablas_auto_check_interval" 
                                   value="<?= $wablas_config['auto_check_interval'] ?? 5 ?>" min="1" max="60">
                            <small class="form-text text-muted">How often to automatically check connection</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bell"></i> Notification Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="school_name">School Name *</label>
                            <input type="text" class="form-control" id="school_name" name="school_name" 
                                   value="<?= htmlspecialchars($notification_settings['school_name'] ?? 'Student Finger School') ?>" required
                                   placeholder="Student Finger School">
                            <small class="form-text text-muted">Will appear in notification messages</small>
                        </div>

                        <div class="form-group">
                            <label for="default_language">Default Language</label>
                            <select class="form-control" id="default_language" name="default_language">
                                <option value="id" <?= ($notification_settings['default_language'] ?? 'id') === 'id' ? 'selected' : '' ?>>Indonesian</option>
                                <option value="en" <?= ($notification_settings['default_language'] ?? 'id') === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Auto Send Notifications</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auto_send_on_session_start" 
                                       name="auto_send_on_session_start" value="1" 
                                       <?= ($notification_settings['auto_send_on_session_start'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_send_on_session_start">
                                    Session Start
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auto_send_on_session_break" 
                                       name="auto_send_on_session_break" value="1" 
                                       <?= ($notification_settings['auto_send_on_session_break'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_send_on_session_break">
                                    Session Break
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auto_send_on_session_resume" 
                                       name="auto_send_on_session_resume" value="1" 
                                       <?= ($notification_settings['auto_send_on_session_resume'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_send_on_session_resume">
                                    Session Resume
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auto_send_on_session_finish" 
                                       name="auto_send_on_session_finish" value="1" 
                                       <?= ($notification_settings['auto_send_on_session_finish'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="auto_send_on_session_finish">
                                    Session Finish
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notification_delay">Notification Delay (seconds)</label>
                                    <input type="number" class="form-control" id="notification_delay" name="notification_delay" 
                                           value="<?= $notification_settings['notification_delay'] ?? 0 ?>" min="0" max="300">
                                    <small class="form-text text-muted">Delay before sending notifications</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_retry_attempts">Max Retry Attempts</label>
                                    <input type="number" class="form-control" id="max_retry_attempts" name="max_retry_attempts" 
                                           value="<?= $notification_settings['max_retry_attempts'] ?? 3 ?>" min="1" max="10">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="retry_delay">Retry Delay (seconds)</label>
                            <input type="number" class="form-control" id="retry_delay" name="retry_delay" 
                                   value="<?= $notification_settings['retry_delay'] ?? 300 ?>" min="30" max="3600">
                            <small class="form-text text-muted">Delay between retry attempts</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection Status -->
        <?php if (isset($connection_status)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line"></i> Connection Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="h5 mb-0 font-weight-bold text-<?= ($connection_status['current_status'] ?? 'disconnected') === 'connected' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($connection_status['current_status'] ?? 'Disconnected') ?>
                                    </div>
                                    <div class="text-xs text-muted">Current Status</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="h5 mb-0 font-weight-bold text-info">
                                        <?= $connection_status['quota_remaining'] ?? 0 ?>
                                    </div>
                                    <div class="text-xs text-muted">Messages Remaining</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="h5 mb-0 font-weight-bold text-warning">
                                        <?= $connection_status['uptime_percentage'] ?? 0 ?>%
                                    </div>
                                    <div class="text-xs text-muted">Uptime (24h)</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="h5 mb-0 font-weight-bold text-primary">
                                        <?= $connection_status['device_id'] ?? 'N/A' ?>
                                    </div>
                                    <div class="text-xs text-muted">Device ID</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($connection_status['last_check'])): ?>
                            <hr>
                            <p class="text-muted mb-0">
                                <small>
                                    <i class="fas fa-clock"></i> 
                                    Last checked: <?= date('d/m/Y H:i:s', strtotime($connection_status['last_check'])) ?>
                                </small>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Save Button -->
        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <button type="button" class="btn btn-success btn-lg ml-2" onclick="testConnection()">
                        <i class="fas fa-wifi"></i> Test Connection
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function testConnection() {
    const btn = $('button[onclick="testConnection()"]');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
    
    // Get current form values
    const testData = {
        base_url: $('#wablas_base_url').val(),
        token: $('#wablas_token').val(),
        secret_key: $('#wablas_secret_key').val(),
        test_phone: $('#wablas_test_phone').val()
    };
    
    $.ajax({
        url: '<?= base_url('classroom-notifications/ajax/test-connection') ?>',
        type: 'POST',
        data: testData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Connection test successful!\n\nMessage sent to: ' + testData.test_phone);
                location.reload(); // Reload to update connection status
            } else {
                alert('❌ Connection test failed!\n\nError: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Connection test failed!\n\nPlease check your configuration.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

// Auto-format phone number
$('#wablas_test_phone').on('input', function() {
    let value = $(this).val().replace(/[^0-9]/g, '');
    
    // Convert to Indonesian format
    if (value.startsWith('0')) {
        value = '62' + value.substring(1);
    } else if (!value.startsWith('62')) {
        value = '62' + value;
    }
    
    $(this).val(value);
});
</script>
<?= $this->endSection() ?>
