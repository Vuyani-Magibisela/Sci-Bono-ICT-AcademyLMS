<?php
namespace App\Services;

use App\Models\User;
use Core\Database;

/**
 * Authentication Service
 * 
 * Handles user authentication, registration, and password management
 */
class AuthService
{
    /**
     * @var User User model instance
     */
    private $userModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @param bool $rememberMe Whether to remember the user
     * @return array Result with success status and message
     */
    public function login($email, $password, $rememberMe = false)
    {
        // Validate input
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Please enter both email and password'
            ];
        }

        // Check if user exists
        $user = $this->userModel->verifyPassword($email, $password);

        if (!$user) {
            // For security, don't specify whether email or password is incorrect
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            return [
                'success' => false,
                'message' => 'Your account is not active. Please contact support.'
            ];
        }

        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Update last login time
        $this->userModel->update($user['id'], [
            'last_active_at' => date('Y-m-d H:i:s')
        ]);

        // Set remember me cookie if requested
        if ($rememberMe) {
            $token = $this->userModel->createRememberToken($user['id']);
            
            if ($token) {
                $expiry = time() + (86400 * REMEMBER_ME_DAYS); // Days converted to seconds
                setcookie('remember_token', $token, [
                    'expires' => $expiry,
                    'path' => '/',
                    'domain' => '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    }

    /**
     * Register a new user
     * 
     * @param array $data User registration data
     * @return array Result with success status, message, and errors
     */
    public function register($data)
    {
        $errors = [];
        
        // Validate name
        if (empty($data['name'])) {
            $errors['name'][] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'][] = 'Name must be at least 2 characters';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $errors['email'][] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Please enter a valid email address';
        } else {
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                $errors['email'][] = 'Email address is already registered';
            }
        }
        
        // Validate password
        if (empty($data['password'])) {
            $errors['password'][] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'][] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $data['password']) || 
                  !preg_match('/[a-z]/', $data['password']) ||
                  !preg_match('/[0-9]/', $data['password'])) {
            $errors['password'][] = 'Password must include at least one uppercase letter, one lowercase letter, and one number';
        }
        
        // Validate password confirmation
        if (empty($data['password_confirm'])) {
            $errors['password_confirm'][] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'][] = 'Passwords do not match';
        }
        
        // Validate terms agreement
        if (empty($data['terms']) || $data['terms'] != '1') {
            $errors['terms'][] = 'You must agree to the Terms of Service and Privacy Policy';
        }
        
        // If there are validation errors, return them
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Please correct the errors in the form',
                'errors' => $errors
            ];
        }
        
        // Generate a username from email if not provided
        if (empty($data['username'])) {
            $data['username'] = $this->userModel->generateUsername($data['email'], $data['name']);
        }
        
        // Prepare user data for insertion
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'], // Will be hashed in the model
            'role' => 'student', // Default role
            'status' => 'active', // Default status
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert user into database
        $userId = $this->userModel->create($userData);
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'An error occurred during registration. Please try again.',
                'errors' => ['general' => ['Registration failed']]
            ];
        }
        
        // Return success
        return [
            'success' => true,
            'message' => 'Registration successful! You can now log in.',
            'user_id' => $userId
        ];
    }

    /**
     * Log out a user
     * 
     * @return bool True on success
     */
    public function logout()
    {
        // Delete remember me token if it exists
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $this->userModel->deleteRememberToken($token);
            
            // Expire the cookie
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        
        // Clear session data
        $_SESSION = [];
        
        // If a session cookie exists, destroy it
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax'
            ]);
        }
        
        // Destroy the session
        session_destroy();
        
        return true;
    }

    /**
     * Check if a user is authenticated
     * 
     * @return bool True if user is authenticated
     */
    public function isAuthenticated()
    {
        // Check if user is logged in via session
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Check if user has a remember me token
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            $user = $this->userModel->findByRememberToken($token);
            
            if ($user) {
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Update last login time
                $this->userModel->update($user['id'], [
                    'last_active_at' => date('Y-m-d H:i:s')
                ]);
                
                return true;
            }
            
            // Invalid token, clear it
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        
        return false;
    }

    /**
     * Get the current authenticated user
     * 
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->userModel->findById($_SESSION['user_id']);
    }

    /**
     * Send password reset link
     * 
     * @param string $email User email
     * @return array Result with success status and message
     */
    public function sendPasswordResetLink($email)
    {
        // Check if email exists
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // For security, don't reveal if email exists or not
            return [
                'success' => true,
                'message' => 'If your email exists in our system, you will receive a password reset link.'
            ];
        }
        
        // Generate reset token
        $token = $this->userModel->createPasswordResetToken($email);
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'An error occurred while generating a reset token. Please try again later.'
            ];
        }
        
        // Build reset URL
        $resetUrl = SITE_URL . '/reset-password/' . $token;
        
        // Send email
        $subject = "Password Reset Request";
        $message = "Hello {$user['name']},\n\n";
        $message .= "You requested a password reset for your account on " . APP_NAME . ".\n\n";
        $message .= "Please click the link below to reset your password:\n";
        $message .= $resetUrl . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you didn't request a password reset, please ignore this email.\n\n";
        $message .= "Thank you,\nThe " . APP_NAME . " Team";
        
        $headers = "From: " . EMAIL_NAME . " <" . EMAIL_FROM . ">\r\n";
        $headers .= "Reply-To: " . EMAIL_FROM . "\r\n";
        
        // In a real application, you'd use a proper email library
        // such as PHPMailer or Symfony Mailer
        // This is a simplified example
        $mailSent = mail($user['email'], $subject, $message, $headers);
        
        if (!$mailSent) {
            // Log email failure
            error_log("Failed to send password reset email to {$user['email']}");
            
            return [
                'success' => false,
                'message' => 'Failed to send password reset email. Please try again later.'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Password reset link has been sent to your email address.'
        ];
    }

    /**
     * Validate password reset token
     * 
     * @param string $token Reset token
     * @return bool True if token is valid
     */
    public function validateResetToken($token)
    {
        return $this->userModel->validateResetToken($token) !== false;
    }

    /**
     * Reset password using token
     * 
     * @param string $token Reset token
     * @param string $password New password
     * @return array Result with success status and message
     */
    public function resetPassword($token, $password)
    {
        // Validate token
        $userId = $this->userModel->validateResetToken($token);
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Invalid or expired password reset token. Please request a new password reset link.'
            ];
        }
        
        // Validate password
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters',
                'errors' => ['password' => ['Password must be at least 8 characters']]
            ];
        }
        
        if (!preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)) {
            return [
                'success' => false,
                'message' => 'Password must include at least one uppercase letter, one lowercase letter, and one number',
                'errors' => ['password' => ['Password must include at least one uppercase letter, one lowercase letter, and one number']]
            ];
        }
        
        // Reset password
        $result = $this->userModel->resetPassword($token, $password);
        
        if (!$result) {
            return [
                'success' => false,
                'message' => 'An error occurred while resetting your password. Please try again later.'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Your password has been reset successfully. You can now log in with your new password.'
        ];
    }
}