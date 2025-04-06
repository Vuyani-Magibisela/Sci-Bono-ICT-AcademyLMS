<?php
namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\CourseProgress;
use Core\Database;

/**
 * Course Progress Service
 * 
 * Handles tracking and updating user progress in courses
 */
class CourseProgressService
{
    /**
     * @var CourseProgress The course progress model instance
     */
    private $progressModel;
    
    /**
     * @var Lesson The lesson model instance
     */
    private $lessonModel;
    
    /**
     * @var Course The course model instance
     */
    private $courseModel;
    
    /**
     * @var Module The module model instance
     */
    private $moduleModel;
    
    /**
     * @var Database The database instance
     */
    private $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->progressModel = new CourseProgress();
        $this->lessonModel = new Lesson();
        $this->courseModel = new Course();
        $this->moduleModel = new Module();
        $this->db = Database::getInstance();
    }

    /**
     * Check if a user is enrolled in a course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if enrolled
     */
    public function isUserEnrolled($userId, $courseId)
    {
        return $this->progressModel->isUserEnrolled($userId, $courseId);
    }

    /**
     * Enroll a user in a course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool Success or failure
     */
    public function enrollUser($userId, $courseId)
    {
        // Check if user is already enrolled
        if ($this->isUserEnrolled($userId, $courseId)) {
            // Already enrolled, update last accessed time
            return $this->progressModel->updateLastAccessed($userId, $courseId);
        }
        
        // Enroll user
        $enrollmentData = [
            'user_id' => $userId,
            'course_id' => $courseId,
            'progress' => 0,
            'status' => 'enrolled',
            'enrolled_at' => date('Y-m-d H:i:s'),
            'last_accessed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Insert enrollment
        $result = $this->progressModel->create($enrollmentData);
        
        if ($result) {
            // Create notification for enrollment
            $this->createNotification($userId, 'course_enrolled', $courseId);
            
            // Record this as an activity
            $this->recordActivity($userId, 'course_enrolled', $courseId);
        }
        
        return $result;
    }

    /**
     * Mark a lesson as complete for a user
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID
     * @return float|bool Updated course progress percentage or false on failure
     */
    public function markLessonComplete($userId, $lessonId)
    {
        // Check if already completed
        if ($this->isLessonCompleted($userId, $lessonId)) {
            // Get course ID
            $courseId = $this->lessonModel->getCourseIdByLessonId($lessonId);
            
            // Get current progress
            $progress = $this->getUserCourseProgress($userId, $courseId);
            
            return $progress ? $progress['progress'] : false;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Insert lesson progress
            $query = "
                INSERT INTO lesson_progress (user_id, lesson_id, status, started_at, completed_at)
                VALUES (:user_id, :lesson_id, 'completed', :started_at, :completed_at)
            ";
            
            $this->db->query($query, [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
                'started_at' => date('Y-m-d H:i:s', strtotime('-5 minutes')), // Approximate start time
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            // Get course ID
            $courseId = $this->lessonModel->getCourseIdByLessonId($lessonId);
            
            // Count total lessons in course
            $totalLessons = $this->lessonModel->countLessonsByCourseId($courseId);
            
            // Count completed lessons
            $query = "
                SELECT COUNT(*) as completed_count
                FROM lesson_progress lp
                JOIN lessons l ON lp.lesson_id = l.id
                JOIN modules m ON l.module_id = m.id
                WHERE lp.user_id = :user_id
                AND m.course_id = :course_id
                AND lp.status = 'completed'
            ";
            
            $result = $this->db->query($query, [
                'user_id' => $userId,
                'course_id' => $courseId
            ])->fetch();
            
            $completedLessons = $result['completed_count'] ?? 0;
            
            // Calculate progress percentage
            $progress = ($totalLessons > 0) ? ($completedLessons / $totalLessons) * 100 : 0;
            
            // Update course progress
            $query = "
                UPDATE course_progress
                SET progress = :progress, 
                    status = CASE 
                        WHEN :progress >= 100 THEN 'completed'
                        ELSE 'in_progress'
                    END,
                    completed_at = CASE
                        WHEN :progress >= 100 THEN :now
                        ELSE null
                    END,
                    last_accessed_at = :now,
                    updated_at = :now
                WHERE user_id = :user_id
                AND course_id = :course_id
            ";
            
            $this->db->query($query, [
                'progress' => $progress,
                'now' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'course_id' => $courseId
            ]);
            
            // Create notification if progress milestone reached
            if ($progress >= 25 && $progress < 26) {
                $this->createNotification($userId, 'progress_25', $courseId);
            } elseif ($progress >= 50 && $progress < 51) {
                $this->createNotification($userId, 'progress_50', $courseId);
            } elseif ($progress >= 75 && $progress < 76) {
                $this->createNotification($userId, 'progress_75', $courseId);
            } elseif ($progress >= 100) {
                $this->createNotification($userId, 'course_completed', $courseId);
                
                // Create certificate if course is completed
                if ($progress >= 100) {
                    $this->createCertificate($userId, $courseId);
                }
            }
            
            // Record this as an activity
            $lesson = $this->lessonModel->findById($lessonId);
            $this->recordActivity($userId, 'lesson_completed', $lessonId, $lesson['title'] ?? 'Lesson');
            
            // Commit transaction
            $this->db->commit();
            
            return $progress;
        } catch (\Exception $e) {
            // Roll back transaction on error
            $this->db->rollBack();
            
            // Log the error
            error_log('Error marking lesson complete: ' . $e->getMessage());
            
            return false;
        }
    }

    /**
     * Check if a lesson is completed by a user
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID
     * @return bool True if completed
     */
    public function isLessonCompleted($userId, $lessonId)
    {
        $query = "
            SELECT COUNT(*) as count
            FROM lesson_progress
            WHERE user_id = :user_id
            AND lesson_id = :lesson_id
            AND status = 'completed'
        ";
        
        $result = $this->db->query($query, [
            'user_id' => $userId,
            'lesson_id' => $lessonId
        ])->fetch();
        
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get user's progress in a course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array|bool Progress data or false if not enrolled
     */
    public function getUserCourseProgress($userId, $courseId)
    {
        // Get basic progress information
        $query = "
            SELECT * FROM course_progress
            WHERE user_id = :user_id
            AND course_id = :course_id
        ";
        
        $progress = $this->db->query($query, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        if (!$progress) {
            return false;
        }
        
        // Get completed lessons count
        $query = "
            SELECT COUNT(*) as completed_lessons
            FROM lesson_progress lp
            JOIN lessons l ON lp.lesson_id = l.id
            JOIN modules m ON l.module_id = m.id
            WHERE lp.user_id = :user_id
            AND m.course_id = :course_id
            AND lp.status = 'completed'
        ";
        
        $completedResult = $this->db->query($query, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        // Get total lessons count
        $totalLessons = $this->lessonModel->countLessonsByCourseId($courseId);
        
        // Add counts to progress data
        $progress['completed_lessons'] = $completedResult['completed_lessons'] ?? 0;
        $progress['total_lessons'] = $totalLessons;
        
        return $progress;
    }

    /**
     * Get module progress for a user
     * 
     * @param int $userId User ID
     * @param int $moduleId Module ID
     * @return array Module progress data
     */
    public function getModuleProgress($userId, $moduleId)
    {
        // Get all lessons in the module
        $query = "
            SELECT l.id, l.title, l.order_index,
                   (SELECT COUNT(*) FROM lesson_progress 
                    WHERE user_id = :user_id 
                    AND lesson_id = l.id 
                    AND status = 'completed') > 0 as completed
            FROM lessons l
            WHERE l.module_id = :module_id
            ORDER BY l.order_index
        ";
        
        $lessons = $this->db->query($query, [
            'user_id' => $userId,
            'module_id' => $moduleId
        ])->fetchAll();
        
        // Count completed lessons
        $completedLessons = 0;
        foreach ($lessons as $lesson) {
            if ($lesson['completed']) {
                $completedLessons++;
            }
        }
        
        // Calculate progress
        $totalLessons = count($lessons);
        $progress = ($totalLessons > 0) ? ($completedLessons / $totalLessons) * 100 : 0;
        
        return [
            'module_id' => $moduleId,
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'progress' => $progress,
            'lessons' => $lessons
        ];
    }

    /**
     * Submit a quiz for a user
     * 
     * @param int $userId User ID
     * @param int $lessonId Lesson ID with quiz
     * @param array $answers User's answers
     * @return array Result with success status, score, and feedback
     */
    public function submitQuiz($userId, $lessonId, $answers)
    {
        // Get the quiz for this lesson
        $query = "
            SELECT q.* FROM quizzes q
            WHERE q.lesson_id = :lesson_id
        ";
        
        $quiz = $this->db->query($query, [
            'lesson_id' => $lessonId
        ])->fetch();
        
        if (!$quiz) {
            return [
                'success' => false,
                'message' => 'Quiz not found for this lesson'
            ];
        }
        
        // Get all questions and correct answers
        $query = "
            SELECT qq.id, qq.question_text, qq.question_type, qq.points,
                   (SELECT GROUP_CONCAT(qo.id SEPARATOR ',')
                    FROM quiz_question_options qo
                    WHERE qo.question_id = qq.id AND qo.is_correct = 1) as correct_answers
            FROM quiz_questions qq
            WHERE qq.quiz_id = :quiz_id
            ORDER BY qq.order_index
        ";
        
        $questions = $this->db->query($query, [
            'quiz_id' => $quiz['id']
        ])->fetchAll();
        
        // Calculate score
        $totalPoints = 0;
        $earnedPoints = 0;
        $feedback = [];
        
        foreach ($questions as $question) {
            $totalPoints += $question['points'];
            $questionId = $question['id'];
            $userAnswer = $answers["question_{$questionId}"] ?? null;
            
            // Skip if no answer provided
            if ($userAnswer === null) {
                $feedback[$questionId] = [
                    'correct' => false,
                    'message' => 'No answer provided'
                ];
                continue;
            }
            
            $correctAnswers = explode(',', $question['correct_answers']);
            $isCorrect = false;
            
            // Handle different question types
            switch ($question['question_type']) {
                case 'multiple_choice':
                    // Single selection
                    $isCorrect = in_array($userAnswer, $correctAnswers);
                    break;
                    
                case 'multiple_answer':
                    // Multiple selections
                    if (!is_array($userAnswer)) {
                        $userAnswer = [$userAnswer];
                    }
                    
                    // Check if all selected answers are correct
                    $isCorrect = count(array_diff($userAnswer, $correctAnswers)) === 0 && 
                                 count(array_diff($correctAnswers, $userAnswer)) === 0;
                    break;
                    
                case 'true_false':
                    // True/False question
                    $isCorrect = $userAnswer === $correctAnswers[0];
                    break;
                    
                case 'short_answer':
                    // Text answer - simple exact match
                    // In a real app, you'd want more sophisticated matching
                    $isCorrect = strtolower(trim($userAnswer)) === strtolower(trim($correctAnswers[0]));
                    break;
            }
            
            if ($isCorrect) {
                $earnedPoints += $question['points'];
                $feedback[$questionId] = [
                    'correct' => true,
                    'message' => 'Correct!'
                ];
            } else {
                $feedback[$questionId] = [
                    'correct' => false,
                    'message' => 'Incorrect',
                    'correct_answer' => $correctAnswers
                ];
            }
        }
        
        // Calculate percentage score
        $percentageScore = ($totalPoints > 0) ? ($earnedPoints / $totalPoints) * 100 : 0;
        $passed = $percentageScore >= $quiz['passing_score'];
        
        // Store the quiz result
        $quizResultData = [
            'user_id' => $userId,
            'quiz_id' => $quiz['id'],
            'score' => $percentageScore,
            'passed' => $passed ? 1 : 0,
            'time_taken_seconds' => $_POST['time_taken'] ?? null,
            'completed_at' => date('Y-m-d H:i:s'),
            'answers_data' => json_encode([
                'answers' => $answers,
                'feedback' => $feedback
            ])
        ];
        
        $this->db->query(
            "INSERT INTO quiz_results (user_id, quiz_id, score, passed, time_taken_seconds, completed_at, answers_data)
             VALUES (:user_id, :quiz_id, :score, :passed, :time_taken_seconds, :completed_at, :answers_data)",
            $quizResultData
        );
        
        // If passed, mark the lesson as complete
        if ($passed) {
            $this->markLessonComplete($userId, $lessonId);
            
            // Create notification for quiz passed
            $this->createNotification($userId, 'quiz_passed', $lessonId);
            
            // Record this as an activity
            $this->recordActivity($userId, 'quiz_completed', $lessonId, 'Quiz passed with ' . round($percentageScore) . '%');
        } else {
            // Create notification for quiz failed
            $this->createNotification($userId, 'quiz_failed', $lessonId);
            
            // Record this as an activity
            $this->recordActivity($userId, 'quiz_failed', $lessonId, 'Quiz attempt with ' . round($percentageScore) . '%');
        }
        
        return [
            'success' => true,
            'score' => $percentageScore,
            'passed' => $passed,
            'feedback' => $feedback,
            'passing_score' => $quiz['passing_score']
        ];
    }

    /**
     * Get user's course activity
     * 
     * @param int $userId User ID
     * @param int $limit Number of activities to return
     * @return array Activities
     */
    public function getUserActivity($userId, $limit = 10)
    {
        $query = "
            (
                -- Lesson completions
                SELECT 
                    'lesson_completed' as type,
                    l.title,
                    CONCAT('Completed lesson: ', l.title) as description,
                    lp.completed_at as date,
                    CONCAT('/lessons/', l.id) as link
                FROM lesson_progress lp
                JOIN lessons l ON lp.lesson_id = l.id
                WHERE lp.user_id = :user_id AND lp.status = 'completed'
            )
            UNION
            (
                -- Course enrollments
                SELECT 
                    'course_enrolled' as type,
                    c.title,
                    CONCAT('Enrolled in course: ', c.title) as description,
                    cp.enrolled_at as date,
                    CONCAT('/courses/', c.slug) as link
                FROM course_progress cp
                JOIN courses c ON cp.course_id = c.id
                WHERE cp.user_id = :user_id
            )
            UNION
            (
                -- Quiz completions
                SELECT 
                    CASE 
                        WHEN qr.passed = 1 THEN 'quiz_completed'
                        ELSE 'quiz_failed'
                    END as type,
                    CONCAT('Quiz for ', l.title) as title,
                    CASE 
                        WHEN qr.passed = 1 THEN CONCAT('Passed quiz with score of ', ROUND(qr.score), '%')
                        ELSE CONCAT('Attempted quiz with score of ', ROUND(qr.score), '%')
                    END as description,
                    qr.completed_at as date,
                    CONCAT('/lessons/', l.id, '/quiz-result') as link
                FROM quiz_results qr
                JOIN quizzes q ON qr.quiz_id = q.id
                JOIN lessons l ON q.lesson_id = l.id
                WHERE qr.user_id = :user_id
            )
            ORDER BY date DESC
            LIMIT :limit
        ";
        
        $activities = $this->db->query($query, [
            'user_id' => $userId,
            'limit' => $limit
        ])->fetchAll();
        
        // Format the date as time ago
        foreach ($activities as &$activity) {
            $activity['time_ago'] = $this->timeAgo(strtotime($activity['date']));
        }
        
        return $activities;
    }

    /**
     * Get courses in progress for a user
     * 
     * @param int $userId User ID
     * @param int $limit Number of courses to return
     * @return array Courses in progress
     */
    public function getInProgressCourses($userId, $limit = 5)
    {
        $query = "
            SELECT c.*, cp.progress, cp.last_accessed_at
            FROM course_progress cp
            JOIN courses c ON cp.course_id = c.id
            WHERE cp.user_id = :user_id
            AND cp.status = 'in_progress'
            ORDER BY cp.last_accessed_at DESC
            LIMIT :limit
        ";
        
        return $this->db->query($query, [
            'user_id' => $userId,
            'limit' => $limit
        ])->fetchAll();
    }

    /**
     * Create a notification for a user
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param int $referenceId Related entity ID (course, lesson, etc.)
     * @return bool Success or failure
     */
    private function createNotification($userId, $type, $referenceId)
    {
        $title = '';
        $message = '';
        $link = '';
        
        // Set notification details based on type
        switch ($type) {
            case 'course_enrolled':
                $course = $this->courseModel->findById($referenceId);
                $title = 'Course Enrollment';
                $message = 'You have successfully enrolled in ' . ($course['title'] ?? 'a course');
                $link = '/courses/' . ($course['slug'] ?? $referenceId);
                break;
                
            case 'lesson_completed':
                $lesson = $this->lessonModel->findById($referenceId);
                $title = 'Lesson Completed';
                $message = 'You have completed the lesson: ' . ($lesson['title'] ?? 'a lesson');
                $link = '/lessons/' . $referenceId;
                break;
                
            case 'quiz_passed':
                $lesson = $this->lessonModel->findById($referenceId);
                $title = 'Quiz Passed';
                $message = 'Congratulations! You passed the quiz for ' . ($lesson['title'] ?? 'a lesson');
                $link = '/lessons/' . $referenceId . '/quiz-result';
                break;
                
            case 'quiz_failed':
                $lesson = $this->lessonModel->findById($referenceId);
                $title = 'Quiz Attempted';
                $message = 'You can try the quiz again for ' . ($lesson['title'] ?? 'a lesson');
                $link = '/lessons/' . $referenceId . '/quiz';
                break;
                
            case 'course_completed':
                $course = $this->courseModel->findById($referenceId);
                $title = 'Course Completed';
                $message = 'Congratulations! You have completed ' . ($course['title'] ?? 'a course');
                $link = '/certificates';
                break;
                
            case 'progress_25':
            case 'progress_50':
            case 'progress_75':
                $progress = substr($type, 9); // Extract "25", "50", or "75"
                $course = $this->courseModel->findById($referenceId);
                $title = 'Course Progress: ' . $progress . '%';
                $message = 'You are ' . $progress . '% through ' . ($course['title'] ?? 'your course');
                $link = '/courses/' . ($course['slug'] ?? $referenceId);
                break;
        }
        
        // Insert notification
        $query = "
            INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
            VALUES (:user_id, :type, :title, :message, :link, 0, :created_at)
        ";
        
        return $this->db->query($query, [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'created_at' => date('Y-m-d H:i:s')
        ])->rowCount() > 0;
    }

    /**
     * Record a user activity
     * 
     * @param int $userId User ID
     * @param string $type Activity type
     * @param int $referenceId Related entity ID
     * @param string $title Activity title
     * @return bool Success or failure
     */
    private function recordActivity($userId, $type, $referenceId, $title = '')
    {
        // In a more complex application, you would store user activities in a dedicated table
        // For simplicity, we're using the existing notification system as our activity log
        return true;
    }

    /**
     * Create a certificate for a completed course
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return int|string|bool Certificate ID, or false on failure
     */
    private function createCertificate($userId, $courseId)
    {
        // Check if certificate already exists
        $query = "
            SELECT id FROM certificates
            WHERE user_id = :user_id
            AND course_id = :course_id
        ";
        
        $existing = $this->db->query($query, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        if ($existing) {
            return $existing['id'];
        }
        
        // Generate a unique certificate number
        $certificateNumber = 'CERT-' . date('Y') . '-' . strtoupper(substr(md5($userId . $courseId . time()), 0, 8));
        
        // Insert certificate
        $query = "
            INSERT INTO certificates (user_id, course_id, certificate_number, issued_at)
            VALUES (:user_id, :course_id, :certificate_number, :issued_at)
        ";
        
        $result = $this->db->query($query, [
            'user_id' => $userId,
            'course_id' => $courseId,
            'certificate_number' => $certificateNumber,
            'issued_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result->rowCount() > 0) {
            $certificateId = $this->db->lastInsertId();
            
            // Create notification for certificate
            $course = $this->courseModel->findById($courseId);
            $this->createNotification($userId, 'certificate_issued', $certificateId);
            
            // In a real application, you would generate a PDF certificate here
            // and update the file_path in the certificates table
            
            return $certificateId;
        }
        
        return false;
    }

    /**
     * Format timestamp as time ago string
     * 
     * @param int $timestamp Timestamp
     * @return string Formatted time ago
     */
    private function timeAgo($timestamp)
    {
        $current_time = time();
        $difference = $current_time - $timestamp;
        
        if ($difference < 60) {
            return 'just now';
        } elseif ($difference < 3600) {
            $minutes = floor($difference / 60);
            return $minutes == 1 ? '1 minute ago' : $minutes . ' minutes ago';
        } elseif ($difference < 86400) {
            $hours = floor($difference / 3600);
            return $hours == 1 ? '1 hour ago' : $hours . ' hours ago';
        } elseif ($difference < 604800) {
            $days = floor($difference / 86400);
            return $days == 1 ? '1 day ago' : $days . ' days ago';
        } elseif ($difference < 2592000) {
            $weeks = floor($difference / 604800);
            return $weeks == 1 ? '1 week ago' : $weeks . ' weeks ago';
        } elseif ($difference < 31536000) {
            $months = floor($difference / 2592000);
            return $months == 1 ? '1 month ago' : $months . ' months ago';
        } else {
            $years = floor($difference / 31536000);
            return $years == 1 ? '1 year ago' : $years . ' years ago';
        }
    }
}