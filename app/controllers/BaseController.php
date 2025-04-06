<?php
namespace App\Controllers;

/**
 * Base Controller
 * 
 * Provides common functionality for all controllers
 */
class BaseController
{
    /**
     * Render a view with data
     *
     * @param string $view Path to the view file
     * @param array $data Data to pass to the view
     * @param string $layout Layout to use
     * @return void
     */
    protected function render($view, $data = [], $layout = 'main')
    {
        // Convert data array to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewPath = ROOT_PATH . "/app/views/{$view}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("View '{$view}' not found");
        }
        
        // Get the content of the view
        $content = ob_get_clean();
        
        // Set page title if not provided
        if (!isset($title)) {
            $title = 'YDP Training';
        }
        
        // Include the layout
        $layoutPath = ROOT_PATH . "/app/views/layouts/{$layout}.php";
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            // If layout doesn't exist, just output the content
            echo $content;
        }
    }
    
    /**
     * Redirect to another page
     *
     * @param string $path Path to redirect to
     * @return void
     */
    protected function redirect($path)
    {
        header("Location: {$path}");
        exit;
    }
    
    /**
     * Return JSON response
     *
     * @param array $data Data to return as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get authenticated user
     *
     * @return array|false|null User data array if found, false if not found, null if not logged in
     */
    protected function getAuthUser()
    {
        if (isset($_SESSION['user_id'])) {
            $userModel = new \App\Models\User();
            return $userModel->findById($_SESSION['user_id']);
        }
        
        return null;
    }
    
    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Require authentication to access a page
     *
     * @param string $redirectTo Path to redirect to if not authenticated
     * @return void
     */
    protected function requireAuth($redirectTo = '/login')
    {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect($redirectTo);
        }
    }
    
    /**
     * Get form data (supporting both GET and POST)
     *
     * @param string $method HTTP method (GET or POST)
     * @return array Form data
     */
    protected function getFormData($method = 'POST')
    {
        if ($method === 'GET') {
            return $_GET;
        }
        
        return $_POST;
    }
}
