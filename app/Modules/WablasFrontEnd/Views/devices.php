<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-mobile-alt text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" onclick="scanQRCode()">
                <i class="fas fa-qrcode"></i> Scan QR Code
            </button>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Device Status Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-mobile-alt"></i> Device Status
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($device_status['connection_status'] === 'connected'): ?>
                        <div class="alert alert-success" role="alert">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-check-circle"></i> Device Connected
                                    </h5>
                                    <p class="mb-0">
                                        <strong>Device ID:</strong> <?= htmlspecialchars($device_status['device_id'] ?? 'N/A') ?><br>
                                        <strong>Device Name:</strong> <?= htmlspecialchars($device_status['device_name'] ?? 'Unknown') ?><br>
                                        <strong>Last Check:</strong> <?= $device_status['last_check'] ? date('Y-m-d H:i:s', strtotime($device_status['last_check'])) : 'Never' ?><br>
                                        <strong>Quota Remaining:</strong> <?= number_format($device_status['quota_remaining'] ?? 0) ?> messages
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-mobile-alt fa-4x text-success"></i>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" role="alert">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="alert-heading">
                                        <i class="fas fa-exclamation-triangle"></i> Device Not Connected
                                    </h5>
                                    <p class="mb-0">
                                        Your WhatsApp device is not connected. Please scan the QR code to connect your device.
                                        <?php if (!empty($device_status['error_message'])): ?>
                                            <br><strong>Error:</strong> <?= htmlspecialchars($device_status['error_message']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-mobile-alt fa-4x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary btn-block" onclick="checkDeviceStatus()">
                                <i class="fas fa-sync"></i> Refresh Status
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success btn-block" onclick="scanQRCode()">
                                <i class="fas fa-qrcode"></i> Scan QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Section -->
    <div class="row mb-4" id="qrCodeSection" style="display: none;">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-qrcode"></i> QR Code Scanner
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div id="qrCodeContainer">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading QR Code...</span>
                        </div>
                        <p class="mt-2">Generating QR Code...</p>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Scan this QR code with your WhatsApp mobile app to connect your device.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection History -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Connection History
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($connection_history)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Status</th>
                                        <th>Device Info</th>
                                        <th>Error Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($connection_history as $history): ?>
                                        <tr>
                                            <td>
                                                <?= date('Y-m-d H:i:s', strtotime($history['created_at'])) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusColors = [
                                                    'connected' => 'success',
                                                    'disconnected' => 'secondary',
                                                    'connecting' => 'warning',
                                                    'error' => 'danger'
                                                ];
                                                $statusColor = $statusColors[$history['connection_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?= $statusColor ?>">
                                                    <?= ucfirst($history['connection_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($history['device_name'])): ?>
                                                    <strong><?= htmlspecialchars($history['device_name']) ?></strong><br>
                                                    <small class="text-muted">ID: <?= htmlspecialchars($history['device_id'] ?? 'N/A') ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">No device info</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($history['error_message'])): ?>
                                                    <span class="text-danger"><?= htmlspecialchars($history['error_message']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">No Connection History</h5>
                            <p class="text-muted">Connection history will appear here once you start using the device.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function scanQRCode() {
    $('#qrCodeSection').show();
    
    // Simulate QR code generation
    setTimeout(function() {
        $('#qrCodeContainer').html(`
            <div class="qr-code-placeholder bg-light border p-4" style="width: 300px; height: 300px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <i class="fas fa-qrcode fa-5x text-muted"></i>
                    <p class="mt-2 text-muted">QR Code would appear here</p>
                    <small class="text-muted">Integration with WABLAS QR API needed</small>
                </div>
            </div>
        `);
    }, 2000);
}

function checkDeviceStatus() {
    const btn = $('button:contains("Refresh Status")');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Checking...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/devices/status') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload(); // Refresh page to show updated status
            } else {
                alert('Failed to check device status: ' + response.message);
            }
        },
        error: function() {
            alert('Error checking device status. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

// Auto-refresh status every 30 seconds
setInterval(function() {
    checkDeviceStatus();
}, 30000);
</script>
<?= $this->endSection() ?>
