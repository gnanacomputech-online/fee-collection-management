<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="fs-page-title">Fee Structures</h1>
        <p class="fs-page-sub">Per class, per term -- combines fee heads into what each student in that class owes.</p>
    </div>
    <a href="/index.php?route=fee-structures/create" class="btn btn-primary">New Fee Structure</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Class</th><th>Term</th><th>Academic Year</th><th>Due Date</th><th>Late Fee</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($structures)): ?>
                <tr><td colspan="7" class="fs-empty">No fee structures yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($structures as $s): ?>
                <tr>
                    <td><?= e($s['class_name']) ?> - <?= e($s['class_section']) ?></td>
                    <td><?= e($s['term']) ?></td>
                    <td><?= e($s['academic_year']) ?></td>
                    <td><?= format_date($s['due_date']) ?></td>
                    <td><?= $s['late_fee_type'] === 'flat' ? inr($s['late_fee_value']) . ' flat' : (float)$s['late_fee_value'] . '%' ?> after <?= (int)$s['grace_days'] ?>d grace</td>
                    <td><span class="badge-status badge-<?= $s['status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($s['status']) ?></span></td>
                    <td class="d-flex gap-2">
                        <a href="/index.php?route=fee-structures/view&id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <form method="POST" action="/index.php?route=fee-structures/status" class="d-inline">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $s['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
