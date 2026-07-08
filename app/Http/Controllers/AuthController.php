<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('loggedin')) {
            return redirect('/dashboard');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $username = trim($request->input('usuario'));
        $password = $request->input('password');

        // Validación básica
        if (empty($username) || empty($password)) {
            return redirect()->route('login')->with('error', 'Usuario y contraseña son requeridos');
        }

        if (strlen($password) > 12) {
            return redirect()->route('login')->with('error', 'La contraseña no puede exceder los 12 caracteres');
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
            return redirect()->route('login')->with('error', 'Formato de usuario inválido (solo letras, números y guiones bajos, máx 32 caracteres)');
        }

        $user = DB::table('ss_usuarios')
                  ->where('username', $username)
                  ->first();

        if ($user && Hash::check($password, $user->password)) {
            // Regenerar ID de sesión para prevenir session fixation
            $request->session()->regenerate();
            
            $roles = ['1' => 'administrador', '2' => 'supervisor', '3' => 'secretario'];
            $rol = $roles[$user->rol_id] ?? 'administrador';

            session([
                'loggedin' => true,
                'username' => $username,
                'user_id'  => $user->id,
                'rol'      => $rol,
                'login_time' => time(),
                'ip_address' => $request->ip(),
            ]);

            $intendedUrl = session('url.intended', '/dashboard');
            session()->forget('url.intended');

            return redirect($intendedUrl);
        }

        return redirect()->route('login')->with('error', 'Usuario o contraseña incorrectos');
    }

    public function logout(Request $request)
    {
        // Invalidar sesión completamente
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'redirect' => route('login')]);
        }

        return redirect()->route('login');
    }

    /**
     * Ping de actividad — reinicia el contador server-side de inactividad.
     * Llamado cada vez que el cliente detecta actividad del usuario.
     */
    public function sessionPing(Request $request)
    {
        if (!session('loggedin')) {
            return response()->json(['expired' => true], 401);
        }

        session(['last_activity' => time()]);

        return response()->json([
            'ok'              => true,
            'last_activity'   => time(),
            'session_expires' => time() + 210,
        ]);
    }

    /**
     * Cierre de sesión silencioso para sendBeacon (cierre de pestaña).
     * No requiere redirección — solo invalida la sesión.
     */
    public function sessionClose(Request $request)
    {
        if (session('loggedin')) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['ok' => true]);
    }
}