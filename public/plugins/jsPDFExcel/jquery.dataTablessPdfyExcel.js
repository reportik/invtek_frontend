//var tblTr;
function reiniciarTabla(){
    $('#tercerTabla').remove();
//eliminamos la tercera tabla del input para que pueda generarce otra
  /*  var nomImput =$('#inputContieneTabla').html();
     alert($('#tercerTabla').html());
    if(nomImput!=null){
        // alert("ya se genero un input");
        alert("estas eliminando la anterior");
        $('#tercerTabla').remove();
    }
*/


}

//en esta funcion creamos una tabla para mandarla como un input al form de reportes y generar el pdf segun datos dela tabla
function prePdf(){
    reiniciarTabla();

    //obtenemos el valor que intenta buscar
    var valorAbuscar = $('input[type=search]').val();
    //obtenemos el encabezado de la tabla
   var tblTr= document.getElementById("data-tableExcel").tHead.innerHTML;




    //cremos la cabecera y cuerpo de la tabla html tercerTabla
    var head = document.getElementsByTagName("head")[0];
    var body = document.getElementsByTagName("body")[0];


    //le pasamos el encabezado de la segunda tabla a la 3er tabla
    var tblHead = document.createElement("thead");
    tblHead.innerHTML=tblTr;


    //creamos el cuerpo de la tabla
    var tblBody = document.createElement("tbody");
    //creamos la tabla ponemos el id y la ocultamos
    var tablaAllenar   = document.createElement("table");
    tablaAllenar.id="tercerTabla";
    tablaAllenar.class='table';
    tablaAllenar.style.visibility="hidden";

    //   --- pasamos a la tabla el encabezado y elcuerpo
    tablaAllenar.appendChild(tblHead);
    head.appendChild(tablaAllenar);

    tablaAllenar.appendChild(tblBody);
    body.appendChild(tablaAllenar);
//   ---
    //creamos una variable para condicionar; si hay mas de una coincidencia en la misma fila pero
    //diferente celda solo se dibuje una vez la fila
    var filaDiferente=-1;

    //obtenemos la segunda tabla para comenzar lectura de datos
    var $objTabla = $('#data-tableExcel');
    //buscamos el cuerpo de la tabla y leemos las filas
    $objTabla.find('tbody tr').each(function(Fila,objFila){
        var hilera = document.createElement("tr");

        //obtenemos la cantidad de celdas segun las filas
        var $objCelda=$(objFila).find('td');

        //recorremos todas las celdas de la fila actual
        $objCelda.each(function(Celda,objCeldaFila){

            //alert(Fila);
            //comparamos nuestra busqueda con cada una de las celdas
            if(($(objCeldaFila).text().toUpperCase()).search(valorAbuscar.toUpperCase())!=-1){

                while(filaDiferente!=Fila){

                    //console.log("esta es la Fila : "+ Fila);
                    for(var xx=1; xx<$objCelda.length; xx++ ){


                        var celda = document.createElement("td");
                        tblBody.appendChild(hilera);
                        hilera.appendChild(celda);
                        //celda.appendChild(textoCelda);
                        celda.innerHTML = '<font color="black">'+$($objCelda[xx]).text()+'</font>';

                        //if -> para agregar imagen dentro de la celda
                       /* if(Fila==3 && xx == 2){
                            celda.innerHTML = "<img src='./img/TransportesUnidadesFotografia/cocodrilo.JPG' WIDTH=80 HEIGHT=50/>"+'<font color="black">'+$($objCelda[xx]).text()+'</font>';
                        }  */

                        //console.log($($objCelda[xx]).text());

                    }

                    filaDiferente=Fila;
                }


                //alert($(objCeldaFila).text());
            }


        });

        // }

    });



    var TercerTabla =$( "#tercerTabla" ).html();

    var inputHtmlTabla = document.createElement('input');
    inputHtmlTabla.type = 'hidden';
    inputHtmlTabla.name = "nuevoinput";
    inputHtmlTabla.id="inputContieneTabla";
    inputHtmlTabla.value =TercerTabla;
    document.getElementById('form-reporte').appendChild( inputHtmlTabla);

    //console.log(tablaAllenar);




    //con este imput indicamos que se mande a generar un PDF
    var tiporeporte = document.createElement('input');
    tiporeporte.type='hidden';
    tiporeporte.name='tipoReporte';
    tiporeporte.value="pdf";
    document.getElementById('form-reporte').appendChild(tiporeporte);

}


