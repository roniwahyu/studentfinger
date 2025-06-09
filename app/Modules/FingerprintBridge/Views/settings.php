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
            <a href="<?= base_url('fingerprint-bridge') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button type="button" class="btn btn-info" id="testConnectionBtn">
                <i class="fas fa-sync"></i> Test Connection
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Database Configuration -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">FinPro Database Configuration</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Important:</strong> Configure the connection to your FingerSpot machine database (FinPro).
                        These settings are stored in the .env file and require proper database credentials.
                    </div>

                    <?= form_open('fingerprint-bridge/settings', ['id' => 'settingsForm']) ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finpro_host">Database Host <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="finpro_host" name="finpro_host" 
                                           value="<?= old('finpro_host', $current_config['host']) ?>" required>
                                    <small class="form-text text-muted">IP address or hostname of FinPro database server</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finpro_port">Port <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="finpro_port" name="finpro_port" 
                                           value="<?= old('finpro_port', $current_config['port']) ?>" min="1" max="65535" required>
                                    <small class="form-text text-muted">Database port (usually 3306 for MySQL)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finpro_username">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="finpro_username" name="finpro_username" 
                                           value="<?= old('finpro_username', $current_config['username']) ?>" required>
                                    <small class="form-text text-muted">Database username</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finpro_password">Password</label>
                                    <input type="password" class="form-control" id="finpro_password" name="finpro_password" 
                                           value="<?= old('finpro_password', $current_config['password']) ?>">
                                    <small class="form-text text-muted">Database password (leave empty if no password)</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finpro_database">Database Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="finpro_database" name="finpro_database" 
                                           value="<?= old('finpro_database', $current_config['database']) ?>" required>
                                    <small class="form-text text-muted">Name of the FinPro database</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="finpro_charset">Character Set</label>
                                    <select class="form-control" id="finpro_charset" name="finpro_charset">
                                        <option value="latin1" <?= $current_config['charset'] === 'latin1' ? 'selected' : '' ?>>latin1 (Default for FingerSpot)</option>
                                        <option value="utf8" <?= $current_config['charset'] === 'utf8' ? 'selected' : '' ?>>utf8</option>
                                        <option value="utf8mb4" <?= $current_config['charset'] === 'utf8mb4' ? 'selected' : '' ?>>utf8mb4</option>
                                    </select>
                                    <small class="form-text text-muted">Database character encoding</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="finpro_collation">Collation</label>
                            <select class="form-control" id="finpro_collation" name="finpro_collation">
                                <option value="latin1_swedish_ci" <?= $current_config['collation'] === 'latin1_swedish_ci' ? 'selected' : '' ?>>latin1_swedish_ci (Default)</option>
                                <option value="utf8_general_ci" <?= $current_config['collation'] === 'utf8_general_ci' ? 'selected' : '' ?>>utf8_general_ci</option>
                                <option value="utf8mb4_general_ci" <?= $current_config['collation'] === 'utf8mb4_general_ci' ? 'selected' : '' ?>>utf8mb4_general_ci</option>
                            </select>
                            <small class="form-text text-muted">Database collation</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                            <button type="button" class="btn btn-outline-info" id="testBeforeSaveBtn">
                                <i class="fas fa-vial"></i> Test Before Save
                            </button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>

            <!-- Module Settings -->
            <?php if (!empty($module_settings)): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Module Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($module_settings as $setting): ?>
                                        <tr>
                                            <td><strong><?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?></strong></td>
                                            <td>
                                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                                    <span class="badge badge-<?= $setting['setting_value'] === '1' ? 'success' : 'secondary' ?>">
                                                        <?= $setting['setting_value'] === '1' ? 'Enabled' : 'Disabled' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <code><?= htmlspecialchars($setting['setting_value']) ?></code>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted"><?= htmlspecialchars($setting['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Connection Status & Info -->
        <div class="col-lg-4">
            <!-- Current Connection Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Connection Status</h6>
                </div>
                <div class="card-body">
                    <div id="connectionStatus">
                        <?php if ($connection_test['success']): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Connected</strong>
                                <p class="mb-0">Successfully connected to FinPro database</p>
                            </div>
                            
                            <div class="mt-3">
                                <h6>Database Information:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Total Records:</strong> <?= number_format($connection_test['data']['total_records'] ?? 0) ?></li>
                                    <li><strong>Unique PINs:</strong> <?= number_format($connection_test['data']['unique_pins'] ?? 0) ?></li>
                                    <li><strong>Devices:</strong> <?= number_format($connection_test['data']['unique_devices'] ?? 0) ?></li>
                                    <?php if (!empty($connection_test['data']['earliest_date'])): ?>
                                        <li><strong>Date Range:</strong> 
                                            <?= date('M j, Y', strtotime($connection_test['data']['earliest_date'])) ?> - 
                                            <?= date('M j, Y', strtotime($connection_test['data']['latest_date'])) ?>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Connection Failed</strong>
                                <p class="mb-0"><?= $connection_test['message'] ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Configuration Help -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Configuration Help</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Tips:</h6>
                        <ul class="mb-0">
                            <li>Use the same server where FingerSpot software is installed</li>
                            <li>Default FinPro database name is usually "fin_pro"</li>
                            <li>Character set should be "latin1" for most FingerSpot installations</li>
                            <li>Test connection before saving to ensure settings are correct</li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Common Issues:</h6>
                        <ul class="small">
                            <li><strong>Connection refused:</strong> Check host and port</li>
                            <li><strong>Access denied:</strong> Verify username and password</li>
                            <li><strong>Database not found:</strong> Check database name</li>
                            <li><strong>Character encoding issues:</strong> Try different charset</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Module Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Module Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" class="btn btn-success">
                            <i class="fas fa-upload"></i> Start Import
                        </a>
                        <a href="<?= base_url('fingerprint-bridge/pin-mapping') ?>" class="btn btn-info">
                            <i class="fas fa-link"></i> Manage PIN Mapping
                        </a>
                        <a href="<?= base_url('fingerprint-bridge/logs') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> View Import Logs
                        </a>
                        <button class="btn btn-outline-warning" onclick="reinstallModule()">
                            <i class="fas fa-redo"></i> Reinstall Module
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Test connection button
    $('#testConnectionBtn').click(function() {
        testConnection();
    });
    
    // Test before save button
    $('#testBeforeSaveBtn').click(function() {
        testConnectionWithFormData();
    });
    
    // Auto-update collation based on charset
    $('#finpro_charset').change(function() {
        const charset = $(this).val();
        const collationSelect = $('#finpro_collation');
        
        collationSelect.empty();
        
        if (charset === 'latin1') {
            collationSelect.append('<option value="latin1_swedish_ci" selected>latin1_swedish_ci</option>');
            collationSelect.append('<option value="latin1_general_ci">latin1_general_ci</option>');
        } else if (charset === 'utf8') {
            collationSelect.append('<option value="utf8_general_ci" selected>utf8_general_ci</option>');
            collationSelect.append('<option value="utf8_unicode_ci">utf8_unicode_ci</option>');
        } else if (charset === 'utf8mb4') {
            collationSelect.append('<option value="utf8mb4_general_ci" selected>utf8mb4_general_ci</option>');
            collationSelect.append('<option value="utf8mb4_unicode_ci">utf8mb4_unicode_ci</option>');
        }
    });
});

