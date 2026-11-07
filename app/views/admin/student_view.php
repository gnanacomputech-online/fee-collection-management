<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title"><?= e($student['name']) ?></h1>
<p class="fs-page-sub"><?= e($student['admission_no']) ?> &middot; <?= e($student['class_name']) ?> - <?= e($student['class_section']) ?> &middot; Roll No. <?= (int)$student['roll_no'] ?></p>

<div class="row g-4 mb-2">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Profile</div>
            <div class="card-body small">
                <p class="mb-1"><strong>Email:</strong> <?= e($student['email']) ?></p>
                <p class="mb-1"><strong>Guardian:</strong> <?= e($student['guardian_name'] ?: '-') ?></p>
                <p class="mb-1"><strong>Guardian Phone:</strong> <?= e($student['guardian_phone'] ?: '-') ?></p>
                <p class="mb-1"><strong>DOB:</strong> <?= format_date($student['dob']) ?></p>
                <p class="mb-0"><strong>Status:</strong> <span class="badge-status badge-<?= $student['user_status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($student['user_status']) ?></span></p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Dues</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Term</th><th>Fee Head</th><th>Due</th><th>Net Payable</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($dues)): ?>
                        <tr><td colspan="7" class="fs-empty">No dues generated yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($dues as $d): ?>
                        <tr>
                            <td><?= e($d['term']) ?></td>
                            <td><?= e($d['fee_head_name']) ?></td>
                            <td><?= format_date($d['due_date']) ?></td>
                            <td><?= inr($d['net_payable']) ?></td>
                            <td><?= inr($d['amount_paid']) ?></td>
                            <td><?= inr($d['balance']) ?></td>
                            <td><span class="badge-status badge-<?= e($d['status']) ?>"><?= e($d['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Payment History</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Receipt</th><th>Amount</th><th>Method</th><th>Paid On</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="4" class="fs-empty">No payments yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><a href="/index.php?route=payments/receipt&id=<?= (int)$p['id'] ?>"><?= e($p['receipt_no']) ?></a></td>
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
