<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title"><?= e($structure['class_name']) ?> - <?= e($structure['class_section']) ?> &middot; <?= e($structure['term']) ?></h1>
<p class="fs-page-sub"><?= e($structure['academic_year']) ?> &middot; Due <?= format_date($structure['due_date']) ?> &middot;
    Late fee: <?= $structure['late_fee_type'] === 'flat' ? inr($structure['late_fee_value']) . ' flat' : (float)$structure['late_fee_value'] . '%' ?> after <?= (int)$structure['grace_days'] ?> grace day(s)
    &middot; <span class="badge-status badge-<?= $structure['status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($structure['status']) ?></span>
</p>

<div class="card" style="max-width:600px;">
    <div class="card-header">Fee Head Line Items</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Fee Head</th><th>Amount</th></tr></thead>
            <tbody>
            <?php $total = 0; foreach ($items as $it): $total += (float)$it['amount']; ?>
                <tr><td><?= e($it['fee_head_name']) ?></td><td><?= inr($it['amount']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold"><td>Total per student</td><td><?= inr($total) ?></td></tr>
            </tfoot>
        </table>
    </div>
</div>

<a href="/index.php?route=fee-structures" class="btn btn-outline-primary mt-3">&larr; Back to Fee Structures</a>
<?php require __DIR__ . '/../partials/footer.php'; ?>
