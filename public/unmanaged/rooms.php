<?php
   require_once("1.php");
   if (!$dean) login_error();

   if (isset($_REQUEST['c'])) {
       $c = $_REQUEST['c'];
   }

    if (isset($_REQUEST['rid'])) {
        $rid = $_REQUEST['rid'];
    }

//     echo"!!! ses=$sess !!!";
global $adodb;
switch ($c) {

case "":
   echo show_tb();

   $GLOBALS['is_edited'] = $GLOBALS['controller']->checkPermission(ROOMS_PERM_EDIT);
   echo ph(_("Ресурсы"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo "
    <div style='padding-bottom: 5px;'>
        <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
        <div><a href='{$PHP_SELF}?c=edit&rid=0' style='text-decoration: none;'>"._("Создать место проведения")."</a></div>
    </div>
    <table width=100% class=main cellspacing=0>
    <tr><th>"._("Название")."</th><th>"._('Тип')."</th><th>"._('Описание')."</th><th>"._('Количество мест')."</th>";
   if ($GLOBALS['is_edited'])
      echo "<th width='100px' align='center'>"._("Действия")."</th>";
   echo "</tr>";
   $res=sql("SELECT * FROM rooms ORDER BY rid","errGR73");
   while ($r=sqlget($res)) {
      echo "<tr>
            <td>";
      //if ($GLOBALS['is_edited'])
      //echo "<a href=$PHP_SELF?c=edit&rid=$r[rid]$sess>";
      echo "$r[name]</td><td>".getRoomType($r[type])."</td><td>" . nl2br($r['description']) ."</td><td>$r[volume]</td>";
      //if ($GLOBALS['is_edited'])
      //echo "</a>";
      echo "</td>";
      if ($GLOBALS['is_edited']) {
          echo "<td  align='center'>";
          echo "<a href=$PHP_SELF?c=edit&rid=$r[rid]$sess>".getIcon("edit", _('Редактировать место проведения'))."</a> &nbsp; ";
          echo "<a href=$PHP_SELF?c=delete&rid=$r[rid]$sess
                onclick=\"if (!confirm('"._("Вы действительно желаете удалить место проведения?")."')) return false;\" >".getIcon("delete", _('Удалить место проведения'))."</a>";
      }
      echo "</tr>";
   }
   if (sqlrows($res)==0) echo "<tr><td colspan=4>"._("нет данных для отображения")."</td></tr>";
   else sqlfree($res);
   echo "</table>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
break;


case "delete":
   intvals("rid");
   if (!empty($rid)) {
        $res=sql("DELETE FROM rooms WHERE rid='$rid'","errFM185");
        sqlfree($res);
   }
   refresh("$PHP_SELF?$sess");
break;

case "edit":
   intvals("rid");
   echo show_tb();
   echo ph(_("Редактирование ресурса"));
   $GLOBALS['controller']->setHelpSection('edit');
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Редактирование места проведения"));
   if ($rid) {
       $tmp="SELECT * FROM rooms WHERE rid=$rid";
       $r=sql( $tmp );
       $res=sqlget( $r );
       sqlfree($r);
   }
   else {
       $res = array();
   }
   $tmp="<form action=$PHP_SELF method=post>
   <input type=hidden name=c value=\"post_editroom\">
   <input type=hidden name=rid value='$rid'>";
   $tmp.="<table width=100% class=main cellspacing=0>
   <tr>
   <td>"._("Название")."</td><td><input type=text name=name value=\"".htmlspecialchars($res['name'])."\"></td>
   </tr>
   <tr>
   <td>"._("Количество мест")."</td><td><input type=text name=volume value='".$res['volume']."'></td>
   </tr>
   <tr>
   <td>"._("Тип")."</td><td><select name=type>";
   $i=0;
   $st=getRoomType( $i );
   while( $st!="" ){
     if ( $i == intval($res['type'])) $sl=" selected"; else $sl="";
     $tmp.="<option value=$i $sl>$st</option>";
     $i++;
     $st=getRoomType( $i );
   }
   $tmp.="</select></td>
   </tr>
   <tr>
   <td>"._("Статус")."</td><td><select name=status>";
   $i=0;
   $st=getRoomStatus( $i );
   while( $st!="" ){
     if ( $i == intval( $res['status'])) $sl=" selected"; else $sl="";
     $tmp.="<option value=$i $sl>$st</option>";
     $i++;
     $st=getRoomStatus( $i );
   }
   $tmp.="</select></td>
   </tr>
   <tr>
   <td>"._("Описание")."</td><td><textarea rows=10 cols=60 name=description>".$res['description']."</textarea></td>
   </tr>
   </table>";
   $tmp.="
<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" width=\"100%\">
      <tr>
        <td align=\"right\" width=\"99%\">
        ".okbutton()."
        </td>
        <td align=\"right\" width=\"1%\">
       <button type=\"button\" onClick=\"history.back();\" class=\"ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\" role=\"button\" aria-disabled=\"false\"><span class=\"ui-button-text\">" . _('Отмена') ."</span></button>

       <!-- <div style='float: right;' class='button'><a href='javascript:history.back();'>"._("Отмена")."</a></div><input type='button' value='отмена' style='display: none;'/><div class='clear-both'></div>  -->
        </td>
      </tr>
</table>
   </form>";

   echo $tmp;
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;

case "post_editroom":
   $name = $_REQUEST['name'];
   $volume = $_REQUEST['volume'];
   $type = $_REQUEST['type'];
   $status = $_REQUEST['status'];
   $description = $_REQUEST['description'];

   intvals("rid");
   global $adodb;
   $message = '';
   if (strlen(trim($name)) <= 0) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_('Вы не указали название места проведения'),JS_GO_URL, $sitepath.'rooms.php?c=edit&rid='.(int) $rid);
       $GLOBALS['controller']->terminate();
       exit();
   }

   if ($volume <= 0) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_('Введена некорректная вместимость места проведения'),JS_GO_URL, $sitepath.'rooms.php?c=edit&rid='.(int) $rid);
       $GLOBALS['controller']->terminate();
       exit();
   }

   if ($rid) {
       $rq="UPDATE `rooms`
            SET name=".$adodb->Quote($name).",
                volume = ".intval($volume).",
                type = '$type',
                status = '$status',
                description = ".$adodb->Quote($description)."
            WHERE rid=$rid";
   } else {
       $rq="INSERT INTO `rooms` (name, volume, type, status, description)
            VALUES (".$adodb->Quote($name).", ".intval($volume).", '$type', '$status', ".$adodb->Quote($description).")";
   }

   if ($rid && intval($status) == 0) {
       $sql = "UPDATE schedule SET rid=0 WHERE rid='".(int) $rid."'";
       sql($sql);
   }
   $res=sql($rq,"errGR138");
   sqlfree($res);

   if ($rid && $max = check_room_capacity((int) $rid, (int) $volume)) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_('Введённая вместимость места проведения слишком мала (В место проведения назначено обучение большего числа слушателей: '.(int) $max.')'),JS_GO_URL,$sitepath.'rooms.php');
       $GLOBALS['controller']->terminate();
       exit();
   }

   refresh("$PHP_SELF?$sess");

   return;
}

