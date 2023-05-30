<?php

//defines

$include=TRUE ;
error_reporting(2039);

// include

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
require ("adm_fun.php4");



$connect=get_mysql_base();

$sql="personal order by 'PID' ASC";
if (isset($HTTP_GET_VARS['sn']) && isset($HTTP_GET_VARS['st'])) if(!empty($HTTP_GET_VARS['sn']) && !empty($HTTP_GET_VARS['st'])) $sql="personal ORDER BY ".$HTTP_GET_VARS['sn']." ".$HTTP_GET_VARS['st'];
if (isset($HTTP_GET_VARS['st'])) $st= ($HTTP_GET_VARS['st']=="ASC")? "DESC" : "ASC";
   else $st="ASC";

//$string = strip_tags($string, '<a><b><i><u>');

debug_yes("st",$st);
debug_yes("sn",$sn);
debug_yes("sql",$sql);

$res=sql_query(0,$sql);

debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
//if (isset($result)) debug_yes("array",$result);
while ($result=@sqlget($res))
        {
                debug_yes("array",$result);
        }//while

               $res=sql_query(0,$sql);
   debug_yes("field's","type");
   while ($field = sqlfetchfield($res)) debug_yes("name",$field->name);
/**
 *                   $res=sql_query(0,$sql);
   debug_yes("object","type");
   while ($field = mysql_fetch_object($res)) debug_yes("array",$field);
                  $res=sql_query(0,$sql);
   debug_yes("row","type");
   while ($field = mysql_fetch_row($res)) debug_yes("array",$field);
                  $res=sql_query(0,$sql);
   debug_yes("assoc ","type");
   while ($field = mysql_fetch_assoc($res)) debug_yes("array",$field);
                  $res=sql_query(0,$sql);
   debug_yes("lenth","type");
   while ($field = mysql_fetch_lengths($res)) debug_yes("array",$field);
             //   $res=sql_query(0,$sql);
 */
?>

<?php
$GLOBALS['controller']->captureFromOb(TRASH);
?>
<center>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class=questt>
        <b><a href="sp.php4"><?=_("Персонал сервера. Администрация, Тех поддержка")?></a></b>
          </td>
   </tr>
  </table>

<?php
$GLOBALS['controller']->captureStop(TRASH);
     $show=TRUE;

         if (isset($ok))
                {
                        $ok=TRUE;

            if (!isset($HTTP_GET_VARS['FIO']) || !isset($HTTP_GET_VARS['work']) || !isset($HTTP_GET_VARS['tel']) || !isset($HTTP_GET_VARS['email']) || !isset($HTTP_GET_VARS['type']) || !isset($HTTP_GET_VARS['PID']))
                                {
                                        $ok=FALSE;
                                        $result=0;
                                }else
                                {
                                        $result=array("FIO"=>htmlspecialchars($HTTP_GET_VARS['FIO']),
                                                                "work"=>htmlspecialchars($HTTP_GET_VARS['work']),
                                                                "tel"=>htmlspecialchars($HTTP_GET_VARS['tel']),
                                                                "email"=>htmlspecialchars($HTTP_GET_VARS['email']),
                                                                "type"=>htmlspecialchars($HTTP_GET_VARS['type']),
                                                                "PID"=>htmlspecialchars($HTTP_GET_VARS['PID']));
                                }
                        if (empty($HTTP_GET_VARS['FIO']) || empty($HTTP_GET_VARS['work']) || empty($HTTP_GET_VARS['tel']) || empty($HTTP_GET_VARS['email']) || empty($HTTP_GET_VARS['type'])) $ok=FALSE;
                        if (!$ok)
                                {
                                        $show=FALSE;
                                        show_edit_table($result);
                                        $GLOBALS['controller']->setMessage(_("Не заполнены необходимые поля"));
                                }else
                                {
                                        $res=(empty($HTTP_GET_VARS['PID'])) ? sql_query(12,$result) : sql_query(14,$result);
                                        $res=sql_query(0,$sql, "err34343");
                                        $GLOBALS['controller']->setMessage(_("Информация успешно добавлена"));
                                }

                }

     if (isset($edit))
        {
                $GLOBALS['controller']->setHeader(_("Редактирование информации по тех. поддержки"));
                $show=FALSE;
                if (isset($pid))
                        {
                          $res=sql_query(0,"personal WHERE PID='".$pid."'", "err2345f");
                          $result=@sqlget($res);
                          debug_yes("array",$result);
                          show_edit_table($result);
                        }else
                        {
                          show_edit_table();
                        }
        }

     if (isset($del))
        {
                if (isset($pid))
                        {
                          $res=sql_query(11,"personal WHERE PID='".$pid."'");
                          $res=sql_query(0,$sql);
                          $GLOBALS['controller']->setMessage(_("Информация успешно удалена"));
                        }
        }

     if ($show)
        {
           $show=FALSE;
           show_personal_table($res,$st);
        }

$GLOBALS['controller']->captureStop(CONTENT);
?>
</center>
<?php
//require_once("adm_b.php4");
echo show_tb();
?>