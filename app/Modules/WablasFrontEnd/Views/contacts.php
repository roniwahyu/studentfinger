<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-address-book text-info"></i>
            <?= $title ?>
        </h1>
        <div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addContactModal">
                <i class="fas fa-plus"></i> Add Contact
            </button>
            <a href="<?= base_url('wablas-frontend/contacts/import') ?>" class="btn btn-primary">
                <i class="fas fa-upload"></i> Import Contacts
            </a>
            <a href="<?= base_url('wablas-frontend/dashboard') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

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
                                <?= number_format($stats['total'] ?? 0) ?>
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
                                <?= number_format($stats['active'] ?? 0) ?>
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
                                With Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['with_students'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
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
                                Contact Groups
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($groups ?? []) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Contact Filters
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= base_url('wablas-frontend/contacts') ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="contact_type">Contact Type</label>
                        <select class="form-control" id="contact_type" name="contact_type">
                            <option value="">All Types</option>
                            <option value="parent" <?= ($filters['contact_type'] === 'parent') ? 'selected' : '' ?>>Parent</option>
                            <option value="guardian" <?= ($filters['contact_type'] === 'guardian') ? 'selected' : '' ?>>Guardian</option>
                            <option value="emergency" <?= ($filters['contact_type'] === 'emergency') ? 'selected' : '' ?>>Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="is_active">Status</label>
                        <select class="form-control" id="is_active" name="is_active">
                            <option value="">All Status</option>
                            <option value="1" <?= ($filters['is_active'] === '1') ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($filters['is_active'] === '0') ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Name, phone, student name..." value="<?= $filters['search'] ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="<?= base_url('wablas-frontend/contacts') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Contacts
            </h6>
        </div>
        <div class="card-body">
            <?php if (!empty($contacts)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="contactsTable">
                        <thead>
                            <tr>
                                <th>Contact Name</th>
                                <th>Phone Number</th>
                                <th>Type</th>
                                <th>Student</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $contact): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold"><?= htmlspecialchars($contact['contact_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($contact['email'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold"><?= htmlspecialchars($contact['phone_number']) ?></span>
                                        <?php if (!empty($contact['whatsapp_verified'])): ?>
                                            <i class="fab fa-whatsapp text-success ml-1" title="WhatsApp Verified"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= ucfirst($contact['contact_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($contact['student_firstname'])): ?>
                                            <?= htmlspecialchars($contact['student_firstname'] . ' ' . $contact['student_lastname']) ?>
                                            <br><small class="text-muted">Class: <?= htmlspecialchars($contact['class_name'] ?? 'N/A') ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No student linked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColor = $contact['is_active'] ? 'success' : 'secondary';
                                        $statusText = $contact['is_active'] ? 'Active' : 'Inactive';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editContact(<?= $contact['id'] ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="sendMessage('<?= $contact['phone_number'] ?>')" title="Send Message">
                                            <i class="fab fa-whatsapp"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteContact(<?= $contact['id'] ?>)" title="Delete">
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
                    <i class="fas fa-address-book fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No contacts found</h5>
                    <p class="text-muted">Try adjusting your filters or add your first contact.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addContactModal">
                        <i class="fas fa-plus"></i> Add First Contact
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Add New Contact
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addContactForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_name">Contact Name *</label>
                                <input type="text" class="form-control" id="contact_name" name="contact_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone_number">Phone Number *</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required
                                       placeholder="628123456789">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact_type">Contact Type *</label>
                                <select class="form-control" id="contact_type" name="contact_type" required>
                                    <option value="">Select Type</option>
                                    <option value="parent">Parent</option>
                                    <option value="guardian">Guardian</option>
                                    <option value="emergency">Emergency Contact</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="student_id">Link to Student (Optional)</label>
                        <select class="form-control" id="student_id" name="student_id">
                            <option value="">Select Student</option>
                            <!-- Students would be loaded via AJAX -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
