<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">

  <style>
    /*
                Generic Styling, for Desktops/Laptops
                */
    table {
      width: 100%;
      border-collapse: collapse;
      font-family: arial;
    }

    @page {
      margin-left: 0.5cm;
      margin-right: 0.5cm;
    }

    th {
      color: white;
      font-weight: bold;
      color: white;
      font-family: 'Helvetica';
      font-size: 65%;
      background-color: #474747;
    }

    td {
      font-family: 'Helvetica';
      font-size: 60%;
    }

    img {
      display: block;
      width: 130px;
      margin: 0.6rem 0 0.1rem 1rem;
      position: absolute;
    }

    h3 {
      font-family: 'Helvetica';
    }

    b {
      font-size: 100%;
    }

    #header {
      position: fixed;
      margin-top: 2px;
    }

    #content {
      position: relative;
      top: 14%
    }

    table,
    th,
    td {
      text-align: center;
      border: 1.5px solid black;
    }

    tr:nth-child(even) {
      background-color: #f2f2f2
    }

    .total {
      text-align: right;
      padding-right: 4px;
    }

    .titulos {
      background-color: #cacaca;
      font-weight: bold;
      font-size: 10px;
    }
  </style>
</head>

<body>

  <!--Cuerpo o datos de la tabla-->
  <div id="content">
    @if(count($data)>0)
    <p>Gastos comprobables para <b>{{Str::upper($sitio)}}</b> de {{$dias_habiles}} día(s) hábil(es)</p>
    <div class="row">
      <?php
                    $index = 0;
                    $A = 0;
                    $B = 0;
                ?>

      <table class="table tabla-pdf" style="table-layout:fixed;">
        <tbody>
          <tr style="height: 1cm;">
            <td style="width:15%" class="titulos" scope="row">
              CONCEPTO
            </td>
            <td style="width:70%" class="titulos" scope="row">
              DESCRIPCION
            </td>
            <td style="width:15%" class="titulos" scope="row">
              MONTO
            </td>

          </tr>
          @foreach ($data as $rep)

          <tr>
            <td style="width:15%" class="nombres" scope="row">
              {{Str::upper( $rep->GAD_concepto)}}
            </td>
            <td style="width:70%; text-align:left" class="nombres" scope="row">
              {{Str::upper($rep->GAD_descripcion)}}
            </td>
            <td style="width:15%; text-align:right; padding-right:5px" class="zrk-gris-claro" scope="row">
              $ {{number_format($rep->monto,'2', '.',',')}}
              <?php $A += $rep->monto; ?>
            </td>
          </tr>

          <?php
                            $index++;
                        ?>
          @endforeach
        </tbody>
      </table>

    </div>
    @endif
  </div>

</body>

</html>