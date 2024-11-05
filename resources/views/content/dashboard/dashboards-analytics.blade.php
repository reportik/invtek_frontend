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

@section('content')
    <div class="row gy-6">
        <img src="{{ asset('fondo_mision.png') }}" alt="">
    </div>
@endsection
