<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create New Table<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Create New Table</h4>
        <a href="<?= base_url('table-manager') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="<?= base_url('table-manager/create') ?>" method="post" id="createTableForm">
                <?= csrf_field() ?>
                
                <div class="mb-4">
                    <label class="form-label">Table Name</label>
                    <input type="text" class="form-control" name="table_name" required 
                           placeholder="Enter table name (e.g., students)">
                    <small class="text-muted">Use lowercase letters, numbers, and underscores only</small>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Table Fields</h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addField()">
                            <i class="fas fa-plus me-2"></i>Add Field
                        </button>
                    </div>
                    
                    <div id="fieldsContainer">
                        <!-- Fields will be added here dynamically -->
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Table
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Field Template -->
<template id="fieldTemplate">
    <div class="field-row card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Field Name</label>
                        <input type="text" class="form-control" name="fields[INDEX][name]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select select2" name="fields[INDEX][type]" required onchange="updateLengthField(this)">
                            <option value="">Select type...</option>
                            <option value="VARCHAR">VARCHAR</option>
                            <option value="INT">INT</option>
                            <option value="TEXT">TEXT</option>
                            <option value="DATETIME">DATETIME</option>
                            <option value="BOOLEAN">BOOLEAN</option>
                            <option value="DECIMAL">DECIMAL</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Length</label>
                        <input type="number" class="form-control" name="fields[INDEX][length]" 
                               placeholder="e.g., 255">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Nullable</label>
                        <select class="form-select" name="fields[INDEX][null]">
                            <option value="YES">Yes</option>
                            <option value="NO">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Default</label>
                        <input type="text" class="form-control" name="fields[INDEX][default]" 
                               placeholder="Default value">
                    </div>
                </div>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeField(this)">
                    <i class="fas fa-trash me-2"></i>Remove
                </button>
            </div>
        </div>
    </div>
</template>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let fieldIndex = 0;

function addField() {
    const template = document.getElementById('fieldTemplate');
    const container = document.getElementById('fieldsContainer');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX with actual index
    clone.querySelectorAll('[name*="INDEX"]').forEach(element => {
        element.name = element.name.replace('INDEX', fieldIndex);
    });
    
    container.appendChild(clone);
    fieldIndex++;
    
    // Initialize Select2 for the new field
    $('.select2').select2({
        theme: 'bootstrap-5'
    });
}

function removeField(button) {
    button.closest('.field-row').remove();
}

function updateLengthField(select) {
    const type = select.value;
    const lengthInput = select.closest('.row').querySelector('[name*="length"]');
    
    if (type === 'VARCHAR') {
        lengthInput.value = '255';
        lengthInput.required = true;
    } else if (type === 'INT') {
        lengthInput.value = '11';
        lengthInput.required = true;
    } else if (type === 'DECIMAL') {
        lengthInput.value = '10,2';
        lengthInput.required = true;
    } else {
        lengthInput.value = '';
        lengthInput.required = false;
    }
}

// Add first field on page load
document.addEventListener('DOMContentLoaded', function() {
    addField();
});

// Form validation
$('#createTableForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('Table created successfully!', 'success');
                window.location.href = '<?= base_url('table-manager') ?>';
            } else {
                showToast(response.message || 'Failed to create table', 'error');
            }
        },
        error: function() {
            showToast('An error occurred while creating table', 'error');
        }
    });
});
</script>
<?= $this->endSection() ?> 