<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Reports</h1>
<p class="fs-page-sub">Collection totals and the defaulters list.</p>

<ul class="nav nav-pills mb-4">
    <li class="nav-item"><a class="nav-link <?= $type === 'collection' ? 'active' : '' ?>" href="/index.php?route=reports&type=collection">Collection</a></li>
    <li class="nav-item"><a class="nav-link <?= $type === 'defaulters' ? 'active' : '' ?>" href="/index.php?route=reports&type=defaulters">Defaulters</a></li>
</ul>

<?php if ($type === 'defaulters'): ?>

    <form method="GET" action="/index.php" class="row g-2 align-items-end mb-4">
        <input type="hidden" name="route" value="reports">
        <input type="hidden" name="type" value="defaulters">
        <div class="col-sm-4">
            <label class="form-label">Class</label>
            <select class="form-select" name="class_id">
                <option value="">All classes</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $classId === (int)$c['id'] ? 'selected' : '' ?>><?= e(SchoolClass::label($c)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-2"><button class="btn btn-primary w-100" type="submit">Filter</button></div>
    </form>

    <div class="card">
        <div class="card-header">Defaulters (<?= count($defaulters) ?> overdue due items)</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Student</th><th>Class</th><th>Term</th><th>Fee Head</th><th>Due Date</th><th>Balance</th></tr></thead>
                <tbody>
                <?php if (empty($defaulters)): ?>
                    <tr><td colspan="6" class="fs-empty">No overdue dues -- nice work!</td></tr>
                <?php endif; ?>
                <?php foreach ($defaulters as $d): ?>
                    <tr>
                        <td><a href="/index.php?route=dues/view&student_id=<?= (int)$d['student_id'] ?>"><?= e($d['student_name']) ?></a> <span class="text-muted small">(<?= e($d['admission_no']) ?>)</span></td>
                        <td><?= e($d['class_name']) ?> - <?= e($d['class_section']) ?></td>
                        <td><?= e($d['term']) ?></td>
                        <td><?= e($d['fee_head_name']) ?></td>
                        <td><?= format_date($d['due_date']) ?></td>
                        <td><?= inr($d['balance']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php else: ?>

    <form method="GET" action="/index.php" class="row g-2 align-items-end mb-4">
        <input type="hidden" name="route" value="reports">
        <input type="hidden" name="type" value="collection">
        <div class="col-sm-3">
            <label class="form-label">From</label>
            <input class="form-control" type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>">
        </div>
        <div class="col-sm-3">
            <label class="form-label">To</label>
            <input class="form-control" type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>">
        </div>
        <div class="col-sm-2"><button class="btn btn-primary w-100" type="submit">Filter</button></div>
    </form>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Daily Collection</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date</th><th>Payments</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($byDay)): ?><tr><td colspan="3" class="fs-empty">No data.</td></tr><?php endif; ?>
                        <?php foreach ($byDay as $row): ?>
                            <tr><td><?= format_date($row['day']) ?></td><td><?= (int)$row['payment_count'] ?></td><td><?= inr($row['total']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Class-wise Collection</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Class</th><th>Payments</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($byClass)): ?><tr><td colspan="3" class="fs-empty">No data.</td></tr><?php endif; ?>
                        <?php foreach ($byClass as $row): ?>
                            <tr><td><?= e($row['class_name']) ?> - <?= e($row['class_section']) ?></td><td><?= (int)$row['payment_count'] ?></td><td><?= inr($row['total']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Fee-head-wise Collection</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Fee Head</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($byFeeHead)): ?><tr><td colspan="2" class="fs-empty">No data.</td></tr><?php endif; ?>
                        <?php foreach ($byFeeHead as $row): ?>
                            <tr><td><?= e($row['fee_head_name']) ?></td><td><?= inr($row['total']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
