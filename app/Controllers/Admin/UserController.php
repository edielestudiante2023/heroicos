<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * List all users with filters
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Get filter parameters
        $search = $this->request->getGet('search');
        $roleFilter = $this->request->getGet('rol');
        $statusFilter = $this->request->getGet('estado');

        // Build query
        $builder = $db->table('usuarios')
                      ->select('usuarios.*, roles.nombre as rol_nombre')
                      ->join('roles', 'roles.id = usuarios.rol_id');

        // Apply filters
        if ($search) {
            $builder->groupStart()
                    ->like('usuarios.email', $search)
                    ->groupEnd();
        }

        if ($roleFilter) {
            $builder->where('roles.nombre', $roleFilter);
        }

        if ($statusFilter) {
            $builder->where('usuarios.estado', $statusFilter);
        }

        // Order and get results
        $usuarios = $builder->orderBy('usuarios.created_at', 'DESC')
                            ->get()
                            ->getResultArray();

        // Get profile data for each user
        foreach ($usuarios as &$usuario) {
            $profile = $this->userModel->getProfileData($usuario['id'], $usuario['rol_nombre']);
            if ($profile) {
                $usuario['nombre'] = $profile['nombre'] ?? $profile['nombres'] ?? '';
                $usuario['apellido'] = $profile['apellido'] ?? $profile['apellidos'] ?? '';
            } else {
                $usuario['nombre'] = '';
                $usuario['apellido'] = '';
            }
        }

        // Get roles for filter dropdown
        $roles = $this->roleModel->getAllRoles();

        return view('admin/users/index', [
            'title'     => 'Gestión de Usuarios - Academia Heroicos',
            'pageTitle' => 'Usuarios',
            'usuarios'  => $usuarios,
            'roles'     => $roles,
            'filters'   => [
                'search' => $search,
                'rol'    => $roleFilter,
                'estado' => $statusFilter,
            ],
        ]);
    }

    /**
     * Show create user form
     */
    public function new()
    {
        $roles = $this->roleModel->getAllRoles();

        return view('admin/users/form', [
            'title'     => 'Nuevo Usuario - Academia Heroicos',
            'pageTitle' => 'Nuevo Usuario',
            'roles'     => $roles,
            'usuario'   => null,
            'action'    => 'create',
        ]);
    }

    /**
     * Create new user
     */
    public function create()
    {
        // Validate input
        $rules = [
            'email'           => 'required|valid_email|is_unique[usuarios.email]',
            'password'        => 'required|min_length[8]',
            'password_confirm'=> 'required|matches[password]',
            'rol_id'          => 'required|integer',
            'nombres'         => 'required|min_length[2]|max_length[100]',
            'apellidos'       => 'required|min_length[2]|max_length[100]',
            'telefono'        => 'permit_empty|max_length[20]',
        ];

        $messages = [
            'email' => [
                'is_unique' => 'Este correo ya está registrado.',
            ],
            'password_confirm' => [
                'matches' => 'Las contraseñas no coinciden.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get role info
            $rolId = $this->request->getPost('rol_id');
            $rol = $this->roleModel->find($rolId);

            if (!$rol) {
                throw new \Exception('Rol no válido');
            }

            // Create user
            $userData = [
                'rol_id'           => $rolId,
                'email'            => $this->request->getPost('email'),
                'password'         => $this->request->getPost('password'),
                'estado'           => $this->request->getPost('estado') ?? 'activo',
                'email_verificado' => 1,
                'created_at'       => date('Y-m-d H:i:s'),
            ];

            $this->userModel->insert($userData);
            $userId = $this->userModel->getInsertID();

            // Create profile based on role
            $profileData = [
                'usuario_id' => $userId,
                'nombres'    => $this->request->getPost('nombres'),
                'apellidos'  => $this->request->getPost('apellidos'),
                'telefono'   => $this->request->getPost('telefono'),
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $profileTable = match ($rol['nombre']) {
                'admin'     => 'administradores',
                'profesor'  => 'profesores',
                'acudiente' => 'acudientes',
                default     => null
            };

            if ($profileTable) {
                // Add role-specific fields
                if ($rol['nombre'] === 'admin') {
                    $profileData['es_superadmin'] = 0;
                }
                if (in_array($rol['nombre'], ['profesor', 'acudiente'])) {
                    $profileData['tipo_documento']   = $this->request->getPost('tipo_documento') ?? 'CC';
                    $profileData['numero_documento']  = $this->request->getPost('numero_documento') ?: null;
                }
                $db->table($profileTable)->insert($profileData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al crear usuario');
            }

            // Log action
            $this->logAction('CREATE', 'usuarios', $userId);

            // Enviar email de bienvenida con credenciales
            $this->enviarEmailBienvenida(
                $this->request->getPost('email'),
                $this->request->getPost('nombres'),
                $this->request->getPost('password'),
                $rol['nombre']
            );

            return redirect()->to('/admin/users')
                           ->with('message', 'Usuario creado exitosamente. Se enviaron las credenciales por email.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        $usuario = $this->userModel->getUserWithRole($id);

        if (!$usuario) {
            return redirect()->to('/admin/users')
                           ->with('error', 'Usuario no encontrado.');
        }

        return view('admin/users/show', [
            'title'     => 'Detalle Usuario - Academia Heroicos',
            'pageTitle' => 'Detalle de Usuario',
            'usuario'   => $usuario,
        ]);
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $usuario = $this->userModel->getUserWithRole($id);

        if (!$usuario) {
            return redirect()->to('/admin/users')
                           ->with('error', 'Usuario no encontrado.');
        }

        $roles = $this->roleModel->getAllRoles();

        return view('admin/users/form', [
            'title'     => 'Editar Usuario - Academia Heroicos',
            'pageTitle' => 'Editar Usuario',
            'roles'     => $roles,
            'usuario'   => $usuario,
            'action'    => 'update',
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $usuario = $this->userModel->find($id);

        if (!$usuario) {
            return redirect()->to('/admin/users')
                           ->with('error', 'Usuario no encontrado.');
        }

        // Validate input
        $rules = [
            'email'    => "required|valid_email|is_unique[usuarios.email,id,{$id}]",
            'rol_id'   => 'required|integer',
            'nombres'  => 'required|min_length[2]|max_length[100]',
            'apellidos'=> 'required|min_length[2]|max_length[100]',
            'telefono' => 'permit_empty|max_length[20]',
        ];

        // Add password validation only if provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get old and new role
            $oldRol = $this->roleModel->find($usuario['rol_id']);
            $newRolId = $this->request->getPost('rol_id');
            $newRol = $this->roleModel->find($newRolId);

            // Update user
            $userData = [
                'rol_id'     => $newRolId,
                'email'      => $this->request->getPost('email'),
                'estado'     => $this->request->getPost('estado'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Update password if provided
            if ($this->request->getPost('password')) {
                $userData['password'] = $this->request->getPost('password');
            }

            $this->userModel->update($id, $userData);

            // Handle profile update
            $profileData = [
                'nombres'    => $this->request->getPost('nombres'),
                'apellidos'  => $this->request->getPost('apellidos'),
                'telefono'   => $this->request->getPost('telefono'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // If role changed, delete old profile and create new
            if ($oldRol['nombre'] !== $newRol['nombre']) {
                $oldTable = match ($oldRol['nombre']) {
                    'admin'     => 'administradores',
                    'profesor'  => 'profesores',
                    'acudiente' => 'acudientes',
                    default     => null
                };

                if ($oldTable) {
                    $db->table($oldTable)->where('usuario_id', $id)->delete();
                }

                $newTable = match ($newRol['nombre']) {
                    'admin'     => 'administradores',
                    'profesor'  => 'profesores',
                    'acudiente' => 'acudientes',
                    default     => null
                };

                if ($newTable) {
                    $profileData['usuario_id'] = $id;
                    $profileData['created_at'] = date('Y-m-d H:i:s');
                    if ($newRol['nombre'] === 'admin') {
                        $profileData['es_superadmin'] = 0;
                    }
                    if (in_array($newRol['nombre'], ['profesor', 'acudiente'])) {
                        $profileData['tipo_documento']   = $this->request->getPost('tipo_documento') ?? 'CC';
                        $profileData['numero_documento']  = $this->request->getPost('numero_documento') ?: null;
                    }
                    $db->table($newTable)->insert($profileData);
                }
            } else {
                // Same role, just update profile
                $profileTable = match ($newRol['nombre']) {
                    'admin'     => 'administradores',
                    'profesor'  => 'profesores',
                    'acudiente' => 'acudientes',
                    default     => null
                };

                if ($profileTable) {
                    if (in_array($newRol['nombre'], ['profesor', 'acudiente'])) {
                        $profileData['tipo_documento']   = $this->request->getPost('tipo_documento') ?? 'CC';
                        $profileData['numero_documento']  = $this->request->getPost('numero_documento') ?: null;
                    }
                    $db->table($profileTable)
                       ->where('usuario_id', $id)
                       ->update($profileData);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al actualizar usuario');
            }

            // Log action
            $this->logAction('UPDATE', 'usuarios', $id);

            return redirect()->to('/admin/users')
                           ->with('message', 'Usuario actualizado exitosamente.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $usuario = $this->userModel->find($id);

        if (!$usuario) {
            return redirect()->to('/admin/users')
                           ->with('error', 'Usuario no encontrado.');
        }

        // Prevent self-deletion
        if ($id == session()->get('user_id')) {
            return redirect()->to('/admin/users')
                           ->with('error', 'No puede eliminar su propio usuario.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Get role to delete profile
            $rol = $this->roleModel->find($usuario['rol_id']);
            $profileTable = match ($rol['nombre']) {
                'admin'     => 'administradores',
                'profesor'  => 'profesores',
                'acudiente' => 'acudientes',
                default     => null
            };

            if ($profileTable) {
                $db->table($profileTable)->where('usuario_id', $id)->delete();
            }

            // Delete user
            $this->userModel->delete($id);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al eliminar usuario');
            }

            // Log action
            $this->logAction('DELETE', 'usuarios', $id);

            return redirect()->to('/admin/users')
                           ->with('message', 'Usuario eliminado exitosamente.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/admin/users')
                           ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Send credentials email to existing user (generates temp password)
     */
    public function sendCredentials($id)
    {
        $usuario = $this->userModel->getUserWithRole($id);

        if (!$usuario) {
            return redirect()->to('/admin/users')
                           ->with('error', 'Usuario no encontrado.');
        }

        try {
            // Generate temporary password
            $tempPassword = 'Hero' . rand(1000, 9999) . '!';

            // Update password in database
            $this->userModel->update($id, [
                'password'   => $tempPassword,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Get profile name
            $profile = $this->userModel->getProfileData($id, $usuario['rol_nombre']);
            $nombre = $profile['nombres'] ?? $profile['nombre'] ?? $usuario['email'];

            // Send email with credentials
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $usuario['email'], 'nombre' => $nombre],
                'bienvenida',
                [
                    'nombre'            => $nombre,
                    'email'             => $usuario['email'],
                    'password_temporal' => $tempPassword,
                ]
            );

            // Log action
            $this->logAction('SEND_CREDENTIALS', 'usuarios', $id);

            return redirect()->to('/admin/users')
                           ->with('message', 'Credenciales enviadas por email a ' . $usuario['email']);

        } catch (\Exception $e) {
            log_message('error', 'Error enviando credenciales: ' . $e->getMessage());
            return redirect()->to('/admin/users')
                           ->with('error', 'Error al enviar credenciales: ' . $e->getMessage());
        }
    }

    /**
     * Send welcome email with credentials to newly created user
     */
    protected function enviarEmailBienvenida(string $email, string $nombres, string $password, string $rol): void
    {
        try {
            $sendgrid = new \App\Libraries\SendGridService();
            $sendgrid->enviar(
                ['email' => $email, 'nombre' => $nombres],
                'bienvenida',
                [
                    'nombre'           => $nombres,
                    'email'            => $email,
                    'password_temporal'=> $password,
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error enviando email de bienvenida: ' . $e->getMessage());
        }
    }

    /**
     * Log admin action
     */
    protected function logAction(string $action, string $tabla, int $registroId): void
    {
        try {
            $db = \Config\Database::connect();
            $db->table('auditoria')->insert([
                'usuario_id'  => session()->get('user_id'),
                'accion'      => $action,
                'tabla'       => $tabla,
                'registro_id' => $registroId,
                'ip'          => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error logging action: ' . $e->getMessage());
        }
    }
}
