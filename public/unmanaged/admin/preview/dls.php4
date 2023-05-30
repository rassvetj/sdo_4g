<?
$path="../../";
$not=TRUE;
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
<TABLE align=center border=0 cellPadding=0 cellSpacing=0 height="100%" 
width=769>
  <TBODY>
  <TR>
    <TD class=cBorder height=62 width=1><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD align=right height=62 vAlign=bottom width=767 background="../../images/head.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/head.jpg',767)" onmouseout="CloseMenu()" onclick="get_image_name()">
      <TABLE border=0 cellPadding=0 cellSpacing=0 class=skip>
        <TBODY>
        <TR >
          <TD>
         <A onmouseout="CloseMenu(); changeImages('menutop_dls', '../../images/menu/menutop_dls.gif'); return true;" 
            onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/menu/menutop_dls.gif',55); changeImages('menutop_dls', '../../images/menu/menutop_dls-over.gif'); return true;"><IMG 
            alt="" border=0 height=20 name=menutop_dls 
            src="../../images/menu/menutop_dls.gif" 
width=55></A></TD>
          
          <TD><A onmouseout="CloseMenu(); changeImages('menutop_register', '../../images/menu/menutop_register.gif'); return true;" 
            onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/menu/menutop_register.gif',105); changeImages('menutop_register', '../../images/menu/menutop_register-over.gif'); return true;"><IMG 
            alt="" border=0 height=20 name=menutop_register 
            src="../../images/menu/menutop_register.gif" 
            width=105></A></TD>
          
          <TD><A onmouseout="CloseMenu();changeImages('menutop_courses', '../../images/menu/menutop_courses.gif'); return true;" 
            onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/menu/menutop_courses.gif',83); changeImages('menutop_courses', '../../images/menu/menutop_courses-over.gif'); return true;"><IMG 
            alt="" border=0 height=20 name=menutop_courses 
            src="../../images/menu/menutop_courses.gif" 
            width=83></A></TD>
          
          <TD><A onmouseout="CloseMenu();changeImages('menutop_library', '../../images/menu/menutop_library.gif'); return true;" 
            onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/menu/menutop_library.gif',104); changeImages('menutop_library', '../../images/menu/menutop_library-over.gif'); return true;"><IMG 
            alt="" border=0 height=20  name=menutop_library 
            src="../../images/menu/menutop_library.gif" 
            width=104></A></TD>
          
          <TD><A onmouseout="CloseMenu();changeImages('menutop_server', '../../images/menu/menutop_server.gif'); return true;" 
            onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/menu/menutop_server.gif',87); changeImages('menutop_server', '../../images/menu/menutop_server-over.gif'); return true;"><IMG 
            alt="" border=0 height=20 name=menutop_server 
            src="../../images/menu/menutop_server.gif" 
          width=87></A></TD>
          
          <TD><A onmouseout="CloseMenu();changeImages('menutop_quest', '../../images/menu/menutop_quest.gif'); return true;" 
            onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/menu/menutop_quest.gif',25); changeImages('menutop_quest', '../../images/menu/menutop_quest-over.gif'); return true;"><IMG 
            alt="" border=0 height=20 name=menutop_quest 
            src="../../images/menu/menutop_quest.gif" 
          width=20></A></TD></TR></TBODY></TABLE></TD>

    <TD class=cBorder width=1><img src="../../images/spacer.gif" height=1 width=1></TD></TR>
  <TR>
    <TD class=cBorder width=1><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD class=cBorder ><img src="../../images/spacer.gif" height=1 width=1><IMG alt="" height=1 src="" width=1></TD>
    <TD class=cBorder width=1><img src="../../images/spacer.gif" height=1 width=1></TD></TR>
  <TR class=cPagebg>
    <TD height=15 width=1><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD width=1><img src="../../images/spacer.gif" height=1 width=1></TD></TR>
<TR class=cbORDER>
    <TD height=1 width=1><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD width=1><img src="../../images/spacer.gif" height=1 width=1></TD></TR>
    
