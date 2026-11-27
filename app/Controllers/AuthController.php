<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Security;
use App\Core\Database;
use App\Models\User;
use App\Models\Company;
use App\Models\AuditLog;

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth.login', ['pageTitle' => 'Login'], 'auth');
    }

    public function login(): void
    {
        $this->validateCsrf();

        $email = trim($this->request->input('email', ''));
        $password = $this->request->input('password', '');

        if (empty($email) || empty($password)) {
            $this->flash('danger', 'Please enter both email and password.');
            $this->redirect('/login');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !Security::verifyPassword($password, $user['password_hash'])) {
            $this->flash('danger', 'Invalid email or password.');
            $this->redirect('/login');
        }

        if ($user['status'] !== 'active') {
            $this->flash('danger', 'Your account is suspended. Please contact support.');
            $this->redirect('/login');
        }

        // Update last login
        $db = Database::getInstance();
        $db->execute("UPDATE `users` SET `last_login_at` = NOW() WHERE `id` = :id", ['id' => $user['id']]);

        // Login Session
        Session::login($user);
        AuditLog::log('Login', 'Auth', $user['id']);

        if ($user['is_super_admin']) {
            $this->flash('success', 'Welcome back, Super Admin!');
            $this->redirect('/admin');
        }

        // Resolve Tenant
        $companyModel = new Company();
        $companies = $companyModel->getUserCompanies((int)$user['id']);

        if (empty($companies)) {
            $this->flash('info', 'Welcome to MYSLIP! Let us create your first business company.');
            $this->redirect('/company/create');
        }

        // Select first active company
        $comp = $companies[0];
        $fy = $db->fetchOne("SELECT * FROM `financial_years` WHERE `company_id` = :comp_id AND `is_current` = 1 LIMIT 1", ['comp_id' => $comp['id']]);
        Session::setTenant($comp, $fy, (int)$comp['role_id']);

        $this->flash('success', "Logged in successfully as {$user['full_name']}.");
        $this->redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth.register', ['pageTitle' => 'Create Free Account'], 'auth');
        // Clear old inputs after displaying view so subsequent clean visits don't show old data
        Session::remove('_old_input');
    }

    public function register(): void
    {
        $this->validateCsrf();

        $fullName = trim($this->request->input('full_name', ''));
        $email = strtolower(trim($this->request->input('email', '')));
        $mobile = trim($this->request->input('mobile', ''));
        $password = $this->request->input('password', '');
        $confirmPassword = $this->request->input('confirm_password', '');

        // Preserve input values so the user does not have to retype everything
        Session::set('_old_input', [
            'full_name' => $fullName,
            'email' => $email,
            'mobile' => $mobile
        ]);

        if (empty($fullName) || empty($email) || empty($mobile) || empty($password)) {
            $this->flash('danger', 'Please fill in all required fields.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('danger', 'Registration not possible: Invalid email address format (' . htmlspecialchars($email) . ').');
            $this->redirect('/register');
        }

        // Validate 10-digit mobile number
        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            $this->flash('danger', 'Registration not possible: Mobile number must be exactly 10 numeric digits.');
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            $this->flash('danger', 'Registration not possible: Password must be at least 6 characters long.');
            $this->redirect('/register');
        }

        if ($password !== $confirmPassword) {
            $this->flash('danger', 'Registration not possible: Passwords do not match. Please re-enter.');
            $this->redirect('/register');
        }

        // Check if email already registered
        if ($this->userModel->findByEmail($email)) {
            $this->flash('danger', 'Registration not possible: Email [' . htmlspecialchars($email) . '] is already registered. Please log in or use a different email.');
            $this->redirect('/register');
        }

        // Check if mobile number already registered
        if ($this->userModel->findByMobile($mobile)) {
            $this->flash('danger', 'Registration not possible: Mobile number [' . htmlspecialchars($mobile) . '] is already registered. Please log in or use a different number.');
            $this->redirect('/register');
        }

        // All validations passed! Clear temporary old input
        Session::remove('_old_input');

        $token = Security::randomToken(32);
        $userId = $this->userModel->create([
            'full_name' => $fullName,
            'email' => $email,
            'mobile' => $mobile,
            'password_hash' => Security::hashPassword($password),
            'is_super_admin' => 0,
            'is_email_verified' => 1, // Auto-verify on registration for smooth trial onboarding
            'verification_token' => $token,
            'status' => 'active'
        ]);

        $user = $this->userModel->find($userId);
        Session::login($user);
        AuditLog::log('User Registered', 'Auth', $userId);

        $this->flash('success', 'Your account has been created! Please configure your company details.');
        $this->redirect('/company/create');
    }

    public function logout(): void
    {
        AuditLog::log('Logout', 'Auth', Session::getUserId());
        Session::destroy();
        $this->redirect('/login');
    }

    public function showForgotPassword(): void
    {
        $this->view('auth.forgot_password', ['pageTitle' => 'Forgot Password'], 'auth');
    }

    public function forgotPassword(): void
    {
        $this->sendResetLink();
    }

    public function sendResetLink(): void
    {
        $this->validateCsrf();
        $email = trim($this->request->input('email', ''));

        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $token = Security::randomToken(32);
            $db = Database::getInstance();
            $db->execute("INSERT INTO `password_resets` (`email`, `token`, `expires_at`) VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))", [
                'email' => $email,
                'token' => $token
            ]);
            $this->flash('success', 'Password reset instructions have been simulated. Reset Token: ' . $token);
        } else {
            $this->flash('info', 'If your email is registered, you will receive reset instructions.');
        }

        $this->redirect('/login');
    }
}
