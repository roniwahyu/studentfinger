<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-address-book text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#bulkMessageModal">
                <i class="fab fa-whatsapp"></i> Send Bulk Message
            </button>
            <a href="<?= base_url('classroom-notifications/contacts/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Contact
            </a>
            <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Connection Status Alert -->
    <?php if ($connection_status['connection_status'] === 'connected'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <strong>WhatsApp Connected!</strong> Device: <?= htmlspecialchars($connection_status['device_name'] ?? 'Unknown') ?>
            | Quota: <?= number_format($connection_status['quota_remaining'] ?? 0) ?> messages
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>WhatsApp Not Connected!</strong> <?= htmlspecialchars($connection_status['error_message'] ?? 'Please check your connection.') ?>
            <a href="<?= base_url('classroom-notifications/settings') ?>" class="btn btn-sm btn-outline-warning ml-2">
                Check Settings
            </a>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_contacts']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Active Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['active_contacts']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Primary Contacts
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['primary_contacts']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
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
                                Notification Enabled
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['notification_enabled']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters & Search</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('classroom-notifications/contacts') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="contact_type">Contact Type</label>
                            <select name="contact_type" id="contact_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="father" <?= $filters['contact_type'] === 'father' ? 'selected' : '' ?>>Father</option>
                                <option value="mother" <?= $filters['contact_type'] === 'mother' ? 'selected' : '' ?>>Mother</option>
                                <option value="guardian" <?= $filters['contact_type'] === 'guardian' ? 'selected' : '' ?>>Guardian</option>
                                <option value="emergency" <?= $filters['contact_type'] === 'emergency' ? 'selected' : '' ?>>Emergency</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="">All Status</option>
                                <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                                   placeholder="Search by name, phone, or student name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Parent Contacts</h6>
            <div>
                <button type="button" class="btn btn-sm btn-info" onclick="selectAll()">
                    <i class="fas fa-check-square"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="clearSelection()">
                    <i class="fas fa-square"></i> Clear
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($contacts)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                                </th>
                                <th>Contact Info</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Phone Numbers</th>
                                <th>Status</th>
                                <th>Notifications</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="contact-checkbox" value="<?= $contact['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="font-weight-bold">
                                                    <?= htmlspecialchars($contact['contact_name']) ?>
                                                    <?php if ($contact['is_primary']): ?>
                                                        <span class="badge badge-warning badge-sm ml-1">Primary</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($contact['relationship'] ?? $contact['contact_type']) ?>
                                                </small>
                                                <?php if (!empty($contact['email'])): ?>
                                                    <br><small class="text-info">
                                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($contact['email']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">
                                            <?= htmlspecialchars($contact['firstname'] . ' ' . $contact['lastname']) ?>
                                        </div>
                                        <small class="text-muted">
                                            ID: <?= $contact['student_id'] ?>
                                            <?php if (!empty($contact['admission_no'])): ?>
                                                | <?= htmlspecialchars($contact['admission_no']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $typeColors = [
                                            'father' => 'primary',
                                            'mother' => 'info',
                                            'guardian' => 'success',
                                            'emergency' => 'warning'
                                        ];
                                        $typeColor = $typeColors[$contact['contact_type']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $typeColor ?>">
                                            <?= ucfirst($contact['contact_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Phone:</strong> <?= htmlspecialchars($contact['phone_number']) ?>
                                        </div>
                                        <?php if ($contact['whatsapp_number'] && $contact['whatsapp_number'] !== $contact['phone_number']): ?>
                                            <div>
                                                <strong>WhatsApp:</strong> <?= htmlspecialchars($contact['whatsapp_number']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($contact['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($contact['receive_notifications']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-bell"></i> Enabled
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-bell-slash"></i> Disabled
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-success" onclick="sendTestMessage(<?= $contact['id'] ?>)" title="Send Test Message">
                                                <i class="fab fa-whatsapp"></i>
                                            </button>
                                            <a href="<?= base_url('classroom-notifications/contacts/edit/' . $contact['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (!$contact['is_primary']): ?>
                                                <button class="btn btn-sm btn-info" onclick="setPrimary(<?= $contact['id'] ?>, <?= $contact['student_id'] ?>)" title="Set as Primary">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteContact(<?= $contact['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-address-book fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No contacts found</h5>
                    <p class="text-muted">Add parent contacts to start sending notifications.</p>
                    <a href="<?= base_url('classroom-notifications/contacts/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Contact
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bulk Message Modal -->
<div class="modal fade" id="bulkMessageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Bulk WhatsApp Message</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkMessageForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Selected Contacts</label>
                        <div id="selectedContactsList" class="border rounded p-2 bg-light">
                            <small class="text-muted">No contacts selected</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bulkMessage">Message *</label>
                        <textarea class="form-control" id="bulkMessage" name="message" rows="6" required 
                                  placeholder="Enter your WhatsApp message here..."></textarea>
                        <small class="form-text text-muted">
                            You can use variables like {parent_name}, {student_name}, etc.
                        </small>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="logBulkMessage" name="log_message" checked>
                        <label class="form-check-label" for="logBulkMessage">
                            Log this message in notification history
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fab fa-whatsapp"></i> Send Messages
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedContacts = [];

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const contactCheckboxes = document.querySelectorAll('.contact-checkbox');
    
    contactCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelectedContacts();
}

function selectAll() {
    document.getElementById('selectAllCheckbox').checked = true;
    toggleSelectAll();
}

function clearSelection() {
    document.getElementById('selectAllCheckbox').checked = false;
    toggleSelectAll();
}

function updateSelectedContacts() {
    const checkboxes = document.querySelectorAll('.contact-checkbox:checked');
    selectedContacts = Array.from(checkboxes).map(cb => cb.value);
    
    const listDiv = document.getElementById('selectedContactsList');
    if (selectedContacts.length === 0) {
        listDiv.innerHTML = '<small class="text-muted">No contacts selected</small>';
    } else {
        listDiv.innerHTML = `<span class="badge badge-primary">${selectedContacts.length} contacts selected</span>`;
    }
}

// Update selected contacts when individual checkboxes change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('contact-checkbox')) {
        updateSelectedContacts();
    }
});

function sendTestMessage(contactId) {
    const message = prompt('Enter test message:');
    if (!message) return;
    
    $.ajax({
        url: '<?= base_url('classroom-notifications/ajax/send-test-message') ?>',
        type: 'POST',
        data: {
            contact_id: contactId,
            message: message
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Test message sent successfully!');
            } else {
                alert('Failed to send test message: ' + response.message);
            }
        },
        error: function() {
            alert('Error sending test message');
        }
    });
}

function setPrimary(contactId, studentId) {
    if (confirm('Set this contact as primary for the student?')) {
        $.ajax({
            url: '<?= base_url('classroom-notifications/contacts/set-primary') ?>',
            type: 'POST',
            data: {
                contact_id: contactId,
                student_id: studentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to set primary contact: ' + response.message);
                }
            },
            error: function() {
                alert('Error setting primary contact');
            }
        });
    }
}

function deleteContact(contactId) {
    if (confirm('Are you sure you want to delete this contact?')) {
        $.ajax({
            url: '<?= base_url('classroom-notifications/contacts/delete') ?>/' + contactId,
            type: 'POST',
            success: function() {
                location.reload();
            },
            error: function() {
                alert('Error deleting contact');
            }
        });
    }
}

// Bulk message form submission
$('#bulkMessageForm').submit(function(e) {
    e.preventDefault();
    
    if (selectedContacts.length === 0) {
        alert('Please select at least one contact');
        return;
    }
    
    const message = $('#bulkMessage').val();
    if (!message.trim()) {
        alert('Please enter a message');
        return;
    }
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('classroom-notifications/ajax/send-bulk-message') ?>',
        type: 'POST',
        data: {
            contact_ids: selectedContacts,
            message: message,
            log_message: $('#logBulkMessage').is(':checked')
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(`Messages sent successfully!\nSent: ${response.sent}\nFailed: ${response.failed}`);
                $('#bulkMessageModal').modal('hide');
                $('#bulkMessageForm')[0].reset();
                clearSelection();
            } else {
                alert('Failed to send messages: ' + response.message);
            }
        },
        error: function() {
            alert('Error sending bulk messages');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});
</script>
<?= $this->endSection() ?>
