<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version="1.0" xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
  <xsl:output method="html" indent='yes' encoding="UTF-8"/>
        <xsl:template match="/">
<HTML>
<head>
<link rel="stylesheet" href="[URLServer]styles/style.css" type="text/css"/>
</head>
<BODY topmargin='0' leftmargin='0'>
<h1>[SHE_TITLE]</h1>
<h2>[Coursename]</h2>
<b>Преподаватель</b>: [fname] [sname] <br />
<b>Начало занятия</b>: [starttime] <br />
<b>Окончание занятия</b>: [endtime] <br />
<b>Заметки преподавателя</b>: <i>[TEACHNOTES]</i> <br /><br />
                                <xsl:apply-templates select="eventType/redirectURL"/>
                                <xsl:apply-templates select="eventType/liveCamPro"/>
                                <xsl:apply-templates select="eventType/chatApplet"/>
                                <xsl:apply-templates select="eventType/liveCam1"/>
                                <xsl:apply-templates select="eventType/books"/>
                                <xsl:apply-templates select="eventType/tests"/>
                                <xsl:apply-templates select="eventType/externalURLs"/>
                                <xsl:apply-templates select="eventType/module"/>
                                <xsl:apply-templates select="eventType/video_live"/>
</BODY>
</HTML>
        </xsl:template>
<xsl:template match="redirectURL">
        <b>You will be redirected to:</b>
        <A>     <xsl:attribute name='href'>[redirectURL_url]</xsl:attribute>
                        <font color="green">
                        [redirectURL_url]
                </font>
                <hr/>
        </A>
</xsl:template>
<xsl:template match="books">
                <hr/>
   <form name='showLib' action='[URLServer]library.php4' method='POST' target='_self'>
   <input type='hidden' name='make' value='viewLib'/>
   <input type='hidden' name='CID' value='[SHE_CID]'/>
   <span style='cursor:hand' onclick='submit();'><span class='cHilight'><u>Библиотека по курсу: [SHE_COURSE]</u></span></span>
   </form>
                <hr/>
</xsl:template>

<xsl:template match="tests">
        <!--window.open("","testWin","width=800,height=600,scrollbars=1,titlebar=0,resizable=yes"); &#1053;&#1072;&#1095;&#1072;&#1090;&#1100;-->
 <!--       <form method='POST' action='[URLServlets]Tests'>
        <input type='hidden' name='tests_testID'>
                <xsl:attribute name='value'>[tests_testID]</xsl:attribute>
        </input>
        <input type='hidden' name='make' value='showTest'/><center>
        <input type='button' value='Начать выполнение задания' onclick='submit()'/></center>
        </form> -->
      <a>
                <xsl:attribute name='href'>test_start.php?tid=[tests_testID][more_param]</xsl:attribute>
           Начать тест
      </a>
</xsl:template>

<xsl:template match="liveCam1">
<xsl:text disable-output-escaping='yes'>
<![CDATA[
<br>
<a href="#" onclick='window.open("[URLServer]live_up.php4?CID=[SHE_CID]&ID=[SHE_ID]", "_", "toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=450")'>Справка по LiveCam Pro</a>:
<br>
]]>
</xsl:text>
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>834</xsl:attribute>
      <xsl:attribute name='height'>668</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
</xsl:template>

<xsl:template match="liveCamPro">
<xsl:text disable-output-escaping='yes'>
<![CDATA[
<br>
<a href="#" onclick='window.open("[URLServer]live_up.php4?CID=[SHE_CID]&ID=[SHE_ID]", "_", "toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=450")'>Справка по LiveCam Pro</a>:
<br>
]]>
</xsl:text>
   <APPLET code="camapplet.HyperCam.class">
      <xsl:attribute name='codebase'>[URLServer]applets/</xsl:attribute>
      <xsl:attribute name='width'>514</xsl:attribute>
      <xsl:attribute name='height'>478</xsl:attribute>
      <xsl:attribute name='file'>[liveCamPro_file]</xsl:attribute>
   </APPLET>
