<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Show login form
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Process login
     */
    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Find user by email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Credenciales inválidas.');
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            // Log failed attempt
            $this->logAuthAttempt($user['id'], false);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Credenciales inválidas.');
        }

        // Check if user is active
        if (!$user['activo']) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Su cuenta está desactivada. Contacte al administrador.');
        }

        // Get user with role info
        $userWithRole = $this->userModel->getUserWithRole($user['id']);

        // Set session data
        $sessionData = [
            'user_id'     => $user['id'],
            'email'       => $user['email'],
            'nombre'      => $user['nombre'],
            'apellido'    => $user['apellido'],
            'role_id'     => $user['role_id'],
            'rol_nombre'  => $userWithRole['rol_nombre'],
            'rol_slug'    => $userWithRole['rol_slug'],
            'isLoggedIn'  => true,
        ];
        session()->set($sessionData);

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Log successful attempt
        $this->logAuthAttempt($user['id'], true);

        // Handle remember me
        if ($remember) {
            $this->setRememberToken($user['id']);
        }

        // Redirect based on role
        return $this->redirectByRole($userWithRole['rol_slug']);
    }

    /**
     * Logout
     */
    public function logout()
    {
        // Clear remember token if exists
        if ($token = get_cookie('remember_token')) {
            $this->clearRememberToken($token);
        }

        // Destroy session
        session()->destroy();

        return redirect()->to('/login')->with('message', 'Ha cerrado sesión correctamente.');
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Process forgot password request
     */
    public function sendResetLink()
    {
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user = $this->userModel->findByEmail($email);

        // Always show success message (security - don't reveal if email exists)
        $successMessage = 'Si el correo existe en nuestro sistema, recibirá un enlace para restablecer su contraseña.';

        if (!$user) {
            return redirect()->back()->with('message', $successMessage);
        }

        // Generate reset token
        $token = $this->userModel->generateResetToken($user['id']);

        // Send email (will be implemented with SendGrid later)
        $this->sendResetEmail($user, $token);

        return redirect()->back()->with('message', $successMessage);
    }

    /**
     * Show reset password form
     */
    public function resetPassword(string $token)
    {
        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            return redirect()->to('/forgot-password')
                           ->with('error', 'El enlace es inválido o ha expirado.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    /**
     * Process password reset
     */
    public function updatePassword()
    {
        $rules = [
            'token'            => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        $messages = [
            'password_confirm' => [
                'matches' => 'Las contraseñas no coinciden.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                           ->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            return redirect()->to('/forgot-password')
                           ->with('error', 'El enlace es inválido o ha expirado.');
        }

        // Update password
        $this->userModel->update($user['id'], [
            'password' => $this->request->getPost('password')
        ]);

        // Mark token as used
        $this->userModel->markTokenUsed($token);

        return redirect()->to('/login')
                       ->with('message', 'Contraseña actualizada correctamente. Puede iniciar sesión.');
    }

    /**
     * Redirect user based on role
     */
    protected function redirectByRole(string $role): \CodeIgniter\HTTP\RedirectResponse
    {
        switch ($role) {
            case RoleModel::ADMIN:
                return redirect()->to('/admin/dashboard');
            case RoleModel::PROFESOR:
                return redirect()->to('/profesor/dashboard');
            case RoleModel::ACUDIENTE:
                return redirect()->to('/acudiente/dashboard');
            default:
                return redirect()->to('/dashboard');
        }
    }

    /**
     * Log authentication attempt
     */
    protected function logAuthAttempt(int $userId, bool $success): void
    {
        $db = \Config\Database::connect();
        $db->table('audit_log')->insert([
            'user_id'    => $userId,
            'accion'     => $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED',
            'tabla'      => 'users',
            'registro_id'=> $userId,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Set remember me token
     */
    protected function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        // Store in database
        $this->userModel->update($userId, [
            'remember_token' => $hashedToken
        ]);

        // Set cookie for 30 days
        set_cookie('remember_token', $token, 60 * 60 * 24 * 30);
    }

    /**
     * Clear remember token
     */
    protected function clearRememberToken(string $token): void
    {
        $hashedToken = hash('sha256', $token);

        $db = \Config\Database::connect();
        $db->table('users')
           ->where('remember_token', $hashedToken)
           ->update(['remember_token' => null]);

        delete_cookie('remember_token');
    }

    /**
     * Send password reset email
     */
    protected function sendResetEmail(array $user, string $token): bool
    {
        // TODO: Implement with SendGrid in Phase 8
        // For now, just log the token for testing
        log_message('info', "Password reset token for {$user['email']}: {$token}");

        $resetUrl = site_url("reset-password/{$token}");
        log_message('info', "Reset URL: {$resetUrl}");

        return true;
    }
}
