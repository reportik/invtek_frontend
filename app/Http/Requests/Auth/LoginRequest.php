<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use DateTime;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
   */
  public function rules(): array
  {
    return [
      'email' => ['required', 'email'],
      'password' => ['required', 'string'],
    ];
  }

  /**
   * Attempt to authenticate the request's credentials.
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function authenticate(): void
  {
    $this->ensureIsNotRateLimited();
    $result = false;
    //$result = Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->boolean('remember'));

    //dd($response, $response->successful());

    if (! $result) {
      $response = Http::asJson()->post('http://itekniaapp.serveftp.com:3036/auth', [
        'user_id' => $this->email,
        'password' => $this->password,
      ]);
      $userData = $response->json();
      //dd($userData);
      if ($response->successful()) {
        // Buscar o crear usuario en Laravel
        //dd($userData);
        $user = User::updateOrCreate(
          ['email' => $this->email], // Cambiar a email si Odoo lo maneja
          [
            'name' => $userData['name'],
            'odoo_user_id' => $userData['user_id'],
            'odoo_partner_id' => $userData['partner_id'],
            'password' => bcrypt($this->password), // Evita guardar contraseñas reales
            'price_list_id' => $userData['price_list'][0], // Asumiendo que tienes un campo price_list_id en tu tabla users
            'price_list_name' => $userData['price_list'][1], // Asumiendo que tienes un campo price_list_name en tu tabla users
            'role_id' => ($userData['config']) ? 1 : 0, // Asumiendo que tienes un campo price_list_name en tu tabla users
          ]
        );
        $user->save();
        //dd($user);

        // Iniciar sesión en Laravel

        $result = Auth::loginUsingId($user->id, remember: $this->boolean('remember'));
        //return redirect()->intended(route('dashboard', absolute: false));
      }
    }
    //dd($result);
    if (! $result) {
      //RateLimiter::hit($this->throttleKey());
      throw ValidationException::withMessages([
        'email' => __('Verifica tus Credenciales'),
      ]);
    }

    //RateLimiter::clear($this->throttleKey());
  }

  /**
   * Ensure the login request is not rate limited.
   *
   * @throws \Illuminate\Validation\ValidationException
   */
  public function ensureIsNotRateLimited(): void
  {
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
      return;
    }

    event(new Lockout($this));

    $seconds = RateLimiter::availableIn($this->throttleKey());

    throw ValidationException::withMessages([
      'email' => trans('auth.throttle', [
        'seconds' => $seconds,
        'minutes' => ceil($seconds / 60),
      ]),
    ]);
  }

  /**
   * Get the rate limiting throttle key for the request.
   */
  public function throttleKey(): string
  {
    return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
  }
}
