<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">New Fee Structure</h1>
<p class="fs-page-sub">Creating this will immediately generate dues for every active student in the selected class.</p>

<div class="card" style="max-width:760px;">
    <div class="card-body">
        <form method="POST" action="/index.php?route=fee-structures/store">
            <?= Csrf::field() ?>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Class</label>
                    <select class="form-select" name="class_id" required>
                        <option value="">Select a class</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= e(SchoolClass::label($c)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Term</label>
                    <input class="form-control" name="term" placeholder="e.g. Term 1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Academic Year</label>
                    <input class="form-control" name="academic_year" placeholder="e.g. 2025-26" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Due Date</label>
                    <input class="form-control" type="date" name="due_date" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Late Fee Type</label>
                    <select class="form-select" name="late_fee_type">
                        <option value="flat">Flat amount</option>
                        <option value="percent">Percent of fee item</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Late Fee Value</label>
                    <input class="form-control" type="number" step="0.01" min="0" name="late_fee_value" value="200" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Grace Days</label>
                    <input class="form-control" type="number" min="0" name="grace_days" value="7" required>
                </div>
            </div>

            <label class="form-label">Fee Head Line Items</label>
            <div id="fee-items">
                <div class="row g-2 mb-2 fee-item-row">
                    <div class="col-7">
                        <select class="form-select" name="fee_head_id[]">
                            <option value="">Select fee head</option>
                            <?php foreach ($feeHeads as $fh): ?>
                                <option value="<?= (int)$fh['id'] ?>"><?= e($fh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <input class="form-control" type="number" step="0.01" min="0" name="amount[]" placeholder="Amount (₹)">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
                    </div>
                </div>
            </div>
            <button type="button" id="add-item-row" class="btn btn-sm btn-outline-primary mb-3">+ Add another fee head</button>

            <button type="submit" class="btn btn-primary w-100">Create Fee Structure</button>
        </form>
    </div>
</div>

<template id="fee-item-template">
    <div class="row g-2 mb-2 fee-item-row">
        <div class="col-7">
            <select class="form-select" name="fee_head_id[]">
                <option value="">Select fee head</option>
                <?php foreach ($feeHeads as $fh): ?>
                    <option value="<?= (int)$fh['id'] ?>"><?= e($fh['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-4">
            <input class="form-control" type="number" step="0.01" min="0" name="amount[]" placeholder="Amount (₹)">
        </div>
        <div class="col-1">
            <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
        </div>
    </div>
</template>

<script>
document.getElementById('add-item-row').addEventListener('click', function () {
    var tpl = document.getElementById('fee-item-template');
    var clone = tpl.content.cloneNode(true);
    document.getElementById('fee-items').appendChild(clone);
});
document.getElementById('fee-items').addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        var rows = document.querySelectorAll('.fee-item-row');
        if (rows.length > 1) {
            e.target.closest('.fee-item-row').remove();
        }
    }
});
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
