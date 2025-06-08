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
            Wablas Integration Dashboard
        </h1>
        <div class="btn-group">
            <a href="<?= base_url('wablas/messages/send') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-paper-plane"></i> Send Message
            </a>
            <a href="<?= base_url('wablas/devices') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-mobile-alt"></i> Manage Devices
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <!-- Devices Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Devices
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['devices']['active'] ?> / <?= $stats['devices']['total'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= $stats['devices']['connected'] ?> connected
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Today Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Messages Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['messages']['today']['total'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= $stats['messages']['today']['success_rate'] ?>% success rate
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contacts Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['contacts']['total'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= $stats['contacts']['whatsapp_active'] ?> WhatsApp active
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-address-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scheduled Messages Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Scheduled
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['schedules']['pending'] ?>
                            </div>
                            <div class="text-xs text-muted">
                                <?= $stats['schedules']['total'] ?> total schedules
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="row">
        <!-- Message Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Message Activity</h6>
                    <div class="dropdown no-arrow">
                        <select id="chartPeriod" class="form-control form-control-sm" style="width: auto;">
                            <option value="week">Last 7 Days</option>
                            <option value="month">Last 30 Days</option>
                            <option value="year">Last Year</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="messageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Status -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Status</h6>
                </div>
                <div class="card-body">
                    <div id="deviceStatusContainer">
                        <?php if (!empty($device_status)): ?>
                            <?php foreach ($device_status as $device): ?>
                                <div class="device-status-item mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= esc($device['device_name']) ?></h6>
                                            <small class="text-muted"><?= esc($device['phone_number']) ?></small>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-<?= $device['connection_status'] === 'connected' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($device['connection_status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-<?= $device['quota_percentage'] > 80 ? 'danger' : ($device['quota_percentage'] > 60 ? 'warning' : 'success') ?>" 
                                             style="width: <?= $device['quota_percentage'] ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        Quota: <?= $device['quota_used'] ?>/<?= $device['quota_limit'] ?> (<?= $device['quota_percentage'] ?>%)
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-mobile-alt fa-3x mb-3"></i>
                                <p>No devices configured</p>
                                <a href="<?= base_url('wablas/devices/create') ?>" class="btn btn-primary btn-sm">
                                    Add Device
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages and Activity -->
    <div class="row">
        <!-- Recent Messages -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Messages</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Phone</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_messages)): ?>
                                    <?php foreach ($recent_messages as $message): ?>
                                        <tr>
                                            <td>
                                                <small><?= esc($message['phone_number']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary badge-sm">
                                                    <?= ucfirst($message['message_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $message['status'] === 'sent' ? 'success' : ($message['status'] === 'failed' ? 'danger' : 'warning') ?> badge-sm">
                                                    <?= ucfirst($message['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('H:i', strtotime($message['created_at'])) ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No recent messages
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <a href="<?= base_url('wablas/messages/history') ?>" class="btn btn-outline-primary btn-sm">
                            View All Messages
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div id="recentActivityContainer">
                        <?php if (!empty($stats['recent_activity'])): ?>
                            <?php foreach ($stats['recent_activity'] as $activity): ?>
                                <div class="activity-item mb-3">
                                    <div class="d-flex">
                                        <div class="activity-icon mr-3">
                                            <i class="fas fa-<?= $activity['type'] === 'message' ? 'comment' : 'cog' ?> text-<?= $activity['status'] === 'error' ? 'danger' : 'success' ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= esc($activity['action']) ?></h6>
                                            <p class="mb-1 text-muted small"><?= esc($activity['description']) ?></p>
                                            <small class="text-muted">
                                                <?= date('M j, H:i', strtotime($activity['timestamp'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh toggle -->
<div class="position-fixed" style="bottom: 20px; right: 20px;">
    <div class="custom-control custom-switch">
        <input type="checkbox" class="custom-control-input" id="autoRefresh" checked>
        <label class="custom-control-label" for="autoRefresh">Auto Refresh</label>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    let messageChart;
    let autoRefreshInterval;
    
    // Initialize message chart
    initMessageChart();
    
    // Auto refresh functionality
    $('#autoRefresh').change(function() {
        if ($(this).is(':checked')) {
            startAutoRefresh();
        } else {
            stopAutoRefresh();
        }
    });
    
    // Chart period change
    $('#chartPeriod').change(function() {
        updateMessageChart();
    });
    
    // Start auto refresh by default
    startAutoRefresh();
    
    function initMessageChart() {
        const ctx = document.getElementById('messageChart').getContext('2d');
        messageChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Sent',
                    data: [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.3
                }, {
                    label: 'Delivered',
                    data: [],
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.3
                }, {
                    label: 'Failed',
                    data: [],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        updateMessageChart();
    }
    
    function updateMessageChart() {
        const period = $('#chartPeriod').val();
        
        $.get('<?= base_url('wablas/dashboard/message-chart-data') ?>', {
            period: period
        }).done(function(response) {
            if (response.success) {
                messageChart.data.labels = response.data.labels;
                messageChart.data.datasets[0].data = response.data.datasets.sent;
                messageChart.data.datasets[1].data = response.data.datasets.delivered;
                messageChart.data.datasets[2].data = response.data.datasets.failed;
                messageChart.update();
            }
        });
    }
    
    function refreshDashboard() {
        // Refresh statistics
        $.get('<?= base_url('wablas/dashboard/stats') ?>').done(function(response) {
            if (response.success) {
                updateStatistics(response.data);
            }
        });
        
        // Refresh device status
        $.get('<?= base_url('wablas/dashboard/device-status') ?>').done(function(response) {
            if (response.success) {
                updateDeviceStatus(response.data);
            }
        });
        
        // Refresh recent messages
        $.get('<?= base_url('wablas/dashboard/recent-messages') ?>').done(function(response) {
            if (response.success) {
                updateRecentMessages(response.data);
            }
        });
    }
    
    function updateStatistics(stats) {
        // Update device stats
        $('.border-left-primary .h5').text(stats.devices.active + ' / ' + stats.devices.total);
        $('.border-left-primary .text-xs.text-muted').text(stats.devices.connected + ' connected');
        
        // Update message stats
        $('.border-left-success .h5').text(stats.messages.today.total);
        $('.border-left-success .text-xs.text-muted').text(stats.messages.today.success_rate + '% success rate');
        
        // Update contact stats
        $('.border-left-info .h5').text(stats.contacts.total);
        $('.border-left-info .text-xs.text-muted').text(stats.contacts.whatsapp_active + ' WhatsApp active');
        
        // Update schedule stats
        $('.border-left-warning .h5').text(stats.schedules.pending);
        $('.border-left-warning .text-xs.text-muted').text(stats.schedules.total + ' total schedules');
    }
    
    function updateDeviceStatus(devices) {
        let html = '';
        
        if (devices.length > 0) {
            devices.forEach(function(device) {
                const statusClass = device.status === 'connected' ? 'success' : 'danger';
                const quotaClass = device.quota_percentage > 80 ? 'danger' : (device.quota_percentage > 60 ? 'warning' : 'success');
                
                html += `
                    <div class="device-status-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${device.name}</h6>
                                <small class="text-muted">${device.phone}</small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-${statusClass}">
                                    ${device.status.charAt(0).toUpperCase() + device.status.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-${quotaClass}" style="width: ${device.quota_percentage}%"></div>
                        </div>
                        <small class="text-muted">
                            Quota: ${device.quota_used}/${device.quota_limit} (${device.quota_percentage}%)
                        </small>
                    </div>
                `;
            });
        } else {
            html = `
                <div class="text-center text-muted">
                    <i class="fas fa-mobile-alt fa-3x mb-3"></i>
                    <p>No devices configured</p>
                    <a href="<?= base_url('wablas/devices/create') ?>" class="btn btn-primary btn-sm">
                        Add Device
                    </a>
                </div>
            `;
        }
        
        $('#deviceStatusContainer').html(html);
    }
    
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(refreshDashboard, 30000); // Refresh every 30 seconds
    }
    
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }
});
</script>
<?= $this->endSection() ?>
