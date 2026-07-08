<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class ServerController extends Controller
{
    public function store(Request $request)
    {
        // Solo administradores pueden agregar servidores
        if (session('rol') !== 'administrador') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre'    => 'required|string|max:255',
            'ip'        => 'required|string|max:255',
            'puerto'    => 'required|integer',
            'usuario'   => 'nullable|string|max:255',
            'password'  => 'nullable|string|max:255',
            'protocolo' => 'required|string|in:ftp,sftp',
        ]);

        DB::table('ss_servidores_externos')->insert([
            'nombre'    => trim($request->nombre),
            'ip'        => trim($request->ip),
            'puerto'    => $request->puerto,
            'usuario'   => trim($request->usuario ?? 'anonymous'),
            // Encriptamos la contraseña para no guardarla en texto plano, si está vacía guardamos un string vacío encriptado
            'password'  => Crypt::encryptString($request->password ?? ''),
            'protocolo' => $request->protocolo,
        ]);

        return response()->json(['success' => true, 'message' => 'Servidor agregado correctamente.']);
    }

    public function update(Request $request, $id)
    {
        if (session('rol') !== 'administrador') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre'    => 'required|string|max:255',
            'ip'        => 'required|string|max:255',
            'puerto'    => 'required|integer',
            'usuario'   => 'nullable|string|max:255',
            'protocolo' => 'required|string|in:ftp,sftp',
        ]);

        $data = [
            'nombre'    => trim($request->nombre),
            'ip'        => trim($request->ip),
            'puerto'    => $request->puerto,
            'usuario'   => trim($request->usuario ?? 'anonymous'),
            'protocolo' => $request->protocolo,
        ];

        // Solo se actualiza la contraseña si se proporcionó una nueva
        if (!empty($request->password)) {
            $data['password'] = Crypt::encryptString($request->password);
        }

        DB::table('ss_servidores_externos')->where('id', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'Servidor actualizado.']);
    }

    public function destroy($id)
    {
        if (session('rol') !== 'administrador') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        DB::table('ss_servidores_externos')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'Servidor eliminado.']);
    }
}
