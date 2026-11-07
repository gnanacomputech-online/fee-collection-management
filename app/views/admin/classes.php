<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Classes &amp; Sections</h1>
<p class="fs-page-sub">Every fee structure and student roster is scoped to a class.</p>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">All Classes</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Class</th><th>Section</th><th>Academic Year</th><th>Active Students</th><th></th></tr></thead>
                    <tbody>
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="5" class="fs-empty">No classes yet -- add one to get started.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($classes as $c): ?>
                        <?php $editFormId = 'edit-class-' . (int)$c['id']; ?>
                        <tr>
                            <td style="min-width:130px;">
                                <form id="<?= $editFormId ?>" method="POST" action="/index.php?route=admin/classes/update">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                </form>
                                <input class="form-control form-control-sm" name="name" form="<?= $editFormId ?>" value="<?= e($c['name']) ?>" required>
                            </td>
                            <td style="min-width:90px;"><input class="form-control form-control-sm" name="section" form="<?= $editFormId ?>" value="<?= e($c['section']) ?>" required></td>
                            <td style="min-width:110px;"><input class="form-control form-control-sm" name="academic_year" form="<?= $editFormId ?>" value="<?= e($c['academic_year']) ?>" required></td>
                            <td><?= (int)$c['student_count'] ?></td>
                            <td class="d-flex gap-2">
                                <button type="submit" form="<?= $editFormId ?>" class="btn btn-sm btn-outline-primary">Save</button>
                                <form method="POST" action="/index.php?route=admin/classes/delete" onsubmit="return confirm('Delete this class?');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
            <div class="card-header">Add a Class</div>
            <div class="card-body">
                <form method="POST" action="/index.php?route=admin/classes/store">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input class="form-control" name="name" placeholder="e.g. Class 8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <input class="form-control" name="section" placeholder="e.g. A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input class="form-control" name="academic_year" placeholder="e.g. 2025-26" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Class</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
