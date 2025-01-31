<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:psgecfd="http://www.sat.gob.mx/psgecfd">
	<!-- Manejador de nodos tipo psgecfd:PrestadoresDeServiciosDeCFD -->
  <!--<xsl:include href="C:\inetpub/wwwroot/SuFacturacion.Archivos/XSLT/utilerias.xslt"/>-->
	<xsl:template match="psgecfd:PrestadoresDeServiciosDeCFD">
		<!-- Iniciamos el tratamiento de los atributos de psgecfd:PrestadoresDeServiciosDeCFD -->
		<xsl:call-template name="Requerido"><xsl:with-param name="valor" select="./@nombre"/></xsl:call-template>
		<xsl:call-template name="Requerido"><xsl:with-param name="valor" select="./@rfc"/></xsl:call-template>
		<xsl:call-template name="Requerido"><xsl:with-param name="valor" select="./@noCertificado"/></xsl:call-template>
		<xsl:call-template name="Requerido"><xsl:with-param name="valor" select="./@fechaAutorizacion"/></xsl:call-template>
		<xsl:call-template name="Requerido"><xsl:with-param name="valor" select="./@noAutorizacion"/></xsl:call-template>
	</xsl:template>
</xsl:stylesheet>