</xsl:template>

<xsl:template match="chatApplet">
   <applet code='kpaint.client.KPaintApplet.class' >
      <xsl:attribute name='ARCHIVE'>kclient.jar?1246</xsl:attribute>
      <xsl:attribute name='codebase'>[URLServer]applets/kpaint/</xsl:attribute>
      <xsl:attribute name='in_serverName'>[Server]</xsl:attribute>
      <xsl:attribute name='width'>600</xsl:attribute>
      <xsl:attribute name='height'>400</xsl:attribute>
      <xsl:attribute name='hspace'>0</xsl:attribute>
      <xsl:attribute name='vspace'>0</xsl:attribute>
      <xsl:attribute name='in_pass'>[chatApplet_in_pass]</xsl:attribute>
      <xsl:attribute name='in_login'>[chatApplet_in_login]</xsl:attribute>
      <xsl:attribute name='in_serverPort'>[chatApplet_in_serverPort]</xsl:attribute>
   </applet>
</xsl:template>
<xsl:template match="chatApplet_">
   <applet code='chatapplet.ChatApplet.class' >
      <xsl:attribute name='ARCHIVE'>ChatApplet.jar</xsl:attribute>
      <xsl:attribute name='codebase'>[URLServer]applets/chatclient/</xsl:attribute>
      <xsl:attribute name='in_serverName'>[Server]</xsl:attribute>
      <xsl:attribute name='width'>500</xsl:attribute>
      <xsl:attribute name='height'>400</xsl:attribute>
      <xsl:attribute name='hspace'>0</xsl:attribute>
      <xsl:attribute name='vspace'>0</xsl:attribute>
      <xsl:attribute name='in_pass'>[chatApplet_in_pass]</xsl:attribute>
      <xsl:attribute name='in_login'>[chatApplet_in_login]</xsl:attribute>
      <xsl:attribute name='in_serverPort'>[chatApplet_in_serverPort]</xsl:attribute>
<!--      <xsl:attribute name='URLServlets'>[URLServlets]</xsl:attribute> -->
   </applet>
</xsl:template>

<xsl:template match="externalURLs">
        <!--b>list of ext. res:</b-->
        <ul>
                [externalResourses]
        </ul>
</xsl:template>

<xsl:template match="module">
   <form name='showMod' action='[URLServer]teachers/edit_mod.php4' method='GET'>
   <input type='hidden' name='make' value='editMod'/>
   <input type='hidden' name='ModID' value='[module_moduleID]'/>
   <input type='hidden' name='CID' value='[SHE_CID]'/>
   <input type='hidden' name='PID' value='' />
   <input type='hidden' name='new_win' value='1' />
   <input type='hidden' name='mode_frames' value='1' />
   [FSESSID]
        <!--a href='[URLServer]teachers/mods/[SHE_CID]/[module_moduleID]/show_mod.php4'>Перейти к учебному модулю</a-->
   <!--<span style='cursor:hand' onClick="window.open('', 'show_mod', 'width=600,height=500,scrollbars=1,titlebar=0,resizable=yes'); submit()"><span class='cHilight'><u>Изучение учебного материала по курсу: [SHE_COURSE]</u></span></span>-->
   <span style='cursor:hand' onClick="getElementById('showMod').submit();"><span class='cHilight'><u>Изучение учебного материала по курсу: [SHE_COURSE]</u></span></span>

   </form>
</xsl:template>

<xsl:template match="video_live">
   <hr/>
   <form name='showVideoLive' action='[URLServer]video_live.php' method='POST' target='_blank'>
   <input type='hidden' name='make' value='showVideoLive'/>
   <span style='cursor:hand' onclick='submit();'><span class='cHilight'><u>Видеотрансляция по курсу: [SHE_COURSE]</u></span></span>
   </form>
   <hr/>
</xsl:template>

</xsl:stylesheet>