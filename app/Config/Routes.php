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

// Public registration (no auth required)
$routes->get('registro/(:segment)', 'RegistroController::index/$1');
$routes->post('registro/(:segment)', 'RegistroController::store/$1');

// ============================================================================
// PROTECTED ROUTES (Authentication required)
// ============================================================================

// Generic dashboard - redirects based on role
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

// Notificaciones (all authenticated roles)
$routes->get('notificaciones', 'NotificacionController::index', ['filter' => 'auth']);
$routes->get('notificaciones/recientes', 'NotificacionController::recientes', ['filter' => 'auth']);
$routes->post('notificaciones/marcar-leida/(:num)', 'NotificacionController::marcarLeida/$1', ['filter' => 'auth']);
$routes->post('notificaciones/marcar-todas-leidas', 'NotificacionController::marcarTodasLeidas', ['filter' => 'auth']);

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
    $routes->post('users/(:num)/send-credentials', 'Admin\UserController::sendCredentials/$1');

    // Students management
    $routes->get('students', 'Admin\StudentController::index');
    $routes->get('students/new', 'Admin\StudentController::new');
    $routes->post('students', 'Admin\StudentController::create');
    $routes->get('students/(:num)', 'Admin\StudentController::show/$1');
    $routes->get('students/(:num)/edit', 'Admin\StudentController::edit/$1');
    $routes->put('students/(:num)', 'Admin\StudentController::update/$1');
    $routes->delete('students/(:num)', 'Admin\StudentController::delete/$1');
    $routes->delete('students/(:num)/hard', 'Admin\StudentController::hardDelete/$1');

    // Groups management
    $routes->get('groups', 'Admin\GroupController::index');
    $routes->get('groups/new', 'Admin\GroupController::new');
    $routes->post('groups', 'Admin\GroupController::create');
    $routes->get('groups/(:num)', 'Admin\GroupController::show/$1');
    $routes->get('groups/(:num)/edit', 'Admin\GroupController::edit/$1');
    $routes->put('groups/(:num)', 'Admin\GroupController::update/$1');
    $routes->delete('groups/(:num)', 'Admin\GroupController::delete/$1');
    $routes->post('groups/(:num)/enroll', 'Admin\GroupController::enrollStudent/$1');
    $routes->post('groups/(:num)/unenroll/(:num)', 'Admin\GroupController::unenrollStudent/$1/$2');

    // Schedules management
    $routes->get('schedules', 'Admin\ScheduleController::index');
    $routes->get('schedules/new', 'Admin\ScheduleController::new');
    $routes->post('schedules', 'Admin\ScheduleController::create');
    $routes->get('schedules/(:num)', 'Admin\ScheduleController::show/$1');
    $routes->get('schedules/(:num)/edit', 'Admin\ScheduleController::edit/$1');
    $routes->put('schedules/(:num)', 'Admin\ScheduleController::update/$1');
    $routes->delete('schedules/(:num)', 'Admin\ScheduleController::delete/$1');

    // ---- FASE 5: Cartera, Pagos, Tarifas ----

    // Cartera
    $routes->get('cartera', 'Admin\CarteraController::index');
    $routes->get('cartera/estudiante/(:num)', 'Admin\CarteraController::estudiante/$1');
    $routes->post('cartera/generar-cargo', 'Admin\CarteraController::generarCargo');
    $routes->post('cartera/anular/(:num)', 'Admin\CarteraController::anularCargo/$1');
    $routes->post('cartera/recordatorios', 'Admin\CarteraController::enviarRecordatorios');

    // Payments
    $routes->get('payments', 'Admin\PaymentController::index');
    $routes->get('payments/create', 'Admin\PaymentController::create');
    $routes->post('payments', 'Admin\PaymentController::store');
    $routes->get('payments/cargos-pendientes', 'Admin\PaymentController::getCargosPendientes');
    $routes->get('payments/(:num)', 'Admin\PaymentController::review/$1');
    $routes->post('payments/(:num)/approve', 'Admin\PaymentController::approve/$1');
    $routes->post('payments/(:num)/reject', 'Admin\PaymentController::reject/$1');

    // Tarifas
    $routes->get('tarifas', 'Admin\TarifaController::index');
    $routes->get('tarifas/new', 'Admin\TarifaController::new');
    $routes->post('tarifas', 'Admin\TarifaController::create');
    $routes->get('tarifas/(:num)/edit', 'Admin\TarifaController::edit/$1');
    $routes->put('tarifas/(:num)', 'Admin\TarifaController::update/$1');
    $routes->delete('tarifas/(:num)', 'Admin\TarifaController::delete/$1');

    // ---- FASE 6: Asistencia ----

    // Attendance (admin)
    $routes->get('attendance', 'Admin\AttendanceController::index');
    $routes->get('attendance/report/(:num)', 'Admin\AttendanceController::report/$1');
    $routes->get('attendance/session/(:num)', 'Admin\AttendanceController::session/$1');

    // ---- FASE 7: Torneos ----

    // Tournaments
    $routes->get('tournaments', 'Admin\TournamentController::index');
    $routes->get('tournaments/new', 'Admin\TournamentController::new');
    $routes->post('tournaments', 'Admin\TournamentController::create');
    $routes->get('tournaments/(:num)', 'Admin\TournamentController::show/$1');
    $routes->get('tournaments/(:num)/edit', 'Admin\TournamentController::edit/$1');
    $routes->put('tournaments/(:num)', 'Admin\TournamentController::update/$1');
    $routes->delete('tournaments/(:num)', 'Admin\TournamentController::delete/$1');
    $routes->post('tournaments/(:num)/enroll', 'Admin\TournamentController::enrollStudent/$1');
    $routes->post('tournaments/(:num)/unenroll/(:num)', 'Admin\TournamentController::unenrollStudent/$1/$2');
    $routes->post('tournaments/(:num)/status', 'Admin\TournamentController::changeStatus/$1');

    // Auditoria
    $routes->get('auditoria', 'Admin\AuditoriaController::index');
    $routes->get('auditoria/(:num)', 'Admin\AuditoriaController::detalle/$1');
});

