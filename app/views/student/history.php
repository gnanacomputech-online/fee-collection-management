<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Payment History</h1>
<p class="fs-page-sub">Every payment recorded against your account, with downloadable receipts.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Receipt No.</th><th>Amount</th><th>Method</th><th>Paid On</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="5" class="fs-empty">No payments yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= e($p['receipt_no']) ?></td>
                    <td><?= inr($p['amount']) ?></td>
                    <td class="text-capitalize"><?= e($p['method']) ?></td>
                    <td><?= format_datetime($p['paid_at']) ?></td>
                    <td>
                        <a href="/index.php?route=payments/receipt&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="/index.php?route=payments/receipt/download&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-accent">Download</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
