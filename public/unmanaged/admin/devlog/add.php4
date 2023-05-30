<?php

$include=TRUE;
$path="../../";

require ("../setup.inc.php");
require ("../adm_t.php4");
require ("../adm_fun.php4");
require ("dev_f.php4");

if (isset($edit) && isset($id)) $vars=get_vars($id);
   else
      {
       $vars->name="Имя";
       $vars->email="email";
       $vars->file="Путь до файла";
       $vars->desc="Описание";
       $vars->id="";
         }

debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("array",$HTTP_POST_VARS);
?>

<center>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="50%" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5">
                <b><a href="../bug/index.php4">Bug report</a></b>
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white">
      <b><a href="show.php4">Current Changes</a></b><br>
          </td>
   </tr>
  </table>
<br>
<br>
<form action="show.php4" method="GET" name="devlog">
<input type="hidden" name="id" value="<?=$vars->id?>">
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5" width="50%">
      <input type="text" name="name" value="<?=$vars->name?>" style="width:'100%'">
          </td>
      <td bgcolor="f5f5f5" width="50%">
      <input type="text" name="email" value="<?=$vars->email?>" style="width:'100%'">
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="white"  colspan="2">
      <input name="file"  style="width:'100%'" value="<?=$vars->file?>">
          </td>
   </tr>
   <tr align="center">
      <td bgcolor="f5f5f5"  colspan="2">
      <textarea name="desc" style="width:'100%'"><?=$vars->desc?></textarea>
          </td>
   </tr>
  </table>
  <br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5">
      <input type="submit" name="ok" value="Добавить" style="width:'100px'">
          </td>
   </tr>
  </table>
</form>
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