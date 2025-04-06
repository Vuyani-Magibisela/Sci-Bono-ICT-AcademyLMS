<section class="course-header">
    <div class="container">
        <div class="course-title">
            <h1><?= htmlspecialchars($course['title']) ?></h1>
            <p><?= htmlspecialchars($course['description']) ?></p>
        </div>
        <div class="course-actions">
            <?php if (isset($isEnrolled) && $isEnrolled): ?>
                <a href="/courses/<?= htmlspecialchars($course['slug']) ?>/modules" class="btn btn-primary">Continue Learning</a>
            <?php else: ?>
                <form action="/courses/<?= htmlspecialchars($course['id']) ?>/enroll" method="post">
                    <button type="submit" class="btn btn-primary">Enroll Now</button>
                </form>
            <?php endif; ?>
            <button class="btn btn-outline download-btn">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </div>
    </div>
</section>

<section class="course-description">
    <div class="container">
        <div class="description-content">
            <div class="description-text">
                <h2>Course Overview</h2>
                <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                
                <?php if (isset($progress)): ?>
                    <div class="course-progress-container">
                        <h3>Your Progress</h3>
                        <div class="progress-text">
                            <span><?= round($progress['progress']) ?>% Complete</span>
                            <span><?= $progress['completed_lessons'] ?? 0 ?>/<?= $course['total_lessons'] ?? 0 ?> Lessons</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?= round($progress['progress']) ?>%;"></div>
                        </div>
                        <div class="progress-status">
                            Last activity: <?= isset($progress['last_accessed_at']) ? date('M j, Y', strtotime($progress['last_accessed_at'])) : 'Not started' ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="description-image">
                <?php if ($course['featured_image']): ?>
                    <img src="<?= htmlspecialchars($course['featured_image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                <?php else: ?>
                    <img src="/svg/illustrations/course-default.svg" alt="Course illustration">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="course-modules">
    <div class="container">
        <h2>Course Modules</h2>
        
        <?php if (empty($modules)): ?>
            <div class="no-modules">
                <p>No modules available yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="modules-grid">
                <?php foreach ($modules as $module): ?>
                    <div class="module-card">
                        <h3><?= htmlspecialchars($module['title']) ?></h3>
                        <p><?= htmlspecialchars($module['description']) ?></p>
                        <div class="module-meta">
                            <span><i class="fas fa-book"></i> <?= htmlspecialchars($module['lesson_count']) ?> lessons</span>
                        </div>
                        <a href="/courses/<?= htmlspecialchars($course['slug']) ?>/modules/<?= htmlspecialchars($module['id']) ?>" class="module-link">Explore Module</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="course-info">
    <div class="container">
        <div class="info-cards">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Who This Course Is For</h3>
                <ul>
                    <li>Beginners with no prior experience in this field</li>
                    <li>Individuals transitioning to new tech roles</li>
                    <li>Students interested in practical, hands-on learning</li>
                    <li>Anyone looking to develop in-demand skills</li>
                </ul>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>What You'll Learn</h3>
                <ul>
                    <li>Core principles and methodologies</li>
                    <li>Practical techniques and tools</li>
                    <li>Industry-standard best practices</li>
                    <li>How to apply skills to real-world projects</li>
                    <li>Problem-solving approaches</li>
                </ul>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Course Requirements</h3>
                <ul>
                    <li>A laptop or desktop computer</li>
                    <li>Basic computer literacy</li>
                    <li>Reliable internet connection</li>
                    <li>No prior experience required</li>
                    <li>Interest in learning and problem-solving</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="course-cta">
    <div class="container">
        <h2>Ready to Start Your Learning Journey?</h2>
        <p>Join this comprehensive course and take your first step toward becoming a professional.</p>
        <?php if (isset($isEnrolled) && $isEnrolled): ?>
            <a href="/courses/<?= htmlspecialchars($course['slug']) ?>/modules" class="btn btn-primary">Continue Learning</a>
        <?php else: ?>
            <form action="/courses/<?= htmlspecialchars($course['id']) ?>/enroll" method="post">
                <button type="submit" class="btn btn-primary">Enroll Now - It's Free</button>
            </form>
        <?php endif; ?>
    </div>
</section>