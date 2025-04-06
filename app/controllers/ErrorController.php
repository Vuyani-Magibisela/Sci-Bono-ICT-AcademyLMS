<?php

namespace App\Controllers;

class ErrorController extends BaseController
{
    public function notFound()
    {
        http_response_code(404);
        return $this->render('errors/404', [
            'title' => 'Page Not Found',
            'message' => 'The requested page could not be found.'
        ]);
    }

    public function serverError($exception = null)
    {
        http_response_code(500);
        $message = 'An internal server error occurred.';
        
        if ($exception && DEBUG_MODE) {
            $message = $exception->getMessage();
        }
        
        return $this->render('errors/500', [
            'title' => 'Server Error',
            'message' => $message,
            'exception' => $exception
        ]);
    }

    public function forbidden()
    {
        http_response_code(403);
        return $this->render('errors/403', [
            'title' => 'Access Forbidden',
            'message' => 'You do not have permission to access this resource.'
        ]);
    }
}
