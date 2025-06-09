<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    :root {
        --wablas-primary: #25D366;
        --wablas-secondary: #128C7E;
        --wablas-accent: #075E54;
        --wablas-light: #DCF8C6;
        --wablas-dark: #34495e;
        --wablas-danger: #e74c3c;
        --wablas-warning: #f39c12;
        --wablas-info: #3498db;
        --wablas-success: #27ae60;
        --wablas-gradient: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    }

    .wablas-header {
        background: var(--wablas-gradient);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
    }

    .wablas-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border: none;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .wablas-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    .stat-card {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        border-left: 4px solid var(--wablas-primary);
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--wablas-primary);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .integration-badge {
        background: var(--wablas-gradient);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 1rem;
    }

    .device-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-connected { background-color: var(--wablas-success); }
    .status-disconnected { background-color: var(--wablas-danger); }
    .status-connecting { background-color: var(--wablas-warning); }

    .message-item {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s ease;
    }

    .message-item:hover {
        background-color: #f8f9fa;
    }

    .message-status {
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-sent { background-color: #d4edda; color: #155724; }
    .status-delivered { background-color: #cce7ff; color: #004085; }
    .status-read { background-color: #d1ecf1; color: #0c5460; }
    .status-failed { background-color: #f8d7da; color: #721c24; }

    .quick-action-btn {
        background: var(--wablas-gradient);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        margin: 0.25rem;
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(37, 211, 102, 0.4);
        color: white;
        text-decoration: none;
    }

    .attendance-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
    }

    .notification-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        border-left: 4px solid var(--wablas-primary);
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .integration-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        border-top: 3px solid var(--wablas-primary);
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--wablas-primary);
        margin-bottom: 0.5rem;
    }

    .metric-label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .wablas-nav-tabs {
        border: none;
        margin-bottom: 2rem;
    }

    .wablas-nav-tabs .nav-link {
        border: none;
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 25px;
        margin-right: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .wablas-nav-tabs .nav-link.active {
        background: var(--wablas-gradient);
        color: white;
        box-shadow: 0 3px 15px rgba(37, 211, 102, 0.3);
    }

    .real-time-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #28a745;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .pulse {
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(0.95); opacity: 1; }
        70% { transform: scale(1); opacity: 0.7; }
        100% { transform: scale(0.95); opacity: 1; }
    }

    .floating-action-btn {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 60px;
        height: 60px;
        background: var(--wablas-gradient);
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 5px 20px rgba(37, 211, 102, 0.4);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .floating-action-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
    }

    @media (max-width: 768px) {
        .integration-metrics {
            grid-template-columns: 1fr;
        }
        
        .wablas-header {
            padding: 1rem 0;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- WablasFrontEnd Header -->
<div class="wablas-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fab fa-whatsapp me-3"></i>
                    WablasFrontEnd
                </h1>
                <p class="mb-0 opacity-75">
                    Integrated WhatsApp Communication for Student Attendance Management
                </p>
                <div class="real-time-indicator mt-2">
                    <div class="pulse"></div>
                    Real-time Integration Active
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="integration-badge">
                    <i class="fas fa-link me-2"></i>
                    Fingerprint Integration
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Integration Metrics -->
<div class="integration-metrics">
    <div class="metric-card">
        <div class="metric-value"><?= number_format($stats['integration']['notifications_sent_today']) ?></div>
        <div class="metric-label">Notifications Today</div>
    </div>
    <div class="metric-card">
        <div class="metric-value"><?= number_format($stats['integration']['attendance_notifications']) ?></div>
        <div class="metric-label">Attendance Alerts</div>
    </div>
    <div class="metric-card">
        <div class="metric-value"><?= $stats['integration']['success_rate'] ?>%</div>
        <div class="metric-label">Success Rate</div>
    </div>
    <div class="metric-card">
        <div class="metric-value"><?= count($devices) ?></div>
        <div class="metric-label">Active Devices</div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs wablas-nav-tabs" id="wablasTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
            <i class="fas fa-chart-line me-2"></i>Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
            <i class="fas fa-calendar-check me-2"></i>Attendance
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">
            <i class="fas fa-comments me-2"></i>Messages
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="devices-tab" data-bs-toggle="tab" data-bs-target="#devices" type="button" role="tab">
            <i class="fas fa-mobile-alt me-2"></i>Devices
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="wablasTabContent">
    <!-- Overview Tab -->
    <div class="tab-pane fade show active" id="overview" role="tabpanel">
        <div class="row">
            <!-- Attendance Summary -->
            <div class="col-lg-8 mb-4">
                <div class="attendance-summary">
                    <h4 class="mb-3">
                        <i class="fas fa-users me-2"></i>
                        Today's Attendance Summary
                    </h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-number"><?= $attendance_summary['today']['unique_users'] ?? 0 ?></div>
                            <div class="stat-label">Present</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-number"><?= count($attendance_summary['absent_students'] ?? []) ?></div>
                            <div class="stat-label">Absent</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-number"><?= count($attendance_summary['late_students'] ?? []) ?></div>
                            <div class="stat-label">Late</div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-number"><?= $stats['integration']['automated_messages'] ?></div>
                            <div class="stat-label">Auto Messages</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="wablas-card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-bolt me-2"></i>
                            Quick Actions
                        </h5>
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('wablas-frontend/messages') ?>" class="quick-action-btn">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </a>
                            <a href="<?= base_url('wablas-frontend/attendance') ?>" class="quick-action-btn">
                                <i class="fas fa-bell me-2"></i>Send Attendance Alert
                            </a>
                            <a href="<?= base_url('wablas-frontend/contacts') ?>" class="quick-action-btn">
                                <i class="fas fa-users me-2"></i>Manage Contacts
                            </a>
                            <a href="<?= base_url('wablas-frontend/reports') ?>" class="quick-action-btn">
                                <i class="fas fa-chart-bar me-2"></i>View Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="wablas-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-clock me-2"></i>
                            Recent Messages
                        </h5>
                        <div class="message-list">
                            <?php if (!empty($recent_messages)): ?>
                                <?php foreach (array_slice($recent_messages, 0, 5) as $message): ?>
                                    <div class="message-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?= esc($message['phone_number']) ?></strong>
                                                <p class="mb-1 text-muted"><?= esc(substr($message['message_content'], 0, 50)) ?>...</p>
                                                <small class="text-muted"><?= date('H:i A', strtotime($message['created_at'])) ?></small>
                                            </div>
                                            <span class="message-status status-<?= esc($message['status']) ?>">
                                                <?= ucfirst(esc($message['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No recent messages</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Notifications -->
            <div class="col-lg-6 mb-4">
                <div class="wablas-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Student Notifications
                        </h5>
                        <div class="notification-list">
                            <?php if (!empty($student_notifications)): ?>
                                <?php foreach (array_slice($student_notifications, 0, 5) as $notification): ?>
                                    <div class="notification-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= esc($notification['student_name'] ?? 'Unknown Student') ?></strong>
                                                <p class="mb-0 text-muted small"><?= esc($notification['phone_number']) ?></p>
                                            </div>
                                            <small class="text-muted"><?= date('H:i', strtotime($notification['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No notifications today</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Other tabs content will be loaded via AJAX -->
    <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading attendance integration...</p>
        </div>
    </div>
    
    <div class="tab-pane fade" id="messages" role="tabpanel">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading message center...</p>
        </div>
    </div>
    
    <div class="tab-pane fade" id="devices" role="tabpanel">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading device management...</p>
        </div>
    </div>
</div>

<!-- Floating Action Button -->
<button class="floating-action-btn" onclick="showQuickSendModal()" title="Quick Send Message">
    <i class="fas fa-plus"></i>
</button>

<!-- Quick Send Modal -->
<div class="modal fade" id="quickSendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-whatsapp me-2"></i>
                    Quick Send Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickSendForm">
                    <div class="mb-3">
                        <label for="quickPhoneNumber" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="quickPhoneNumber" placeholder="628xxxxxxxxx" required>
                    </div>
                    <div class="mb-3">
                        <label for="quickMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="quickMessage" rows="4" placeholder="Type your message here..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="quickDevice" class="form-label">Device</label>
                        <select class="form-select" id="quickDevice" required>
                            <option value="">Select Device</option>
                            <?php foreach ($devices as $device): ?>
                                <option value="<?= esc($device['device_id']) ?>"><?= esc($device['device_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="sendQuickMessage()">
                    <i class="fas fa-paper-plane me-2"></i>Send Message
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Real-time updates
    let updateInterval;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize real-time updates
        startRealTimeUpdates();

        // Initialize tab loading
        initializeTabLoading();

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function startRealTimeUpdates() {
        updateInterval = setInterval(function() {
            updateDashboardStats();
            updateRecentMessages();
            updateDeviceStatus();
        }, 30000); // Update every 30 seconds
    }

    function updateDashboardStats() {
        fetch('<?= base_url('wablas-frontend/api/stats') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatsDisplay(data.data);
                }
            })
            .catch(error => console.error('Error updating stats:', error));
    }

    function updateRecentMessages() {
        fetch('<?= base_url('wablas-frontend/api/recent-messages') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateMessagesDisplay(data.data);
                }
            })
            .catch(error => console.error('Error updating messages:', error));
    }

    function updateDeviceStatus() {
        fetch('<?= base_url('wablas-frontend/api/device-status') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateDevicesDisplay(data.data);
                }
            })
            .catch(error => console.error('Error updating devices:', error));
    }

    function updateStatsDisplay(stats) {
        // Update metric cards
        document.querySelectorAll('.metric-value').forEach((element, index) => {
            const values = [
                stats.integration.notifications_sent_today,
                stats.integration.attendance_notifications,
                stats.integration.success_rate + '%',
                stats.devices.active_count
            ];
            if (values[index] !== undefined) {
                element.textContent = values[index];
            }
        });
    }

    function updateMessagesDisplay(messages) {
        const messageList = document.querySelector('.message-list');
        if (messageList && messages.length > 0) {
            messageList.innerHTML = messages.slice(0, 5).map(message => `
                <div class="message-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${escapeHtml(message.phone_number)}</strong>
                            <p class="mb-1 text-muted">${escapeHtml(message.message_content.substring(0, 50))}...</p>
                            <small class="text-muted">${formatTime(message.created_at)}</small>
                        </div>
                        <span class="message-status status-${message.status}">
                            ${message.status.charAt(0).toUpperCase() + message.status.slice(1)}
                        </span>
                    </div>
                </div>
            `).join('');
        }
    }

    function updateDevicesDisplay(devices) {
        // Update device status indicators
        devices.forEach(device => {
            const indicator = document.querySelector(`[data-device-id="${device.device_id}"]`);
            if (indicator) {
                indicator.className = `status-indicator status-${device.status}`;
            }
        });
    }

    function initializeTabLoading() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function(event) {
                const targetTab = event.target.getAttribute('data-bs-target');
                loadTabContent(targetTab);
            });
        });
    }

    function loadTabContent(tabId) {
        const tabPane = document.querySelector(tabId);
        if (!tabPane || tabPane.dataset.loaded === 'true') return;

        const tabName = tabId.replace('#', '');
        const loadingHtml = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading ${tabName}...</p>
            </div>
        `;

        tabPane.innerHTML = loadingHtml;

        fetch(`<?= base_url('wablas-frontend/api/tab-content/') ?>${tabName}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tabPane.innerHTML = data.html;
                    tabPane.dataset.loaded = 'true';
                } else {
                    tabPane.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading ${tabName}: ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading tab content:', error);
                tabPane.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Failed to load ${tabName} content
                    </div>
                `;
            });
    }

    function showQuickSendModal() {
        const modal = new bootstrap.Modal(document.getElementById('quickSendModal'));
        modal.show();
    }

    function sendQuickMessage() {
        const form = document.getElementById('quickSendForm');
        const formData = new FormData();

        formData.append('phone_number', document.getElementById('quickPhoneNumber').value);
        formData.append('message', document.getElementById('quickMessage').value);
        formData.append('device_id', document.getElementById('quickDevice').value);

        // Show loading state
        const sendButton = event.target;
        const originalText = sendButton.innerHTML;
        sendButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        sendButton.disabled = true;

        fetch('<?= base_url('wablas-frontend/api/send-message') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Message sent successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('quickSendModal')).hide();
                form.reset();
                updateRecentMessages(); // Refresh messages
            } else {
                showToast('Failed to send message: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            showToast('Error sending message', 'error');
        })
        .finally(() => {
            sendButton.innerHTML = originalText;
            sendButton.disabled = false;
        });
    }

    function showToast(message, type = 'info') {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        // Add to toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        // Show toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });
</script>
<?= $this->endSection() ?>
