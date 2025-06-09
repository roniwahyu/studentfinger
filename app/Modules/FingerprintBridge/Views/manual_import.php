<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-upload text-primary"></i>
            <?= $title ?>
        </h1>
        <a href="<?= base_url('fingerprint-bridge') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
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
        <!-- Import Form -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Import Configuration</h6>
                </div>
                <div class="card-body">
                    <?= form_open('fingerprint-bridge/manual-import', ['id' => 'importForm']) ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="<?= old('start_date', date('Y-m-d', strtotime('-7 days'))) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="<?= old('end_date', date('Y-m-d')) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duplicate_handling">Duplicate Handling</label>
                                    <select class="form-control" id="duplicate_handling" name="duplicate_handling">
                                        <option value="skip" <?= old('duplicate_handling') === 'skip' ? 'selected' : '' ?>>
                                            Skip duplicates
                                        </option>
                                        <option value="update" <?= old('duplicate_handling') === 'update' ? 'selected' : '' ?>>
                                            Update existing records
                                        </option>
                                        <option value="error" <?= old('duplicate_handling') === 'error' ? 'selected' : '' ?>>
                                            Stop on duplicate
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        How to handle records that already exist in the database
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batch_size">Batch Size</label>
                                    <input type="number" class="form-control" id="batch_size" name="batch_size" 
                                           value="<?= old('batch_size', '1000') ?>" min="100" max="10000">
                                    <small class="form-text text-muted">
                                        Number of records to process at once (100-10000)
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-info" id="previewBtn">
                                <i class="fas fa-eye"></i> Preview Import
                            </button>
                            <button type="submit" class="btn btn-primary" id="importBtn">
                                <i class="fas fa-upload"></i> Start Import
                            </button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>

            <!-- Preview Results -->
            <div class="card shadow mb-4" id="previewCard" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Import Preview</h6>
                </div>
                <div class="card-body" id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Connection Status & Info -->
        <div class="col-lg-4">
            <!-- Connection Status -->
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
                    
                    <button type="button" class="btn btn-outline-primary btn-sm" id="testConnectionBtn">
                        <i class="fas fa-sync"></i> Test Connection
                    </button>
                </div>
            </div>

            <!-- Import Guidelines -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Import Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Important Notes:</h6>
                        <ul class="mb-0">
                            <li>Import processes data from FinPro fingerprint machine database</li>
                            <li>Large date ranges may take longer to process</li>
                            <li>Duplicate records are handled based on your selection</li>
                            <li>Student PIN mapping is required for proper attendance tracking</li>
                        </ul>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Recommended Steps:</h6>
                        <ol>
                            <li>Test database connection</li>
                            <li>Preview import to check data</li>
                            <li>Configure PIN mappings</li>
                            <li>Start the import process</li>
                        </ol>
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
        const btn = $(this);
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('fingerprint-bridge/ajax/test-connection') ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#connectionStatus').html(`
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Connected</strong>
                            <p class="mb-0">${response.message}</p>
                        </div>
                        <div class="mt-3">
                            <h6>Database Information:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Total Records:</strong> ${response.data.total_records.toLocaleString()}</li>
                                <li><strong>Unique PINs:</strong> ${response.data.unique_pins.toLocaleString()}</li>
                                <li><strong>Devices:</strong> ${response.data.unique_devices.toLocaleString()}</li>
                            </ul>
                        </div>
                    `);
                } else {
                    $('#connectionStatus').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Connection Failed</strong>
                            <p class="mb-0">${response.message}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#connectionStatus').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Error</strong>
                        <p class="mb-0">Failed to test connection</p>
                    </div>
                `);
            },
            complete: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Preview import button
    $('#previewBtn').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Loading Preview...').prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('fingerprint-bridge/ajax/preview-import') ?>',
            type: 'POST',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    let previewHtml = `
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Preview Results:</h6>
                            <ul class="mb-0">
                                <li><strong>Total Records:</strong> ${data.total_count.toLocaleString()}</li>
                                <li><strong>New Records:</strong> ${data.new_count.toLocaleString()}</li>
                                <li><strong>Existing Records:</strong> ${data.existing_count.toLocaleString()}</li>
                            </ul>
                        </div>
                    `;
                    
                    if (data.records.length > 0) {
                        previewHtml += `
                            <h6>Sample Records (first ${data.preview_count}):</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>PIN</th>
                                            <th>Scan Date</th>
                                            <th>Device</th>
                                            <th>Verify Mode</th>
                                            <th>In/Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.records.forEach(function(record) {
                            previewHtml += `
                                <tr>
                                    <td>${record.pin}</td>
                                    <td>${new Date(record.scan_date).toLocaleString()}</td>
                                    <td>${record.sn}</td>
                                    <td>${record.verifymode}</td>
                                    <td>${record.inoutmode}</td>
                                </tr>
                            `;
                        });
                        
                        previewHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                    
                    $('#previewContent').html(previewHtml);
                    $('#previewCard').show();
                } else {
                    alert('Preview failed: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to load preview');
            },
            complete: function() {
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Form validation
    $('#importForm').submit(function(e) {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        
        if (startDate > endDate) {
            e.preventDefault();
            alert('Start date cannot be later than end date');
            return false;
        }
        
        if (!confirm('Are you sure you want to start the import process?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
<?= $this->endSection() ?>
