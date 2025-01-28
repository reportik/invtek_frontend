<?php

namespace App\Http\Controllers\dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class Analytics extends Controller
{
  public function index()
  {
    ini_set('memory_limit', '256M');
    /* $data = [];
    try {
      $response = Http::get('http://localhost:8036/items');
      $data = $response->json();
    } catch (\Throwable $th) {
      //throw $th;
    } */
    $cards_1 = [
      ["opcion_radio" => "Muro Interior", "image" => "im1.png", "a_selected" => "true"],
      ["opcion_radio" => "Muro Exterior", "image" => "im2.png", "a_selected" => ""],
      ["opcion_radio" => "Techo Interior", "image" => "im3.png", "a_selected" => ""],
      ["opcion_radio" => "Techo Exterior", "image" => "im4.png", "a_selected" => ""],
      ["opcion_radio" => "Escuadra", "image" => "im5.png", "a_selected" => ""]
    ];

    $cards_2 = [
      ["opcion_radio" => "Tradicional", "image" => "IMG6.jpg", "a_selected" => "true"],
      ["opcion_radio" => "Ripplefold", "image" => "IMG7.jpg", "a_selected" => ""],
      ["opcion_radio" => "Ojillos", "image" => "IMG8.jpg", "a_selected" => ""]
    ];

    $cards_3 = [
      ["opcion_radio" => "Blackout", "image" => "img9.PNG", "a_selected" => "true"],
      ["opcion_radio" => "Sheer", "image" => "img10.PNG", "a_selected" => ""]
      // ["opcion_radio" => "Decorativa", "image" => "img11.PNG"]
    ];

    $steps = [
      ["a_selected" => "true", "title" => "ESPACIO O UBICACIÓN", "number" => "1"],
      ["a_selected" => "false", "title" => "SISTEMA DE CONFECCIÓN", "number" => "2"],
      ["a_selected" => "false", "title" => "TIPO DE TELA", "number" => "3"],
      ["a_selected" => "false", "title" => "MEDIDAS Y HOJAS", "number" => "4"]
    ];

    // Consulta todos los datos de la tabla
    $telas = \DB::table('RPT_ODOO_CORTINAS')->select('id', 'name', 'Tipo')->get();

    // Separar las telas en dos arrays según el tipo
    $telas_blackout = $telas->where('Tipo', 'blackout')->values();
    $telas_sheer = $telas->where('Tipo', 'sheer')->values();

    /*try {
      // Ruta al archivo JSON en la carpeta public
      $path_blackout = public_path('BLACKOUT.json');
      $path_sheer = public_path('SHEER.json');

      // Lee el contenido del archivo
      $json_blackout = File::get($path_blackout);
      $json_sheer = File::get($path_sheer);


      // Decodifica el JSON a un array asociativo
      $data_blackout = json_decode($json_blackout, true);
      $data_sheer = json_decode($json_sheer, true);

      // Asigna el contenido a una variable
      $telas_blackout = $data_blackout;
      $telas_sheer = $data_sheer;
    } catch (\Throwable $th) {
      //throw $th;
    }*/
    return view('main', compact('cards_1', 'cards_2', 'cards_3', 'steps', 'telas_blackout', 'telas_sheer'));

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
