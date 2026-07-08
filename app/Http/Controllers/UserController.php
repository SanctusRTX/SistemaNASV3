<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // This is handled by DashboardController in this architecture
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:32|unique:ss_usuarios,username',
            'password' => 'required|string|max:12',
            'rol_id'   => 'required|integer|in:1,2,3',
        ]);

        DB::table('ss_usuarios')->insert([
            'username' => trim($request->username),
            'password' => Hash::make($request->password),
            'rol_id'   => $request->rol_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario creado correctamente.']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|string|min:3|max:32|unique:ss_usuarios,username,' . $id,
            'rol_id'   => 'required|integer|in:1,2,3',
            'password' => 'nullable|string|max:12',
        ]);

        $data = [
            'username' => trim($request->username),
            'rol_id'   => $request->rol_id,
        ];

        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('ss_usuarios')->where('id', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
    }

    public function destroy($id)
    {
        // Evitar que el administrador se elimine a sí mismo
        if (session('user_id') == $id) {
            return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.']);
        }

        DB::table('ss_usuarios')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
    }

    /**
     * El usuario autenticado edita su propio perfil (nombre y/o contraseña).
     * No permite cambiar el rol (seguridad).
     */
    public function updatePerfil(Request $request)
    {
        $id = session('user_id');

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Sesión no válida.'], 401);
        }

        $request->validate([
            'username' => 'required|string|min:3|max:32|unique:ss_usuarios,username,' . $id,
            'password' => 'nullable|string|max:12',
        ]);

        $data = ['username' => trim($request->username)];

        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('ss_usuarios')->where('id', $id)->update($data);

        // Actualizar el nombre en la sesión activa
        session(['username' => trim($request->username)]);

        return response()->json(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
    }
}
