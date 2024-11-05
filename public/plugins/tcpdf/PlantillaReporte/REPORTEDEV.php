<?php
/**
 * Created by PhpStorm.
 * User: dnlimon
 * Date: 16/10/2016
 * Time: 03:26 PM
 */

    //require_once('tcpdf.php');
    require_once __DIR__ . '/../../../../public/plugins/tcpdf/tcpdf.php';

    class REPORTEDEV extends TCPDF {

        public $codigo;
        public $tipoDocumento;
        public $FechaImpresion;
        public $Fecha;

        public function setCodigo($codigo){
            $this->codigo = $codigo;
        }

        public function setTipoDocumento($tipoDocumento){
            $this->tipoDocumento = $tipoDocumento;
        }

        public function setFechaImpresion($Fechaimpresion){

            $this->FechaImpresion = $Fechaimpresion;
        }


        //Page header
        public function Header() {

            $this->SetFont('times', '', 8, '', 'false');
            // Datos Cliente
            $tbl =
                '<table cellpadding="1" cellspacing="1" border="0" style="text-align:center; vertical-align: middle">

                    <tr>
                        <td rowspan="3" align="center" width="90" height="80"><img src="../public/img/logocorona.jpg"/></td>
                        <td width="320" align="center">

                            <table border="0" style="text-align:center;">

                                <tr><td></td></tr>

                                <tr>
                                    <td style="font-size: 14px"> <b>Embutidos Corona S.A de C.V</b> </td>
                                </tr>

                                <tr><td></td></tr>

                                <tr>
                                    <td width="95"> </td>
                                    <td width="121" height="20" border="1" bgcolor="#dcdcdc"> <b>DEVOLUCION DE PRODUCTO</b> </td>
                                    <td width="95" align=""> </td>
                                </tr>

                                <tr>
                                    <td width="95"> </td>
                                    <td width="121" height="20" border="1" style="font-size: 10px"> <b>'.trim($this->codigo).'</b> </td>
                                    <td width="95" align=""> </td>
                                </tr>
                            </table>

                        </td>
                        <td width="90" height="80"> <img src="../public/img/logoros.jpg" /></td>
                    </tr>

                </table>';
            $this->writeHTML($tbl, true, false, true, false, '');

        }

        // Page footer
        public function Footer() {

            $this->SetFont('helvetica', 8);

            $tbl =
                '<table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <th> <b>Fecha de impresión:</b> '. $this->FechaImpresion .'</th>
                            <th align="rigth">Pagina '. $this->getAliasNumPage().' de '.$this->getAliasNbPages().'</th>
                        </tr>

                    </table>';

            $this->writeHTML($tbl, true, false, true, false, '');
            $this->Rotate(0, 0, 0);
        }
    }