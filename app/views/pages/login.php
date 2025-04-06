<div class="login-container">
    <a href="/" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="login-image">
        <div class="login-image-content">
            <h2>Welcome Back!</h2>
            <p>Continue your learning journey with YDP Training and unlock your potential in tech.</p>
            <p>Don't have an account? <a href="/register" style="color: white; text-decoration: underline;">Sign up now</a></p>
        </div>
    </div>
    
    <div class="login-form-container">
        <form class="login-form" id="login-form" method="post" action="/login">
            <h1>Sign In</h1>
            <p class="login-subtitle">Access your courses, track your progress, and continue learning</p>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-check">
                <input type="checkbox" id="remember" name="remember_me" value="1" class="form-check-input">
                <label for="remember">Remember me</label>
                <a href="/forgot-password" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="login-btn">Sign In</button>
            
            <div class="login-divider">
                <span>or sign in with</span>
            </div>
            
            <div class="social-login">
                <a href="/auth/google" class="social-btn google"><i class="fab fa-google"></i></a>
                <a href="/auth/facebook" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="/auth/linkedin" class="social-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
            </div>
            
            <div class="signup-link">
                Don't have an account? <a href="/register">Sign up</a>
            </div>
        </form>
    </div>
</div>