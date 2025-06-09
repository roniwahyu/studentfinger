<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#templateModal">
                <i class="fas fa-plus"></i> New Template
            </button>
            <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row">
        <?php foreach ($event_types as $event_type => $event_label): ?>
            <div class="col-lg-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bell"></i> <?= $event_label ?>
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="createTemplate('<?= $event_type ?>')">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                    <div class="card-body">
                        <?php 
                        $eventTemplates = array_filter($templates, function($template) use ($event_type) {
                            return $template['event_type'] === $event_type;
                        });
                        ?>
                        
                        <?php if (!empty($eventTemplates)): ?>
                            <?php foreach ($eventTemplates as $template): ?>
                                <div class="border rounded p-3 mb-3 <?= $template['is_active'] ? 'border-success' : 'border-secondary' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <?= htmlspecialchars($template['template_name']) ?>
                                                <?php if ($template['is_active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                                <span class="badge badge-info"><?= strtoupper($template['language']) ?></span>
                                            </h6>
                                            <?php if (!empty($template['description'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($template['description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editTemplate(<?= $template['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewTemplate(<?= $template['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(<?= $template['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="template-preview bg-light p-2 rounded">
                                        <small class="text-muted">Preview:</small>
                                        <div class="mt-1" style="font-size: 0.85em; max-height: 100px; overflow-y: auto;">
                                            <?= nl2br(htmlspecialchars(substr($template['message_template'], 0, 200))) ?>
                                            <?php if (strlen($template['message_template']) > 200): ?>
                                                <span class="text-muted">...</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Variables: 
                                            <?php 
                                            $variables = json_decode($template['variables'], true) ?? [];
                                            echo implode(', ', array_map(function($var) {
                                                return '{' . $var . '}';
                                            }, $variables));
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-2x text-gray-300 mb-2"></i>
                                <p class="text-muted">No templates for this event type</p>
                                <button type="button" class="btn btn-sm btn-primary" onclick="createTemplate('<?= $event_type ?>')">
                                    <i class="fas fa-plus"></i> Create Template
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Template Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create/Edit Template</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="templateForm" method="POST" action="<?= base_url('classroom-notifications/templates/save') ?>">
                <div class="modal-body">
                    <input type="hidden" id="template_id" name="template_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="template_name">Template Name *</label>
                                <input type="text" class="form-control" id="template_name" name="template_name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="event_type">Event Type *</label>
                                <select class="form-control" id="event_type" name="event_type" required>
                                    <?php foreach ($event_types as $type => $label): ?>
                                        <option value="<?= $type ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="language">Language *</label>
                                <select class="form-control" id="language" name="language" required>
                                    <option value="id">Indonesian</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Brief description of this template">
                    </div>
                    
                    <div class="form-group">
                        <label for="message_template">Message Template *</label>
                        <textarea class="form-control" id="message_template" name="message_template" rows="8" required placeholder="Enter your WhatsApp message template here..."></textarea>
                        <small class="form-text text-muted">
                            Use variables like {student_name}, {parent_name}, {class_name}, {subject}, {teacher_name}, {start_time}, {session_date}, {school_name}
                        </small>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            Active Template
                        </label>
                    </div>
                    
                    <div class="mt-3">
                        <h6>Available Variables:</h6>
                        <div id="available_variables" class="text-muted small">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="fab fa-whatsapp"></i> WhatsApp Message Preview
                    </div>
                    <div class="card-body">
                        <div id="preview_content" style="white-space: pre-line; font-family: monospace; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                            <!-- Preview content will be loaded here -->
                        </div>
                    </div>
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
const eventVariables = {
    'session_start': ['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'start_time', 'session_date', 'school_name'],
    'session_break': ['parent_name', 'student_name', 'subject', 'class_name', 'break_time', 'break_duration', 'school_name'],
    'session_resume': ['parent_name', 'student_name', 'subject', 'class_name', 'resume_time', 'school_name'],
    'session_finish': ['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'end_time', 'total_duration', 'school_name']
};

function createTemplate(eventType = null) {
    $('#templateForm')[0].reset();
    $('#template_id').val('');
    $('#templateModal .modal-title').text('Create New Template');
    
    if (eventType) {
        $('#event_type').val(eventType);
    }
    
    updateAvailableVariables();
    $('#templateModal').modal('show');
}

function editTemplate(templateId) {
    $.ajax({
        url: `<?= base_url('classroom-notifications/templates/edit') ?>/${templateId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const template = response.data;

                $('#templateModal .modal-title').text('Edit Template');
                $('#template_id').val(template.id);
                $('#template_name').val(template.template_name);
                $('#event_type').val(template.event_type);
                $('#language').val(template.language);
                $('#description').val(template.description);
                $('#message_template').val(template.message_template);
                $('#is_active').prop('checked', template.is_active == 1);

                updateAvailableVariables();
                $('#templateModal').modal('show');
            } else {
                alert('Error loading template: ' + response.message);
            }
        },
        error: function() {
            alert('Error loading template data');
        }
    });
}

function previewTemplate(templateId) {
    $.ajax({
        url: `<?= base_url('classroom-notifications/templates/edit') ?>/${templateId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const template = response.data;

                // Sample variables for preview
                const sampleVars = {
                    'parent_name': 'Bapak/Ibu Ahmad',
                    'student_name': 'Ahmad Rizki',
                    'subject': 'Matematika',
                    'class_name': 'X-A',
                    'teacher_name': 'Mrs. Sari',
                    'start_time': '08:00',
                    'session_date': new Date().toLocaleDateString('id-ID'),
                    'school_name': 'Student Finger School',
                    'break_time': '09:30',
                    'break_duration': '15',
                    'resume_time': '09:45',
                    'end_time': '10:30',
                    'total_duration': '2 jam'
                };

                let previewText = template.message_template;

                // Replace variables with sample data
                Object.keys(sampleVars).forEach(key => {
                    const regex = new RegExp(`\\{${key}\\}`, 'g');
                    previewText = previewText.replace(regex, sampleVars[key]);
                });

                $('#preview_content').html(previewText);
                $('#previewModal').modal('show');
            } else {
                alert('Error loading template: ' + response.message);
            }
        },
        error: function() {
            alert('Error loading template data');
        }
    });
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template?')) {
        $.ajax({
            url: `<?= base_url('classroom-notifications/templates/delete') ?>/${templateId}`,
            type: 'POST',
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Error deleting template');
            }
        });
    }
}

function updateAvailableVariables() {
    const eventType = $('#event_type').val();
    const variables = eventVariables[eventType] || [];
    
    const variableHtml = variables.map(variable => 
        `<span class="badge badge-secondary mr-1 mb-1">{${variable}}</span>`
    ).join('');
    
    $('#available_variables').html(variableHtml);
}

$('#event_type').change(updateAvailableVariables);

// Initialize on page load
$(document).ready(function() {
    updateAvailableVariables();
});
</script>
<?= $this->endSection() ?>
