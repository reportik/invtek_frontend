<?php
/**
 * Created by PhpStorm.
 * User: TOSHIBAS
 * Date: 25/01/16
 * Time: 01:19 PM
 */
//require_once('tcpdf.php');
require_once __DIR__ . '/../../../../public/plugins/tcpdf/tcpdf/tcpdf.php';

class AnalisisDeVenta extends TCPDF {

    public $nombreReporte;
    public $rangoFechaReporte;
    public $fechaImpresion;

    public function setNombreReporte($nombreReporte){
        $this->nombreReporte = $nombreReporte;
    }

    public function setRangoFechadelReporte($rangoFechaReporte){
        $this->rangoFechaReporte=$rangoFechaReporte;
    }

    public function setFechaImpresion($fechaImpresion){


       $this->fechaImpresion=$fechaImpresion;
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
        $this->Cell(0, 0, $this->rangoFechaReporte, 0, 1, 'C', 0, '', 1);

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        $this->SetX(-15);
        // Set font
        $this->SetFont('helvetica', 'B', 8);
        // Page number

        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 1, 'C', 0, '', 0);
        $this->Cell(0, 0,$this->fechaImpresion, 0, 1, 'C', 0, '', 1);
    }
}