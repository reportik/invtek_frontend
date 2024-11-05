@extends('layouts/contentNavbarLayout')

@section('title', 'Comprobación Gastos')

@section('page-script')

    <script src="{{ URL::asset('plugins/jquery/jquery-1.9.1.min.js') }}"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script script src="{{ URL::asset('plugins/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ URL::asset('js/comprobacion-gastos.js?v=' . $version) }}"></script>
@endsection


@section('page-style')
    <link href="{{ URL::asset('plugins/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet">
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
                            <select class="form-control selectpicker" id="ceco" name="ceco">
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
                <button type="button" class="btn btn-primary mt-3" id="addRow">Agregar Fila</button>
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>Acciones</th>
                            <th>Archivos</th>
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

    <script>
        document.getElementById('addRow').addEventListener('click', function() {
            let tableBody = document.getElementById('tableBody');
            let row = document.createElement('tr');

            row.innerHTML = `
                <td>
                    <button type="button" class="btn btn-danger btn-sm">Eliminar</button>
                    <button type="button" class="btn btn-secondary btn-sm">Editar</button>
                </td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm">Cargar XML</button>
                    <button type="button" class="btn btn-primary btn-sm">Cargar PDF</button>
                </td>
                <td><input type="number" class="form-control" name="asistentes[]"></td>
                <td><input type="date" class="form-control" name="fecha_gasto[]"></td>
                <td><input type="text" class="form-control" name="descripcion[]"></td>
                <td><input type="text" class="form-control" name="monto[]"></td>
            `;

            tableBody.appendChild(row);
        });
    </script>
@endsection
