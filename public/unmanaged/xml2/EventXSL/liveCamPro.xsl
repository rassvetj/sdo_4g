<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
  <xsl:output method="html" indent='yes'/>
   <xsl:template match="/">
<HTML>
<head>
<link rel="stylesheet" href="[URLServer]styles/style.css" type="text/css"/>
</head>
<BODY topmargin='0' leftmargin='0'>
   <table border='0' celspacing='1'>
   <tr><td>
      <xsl:apply-templates select="eventType/liveCamPro"/>
      <xsl:apply-templates select="eventType/liveCam1"/>
   </td>
   </tr>
   <tr><td>
   <xsl:apply-templates select="eventType/externalURLs"/>
   </td></tr>
   <tr><td>
<xsl:text disable-output-escaping='yes'>
<![CDATA[
<br><a href="#" onclick='window.open("[URLServer]live_up.php4?CID=[SHE_CID]&ID=[SHE_ID]", "_", "toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=450")'>Справка по LiveCam Pro</a><br>
]]>
</xsl:text>
   </td></tr>

   </table>
</BODY>
</HTML>
</xsl:template>
<xsl:template match="liveCam1">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>834</xsl:attribute>
      <xsl:attribute name='height'>668</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
</xsl:template>
<xsl:template match="liveCamPro">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>514</xsl:attribute>
      <xsl:attribute name='height'>478</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
</xsl:template>
<xsl:template match="externalURLs">
   <ul>
      [externalResourses]
   </ul>    
</xsl:template>
</xsl:stylesheet>