//en esta funcion creamos una tabla para mandarla como un input al form de reportes y generar el Excel segun datos dela tabla
function preExcel(){

    reiniciarTabla();


    var valorAbuscar = $('input[type=search]').val();
   var tblTr= document.getElementById("data-tableExcel").tHead.innerHTML;



    tercerTablaCreada=true;
    //alert(tblTr);
    var head = document.getElementsByTagName("head")[0];
    var body = document.getElementsByTagName("body")[0];



    var tblHead = document.createElement("thead");
    tblHead.innerHTML=tblTr;



    var tblBody = document.createElement("tbody");
    var tablaAllenar   = document.createElement("table");
    tablaAllenar.id="tercerTabla";
    tablaAllenar.style.visibility="hidden";


    tablaAllenar.appendChild(tblHead);
    head.appendChild(tablaAllenar);

    tablaAllenar.appendChild(tblBody);
    body.appendChild(tablaAllenar);


    var filaDiferente=-1;


    var $objTabla = $('#data-tableExcel');
    //buscamos el cuerpo de la tabla
    $objTabla.find('tbody tr').each(function(Fila,objFila){
        var hilera = document.createElement("tr");

        //obtenemos la cantidad de celdas segun las filas ;)
        var $objCelda=$(objFila).find('td');

        //recorremos todas las celdas de la fila actual
        $objCelda.each(function(Celda,objCeldaFila){

            //comparamos nuestra busqueda con cada una de las celdas
            if(($(objCeldaFila).text().toUpperCase()).search(valorAbuscar.toUpperCase())!=-1){

                while(filaDiferente!=Fila){


                    for(var xx=1; xx<$objCelda.length; xx++ ){


                        var celda = document.createElement("td");
                        tblBody.appendChild(hilera);
                        hilera.appendChild(celda);
                        //celda.appendChild(textoCelda);
                        celda.innerHTML = '<font color="black">'+$($objCelda[xx]).text()+'</font>';
                        // console.log($($objCelda[xx]).text());
                        // if para agregar imagen dentro de la celda
                       /* if(Fila==3 && xx == 2){
                            celda.innerHTML = "<img src='./img/TransportesUnidadesFotografia/cocodrilo.JPG' WIDTH=80 HEIGHT=50/>"+'<font color="black">'+$($objCelda[xx]).text()+'</font>';
                        } */

                    }

                    filaDiferente=Fila;
                }


                //alert($(objCeldaFila).text());
            }


        });

        // }

    });



    var TercerTabla =$( "#tercerTabla" ).html();

    var inputHtmlTabla = document.createElement('input');
    inputHtmlTabla.type = 'hidden';
    inputHtmlTabla.name = "nuevoinput";
    inputHtmlTabla.id="inputContieneTabla";
    inputHtmlTabla.value =TercerTabla;
    document.getElementById('form-reporte').appendChild( inputHtmlTabla);

    // console.log(tablaAllenar);

    //indicamos que se genere un excel

    var tiporeporte = document.createElement('input');
    tiporeporte.type='hidden';
    tiporeporte.name='tipoReporte';
    tiporeporte.value="excel";
    document.getElementById('form-reporte').appendChild(tiporeporte);




}


/*

//funcion busqueda avanzada del modal
function BuscarAvanzada(){
    //eliminamos la tercer tabla para que cuando se genere se mande llamar la nueva tabla "`_'"
    $('#tercerTabla').remove();
    // alert("quieres buscar .-.");

    var formulario = $("#busquedaAvanzada").serializeArray();
    // console.log(formulario);
    var url = document.location.href;
    $(".modal-backdrop").css("z-index");
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();


    $.ajax({
        type: "GET",

        url: "Transportes/TransportesUnidades-paraBusqueda",
        data: formulario,

        //dataType:'html',

        success:function(data){
            console.log(formulario[0]['value']);

            $("#ajax-content").html(data);



        }
    });


}   */