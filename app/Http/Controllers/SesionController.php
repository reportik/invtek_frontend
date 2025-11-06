<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SesionController extends Controller
{
    public function actualizar(Request $request)
    {
        $clave = $request->input('clave');
        $valor = $request->input('valor');

        // Si la clave es 'avance_temporal', manejar de forma especial
        if ($clave === 'avance_temporal') {
            // Obtener el avance actual de la sesión
            $avanceActual = Session::get('avance_temporal', '');
            
            // Decodificar si es string
            if (is_string($avanceActual)) {
                $avanceActual = json_decode($avanceActual, true);
            }
            
            // Si no es array, inicializar como array vacío
            if (!is_array($avanceActual)) {
                $avanceActual = [];
            }
            
            // Decodificar el nuevo valor si es string
            $nuevoValor = $valor;
            if (is_string($nuevoValor)) {
                $nuevoValor = json_decode($nuevoValor, true);
            }
            
            // Si el nuevo valor no es array, inicializar como array vacío
            if (!is_array($nuevoValor)) {
                $nuevoValor = [];
            }
            
            // Fusionar el avance actual con el nuevo valor
            $avanceFusionado = array_merge($avanceActual, $nuevoValor);
            
            // Guardar en la sesión como JSON
            Session::put('avance_temporal', json_encode($avanceFusionado));
            
            // Si el usuario está autenticado, guardar también en la base de datos
            if (Auth::check()) {
                Auth::user()->avance = json_encode($avanceFusionado);
                Auth::user()->save();
            }
            
            return response()->json([
                'success' => true,
                'avance_fusionado' => $avanceFusionado
            ]);
        }
        
        // Para cualquier otra clave, guardar directamente
        Session::put($clave, $valor);

        return response()->json(['success' => true]);
    }

    public function obtener(Request $request)
    {
        $clave = $request->query('clave');
        $valor = Session::get($clave);
        
        // Si la clave es 'avance_temporal' y el valor es string, intentar decodificar
        if ($clave === 'avance_temporal' && is_string($valor)) {
            try {
                $valor = json_decode($valor, true);
            } catch (\Exception $e) {
                // Si falla el decode, devolver el valor tal cual
            }
        }
        
        return response()->json([
            'success' => true,
            'valor' => $valor
        ]);
    }
}
