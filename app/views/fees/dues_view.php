<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<?php $isAccountant = Auth::role() === 'accountant'; ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="fs-page-title"><?= e($student['name']) ?></h1>
        <p class="fs-page-sub"><?= e($student['admission_no']) ?> &middot; <?= e($student['class_name']) ?> - <?= e($student['class_section']) ?></p>
    </div>
    <?php if ($isAccountant): ?>
        <a href="/index.php?route=payments/collect&student_id=<?= (int)$student['id'] ?>" class="btn btn-accent">Collect Payment</a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-header">Dues</div>
    <div class="table-responsive">
        <table class="table table-compact mb-0 align-middle">
            <thead>
                <tr>
                    <th>Term</th><th>Fee Head</th><th>Due Date</th><th>Base</th><th>Concession</th><th>Late Fee</th>
                    <th>Net Payable</th><th>Paid</th><th>Balance</th><th>Status</th><?php if ($isAccountant): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($dues)): ?>
                <tr><td colspan="11" class="fs-empty">No dues generated for this student yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($dues as $d): ?>
                <?php $overdue = $d['status'] !== 'paid' && strtotime($d['due_date']) < strtotime(date('Y-m-d')); ?>
                <tr>
                    <td><?= e($d['term']) ?></td>
                    <td><?= e($d['fee_head_name']) ?></td>
                    <td class="<?= $overdue ? 'text-danger fw-semibold' : '' ?>"><?= format_date($d['due_date']) ?></td>
                    <td><?= inr($d['base_amount']) ?></td>
                    <td><?= $d['concession_amount'] > 0 ? '-' . inr($d['concession_amount']) : '-' ?></td>
                    <td><?= $d['late_fee_amount'] > 0 ? inr($d['late_fee_amount']) : '-' ?></td>
                    <td><?= inr($d['net_payable']) ?></td>
                    <td><?= inr($d['amount_paid']) ?></td>
                    <td><?= inr($d['balance']) ?></td>
                    <td><span class="badge-status badge-<?= $overdue ? 'overdue' : e($d['status']) ?>"><?= $overdue ? 'overdue' : e($d['status']) ?></span></td>
                    <?php if ($isAccountant): ?>
                    <td>
                        <?php if ($d['status'] !== 'paid'): ?>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#concession-<?= (int)$d['id'] ?>">Concession</button>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php if ($isAccountant && $d['status'] !== 'paid'): ?>
                <tr class="collapse" id="concession-<?= (int)$d['id'] ?>">
                    <td colspan="11" class="bg-light">
                        <form method="POST" action="/index.php?route=dues/concession" class="row g-2 align-items-end py-2">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="due_id" value="<?= (int)$d['id'] ?>">
                            <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">
                            <div class="col-sm-3">
                                <label class="form-label small mb-1">Concession Amount (max <?= inr($d['base_amount'] - $d['amount_paid']) ?>)</label>
                                <input class="form-control form-control-sm" type="number" step="0.01" min="0" max="<?= (float)$d['base_amount'] - (float)$d['amount_paid'] ?>" name="concession_amount" value="<?= (float)$d['concession_amount'] ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small mb-1">Reason</label>
                                <input class="form-control form-control-sm" name="concession_reason" value="<?= e($d['concession_reason'] ?? '') ?>" placeholder="e.g. sibling discount, scholarship">
                            </div>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Apply</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
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