function getRoomType( $i ){
  $f="";
  $t[]=" ";
  $t[]=_("лекционная аудитория");
  $t[]=_("семинарская аудитория");
  $t[]=_("учебный класс");
  $t[]=_("лаборатория");
  $t[]=_("рабочее помещение");

  if ( $i<count ( $t ) ) $f = $t[$i];

return( $f );
}

function getRoomStatus( $i ){
  $f="";
  $t[]=_("недоступно");
  $t[]=_("доступно");
  if ( $i<count ( $t ) ) $f = $t[$i];
  return( $f );
}

function check_room_capacity($rid, $volume) {
    if ($rid) {
        $sql = "
        SELECT COUNT(SSID) AS cnt, scheduleID.SHEID
        FROM scheduleID
        INNER JOIN schedule ON (schedule.SHEID=scheduleID.SHEID)
        WHERE schedule.rid='".$rid."'
        AND scheduleID.MID <> '-1'
        AND schedule.end > ".$GLOBALS['adodb']->DBTimestamp(time())."
        GROUP BY scheduleID.SHEID
        HAVING COUNT(SSID) > '".(int) $volume."'";
        $res = sql($sql);

        $max = 0;
        while($row = sqlget($res)) {
            if ($row['cnt'] > $max) $max = $row['cnt'];
        }

        return $max;
    }
}

?>