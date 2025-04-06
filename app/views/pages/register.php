<div class="register-container">
    <a href="/" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="register-image">
        <div class="register-image-content">
            <h2>Join the YDP Community</h2>
            <p>Start your learning journey today and unlock new skills and opportunities in tech.</p>
            <p>Already have an account? <a href="/login" style="color: white; text-decoration: underline;">Sign in now</a></p>
        </div>
    </div>
    
    <div class="register-form-container">
        <form class="register-form" id="register-form" method="post" action="/register">
            <h1>Create Account</h1>
            <p class="register-subtitle">Join our community of learners and start your journey</p>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="error-container">
                    <ul class="error-list">
                        <?php foreach ($errors as $field => $fieldErrors): ?>
                            <?php foreach ($fieldErrors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small class="form-text">Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
            </div>
            
            <div class="form-check">
                <input type="checkbox" id="terms" name="terms" value="1" class="form-check-input" <?= isset($formData['terms']) && $formData['terms'] ? 'checked' : '' ?> required>
                <label for="terms">I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a></label>
            </div>
            
            <button type="submit" class="register-btn">Create Account</button>
            
            <div class="login-divider">
                <span>or sign up with</span>
            </div>
            
            <div class="social-login">
                <a href="/auth/google" class="social-btn google"><i class="fab fa-google"></i></a>
                <a href="/auth/facebook" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="/auth/linkedin" class="social-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
            </div>
            
            <div class="login-link">
                Already have an account? <a href="/login">Sign in</a>
            </div>
        </form>
    </div>
</div>