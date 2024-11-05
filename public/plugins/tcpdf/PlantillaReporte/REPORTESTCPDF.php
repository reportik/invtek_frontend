<?php
/**
 * Created by PhpStorm.
 * User: dnlimon
 * Date: 18/02/2016
 * Time: 12:19 PM
 */

    //require_once('tcpdf.php');
    require_once __DIR__ . '/../../../../public/plugins/tcpdf/tcpdf.php';

    class REPORTESTCPDF extends TCPDF {

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
                '<table cellpadding="1" cellspacing="1" border="0" style="text-align:center;">
                        <tr>
                        <th rowspan="3" align="center" width="90" height="100"><img src="../public/img/logocorona.jpg"/></th>
                        <th rowspan="3" align="center" width="90" height="100"><img src="../public/img/logoros.jpg"/></th>
                        <th rowspan="3" align="justify" width="183" height="100">
                            Embutidos Corona S.A de C.V<br>
                            RFC: ECO7711301I4<br>
                            Luis Enrique Williams 865<br>
                            Zapopan, Jalisco, México CP45150<br>
                            Tel: 01 (33) 36367015 Fax (33) 36569749<br>
                            Expedido en: Zapopan, Jalisco<br>
                            www.embutidoscorona.com.mx<br>
                            embutidoscorona@embutidoscorona.com.mx
                        </th>
                        <td colspan="3" style="background-color:#d4d4d4" border="1" align="center" width="168" height="12"> <b>'.$this->tipoDocumento.'</b> </td>
                        </tr>
                        <tr>
                        <td colspan="3" align="center" border="1" width="168" height="12"> <b>'.trim($this->codigo).'</b> </td>
                        </tr>
                        <tr>
                        <td width="56" height="76"> <img src="../public/img/logocorona3.jpg" width="55" height="65" /></td>
                        <td width="56" height="76"> <img src="../public/img/logocorona2.jpg" width="55" height="65" /></td>
                        <td width="56" height="76"> <img src="../public/img/logoros2.jpg" width="55" height="65" /></td>
                        </tr>
                        </table>';
            $this->writeHTML($tbl, true, false, true, false, '');

        }

        // Page footer
        public function Footer() {

            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);
            // Page number
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

            $this->writeHTML($this->FechaImpresion, true, false, true, false, '');
        }
    }