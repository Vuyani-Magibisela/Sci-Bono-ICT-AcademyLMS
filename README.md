# Sci-Bono ICT Academy LMS

A Learning Management System (LMS) designed for the Sci-Bono Youth Development Program's two-week interactive course on UX Design and Design Systems.

## Project Overview

This LMS is built to support a comprehensive two-week course focusing on UX Design (Week 1) and Design Systems (Week 2). It's specifically designed for beginners transitioning into web and app development, with a focus on foundational UX design principles and design systems.

### Key Features

- **Module-Based Learning:** Content organized into logical daily modules
- **Interactive Elements:** Discussion forums, quizzes, and assignments
- **Progress Tracking:** Visual indicators of learning progress
- **Responsive Design:** Seamless experience on desktops, tablets, and smartphones
- **Secure Authentication:** Role-based access for students and instructors

## Technology Stack

The platform is developed using a custom MVC architecture with native PHP, implementing a clean separation of concerns without relying on a framework.

- **Backend:** Native PHP with custom MVC pattern
- **Database:** MySQL with PDO
- **Frontend:** HTML, CSS, JavaScript
- **Authentication:** Custom authentication system

## Installation

### Prerequisites

- PHP >= 7.4
- MySQL
- Web server (Apache/Nginx)
- PDO PHP extension
- mod_rewrite enabled (for Apache)

### Setup Instructions

1. **Clone the repository:**
   ```
   git clone git@github.com:Vuyani-Magibisela/Sci-Bono-ICT-AcademyLMS.git
   cd Sci-Bono-ICT-AcademyLMS
   ```

2. **Configure database:**
   - Open `app/config/config.php` and `app/config/database.php`
   - Update database credentials with your MySQL details

3. **Set up database:**
   - Create a MySQL database named `ydp_lms_system`
   - Import the provided SQL schema file (if available)

4. **Configure web server:**
   - For Apache: Ensure the `.htaccess` file is properly configured
   - For Nginx: Set up proper URL rewriting to the public folder

5. **Set file permissions:**
   ```
   chmod -R 755 public/
   chmod -R 777 logs/
   ```

6. **Access the application:**
   - Either set up a virtual host or
   - Navigate to the project URL in your web server configuration

## Project Structure

The application follows a custom MVC architecture:

```
/
├── app/                      # Application code
│   ├── config/               # Configuration files
│   │   ├── config.php        # Main configuration
│   │   └── database.php      # Database configuration
│   ├── controllers/          # Controller classes
│   │   ├── AuthController.php
│   │   ├── BaseController.php
│   │   ├── CourseController.php
│   │   ├── ErrorController.php
│   │   ├── HomeController.php
│   │   └── LessonController.php
│   ├── models/               # Model classes
│   │   ├── Course.php
│   │   ├── CourseProgress.php
│   │   ├── Lesson.php
│   │   ├── Module.php
│   │   └── User.php
│   ├── services/             # Service classes
│   │   ├── AuthService.php
│   │   └── CourseProgressService.php
│   ├── views/                # View templates
│   │   ├── components/       # Reusable UI components
│   │   ├── errors/           # Error pages
│   │   ├── layouts/          # Layout templates
│   │   └── pages/            # Page content
│   └── bootstrap.php         # Application bootstrap
├── core/                     # Core framework files
│   ├── Application.php       # Main application class
│   ├── Database.php          # Database connection class (PDO)
│   └── Router.php            # Custom URL routing system
├── logs/                     # Application logs
├── public/                   # Public assets and entry point
│   ├── css/                  # Stylesheets
│   ├── img/                  # Images and icons
│   ├── js/                   # JavaScript files
│   ├── uploads/              # User uploaded content
│   └── index.php             # Main entry point
├── .htaccess                 # Apache URL rewriting configuration
└── README.md                 # Project documentation
```

## Course Content

The LMS is populated with a comprehensive curriculum covering:

### Week 1: UX Design
- Introduction to UX Design & Design Thinking
- User Research & Data Collection
- Analyzing User Research & Creating Personas
- Information Architecture & Wireframing
- Interaction Design & Prototyping

### Week 2: Design Systems
- Overview of Design Systems
- Visual Design Fundamentals
- Building Component Libraries
- Integrating UX with Design Systems
- Final Project

## Deployment

For production deployment:

1. Set up a production-ready web server (Nginx/Apache)
2. Configure proper server permissions:
   ```
   chmod -R 755 public/
   chmod -R 777 logs/
   ```
3. Update database configuration in `app/config/config.php` for production
4. Set `DISPLAY_ERRORS` to `false` in `app/config/config.php`
5. Ensure proper security measures are in place:
   - Configure firewall rules
   - Install security updates
   - Set up input validation
6. Set up SSL certificate for secure connections
7. Configure proper caching for improved performance

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Commit your changes: `git commit -m 'Add new feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request

## License

This project is proprietary and designed specifically for the Sci-Bono Youth Development Program.

## Contact

For questions or support regarding this project, please contact:
- **Developer:** Vuyani Magibisela
- **Institution:** Sci-Bono Discovery Centre, Newtown, Johannesburg
- **Phone:** (011) 639-8400
- **Email:** info@sci-bono.co.za

## Acknowledgments

- Sci-Bono Youth Development Program for providing the opportunity to develop this educational platform
- The open-source community for providing tools and libraries that made this project possible