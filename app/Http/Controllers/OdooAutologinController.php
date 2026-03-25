<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OdooAutologinController extends Controller
{
    /**
     * Garantiza URL absoluta (https) para redirect()->away().
     * Si solo viene "mi-instancia.odoo.com/autologin?...", el navegador lo resuelve como ruta bajo el sitio Laravel.
     */
    private static function absolutizeOdooAutologinUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return 'https://'.ltrim($url, '/');
    }

    /**
     * Página de prueba con un enlace para ir a Odoo sin volver a iniciar sesión.
     */
    public function paginaPrueba(): View
    {
        return view('odoo.pagina-prueba-autologin');
    }

    // ─── Laravel → Odoo ───────────────────────────
    public function redirectToOdoo(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder a Odoo.');
        }

        $odooBaseUrl = config('services.odoo.base_url');
        if (!$odooBaseUrl) {
            return redirect()->back()
                ->with('error', 'Falta configurar ODOO_BASE_URL en .env');
        }

        $response = Http::asJson()->post('http://localhost:3036/autologin-token', [
            'login' => $user->email,
        ]);

        if (!$response->successful()) {
            return redirect()->back()
                ->with('error', 'No se pudo generar el enlace a Odoo: ' . ($response->body() ?: 'Error del servidor'));
        }

        $data = $response->json();
        $autologinUrl = $data['autologin_url'] ?? null;

        if (!$autologinUrl) {
            return redirect()->back()
                ->with('error', 'Respuesta inválida del servidor de autologin.');
        }

        $autologinUrl = self::absolutizeOdooAutologinUrl($autologinUrl);
        if ($autologinUrl === '') {
            return redirect()->back()
                ->with('error', 'URL de autologin inválida.');
        }

        $redirectPath = $request->query('redirect', '/my/home');
        if ($redirectPath && str_starts_with($redirectPath, '/')) {
            $autologinUrl .= '&redirect=' . urlencode($redirectPath);
        }

        return redirect()->away($autologinUrl);
    }

    // ─── Odoo → Laravel ───────────────────────────
    public function autologinFromOdoo(Request $request)
    {
        $token = $request->query('token');
        if (!$token) {
            return redirect()->route('login')->with('error', 'Token no proporcionado.');
        }

        $response = Http::asJson()->post('http://localhost:3036/validate-autologin-token', [
            'token' => $token,
        ]);

        if (!$response->successful()) {
            return redirect()->route('login')
                ->with('error', 'Token inválido o expirado.');
        }

        $data = $response->json();
        $login = $data['login'] ?? null;
        if (!$login) {
            return redirect()->route('login')
                ->with('error', 'No se pudo obtener el usuario del token.');
        }

        $user = User::where('email', $login)->first();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'El usuario no existe en este sistema. Inicia sesión primero de forma normal.');
        }

        Auth::login($user);

        $redirectPath = $request->query('redirect', '/');
        return redirect($redirectPath);
    }
}
