<?php

require_once __DIR__ . '/../models/User.php';

/**
 * Self-service password change, available to every logged-in role (Super Admin,
 * Accountant, Student). Added alongside the random-per-account temporary password
 * generated at enrollment (see RosterImporter/AdminController::studentStore) so a
 * student (or any account) is no longer stuck on the password an admin picked/generated
 * at creation time forever.
 */
class AccountController
{
    private function guard(): array
    {
        Auth::requireLogin();
        $user = User::find((int)Auth::id());
        if (!$user) {
            Auth::logout();
            redirect('login');
        }
        return $user;
    }

    public function passwordForm(): void
    {
        $this->guard();
        $pageTitle = 'Change Password';
        $errors = [];
        require __DIR__ . '/../views/account/password.php';
    }

    public function passwordUpdate(): void
    {
        $user = $this->guard();
        Csrf::requireValid();

        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $v = new Validator();
        $v->required($_POST, 'current_password', 'Current password')
          ->required($_POST, 'new_password', 'New password')->minLength($_POST, 'new_password', 8, 'New password')
          ->required($_POST, 'confirm_password', 'Confirm password');

        $errors = $v->errors();

        if (empty($errors) && !password_verify($current, $user['password'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        }
        if (empty($errors) && $new !== $confirm) {
            $errors['confirm_password'] = 'New password and confirmation do not match.';
        }
        if (empty($errors) && $new === $current) {
            $errors['new_password'] = 'New password must be different from the current password.';
        }

        if (!empty($errors)) {
            $pageTitle = 'Change Password';
            require __DIR__ . '/../views/account/password.php';
            return;
        }

        User::updatePassword((int)$user['id'], $new);
        flash_set('success', 'Password updated. Use your new password next time you log in.');
        redirect(Auth::dashboardRoute(Auth::role()));
    }
}