<TR height="100%">
    <TD class=cBorder width=1><img src="../../images/spacer.gif" height=1 width=1></TD>
    <TD class=cMainBG vAlign=top>
      <TABLE border=0 cellPadding=0 cellSpacing=0 width=767 valign="top">
        <TBODY>
        <TR vAlign=top>
          <TD  width=198 CLASS=QUESTT> 
            <TABLE border=0 cellPadding=0 cellSpacing=0 width=198 
              valign="top" onclick="get_image_name()"><TBODY>
              <TR vAlign=top>
                <TD vAlign=top><IMG border=0 height=39
                  src="../../images/index2/ind_1.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_1.jpg',198)" onmouseout="CloseMenu()"
width=198></TD></TR>
              <TR vAlign=top>
                <TD><A target=window_blank><IMG 
                  border=0 height=179 
                  src="../../images/index2/ind_2.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_2.jpg',198)" onmouseout="CloseMenu()"
                  width=198></A></TD></TR>
              <TR>


                <TD><IMG border=0 height=34 
                  src="../../images/index2/ind_3.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_3.jpg',198)" onmouseout="CloseMenu()"
width=198></TD></TR>
              

              <TR vAlign=top>
                <TD vAlign=top><IMG border=0 height=164
                  src="../../images/index2/ind_4.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_4.jpg',198)" onmouseout="CloseMenu()"
width=198></TD></TR>
              <TR vAlign=top>
                <TD><A target=window_blank><IMG 
                  border=0 height=41 
                  src="../../images/index2/ind_5.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_5.jpg',198)" onmouseout="CloseMenu()"
                  width=198></A></TD></TR>






              <TR>
                <TD><IMG border=0 height=116 
                  src="../../images/index2/ind_6.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_6.jpg',198)" onmouseout="CloseMenu()"
