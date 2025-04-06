<?php
namespace Core;

/**
 * Application Class
 * 
 * Main application class that handles routing and dispatching requests.
 */
class Application
{
    /**
     * @var Router The router instance
     */
    private $router;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }
    
    /**
     * Run the application
     */
    public function run()
    {
        $this->router->resolve();
    }
    
    /**
     * Register application routes
     */
    private function registerRoutes()
    {
        // Home routes
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/about', 'HomeController@about');
        $this->router->get('/contact', 'HomeController@contact');
        $this->router->post('/contact', 'HomeController@submitContact');
        
        // Auth routes
        $this->router->get('/login', 'AuthController@loginForm');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/register', 'AuthController@registerForm');
        $this->router->post('/register', 'AuthController@register');
        $this->router->get('/logout', 'AuthController@logout');
        $this->router->get('/forgot-password', 'AuthController@forgotPasswordForm');
        $this->router->post('/forgot-password', 'AuthController@forgotPassword');
        $this->router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
        $this->router->post('/reset-password', 'AuthController@resetPassword');
        
        // Course routes
        $this->router->get('/courses', 'CourseController@index');
        $this->router->get('/courses/{slug}', 'CourseController@view');
        $this->router->post('/courses/{id}/enroll', 'CourseController@enroll');
        $this->router->get('/courses/my-courses', 'CourseController@myCourses');
        $this->router->get('/courses/search', 'CourseController@search');
        $this->router->get('/courses/{courseSlug}/modules', 'CourseController@modules');
        $this->router->get('/courses/{courseSlug}/modules/{moduleId}', 'ModuleController@view');
        
        // Lesson routes
        $this->router->get('/lessons/{id}', 'LessonController@view');
        $this->router->post('/lessons/{id}/complete', 'LessonController@markComplete');
        $this->router->get('/lessons/{id}/resources', 'LessonController@resources');
        $this->router->get('/lessons/{id}/quiz', 'LessonController@quiz');
        $this->router->post('/lessons/{id}/quiz', 'LessonController@submitQuiz');
        $this->router->get('/lessons/{id}/quiz-result', 'LessonController@quizResult');
        
        // User dashboard routes
        $this->router->get('/dashboard', 'DashboardController@index');
        $this->router->get('/profile', 'ProfileController@index');
        $this->router->post('/profile/update', 'ProfileController@update');
        $this->router->post('/profile/change-password', 'ProfileController@changePassword');
        $this->router->post('/profile/upload-picture', 'ProfileController@uploadPicture');
        $this->router->get('/profile/remove-picture', 'ProfileController@removePicture');
        $this->router->get('/certificates', 'CertificateController@index');
        $this->router->get('/certificates/{id}/download', 'CertificateController@download');
        
        // Error routes
        $this->router->get('/error/404', 'ErrorController@notFound');
        $this->router->get('/error/500', 'ErrorController@serverError');
        
        // Handle 404 errors for any route not matched
        $this->router->notFound('ErrorController@notFound');
    }
    
    /**
     * Get the router instance
     * 
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }
    
    /**
     * Render a view
     * 
     * @param string $view View file path
     * @param array $data Data to pass to the view
     * @param string $layout Layout to use
     * @return string The rendered view
     */
    public function renderView($view, $data = [], $layout = 'main')
    {
        $viewContent = $this->renderViewOnly($view, $data);
        
        if ($layout) {
            $layoutData = array_merge($data, ['content' => $viewContent]);
            return $this->renderViewOnly("layouts/{$layout}", $layoutData);
        }
        
        return $viewContent;
    }
    
    /**
     * Render a view without layout
     * 
     * @param string $view View file path
     * @param array $data Data to pass to the view
     * @return string The rendered view
     */
    public function renderViewOnly($view, $data = [])
    {
        // Extract data to make variables available in the view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = APP_PATH . "/views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("View '{$view}' not found");
        }
        
        // Return the buffered content
        return ob_get_clean();
    }
}