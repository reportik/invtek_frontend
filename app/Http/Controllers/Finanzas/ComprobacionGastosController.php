<?php

namespace App\Http\Controllers\Finanzas;

use DateTime;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CG_CXC_gastos as Gastos;
use App\Models\CG_CXC_cuentas as Cuentas;
use App\Models\CG_CXC_CentroCosto as CentroCosto;
use App\Models\CG_CXC_gastosDetalle as GastosDetalle;

class ComprobacionGastosController extends Controller
{
  /**
   * Show the profile for a given user.
   */
  public function show(string $id): View
  {
    return view('user.profile', []);
  }
  public function index(): View
  {
    $cecos = CentroCosto::all();
    $conceptos = Cuentas::all();
    $concepto_items = '<option value="">Selecciona</option>';
    foreach ($conceptos as $item) {
      $concepto_items .= '<option value="' . $item->CUE_cuenta . '">' . $item->CUE_concepto . '</option>';
    }

    $version = random_int(1, 10000);
    return view('finanzas.comprobacionGastos.comprobacion-gastos', compact('cecos', 'version', 'concepto_items'));
  }
  public function guardar_comprobacion(Request $request)
  {
    $var = $request->all();
    $ceco = $request->get('ceco');
    $sitio = $request->get('sitio');
    $grantotal = $request->get('grantotal');
    $dias_habiles = $request->get('dias_habiles');
    $tbl_cg = json_decode($request->get('Tbl'));
    //return compact('tbl_cg');

    $partidas = (array)$tbl_cg;
    //return compact('partidas');
    try {
      if (count($partidas) > 0) {
        \DB::beginTransaction();
        $gasto = new Gastos();
        $gasto->GAS_fecha_registro = new DateTime('now'); //date('Y-m-d') //
        $gasto->GAS_empleado = Auth::user()->codigo_empleado;
        $gasto->GAS_ceco = $ceco;
        $gasto->GAS_sitio = $sitio;
        $gasto->GAS_dias_habiles = $dias_habiles;
        $gasto->GAS_monto_total = $grantotal;
        $gasto->save();
        foreach ($partidas as $key => $partida) {
          $gas_detalle = new GastosDetalle();
          //falta pasar los pdf y xml
          $gas_detalle->GAD_GAS_id = $gasto->value('GAS_id'); //?
          $gas_detalle->GAD_xml = $partida->xml; //?
          $gas_detalle->GAD_pdf = $partida->pdf; //?
          $gas_detalle->GAD_asistentes = $partida->asistentes; //?
          $gas_detalle->GAD_fecha_factura = $partida->fecha; //?
          $gas_detalle->GAD_concepto = $partida->concepto; //?
          $gas_detalle->GAD_descripcion = $partida->descripcion; //?
          $gas_detalle->GAD_monto = $partida->monto; //?
          $gas_detalle->GAD_iva = $partida->iva; //?

          $gas_detalle->save();
        }
        \DB::commit();
      }
      return compact('var');
    } catch (Exception $e) {
      \DB::rollback();
      throw $e;
    }
  }
}
