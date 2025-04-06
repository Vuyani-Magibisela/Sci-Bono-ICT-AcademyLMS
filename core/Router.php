<?php
namespace Core;

/**
 * Router Class
 * 
 * Handles URL routing and dispatching to the appropriate controller and method.
 */
class Router
{
    /**
     * @var array Array of registered routes
     */
    private $routes = [];
    
    /**
     * @var callable|null Handler for 404 errors
     */
    private $notFoundHandler = null;
    
    /**
     * Register a GET route
     * 
     * @param string $path The route path
     * @param string|callable $handler The route handler
     * @return void
     */
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Register a POST route
     * 
     * @param string $path The route path
     * @param string|callable $handler The route handler
     * @return void
     */
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Register a PUT route
     * 
     * @param string $path The route path
     * @param string|callable $handler The route handler
     * @return void
     */
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Register a DELETE route
     * 
     * @param string $path The route path
     * @param string|callable $handler The route handler
     * @return void
     */
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Add a route to the routes array
     * 
     * @param string $method HTTP method
     * @param string $path The route path
     * @param string|callable $handler The route handler
     * @return void
     */
    private function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Set the handler for 404 errors
     * 
     * @param string|callable $handler The handler for 404 errors
     * @return void
     */
    public function notFound($handler)
    {
        $this->notFoundHandler = $handler;
    }
    
    //handling errors
    private function handleError($code) {
        $errorController = new \App\Controllers\ErrorController();
        
        switch ($code) {
            case 404:
                return $errorController->notFound();
            case 403:
                return $errorController->forbidden();
            default:
                return $errorController->serverError();
        }
    }
    
    /**
     * Resolve the current request to a route and dispatch it
     * 
     * @return mixed The result of the route handler
     */
    public function resolve()
    {
        
        // Get the request method and path
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // If your project is in a subdirectory, add this:
        $basePath = '/Sci-Bono-ICT-AcademyLMS';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        // If path is empty, set it to '/'
        if (empty($path)) {
            $path = '/';
        }
        
        // Check if there's a query string and remove it
        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            $path = str_replace('?' . $_SERVER['QUERY_STRING'], '', $path);
        }
        
        // If the request is a POST with _method field, use that as the method (for PUT, DELETE)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        // Match the route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->getPatternFromPath($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                // Remove the full match
                array_shift($matches);
                
                // Get handler
                $handler = $route['handler'];
                
                // If handler is a closure
                if (is_callable($handler)) {
                    return call_user_func_array($handler, $matches);
                }
                
                // If handler is a string in format "Controller@method"
                if (is_string($handler) && strpos($handler, '@') !== false) {
                    list($controller, $method) = explode('@', $handler);
                    
                    // Add namespace to controller if not already present
                    if (strpos($controller, '\\') === false) {
                        $controller = "\\App\\Controllers\\{$controller}";
                    }
                    
                    // Check if the controller class exists
                    if (!class_exists($controller)) {
                        throw new \Exception("Controller {$controller} not found");
                    }
                    
                    // Create controller instance
                    $controllerInstance = new $controller();
                    
                    // Check if the method exists
                    if (!method_exists($controllerInstance, $method)) {
                        throw new \Exception("Method {$method} not found in controller {$controller}");
                    }
                    
                    // Call the controller method with the matches as parameters
                    return call_user_func_array([$controllerInstance, $method], $matches);
                }
            }
        }
        
        // If no route matched, handle 404
        return $this->handleNotFound();
    }
    
    /**
     * Convert route path to regex pattern
     * 
     * @param string $path The route path
     * @return string The regex pattern
     */
    private function getPatternFromPath($path)
    {
        // Replace route parameters with regex patterns
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        
        // Add start and end anchors
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Handle 404 errors
     * 
     * @return mixed The result of the not found handler
     */
    private function handleNotFound()
    {
        // If a not found handler is set, call it
        if ($this->notFoundHandler) {
            // If the handler is a closure
            if (is_callable($this->notFoundHandler)) {
                return call_user_func($this->notFoundHandler);
            }
            
            // If the handler is a string in format "Controller@method"
            if (is_string($this->notFoundHandler) && strpos($this->notFoundHandler, '@') !== false) {
                list($controller, $method) = explode('@', $this->notFoundHandler);
                
                // Add namespace to controller if not already present
                if (strpos($controller, '\\') === false) {
                    $controller = "\\App\\Controllers\\{$controller}";
                }
                
                // Create controller instance
                $controllerInstance = new $controller();
                
                // Call the controller method
                return call_user_func([$controllerInstance, $method]);
            }
        }
        
        // Default 404 response
        http_response_code(404);
        echo '404 Not Found';
        return false;
    }
    
    /**
     * Generate a URL for a named route
     * 
     * @param string $name The route name
     * @param array $params The route parameters
     * @return string The generated URL
     */
    public function url($name, $params = [])
    {
        // Implementation for named routes (future enhancement)
        // For now, just return a basic URL with the parameters
        $url = $name;
        
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        
        return $url;
    }
}