<?
//require("phplib.php4");
require("1.php");
//$top1=1;
//require("top.php4");
if (!$stud) login_error();
//      if (empty($s[skurs]) && empty($s[tkurs])) login_error();
echo show_tb();

   echo ph(_("Чат и доска для рисования"));

   $GLOBALS['controller']->captureFromOb(CONTENT);

   $pass=substr(md5(microtime()),0,20);
   $res=sql("UPDATE People SET javapassword='$pass'
             WHERE login=".$GLOBALS['adodb']->Quote($s[login])."","errCH35");

/*   echo "
<applet
  ARCHIVE  = 'ChatApplet.jar'
  codebase = '/applets/chatclient/'
  code     = 'client.ChatApplet.class'
  name     = 'TestApplet'
  width    = '600'
  height   = '300'
  hspace   = '0'
  vspace   = '0'
  align    = 'middle'
>
<param name='in_serverPort' value=".chatport.">
<param name='in_serverName' value='".getenv("HTTP_HOST")."'>
<param name='in_room' value='0'>
<param name='in_login' value='$s[login]'>
<param name='in_pass' value='$pass'>
</applet>
<P>
   ";
*/
?>
<table class="brdr" cellpadding=0 cellspacing=0><tr><td>
<APPLET
  ARCHIVE  = "kclient.jar?1246"
  CODEBASE = "applets/kpaint/"
  CODE     = "kpaint.client.KPaintApplet.class"
  NAME     = "TestApplet"
  WIDTH    = 600
  HEIGHT   = 400
  HSPACE   = 0
  VSPACE   = 0
  ALIGN    = middle
  id     = "p1"
>
<?
echo "
   <param name='in_serverName' value='".$_SERVER['SERVER_NAME']."'>
   <param name='in_serverPort' value='".kclientport."'>
   <param name='in_login' value='$s[login]'>
   <param name='in_pass' value='$pass'>
</APPLET>";
?>
</td></tr></table>
<?

//   echo "<a href='applets/chatclient/chat.php?$sess'>Чат - общая комната (на отдельной странице) &gt;&gt;</a>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();

?>