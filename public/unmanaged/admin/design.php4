<?php

$include=TRUE;

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");

//$connect=get_mysql_base();

echo ph(_("Настройка сервера"));
//debug_yes("array",$HTTP_COOKIE_VARS);
?>

<center>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
<!--   <tr align="center">
      <td class=questt>
                <b><a href="images.php4">Изменение дизайна сервера - Картинки</a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
        <b><a href="style.php4">Сменить стилевую таблицу - шаблон</a></b>
          </td>
   </tr>
   <tr align="center">
      <td class=questt>
        <b><a href="setup2.php4">Мастер редактирования цветовой гаммы сервера</a></b>
          </td>
   </tr>
-->   <tr align="center">
      <td class=questt>
        <b><a href="mail.php4"><?=_("Настроить письма")?></a></b>
          </td>
   </tr>
<!--   <tr align="center">
      <td class=questt>
        <b><a href="baner.php4">Настроить центральные картинки</a></b>
          </td>
   </tr>
-->   <tr align="center">
      <td bgcolor="white">
        <b><a href="check_dirs.php"><?=_("Проверить права на каталоги")?></a></b>
          </td>
   </tr>
   <tr align="center">
      <td class=questt>
        <b><a href="check_db.php"><?=_("Проверить БД")?></a></b>
          </td>
   </tr>
  </table>

<!--  <br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class=questt>
                <b><a href="http://www.hypermethod.com">
                <?php
                        $res=sql_query(5);
                        $result=@sqlget($res);
//                      debug_yes("array",$result);
//                        foreach( $result as $res )
//                          echo "$res<BR>";
                        echo "version: ".$result['value'];
                ?>
                </a></b>

          </td>
   </tr>
   <tr>
          <td bgcolor="white">
             &nbsp
          </td>
   <tr>
   <tr align="center">
      <td class=questt>
                <?php
//                        $res=sql_query(-35,'yes');
                        $res=sql_query(35);
                        $result=@sqlget($res);
                        if( sqlget($res) )
                          echo "free new courses registration: ".$result['value'];
                        else
                          echo "free new courses registration: NOT INSTALLED";

                ?>
          </td>
   </tr>
  </table>


-->
                </center>


<?php

//require_once("adm_b.php4");
   echo show_tb();
?>