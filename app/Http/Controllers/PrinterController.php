<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrinterController extends Controller
{
  // Array de URLs de las impresoras
  private $printerUrls = [
    'https://192.168.1.15/stat/welcome.php',
    'https://192.168.1.25/stat/welcome.php',
    'https://192.168.1.28/stat/welcome.php',
    'https://192.168.1.33/stat/welcome.php',
    'https://192.168.1.36/stat/welcome.php',
    'https://192.168.1.40/stat/welcome.php',
    'https://192.168.1.45/stat/welcome.php',
    'https://192.168.1.52/stat/welcome.php',
    'https://192.168.2.22/stat/welcome.php',
    'https://192.168.2.24/stat/welcome.php'
  ];

  // Función para obtener el contenido de la URL
  private function getDataFromUrl($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Para seguir redirecciones
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Deshabilitar la verificación del certificado SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Deshabilitar la verificación del host SSL
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
      return 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return $data;
  }

  public function index()
  {
    $printerData = [];

    foreach ($this->printerUrls as $url) {
      // Obtener los datos de la URL
      $data = $this->getDataFromUrl($url);

      // Verificar si se obtuvieron datos
      if ($data === false) {
        $printerData[] = [
          'url' => $url,
          'error' => 'Error al obtener los datos de la impresora'
        ];
        continue;
      }

      // Extraer los porcentajes de consumibles usando expresiones regulares
      preg_match_all('/<div class="levelIndicatorPercentage">\s*<span>(\d+)%<\/span>/', $data, $percentageMatches);

      // Extraer el número de serie y las impresiones usando expresiones regulares
      preg_match('/Device Serial Number<\/td>\s*<td class="feedback">([^<]+)<\/td>/s', $data, $serialMatches);
      preg_match('/Black Impressions<\/td>\s*<td class="feedback">(\d+)<\/td>/', $data, $blackPrintsMatches);
      preg_match('/Color Impressions<\/td>\s*<td class="feedback">(\d+)<\/td>/', $data, $colorPrintsMatches);
      preg_match('/Total Impressions<\/td>\s*<td class="feedback">(\d+)<\/td>/', $data, $totalPrintsMatches);

      // Asignar los valores extraídos
      $serialNumber = isset($serialMatches[1]) ? $serialMatches[1] : 'N/A';
      $blackPrints = isset($blackPrintsMatches[1]) ? $blackPrintsMatches[1] : 'N/A';
      $colorPrints = isset($colorPrintsMatches[1]) ? $colorPrintsMatches[1] : 'N/A';
      $totalPrints = isset($totalPrintsMatches[1]) ? $totalPrintsMatches[1] : 'N/A';
      $percentages = $percentageMatches[1];

      // Añadir los datos de la impresora al array
      $printerData[] = [
        'url' => str_replace('/stat/welcome.php', '', $url),
        'serialNumber' => $serialNumber,
        'blackPrints' => $blackPrints,
        'colorPrints' => $colorPrints,
        'totalPrints' => $totalPrints,
        'percentages' => $percentages
      ];
    }

    return view('printers', compact('printerData'));
  }
}
