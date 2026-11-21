<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Flash;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use App\Models\Branch;
use App\Core\Database;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            if (($_SESSION['role'] ?? '') === 'ADMIN' && (int)($_SESSION['branch_id'] ?? 0) === 0) {
                redirect('/select-branch');
            }
            redirect('/dashboard');
        }
        view('auth/login', ['layout' => 'auth', 'pageTitle' => 'Sign In']);
    }

    public function login(): void
    {
        $phone    = trim($_POST['phone'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $v = new Validator($_POST);
        $v->required('phone', 'Phone')->required('password', 'Password');
        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = ['phone' => $phone];
            redirect('/login');
        }

        $user = User::attempt($phone, $password);
        if (!$user) {
            Flash::error('Invalid phone or password.');
            $_SESSION['_old'] = ['phone' => $phone];
            redirect('/login');
        }

        Auth::login($user);

        // Branch count for this business
        $branchCount = Branch::count();

        if ($user['role'] === 'ADMIN') {
            if ($branchCount === 0) {
                // Owner hasn't created any branch yet
                redirect('/setup/branch');
            }
            // Always show picker for admin
            redirect('/select-branch');
        }

        // Non-admin
        if ((int)$user['branch_id'] === 0) {
            Auth::logout();
            Flash::error('Your account has no branch assigned. Contact the administrator.');
            redirect('/login');
        }

        redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if (Auth::check()) redirect('/dashboard');
        // Locked after first owner exists
        if (User::count() > 0) {
            Flash::error('Registration is closed. Please sign in.');
            redirect('/login');
        }
        view('auth/register', ['layout' => 'auth', 'pageTitle' => 'Create Account']);
    }

    public function register(): void
    {
        if (User::count() > 0) {
            Flash::error('Registration is closed.');
            redirect('/login');
        }

        $data = [
            'business_name'    => trim($_POST['business_name'] ?? ''),
            'full_name'        => trim($_POST['full_name'] ?? ''),
            'phone'            => trim($_POST['phone'] ?? ''),
            'email'            => trim($_POST['email'] ?? ''),
            'password'         => (string)($_POST['password'] ?? ''),
            'confirm_password' => (string)($_POST['confirm_password'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('business_name', 'Business name')
          ->required('full_name', 'Your name')
          ->required('phone', 'Phone number')
          ->min('phone', 9, 'Phone')
          ->email('email', 'Email')
          ->min('password', 6, 'Password')
          ->matches('password', 'confirm_password', 'Password confirmation');

        if ($v->fails()) {
            foreach ($v->errors() as $msg) Flash::error($msg);
            $_SESSION['_old'] = $data;
            redirect('/register');
        }

        if (User::findByPhone($data['phone'])) {
            Flash::error('That phone number is already registered.');
            $_SESSION['_old'] = $data;
            redirect('/register');
        }

        try {
            Database::begin();
            $id = User::create([
                'full_name'    => $data['full_name'],
                'phone_number' => $data['phone'],
                'email'        => $data['email'],
                'password'     => $data['password'],
                'role'         => 'ADMIN',
            ]);
            Database::execute(
                "UPDATE app_settings SET business_name = ?, business_phone = ?, business_email = ?, last_updated = ? WHERE id = 1",
                [$data['business_name'], $data['phone'], $data['email'] ?: null, now()]
            );
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            Flash::error('Registration failed: ' . $e->getMessage());
            redirect('/register');
        }

        $user = User::find($id);
        Auth::login($user);
        Flash::success('Welcome! Let\'s set up your first branch.');
        redirect('/setup/branch');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/');
    }
}
