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
  public function calcularMonto($ceco, $dias_habiles, $partidas)
  {
    // Obtener todas las cuentas
    $error_messages = array();
    $cuentas = Cuentas::all()->toArray();
    foreach ($partidas as $partida) {
      $concepto = $partida->concepto;
      $monto = $partida->monto + $partida->iva;
      $asistentes = $partida->asistentes;
      // Encontrar la cuenta correspondiente al concepto
      $cuenta = array_filter($cuentas, function ($cuenta) use ($concepto) {
        return $cuenta['CUE_cuenta'] == $concepto;
      });
      if (!$cuenta) {
        $error_messages[] = "No se encontró la cuenta para el concepto: $concepto";
        continue;
      }
      // Suponemos que el array_filter devuelve un array con un solo elemento
      $cuenta = array_values($cuenta)[0];
      // Calcular el límite según el CECO y el número de asistentes
      if ($ceco == "1132100") {
        $limite = $cuenta['CUE_M_limiteDG'] * $asistentes * $dias_habiles;
      } elseif (in_array($ceco, ["1133100", "1133200", "1133210", "1133220", "1133300", "1133400", "1133500", "1133600", "1133700", "1133800", "1133230"])) {
        $limite = $cuenta['CUE_M_limiteV'] * $asistentes * $dias_habiles;
      } else {
        $limite = $cuenta['CUE_M_limite'] * $asistentes * $dias_habiles;
      }
      // Verificar si el monto excede el límite
      if ($monto == 0) {
        $error_messages[] = "El monto ingresado no puede ser igual a cero para el concepto: " . $cuenta['CUE_concepto'];
      }
      if ($monto > $limite) {
        $error_messages[] = "El monto ingresado $" . number_format($monto, 2) . " supera el límite de $" . number_format($limite, 2) . " para el concepto: " . $cuenta['CUE_concepto'];
      }
    }
    return $error_messages;
  }
  public function guardar(Request $request)
  {
    $disco_cg = "almacen_digital_comprobacion_gastos";

    //$var = $request->all();
    $ceco = $request->get('ceco');
    $cg_id = $request->get('registroNuevo');
    $sitio = $request->get('sitio');
    $grantotal = $request->get('grantotal');
    $dias_habiles = $request->get('dias_habiles');
    $tbl_cg = json_decode($request->get('Tbl'));

    $partidas = (array)$tbl_cg;
    //return compact('partidas');
    try {
      if (count($partidas) > 0) {

        $user = Auth::user();
        $nomina_empleado = $user->codigo_empleado;
        $dept_empleado = $user->departamento;

        $verificacion = true;
        $rs = self::calcularMonto($ceco, $dias_habiles, $partidas);
        if (count($rs) > 0) {
          return response()->json(['message' => 'Se encontraron errores en el cálculo', 'errors' => $rs], 400);
        } else {
          if ($cg_id == '-') {
            $cg_id = \DB::table('CG_gastos')->insertGetId([
              'GAS_fecha_registro' => new DateTime('now'),
              'GAS_empleado' => $nomina_empleado,
              'GAS_ceco' => $ceco,
              'GAS_sitio' => $sitio,
              'GAS_dias_habiles' => $dias_habiles,
              'GAS_monto_total' => $grantotal,
              'GAS_estatus' => 'creada'
            ]);
            $estatus = 'creada';
          } else {

            $estatus = \DB::table('CG_gastos')
              ->where('GAS_id', $cg_id)->value('GAS_estatus');

            if ($estatus == 'creada' || $estatus == 'actualizada') {
              \DB::table('CG_gastos')
                ->where('GAS_id', $cg_id)
                ->update([
                  'GAS_fecha_registro' => new DateTime('now'),
                  'GAS_empleado' => $nomina_empleado,
                  'GAS_ceco' => $ceco,
                  'GAS_sitio' => $sitio,
                  'GAS_dias_habiles' => $dias_habiles,
                  'GAS_monto_total' => $grantotal,
                  'GAS_estatus' => 'actualizada'
                ]);
              $estatus = 'actualizada';
              \DB::table('CG_gastos_detalle')->where('GAD_GAS_id', $cg_id)->delete();
            }
          }
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
            /* $carpeta_destino = "/cg-temp-" . $nomina_empleado;
            $carpeta_nueva = date('Y-m-d') . '/cg-' . $cg_id . '-emp-' . $nomina_empleado;

            if (Storage::disk($disco_cg)->exists($carpeta_destino)) {
              // Mueve (renombra) la carpeta antigua a la nueva
              Storage::disk($disco_cg)->move($carpeta_destino, $carpeta_nueva);
            } */
          }
        }
      }
      return compact('cg_id', 'estatus');
    } catch (Exception $e) {
      //\DB::rollback();
      return response()->json([
        'message' => $e->getMessage(),
        'exception' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ], 500);
    }
  }
  public function enviar(Request $request)
  {
    $disco_cg = "almacen_digital_comprobacion_gastos";
    $cg_id = $request->get('id');

    try {
      $nomina_empleado = Auth::user()->codigo_empleado;

      $carpeta_destino = "/cg-temp-" . $nomina_empleado;
      $carpeta_nueva = date('Y-m-d') . '/cg-' . $cg_id . '-emp-' . $nomina_empleado;

      if (Storage::disk($disco_cg)->exists($carpeta_destino)) {
        // Mueve (renombra) la carpeta antigua a la nueva
        Storage::disk($disco_cg)->move($carpeta_destino, $carpeta_nueva);
      }
      \DB::table('CG_gastos')
        ->where('GAS_id', $cg_id)
        ->update([
          'GAS_fecha_registro' => new DateTime('now'),
          'GAS_estatus' => 'enviada'
        ]);
      return compact('cg_id');
    } catch (Exception $e) {
      //\DB::rollback();
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
    $query = "select cu.CUE_concepto as GAD_concepto, d.GAD_descripcion, d.GAD_monto , d.GAD_iva, d.GAD_monto + d.GAD_iva as monto, d.GAD_asistentes
    from CG_gastos_detalle d
    left join CG_CXC_cuentas cu on cu.CUE_cuenta = d.GAD_concepto
    where GAD_GAS_id=" . $gas_id;
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

  public function cuentas(Request $request)
  {
    return Cuentas::all();
  }
}
