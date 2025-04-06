<section class="course-header">
    <div class="container">
        <div class="course-title">
            <h1>Our Courses</h1>
            <p>Develop your skills with our comprehensive training programs</p>
        </div>
        <div class="course-actions">
            <form action="/courses/search" method="get" class="search-form">
                <input type="text" name="q" placeholder="Search courses..." class="search-input">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>
</section>

<section class="course-filters">
    <div class="container">
        <div class="filter-container">
            <div class="filter-group">
                <label>Difficulty</label>
                <div class="filter-options">
                    <a href="/courses?difficulty=beginner" class="<?= ($difficulty ?? '') === 'beginner' ? 'active' : '' ?>">Beginner</a>
                    <a href="/courses?difficulty=intermediate" class="<?= ($difficulty ?? '') === 'intermediate' ? 'active' : '' ?>">Intermediate</a>
                    <a href="/courses?difficulty=advanced" class="<?= ($difficulty ?? '') === 'advanced' ? 'active' : '' ?>">Advanced</a>
                </div>
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select name="category" class="filter-select" onchange="location = this.value;">
                    <option value="/courses">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="/courses?category=<?= htmlspecialchars($category['slug']) ?>" <?= ($categorySlug ?? '') === $category['slug'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</section>

<section class="course-listing">
    <div class="container">
        <?php if (empty($courses)): ?>
            <div class="no-courses">
                <h3>No courses found</h3>
                <p>Try adjusting your search or filters to find what you're looking for.</p>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-image">
                            <?php if ($course['featured_image']): ?>
                                <img src="<?= htmlspecialchars($course['featured_image']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                            <?php endif; ?>
                            <div class="course-badge"><?= ucfirst(htmlspecialchars($course['difficulty_level'])) ?></div>
                        </div>
                        <div class="course-content">
                            <h3><?= htmlspecialchars($course['title']) ?></h3>
                            <p><?= htmlspecialchars(substr($course['description'], 0, 120)) ?>...</p>
                            <div class="course-meta">
                                <span><i class="far fa-clock"></i> <?= htmlspecialchars($course['duration_hours'] ? floor($course['duration_hours'] / 40) . ' weeks' : 'Self-paced') ?></span>
                                <span><i class="fas fa-book"></i> <?= htmlspecialchars($course['total_modules'] ?? 0) ?> modules</span>
                            </div>
                            <a href="/courses/<?= htmlspecialchars($course['slug']) ?>" class="course-link">View Course</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (isset($pagination) && $pagination['pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="/courses?page=<?= $pagination['current_page'] - 1 ?><?= isset($categorySlug) ? '&category=' . $categorySlug : '' ?><?= isset($difficulty) ? '&difficulty=' . $difficulty : '' ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                        <a href="/courses?page=<?= $i ?><?= isset($categorySlug) ? '&category=' . $categorySlug : '' ?><?= isset($difficulty) ? '&difficulty=' . $difficulty : '' ?>" class="page-link <?= $pagination['current_page'] === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['current_page'] < $pagination['pages']): ?>
                        <a href="/courses?page=<?= $pagination['current_page'] + 1 ?><?= isset($categorySlug) ? '&category=' . $categorySlug : '' ?><?= isset($difficulty) ? '&difficulty=' . $difficulty : '' ?>" class="page-link">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>