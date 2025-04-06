<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="container">
            <h1>My Dashboard</h1>
            <p>Track your progress and continue learning</p>
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
                            <li><a href="/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="/courses/my-courses"><i class="fas fa-book"></i> My Courses</a></li>
                            <li><a href="/profile"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a href="/certificates"><i class="fas fa-certificate"></i> Certificates</a></li>
                            <li><a href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </div>
                
                <div class="dashboard-main">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Enrolled Courses</h3>
                                <p class="stat-value"><?= $stats['enrolled_courses'] ?? 0 ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Completed Lessons</h3>
                                <p class="stat-value"><?= $stats['completed_lessons'] ?? 0 ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Completed Quizzes</h3>
                                <p class="stat-value"><?= $stats['completed_quizzes'] ?? 0 ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Avg Quiz Score</h3>
                                <p class="stat-value"><?= isset($stats['average_quiz_score']) ? round($stats['average_quiz_score']) . '%' : 'N/A' ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="recent-activity">
                        <h2>Recent Activity</h2>
                        <?php if (empty($activities)): ?>
                            <div class="no-activity">
                                <p>No recent activity. Start learning to see your progress here!</p>
                                <a href="/courses" class="btn btn-primary">Explore Courses</a>
                            </div>
                        <?php else: ?>
                            <div class="activity-list">
                                <?php foreach ($activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <?php if ($activity['type'] === 'lesson_completed'): ?>
                                                <i class="fas fa-check-circle"></i>
                                            <?php elseif ($activity['type'] === 'course_enrolled'): ?>
                                                <i class="fas fa-book"></i>
                                            <?php elseif ($activity['type'] === 'quiz_completed'): ?>
                                                <i class="fas fa-question-circle"></i>
                                            <?php else: ?>
                                                <i class="fas fa-star"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-content">
                                            <h4><?= htmlspecialchars($activity['title']) ?></h4>
                                            <p><?= htmlspecialchars($activity['description']) ?></p>
                                            <div class="activity-meta">
                                                <span class="activity-time"><?= htmlspecialchars($activity['time_ago']) ?></span>
                                                <?php if (!empty($activity['link'])): ?>
                                                    <a href="<?= htmlspecialchars($activity['link']) ?>" class="activity-link">View</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="course-progress-section">
                        <h2>My Course Progress</h2>
                        <?php if (empty($inProgressCourses)): ?>
                            <div class="no-courses">
                                <p>You haven't started any courses yet.</p>
                                <a href="/courses" class="btn btn-primary">Explore Courses</a>
                            </div>
                        <?php else: ?>
                            <div class="course-progress-list">
                                <?php foreach ($inProgressCourses as $course): ?>
                                    <div class="course-progress-item">
                                        <div class="course-info">
                                            <h3><?= htmlspecialchars($course['title']) ?></h3>
                                            <div class="progress-text">
                                                <span><?= round($course['progress']) ?>% Complete</span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress" style="width: <?= round($course['progress']) ?>%;"></div>
                                            </div>
                                        </div>
                                        <a href="/courses/<?= htmlspecialchars($course['slug']) ?>/modules" class="btn btn-outline">Continue</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>