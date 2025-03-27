<?php
namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;

/**
 * Auth Controller
 * 
 * Handles user authentication and registration
 */
class AuthController extends BaseController
{
    /**
     * Display login form
     *
     * @return void
     */
    public function loginForm()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        $this->render('pages/login', [
            'title' => 'Sign In - YDP Training'
        ]);
    }
    
    /**
     * Process login
     *
     * @return void
     */
    public function login()
    {
        $data = $this->getFormData();
        
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $rememberMe = isset($data['remember_me']) && $data['remember_me'] == '1';
        
        $authService = new AuthService();
        $result = $authService->login($email, $password, $rememberMe);
        
        if ($result['success']) {
            // Check for redirect after login
            $redirect = $_SESSION['redirect_after_login'] ?? '/dashboard';
            unset($_SESSION['redirect_after_login']);
            
            $this->redirect($redirect);
        } else {
            // Return to login form with error
            $this->render('pages/login', [
                'title' => 'Sign In - YDP Training',
                'error' => $result['message'],
                'email' => $email
            ]);
        }
    }
    
    /**
     * Display registration form
     *
     * @return void
     */
    public function registerForm()
    {
        // Redirect if already logged in
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        
        $this->render('pages/register', [
            'title' => 'Register - YDP Training'
        ]);
    }
    
    /**
     * Process registration
     *
     * @return void
     */
    public function register()
    {
        $data = $this->getFormData();
        
        $authService = new AuthService();
        $result = $authService->register($data);
        
        if ($result['success']) {
            // Set success message
            $_SESSION['flash_message'] = 'Registration successful! Please sign in.';
            $_SESSION['flash_type'] = 'success';
            
            $this->redirect('/login');
        } else {
            // Return to registration form with errors
            $this->render('pages/register', [
                'title' => 'Register - YDP Training',
                'errors' => $result['errors'],
                'formData' => $data
            ]);
        }
    }
    
    /**
     * Process logout
     *
     * @return void
     */
    public function logout()
    {
        $authService = new AuthService();
        $authService->logout();
        
        $this->redirect('/');
    }
    
    /**
     * Display forgot password form
     *
     * @return void
     */
    public function forgotPasswordForm()
    {
        $this->render('pages/forgot-password', [
            'title' => 'Forgot Password - YDP Training'
        ]);
    }
    
    /**
     * Process forgot password
     *
     * @return void
     */
    public function forgotPassword()
    {
        $data = $this->getFormData();
        $email = $data['email'] ?? '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('pages/forgot-password', [
                'title' => 'Forgot Password - YDP Training',
                'error' => 'Please enter a valid email address',
                'email' => $email
            ]);
            return;
        }
        
        $authService = new AuthService();
        $result = $authService->sendPasswordResetLink($email);
        
        // Always show success even if email not found (security best practice)
        $_SESSION['flash_message'] = 'If your email exists in our system, you will receive a password reset link.';
        $_SESSION['flash_type'] = 'success';
        
        $this->redirect('/login');
    }
    
    /**
     * Display reset password form
     *
     * @param string $token Reset token
     * @return void
     */
    public function resetPasswordForm($token)
    {
        $authService = new AuthService();
        if (!$authService->validateResetToken($token)) {
            $_SESSION['flash_message'] = 'Invalid or expired password reset link.';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('/login');
        }
        
        $this->render('pages/reset-password', [
            'title' => 'Reset Password - YDP Training',
            'token' => $token
        ]);
    }
    
    /**
     * Process reset password
     *
     * @return void
     */
    public function resetPassword()
    {
        $data = $this->getFormData();
        
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';
        
        $errors = [];
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $this->render('pages/reset-password', [
                'title' => 'Reset Password - YDP Training',
                'token' => $token,
                'errors' => $errors
            ]);
            return;
        }
        
        $authService = new AuthService();
        $result = $authService->resetPassword($token, $password);
        
        if ($result['success']) {
            $_SESSION['flash_message'] = 'Your password has been reset successfully. Please sign in.';
            $_SESSION['flash_type'] = 'success';
            $this->redirect('/login');
        } else {
            $this->render('pages/reset-password', [
                'title' => 'Reset Password - YDP Training',
                'token' => $token,
                'error' => $result['message']
            ]);
        }
    }
}