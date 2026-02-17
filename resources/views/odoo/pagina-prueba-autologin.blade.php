@extends('layouts/contentNavbarLayoutOnly')

@section('title', 'Prueba SSO - Ir a Odoo')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Prueba: enlace a Odoo (autologin)</h5>
        </div>
        <div class="card-body">
          @if (session('error'))
            <div class="alert alert-danger" role="alert">
              {{ session('error') }}
            </div>
          @endif
          <p class="text-muted">
            Si ya iniciaste sesión en este sitio, puedes usar el enlace de abajo para entrar a Odoo
            <strong>sin volver a escribir usuario ni contraseña</strong>. Serás redirigido al portal (mis pedidos).
          </p>
          <div class="d-grid gap-2">
            <a href="{{ route('odoo.autologin.redirect', ['redirect' => '/my/home']) }}"
               class="btn btn-primary btn-lg">
              Ir a Mis Cotizaciones
            </a>
          </div>
          <p class="small text-muted mt-3 mb-0">
            Usuario actual: <strong>{{ Auth::user()->email }}</strong>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
