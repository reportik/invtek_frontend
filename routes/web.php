<?php

use App\Models\OpcionCotizador;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\icons\RiIcons;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\OpcionCotizadorController;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\Cotizador\CotizacionController;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\Finanzas\ComprobacionGastosController;
use App\Http\Controllers\ProductoCantidadController;
use Illuminate\Support\Facades\Auth;

Route::get('login-test', function () {
  $response = Http::asJson()->post('http://localhost:3036/auth', [
    'user_id' => "albert91.me.d@gmail.com",
    'password' => "menajem28",
  ]);
  return $response->json();
});
Route::post('nueva-cotizacion', [CotizacionController::class, 'nuevaCotizacion']);
Route::get('generate-quotation-pdf', [CotizacionController::class, 'generateQuotationPdf']);
Route::post('create_quotation', [CotizacionController::class, 'createQuotation']);
Route::post('guardar-cotizacion', [CotizacionController::class, 'store']);
Route::post('get-cotizaciones', [CotizacionController::class, 'getCotizaciones']);
Route::post('update-cotizacion', [CotizacionController::class, 'actualizaCantidadesCotizacion']);
Route::post('eliminar-cotizacion', [CotizacionController::class, 'delete']);

Route::get('/create-quotation', [CotizacionController::class, 'createOdooCotizacion']);
Route::any('/create-quotation2', [CotizacionController::class, 'createOdooCotizacion2'])->name('create-quotation2');
Route::get('/create-contact', [CotizacionController::class, 'createOdooContact']);

Route::post('upload-pdf-cg', [FileUploadController::class, 'upload_pdf_cg'])->name('upload-pdf-cg');
Route::post('upload-xml-cg', [FileUploadController::class, 'upload_xml_cg'])->name('upload-xml-cg');
// Main Page Route
Route::get('/dashboard', [Analytics::class, 'inicio'])->name('dashboard');
Route::get('/', [Analytics::class, 'inicio']);
Route::get('inicio',  [Analytics::class, 'inicio'])->name('inicio');
Route::get('/set-password', [Analytics::class, 'set_password'])->middleware(['auth', 'verified'])->name('set-password');
Route::get('/cotizar', [Analytics::class, 'cotizar'])->name('cotizar');
// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// authentication
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('/auth/update-password', [ForgotPasswordBasic::class, 'update_password'])->name('auth-update-password');
Route::post('/update-password', [ForgotPasswordBasic::class, 'store_update_password'])->name('update-password');
Route::get('/reset-password/{codigo_empleado}', [ForgotPasswordBasic::class, 'reset_password']);

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
//Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/icons-ri', [RiIcons::class, 'index'])->name('icons-ri');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');


Route::post('/cotizador',  [Analytics::class, 'guardarAvance'])->name('guardarAvance');

Route::post('/guardar-articulo', [Analytics::class, 'guardarArticulo'])->name('guardar.articulo');

Route::get('/tipo-producto',  [Analytics::class, 'tipo_producto'])->name('tipo_producto'); //tipo_producto
Route::get('/tipo-confeccion',  [Analytics::class, 'tipo_confeccion'])->name('tipo_confeccion'); //tipo_confeccion
Route::get('/configuracion-medidas',  [Analytics::class, 'medidas'])->name('medidas'); //tipo_confeccion
Route::get('/telas',  [Analytics::class, 'telas'])->name('telas'); //tipo_confeccion
Route::get('/sistema_apertura',  [Analytics::class, 'sistema_apertura'])->name('sistema_apertura'); //tipo_confeccion
Route::get('/bastones',  [Analytics::class, 'bastones'])->name('bastones'); //tipo_confeccion
Route::get('/resumen',  [Analytics::class, 'resumen'])->name('resumen'); //resumen
Route::get('/test', [Analytics::class, 'testGetSelectorSiguiente'])->name('test');
Route::any('cotlog', function () {
  //obtener session(['avance_temporal' => json_encode($request->all())]);
  $cotlog = session('avance_temporal');
  $cotlog = json_decode($cotlog, true);
  /* if (Auth::check()) {
    $cotlog =  Auth::user()->avance;
    $cotlog = json_decode($cotlog, true);
  } */
  dd($cotlog, session('cotizacion_id'));
})->name('cotlog');

Route::any('descripcion', [Analytics::class, 'getDescripcionOpciones'])->name('descripcion');

/* Route::get('/dashboard2', function () {
  return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard2'); */

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use Illuminate\Support\Facades\App;

Route::get('/printers', [PrinterController::class, 'index']);
//Route::any('/cuentas', [ComprobacionGastosController::class, 'cuentas']);

Route::any('/pdf', function () {
  $pdf = App::make('snappy.pdf.wrapper');
  $pdf->loadHTML('<h1>Test</h1>');
  return $pdf->inline();
});

//test update-quotation
Route::get('/update-quotation', [Analytics::class, 'updateQuotation'])->name('update-quotation');

require __DIR__ . '/auth.php';

Route::resource('opciones', OpcionCotizadorController::class);
Route::post('/get-opciones', [OpcionCotizadorController::class, 'getOpcionesAjax'])->name('opciones.ajax');
Route::get('/opciones/show/{id}', [OpcionCotizadorController::class, 'show'])->name('opciones.show');
Route::resource('productos', ProductoCantidadController::class)->except(['index']);
//Route::post('/productos/ajax/{opcionId}', [ProductoCantidadController::class, 'getByOpcion'])->name('productos.ajax');
Route::post('/productos/ajax/{opcionId}', [ProductoCantidadController::class, 'getProductosAjax'])->name('productos.ajax');
