<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
  <xsl:output method="html" indent='yes'/>
   <xsl:template match="/">
<HTML>
<head>
<link rel="stylesheet" href="[URLServer]styles/style.css" type="text/css"/>
</head>
<BODY topmargin='0' leftmargin='0'>
<h1>[SHE_TITLE]</h1>
<h2>[Coursename]</h2>
<b>Преподаватель</b>: [fname] [sname]<br />
<b>Начало занятия</b>: [starttime] <br />
<b>Окончание занятия</b>: [endtime] <br />
<b>Заметки преподавателя</b>: <i>[TEACHNOTES]</i> <br /><br />
   <table border='0' celspacing='1'>
   <tr><td>
      <xsl:apply-templates select="eventType/liveCamPro"/>
      <xsl:apply-templates select="eventType/liveCam1"/>
   </td>
   <td rowspan='2' valign='top'>
      <xsl:apply-templates select="eventType/chatApplet"/>
   </td></tr>
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
<!--xsl:template match="liveCam1">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>514</xsl:attribute>
      <xsl:attribute name='height'>478</xsl:attribute>
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
</xsl:template-->
<xsl:template match="liveCam1">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>314</xsl:attribute>
      <xsl:attribute name='height'>278</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
</xsl:template>
<xsl:template match="liveCamPro">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>314</xsl:attribute>
      <xsl:attribute name='height'>278</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
</xsl:template>
<xsl:template match="chatApplet">
   <applet code='client.ChatApplet.class' >
      <xsl:attribute name='ARCHIVE'>ChatApplet.jar</xsl:attribute>
      <xsl:attribute name='codebase'>[URLServer]applets/chatclient/</xsl:attribute>
      <xsl:attribute name='in_serverName'>[Server]</xsl:attribute>
      <xsl:attribute name='width'>500</xsl:attribute>
      <xsl:attribute name='height'>400</xsl:attribute>
      <xsl:attribute name='hspace'>0</xsl:attribute>
      <xsl:attribute name='vspace'>0</xsl:attribute>
      <xsl:attribute name='in_room'>[chatApplet_in_room]</xsl:attribute>
      <xsl:attribute name='in_pass'>[chatApplet_in_pass]</xsl:attribute>
      <xsl:attribute name='in_login'>[chatApplet_in_login]</xsl:attribute>
      <xsl:attribute name='in_serverPort'>[chatApplet_in_serverPort]</xsl:attribute>
<!--      <xsl:attribute name='URLServlets'>[URLServlets]</xsl:attribute> -->
   </applet>
</xsl:template>
<xsl:template match="externalURLs">
   <ul>
      [externalResourses]
   </ul>
</xsl:template>
</xsl:stylesheet>