<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Collect Payment</h1>
<p class="fs-page-sub"><?= e($student['name']) ?> &middot; <?= e($student['admission_no']) ?> &middot; <?= e($student['class_name']) ?> - <?= e($student['class_section']) ?></p>

<?php if (empty($outstanding)): ?>
    <p class="fs-empty">This student has no outstanding dues.</p>
    <a href="/index.php?route=dues/view&student_id=<?= (int)$student['id'] ?>" class="btn btn-outline-primary">Back to Dues</a>
<?php else: ?>
<form method="POST" action="/index.php?route=payments/store">
    <?= Csrf::field() ?>
    <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">

    <div class="card mb-4">
        <div class="card-header">Select Fee Items to Pay</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Term</th><th>Fee Head</th><th>Due Date</th><th>Net Payable</th><th>Balance</th><th style="width:160px;">Amount to Pay</th></tr></thead>
                <tbody>
                <?php foreach ($outstanding as $d): ?>
                    <?php $overdue = strtotime($d['due_date']) < strtotime(date('Y-m-d')); ?>
                    <tr>
                        <td><?= e($d['term']) ?></td>
                        <td><?= e($d['fee_head_name']) ?></td>
                        <td><?= format_date($d['due_date']) ?><?= $overdue ? ' <span class="text-danger small">(overdue)</span>' : '' ?></td>
                        <td><?= inr($d['net_payable']) ?></td>
                        <td><?= inr($d['balance']) ?></td>
                        <td>
                            <input type="hidden" name="due_id[]" value="<?= (int)$d['id'] ?>">
                            <input type="number" class="form-control form-control-sm" name="pay_amount[]"
                                   step="0.01" min="0" max="<?= (float)$d['balance'] ?>" placeholder="0.00" data-balance="<?= (float)$d['balance'] ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="max-width:520px;">
        <div class="card-header">Payment Details</div>
        <div class="card-body">
            <div class="mb-2">
                <label class="form-label">Method</label>
                <select class="form-select" name="method" required>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="online">Online / UPI</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Reference No. (cheque no. / transaction id)</label>
                <input class="form-control" name="reference_no">
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <input class="form-control" name="remarks">
            </div>
            <button type="submit" class="btn btn-primary w-100">Record Payment &amp; Generate Receipt</button>
        </div>
    </div>
</form>
<script>
document.querySelectorAll('input[name="pay_amount[]"]').forEach(function (el) {
    el.addEventListener('input', function () {
        var max = parseFloat(el.dataset.balance);
        if (parseFloat(el.value) > max) { el.value = max.toFixed(2); }
    });
});
</script>
<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
