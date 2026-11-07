<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">My Dues</h1>
<p class="fs-page-sub">Itemized by term and fee head.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-compact mb-0 align-middle">
            <thead>
                <tr><th>Term</th><th>Fee Head</th><th>Due Date</th><th>Base</th><th>Concession</th><th>Late Fee</th><th>Net Payable</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (empty($dues)): ?>
                <tr><td colspan="10" class="fs-empty">No dues on record.</td></tr>
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
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
