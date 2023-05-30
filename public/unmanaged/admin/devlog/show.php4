<?php

$include=TRUE;
$path="../../";

require ("../setup.inc.php");
require ("../adm_t.php4");
require ("../adm_fun.php4");
require ("dev_f.php4");

debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("array",$HTTP_POST_VARS);

if (isset($sql)) run_sql($sql);

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
   <tr align="center">
      <td bgcolor="f5f5f5">
      Builds: <? show_all_builds(); ?>
          </td>
   </tr>
  </table>
<br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="1000px" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5" colspan="5">
      <b><a href="add.php4"><font color=red>add new</font></a></b>
          </td>
   <tr>
      <td align="center" bgcolor="f5f5f5"><a href="show.php4?do=1">new</a></td>
      <td align="center" bgcolor="f5f5f5"><a href="show.php4?do=2">added</a></td>
      <td align="center" bgcolor="f5f5f5"><a href="show.php4?do=3">return</a></td>
      <td align="center" bgcolor="f5f5f5"><a href="show.php4">all</a></td>
      <td align="center" bgcolor="f5f5f5"><a href="show.php4?do=4">Create Build</a></td>
   </tr>
   </tr>
  </table>
<br>
<?
      $work->complete=0;
if (isset($ok))
   {
      $work->complete=0;
      $work->ok=1;
      $result=array("id"=>htmlspecialchars($id),
               "name"=>htmlspecialchars($name),
                              "email"=>htmlspecialchars($email),
                              "file"=>htmlspecialchars($file),
                              "pd"=>date("Y-m-d"),
                              "ad"=>date("Y-m-d"),
                              "desc"=>htmlspecialchars($desc),
                              "type"=>"new",
                              "build"=>0,
                               );
      debug_yes("array",$result);
      $res=dev_sql(8,$result);

   }


if (isset($do))
   {
   if ($do=="1")
      {
      $work->new=1;
      $work->complete=1;
      $res=dev_sql(3);
      }
   if ($do=="2")
      {
      $work->add=1;
      $res=dev_sql(4);
      $work->complete=1;
      }
   if ($do=="3")
      {
      $work->return=1;
      $res=dev_sql(5);
      $work->complete=1;
      }
   if ($do=="4")
      {
      $work->create=1;
      $b=create_build();
      $work->complete=1;
      }
   }

if (isset($b))
   {
   $work->build=1;
   $res=dev_sql(6,$b);
   $work->complete=1;
   }

if (!$work->complete) $res=dev_sql(1);

   show_all_changes($res);
?>
  <br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="1000px" bgcolor="black"  style="font-size:13px">
   <tr align="center">
      <td bgcolor="f5f5f5">
                <b><a href="http://www.hypermethod.com">v.
                <?php
                        $res=sql_query(5);
                        $result=@sqlget($res);
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