<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Accountant Accounts</h1>
<p class="fs-page-sub">Staff who can define fee structures and collect payments.</p>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">All Accountants</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Email</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php if (empty($accountants)): ?>
                        <tr><td colspan="4" class="fs-empty">No accountant accounts yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($accountants as $a): ?>
                        <tr>
                            <td><?= e($a['name']) ?></td>
                            <td><?= e($a['email']) ?></td>
                            <td><span class="badge-status badge-<?= $a['status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($a['status']) ?></span></td>
                            <td>
                                <form method="POST" action="/index.php?route=admin/accountants/status" class="d-inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $a['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
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
            <div class="card-header">Add an Accountant</div>
            <div class="card-body">
                <form method="POST" action="/index.php?route=admin/accountants/store">
                    <?= Csrf::field() ?>
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input class="form-control" name="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input class="form-control" type="password" name="password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
