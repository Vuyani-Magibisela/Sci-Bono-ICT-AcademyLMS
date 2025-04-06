<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="container">
            <h1>My Profile</h1>
            <p>Manage your account settings and information</p>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="container">
            <div class="dashboard-grid">
                <div class="dashboard-sidebar">
                    <div class="user-profile">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="profile-image">
                        <?php else: ?>
                            <div class="profile-initial"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div class="user-info">
                            <h3><?= htmlspecialchars($user['name']) ?></h3>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                    
                    <nav class="dashboard-nav">
                        <ul>
                            <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="/courses/my-courses"><i class="fas fa-book"></i> My Courses</a></li>
                            <li><a href="/profile" class="active"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a href="/certificates"><i class="fas fa-certificate"></i> Certificates</a></li>
                            <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="dashboard-main">
                    <div class="profile-content">
                        <div class="profile-tabs">
                            <button class="tab-button active" data-tab="personal">Personal Information</button>
                            <button class="tab-button" data-tab="password">Change Password</button>
                            <button class="tab-button" data-tab="avatar">Profile Picture</button>
                        </div>
                        
                        <div class="tab-content active" id="personal-tab">
                            <h2>Personal Information</h2>
                            
                            <?php if (isset($successMessage)): ?>
                                <div class="success-message">
                                    <?= htmlspecialchars($successMessage) ?>
                                </div>
                            <?php endif; ?>
                            
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
                            
                            <form action="/profile/update" method="post" class="profile-form">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    <small class="form-text">Email address cannot be changed.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea id="bio" name="bio" class="form-control" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                        
                        <div class="tab-content" id="password-tab">
                            <h2>Change Password</h2>
                            
                            <form action="/profile/change-password" method="post" class="profile-form">
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                        
                        <div class="tab-content" id="avatar-tab">
                            <h2>Profile Picture</h2>
                            
                            <div class="profile-picture-container">
                                <div class="current-picture">
                                    <?php if ($user['profile_image']): ?>
                                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile picture" class="profile-image-large">
                                    <?php else: ?>
                                        <div class="profile-initial-large"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <form action="/profile/upload-picture" method="post" enctype="multipart/form-data" class="picture-form">
                                    <div class="form-group">
                                        <label for="profile_image" class="form-label">Select New Picture</label>
                                        <input type="file" id="profile_image" name="profile_image" class="form-control-file" accept="image/*" required>
                                        <small class="form-text">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF.</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Upload New Picture</button>
                                    
                                    <?php if ($user['profile_image']): ?>
                                        <a href="/profile/remove-picture" class="btn btn-outline-danger">Remove Picture</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>