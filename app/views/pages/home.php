<section class="landing">
    <div class="container">
        <div class="hero">
            <h1>Welcome to <span class="highlight">Sci-Bono</span> Youth Development Program</h1>
            <h2>Building the next generation of tech innovators</h2>
            <p>Join our 8-12 month program offering internationally recognized qualifications in programming and design for unemployed youth aged 18-25.</p>
            <div class="cta-buttons">
                <a href="/courses" class="btn" id="startCourse">Explore Courses</a>
                <a href="/about" class="btn" id="viewContents">Learn More</a>
            </div>
        </div>
    </div>
    <div class="hero-graphics">
        <img src="/illustrations/code-icon.svg" alt="Code Icon" class="graphic-element" id="data">
        <img src="/illustrations/web-icon.svg" alt="Web Development" class="graphic-element" id="brain">
        <img src="/illustrations/tech-icon.svg" alt="Technology" class="graphic-element" id="robot">
    </div>
</section>

<section id="about" class="toc">
    <div class="container">
        <h2>About ICT Academy</h2>
        <div class="about-content">
            <div class="about-text">
                <p>Sci-Bono Youth Development Program is an 8-12-month full-time program that provides 180 unemployed youth aged 18-25 years an opportunity to gain internationally recognised qualifications.</p>
                <p>The ICT Academy offers training in various technological fields, responding to the evolving demands of digital transformation and technological disruption.</p>
                <p>The program is supplemented with essential life skills, entrepreneurship, and job readiness skills to ensure holistic development for all participants.</p>
                <div class="about-stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <h4>180+</h4>
                            <p>Youth Enrolled Annually</p>
                        </div>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>8-12 Months</h4>
                            <p>Intensive Training</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="/illustrations/students.svg" alt="Students learning at Sci-Bono ICT Academy">
            </div>
        </div>
    </div>
</section>

<section id="courses" class="toc">
    <div class="container">
        <h2>What I teach</h2>
        <p class="section-subtitle">Comprehensive training programs in programming and design</p>
        <div class="toc-container">
            <!-- Active UI/UX Design Systems Course -->
            <?php foreach ($featuredCourses as $course): ?>
                <div class="toc-unit <?= $course['status'] === 'active' ? 'active-course' : 'disabled-course' ?>">
                    <?php if ($course['featured']): ?>
                        <div class="course-badge">Now Available</div>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <p><?= htmlspecialchars($course['description']) ?></p>
                    <ul class="course-features">
                        <?php if (!empty($course['duration_hours'])): ?>
                            <li><i class="far fa-clock"></i> <?= htmlspecialchars(floor($course['duration_hours'] / 40)) ?> weeks</li>
                        <?php endif; ?>
                        <li><i class="fas fa-signal"></i> <?= ucfirst(htmlspecialchars($course['difficulty_level'])) ?></li>
                        <li><i class="fas fa-pencil-ruler"></i> Hands-on projects</li>
                    </ul>
                    <?php if ($course['status'] === 'active'): ?>
                        <a href="/courses/<?= htmlspecialchars($course['slug']) ?>" class="toc-link">Start Learning</a>
                    <?php else: ?>
                        <span class="toc-link disabled">Coming Soon</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="benefits" class="benefits-section">
    <div class="container">
        <h2>Program Benefits</h2>
        <p class="section-subtitle">What you'll gain from our Youth Development Program</p>
        <div class="benefits-container">
            <div class="key-concept">
                <div class="benefit-icon">
                    <i class="fas fa-certificate"></i>
                </div>
                <h3>Internationally Recognized Qualifications</h3>
                <p>Earn certifications that are recognized globally and valued by employers.</p>
            </div>

            <div class="key-concept">
                <div class="benefit-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3>Practical Skills Development</h3>
                <p>Gain hands-on experience with real-world projects and industry-standard tools.</p>
            </div>

            <div class="key-concept">
                <div class="benefit-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Entrepreneurship Training</h3>
                <p>Learn essential business skills to start your own tech venture or freelance career.</p>
            </div>

            <div class="key-concept">
                <div class="benefit-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3>Job Readiness Preparation</h3>
                <p>Develop professional skills, build your portfolio, and prepare for job interviews.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2>Ready to Start Your Tech Career?</h2>
        <p>Join our Youth Development Program and gain the skills you need to succeed in the digital economy.</p>
        <div class="cta-buttons">
            <a href="/register" class="btn" id="startCourse">Register Now</a>
            <a href="/courses" class="btn" id="viewContents">View Courses</a>
        </div>
    </div>
</section>