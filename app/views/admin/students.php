<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="fs-page-title">Students</h1>
        <p class="fs-page-sub">Full roster across all classes.</p>
    </div>
    <a href="/index.php?route=admin/students/import" class="btn btn-accent">Bulk Import (Excel)</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/index.php" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="admin/students">
            <div class="col-sm-5">
                <label class="form-label">Search</label>
                <input class="form-control" type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Name, admission no, or email">
            </div>
            <div class="col-sm-4">
                <label class="form-label">Class</label>
                <select class="form-select" name="class_id">
                    <option value="">All classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)($_GET['class_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= e(SchoolClass::label($c)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-3">
                <button class="btn btn-primary w-100" type="submit">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Roster (<?= count($students) ?>)</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Admission No.</th><th>Name</th><th>Class</th><th>Roll</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="6" class="fs-empty">No students match.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td><?= e($s['admission_no']) ?></td>
                            <td><a href="/index.php?route=admin/students/view&id=<?= (int)$s['id'] ?>"><?= e($s['name']) ?></a></td>
                            <td><?= e($s['class_name']) ?> - <?= e($s['class_section']) ?></td>
                            <td><?= (int)$s['roll_no'] ?></td>
                            <td><span class="badge-status badge-<?= $s['user_status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($s['user_status']) ?></span></td>
                            <td>
                                <form method="POST" action="/index.php?route=admin/students/status" class="d-inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $s['user_status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Enroll a Student</div>
            <div class="card-body">
                <form method="POST" action="/index.php?route=admin/students/store">
                    <?= Csrf::field() ?>
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Admission No.</label>
                        <input class="form-control" name="admission_no" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-8">
                            <label class="form-label">Class</label>
                            <select class="form-select" name="class_id" required>
                                <option value="">Select</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= e(SchoolClass::label($c)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Roll No.</label>
                            <input class="form-control" type="number" name="roll_no" required>
                        </div>
                    </div>
                    <div class="mb-2 mt-2">
                        <label class="form-label">Guardian Name</label>
                        <input class="form-control" name="guardian_name">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Guardian Phone</label>
                        <input class="form-control" name="guardian_phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input class="form-control" type="date" name="dob">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Enroll Student</button>
                    <p class="text-muted small mt-2 mb-0">A random temporary password is generated on enrollment and shown once in the confirmation message -- share it with the student now, as it can't be recovered later. They can change it from "Change Password" after logging in.</p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
