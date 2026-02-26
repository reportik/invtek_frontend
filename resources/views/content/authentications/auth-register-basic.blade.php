@extends('layouts/blankLayout')

@section('title', 'Register Basic - Pages')

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-auth.scss'
])
@endsection


@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">

      <!-- Register Card -->
      <div class="card p-7">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{url('/')}}" class="app-brand-link gap-3">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-1">
          <h4 class="mb-1">Registrate Aquí 🚀</h4>
          <p class="mb-5">Nos da gusto conocerte!</p>
          @if(session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
          @endif
          <!-- handle errors  -->
          @error('username')
          <div class="alert alert-danger">
            {{ $message }}
          </div>
          @enderror
          @error('email')
          <div class="alert alert-danger">
            {{ $message }}
          </div>
          @enderror
          @error('password')
          <div class="alert alert-danger">
            {{ $message }}
          </div>
          @enderror
          <form id="formAuthentication" class="mb-5" action="{{route('register')}}" method="POST" autocomplete="off">
            @csrf
            <div class="form-floating form-floating-outline mb-5">
              <input type="text" class="form-control" id="username" name="username" placeholder="Nombre" autofocus autocomplete="">
              <label for="username">Nombre</label>
            </div>
            <div class="form-floating form-floating-outline mb-5">
              <input type="text" class="form-control" id="email" name="email" placeholder="Correo">
              <label for="email">Correo</label>
            </div>
            <div class="mb-5 form-password-toggle">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <label for="password">Contraseña</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="ri-eye-off-line ri-20px"></i></span>
              </div>
            </div>

            <div class="mb-5 py-2">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms">
                <label class="form-check-label" for="terms-conditions">

                  <span>Acepto los</span>
                  <a href="javascript:void(0);">terminos y condiciones</a>
                </label>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100 mb-5">
              Registrate
            </button>
          </form>

          <p class="text-center mb-5">
            <span>Ya tienes una cuenta?</span>
            <a href="{{url('auth/login-basic')}}">
              <span>Inicia aquí</span>
            </a>
          </p>
        </div>
      </div>
      <!-- Register Card -->
      <img src="{{asset('assets/img/illustrations/tree-3.png')}}" alt="auth-tree"
        class="authentication-image-object-left d-none d-lg-block">
      <img src="{{asset('assets/img/illustrations/auth-basic-mask-light.png')}}"
        class="authentication-image d-none d-lg-block" height="172" alt="triangle-bg">
      <img src="{{asset('assets/img/illustrations/tree.png')}}" alt="auth-tree"
        class="authentication-image-object-right d-none d-lg-block">
    </div>
  </div>
</div>
@endsection