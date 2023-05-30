<?php

//defines

$include=TRUE ;

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");
require ("error.inc.php4");


$connect=get_mysql_base();

debug_yes("array",$HTTP_COOKIE_VARS);
$GLOBALS['controller']->captureFromOb(CONTENT);
?>

<center>
<?php
$GLOBALS['controller']->captureFromOb(TRASH);
?>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class=questt>
        <b><a href="arhiv.php4"><?=_("Архив")?></a></b>
          </td> 
   </tr>
  </table> 
<?
$GLOBALS['controller']->captureStop(TRASH);
// generate table

if (isset($ok) && isset($MID)) 
   {
      $work->complete=1;
      $work->ok=1;
      $result=array("MID"=>htmlspecialchars($MID),
                              "LastName"=>htmlspecialchars($LastName),
                              "FirstName"=>htmlspecialchars($FirstName),
                              "Registered"=>htmlspecialchars($Registered),
                              "Course"=>htmlspecialchars($Course),
                              "EMail"=>htmlspecialchars($EMail),
                              "Phone"=>htmlspecialchars($Phone),
                              "Information"=>htmlspecialchars($Information ),
                              "Patronymic"=>htmlspecialchars($Patronymic),
                              "Address"=>htmlspecialchars($Address),
                              "Fax"=>htmlspecialchars($Fax),
                              "Password"=>htmlspecialchars($Password),
                              "Login"=>htmlspecialchars($Login),
                              "BirthDate"=>htmlspecialchars($BirthDate),
                              "CellularNumber"=>htmlspecialchars($CellularNumber),
                              "ICQNumber"=>htmlspecialchars($ICQNumber),
                              "Age"=>htmlspecialchars($Age),
                               );
      debug_yes("array",$result);
      $res=sql_query(23,$result);

   }

if (isset($edit) && isset($HTTP_GET_VARS["MID"])) 
   {
      $work->complete=0;
      $work->edit=1;
      $work->complete=edit_table("arhiv.php4",$HTTP_GET_VARS["MID"]);
   }
if (isset($del) && isset($HTTP_GET_VARS["MID"]))
   {
      $work->complete=0;
      $work->del=1;
      $work->complete=delete_from_arhiv($HTTP_GET_VARS["MID"]);
      debug_yes("Num Rows 1",$work->complete);     
   }

if ($work->del && $work->complete) echo "<h1>"._("Удалено")."</h1>";

if (!isset($edit))
   {
      $work->complete=0;
      $work->show=1;
      if (isset($order)){$res=sql_query(17,$order);}
      else $res=sql_query(17);
      $work->complete=generate_table("arhiv.php4",$res);
   }

if (!$work->complete) show_error(1);

?>

</center>

<?php
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();
//require_once("adm_b.php4");
?>