function testConnection() {
    const btn = $('#testConnectionBtn');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('fingerprint-bridge/ajax/test-connection') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            updateConnectionStatus(response);
        },
        error: function() {
            updateConnectionStatus({
                success: false,
                message: 'Failed to test connection'
            });
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

function testConnectionWithFormData() {
    const btn = $('#testBeforeSaveBtn');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
    
    // This would require a separate endpoint that tests with form data
    // For now, just test current connection
    testConnection();
    
    setTimeout(function() {
        btn.html(originalText).prop('disabled', false);
    }, 2000);
}

function updateConnectionStatus(response) {
    let statusHtml = '';
    
    if (response.success) {
        statusHtml = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Connected</strong>
                <p class="mb-0">${response.message}</p>
            </div>
        `;
        
        if (response.data) {
            statusHtml += `
                <div class="mt-3">
                    <h6>Database Information:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Records:</strong> ${response.data.total_records.toLocaleString()}</li>
                        <li><strong>Unique PINs:</strong> ${response.data.unique_pins.toLocaleString()}</li>
                        <li><strong>Devices:</strong> ${response.data.unique_devices.toLocaleString()}</li>
                    </ul>
                </div>
            `;
        }
    } else {
        statusHtml = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Connection Failed</strong>
                <p class="mb-0">${response.message}</p>
            </div>
        `;
    }
    
    $('#connectionStatus').html(statusHtml);
}

function reinstallModule() {
    if (confirm('This will reinstall the FingerprintBridge module. Continue?')) {
        // Implement reinstallation logic
        alert('Reinstallation feature will be implemented');
    }
}
</script>
<?= $this->endSection() ?>
