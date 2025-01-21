<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function index()
  {
    $data = [];
    try {
      // $response = Http::get('http://localhost:8036/items');
      // $data = $response->json();
    } catch (\Throwable $th) {
      //throw $th;
    }
    $cards_1 = [
      ["opcion_radio" => "Muro Interior", "image" => "im1.png"],
      ["opcion_radio" => "Muro Exterior", "image" => "im2.png"],
      ["opcion_radio" => "Techo Interior", "image" => "im3.png"],
      ["opcion_radio" => "Techo Exterior", "image" => "im4.png"],
      ["opcion_radio" => "Escuadra", "image" => "im5.png"]
    ];

    $cards_2 = [
      ["opcion_radio" => "Tradicional", "image" => "IMG6.jpg"],
      ["opcion_radio" => "Ripplefold", "image" => "IMG7.jpg"],
      ["opcion_radio" => "Ojillos", "image" => "IMG8.jpg"]
    ];

    $cards_3 = [
      ["opcion_radio" => "Blackout", "image" => "img9.PNG"],
      ["opcion_radio" => "Sheer", "image" => "img10.PNG"]
      // ["opcion_radio" => "Decorativa", "image" => "img11.PNG"]
    ];

    $steps = [
      ["a_selected" => "true", "title" => "SELECCIONA EL ESPACIO DONDE UBICARÁS TU CORTINA", "number" => "1"],
      ["a_selected" => "false", "title" => "ELIGE EL SISTEMA DE CONFECCIÓN QUE DESEAS", "number" => "2"],
      ["a_selected" => "false", "title" => "ELIGE EL TIPO DE TELA EN QUE DESEES CONFECCIONAR TU CORTINA", "number" => "3"],
      ["a_selected" => "false", "title" => "ELIGE LA TELA QUE DESEES UTILIZAR", "number" => "4"]
    ];

    return view('main', compact('cards_1', 'cards_2', 'cards_3', 'steps'));

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
