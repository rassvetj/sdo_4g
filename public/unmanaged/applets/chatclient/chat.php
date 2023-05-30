<?

   include("../../1.php");

   if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");

   echo show_tb();
   echo ph("Чат - общая комната");

   $pass=substr(md5(microtime()),0,20);
   $res=sql("UPDATE People SET javapassword='$pass'
             WHERE login='".addslashes($s[login])."'","errCH35");

   echo "
<applet
  ARCHIVE  = 'ChatApplet.jar'
  codebase = '.'
  code     = 'client.ChatApplet.class'
  name     = 'TestApplet'
  width    = '500'
  height   = '400'
  hspace   = '0'
  vspace   = '0'
  align    = 'middle'
>
<param name='in_serverPort' value=".chatport.">
<param name='in_serverName' value='".$_SERVER["HTTP_HOST"]."'>
<param name='in_room' value='0'>
<param name='in_login' value='$s[login]'>
<param name='in_pass' value='$pass'>
</applet>
<P>
<a href=../../chat.php4>Доска для рисования</a>
   ";

   echo show_tb();

?>