width=198></TD></TR>
             <TR >
                      <TD 
                      background="../../images/news_back.gif" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/news_back.gif',198)" onmouseout="CloseMenu()"
                      vAlign=top>
                        <SCRIPT language=JavaScript type=text/javascript>
                        <!-- 
                          showcalendar(); 
                        -->
                        </SCRIPT>
                      </TD></TR>
              </TBODY></TABLE></TD><TD CLASS=CBORDER WIDTH=1><IMG src="../../images/spacer.gif"> </TD>
          <TD vAlign=top><!--Наша табле-->
            <TABLE border=0 cellPadding=0 cellSpacing=0 height="100%" 
            width="100%">
              <TBODY>
              <TR vAlign=top>
                <TD height="100%" width=20><IMG alt="" height=1 src="" 
                width=1></TD>
                <TD vAlign=top>
                  <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%">
                    <TBODY>
                    <TR>
                      <TD height=20 width=205><IMG alt="" height=1 src="" 
                        width=1></TD></TR>
                    <TR>
                      <TD vAlign=top>
                        <TABLE border=0 cellPadding=0 cellSpacing=0 >
                          <INPUT 
                          name=curTeam type=hidden value=all> <INPUT name=make 
                          type=hidden value=startLogin> 
                          <TBODY>
                         <tr><td class=questt><!--Вход в систему-->
		<TABLE WIDTH="187" CELLSPACING="0" cellpadding=0 class=brdr>
		<tr><td><table CELLSPACING="0"  width=100% cellpadding=0>
		
		<tr><TD class=th2  colspan=2>&nbsp;вход в систему:</TD></TR>
		
		</table></td></tr>
		<tr><td><TABLE WIDTH="187" CELLSPACING="0" cellpadding=0 >                                  
		<TR><td  colspan=2 class="cBorder" HEIGHT="1"><img src="images/spacer.gif" width=1 height=1></TD></TR>
		<TR height="25" class="shedaddform"><td WIDTH="5"><img src="images/spacer.gif" width=1 height=1></TD>
		<td class=cHilight>имя</TD></TR>
		<TR>    <td WIDTH="10"><img src="images/spacer.gif" width=1 height=1></TD>
			<TD><input type="text" name="login"></TD></TR>
		<TR height="25" class="shedaddform">
			<td WIDTH="10"><img src="images/spacer.gif" width=1 height=1></TD>			
			<TD class=cHilight>пароль</TD></TR>
		<TR><td WIDTH="10"><img src="images/spacer.gif" width=1 height=1></TD>
			<TD><input type="password" name="password"></TD></TR>
		<TR><td WIDTH="10"><img src="images/spacer.gif" width=1 height=20></TD>
			<TD><img src="images/spacer.gif" width=1 height=1></TD></TR></table></td></tr>
		</TABLE><!--Вход в систему--></td></tr>
                          <TR>
                            <TD align=right class=shedaddform 
                              vAlign=top><INPUT align=right alt=ok border=0 
                              name=add_schedule_send 
                              onmouseout="CloseMenu(); this.src='../../images/send.gif';" 
                              onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/send.gif',187); this.src='../../images/send_.gif';" 
                              onclick="get_image_name()"
                              src="../../images/send.gif" 
                              type=image></TD></TR></TBODY></TABLE></TD>
                      <TD width=5></TD>
                      <TD vAlign=top HEIGHT=10>
                        <TABLE border=0 cellPadding=0 cellSpacing=0 class=brdr HEIGHT=100% width=309>
                                                    <TR><th HEIGHT=10>Новости</th></TR>
                          <TR width="100%" HEIGHT=100% CLASS=QUESTT>
                            <TD align=justify class=testsmall >
                              <P><b>Кликните на любую картинку для её замены.</b>
                        </P></TD></TR></TABLE></TD></TR>
                                       <tr><td colspan=3 HEIGHT=10 WIDTH=100%><img src="../../images/spacer.gif" HEIGHT=10 WIDTH=1></td></tr>

                   <TR colspan="3">
                      <TD align=right colSpan=3 vAlign=top>
                        <TABLE border=0 cellPadding=0 cellSpacing=0 width="100%" 
                        Valign="top">
                          <TBODY>
                          
                                            

                          <TR>


                            <TD align=justify vAlign=top><BR>
                              <P align=justify class=news>
                        <IMG alt=" " border=0  height=17  src="../../images/index2/marker1.gif" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/marker1.gif',17)" onmouseout="CloseMenu()" onclick="get_image_name()" width=17>
                        <b>Кликните на любую картинку для её замены.</b><br>
                        После обновления картинки не забудьте нажать "обновить".<br>
                        Это необходимо, чтобы обновить кэш на Вашей машине.<br>
                        <br> </P>
                        <P align=center>
                       <!-- <input type=button onclick="document.execCommand('refresh');" name=refresh value='Обновить'>  -->

                              </P>
                              </TD>
                            <TD height="100%" rowSpan=2 width=20></TD>
                            <TD></TD>
                            <TD align=right  rowSpan=2 vAlign=top 
                            width=177>
                              <TABLE border=0 cellPadding=0 cellSpacing=0>
                                <TBODY>
                                <TR>
                                <TD rowSpan=8 width=20></TD>
                                <TD height=20></TD>
                                <TD rowSpan=8 width=1></TD></TR>
                                <TR>
                                <TD><IMG alt=" " border=0 height=108 
                                src="../../images/index2/ind_pict1.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_pict1.jpg',130)" onmouseout="CloseMenu()" onclick="get_image_name()"
                                width=130></TD></TR>
                                <TR>
                                <TD height=20></TD></TR>
                                <TR>
                                <TD><IMG alt=" " border=0 height=108 
                                src="../../images/index2/ind_pict2.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_pict2.jpg',130)" onmouseout="CloseMenu()" onclick="get_image_name()"
                                width=130></TD></TR>
                                <TR>
                                <TD height=20></TD></TR>
                                <TR>
                                <TD><IMG alt=" " border=0 height=108 
                                src="../../images/index2/ind_pict3.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_pict3.jpg',130)" onmouseout="CloseMenu()" onclick="get_image_name()"
                                width=130></TD></TR>
                                <TR>
                                <TD height=20></TD></TR>
                                <TR>
                                <TD><IMG alt=" " border=0 height=108 
                                src="../../images/index2/ind_pict4.jpg" onmouseover="ShowMenu('Кликните по картинке,если Вы хотите ее изменить','../../images/index2/ind_pict4.jpg',130)" onmouseout="CloseMenu()" onclick="get_image_name()"
                                width=130></TD></TR>
                                <TR>
                                <TD 
                          height=20></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD><!--Наша табле<:--></TR></TBODY></TABLE>
    <TD class=cBorder width=1></TD>
  <TR height=1 class=cborder>
    <TD width=1></TD>
    <TD></TD>
    <TD width=1></TD></TR></TBODY></TABLE>
    </BODY></HTML>
