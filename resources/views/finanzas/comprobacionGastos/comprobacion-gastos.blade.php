@extends('layouts/contentNavbarLayout')

@section('title', 'Comprobación Gastos')

@section('page-script')

<script src="{{ URL::asset('js/comprobacion-gastos.js?v=' . $version) }}"></script>

@endsection


@section('page-style')
@endsection

@section('content')
    <div class="row mb-12 g-6">
        <div class="card">
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" readonly
                                    value="{{ Auth::user()->name }}">
                            </div>
                        </div>
                        <div class="col-3">
                            <label for="ceco">Ceco</label>
                            <select data-live-search="true" class="form-control selectpicker" id="ceco" name="ceco">
                                <option value="">Selecciona una opción</option>
                                @foreach ($cecos as $item)
                                    <option value="{{ $item->CEN_ceco }}">{{ $item->CEN_descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label for="dias_habiles">Días Hábiles</label>
                                <input type="number" class="form-control" id="dias_habiles" name="dias_habiles"
                                    value="1">
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="sitio">Sitio</label>
                                <select class="form-control selectpicker" id="sitio" name="sitio">
                                    <option value="Toluca">Toluca</option>
                                    <option value="Quad">Quad</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn btn-primary mt-3" id="addRow">Agregar Gasto</button>
                <table class="table mt-3" id="tbl_cg">
                    <thead>
                        <tr>
                            <th>Partida</th>
                            <th>Eliminar</th>
                            <th>XML</th>
                            <th>PDF</th>
                            <th>Asistentes</th>
                            <th>Fecha de Gasto</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Filas dinámicas -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
