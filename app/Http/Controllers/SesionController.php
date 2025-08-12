<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SesionController extends Controller
{
    public function actualizar(Request $request)
    {
        $clave = $request->input('clave');
        $valor = $request->input('valor');

        Session::put($clave, $valor);

        return response()->json(['success' => true]);
    }

    public function obtener(Request $request)
    {
        $clave = $request->query('clave');
        return response()->json([
            'success' => true,
            'valor' => session($clave)
        ]);
    }
}
