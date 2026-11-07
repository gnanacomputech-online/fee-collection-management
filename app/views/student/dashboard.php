<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Welcome, <?= e(Auth::name()) ?></h1>
<p class="fs-page-sub"><?= e($student['admission_no']) ?> &middot; <?= e($student['class_name']) ?> - <?= e($student['class_section']) ?></p>

<div class="fs-stat-grid cols-3">
    <div class="fs-stat <?= $totalOutstanding > 0 ? 'tone-red' : 'tone-green' ?>">
        <span class="fs-stat-value"><?= inr($totalOutstanding) ?></span>
        <span class="fs-stat-label">Total Outstanding</span>
    </div>
    <div class="fs-stat <?= $overdueCount > 0 ? 'tone-red' : '' ?>">
        <span class="fs-stat-value"><?= (int)$overdueCount ?></span>
        <span class="fs-stat-label">Overdue Items</span>
    </div>
    <div class="fs-stat tone-purple">
        <span class="fs-stat-value"><?= $nextDue ? format_date($nextDue['due_date']) : '-' ?></span>
        <span class="fs-stat-label">Next Due Date</span>
    </div>
</div>

<?php if ($nextDue): ?>
<div class="card mb-4">
    <div class="card-header">Next Payment Due</div>
    <div class="card-body">
        <p class="mb-1"><strong><?= e($nextDue['fee_head_name']) ?></strong> (<?= e($nextDue['term']) ?>)</p>
        <p class="mb-1">Amount: <?= inr($nextDue['balance']) ?> &middot; Due: <?= format_date($nextDue['due_date']) ?></p>
        <p class="text-muted small mb-0">Payments are collected in person by the school Accountant -- this system does not process online payments.</p>
    </div>
</div>
<?php endif; ?>

<div class="d-flex gap-2">
    <a href="/index.php?route=student/dues" class="btn btn-primary">View All My Dues</a>
    <a href="/index.php?route=student/history" class="btn btn-outline-primary">Payment History</a>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
