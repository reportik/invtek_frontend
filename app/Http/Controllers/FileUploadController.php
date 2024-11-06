<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
  public function upload(Request $request)
  {
    $validatedData = $request->validate([
      'file' => 'required|file|mimes:pdf|max:2048', // Ajusta según tus necesidades
      'param1' => 'required',
      'param2' => 'required'
    ]);

    $file = $request->file('file');
    $param1 = $request->input('param1');
    $param2 = $request->input('param2');

    // Procesa el archivo y los parámetros como necesites
    // Ejemplo de guardado del archivo
    // $path = $file->store('uploads');
    $path = 'x';

    return response()->json(['message' => 'Archivo subido con éxito', 'path' => $path], 200);
  }
}
