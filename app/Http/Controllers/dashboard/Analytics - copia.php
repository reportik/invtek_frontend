<?php

namespace App\Http\Controllers\dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Finanzas\ComprobacionGastosController;

class Analytics extends Controller
{
  public function index()
  {
    $data = [];
    try {
      $response = Http::get('http://localhost:8036/items');
      $data = $response->json();
    } catch (\Throwable $th) {
      //throw $th;
    }


    return view('inicio', ['items' => $data]);

    //return view('welcome');
    // $var = new ComprobacionGastosController();
    // return $var->index(); //para devolver de momento la vista de comprobacion de gastos
    // return view('content.dashboard.dashboards-analytics');
  }
  public function set_password()
  {
    return view('content.authentications.auth-update-password');
  }
}
