<?php
namespace App\Controllers;

use App\Models\Lesson;
use App\Models\Module;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Services\CourseProgressService;

/**
 * Lesson Controller
 * 
 * Handles lesson-related pages and actions
 */
class LessonController extends BaseController
{
    /**
     * Display a single lesson
     *
     * @param int $id Lesson ID
     * @return void
     */
    public function view($id)
    {
        // Require authentication
        $this->requireAuth();
        
        $lessonModel = new Lesson();
        $lesson = $lessonModel->getLessonWithDetails($id);
        
        if (!$lesson) {
            return $this->notFound();
        }
        
        $moduleModel = new Module();
        $module = $moduleModel->findById($lesson['module_id']);
        
        $courseModel = new Course();
        $course = $courseModel->findById($module['course_id']);
        
        // Get next and previous lessons
        $nextLesson = $lessonModel->getNextLesson($id, $module['id']);
        $prevLesson = $lessonModel->getPreviousLesson($id, $module['id']);
        
        // Get completion status
        $user = $this->getAuthUser();
        $progressService = new CourseProgressService();
        $isCompleted = $progressService->isLessonCompleted($user['id'], $id);
        
        // Get all module lessons for the sidebar
        $moduleLessons = $lessonModel->getLessonsByModuleId($module['id']);
        
        // Get course progress
        $progressModel = new CourseProgress();
        $courseProgress = $progressModel->getUserCourseProgress($user['id'], $course['id']);
        
        $this->render('pages/lessons/view', [
            'title' => $lesson['title'] . ' - YDP Training',
            'lesson' => $lesson,
            'module' => $module,
            'course' => $course,
            'nextLesson' => $nextLesson,
            'prevLesson' => $prevLesson,
            'isCompleted' => $isCompleted,
            'moduleLessons' => $moduleLessons,
            'courseProgress' => $courseProgress
        ], 'course'); // Using a different layout for the course content
    }
    
    /**
     * Mark a lesson as complete
     *
     * @param int $id Lesson ID
     * @return void
     */
    public function markComplete($id)
    {
        // Require authentication
        $this->requireAuth();
        
        $lessonModel = new Lesson();
        $lesson = $lessonModel->findById($id);
        
        if (!$lesson) {
            if ($this->isAjaxRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Lesson not found'
                ], 404);
            }
            
            return $this->notFound();
        }
        
        $user = $this->getAuthUser();
        $progressService = new CourseProgressService();
        $result = $progressService->markLessonComplete($user['id'], $id);
        
        if ($this->isAjaxRequest()) {
            return $this->json([
                'success' => $result !== false,
                'progress' => $result,
                'message' => $result !== false ? 'Lesson marked as complete' : 'Error marking lesson as complete'
            ]);
        }
        
        // Handle non-AJAX request
        if ($result !== false) {
            $_SESSION['flash_message'] = 'Lesson marked as complete!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Error marking lesson as complete. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
        
        // Get next lesson if any
        $moduleId = $lesson['module_id'];
        $nextLesson = $lessonModel->getNextLesson($id, $moduleId);
        
        if ($nextLesson) {
            $this->redirect("/lessons/{$nextLesson['id']}");
        } else {
            // No more lessons, redirect to module page
            $moduleModel = new Module();
            $module = $moduleModel->findById($moduleId);
            $this->redirect("/courses/{$module['course_id']}/modules/{$moduleId}");
        }
    }
    
    /**
     * Check if the current request is an AJAX request
     *
     * @return bool
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Display lesson resources
     *
     * @param int $id Lesson ID
     * @return void
     */
    public function resources($id)
    {
        // Require authentication
        $this->requireAuth();
        
        $lessonModel = new Lesson();
        $lesson = $lessonModel->getLessonWithResources($id);
        
        if (!$lesson) {
            return $this->notFound();
        }
        
        $this->render('pages/lessons/resources', [
            'title' => $lesson['title'] . ' - Resources - YDP Training',
            'lesson' => $lesson
        ]);
    }
    
    /**
     * Display lesson quiz
     *
     * @param int $id Lesson ID
     * @return void
     */
    public function quiz($id)
    {
        // Require authentication
        $this->requireAuth();
        
        $lessonModel = new Lesson();
        $lesson = $lessonModel->getLessonWithQuiz($id);
        
        if (!$lesson || empty($lesson['quiz'])) {
            return $this->notFound();
        }
        
        $this->render('pages/lessons/quiz', [
            'title' => $lesson['title'] . ' - Quiz - YDP Training',
            'lesson' => $lesson,
            'quiz' => $lesson['quiz']
        ]);
    }
    
    /**
     * Submit lesson quiz
     *
     * @param int $id Lesson ID
     * @return void
     */
    public function submitQuiz($id)
    {
        // Require authentication
        $this->requireAuth();
        
        $user = $this->getAuthUser();
        $data = $this->getFormData();
        
        $progressService = new CourseProgressService();
        $result = $progressService->submitQuiz($user['id'], $id, $data);
        
        if ($this->isAjaxRequest()) {
            return $this->json([
                'success' => $result['success'],
                'score' => $result['score'],
                'passed' => $result['passed'],
                'feedback' => $result['feedback']
            ]);
        }
        
        // Handle non-AJAX request
        $_SESSION['quiz_result'] = $result;
        $this->redirect("/lessons/{$id}/quiz-result");
    }
    
    /**
     * Display quiz result
     *
     * @param int $id Lesson ID
     * @return void
     */
    public function quizResult($id)
    {
        // Require authentication
        $this->requireAuth();
        
        if (!isset($_SESSION['quiz_result'])) {
            $this->redirect("/lessons/{$id}");
        }
        
        $result = $_SESSION['quiz_result'];
        unset($_SESSION['quiz_result']);
        
        $lessonModel = new Lesson();
        $lesson = $lessonModel->findById($id);
        
        if (!$lesson) {
            return $this->notFound();
        }
        
        $this->render('pages/lessons/quiz-result', [
            'title' => $lesson['title'] . ' - Quiz Results - YDP Training',
            'lesson' => $lesson,
            'result' => $result
        ]);
    }
}