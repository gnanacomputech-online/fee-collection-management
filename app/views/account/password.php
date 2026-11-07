<?php require __DIR__ . '/../partials/header.php'; ?>
<?php require __DIR__ . '/../partials/navbar.php'; ?>
<h1 class="fs-page-title">Change Password</h1>
<p class="fs-page-sub">Update the password on your own account.</p>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/index.php?route=account/password" novalidate>
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>"
                               id="current_password" name="current_password" required>
                        <?php if (!empty($errors['current_password'])): ?><div class="invalid-feedback"><?= e($errors['current_password']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>"
                               id="new_password" name="new_password" minlength="8" required>
                        <?php if (!empty($errors['new_password'])): ?><div class="invalid-feedback"><?= e($errors['new_password']) ?></div><?php else: ?>
                        <div class="form-text">At least 8 characters.</div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                               id="confirm_password" name="confirm_password" minlength="8" required>
                        <?php if (!empty($errors['confirm_password'])): ?><div class="invalid-feedback"><?= e($errors['confirm_password']) ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
