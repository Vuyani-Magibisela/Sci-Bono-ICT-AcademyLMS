<div class="course-breadcrumb">
    <div class="container">
        <ul>
            <li><a href="/courses">Courses</a></li>
            <li><a href="/courses/<?= htmlspecialchars($course['slug']) ?>"><?= htmlspecialchars($course['title']) ?></a></li>
            <li><a href="/courses/<?= htmlspecialchars($course['slug']) ?>/modules/<?= htmlspecialchars($module['id']) ?>"><?= htmlspecialchars($module['title']) ?></a></li>
            <li><?= htmlspecialchars($lesson['title']) ?></li>
        </ul>
    </div>
</div>

<div class="lesson-container">
    <div class="lesson-header">
        <div class="progress-container">
            <div class="progress-text">
                <span>Module <?= htmlspecialchars($module['order_index']) ?>, Lesson <?= htmlspecialchars($lesson['order_index']) ?></span>
                <span><?= htmlspecialchars($courseProgress['progress']) ?>% Complete</span>
            </div>
            <div class="progress-bar">
                <div class="progress" style="width: <?= htmlspecialchars($courseProgress['progress']) ?>%;"></div>
            </div>
        </div>
        
        <h1><?= htmlspecialchars($lesson['title']) ?></h1>
        
        <div class="lesson-meta">
            <?php if ($lesson['duration_minutes']): ?>
                <span><i class="far fa-clock"></i> <?= htmlspecialchars($lesson['duration_minutes']) ?> min</span>
            <?php endif; ?>
            <span><i class="far fa-file-alt"></i> <?= ucfirst(htmlspecialchars($lesson['lesson_type'])) ?></span>
        </div>
    </div>

        <div class="lesson-content">
            <div class="lesson-sidebar">
                <div class="module-info">
                    <h3><?= htmlspecialchars($module['title']) ?></h3>
                    <div class="lesson-progress">
                        <span class="completed"><?= $moduleProgress['completed_lessons'] ?></span>
                        <span class="separator">/</span>
                        <span class="total"><?= $moduleProgress['total_lessons'] ?></span>
                        <span class="label">Lessons</span>
                    </div>
                </div>

                <div class="lessons-list">
                    <?php foreach ($moduleLessons as $index => $moduleLesson): ?>
                        <div class="lesson-item <?= $moduleLesson['id'] == $lesson['id'] ? 'active' : '' ?> <?= isset($moduleLesson['completed']) && $moduleLesson['completed'] ? 'completed' : '' ?>">
                            <a href="/lessons/<?= htmlspecialchars($moduleLesson['id']) ?>">
                                <div class="lesson-number"><?= $index + 1 ?></div>
                                <div class="lesson-info">
                                    <div class="lesson-title"><?= htmlspecialchars($moduleLesson['title']) ?></div>
                                    <div class="lesson-type">
                                        <?php if ($moduleLesson['lesson_type'] === 'video'): ?>
                                            <i class="fas fa-video"></i>
                                        <?php elseif ($moduleLesson['lesson_type'] === 'quiz'): ?>
                                            <i class="fas fa-question-circle"></i>
                                        <?php elseif ($moduleLesson['lesson_type'] === 'assignment'): ?>
                                            <i class="fas fa-tasks"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file-alt"></i>
                                        <?php endif; ?>
                                        <?= ucfirst(htmlspecialchars($moduleLesson['lesson_type'])) ?>
                                        <?php if ($moduleLesson['duration_minutes']): ?>
                                            <span class="duration"><?= htmlspecialchars($moduleLesson['duration_minutes']) ?> min</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (isset($moduleLesson['completed']) && $moduleLesson['completed']): ?>
                                    <div class="completion-status">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="lesson-main">
                <div class="lesson-body">
                    <?php if ($lesson['lesson_type'] === 'video' && $lesson['video_url']): ?>
                        <div class="video-container">
                            <iframe src="<?= htmlspecialchars($lesson['video_url']) ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>

                    <div class="content">
                        <?= $lesson['content'] ?>
                    </div>

                    <?php if (!empty($lesson['resources'])): ?>
                        <div class="lesson-resources">
                            <h3>Additional Resources</h3>
                            <div class="resources-list">
                                <?php foreach ($lesson['resources'] as $resource): ?>
                                    <div class="resource-item">
                                        <?php if ($resource['resource_type'] === 'file'): ?>
                                            <i class="fas fa-file-pdf"></i>
                                        <?php elseif ($resource['resource_type'] === 'link'): ?>
                                            <i class="fas fa-link"></i>
                                        <?php else: ?>
                                            <i class="fas fa-code"></i>
                                        <?php endif; ?>
                                        <div class="resource-info">
                                            <h4><?= htmlspecialchars($resource['title']) ?></h4>
                                            <?php if ($resource['description']): ?>
                                                <p><?= htmlspecialchars($resource['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= htmlspecialchars($resource['resource_url']) ?>" 
                                        target="_blank" 
                                        class="resource-link">
                                            <?php if ($resource['resource_type'] === 'file'): ?>
                                                Download
                                            <?php else: ?>
                                                View
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lesson-actions">
                    <?php if ($prevLesson): ?>
                        <a href="/lessons/<?= htmlspecialchars($prevLesson['id']) ?>" class="btn btn-outline prev-lesson">
                            <i class="fas fa-arrow-left"></i> Previous Lesson
                        </a>
                    <?php else: ?>
                        <span class="btn btn-outline disabled">
                            <i class="fas fa-arrow-left"></i> Previous Lesson
                        </span>
                    <?php endif; ?>

                    <?php if ($isCompleted): ?>
                        <button class="btn btn-primary completed-btn" disabled>
                            <i class="fas fa-check-circle"></i> Completed
                        </button>
                    <?php else: ?>
                        <form action="/lessons/<?= htmlspecialchars($lesson['id']) ?>/complete" method="post" class="complete-form">
                            <button type="submit" class="btn btn-primary complete-btn">
                                Mark as Complete
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($nextLesson): ?>
                        <a href="/lessons/<?= htmlspecialchars($nextLesson['id']) ?>" class="btn btn-outline next-lesson">
                            Next Lesson <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="/courses/<?= htmlspecialchars($course['slug']) ?>/modules/<?= htmlspecialchars($module['id']) ?>" class="btn btn-outline">
                            Back to Module
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>
