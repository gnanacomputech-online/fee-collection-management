<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Bulk Import Student Roster</h1>
<p class="fs-page-sub">Upload an .xlsx sheet to enroll many students at once.</p>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Upload File</div>
            <div class="card-body">
                <form method="POST" action="/index.php?route=admin/students/import" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Roster file (.xlsx, max 5 MB)</label>
                        <input class="form-control" type="file" name="roster" accept=".xlsx" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Import Roster</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Expected Columns</div>
            <div class="card-body">
                <p class="small text-muted">First row must be a header with these column names (any order). The class must already exist under <a href="/index.php?route=admin/classes">Classes &amp; Sections</a> before importing.</p>
                <table class="table table-sm">
                    <thead><tr><th>Column</th><th>Required</th></tr></thead>
                    <tbody>
                        <tr><td><code>admission_no</code></td><td>Yes</td></tr>
                        <tr><td><code>name</code></td><td>Yes</td></tr>
                        <tr><td><code>email</code></td><td>Yes</td></tr>
                        <tr><td><code>class_name</code></td><td>Yes (must match an existing class name)</td></tr>
                        <tr><td><code>section</code></td><td>Yes</td></tr>
                        <tr><td><code>academic_year</code></td><td>Yes</td></tr>
                        <tr><td><code>roll_no</code></td><td>Yes</td></tr>
                        <tr><td><code>guardian_name</code></td><td>No</td></tr>
                        <tr><td><code>guardian_phone</code></td><td>No</td></tr>
                        <tr><td><code>dob</code></td><td>No (YYYY-MM-DD)</td></tr>
                        <tr><td><code>address</code></td><td>No</td></tr>
                    </tbody>
                </table>
                <p class="small text-muted mb-0">Every imported student gets a random temporary password, listed once on the result page after import -- download or copy it before leaving that page, as it can't be recovered later. Dues are generated automatically against their class's active fee structures.</p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
