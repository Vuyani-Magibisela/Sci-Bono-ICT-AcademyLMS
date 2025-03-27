<?php
namespace App\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\CourseProgress;

/**
 * Course Controller
 * 
 * Handles course-related pages and actions
 */
class CourseController extends BaseController
{
    /**
     * Display all courses
     *
     * @return void
     */
    public function index()
    {
        $courseModel = new Course();
        $courses = $courseModel->getAllActiveCourses();
        
        $this->render('pages/courses/index', [
            'title' => 'Courses - YDP Training',
            'courses' => $courses
        ]);
    }
    
    /**
     * Display a single course
     *
     * @param int $id Course ID
     * @return void
     */
    public function view($id)
    {
        $courseModel = new Course();
        $course = $courseModel->getFullCourseData($id);
        
        if (!$course) {
            return $this->notFound();
        }
        
        $moduleModel = new Module();
        $modules = $moduleModel->getModulesByCourseId($id);
        
        // Get course progress for the authenticated user
        $progress = null;
        if ($this->isAuthenticated()) {
            $user = $this->getAuthUser();
            $progressModel = new CourseProgress();
            $progress = $progressModel->getUserCourseProgress($user['id'], $id);
        }
        
        $this->render('pages/courses/view', [
            'title' => $course['title'] . ' - YDP Training',
            'course' => $course,
            'modules' => $modules,
            'progress' => $progress
        ]);
    }
    
    /**
     * Display course modules
     *
     * @param int $id Course ID
     * @return void
     */
    public function modules($id)
    {
        $courseModel = new Course();
        $course = $courseModel->findById($id);
        
        if (!$course) {
            return $this->notFound();
        }
        
        $moduleModel = new Module();
        $modules = $moduleModel->getModulesByCourseId($id);
        
        $this->render('pages/courses/modules', [
            'title' => $course['title'] . ' - Modules - YDP Training',
            'course' => $course,
            'modules' => $modules
        ]);
    }
    
    /**
     * Enroll current user in a course
     *
     * @param int $id Course ID
     * @return void
     */
    public function enroll($id)
    {
        // Require authentication
        $this->requireAuth();
        
        $courseModel = new Course();
        $course = $courseModel->findById($id);
        
        if (!$course) {
            return $this->notFound();
        }
        
        $user = $this->getAuthUser();
        
        // Check if already enrolled
        $progressModel = new CourseProgress();
        if ($progressModel->isUserEnrolled($user['id'], $id)) {
            $_SESSION['flash_message'] = 'You are already enrolled in this course.';
            $_SESSION['flash_type'] = 'info';
            
            $this->redirect("/courses/{$id}");
            return;
        }
        
        // Enroll the user
        $result = $progressModel->enrollUser($user['id'], $id);
        
        if ($result) {
            $_SESSION['flash_message'] = 'You have successfully enrolled in the course.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'There was an error enrolling you in the course. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
        
        $this->redirect("/courses/{$id}");
    }
    
    /**
     * Display user's enrolled courses
     *
     * @return void
     */
    public function myCourses()
    {
        // Require authentication
        $this->requireAuth();
        
        $user = $this->getAuthUser();
        
        $courseModel = new Course();
        $courses = $courseModel->getUserCourses($user['id']);
        
        $this->render('pages/courses/my-courses', [
            'title' => 'My Courses - YDP Training',
            'courses' => $courses
        ]);
    }
    
    /**
     * Search for courses
     *
     * @return void
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            $this->redirect('/courses');
            return;
        }
        
        $courseModel = new Course();
        $courses = $courseModel->searchCourses($query);
        
        $this->render('pages/courses/search', [
            'title' => 'Search Results - YDP Training',
            'query' => $query,
            'courses' => $courses
        ]);
    }
}