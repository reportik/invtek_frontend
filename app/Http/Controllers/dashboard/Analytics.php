<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Finanzas\ComprobacionGastosController;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
    $var = new ComprobacionGastosController();
    return $var->index(); //para devolver de momento la vista de comprobacion de gastos
    return view('content.dashboard.dashboards-analytics');
  }
  public function set_password()
  {
    return view('content.authentications.auth-update-password');
  }
}
