<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Dues Lookup</h1>
<p class="fs-page-sub">Search a student to view their dues<?= Auth::role() === 'accountant' ? ', apply a concession, or collect a payment.' : '.' ?></p>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/index.php" class="row g-2 align-items-end">
            <input type="hidden" name="route" value="dues">
            <div class="col-sm-6">
                <label class="form-label">Search</label>
                <input class="form-control" type="text" name="q" value="<?= e($query) ?>" placeholder="Name, admission no, or email" autofocus>
            </div>
            <div class="col-sm-4">
                <label class="form-label">Class</label>
                <select class="form-select" name="class_id">
                    <option value="">All classes</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $classId === (int)$c['id'] ? 'selected' : '' ?>><?= e(SchoolClass::label($c)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-2">
                <button class="btn btn-primary w-100" type="submit">Search</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($students)): ?>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Admission No.</th><th>Name</th><th>Class</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= e($s['admission_no']) ?></td>
                    <td><?= e($s['name']) ?></td>
                    <td><?= e($s['class_name']) ?> - <?= e($s['class_section']) ?></td>
                    <td><a href="/index.php?route=dues/view&student_id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-primary">View Dues</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif ($query !== '' || $classId > 0): ?>
    <p class="fs-empty">No students match your search.</p>
<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
