@extends('layouts/commonMaster' )

@php
/* Display elements */
$contentNavbar = true;
$containerNav = ($containerNav ?? 'container-xxl');
$isNavbar = (true);
$navbarFull = (true);
$isMenu = ($isMenu ?? true);
$isFlex = ($isFlex ?? false);
$isFooter = ($isFooter ?? true);

/* HTML Classes */
$navbarDetached = 'navbar-detached';

/* Content classes */
$container = ($container ?? 'container-xxl');

@endphp

@section('page-style')

{{--
<link rel="stylesheet"
  href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/build/assets/theme-default-dark-6ufYpaZF.css"
  class="template-customizer-theme-css" /> --}}


<!-- Vendor Styles -->
<link rel="preload" as="style"
  href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/build/assets/bs-stepper-pfNhtc-M.css" />
<link rel="stylesheet"
  href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/build/assets/bs-stepper-pfNhtc-M.css"
  class="" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>


<style>
  /* Importa la fuente "Cantora One" desde Google Fonts */
  @import url('https://fonts.googleapis.com/css2?family=Cantora+One&display=swap');

  button.nav-link.active {
    background-color: #59981A !important;
    color: white !important;

  }

  .bs-stepper-title {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 200px;
    /* Ajusta según sea necesario */
  }

  .bs-title {
    color: #433c50;
    font-weight: 500;
    font-size: 1.1rem;
  }


  .line {

    max-height: 1px;
  }

  .container-p-y:not([class^=pt-]):not([class*=" pt-"]) {
    padding-top: 0rem !important;
  }

  .responsive-logo {
    height: 100px;
    margin-bottom: -100px;
  }

  /* agregar para tablets*/
  @media (max-width: 1024px) {
    .responsive-logo {
      height: 15vh;
      /* o usa un tamaño fijo como 80px si prefieres */
      margin-bottom: 80px;
    }
  }

  @media (max-width: 768px) {

    /*  */
    .responsive-logo {
      height: 8vh;
      /* o usa un tamaño fijo como 80px si prefieres */
      margin-bottom: 20px;
    }

    .btn-full-width {
      width: 100%;
    }
  }

  .card {
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s;
  }

  .card:hover {
    transform: scale(1.02);
  }

  /* Estilo para la clase .titulo */
  .titulo {
    color: #59981A;
    font-family: 'Cantora One', sans-serif;
    /* Asegúrate de incluir una fuente de respaldo (fallback) */
  }

  [type='checkbox']:checked,
  [type='radio']:checked {
    background-color: #59981A !important;
    border-color: #59981A !important;
  }

  [type='checkbox']:focus,
  [type='radio']:focus {
    --tw-ring-color: #59981A !important;
  }

  .subtitulo {
    color: #433c50;
    font-weight: 500;

  }
</style>
<!-- beautify ignore:start -->
      <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
      <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
@endsection

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar layout-without-menu">
  <div class="layout-container">

    <!-- Layout page -->
    <div class="layout-page">
      <!-- BEGIN: Navbar-->
      @if ($isNavbar)
      @include('layouts/sections/navbar/navbar')
      @endif
      <!-- END: Navbar-->
      <!-- Content wrapper -->
      <div class="content-wrapper">
        <!-- Content -->
        @if ($isFlex)
        <div style="padding-top:0px !important" class="{{$container}} d-flex align-items-stretch flex-grow-1 p-0">
          @else
          <div style="padding-top:0px !important;" class="{{$container}} flex-grow-1 container-p-y">
            @endif
            @yield('content')
          </div>
          <!-- / Content -->

          <!-- Footer -->
          @include('layouts/sections/footer/footer')

          <!-- / Footer -->
          <div class="content-backdrop fade"></div>
        </div>
        <!--/ Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    @if ($isMenu)
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
  @endsection