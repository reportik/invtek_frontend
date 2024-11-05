<?php
/**
 * Created by PhpStorm.
 * User: Juan
 * Date: 18/11/2016
 * Time: 03:32 PM
 */
use Illuminate\Support\Facades\Response;
define('JAVA_INC_URL','http://localhost:8080/JavaBridge/java/Java.inc');
define('PHPJASPER_PDF', 'pdf');
define('PHPJASPER_XLS', 'xls');
define('PHPJASPER_CVS', 'cvs');
if( ! function_exists('java')){
    if( ini_get("allow_url_include"))
        require_once(JAVA_INC_URL);
    else
        die ('necesita habilitar allow_url_include en php.ini para poder usar php-jru.');
}
class PHPJasper {
    private $usuario = "sa";
    private $contrasenia = "Itkc4224a";
    private $puerto = "1433";
    private $servidor = "192.168.0.19";
    private $base = "ItekniaDB";


    // SERVIDOR 10
    /*private $usuario = "sa";
    private $contrasenia = "S1st3mas";
    private $puerto = "1433";
    private $servidor = "SERVIDOR10";
    private $base = "Corona";*/

    // MATRIZ
    /*private $usuario = "sa";
    private $contrasenia = "S1st3m4s";
    private $puerto = "1434";
    private $servidor = "SERVIDORMATRIZ";
    private $base = "Corona_Matriz";*/

    // COLIMA
    /*private $usuario = "sa";
    private $contrasenia = "S1st3m4s";
    private $puerto = "1433";
    private $servidor = "SERVIDORCOLIMA";
    private $base = "Corona_Colima";*/

    // VALLARTA
    /*private $usuario = "sa";
    private $contrasenia = "S1st3m4s";
    private $puerto = "1434";
    private $servidor = "SERVIDORVALLART";
    private $base = "Corona_Vallarta";*/

    // TEPIC
    /*private $usuario = "sa";
    private $contrasenia = "S1st3m4s";
    private $puerto = "1433";
    private $servidor = "SERVIDORTEPIC";
    private $base = "Corona_Tepic";*/

    // AGUASCALIENTES
    /*private $usuario = "sa";
    private $contrasenia = "S1st3m4s";
    private $puerto = "1433";
    private $servidor = "SERVIDORAGS";
    private $base = "Corona_Aguascalientes";*/

    // LEON
    /*private $usuario = "sa";
    private $contrasenia = "S1st3m4s";
    private $puerto = "1433";
    private $servidor = "SERVIDORLEON";
    private $base = "Corona_Leon";*/

