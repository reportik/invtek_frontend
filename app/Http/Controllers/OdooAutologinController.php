<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OdooAutologinController extends Controller
{
    /**
     * Página de prueba con un enlace para ir a Odoo sin volver a iniciar sesión.
     */
    public function paginaPrueba(): View
    {
        return view('odoo.pagina-prueba-autologin');
    }

    /**
     * Obtiene un token de autologin desde FastAPI y redirige al usuario a Odoo.
     * Ruta protegida con auth: el usuario debe estar logueado en Laravel.
     */
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

        // Misma URL que usas en el resto del proyecto
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

        // Redirigir a una página específica de Odoo (ej. mis pedidos)
        $redirectPath = $request->query('redirect', '/my/quotes');
        if ($redirectPath && str_starts_with($redirectPath, '/')) {
            $autologinUrl .= '&redirect=' . urlencode($redirectPath);
        }

        return redirect()->away($autologinUrl);
    }
}
