<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Fee Heads</h1>
<p class="fs-page-sub">The individual charge categories (tuition, library, transport...) that fee structures combine.</p>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">All Fee Heads</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Description</th><th></th></tr></thead>
                    <tbody>
                    <?php if (empty($feeHeads)): ?>
                        <tr><td colspan="3" class="fs-empty">No fee heads yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($feeHeads as $fh): ?>
                        <?php $formId = 'edit-head-' . (int)$fh['id']; ?>
                        <tr>
                            <td style="min-width:160px;">
                                <form id="<?= $formId ?>" method="POST" action="/index.php?route=fee-heads/update">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$fh['id'] ?>">
                                </form>
                                <input class="form-control form-control-sm" name="name" form="<?= $formId ?>" value="<?= e($fh['name']) ?>" required>
                            </td>
                            <td><input class="form-control form-control-sm" name="description" form="<?= $formId ?>" value="<?= e($fh['description'] ?? '') ?>"></td>
                            <td class="d-flex gap-2">
                                <button type="submit" form="<?= $formId ?>" class="btn btn-sm btn-outline-primary">Save</button>
                                <form method="POST" action="/index.php?route=fee-heads/delete" onsubmit="return confirm('Delete this fee head?');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$fh['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Add a Fee Head</div>
            <div class="card-body">
                <form method="POST" action="/index.php?route=fee-heads/store">
                    <?= Csrf::field() ?>
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input class="form-control" name="name" placeholder="e.g. Transport Fee" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input class="form-control" name="description" placeholder="Optional">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Fee Head</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
