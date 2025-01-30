<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@vite(['resources/assets/vendor/fonts/remixicon/remixicon.scss'])
<!-- Core CSS -->
@vite([
'resources/assets/vendor/scss/core.scss',
'resources/assets/vendor/scss/theme-default.scss',
'resources/assets/css/demo.css'
])
@vite('resources/css/app.css')

<!-- Vendor Styles -->
@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.scss'])
@yield('vendor-style')

<!-- Page Styles -->
@yield('page-style')

<style>
  .layout-content-navbar {
    .layout-page {
      display: flex;
      flex: 1 1 auto;
      align-items: stretch;
      padding: 0;
      transition: margin-left 0.3s ease;

      .layout-without-menu & {
        padding-right: 0 !important;
        padding-left: 0 !important;
      }


      &.layout-page-fullwidth {
        margin-left: 0;
        padding-left: 0px;
        width: 100%;

        /* Asegúrate de que ocupe todo el ancho */
      }
    }

    .content-wrapper {
      width: 100%;
      transition: padding 0.3s ease;
      /* Añadir una transición suave para el padding */
    }

    .layout-menu-collapsed .layout-page .layout-page {
      padding-left: 0rem;
    }

    .container-xxl {
      transition: padding 0.3s ease;
      /* Añadir una transición suave para el padding */

      .layout-page-fullwidth & {
        padding-left: 0;
        padding-right: 0;
      }


    }

    input:focus,
    textarea:focus,
    select:focus {
      border-color: #006991;
      /* Cambia este color al que prefieras */
      /*box-shadow: 0 0 5px rgba(236, 232, 231, 0.5);
       Opcional: añade un efecto de sombra */
      /*outline: none;
       Opcional: elimina el borde de enfoque predeterminado */
    }

    .btn-primary:hover {
      color: #fff !important;
      background-color: #74BC1B !important;
      border-color: #74BC1B !important;
    }
  }



  @media (min-width: 1200px) {
    .layout-menu-fixed .layout-page {
      padding-left: 16.25rem;
      transition: padding-left 0.3s ease;
    }

    .layout-menu-fixed.layout-menu-collapsed .layout-page {
      padding-left: 80px;
    }

    .layout-page-fullwidth .content-wrapper,
    .layout-page-fullwidth .container-xxl {
      padding-left: 0;
      padding-right: 0;
    }
  }
</style>