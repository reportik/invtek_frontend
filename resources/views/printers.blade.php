@extends('layouts.contentNavbarLayout')

@section('content')
<table border="3" class="table table-striped table-bordered">
  <thead>
    <tr>
      <th>IP de la Impresora</th>
      <th>Número de Serie</th>
      <th>Impresiones en Negro</th>
      <th>Impresiones en Color</th>
      <th>Total de Impresiones</th>
      <th>Cian</th>
      <th>Magenta</th>
      <th>Amarillo</th>
      <th>Negro</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($printerData as $printer)
    <tr>
      <td>{{ $printer['url'] }}</td>
      <td>{{ $printer['serialNumber'] }}</td>
      <td>{{ $printer['blackPrints'] }}</td>
      <td>{{ $printer['colorPrints'] }}</td>
      <td>{{ $printer['totalPrints'] }}</td>
      <td>{{ isset($printer['percentages'][0]) ? $printer['percentages'][0] . '%' : 'N/A' }}</td>
      <td>{{ isset($printer['percentages'][1]) ? $printer['percentages'][1] . '%' : 'N/A' }}</td>
      <td>{{ isset($printer['percentages'][2]) ? $printer['percentages'][2] . '%' : 'N/A' }}</td>
      <td>{{ isset($printer['percentages'][3]) ? $printer['percentages'][3] . '%' : 'N/A' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection