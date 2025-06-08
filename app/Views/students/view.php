<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>View Student<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">View Student</h4>
        <div>
            <a href="<?= base_url('students/edit/' . $Student['id']) ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="<?= base_url('students') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Add your view fields here -->
        </div>
    </div>
</div>
<?= $this->endSection() ?>