<?
$not=TRUE;
$path="../../";
require ("../setup.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<HTML><HEAD><TITLE>eLearning Server 3000</TITLE>
<META content="text/html; charset=windows-1251" http-equiv=Content-Type><!--
<script language="Javascript" type="text/javascript">
    if(navigator.appName.indexOf("Netscape") != -1){
                alert("Используйте IE версии 5.0  или выше!");
                close();
        }
    if (navigator.userAgent.indexOf("Opera") != -1){
                alert("Используйте IE версии 5.0  или выше!");
                close();
    }
</script>
 -->
<SCRIPT src="<?=HTTP_SITE?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=HTTP_SITE?>js/img.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=HTTP_SITE?>js/hide.js" language="JScript" type="text/javascript"></script> 
<div id=mega name=mega style="position:absolute; width:200px; z-index:1; visibility: hidden">
</DIV>
<SCRIPT language=JavaScript src="cool.js" type=text/javascript>
</SCRIPT>

<LINK href="../../styles/style.css" rel=stylesheet 
type=text/css>
<LINK href="prev.css" rel=stylesheet 
type=text/css>
<META content="MSHTML 5.00.3315.2870" name=GENERATOR></HEAD>
<BODY class=cPageBG leftMargin=0 onload=preloadImages(); rightMargin=0 
topMargin=0 marginheight="0" marginwidth="0">
<CENTER>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" bgcolor="black" style="font-size:15px">
 <tr align="center">
       <td  bgcolor="f5f5f5">
                <b>Рабочий вариант меню</b>
  </td>
 </tr>
 <tr align="center">
       <td  bgcolor="white">
<TABLE align=center border=0 cellPadding=0 cellSpacing=0 height="64px" 
width=780>
  <TBODY>
  <TR>
    <TD class=cBorder height=64 width=5></TD>
    <TD align=right height=64 vAlign=bottom width=770 background="../../images/head.jpg">
      <TABLE border=0 cellPadding=0 cellSpacing=0 class=skip>
        <TBODY>
        <TR>
          <TD>
         <A onmouseout="changeImages('menutop_dls', '../../images/menu/menutop_dls.gif'); return true;" 
            onmouseover="changeImages('menutop_dls', '../../images/menu/menutop_dls-over.gif'); return true;"><IMG 
            alt="О DLS" border=0 height=14 name=menutop_dls 
            src="../../images/menu/menutop_dls.gif" 
width=90></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="changeImages('menutop_register', '../../images/menu/menutop_register.gif'); return true;" 
            onmouseover="changeImages('menutop_register', '../../images/menu/menutop_register-over.gif'); return true;"><IMG 
            alt=Регистрация border=0 height=14 name=menutop_register 
            src="../../images/menu/menutop_register.gif" 
            width=85></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="changeImages('menutop_courses', '../../images/menu/menutop_courses.gif'); return true;" 
            onmouseover="changeImages('menutop_courses', '../../images/menu/menutop_courses-over.gif'); return true;"><IMG 
            alt="О курсах" border=0 height=14 name=menutop_courses 
            src="../../images/menu/menutop_courses.gif" 
            width=65></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="changeImages('menutop_library', '../../images/menu/menutop_library.gif'); return true;" 
            onmouseover=" changeImages('menutop_library', '../../images/menu/menutop_library-over.gif'); return true;"><IMG 
            alt=Библиотека border=0 height=14 name=menutop_library 
            src="../../images/menu/menutop_library.gif" 
            width=78></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="changeImages('menutop_server', '../../images/menu/menutop_server.gif'); return true;" 
            onmouseover=" changeImages('menutop_server', '../../images/menu/menutop_server-over.gif'); return true;"><IMG 
            alt="О сервере" border=0 height=14 name=menutop_server 
            src="../../images/menu/menutop_server.gif" 
          width=64></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="changeImages('menutop_quest', '../../images/menu/menutop_quest.gif'); return true;" 
            onmouseover="changeImages('menutop_quest', '../../images/menu/menutop_quest-over.gif'); return true;"><IMG 
            alt=Помощь border=0 height=14 name=menutop_quest 
            src="../../images/menu/menutop_quest.gif" 
          width=16></A></TD></TR></TBODY></TABLE> </TD></TR></TBODY></TABLE>
 </td>
 </tr>
 <tr align="center">
       <td  bgcolor="f5f5f5">
                <b>Меню без наведения мышкой (выберите кнопку для изменения)</b>
  </td>
 </tr>
 <tr align="center">
       <td  bgcolor="white">

<TABLE align=center border=0 cellPadding=0 cellSpacing=0 height="64px" 
width=780>
  <TBODY>
  <TR>
    <TD class=cBorder height=64 width=5></TD>
    <TD align=right height=64 vAlign=bottom width=770 background="../../images/head.jpg"  onclick="get_image_name()">
      <TABLE border=0 cellPadding=0 cellSpacing=0 class=skip>
        <TBODY>
        <TR>
          <TD>
         <A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here','../../images/menu/menutop_dls.gif',90)"><IMG 
            alt="О DLS" border=0 height=14 
            src="../../images/menu/menutop_dls.gif" 
width=90></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 2','../../images/menu/menutop_register.gif',85)"><IMG 
            alt=Регистрация border=0 height=14
            src="../../images/menu/menutop_register.gif" 
            width=85></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 3','../../images/menu/menutop_courses.gif',65)"><IMG 
            alt="О курсах" border=0 height=14 
            src="../../images/menu/menutop_courses.gif" 
            width=65></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 4','../../images/menu/menutop_library.gif',78)"><IMG 
            alt=Библиотека border=0 height=14 
            src="../../images/menu/menutop_library.gif" 
            width=78></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 5','../../images/menu/menutop_server.gif',64)"><IMG 
            alt="О сервере" border=0 height=14 
            src="../../images/menu/menutop_server.gif" 
          width=64></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 6','../../images/menu/menutop_quest.gif',16)"><IMG 
            alt=Помощь border=0 height=14 
            src="../../images/menu/menutop_quest.gif" 
          width=16></A></TD></TR></TBODY></TABLE> </TD></TR></TBODY></TABLE>
        </td>
</tr>
 <tr align="center">
       <td  bgcolor="f5f5f5">
                <b>Меню при наведении мышкой. (выберите кнопку для изменения)</b>
  </td>
 </tr>
 <tr align="center">
       <td  bgcolor="white">
<TABLE align=center border=0 cellPadding=0 cellSpacing=0 height="64px" 
width=780>
  <TBODY>
  <TR>
    <TD class=cBorder height=64 width=5></TD>
    <TD align=right height=64 vAlign=bottom width=770 background="../../images/head.jpg"  onclick="get_image_name()">
      <TABLE border=0 cellPadding=0 cellSpacing=0 class=skip>
        <TBODY>
        <TR>
          <TD>
         <A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here','../../images/menu/menutop_dls-over.gif',90)"><IMG 
            alt="О DLS" border=0 height=14 
            src="../../images/menu/menutop_dls-over.gif" 
width=90></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 2','../../images/menu/menutop_register-over.gif',85)"><IMG 
            alt=Регистрация border=0 height=14
            src="../../images/menu/menutop_register-over.gif" 
            width=85></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 3','../../images/menu/menutop_courses-over.gif',65)"><IMG 
            alt="О курсах" border=0 height=14  
            src="../../images/menu/menutop_courses-over.gif" 
            width=65></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 4','../../images/menu/menutop_library-over.gif',78)"><IMG 
            alt=Библиотека border=0 height=14 
            src="../../images/menu/menutop_library-over.gif" 
            width=78></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 5','../../images/menu/menutop_server-over.gif',64)"><IMG 
            alt="О сервере" border=0 height=14 
            src="../../images/menu/menutop_server-over.gif" 
          width=64></A></TD>
          <TD width=10>&nbsp;</TD>
          <TD><A onmouseout="CloseMenu()" 
            onmouseover="ShowMenu('Click Here 6','../../images/menu/menutop_quest-over.gif',16)"><IMG 
            alt=Помощь border=0 height=14  
            src="../../images/menu/menutop_quest-over.gif" 
          width=16></A></TD></TR></TBODY></TABLE> </TD></TR></TBODY></TABLE>
</td>
</tr>
</table>
</CENTER>          
          </BODY></HTML>
