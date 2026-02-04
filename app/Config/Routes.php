<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

// Home - redirect to login
$routes->get('/', 'AuthController::login');

// Authentication routes
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');
$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('reset-password', 'AuthController::updatePassword');

// ============================================================================
// PROTECTED ROUTES (Authentication required)
// ============================================================================

// Generic dashboard - redirects based on role
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

// ============================================================================
// ADMIN ROUTES
// ============================================================================

$routes->group('admin', ['filter' => 'role:admin'], static function ($routes) {
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // Users management
    $routes->get('users', 'Admin\UserController::index');
    $routes->get('users/new', 'Admin\UserController::new');
    $routes->post('users', 'Admin\UserController::create');
    $routes->get('users/(:num)', 'Admin\UserController::show/$1');
    $routes->get('users/(:num)/edit', 'Admin\UserController::edit/$1');
    $routes->put('users/(:num)', 'Admin\UserController::update/$1');
    $routes->delete('users/(:num)', 'Admin\UserController::delete/$1');

    // Students management
    $routes->get('students', 'Admin\StudentController::index');
    $routes->get('students/new', 'Admin\StudentController::new');
    $routes->post('students', 'Admin\StudentController::create');
    $routes->get('students/(:num)', 'Admin\StudentController::show/$1');
    $routes->get('students/(:num)/edit', 'Admin\StudentController::edit/$1');
    $routes->put('students/(:num)', 'Admin\StudentController::update/$1');
    $routes->delete('students/(:num)', 'Admin\StudentController::delete/$1');

    // Groups management (future)
    // $routes->resource('groups', ['controller' => 'Admin\GroupController']);

    // Payments management (future)
    // $routes->resource('payments', ['controller' => 'Admin\PaymentController']);
});

// ============================================================================
// PROFESOR ROUTES
// ============================================================================

$routes->group('profesor', ['filter' => 'role:admin,profesor'], static function ($routes) {
    $routes->get('dashboard', 'Profesor\DashboardController::index');

    // Attendance (future)
    // $routes->get('attendance', 'Profesor\AttendanceController::index');
    // $routes->post('attendance', 'Profesor\AttendanceController::save');

    // My groups (future)
    // $routes->get('groups', 'Profesor\GroupController::index');
});

// ============================================================================
// ACUDIENTE (PARENT) ROUTES
// ============================================================================

$routes->group('acudiente', ['filter' => 'role:admin,acudiente'], static function ($routes) {
    $routes->get('dashboard', 'Acudiente\DashboardController::index');

    // My students (future)
    // $routes->get('students', 'Acudiente\StudentController::index');

    // Payments (future)
    // $routes->get('payments', 'Acudiente\PaymentController::index');

    // Enrollment (future)
    // $routes->get('enrollment', 'Acudiente\EnrollmentController::index');
});

// ============================================================================
// API ROUTES (Future - for PWA)
// ============================================================================

// $routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
//     $routes->post('login', 'AuthController::login');
//     // ... more API routes
// });
