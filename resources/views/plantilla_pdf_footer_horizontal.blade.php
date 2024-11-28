<!DOCTYPE html>

<head>
  <style>
    .mi-tabla {
      border-collapse: collapse;
    }

    .mi-celda {
      font-size: 16px;
      height: 1in;
      border: 2px solid
        /* #006991 */
      ;
      /* Borde grueso en color especificado */
      text-align: center;
      /* Centrar contenido */
      vertical-align: middle;
    }

    .total {
      text-align: right;
      padding-right: 5px;
    }

    .titulos {
      background-color: #cacaca;
      font-weight: bold;
      font-size: 12px;
    }
  </style>
</head>


<table class="mi-tabla" style="width:100%">
  <tr style="height: 1cm;">
    <td style="width:100%:" class="titulos total" scope="row" colspan="3">
      TOTAL: $ {{$total_documento}}
    </td>

  </tr>
  <tr>
    <td style="width:33%; vertical-align: top;" class="mi-celda">Solicito</td>
    <td style="width:33%; vertical-align: top;" class="mi-celda">Jefe Inmediato</td>
    <td style="width:33%; vertical-align: top;" class="mi-celda">Director de Area</td>
  </tr>
</table>
<br>