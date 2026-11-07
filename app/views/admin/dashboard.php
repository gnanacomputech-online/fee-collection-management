<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Super Admin Dashboard</h1>
<p class="fs-page-sub">Institution-wide overview across all classes and accountants.</p>

<div class="fs-stat-grid cols-4">
    <div class="fs-stat tone-purple">
        <span class="fs-stat-value"><?= (int)$stats['classes'] ?></span>
        <span class="fs-stat-label">Classes</span>
    </div>
    <div class="fs-stat tone-purple">
        <span class="fs-stat-value"><?= (int)$stats['students'] ?></span>
        <span class="fs-stat-label">Active Students</span>
    </div>
    <div class="fs-stat tone-purple">
        <span class="fs-stat-value"><?= (int)$stats['accountants'] ?></span>
        <span class="fs-stat-label">Accountants</span>
    </div>
    <div class="fs-stat">
        <span class="fs-stat-value"><?= (int)$stats['structures'] ?></span>
        <span class="fs-stat-label">Fee Structures</span>
    </div>
</div>

<div class="fs-stat-grid cols-3">
    <div class="fs-stat tone-green">
        <span class="fs-stat-value"><?= inr($stats['todays_collection']) ?></span>
        <span class="fs-stat-label">Today's Collection</span>
    </div>
    <div class="fs-stat tone-red">
        <span class="fs-stat-value"><?= inr($stats['total_outstanding']) ?></span>
        <span class="fs-stat-label">Total Outstanding</span>
    </div>
    <div class="fs-stat tone-red">
        <span class="fs-stat-value"><?= (int)$stats['defaulters'] ?></span>
        <span class="fs-stat-label">Overdue Due Items</span>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent Payments</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Receipt No</th><th>Student</th><th>Amount</th><th>Method</th><th>Paid On</th></tr>
            </thead>
            <tbody>
            <?php if (empty($recentPayments)): ?>
                <tr><td colspan="5" class="fs-empty">No payments recorded yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($recentPayments as $p): ?>
                <tr>
                    <td><a href="/index.php?route=payments/receipt&id=<?= (int)$p['id'] ?>"><?= e($p['receipt_no']) ?></a></td>
                    <td><?= e($p['student_name']) ?> <span class="text-muted">(<?= e($p['admission_no']) ?>)</span></td>
                    <td><?= inr($p['amount']) ?></td>
                    <td class="text-capitalize"><?= e($p['method']) ?></td>
                    <td><?= format_datetime($p['paid_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
