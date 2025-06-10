<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt text-warning"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addTemplateModal">
                <i class="fas fa-plus"></i> Add Template
            </button>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Template Categories -->
    <div class="row mb-4">
        <?php foreach ($categories as $key => $category): ?>
            <div class="col-md-4 mb-3">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?= $category ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php
                                    $count = 0;
                                    foreach ($templates as $template) {
                                        if ($template['event_type'] === $key) $count++;
                                    }
                                    echo $count;
                                    ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Available Variables -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-info">
                <i class="fas fa-code"></i> Available Variables
            </h6>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Use these variables in your templates. They will be automatically replaced with actual values when sending messages.</p>
            <div class="row">
                <?php foreach ($variables as $var => $description): ?>
                    <div class="col-md-3 mb-2">
                        <span class="badge badge-info">{<?= $var ?>}</span>
                        <small class="text-muted d-block"><?= $description ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Message Templates
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($templates)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="templatesTable">
                        <thead>
                            <tr>
                                <th>Template Name</th>
                                <th>Category</th>
                                <th>Language</th>
                                <th>Message Preview</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($template['template_name']) ?></div>
                                        <small class="text-muted">Created: <?= date('Y-m-d', strtotime($template['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= $categories[$template['event_type']] ?? ucfirst($template['event_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= strtoupper($template['language']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="message-preview" style="max-width: 300px;">
                                            <?= htmlspecialchars(substr($template['message_template'], 0, 100)) ?>
                                            <?php if (strlen($template['message_template']) > 100): ?>...<?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColor = $template['is_active'] ? 'success' : 'secondary';
                                        $statusText = $template['is_active'] ? 'Active' : 'Inactive';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate(<?= $template['id'] ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" onclick="previewTemplate(<?= $template['id'] ?>)" title="Preview">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="testTemplate(<?= $template['id'] ?>)" title="Test Send">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(<?= $template['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No templates found</h5>
                    <p class="text-muted">Create your first message template to get started.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTemplateModal">
                        <i class="fas fa-plus"></i> Create First Template
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New Template
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addTemplateForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="template_name">Template Name *</label>
                                <input type="text" class="form-control" id="template_name" name="template_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="event_type">Category *</label>
                                <select class="form-control" id="event_type" name="event_type" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $key => $category): ?>
                                        <option value="<?= $key ?>"><?= $category ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="language">Language *</label>
                                <select class="form-control" id="language" name="language" required>
                                    <option value="id">Indonesian</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Status</label>
                                <select class="form-control" id="is_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message_template">Message Template *</label>
                        <textarea class="form-control" id="message_template" name="message_template" rows="6" required
                                  placeholder="Enter your message template here. Use variables like {parent_name}, {student_name}, etc."></textarea>
                        <small class="form-text text-muted">
                            Use variables from the list above. Example: "Dear {parent_name}, your child {student_name} has arrived at school."
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                                  placeholder="Optional description for this template"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Template Modal -->
<div class="modal fade" id="previewTemplateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Template Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="templatePreviewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Add template form submission
$('#addTemplateForm').submit(function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('wablas-frontend/templates/save') ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('✅ Template saved successfully!');
                $('#addTemplateModal').modal('hide');
                $('#addTemplateForm')[0].reset();
                location.reload();
            } else {
                alert('❌ Failed to save template: ' + response.message);
            }
        },
        error: function() {
            alert('❌ Error saving template. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
});

function editTemplate(templateId) {
    // Implementation for editing template
    window.location.href = '<?= base_url('wablas-frontend/templates/edit') ?>/' + templateId;
}

function previewTemplate(templateId) {
    $('#previewTemplateModal').modal('show');
    $('#templatePreviewContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading preview...</div>');
    
    // Load template preview
    setTimeout(function() {
        $('#templatePreviewContent').html(`
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fab fa-whatsapp"></i> WhatsApp Message Preview
                </div>
                <div class="card-body">
                    <p><strong>Sample message with variables replaced:</strong></p>
                    <div class="bg-light p-3 rounded">
                        Dear John Doe, your child Jane Doe has arrived at school at 07:30 AM on 2024-01-15. Thank you.
                    </div>
                </div>
            </div>
        `);
    }, 1000);
}

function testTemplate(templateId) {
    if (confirm('Send a test message using this template?')) {
        alert('Test message functionality would be implemented here.');
    }
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template?')) {
        $.ajax({
            url: '<?= base_url('wablas-frontend/templates/delete') ?>/' + templateId,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('✅ Template deleted successfully!');
                    location.reload();
                } else {
                    alert('❌ Failed to delete template: ' + response.message);
                }
            },
            error: function() {
                alert('❌ Error deleting template.');
            }
        });
    }
}

// Initialize DataTable
$(document).ready(function() {
    if ($('#templatesTable').length) {
        $('#templatesTable').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [5] }
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
