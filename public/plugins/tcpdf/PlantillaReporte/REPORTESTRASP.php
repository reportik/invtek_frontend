<?php
/**
 * Created by PhpStorm.
 * User: dnlimon
 * Date: 18/02/2016
 * Time: 12:19 PM
 */

    //require_once('tcpdf.php');
    require_once __DIR__ . '/../../../../public/plugins/tcpdf/tcpdf.php';

    class REPORTESTRASP extends TCPDF {

        public $FechaImpresion;
        public $Vendedor;

        public function setFechaImpresion($Fechaimpresion){

            $this->FechaImpresion = $Fechaimpresion;
        }


        public function Vendedor($Vendedor){

            $this->Vendedor = $Vendedor;
        }

        //Page header
        public function Header() {

            $this->SetFont('times', '', 8, '', 'false');

            // Datos Cliente
            $tbl =
                '<table cellpadding="1" cellspacing="1" border="0" style="text-align:center;">
                    <!--<tr style="font-size: 140%"> <th> <b> Matriz </b> </th> </tr>-->
                    <tr style="font-size: 160%"> <th> <b>EMBUTIDOS CORONA S.A DE C.V </b> </th> </tr>
                    <tr style="font-size: 130%"> <th> <b>TRASPASO DE ALMACÉN </b> </th> </tr>
                </table>';

            $this->writeHTMLCell(180, '', '', '', $tbl, 0, 0, 0, true, 'J', true);
        }

        // Page footer
        public function Footer() {



            $this->SetFont('helvetica', 8);
            $this->SetY(-30);

            $tbl =
                '                           
                <table cellpadding="3" cellspacing="1" border="0">

                    <tr>
                        <td width="150"> Placas: ________________________ </td>
                        <td width="5">  </td>
                        <td width="170"> Temperatura: ________________________ </td>
                        <td width="5">  </td>
                        <td width="170"> Limpieza: ____________________________ </td>
                    </tr>
    
                </table>';


            $this->writeHTML($tbl, true, false, true, false, '');

            //-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_

            $this->SetFont('helvetica', 8);
            $this->SetY(-20);

            $tbl =
                '                           
                <table cellpadding="5" cellspacing="1" border="0">

                    <tr>
                        <td align="center" width="240">
                            Recibió: __________________________________________
                            ' . $this->Vendedor . '
                        </td>
                        <td width="30">  </td>
                        <td align="center" width="240"> Entregó: __________________________________________ </td>
                    </tr>
    
                    <tr>
                        <th> <b>Fecha de impresión:</b> '. $this->FechaImpresion .'</th>
                        <td> </td>
                        <th align="rigth" width="240">Pagina '. $this->getAliasNumPage().' de '.$this->getAliasNbPages().'</th>
                    </tr>

                </table>';


            $this->writeHTML($tbl, true, false, true, false, '');
        }
    }