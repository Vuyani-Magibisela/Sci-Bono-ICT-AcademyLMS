<?php
namespace App\Controllers;

use App\Models\Course;

/**
 * Home Controller
 * 
 * Handles the home page and general site pages
 */
class HomeController extends BaseController
{
    /**
     * Display the home page
     *
     * @return void
     */
    public function index()
    {
        // Get featured or latest courses
        $courseModel = new Course();
        $featuredCourses = $courseModel->getFeaturedCourses();
        
        return $this->render('pages/home', [
            'title' => 'YDP Training - Sci-Bono Youth Development Program',
            'featuredCourses' => $featuredCourses,
            'extraCss' => ['home'] // Add this line to include home.css
        ]);
    }
    
    /**
     * Display the about page
     *
     * @return void
     */
    public function about()
    {
        $this->render('pages/about', [
            'title' => 'About Us - YDP Training'
        ]);
    }
    
    /**
     * Display the contact page
     *
     * @return void
     */
    public function contact()
    {
        $this->render('pages/contact', [
            'title' => 'Contact Us - YDP Training'
        ]);
    }
    
    /**
     * Process contact form submission
     *
     * @return void
     */
    public function submitContact()
    {
        $data = $this->getFormData();
        
        // Simple validation
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        
        if (empty($data['message'])) {
            $errors['message'] = 'Message is required';
        }
        
        if (!empty($errors)) {
            // Return to the form with errors
            $this->render('pages/contact', [
                'title' => 'Contact Us - YDP Training',
                'errors' => $errors,
                'formData' => $data
            ]);
            return;
        }
        
        // Process the contact form (e.g., send email)
        // ...
        
        // Set success message and redirect
        $_SESSION['flash_message'] = 'Your message has been sent. We will contact you soon!';
        $_SESSION['flash_type'] = 'success';
        
        $this->redirect('/contact');
    }
    
    /**
     * Display the 404 page
     *
     * @return void
     */
    public function notFound()
    {
        http_response_code(404);
        $this->render('errors/404', [
            'title' => 'Page Not Found - YDP Training'
        ]);
    }
}
