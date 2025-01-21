@extends('layouts/commonMaster' )

@php
/* Display elements */
$contentNavbar = true;
$containerNav = ($containerNav ?? 'container-xxl');
$isNavbar = ($isNavbar ?? true);
$isMenu = ($isMenu ?? true);
$isFlex = ($isFlex ?? false);
$isFooter = ($isFooter ?? true);

/* HTML Classes */
$navbarDetached = 'navbar-detached';

/* Content classes */
$container = ($container ?? 'container-xxl');

@endphp

@section('page-style')

<link rel="stylesheet"
  href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/build/assets/theme-default-dark-6ufYpaZF.css"
  class="template-customizer-theme-css" />
<!-- Vendor Styles -->
<link rel="preload" as="style"
  href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/build/assets/bs-stepper-pfNhtc-M.css" />
<link rel="stylesheet"
  href="https://demos.themeselection.com/materio-bootstrap-html-laravel-admin-template/demo/build/assets/bs-stepper-pfNhtc-M.css"
  class="" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {

const stepperElement = document.querySelector("#wizard-property-listing");
const stepper = new Stepper(stepperElement);

// Manejar el botón Next
const nextButtons = document.querySelectorAll(".btn-next");
nextButtons.forEach((button) => {
button.addEventListener("click", () => {
stepper.next(); // Ir al siguiente paso
});
});

// Manejar el botón Previous
const prevButtons = document.querySelectorAll(".btn-prev");
prevButtons.forEach((button) => {
button.addEventListener("click", () => {
stepper.previous(); // Volver al paso anterior
});
});
});
</script>
<style>
  .bs-stepper-title {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    max-width: 200px;
    /* Ajusta según sea necesario */
  }

  .line {

    max-height: 1px;
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
        <div class="{{$container}} d-flex align-items-stretch flex-grow-1 p-0">
          @else
          <div class="{{$container}} flex-grow-1 container-p-y">
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