<?php

namespace App\Controllers\Acudiente;

use App\Controllers\BaseController;

class PerfilController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        $usuario = $db->table('usuarios')
                      ->select('email')
                      ->where('id', $userId)
                      ->get()->getRowArray();

        return view('acudiente/perfil/index', [
            'title'      => 'Mi Perfil - Academia Heroicos',
            'pageTitle'  => 'Mi Perfil',
            'acudiente'  => $acudiente,
            'email'      => $usuario['email'] ?? '',
        ]);
    }

    public function actualizar()
    {
        $db = \Config\Database::connect();
        $userId = session()->get('user_id');

        $acudiente = $db->table('acudientes')
                        ->where('usuario_id', $userId)
                        ->get()->getRowArray();

        if (!$acudiente) {
            return redirect()->to('acudiente/perfil')
                ->with('error', 'Perfil no encontrado.');
        }

        $rules = [
            'telefono'  => 'required|max_length[20]',
            'direccion' => 'required|max_length[255]',
        ];

        // Validate documento fields only if the acudiente doesn't have them yet
        if (empty($acudiente['numero_documento'])) {
            $rules['tipo_documento']   = 'required';
            $rules['numero_documento'] = 'required|numeric|max_length[20]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', 'Por favor verifique los campos.');
        }

        $updateData = [
            'telefono'     => $this->request->getPost('telefono'),
            'telefono_alt' => $this->request->getPost('telefono_alt'),
            'direccion'    => $this->request->getPost('direccion'),
            'ciudad'       => $this->request->getPost('ciudad'),
            'ocupacion'    => $this->request->getPost('ocupacion'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        // Only allow saving documento fields if they weren't set before
        if (empty($acudiente['numero_documento'])) {
            $updateData['tipo_documento']   = $this->request->getPost('tipo_documento');
            $updateData['numero_documento'] = $this->request->getPost('numero_documento');
        }

        $db->table('acudientes')
           ->where('id', $acudiente['id'])
           ->update($updateData);

        // Update session name if changed
        session()->set('nombre', $acudiente['nombres']);

        return redirect()->to('acudiente/perfil')
            ->with('message', 'Perfil actualizado correctamente.');
    }

    public function cambiarPassword()
    {
        $userId   = session()->get('user_id');
        $db       = \Config\Database::connect();

        $passwordActual    = $this->request->getPost('password_actual');
        $passwordNuevo     = $this->request->getPost('password_nuevo');
        $passwordConfirmar = $this->request->getPost('password_confirmar');

        if (empty($passwordActual) || empty($passwordNuevo) || empty($passwordConfirmar)) {
            return redirect()->back()
                ->with('error', 'Todos los campos de contraseña son obligatorios.');
        }

        if (strlen($passwordNuevo) < 6) {
            return redirect()->back()
                ->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }

        if ($passwordNuevo !== $passwordConfirmar) {
            return redirect()->back()
                ->with('error', 'La nueva contraseña y la confirmación no coinciden.');
        }

        $usuario = $db->table('usuarios')
                      ->where('id', $userId)
                      ->get()->getRowArray();

        if (!$usuario) {
            return redirect()->back()
                ->with('error', 'Usuario no encontrado.');
        }

        if (!password_verify($passwordActual, $usuario['password'])) {
            return redirect()->back()
                ->with('error', 'La contraseña actual es incorrecta.');
        }

        try {
            $db->table('usuarios')
               ->where('id', $userId)
               ->update([
                   'password'   => password_hash($passwordNuevo, PASSWORD_DEFAULT),
                   'updated_at' => date('Y-m-d H:i:s'),
               ]);

            return redirect()->to('acudiente/perfil')
                ->with('message', 'Contraseña actualizada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la contraseña. Intente nuevamente.');
        }
    }
}
