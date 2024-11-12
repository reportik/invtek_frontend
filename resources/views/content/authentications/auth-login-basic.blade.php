@extends('layouts/blankLayout')

@section('title', 'Login - Pages')

@section('page-style')
    @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
    <style>
      body {
          background-image: url('https://www.kener.com.mx/prueba/wp-content/uploads/revslider/mg-rs-1/04.jpg');
          background-size: cover; /* Ajusta la imagen para cubrir toda la pantalla */
          background-repeat: no-repeat; /* Evita que la imagen se repita */
          background-position: center; /* Centra la imagen */
      }
    </style>
@endsection

@section('content')
    <div class="position-relative">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6 mx-4">

                <!-- Login -->
                <div class="card p-7">
                    <!-- Logo -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <div class="app-brand justify-content-center mt-5">
                        <a href="{{ url('/') }}" class="app-brand-link gap-3">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                            <span
                                class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->

                    <div class="card-body mt-1">
                        <h4 class="mb-1">Bienvenid@ a {{ config('variables.templateName') }}! 👋🏻</h4>
                        <p class="mb-5">Ingresa a tu cuenta</p>

                        <form id="formAuthentication" class="mb-5" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-floating form-floating-outline mb-5">
                                <input type="text" class="form-control" id="email" name="email"
                                    :value="old('email')" required autofocus autocomplete="username">
                                <label for="email">Email</label>
                            </div>
                            <div class="mb-5">
                                <div class="form-password-toggle">
                                    <div class="input-group input-group-merge">
                                        <div class="form-floating form-floating-outline">
                                            <input type="password" id="password" class="form-control" name="password"
                                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                                aria-describedby="password" />
                                            <label for="password">Password</label>
                                        </div>
                                        <span class="input-group-text cursor-pointer"><i
                                                class="ri-eye-off-line ri-20px"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div style="display: none !important" class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="remember-me">
                                    <label class="form-check-label" for="remember-me">
                                        Recordarme
                                    </label>
                                </div> -
                                <a href="{{ url('auth/forgot-password-basic') }}" class="float-end mb-1">
                                    <span>Olvidé mi Password</span>
                                </a>
                            </div>
                            <div class="mb-5">
                                <button class="btn btn-primary d-grid w-100" type="submit">login</button>
                            </div>
                        </form>

                        <p style="display:none" class="text-center mb-5">
                            <span>¿Nuevo en Kener?</span>
                            <a href="{{ url('auth/register-basic') }}">
                                <span>Crea una Cuenta</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- /Login -->
                <img src="{{ asset('assets/img/illustrations/auth-basic-mask-light.png') }}"
                    class="authentication-image d-none d-lg-block" height="172" alt="triangle-bg">
                {{--  <img src="{{ asset('assets/img/illustrations/tree-3.png') }}" alt="auth-tree"
                    class="authentication-image-object-left d-none d-lg-block">

                <img src="{{ asset('assets/img/illustrations/tree.png') }}" alt="auth-tree"
                    class="authentication-image-object-right d-none d-lg-block"> --}}
            </div>
        </div>
    </div>
@endsection
