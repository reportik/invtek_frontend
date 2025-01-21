@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - KENER')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
@vite('resources/assets/js/dashboards-analytics.js')
@endsection
<style>
  body,
  html {
    height: 100%;
    margin: 0;
  }

  .bg-container {
    position: relative;
    width: 100%;
    height: 100vh;
    /* O ajusta
  según necesites */
    overflow: hidden;
  }

  .bg-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height:
      100%;
    object-fit: cover;
    /* Asegura que la imagen cubra todo el contenedor */
    opacity: 0.7;
    /* Ajusta la opacidad según
  prefieras */
  }

  .content {
    position: relative;
    z-index: 1;
    font-style: bold;
    text-align: center;
    padding: 20px
  }
</style>
@section('content')
<div class="bg-container">

  <img src="{{ asset('fondo_mision.png') }}" alt="Fondo Misión">
  <div class="content">

  </div>
</div>
@endsection