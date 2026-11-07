<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Accountant Dashboard</h1>
<p class="fs-page-sub">Collect payments, manage fee structures, and track dues.</p>

<div class="fs-stat-grid cols-4">
    <div class="fs-stat tone-green">
        <span class="fs-stat-value"><?= inr($stats['todays_collection']) ?></span>
        <span class="fs-stat-label">Today's Collection</span>
    </div>
    <div class="fs-stat tone-purple">
        <span class="fs-stat-value"><?= inr($stats['my_collection_today']) ?></span>
        <span class="fs-stat-label">Collected By You</span>
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

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="/index.php?route=dues" class="card text-decoration-none h-100">
            <div class="card-body">
                <h5 class="mb-1">Look Up a Student</h5>
                <p class="text-muted small mb-0">Search dues, apply a concession, or collect a payment.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/index.php?route=fee-structures/create" class="card text-decoration-none h-100">
            <div class="card-body">
                <h5 class="mb-1">Define a Fee Structure</h5>
                <p class="text-muted small mb-0">Set fee heads, amounts, due date, and the late-fee rule for a class/term.</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="/index.php?route=reports&type=defaulters" class="card text-decoration-none h-100">
            <div class="card-body">
                <h5 class="mb-1">Defaulters List</h5>
                <p class="text-muted small mb-0">Overdue dues that still need follow-up.</p>
            </div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">Payments You Recorded Today</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Receipt No</th><th>Student</th><th>Amount</th><th>Method</th><th>Paid On</th></tr></thead>
            <tbody>
            <?php if (empty($recentPayments)): ?>
                <tr><td colspan="5" class="fs-empty">No payments recorded by you today.</td></tr>
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
