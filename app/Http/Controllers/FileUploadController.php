<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
//use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
//use Symfony\Component\Process\Exception\ProcessFailedException;

class FileUploadController extends Controller
{
  public $gs = "C:\Program Files\gs\gs10.04.0\bin";
  public function upload(Request $request)
  {
    //FECHA/CG_ID-NOMINA/PARTIDA-FACTURA
    $disco_cg = "almacen_digital_comprobacion_gastos";

    //$fecha = date('Y-m-d');
    $cg_id = $request->input('cg_id');
    $nomina_empleado = Auth::user()->codigo_empleado . '-' . Auth::user()->name;
    $num_partida = $request->input('num_partida');

    $validatedData = $request->validate([
      'file' => 'required|file|mimes:pdf|max:2048', //
      'num_partida' => 'required'
    ]);

    //$carpeta_destino = $fecha . "/cg-temp-" . $nomina_empleado;
    $carpeta_destino = "/cg-temp-" . $nomina_empleado;
    $file = $request->file('file');
    $filename = $file->getClientOriginalName();
    $nombre_asignado = $carpeta_destino . "/" . $num_partida . "-" . $filename . "pdf";
    //$nombre_temp = $carpeta_destino . '/temp.pdf';

    $path = str_replace('\\', '/',  public_path() . '/Finanzas/AlmacenDigital_ComprobacionGastos/');
    if (!Storage::disk($disco_cg)->exists($carpeta_destino)) {
      File::makeDirectory($path . $carpeta_destino, 0777, true, true);
      chmod($path . $carpeta_destino, 0777);
    }
    //$comando = "gswin64c -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=";

    Storage::disk($disco_cg)->put($nombre_asignado, File::get($file));

    //$old_pdf = $path . $nombre_temp;
    //$new_pdf = $path . $nombre_asignado;

    //$comando .= $new_pdf . " " . $old_pdf;
    //shell_exec($comando);

    //Storage::disk($disco_cg)->delete($nombreOriginal);
    return response()->json(['message' => 'Archivo subido con éxito', 'path' => $path], 200);
  }
}
