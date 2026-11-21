<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Models\User;

final class ProfileController
{
    public function show(): void
    {
        if (!Auth::check()) redirect('/login');

        $user = Auth::user();

        view('profile/index', [
            'layout'    => 'app',
            'pageTitle' => 'My Profile',
            'user'      => $user,
        ]);
    }

    public function updateInfo(): void
    {
        if (!Auth::check()) redirect('/login');

        $user = Auth::user();
        $userId = (int)$user['employee_id'];

        $name  = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone_number'] ?? '');

        if ($name === '') {
            Flash::error('Full name is required.');
            redirect('/profile');
        }
        if ($phone === '' || strlen($phone) < 9) {
            Flash::error('Valid phone number is required.');
            redirect('/profile');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Invalid email address.');
            redirect('/profile');
        }

        $existing = User::findByPhone($phone);
        if ($existing && (int)$existing['employee_id'] !== $userId) {
            Flash::error('That phone number is already used by another account.');
            redirect('/profile');
        }

        User::update($userId, [
            'full_name'    => $name,
            'email'        => $email,
            'phone_number' => $phone,
        ]);

        $_SESSION['full_name'] = $name;
        $_SESSION['phone']     = $phone;

        Flash::success('Profile updated.');
        redirect('/profile');
    }

    public function updatePassword(): void
    {
        if (!Auth::check()) redirect('/login');

        $user = Auth::user();
        if (!$user) redirect('/login');

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password_hash'])) {
            Flash::error('Current password is incorrect.');
            redirect('/profile');
        }
        if (strlen($new) < 6) {
            Flash::error('New password must be at least 6 characters.');
            redirect('/profile');
        }
        if ($new !== $confirm) {
            Flash::error('Passwords do not match.');
            redirect('/profile');
        }
        if ($new === $current) {
            Flash::error('New password must be different from current password.');
            redirect('/profile');
        }

        User::resetPassword((int)$user['employee_id'], $new);

        Flash::success('Password changed successfully. Use the new password next time you log in.');
        redirect('/profile');
    }
}
