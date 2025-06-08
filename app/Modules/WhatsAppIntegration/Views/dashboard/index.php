<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title"><?= $title ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                        <li class="breadcrumb-item active">WhatsApp Gateway</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Total Devices">Total Devices</h5>
                            <h3 class="my-2 py-1"><?= $stats['totalDevices'] ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i></span>
                                <?= $stats['activeDevices'] ?> Active
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <div id="total-devices-chart" data-colors="#727cf5"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Messages Sent Today">Sent Today</h5>
                            <h3 class="my-2 py-1"><?= $stats['sentToday'] ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2"><i class="mdi mdi-arrow-up-bold"></i></span>
                                Messages
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <div id="sent-messages-chart" data-colors="#0acf97"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Pending Messages">Pending</h5>
                            <h3 class="my-2 py-1"><?= $stats['pendingMessages'] ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-warning me-2"><i class="mdi mdi-arrow-down-bold"></i></span>
                                In Queue
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <div id="pending-messages-chart" data-colors="#ffbc00"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Failed Messages">Failed</h5>
                            <h3 class="my-2 py-1"><?= $stats['failedMessages'] ?></h3>
                            <p class="mb-0 text-muted">
                                <span class="text-danger me-2"><i class="mdi mdi-arrow-down-bold"></i></span>
                                Messages
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <div id="failed-messages-chart" data-colors="#fa5c7c"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Device Status -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">Device Status</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($devices)): ?>
                        <?php foreach ($devices as $device): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-1"><?= esc($device['device_name']) ?></h5>
                                    <p class="mb-0 text-muted"><?= esc($device['device_type']) ?></p>
                                </div>
                                <div class="text-end">
                                    <?php 
                                    $status = $deviceStatus[$device['id']] ?? ['online' => false];
                                    $statusClass = $status['online'] ? 'success' : 'danger';
                                    $statusText = $status['online'] ? 'Online' : 'Offline';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                    <br>
                                    <small class="text-muted">
                                        <?= isset($device['last_activity']) ? date('H:i', strtotime($device['last_activity'])) : 'Never' ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="mdi mdi-cellphone-off h1 text-muted"></i>
                            <p class="text-muted">No devices configured</p>
                            <a href="<?= base_url('whatsappintegration/devices/create') ?>" class="btn btn-primary">
                                Add Device
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">Recent Messages</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentMessages)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentMessages as $message): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0"><?= esc($message['phone_number']) ?></h6>
                                                        <small class="text-muted"><?= esc($message['device_name'] ?? 'Unknown Device') ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $statusMap = [
                                                    0 => ['class' => 'warning', 'text' => 'Pending'],
                                                    1 => ['class' => 'success', 'text' => 'Sent'],
                                                    2 => ['class' => 'danger', 'text' => 'Failed'],
                                                    3 => ['class' => 'info', 'text' => 'Received']
                                                ];
                                                $status = $statusMap[$message['status']] ?? ['class' => 'secondary', 'text' => 'Unknown'];
                                                ?>
                                                <span class="badge bg-<?= $status['class'] ?>"><?= $status['text'] ?></span>
                                            </td>
                                            <td>
                                                <small><?= date('H:i', strtotime($message['created_at'])) ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="mdi mdi-message-outline h1 text-muted"></i>
                            <p class="text-muted">No recent messages</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="<?= base_url('whatsappintegration/send') ?>" class="btn btn-primary w-100">
                                <i class="mdi mdi-send me-1"></i>
                                Send Message
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= base_url('whatsappintegration/bulk') ?>" class="btn btn-info w-100">
                                <i class="mdi mdi-send-outline me-1"></i>
                                Bulk Message
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= base_url('whatsappintegration/devices') ?>" class="btn btn-success w-100">
                                <i class="mdi mdi-cellphone me-1"></i>
                                Manage Devices
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="<?= base_url('whatsappintegration/queue') ?>" class="btn btn-warning w-100">
                                <i class="mdi mdi-clock-outline me-1"></i>
                                Message Queue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh dashboard every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);

// Process queue button
function processQueue() {
    if (confirm('Process message queue now?')) {
        fetch('<?= base_url('whatsappintegration/process-queue') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Queue processed successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error processing queue');
        });
    }
}
</script>
<?= $this->endSection() ?>