// ============================================================================
// PROFESOR ROUTES
// ============================================================================

$routes->group('profesor', ['filter' => 'role:admin,profesor'], static function ($routes) {
    $routes->get('dashboard', 'Profesor\DashboardController::index');

    // Attendance
    $routes->get('attendance', 'Profesor\AttendanceController::index');
    $routes->get('attendance/take/(:num)', 'Profesor\AttendanceController::take/$1');
    $routes->post('attendance/save', 'Profesor\AttendanceController::save');

    // My groups
    $routes->get('groups', 'Profesor\GroupController::index');
    $routes->get('groups/(:num)', 'Profesor\GroupController::show/$1');

    // Inscription links
    $routes->get('inscripcion', 'Profesor\InscriptionController::index');
    $routes->post('inscripcion/generar', 'Profesor\InscriptionController::generate');

    // Clases Particulares
    $routes->get('clases-particulares', 'Profesor\ClaseParticularController::index');
    $routes->get('clases-particulares/(:num)', 'Profesor\ClaseParticularController::ver/$1');
    $routes->post('clases-particulares/(:num)/responder', 'Profesor\ClaseParticularController::responder/$1');
});

// ============================================================================
// ACUDIENTE (PARENT) ROUTES
// ============================================================================

$routes->group('acudiente', ['filter' => 'role:admin,acudiente'], static function ($routes) {
    $routes->get('dashboard', 'Acudiente\DashboardController::index');

    // Mis Estudiantes
    $routes->get('estudiantes', 'Acudiente\EstudiantesController::index');
    $routes->get('estudiantes/crear', 'Acudiente\EstudiantesController::crear');
    $routes->post('estudiantes/guardar', 'Acudiente\EstudiantesController::guardar');
    $routes->get('estudiantes/(:num)', 'Acudiente\EstudiantesController::detalle/$1');
    $routes->get('estudiantes/(:num)/editar', 'Acudiente\EstudiantesController::editar/$1');
    $routes->post('estudiantes/(:num)/actualizar', 'Acudiente\EstudiantesController::actualizar/$1');

    // Perfil
    $routes->get('perfil', 'Acudiente\PerfilController::index');
    $routes->post('perfil/actualizar', 'Acudiente\PerfilController::actualizar');
    $routes->post('perfil/cambiar-password', 'Acudiente\PerfilController::cambiarPassword');

    // Pagos
    $routes->get('pagos', 'Acudiente\PagosController::index');
    $routes->get('pagos/subir', 'Acudiente\PagosController::subir');
    $routes->post('pagos/guardar', 'Acudiente\PagosController::guardar');

    // Horarios
    $routes->get('horarios', 'Acudiente\HorariosController::index');

    // Paz y Salvo
    $routes->get('paz-y-salvo', 'Acudiente\PazYSalvoController::index');
    $routes->post('paz-y-salvo/solicitar', 'Acudiente\PazYSalvoController::solicitar');
    $routes->get('paz-y-salvo/descargar/(:num)', 'Acudiente\PazYSalvoController::descargar/$1');

    // Clases Particulares
    $routes->get('clases-particulares', 'Acudiente\ClasesParticularesController::index');
    $routes->get('clases-particulares/solicitar', 'Acudiente\ClasesParticularesController::solicitar');
    $routes->post('clases-particulares/guardar', 'Acudiente\ClasesParticularesController::guardar');
    $routes->post('clases-particulares/aceptar-precio/(:num)', 'Acudiente\ClasesParticularesController::aceptarPrecio/$1');
    $routes->post('clases-particulares/rechazar-precio/(:num)', 'Acudiente\ClasesParticularesController::rechazarPrecio/$1');
});

// ============================================================================
// OFFLINE API ROUTES (JSON endpoints for PWA sync)
// ============================================================================

$routes->group('api/offline', ['filter' => 'apiauth'], static function ($routes) {
    // Session check
    $routes->get('session-check', 'Api\OfflineController::sessionCheck');

    // Admin: data prefetch
    $routes->get('admin/dashboard', 'Api\OfflineController::adminDashboard');

    // Profesor: data prefetch
    $routes->get('profesor/grupos', 'Api\OfflineController::profesorGrupos');
    $routes->get('profesor/grupo/(:num)/estudiantes', 'Api\OfflineController::grupoEstudiantes/$1');

    // Acudiente: data prefetch
    $routes->get('acudiente/cargos', 'Api\OfflineController::acudienteCargos');
    $routes->get('acudiente/dashboard', 'Api\OfflineController::acudienteDashboard');

    // Offline sync (POST)
    $routes->post('attendance', 'Api\OfflineController::syncAttendance');
    $routes->post('payments', 'Api\OfflineController::syncPayment');
});
