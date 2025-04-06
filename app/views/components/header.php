<header>
    <div class="container">
        <div class="logo">
<img src="/Sci-Bono-ICT-AcademyLMS/public/img/logo.svg" alt="YDP Training Logo">
            <h1>Sci-Bono<span class="highlight">ICT Academy</span></h1>
        </div>
        <button class="mobile-menu-btn">
            <i class="fas fa-bars"></i>
        </button>
        <nav id="main-nav">
            <ul>
                <li><a href="/" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a></li>
                <li><a href="/about" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a></li>
                <li><a href="/courses" class="<?= $currentPage === 'courses' ? 'active' : '' ?>">Courses</a></li>
                <li><a href="/benefits" class="<?= $currentPage === 'benefits' ? 'active' : '' ?>">Benefits</a></li>
                <li><a href="/contact" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
            </ul>
        </nav>
        <div class="header-controls">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-dropdown">
                    <button class="dropdown-toggle">
                        <span><?= htmlspecialchars($userName ?? 'User') ?></span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="/dashboard">Dashboard</a>
                        <a href="/profile">My Profile</a>
                        <a href="/courses/my-courses">My Courses</a>
                        <a href="/logout">Sign Out</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/login" class="btn btn-outline">Sign In</a>
                <a href="/register" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
