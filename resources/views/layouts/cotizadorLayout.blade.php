@extends('layouts/contentNavbarLayoutOnly')

@section('title')
@yield('title')
@endsection

@section('content')
<div style="display: flex; align-items: center; justify-content: center; margin: 20px 0;">
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
    <h2
        style="color: #59981A; font-family: 'Arial', sans-serif; f font-weight: bold; text-align: center; letter-spacing: 1px;">
        Tipo de producto
    </h2>
    <hr style="flex: 1; border: none; border-top: 4px solid #59981A; margin: 0 10px;">
</div>
@yield('content')
@endsection

@section('page-script')
@yield('page-script')
@endsection