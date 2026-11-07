<?php $pageTitle = 'Login'; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login &middot; FeeSetu</title>
<link rel="icon" type="image/png" href="/assets/img/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="fs-auth-wrap">
    <div class="fs-auth-card">
        <div class="fs-auth-logo">
            <img src="/assets/img/logo.png" alt="FeeSetu logo" class="auth-logo-img" width="52" height="60">
            <div>
                <div class="brand-font" style="font-size:1.3rem;line-height:1;">FeeSetu</div>
                <div style="color:var(--fs-muted);font-size:0.8rem;">Fee Collection &amp; Management</div>
            </div>
        </div>

        <?php if (!empty($errors['login'])): ?>
            <div class="alert alert-danger py-2"><?= e($errors['login']) ?></div>
        <?php endif; ?>

        <form method="POST" action="/index.php?route=login" novalidate>
            <?= Csrf::field() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       id="email" name="email" value="<?= old($old ?? [], 'email') ?>" required autofocus>
                <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       id="password" name="password" required>
                <?php if (!empty($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log in</button>
        </form>

        <details class="fs-creds">
            <summary>Demo credentials</summary>
            <p class="fs-creds-hint">Pick a role to fill the form.</p>
            <ul class="fs-creds-list">
                <li>
                    <button type="button" class="fs-cred" data-email="admin@feecollect.edu" data-password="Admin@123">
                        <span class="fs-cred-role">Super Admin</span>
                        <span class="fs-cred-line"><span>admin@feecollect.edu</span><span>Admin@123</span></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="fs-cred" data-email="accountant1@feecollect.edu" data-password="Acc@1234">
                        <span class="fs-cred-role">Accountant</span>
                        <span class="fs-cred-line"><span>accountant1@feecollect.edu</span><span>Acc@1234</span></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="fs-cred" data-email="student1@feecollect.edu" data-password="Student@123">
                        <span class="fs-cred-role">Student</span>
                        <span class="fs-cred-line"><span>student1@feecollect.edu</span><span>Student@123</span></span>
                    </button>
                </li>
            </ul>
        </details>
    </div>
</div>
<script>
document.querySelectorAll('.fs-cred').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('email').value = btn.dataset.email;
        document.getElementById('password').value = btn.dataset.password;
        document.querySelectorAll('.fs-cred').forEach(function (b) { b.classList.toggle('is-selected', b === btn); });
        document.getElementById('password').focus();
    });
});
</script>
</body>
</html>
