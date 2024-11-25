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
use Illuminate\Support\Facades\Storage;

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
    $disco_cg = "almacen_digital_comprobacion_gastos";

    //$var = $request->all();
    $ceco = $request->get('ceco');
    $sitio = $request->get('sitio');
    $grantotal = $request->get('grantotal');
    $dias_habiles = $request->get('dias_habiles');
    $tbl_cg = json_decode($request->get('Tbl'));
    //return compact('tbl_cg');
    $cg_id = 'E';
    $partidas = (array)$tbl_cg;
    //return compact('partidas');
    try {
      if (count($partidas) > 0) {
        \DB::beginTransaction();
        $nomina_empleado = Auth::user()->codigo_empleado;
        $gasto = new Gastos();
        $gasto->GAS_fecha_registro = new DateTime('now'); //date('Y-m-d') //
        $gasto->GAS_empleado = $nomina_empleado;
        $gasto->GAS_ceco = $ceco;
        $gasto->GAS_sitio = $sitio;
        $gasto->GAS_dias_habiles = $dias_habiles;
        $gasto->GAS_monto_total = $grantotal;
        $gasto->save();
        $cg_id = $gasto->value('GAS_id');
        foreach ($partidas as $key => $partida) {
          $gas_detalle = new GastosDetalle();
          //falta pasar los pdf y xml
          $gas_detalle->GAD_GAS_id = $cg_id; //?
          $gas_detalle->GAD_xml = $partida->xml; //?
          $gas_detalle->GAD_pdf = $partida->pdf; //?
          $gas_detalle->GAD_asistentes = $partida->asistentes; //?
          $gas_detalle->GAD_fecha_factura = $partida->fecha; //?
          $gas_detalle->GAD_concepto = $partida->concepto; //?
          $gas_detalle->GAD_descripcion = $partida->descripcion; //?
          $gas_detalle->GAD_monto = $partida->monto; //?
          $gas_detalle->GAD_iva = $partida->iva; //?

          $gas_detalle->save();
          $carpeta_destino = "/cg-temp-" . $nomina_empleado;
          $carpeta_nueva = date('Y-m-d') . '/cg-' . $cg_id . '-emp-' . $nomina_empleado;

          if (Storage::disk($disco_cg)->exists($carpeta_destino)) {
            // Mueve (renombra) la carpeta antigua a la nueva
            Storage::disk($disco_cg)->move($carpeta_destino, $carpeta_nueva);
          }
        }
        \DB::commit();
      }
      return compact('cg_id');
    } catch (Exception $e) {
      \DB::rollback();
      throw $e;
    }
  }
}
