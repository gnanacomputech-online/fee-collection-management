<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Import Result</h1>

<div class="fs-stat-grid" style="max-width:600px;">
    <div class="fs-stat tone-green">
        <span class="fs-stat-value"><?= (int)$result['imported'] ?></span>
        <span class="fs-stat-label">Students Imported</span>
    </div>
    <div class="fs-stat <?= $result['skipped'] > 0 ? 'tone-red' : '' ?>">
        <span class="fs-stat-value"><?= (int)$result['skipped'] ?></span>
        <span class="fs-stat-label">Rows Skipped</span>
    </div>
</div>

<?php if (!empty($result['credentials'])): ?>
<div class="card mt-3">
    <div class="card-header">Generated Passwords (shown once -- share these with each student now)</div>
    <div class="card-body">
        <p class="small text-muted">Each imported student got their own random temporary password. This list is not stored anywhere and cannot be shown again once you leave this page -- copy it now. Students can change their password from "Change Password" after logging in.</p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead><tr><th>Admission No.</th><th>Name</th><th>Email</th><th>Temporary Password</th></tr></thead>
                <tbody>
                    <?php foreach ($result['credentials'] as $cred): ?>
                        <tr>
                            <td><?= e($cred['admission_no']) ?></td>
                            <td><?= e($cred['name']) ?></td>
                            <td><?= e($cred['email']) ?></td>
                            <td><code><?= e($cred['password']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($result['errors'])): ?>
<div class="card mt-3">
    <div class="card-header">Row-level Messages</div>
    <ul class="list-group list-group-flush">
        <?php foreach ($result['errors'] as $err): ?>
            <li class="list-group-item small text-danger"><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="mt-3 d-flex gap-2">
    <a href="/index.php?route=admin/students" class="btn btn-primary">View Roster</a>
    <a href="/index.php?route=admin/students/import" class="btn btn-outline-primary">Import Another File</a>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
