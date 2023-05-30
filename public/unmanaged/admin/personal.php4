<?php

//defines

$include=TRUE ;

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");


$connect=get_mysql_base();


debug_yes("array",$HTTP_COOKIE_VARS);
?>

<center>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class="questt">
        <b><a href="teachers.php4"><?=_("Учетные записи преподавателей")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
        <b><a href="students.php4"><?=_("Учетные записи обучаемых")?></a></b>
          </td>
   </tr>
   <!--tr align="center">
      <td class=questt>
        <b><a href="abitur.php4">Зарегистрированные на курсы</a></b>
          </td>
   </tr-->
   <tr align="center">
      <td class="questt">
        <b><a href="people.php"><?=_("Все учетные записи")?></a></b>
          </td>
   </tr>
   <tr align="center">
   <td bgcolor="white">
        <b><a href="people_add.php"><?=_("Операции с учетными записями")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td class="questt">
        <b><a href="uplimit.php"><?=_("Заблокированные учетные записи")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
        <b><a href="sp.php4"><?=_("Информация для пользователей по тех. поддержке")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td class="questt">
        <b><a href="students.php"><?=_("Назначение прав обучаемым")?></a></b>
          </td>
   </tr>   
   <tr align="center">
      <td bgcolor="white">
        <b><a href="teachers.php"><?=_("Назначение прав преподавателям")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td class="questt">
        <b><a href="dekan.php4"><?=_("Назначение прав учебной администрации")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
        <b><a href="admin.php4"><?=_("Назначение прав администору сервера")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
        <b><a href="roles.php"><?=_("Создание и редактирование ролей пользователей")?></a></b>
          </td>
   </tr>
<!--   <tr align="center">
      <td bgcolor="white">
        <b><a href="log.php4">LOG-файл регистрации (reg.log)</a></b></td>
   </tr>
-->   
  </table>
  <br>
</center>


<?php


//require_once("adm_b.php4");
echo show_tb();
?>