    public function formatoCSV($archivo_jrxml, $consulta, $parametros, $titulo, $conexion, $ignoraPaginacion = true) {
        try {
            $archivo_csv = str_replace('jrxml', 'csv', $archivo_jrxml);
            $archivo_jasper = str_replace('jrxml', uniqid().'.jasper', $archivo_jrxml);
            $JasperDesign = new java ('net.sf.jasperreports.engine.design.JasperDesign');
            $JRDesignQuery = new java ('net.sf.jasperreports.engine.design.JRDesignQuery');
            $JRXmlLoader =  new java ('net.sf.jasperreports.engine.xml.JRXmlLoader');
            $JasperDesign = $JRXmlLoader->load($archivo_jrxml);
            $JRDesignQuery->setText($consulta);
            $JasperDesign->setQuery($JRDesignQuery);
            $JasperCompileManager =  new Java ('net.sf.jasperreports.engine.JasperCompileManager');
            $JasperCompileManager->compileReportToFile($JasperDesign, $archivo_jasper);
            $JasperFillManager = new Java('net.sf.jasperreports.engine.JasperFillManager');
            $fillReport = $JasperFillManager->fillReport($archivo_jasper, self::parametros($parametros, $ignoraPaginacion), $conexion);
            if(java_is_false($fillReport->getPages()->isEmpty()) == false){
                unlink($archivo_jasper);
                echo 'El documento no tiene páginas';
            } else {
                $exporterCSV = new Java ('net.sf.jasperreports.engine.export.JRCsvExporter');
                $JRExporterParameter  =  new Java ('net.sf.jasperreports.engine.JRExporterParameter');
                $exporterCSV->setParameter($JRExporterParameter->CHARACTER_ENCODING, "ISO-8859-1");
                $exporterCSV->setParameter($JRExporterParameter->JASPER_PRINT,$fillReport);
                $exporterCSV->setParameter($JRExporterParameter->OUTPUT_FILE_NAME, $archivo_csv);
                $exporterCSV->exportReport();
                unlink($archivo_jasper);
                if(file_exists($archivo_csv)){
                    $buffer = file_get_contents($archivo_csv);
                    unlink($archivo_csv);
                }
                $filename = $titulo.".csv";
                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename='.$filename);
                header('Pragma: no-cache');
                print $buffer;
                exit();
            }
        } catch (JavaException $ex) {
            if(file_exists($archivo_jasper)){
                unlink($archivo_jasper);
            }
            $trace = new Java('java.io.ByteArrayOutputStream');
            $ex->printStackTrace(new Java('java.io.PrintStream', $trace));
            print nl2br("java stack trace: $trace\n");
            return false;
        }
    }
    public function formatoWord($archivo_jrxml, $consulta, $parametros, $titulo, $conexion, $ignoraPaginacion = true) {
        try {
            $archivo_word = str_replace('jrxml', 'rtf', $archivo_jrxml);
            $archivo_jasper = str_replace('jrxml', uniqid().'.jasper', $archivo_jrxml);
            $JasperDesign = new java ('net.sf.jasperreports.engine.design.JasperDesign');
            $JRDesignQuery = new java ('net.sf.jasperreports.engine.design.JRDesignQuery');
            $JRXmlLoader =  new java ('net.sf.jasperreports.engine.xml.JRXmlLoader');
            $JasperDesign = $JRXmlLoader->load($archivo_jrxml);
            $JRDesignQuery->setText($consulta);
            $JasperDesign->setQuery($JRDesignQuery);
            $JasperCompileManager =  new Java ('net.sf.jasperreports.engine.JasperCompileManager');
            $JasperCompileManager->compileReportToFile($JasperDesign, $archivo_jasper);
            $JasperFillManager = new Java('net.sf.jasperreports.engine.JasperFillManager');
            $fillReport = $JasperFillManager->fillReport($archivo_jasper, self::parametros($parametros, $ignoraPaginacion), $conexion);
            if(java_is_false($fillReport->getPages()->isEmpty()) == false){
                unlink($archivo_jasper);
                echo 'El documento no tiene páginas';
            } else {
                $exporterRTF = new Java ('net.sf.jasperreports.engine.export.JRRtfExporter');
                $JRExporterParameter  =  new Java ('net.sf.jasperreports.engine.JRExporterParameter');
                $exporterRTF->setParameter($JRExporterParameter->JASPER_PRINT,$fillReport);
                $exporterRTF->setParameter($JRExporterParameter->OUTPUT_FILE_NAME, $archivo_word);
                $exporterRTF->exportReport();
                unlink($archivo_jasper);
                if(file_exists($archivo_word)){
                    $buffer = file_get_contents($archivo_word);
                    unlink($archivo_word);
                }
                $filename = $titulo.".rtf";
                header('Content-Type: application/rtf');
                header('Content-Disposition: attachment; filename='.$filename);
                header('Pragma: no-cache');
                print $buffer;
                exit();
            }
        } catch (JavaException $ex) {
            if(file_exists($archivo_jasper)){
                unlink($archivo_jasper);
            }
            $trace = new Java('java.io.ByteArrayOutputStream');
            $ex->printStackTrace(new Java('java.io.PrintStream', $trace));
            print nl2br("java stack trace: $trace\n");
            return false;
        }
    }
    public function formatoPdf($archivo_jrxml, $consulta, $parametros, $titulo, $conexion, $ignoraPaginacion = true, $codigo = '') {
        try {
            $archivo_pdf = str_replace('jrxml', 'pdf', $archivo_jrxml);
            $archivo_jasper = str_replace('jrxml', uniqid().'.jasper', $archivo_jrxml);
            $JasperDesign = new java ('net.sf.jasperreports.engine.design.JasperDesign');
            $JRDesignQuery = new java ('net.sf.jasperreports.engine.design.JRDesignQuery');
            $JRXmlLoader =  new java ('net.sf.jasperreports.engine.xml.JRXmlLoader');
            $JasperDesign = $JRXmlLoader->load($archivo_jrxml);
            $JRDesignQuery->setText($consulta);
            $JasperDesign->setQuery($JRDesignQuery);
            $JasperCompileManager =  new Java ('net.sf.jasperreports.engine.JasperCompileManager');
            $JasperCompileManager->compileReportToFile($JasperDesign, $archivo_jasper);
            $JasperFillManager = new Java('net.sf.jasperreports.engine.JasperFillManager');
            $fillReport = $JasperFillManager->fillReport($archivo_jasper, self::parametros($parametros, $ignoraPaginacion), $conexion);
            if(java_is_false($fillReport->getPages()->isEmpty()) == false){
                unlink($archivo_jasper);
                echo 'El documento no tiene páginas';
            } else {
                $exporterRTF = new Java ('net.sf.jasperreports.engine.export.JRPdfExporter');
                $JRExporterParameter  =  new Java ('net.sf.jasperreports.engine.JRExporterParameter');
                $exporterRTF->setParameter($JRExporterParameter->JASPER_PRINT,$fillReport);
                $exporterRTF->setParameter($JRExporterParameter->OUTPUT_FILE_NAME, $archivo_pdf);
                $exporterRTF->exportReport();

                if(!empty($codigo)){
                    $dir = public_path() ."\\archivosOV\\";
                    $nombre = $codigo;

                    $JasperExportManager = new JavaClass("net.sf.jasperreports.engine.JasperExportManager");
                    $JasperExportManager->exportReportToPdfFile($fillReport, $dir .$nombre.".pdf");
                }
                unlink($archivo_jasper);
                if(file_exists($archivo_pdf)){
                    $buffer = file_get_contents($archivo_pdf);
                    unlink($archivo_pdf);
                }
                $filename = $titulo.".pdf";
                header('Cache-Control: no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('Content-type: application/pdf; charset=utf-8');
                header('Content-Disposition: inline; filename='.$filename);
                print $buffer;
                exit();
            }
        } catch (JavaException $ex) {
            if(file_exists($archivo_jasper)){
                unlink($archivo_jasper);
            }
            $trace = new Java('java.io.ByteArrayOutputStream');
            $ex->printStackTrace(new Java('java.io.PrintStream', $trace));
            print nl2br("java stack trace: $trace\n");
            return false;
        }
    }
    /*public function formatoPdf($archivo_jrxml, $consulta, $parametros, $titulo, $conexion, $ignoraPaginacion = true){
        $archivo_pdf = str_replace('jrxml', 'pdf', $archivo_jrxml);
        $archivo_jasper = str_replace('jrxml', uniqid().'.jasper', $archivo_jrxml);
        $JasperDesign = new java ('net.sf.jasperreports.engine.design.JasperDesign');
        $JRDesignQuery = new java ('net.sf.jasperreports.engine.design.JRDesignQuery');
        $JRXmlLoader =  new java ('net.sf.jasperreports.engine.xml.JRXmlLoader');
        $JasperDesign = $JRXmlLoader->load($archivo_jrxml);
        $JRDesignQuery->setText($consulta);
        $JasperDesign->setQuery($JRDesignQuery);
        $JasperCompileManager =  new Java ('net.sf.jasperreports.engine.JasperCompileManager');
        $JasperCompileManager->compileReportToFile($JasperDesign, $archivo_jasper);
        try {
            $JasperRunManager =  new Java ('net.sf.jasperreports.engine.JasperRunManager');
            $JasperRunManager->runReportToPdfFile($archivo_jasper, $archivo_pdf, self::parametros($parametros, $ignoraPaginacion), $conexion);
            unlink($archivo_jasper);
            if(file_exists($archivo_pdf)){
                $buffer = file_get_contents($archivo_pdf);
                unlink($archivo_pdf);
            }
            $filename = $titulo.".pdf";
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-type: application/pdf; charset=utf-8');
            header('Content-Disposition: inline; filename='.$filename);
            print $buffer;
        } catch (JavaException $ex) {
            $trace = new Java('java.io.ByteArrayOutputStream');
            $ex->printStackTrace(new Java('java.io.PrintStream', $trace));
            print nl2br("java stack trace: $trace\n");
            return false;
        }
    }*/
    public function parametros($parametros, $ignoraPaginacion){
        // Si no existe el parametro MOSTRAR_LOGO entonces lo agregamos.
        if (!array_key_exists('MOSTRAR_LOGO', $parametros)) {
            $parametros = $parametros + ["MOSTRAR_LOGO" => 'true'];
        }
        // Casteamos los parametros de php a java
        $parameters = new Java('java.util.HashMap');
        foreach ($parametros as $clave => $valor) {
            $parameters->put($clave, stripslashes($valor));
        }
        // Opción de paginación
        if($ignoraPaginacion == true){
            $parameters->put("IS_IGNORE_PAGINATION", new Java('java.lang.Boolean', "false"));
        } else {
            $parameters->put("IS_IGNORE_PAGINATION", new Java('java.lang.Boolean', "true"));
        }
        /*$dir = public_path().'/tmp/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $swapFile = new java('net.sf.jasperreports.engine.util.JRSwapFile', $dir, 2048, 1024);
        $virtualizer = new java('net.sf.jasperreports.engine.fill.JRSwapFileVirtualizer', 3, $swapFile, new Java('java.lang.Boolean', "true"));
        $parameters->put("REPORT_VIRTUALIZER", $virtualizer);*/
        return $parameters;
    }
    public function conexionJDBC(){
        $conn = new Java("org.altic.jasperReports.JdbcConnection");
        $conn->setDriver('net.sourceforge.jtds.jdbc.Driver');
        $conn->setConnectString('jdbc:jtds:sqlserver:/'.$this->servidor.':'.$this->puerto.'/'.$this->base);
        $conn->setUser($this->usuario);
        $conn->setPassword($this->contrasenia);
        return $conn->getConnection();
    }
}