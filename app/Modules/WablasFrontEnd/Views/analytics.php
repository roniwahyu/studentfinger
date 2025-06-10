<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line text-info"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="exportReport()">
                <i class="fas fa-download"></i> Export Report
            </button>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-calendar"></i> Date Range Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('wablas-frontend/analytics') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label for="date_from">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $filters['date_from'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $filters['date_to'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="event_type">Event Type</label>
                        <select class="form-control" id="event_type" name="event_type">
                            <option value="">All Events</option>
                            <option value="session_start" <?= $filters['event_type'] === 'session_start' ? 'selected' : '' ?>>Session Start</option>
                            <option value="session_end" <?= $filters['event_type'] === 'session_end' ? 'selected' : '' ?>>Session End</option>
                            <option value="attendance_alert" <?= $filters['event_type'] === 'attendance_alert' ? 'selected' : '' ?>>Attendance Alert</option>
                            <option value="manual_send" <?= $filters['event_type'] === 'manual_send' ? 'selected' : '' ?>>Manual Send</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Apply Filter
                        </button>
                        <a href="<?= base_url('wablas-frontend/analytics') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($analytics['total_messages'] ?? 0) ?>
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
                                Success Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($analytics['success_rate'] ?? 0, 1) ?>%
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg Daily Messages
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($analytics['avg_daily_messages'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-bar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Active Recipients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($analytics['unique_recipients'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Message Trends Chart -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Message Trends
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="messageTrendsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-pie"></i> Status Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="statusDistributionChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Event Type Analysis -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-chart-bar"></i> Event Type Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="eventTypeChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Recipients -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-trophy"></i> Top Recipients
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($analytics['top_recipients'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Messages</th>
                                        <th>Success Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics['top_recipients'] as $recipient): ?>
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold"><?= htmlspecialchars($recipient['parent_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($recipient['parent_phone']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?= $recipient['message_count'] ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $successRate = $recipient['success_rate'];
                                                $badgeColor = $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge badge-<?= $badgeColor ?>"><?= number_format($successRate, 1) ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-users fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted">No recipient data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table"></i> Daily Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($analytics['daily_stats'])): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dailyStatsTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Total Messages</th>
                                        <th>Sent</th>
                                        <th>Failed</th>
                                        <th>Success Rate</th>
                                        <th>Session Start</th>
                                        <th>Session End</th>
                                        <th>Attendance Alerts</th>
                                        <th>Manual Sends</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics['daily_stats'] as $stat): ?>
                                        <tr>
                                            <td><?= date('Y-m-d', strtotime($stat['date'])) ?></td>
                                            <td><?= number_format($stat['total']) ?></td>
                                            <td><span class="text-success"><?= number_format($stat['sent']) ?></span></td>
                                            <td><span class="text-danger"><?= number_format($stat['failed']) ?></span></td>
                                            <td>
                                                <?php
                                                $rate = $stat['total'] > 0 ? ($stat['sent'] / $stat['total']) * 100 : 0;
                                                $badgeColor = $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger');
                                                ?>
                                                <span class="badge badge-<?= $badgeColor ?>"><?= number_format($rate, 1) ?>%</span>
                                            </td>
                                            <td><?= number_format($stat['session_start'] ?? 0) ?></td>
                                            <td><?= number_format($stat['session_end'] ?? 0) ?></td>
                                            <td><?= number_format($stat['attendance_alert'] ?? 0) ?></td>
                                            <td><?= number_format($stat['manual_send'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-muted">No analytics data available</h5>
                            <p class="text-muted">Analytics will appear here once you start sending messages.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Message Trends Chart
const messageTrendsCtx = document.getElementById('messageTrendsChart').getContext('2d');
const messageTrendsChart = new Chart(messageTrendsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($analytics['daily_stats'] ?? [], 'date')) ?>,
        datasets: [{
            label: 'Total Messages',
            data: <?= json_encode(array_column($analytics['daily_stats'] ?? [], 'total')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
        }, {
            label: 'Sent Successfully',
            data: <?= json_encode(array_column($analytics['daily_stats'] ?? [], 'sent')) ?>,
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1
        }, {
            label: 'Failed',
            data: <?= json_encode(array_column($analytics['daily_stats'] ?? [], 'failed')) ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Distribution Chart
const statusDistributionCtx = document.getElementById('statusDistributionChart').getContext('2d');
const statusDistributionChart = new Chart(statusDistributionCtx, {
    type: 'doughnut',
    data: {
        labels: ['Sent', 'Failed', 'Pending'],
        datasets: [{
            data: [
                <?= $analytics['status_counts']['sent'] ?? 0 ?>,
                <?= $analytics['status_counts']['failed'] ?? 0 ?>,
                <?= $analytics['status_counts']['pending'] ?? 0 ?>
            ],
            backgroundColor: [
                'rgb(54, 162, 235)',
                'rgb(255, 99, 132)',
                'rgb(255, 205, 86)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Event Type Chart
const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
const eventTypeChart = new Chart(eventTypeCtx, {
    type: 'bar',
    data: {
        labels: ['Session Start', 'Session End', 'Attendance Alert', 'Manual Send'],
        datasets: [{
            label: 'Messages',
            data: [
                <?= $analytics['event_counts']['session_start'] ?? 0 ?>,
                <?= $analytics['event_counts']['session_end'] ?? 0 ?>,
                <?= $analytics['event_counts']['attendance_alert'] ?? 0 ?>,
                <?= $analytics['event_counts']['manual_send'] ?? 0 ?>
            ],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Export report function
function exportReport() {
    const dateFrom = document.getElementById('date_from').value;
    const dateTo = document.getElementById('date_to').value;
    const eventType = document.getElementById('event_type').value;
    
    let url = '<?= base_url('wablas-frontend/analytics/export') ?>?';
    if (dateFrom) url += 'date_from=' + dateFrom + '&';
    if (dateTo) url += 'date_to=' + dateTo + '&';
    if (eventType) url += 'event_type=' + eventType + '&';
    
    window.open(url, '_blank');
}

// Initialize DataTable
$(document).ready(function() {
    if ($('#dailyStatsTable').length) {
        $('#dailyStatsTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [] }
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
