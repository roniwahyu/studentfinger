<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create Student<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Create Student</h4>
        <a href="<?= base_url('students') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (isset($validation)): ?>
                <div class="alert alert-danger">
                    <?= $validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('students/create') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Basic Information</h5>

                        <div class="mb-3">
                            <label for="student_id" class="form-label">Student ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="student_id" name="student_id"
                                   value="<?= old('student_id') ?>" placeholder="Enter student ID" required>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= old('name') ?>" placeholder="Enter full name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= old('email') ?>" placeholder="Enter email address">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?= old('phone') ?>" placeholder="Enter phone number">
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                   value="<?= old('date_of_birth') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-control" id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?= old('gender') === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= old('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= old('gender') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"
                                      placeholder="Enter address"><?= old('address') ?></textarea>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Academic Information</h5>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-control" id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['id'] ?>" <?= old('class_id') == $class['id'] ? 'selected' : '' ?>>
                                        <?= esc($class['class']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="section_id" class="form-label">Section <span class="text-danger">*</span></label>
                            <select class="form-control" id="section_id" name="section_id" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= $section['id'] ?>" <?= old('section_id') == $section['id'] ? 'selected' : '' ?>>
                                        <?= esc($section['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="session_id" class="form-label">Session <span class="text-danger">*</span></label>
                            <select class="form-control" id="session_id" name="session_id" required>
                                <option value="">Select Session</option>
                                <?php foreach ($sessions as $session): ?>
                                    <option value="<?= $session['id'] ?>" <?= old('session_id') == $session['id'] ? 'selected' : '' ?>>
                                        <?= esc($session['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admission_date" class="form-label">Admission Date</label>
                            <input type="date" class="form-control" id="admission_date" name="admission_date"
                                   value="<?= old('admission_date') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="rfid_card" class="form-label">RFID Card</label>
                            <input type="text" class="form-control" id="rfid_card" name="rfid_card"
                                   value="<?= old('rfid_card') ?>" placeholder="Enter RFID card number">
                        </div>

                        <div class="mb-3">
                            <label for="pin" class="form-label">PIN</label>
                            <input type="text" class="form-control" id="pin" name="pin"
                                   value="<?= old('pin') ?>" placeholder="Enter PIN for fingerprint device">
                        </div>
                    </div>
                </div>

                <!-- Parent Information -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="mb-3">Parent/Guardian Information</h5>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="parent_name" class="form-label">Parent/Guardian Name</label>
                            <input type="text" class="form-control" id="parent_name" name="parent_name"
                                   value="<?= old('parent_name') ?>" placeholder="Enter parent/guardian name">
                        </div>

                        <div class="mb-3">
                            <label for="parent_phone" class="form-label">Parent Phone</label>
                            <input type="text" class="form-control" id="parent_phone" name="parent_phone"
                                   value="<?= old('parent_phone') ?>" placeholder="Enter parent phone number">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="parent_email" class="form-label">Parent Email</label>
                            <input type="email" class="form-control" id="parent_email" name="parent_email"
                                   value="<?= old('parent_email') ?>" placeholder="Enter parent email">
                        </div>

                        <div class="mb-3">
                            <label for="emergency_contact" class="form-label">Emergency Contact</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact"
                                   value="<?= old('emergency_contact') ?>" placeholder="Enter emergency contact number">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Student
                    </button>
                    <a href="<?= base_url('students') ?>" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>