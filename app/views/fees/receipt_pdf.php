<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; color: #241A3D; font-size: 11px; }
    .header { border-bottom: 2px solid #4C1D95; padding-bottom: 10px; margin-bottom: 18px; }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: middle; padding: 0; }
    .header .brand { font-size: 20px; font-weight: bold; color: #4C1D95; }
    .header .sub { color: #7C7195; font-size: 11px; }
    .meta { width: 100%; margin-bottom: 16px; }
    .meta td { padding: 3px 0; vertical-align: top; }
    .meta .label { color: #7C7195; width: 140px; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.items th { background: #F7F2FF; text-align: left; padding: 6px 8px; border-bottom: 2px solid #E6DDFB; font-size: 11px; text-transform: uppercase; color: #4C1D95; }
    table.items td { padding: 6px 8px; border-bottom: 1px solid #E6DDFB; }
    table.items tfoot td { font-weight: bold; border-top: 2px solid #4C1D95; border-bottom: none; }
    .amount { text-align: right; }
    .footer-note { margin-top: 30px; font-size: 10px; color: #7C7195; text-align: center; }
</style>
</head>
<body>
    <?php
    // DomPDF has remote loading disabled, so the logo is embedded inline as a data URI.
    $logoFile = __DIR__ . '/../../../public/assets/img/logo.png';
    $logoSrc = is_file($logoFile) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile)) : null;
    ?>
    <div class="header">
        <table>
            <tr>
                <?php if ($logoSrc): ?><td style="width:52px;"><img src="<?= $logoSrc ?>" alt="" width="42" height="48"></td><?php endif; ?>
                <td>
                    <div class="brand">FeeSetu</div>
                    <div class="sub">Fee Collection &amp; Management System &mdash; Official Payment Receipt</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta">
        <tr><td class="label">Receipt No.</td><td><strong><?= e($payment['receipt_no']) ?></strong></td>
            <td class="label">Paid On</td><td><?= format_datetime($payment['paid_at']) ?></td></tr>
        <tr><td class="label">Student</td><td><?= e($payment['student_name']) ?></td>
            <td class="label">Admission No.</td><td><?= e($payment['admission_no']) ?></td></tr>
        <tr><td class="label">Class</td><td><?= e($payment['class_name']) ?> - <?= e($payment['class_section']) ?></td>
            <td class="label">Method</td><td style="text-transform:capitalize;"><?= e($payment['method']) ?><?= $payment['reference_no'] ? ' (' . e($payment['reference_no']) . ')' : '' ?></td></tr>
        <tr><td class="label">Recorded By</td><td><?= e($payment['recorded_by_name']) ?></td>
            <td class="label">Remarks</td><td><?= e($payment['remarks'] ?: '-') ?></td></tr>
    </table>

    <table class="items">
        <thead><tr><th>Term</th><th>Fee Head</th><th class="amount">Amount Paid</th></tr></thead>
        <tbody>
        <?php foreach ($allocations as $a): ?>
            <tr>
                <td><?= e($a['term']) ?></td>
                <td><?= e($a['fee_head_name']) ?></td>
                <td class="amount"><?= inr($a['allocated_amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total Paid</td><td class="amount"><?= inr($payment['amount']) ?></td></tr>
        </tfoot>
    </table>

    <p class="footer-note">This is a system-generated receipt and does not require a signature.</p>
</body>
</html>
