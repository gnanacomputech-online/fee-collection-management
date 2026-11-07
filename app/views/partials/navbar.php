<?php
$role = Auth::role();
$currentRoute = $_GET['route'] ?? '';

// Each entry is either a plain link (string label) or a dropdown group (['label' => ..., 'items' => [...]]).
$navItems = [];
if ($role === 'super_admin') {
    $navItems = [
        'admin/dashboard' => 'Dashboard',
        ['label' => 'People', 'items' => [
            'admin/students'    => 'Students',
            'admin/classes'     => 'Classes',
            'admin/accountants' => 'Accountants',
        ]],
        ['label' => 'Fees', 'items' => [
            'fee-heads'      => 'Fee Heads',
            'fee-structures' => 'Fee Structures',
        ]],
        'dues'    => 'Dues Lookup',
        'reports' => 'Reports',
    ];
} elseif ($role === 'accountant') {
    $navItems = [
        'accountant/dashboard' => 'Dashboard',
        ['label' => 'Fees', 'items' => [
            'fee-heads'      => 'Fee Heads',
            'fee-structures' => 'Fee Structures',
        ]],
        'dues'    => 'Dues Lookup',
        'reports' => 'Reports',
    ];
} elseif ($role === 'student') {
    $navItems = [
        'student/dashboard' => 'Dashboard',
        'student/dues'      => 'My Dues',
        'student/history'   => 'Payment History',
    ];
}

$isActive = static function (string $route) use ($currentRoute): bool {
    return $currentRoute === $route || str_starts_with($currentRoute, $route . '/');
};
$roleLabel = ['super_admin' => 'Super Admin', 'accountant' => 'Accountant', 'student' => 'Student'][$role] ?? $role;
$userName = (string)Auth::name();
$initial = strtoupper(mb_substr($userName, 0, 1));
?>
<nav class="navbar navbar-expand-lg navbar-dark fs-navbar">
    <div class="container">
        <a class="navbar-brand" href="/index.php?route=<?= e(Auth::dashboardRoute($role)) ?>">
            <img src="/assets/img/logo.png" alt="" class="brand-logo" width="33" height="38"> FeeSetu
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fsNav" aria-controls="fsNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="fsNav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($navItems as $route => $item): ?>
                    <?php if (is_array($item)): ?>
                        <?php $groupActive = false; foreach ($item['items'] as $r => $_) { if ($isActive($r)) { $groupActive = true; break; } } ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= $groupActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= e($item['label']) ?></a>
                            <ul class="dropdown-menu fs-dropdown">
                                <?php foreach ($item['items'] as $r => $label): ?>
                                    <li><a class="dropdown-item <?= $isActive($r) ? 'active' : '' ?>" href="/index.php?route=<?= e($r) ?>"><?= e($label) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isActive($route) ? 'active' : '' ?>" href="/index.php?route=<?= e($route) ?>"><?= e($item) ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <div class="dropdown fs-user">
                <button class="fs-user-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="fs-avatar"><?= e($initial) ?></span>
                    <span class="fs-user-meta">
                        <span class="fs-user-name"><?= e($userName) ?></span>
                        <span class="fs-user-role"><?= e($roleLabel) ?></span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end fs-dropdown">
                    <li><a class="dropdown-item <?= $currentRoute === 'account/password' ? 'active' : '' ?>" href="/index.php?route=account/password">Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="/index.php?route=logout" class="mb-0">
                            <?= Csrf::field() ?>
                            <button type="submit" class="dropdown-item fs-logout-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<div class="container fs-main">
    <?php $flashSuccess = flash_get('success'); $flashError = flash_get('error'); ?>
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><?= e($flashSuccess) ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger"><?= e($flashError) ?></div>
    <?php endif; ?>
