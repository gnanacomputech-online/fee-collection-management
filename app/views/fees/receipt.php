<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h1 class="fs-page-title">Receipt <?= e($payment['receipt_no']) ?></h1>
        <p class="fs-page-sub">Payment recorded successfully.</p>
    </div>
    <a href="/index.php?route=payments/receipt/download&id=<?= (int)$payment['id'] ?>" class="btn btn-accent">Download PDF</a>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        <table class="table table-borderless mb-3">
            <tr><th class="text-muted" style="width:160px;">Receipt No.</th><td><?= e($payment['receipt_no']) ?></td>
                <th class="text-muted" style="width:120px;">Paid On</th><td><?= format_datetime($payment['paid_at']) ?></td></tr>
            <tr><th class="text-muted">Student</th><td><?= e($payment['student_name']) ?></td>
                <th class="text-muted">Admission No.</th><td><?= e($payment['admission_no']) ?></td></tr>
            <tr><th class="text-muted">Class</th><td><?= e($payment['class_name']) ?> - <?= e($payment['class_section']) ?></td>
                <th class="text-muted">Method</th><td class="text-capitalize"><?= e($payment['method']) ?><?= $payment['reference_no'] ? ' (' . e($payment['reference_no']) . ')' : '' ?></td></tr>
            <tr><th class="text-muted">Recorded By</th><td><?= e($payment['recorded_by_name']) ?></td>
                <th class="text-muted">Remarks</th><td><?= e($payment['remarks'] ?: '-') ?></td></tr>
        </table>

        <table class="table">
            <thead><tr><th>Term</th><th>Fee Head</th><th class="text-end">Amount Paid</th></tr></thead>
            <tbody>
            <?php foreach ($allocations as $a): ?>
                <tr>
                    <td><?= e($a['term']) ?></td>
                    <td><?= e($a['fee_head_name']) ?></td>
                    <td class="text-end"><?= inr($a['allocated_amount']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold"><td colspan="2">Total Paid</td><td class="text-end"><?= inr($payment['amount']) ?></td></tr>
            </tfoot>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
