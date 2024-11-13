<?php

namespace App\Http\Controllers\authentications;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class ForgotPasswordBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-forgot-password-basic');
  }
  public function update_password()
  {
    return view('content.authentications.auth-update-password');
  }
  public function store_update_password(Request $request)
  {
    App::setLocale('es');
    $request->validate([
      'password' => 'required|string|min:10|confirmed',
    ]);

    $user = User::where('codigo_empleado', Auth::user()->codigo_empleado)->first();
    $user->password = Hash::make($request->password);
    $user->save();

    return Redirect::route('login')->with('success', 'Inicia sesión con tu nueva contraseña');
  }
  public function reset_password($codigo_empleado)
  {
    $user = User::where('codigo_empleado', $codigo_empleado)->first();
    $user->password = Hash::make('1234');
    $user->save();
    return Redirect::route('login')->with('success', 'Inicia sesión con tu nueva contraseña');
  }
}
