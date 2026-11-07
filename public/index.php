<?php

declare(strict_types=1);

// Let PHP's built-in dev server (`php -S`) serve real static files directly
// instead of routing every asset request through the front controller.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $assetPath = __DIR__ . $requestPath;
    if (!str_ends_with($requestPath, '.php') && is_file($assetPath)) {
        return false;
    }
}

date_default_timezone_set('Asia/Kolkata');
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../app/models/StudentDue.php';

require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AccountController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/AccountantController.php';
require_once __DIR__ . '/../app/controllers/StudentController.php';
require_once __DIR__ . '/../app/controllers/FeeController.php';
require_once __DIR__ . '/../app/controllers/DuesController.php';
require_once __DIR__ . '/../app/controllers/PaymentController.php';
require_once __DIR__ . '/../app/controllers/ReportController.php';

// In production this would be a daily cron job; for this local demo it's cheap enough
// (indexed WHERE clause, small dataset) to run opportunistically on every request so
// overdue dues pick up their late fee the moment the grace period lapses.
if (Auth::check()) {
    StudentDue::applyLateFees();
}

$router = new Router();

// ---- Auth ----
$router->get('login', fn() => (new AuthController())->showLogin());
$router->post('login', fn() => (new AuthController())->login());
$router->post('logout', fn() => (new AuthController())->logout());

// ---- Account (any logged-in role) ----
$router->get('account/password', fn() => (new AccountController())->passwordForm());
$router->post('account/password', fn() => (new AccountController())->passwordUpdate());

// ---- Super Admin ----
$router->get('admin/dashboard', fn() => (new AdminController())->dashboard());
$router->get('admin/classes', fn() => (new AdminController())->classes());
$router->post('admin/classes/store', fn() => (new AdminController())->classStore());
$router->post('admin/classes/update', fn() => (new AdminController())->classUpdate());
$router->post('admin/classes/delete', fn() => (new AdminController())->classDelete());
$router->get('admin/students', fn() => (new AdminController())->students());
$router->post('admin/students/store', fn() => (new AdminController())->studentStore());
$router->get('admin/students/import', fn() => (new AdminController())->studentImportForm());
$router->post('admin/students/import', fn() => (new AdminController())->studentImport());
$router->get('admin/students/import/result', fn() => (new AdminController())->studentImportResult());
$router->get('admin/students/view', fn() => (new AdminController())->studentView());
$router->post('admin/students/status', fn() => (new AdminController())->studentStatus());
$router->get('admin/accountants', fn() => (new AdminController())->accountants());
$router->post('admin/accountants/store', fn() => (new AdminController())->accountantStore());
$router->post('admin/accountants/status', fn() => (new AdminController())->accountantStatus());

// ---- Accountant ----
$router->get('accountant/dashboard', fn() => (new AccountantController())->dashboard());

// ---- Fee heads & structures (Super Admin + Accountant) ----
$router->get('fee-heads', fn() => (new FeeController())->heads());
$router->post('fee-heads/store', fn() => (new FeeController())->headStore());
$router->post('fee-heads/update', fn() => (new FeeController())->headUpdate());
$router->post('fee-heads/delete', fn() => (new FeeController())->headDelete());
$router->get('fee-structures', fn() => (new FeeController())->structures());
$router->get('fee-structures/create', fn() => (new FeeController())->structureCreateForm());
$router->post('fee-structures/store', fn() => (new FeeController())->structureStore());
$router->get('fee-structures/view', fn() => (new FeeController())->structureView());
$router->post('fee-structures/status', fn() => (new FeeController())->structureStatus());

// ---- Dues lookup (Super Admin view-only + Accountant view/concession) ----
$router->get('dues', fn() => (new DuesController())->search());
$router->get('dues/view', fn() => (new DuesController())->view());
$router->post('dues/concession', fn() => (new DuesController())->concessionStore());

// ---- Payments (Accountant collects, both roles + owning student can view receipts) ----
$router->get('payments/collect', fn() => (new PaymentController())->collectForm());
$router->post('payments/store', fn() => (new PaymentController())->store());
$router->get('payments/receipt', fn() => (new PaymentController())->receipt());
$router->get('payments/receipt/download', fn() => (new PaymentController())->receiptDownload());

// ---- Reports (Super Admin + Accountant) ----
$router->get('reports', fn() => (new ReportController())->index());

// ---- Student ----
$router->get('student/dashboard', fn() => (new StudentController())->dashboard());
$router->get('student/dues', fn() => (new StudentController())->dues());
$router->get('student/history', fn() => (new StudentController())->history());

$route = $_GET['route'] ?? '';

if ($route === '') {
    if (Auth::check()) {
        redirect(Auth::dashboardRoute(Auth::role()));
    }
    redirect('login');
}

$router->dispatch($_SERVER['REQUEST_METHOD'], $route);
