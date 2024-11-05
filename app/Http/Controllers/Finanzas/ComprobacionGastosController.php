<?php

namespace App\Http\Controllers\Finanzas;

use Illuminate\View\View;
use App\Models\CG_CXC_CentroCosto as CentroCosto;
use App\Models\CG_CXC_cuentas as Cuentas;
use App\Models\CG_CXC_gastos as Gastos;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
    $version = random_int(1, 10000);
    return view('finanzas.comprobacionGastos.comprobacion-gastos', compact('cecos', 'version'));
  }
}
