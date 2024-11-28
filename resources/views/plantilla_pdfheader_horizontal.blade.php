<!DOCTYPE html>

<head>
  <meta charset="utf-8">
  <title>{{$titulo}}</title>
  <style>
    /*
	Generic Styling, for Desktops/Laptops
	*/
    img {
      display: block;
      width: 130px;
      margin: 0.6rem 0 0.1rem 1rem;
      position: absolute;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-family: arial;
    }

    h3 {
      font-family: 'Helvetica';
      margin-bottom: 2px;
      margin-top: 3px
    }

    h5 {
      margin-top: 2px
    }
  </style>
  <script>
    function subst() {
          var vars = {};
          var query_strings_from_url = document.location.search.substring(1).split('&');
          for (var query_string in query_strings_from_url) {
              if (query_strings_from_url.hasOwnProperty(query_string)) {
                  var temp_var = query_strings_from_url[query_string].split('=', 2);
                  vars[temp_var[0]] = decodeURI(temp_var[1]);
              }
          }
          var css_selector_classes = ['page', 'frompage', 'topage', 'webpage', 'section', 'subsection', 'date', 'isodate', 'time', 'title', 'doctitle', 'sitepage', 'sitepages'];
          for (var css_class in css_selector_classes) {
              if (css_selector_classes.hasOwnProperty(css_class)) {
                  var element = document.getElementsByClassName(css_selector_classes[css_class]);
                  for (var j = 0; j < element.length; ++j) {
                      element[j].textContent = vars[css_selector_classes[css_class]];
                  }
              }
          }
      }
  </script>
</head>

<div id="header">
  <table style="">
    <tr>


      <td class="section"></td>

    </tr>
  </table>
  <br>
  <p style="text-align:right">
    {{$fechaImpresion}}
  </p>
  <img class="logo" src='{{ asset("logo.png") }}'>
  <table style="padding-bottom:10px">
    <tr style="background-color: white">
      <td colspan="2" align="center" bgcolor="#fff">
        <b>{{env('EMPRESA_NAME')}}</b><br>
        <h3>{{$titulo}}</h3>
        <h4>{{ Str::upper($subtitulo)}}</h4>
      </td>
    </tr>
    <tr style="background-color: white">
      <td colspan="2" align='{{$align_subt2}}' bgcolor="#fff">
        <h4>{{$subtitulo2}}</h4>
      </td>
    </tr>
  </table>


  <p>

</div>