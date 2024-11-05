<?php
/**
 * Created by PhpStorm.
 * User: dnlimon
 * Date: 18/02/2016
 * Time: 12:19 PM
 */

    //require_once('tcpdf.php');
    require_once __DIR__ . '/../../../../public/plugins/tcpdf/tcpdf.php';

    class REPORTESOV extends TCPDF {

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
            $tbl =
                '<table cellpadding="1" cellspacing="1" border="0" style="text-align:center;">
					<tr>
					<td rowspan="3" align="center" width="90" height="100"><img src="../public/img/logocorona.jpg"/></td>
					<td rowspan="3" align="center" width="50" height="100"></td>
					<td rowspan="3" align="justify" width="223" height="100">
						ITEKNIA EQUIPAMIENTO, S.A. DE C.V.<br>
						RFC: IEQ030130H71<br>
						CALLE 2 No. 2391  COL. ZONA INDUSTRIAL<br>
						GUADALAJARA, JALISCO, MÉXICO, CP:44940<br>
						Tel: (33)3812-3200<br>
						EXPEDIDO EN: GUADALAJARA, JALISCO, MÉXICO<br>
						601 - GENERAL DE LEY PERSONAS MORALES<br>
						www.iteknia.com<br>
						clientes@iteknia.mx
					</td>
					<td colspan="3" style="background-color:#d4d4d4" border="1" align="center" width="145" height="12"> <b>'.$this->tipoDocumento.'</b> </td>
					</tr>
					<tr>
					<td colspan="3" align="center" border="1" width="145" height="12"> <b>'.trim($this->codigo).'</b> </td>
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
        }
    }