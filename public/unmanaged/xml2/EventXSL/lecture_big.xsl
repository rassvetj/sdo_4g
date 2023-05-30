<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
  <!--xsl:output method="html" indent='yes'/-->
  <xsl:output method="html" indent='yes' encoding="UTF-8"/>
   <xsl:template match="/">
<HTML>
<head>
<link rel="stylesheet" href="[URLServer]styles/style.css" type="text/css"/>
</head>
<BODY topmargin='0' leftmargin='0'>
<h1>[SHE_TITLE]</h1>
   <table border='0' celspacing='1'>
   <tr><td valign='top' align='center' colspan='2'>
<h2>[Coursename]</h2>
<b>Преподаватель</b>: [fname] [sname]<br />
<b>Начало занятия</b>: [starttime] <br />
<b>Окончание занятия</b>: [endtime] <br />
<b>Заметки преподавателя</b>: <i>[TEACHNOTES]</i> <br /><br />
   </td>
   </tr>
   <tr><td valign='top'>
      <xsl:apply-templates select="eventType/liveCamPro"/>
      <xsl:apply-templates select="eventType/liveCam1"/>
      <xsl:apply-templates select="eventType/kpaint"/>
   </td>
   <td valign='top' rowspan='2'>
      <xsl:apply-templates select="eventType/chatApplet"/>
   </td></tr>
   <tr><td valign='top' align='left'>
   <xsl:apply-templates select="eventType/externalURLs"/>
   </td>
   </tr>
   <tr><td valign='top' align='left' colspan='2'>
   <xsl:apply-templates select="eventType/module"/>
   </td>
   </tr>
   </table>
</BODY>
</HTML>
</xsl:template>
<xsl:template match="liveCam1">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>514</xsl:attribute>
      <xsl:attribute name='height'>478</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
<xsl:text disable-output-escaping='yes'>
<![CDATA[
<br><a href="#" onclick='window.open("[URLServer]live_up.php4?CID=[SHE_CID]&ID=[SHE_ID]", "_", "toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=450")'>Справка по LiveCam Pro</a><br>
]]>
</xsl:text>
</xsl:template>
<xsl:template match="liveCamPro">
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>514</xsl:attribute>
      <xsl:attribute name='height'>478</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
<xsl:text disable-output-escaping='yes'>
<![CDATA[
<br><a href="#" onclick='window.open("[URLServer]live_up.php4?CID=[SHE_CID]&ID=[SHE_ID]", "_", "toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=450")'>Справка по LiveCam Pro</a><br>
]]>
</xsl:text>
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
<xsl:template match="kpaint">
<APPLET CODE     = "kpaint.client.KPaintApplet.class">
      <xsl:attribute name='ARCHIVE'>kclient.jar</xsl:attribute>
      <xsl:attribute name='CODEBASE'>/applets/kpaint/</xsl:attribute>
      <xsl:attribute name='NAME'>TestApplet</xsl:attribute>
      <xsl:attribute name='WIDTH'>600</xsl:attribute>
      <xsl:attribute name='HEIGHT'>400</xsl:attribute>
      <xsl:attribute name='HSPACE'>0</xsl:attribute>
      <xsl:attribute name='VSPACE'>0</xsl:attribute>
      <xsl:attribute name='ALIGN'>middle</xsl:attribute>
      <xsl:attribute name='id'>p1</xsl:attribute>
      <param name='in_serverName' value='[KCLIENT_HOST]'/>
      <param name='in_serverPort' value='[KCLIENT_PORT]' />
      <param name='in_login' value='[KCLIENT_LOGIN]'/>
      <param name='in_pass' value='[KCLIENT_PASS]'/>
      <param name='in_room' value='[KCLIENT_ROOM]'/>
</APPLET>
</xsl:template>
<xsl:template match="module">
   <form name='showMod' action='[URLServer]teachers/edit_mod.php4' method='POST' target='show_mod'>
   <input type='hidden' name='make' value='editMod'/>
   <input type='hidden' name='ModID' value='[module_moduleID]'/>
   <input type='hidden' name='CID' value='[SHE_CID]'/>
   <input type='hidden' name='PID' value='' />
   <input type='hidden' name='new_win' value='1'/>
   <input type='hidden' name='mode_frames' value='1'/>
        <!--a href='[URLServer]teachers/mods/[SHE_CID]/[module_moduleID]/show_mod.php4'>¦хЁхщЄш ъ єўхсэюьє ьюфєы¦</a-->
   <span style='cursor:hand' onClick="window.open('', 'show_mod', 'width=600,height=500,scrollbars=1,titlebar=0,resizable=yes'); submit()"><span class='cHilight'><u>Изучение учебного материала по курсу: [SHE_COURSE]</u></span></span>
   </form>
</xsl:template>

</xsl:stylesheet>