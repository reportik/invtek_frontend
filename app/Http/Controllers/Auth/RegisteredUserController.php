<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            // Password rules manuales:
            'password' => 'required|string|min:8',
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo electrónico debe tener máximo 255 caracteres.',
            'username.required' => 'El nombre es requerido.',
            'username.string' => 'El nombre debe ser una cadena de texto.',
            'username.max' => 'El nombre debe tener máximo 255 caracteres.',
            'password.required' => 'La contraseña es requerida.',
            'password.string' => 'La contraseña debe ser una cadena de texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        //dd($request->all());
        // Llamada al endpoint externo para crear usuario
        $response = \Illuminate\Support\Facades\Http::asJson()->post('http://itekniaapp.serveftp.com:3036/register', [
            'name' => $request->username,
            'user_id' => $request->email,
            'password' => $request->password,
        ]);
        $userData = $response->json();
        //dd($userData);
        if ($response->successful()) {
            // Buscar o crear usuario en Laravel
            //dd($userData);
            $user = User::updateOrCreate(
                ['email' => $request->email], // Cambiar a email si Odoo lo maneja
                [
                    'name' => $userData['name'],
                    'odoo_user_id' => $userData['user_id'],
                    'odoo_partner_id' => $userData['partner_id'],
                    'password' => bcrypt($request->password), // Evita guardar contraseñas reales
                    'price_list_id' => $userData['price_list'][0], // Asumiendo que tienes un campo price_list_id en tu tabla users
                    'price_list_name' => $userData['price_list'][1], // Asumiendo que tienes un campo price_list_name en tu tabla users
                ]
            );
            event(new \Illuminate\Auth\Events\Registered($user));
            \Illuminate\Support\Facades\Auth::login($user);
        }
        if (!$response->successful()) {
            $error = $response->json();
            $message = $error['detail'] ?? 'Error desconocido';
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => $message
            ]);
        }

        return redirect('/');
    }
}
