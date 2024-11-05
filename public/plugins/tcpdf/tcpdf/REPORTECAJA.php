<?php
/**
 * Created by PhpStorm.
 * User: dnlimon
 * Date: 18/02/2016
 * Time: 12:19 PM
 */

//require_once('tcpdf.php');
require_once __DIR__ . '/../../../../public/plugins/tcpdf/tcpdf/tcpdf.php';

class REPORTECAJA extends TCPDF {

    public $nombreReporte;
    //public $rangoFechaReporte;
    public $fechaImpresion;

    public $noCaja;
    public $Usuario;
    public $Fecha;

    public function setNombreReporte($nombreReporte){
        $this->nombreReporte = $nombreReporte;
    }

    /*public function setRangoFechadelReporte($rangoFechaReporte){
        $this->rangoFechaReporte=$rangoFechaReporte;
    }*/

    public function setFechaImpresion($fechaImpresion){
        $this->fechaImpresion=$fechaImpresion;
    }

    public function setDatosCaja($noCaja, $Usuario, $Fecha){

        $this->noCaja = $noCaja;
        $this->Usuario = $Usuario;
        $this->Fecha = $Fecha;
    }

    //Page header
    public function Header() {
        // Logo
        // $image_file = '';//K_PATH_IMAGES.'logo_example.jpg';
        // $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 12);
        // Title
        // $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(0, 0, 'EMBUTIDOS CORONA S.A DE C.V', 0, 1, 'C', 0, '', 0);
        $this->Cell(0, 0, $this->nombreReporte, 0, 1, 'C', 0, '', 1);

        //$this->Cell(0, 7, 'Caja: '.$this->nombreReporte.'  Usuario: '.$this->Usuario, 0, 1, 'L', 0, '', 1);
        //$this->Cell(0, 0, 'Fecha: '.$this->nombreReporte, 0, 7, 'L', 0, '', 1);
        //$this->Cell(0, 0, $this->rangoFechaReporte, 0, 1, 'R', 0, '', 1);

        $this->SetFont('helvetica');

        $tbl =
            '<table cellpadding="0" cellspacing="1" border="0" style="font-size: 9px">

                    <tr> <td colspan="2"></td> </tr>

                    <tr>
                        <th width="100"> <b>Caja:</b> '. $this->noCaja .'</th>
                        <th> <b>Usuario:</b> '. $this->Usuario .' </th>
                    </tr>

                    <tr>
                        <th colspan="2"> <b>Fecha:</b> '. $this->Fecha .' </th>
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
                        <th> <b>Fecha de impresión:</b> '. $this->fechaImpresion .'</th>
                        <th align="rigth">Pagina '. $this->getAliasNumPage().' de '.$this->getAliasNbPages().'</th>
                    </tr>

                </table>';

        $this->writeHTML($tbl, true, false, true, false, '');
    }
}