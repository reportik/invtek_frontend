<?php

namespace App\Http\Controllers\Finanzas;

//use PDF;
use DateTime;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\CG_CXC_gastos as Gastos;
use Illuminate\Support\Facades\Storage;
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
  public function guardar(Request $request)
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
        $nomina_empleado = Auth::user()->codigo_empleado;
        /*  $gasto = new Gastos();
        $gasto->GAS_fecha_registro = new DateTime('now'); //date('Y-m-d') //
        $gasto->GAS_empleado = $nomina_empleado;
        $gasto->GAS_ceco = $ceco;
        $gasto->GAS_sitio = $sitio;
        $gasto->GAS_dias_habiles = $dias_habiles;
        $gasto->GAS_monto_total = $grantotal;
        $gasto->save(); */
        \DB::beginTransaction();
        $cg_id = \DB::table('CG_gastos')->insertGetId([
          'GAS_fecha_registro' => new DateTime('now'),
          'GAS_empleado' => $nomina_empleado,
          'GAS_ceco' => $ceco,
          'GAS_sitio' => $sitio,
          'GAS_dias_habiles' => $dias_habiles,
          'GAS_monto_total' => $grantotal
        ]);
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
  public function exportar($gas_id)
  {
    ini_set('memory_limit', '-1');
    set_time_limit(0);

    $formato = 'pdf'; //Request::input('type');
    $storage_path = storage_path() . '/exports/';
    $export_store = ($formato == 'pdf') ? 'inline' : 'export';

    //$gas_id = $request->input('cg_id');

    $gasto = Gastos::find($gas_id);
    $query = "select d.GAD_concepto, d.GAD_descripcion, d.GAD_monto , d.GAD_iva, d.GAD_monto + d.GAD_iva as monto, d.GAD_asistentes
    from CG_gastos_detalle d where GAD_GAS_id=" . $gas_id;
    $data = \DB::select($query);
    if (count($data) == 0) {

      throw new \Exception("Comprobación sin partidas", 1);
    }
    $empleadoId = $gasto->GAS_empleado;
    $empleado = User::where('codigo_empleado', $empleadoId)->first();

    $empleadoInfo = $empleado->codigo_empleado . ' - ' . $empleado->name;
    //dd($gasto->GAS_empleado);
    $titulo = "REPORTE DE GASTOS #" . $gas_id;
    $subtitulo = $empleadoInfo;

    $ceco = CentroCosto::where('CEN_ceco', $gasto->GAS_ceco)->first();

    $subtitulo2 = $ceco->CEN_descripcion;

    $fileName = date('Y-m-d') . " Comprobacion Gastos #" . $gas_id . "-" . $empleado->name;
    $dias_habiles = $gasto->GAS_dias_habiles;
    $sitio = $gasto->GAS_sitio;
    $total_documento = $gasto->GAS_monto_total;
    return self::pdf($total_documento, $sitio, $dias_habiles, $data, $empleadoInfo, $fileName, $storage_path, $titulo, $subtitulo, $subtitulo2, $export_store);
  }
  public function pdf($total_documento, $sitio, $dias_habiles, &$data, $empleadoInfo, $fileName, $storage_path, $titulo, $subtitulo, $subtitulo2, $export_store)
  {
    ini_set('memory_limit', '-1');
    set_time_limit(0);
    $path = 'finanzas.comprobacionGastos.formatoComprobacionGastosPDF';
    $fechaImpresion = date("d-m-Y H:i:s");
    $headerHtml = view()->make(
      'plantilla_pdfheader_horizontal',
      [
        'titulo' => $titulo,
        'fechaImpresion' => 'Fecha de Impresión: ' . $fechaImpresion,
        'subtitulo' =>  $subtitulo,
        'subtitulo2' =>  $subtitulo2,
        'align_subt2' =>  'right',
      ]
    )->render();
    $footerHtml = view()->make(
      'plantilla_pdf_footer_horizontal',
      ['total_documento' => $total_documento]
    )->render();

    $pdf = \PDF::loadView($path, compact('data', 'sitio', 'dias_habiles'));

    $pdf->setOption('header-html', $headerHtml);
    $pdf->setOption('footer-html', $footerHtml);
    //$pdf->setOption('footer-center', 'Pagina [page] de [toPage]');
    //$pdf->setOption('footer-left', '');
    //$pdf->setOption('orientation', 'Landscape');
    $pdf->setOption('margin-top', '50mm');
    $pdf->setOption('margin-left', '5mm');
    $pdf->setOption('margin-right', '5mm');
    $pdf->setOption('page-size', 'Letter');
    /* if ($export_store == 'save') {

      $fileName = $storage_path . $fileName . '_' . $prestacion;
      if (file_exists($fileName . '.pdf')) {
        unlink($fileName . '.pdf');
      }
    } */
    return $pdf->{$export_store}($fileName . '.pdf');
  }
}
