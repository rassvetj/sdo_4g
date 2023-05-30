<?php

$include=TRUE;
$path="../../";


require ("../setup.inc.php");
require ("../adm_t.php4");
require ("../adm_fun.php4");


$connect=get_mysql_base();


debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("array",$HTTP_POST_VARS);
?>

<center>
<h1>Dev Log</h1>
<br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5" width="50%">
      <b><a href="add.php4">Добавить изменение</a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
      <b><a href="show.php4">Просмотреть изменения</a></b>
          </td>
   </tr>
  </table>
<br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5">
                <b><a href="http://www.hypermethod.com">v.
                <?php
                        $res=sql_query(5);
                        $result=@sqlget($res);
//                      debug_yes("array",$result);
                        echo $result['value'];
                ?>
                </a></b>
          </td>
   </tr>
  </table>

</center>


<?php

require_once("../adm_b.php4");
?>