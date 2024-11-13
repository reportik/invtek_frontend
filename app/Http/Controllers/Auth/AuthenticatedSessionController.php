<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Redirect;

class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   */
  public function create(): View
  {
    return view('auth.login');
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(LoginRequest $request): RedirectResponse
  {


    $request->authenticate();

    $request->session()->regenerate();
    //dd($request->password);
    if ($request->password == '1234') {
      return Redirect::route('set-password');
      //return redirect()->intended(route('set-password', absolute: false));
      //return redirect('/auth/update-password');
      //return redirect()->route('auth-update-password');
    } else {
      return Redirect::route('dashboard');
      //return redirect()->intended(route('set-password', absolute: false));
      //return redirect()->intended(route('dashboard', absolute: false));
    }
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request): RedirectResponse
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/');
  }
}
