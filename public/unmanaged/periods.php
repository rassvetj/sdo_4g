<?php

require_once("1.php");
if (!$dean) login_error();

switch (trim($c)) {

case "":
   echo show_tb();
   echo ph(_("Установка типовых времен занятий"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['is_edited'] = $GLOBALS['controller']->checkPermission(PERIODS_PERM_EDIT);
   echo "
    <div style='padding-bottom: 5px;'>
        <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
        <div><a href='{$PHP_SELF}?c=edit&lid=0' style='text-decoration: none;'>"._("создать элемент сетки занятий")."</a></div>
    </div>
    <table width=100% class=main cellspacing=0>
    <tr><th>"._("Название")."</th><th>"._("Начало")."</th><th>"._("Окончание")."</th>";
   if ($GLOBALS['is_edited'])    
   echo "<th width='100px' align='center'>"._("Действия")."</th>";
   echo "</tr>";

   $res=sql("SELECT * FROM periods ORDER BY starttime","errGR73");
   while ($r=sqlget($res)) {
      $startimeH= intval($r[starttime]/60);
      $startimeI= $r[starttime]-$startimeH*60;
      if($startimeH<10) $startimeH="0".$startimeH;
      if($startimeI<10) $startimeI="0".$startimeI;

      $stoptimeH= intval($r[stoptime]/60);
      $stoptimeI= $r[stoptime]-$stoptimeH*60;
      if($stoptimeH<10) $stoptimeH="0".$stoptimeH;
      if($stoptimeI<10) $stoptimeI="0".$stoptimeI;

      //$count_hours = $r['count_hours'];

      echo "<tr>
            <td>";
      echo $r[name];
      echo "</td>
            <td>$startimeH:$startimeI</td>
            <td>$stoptimeH:$stoptimeI</td>";
      if ($GLOBALS['is_edited']) 
      echo "<td  align='center'>
            <a href=$PHP_SELF?c=edit&lid=$r[lid]$sess>".getIcon('edit',_('Редактировать элемент сетки занятий'))."</a>
            <a href=$PHP_SELF?c=delete&lid=$r[lid]$sess
            onclick=\"if (!confirm('"._("Вы действительно желаете удалить элемент сетки занятий?")."')) return false;\" >".getIcon("delete",_('Удалить элемент сетки занятий'))."</a></td>";
      echo "</tr>";
   }

   if (sqlrows($res)==0) echo "<tr><td colspan=5>"._("нет данных для отображения")."</td></tr>";
   else sqlfree($res);

   echo "</table>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   break;

case "delete":
   intvals("lid");
   if (!empty($lid)) {
      $res=sql("DELETE FROM periods WHERE lid='$lid'","errFM185");
      sqlfree($res);
   }
   refresh("$PHP_SELF?$sess");
break;

case "edit":
   $lid = intval($_GET['lid']);
   echo show_tb();
   echo ph(_("Редактирование сетки"));
      
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Редактирование элемента сетки занятий"));
   $GLOBALS['controller']->setHelpSection('edit');
   if ($lid) {
       $tmp="SELECT * FROM periods WHERE lid=$lid";
       $r=sql( $tmp );
       $res=sqlget( $r );
       sqlfree($r);
   }
   else {
       $res = array();
   }

   $tmp = "<form action=$PHP_SELF method=post>
   <input type=hidden name=c value=\"post_edit\">
   <input type=hidden name=lid value='$lid'>";
   $tmp.="<table width=100% class=main cellspacing=0>
   <tr>
   <td>"._("Название")."</td><td><input type='text' name=name value=\"".htmlspecialchars($res['name'])."\"></td>
   </tr>
   <tr>
   <td>"._("Начало")."</td><td>";

      $startimeH= intval($res[starttime]/60);
      $startimeI= $res[starttime]-$startimeH*60;

      $stoptimeH= intval($res[stoptime]/60);
      $stoptimeI= $res[stoptime]-$stoptimeH*60;

      //$count_hours = $res['count_hours'];

   $tmp.="<SELECT name=start_h id=\"start_h\" onChange='hoursCalculate();'>";

   for ($i=0; $i<=23; $i++) $tmp.="<option".($i==$startimeH?" selected":"")." value=\"$i\">$i";

   $tmp.="</select> "._("часов")." <SELECT name=\"start_m\" id=\"start_m\" onChange='hoursCalculate();'>";
   for ($i=0; $i<=59; $i++) $tmp.="<option".($i==$startimeI?" selected":"")." value=\"$i\">$i";
   $tmp.="</select> "._("мин.");

   $tmp.="</td>
   </tr>
   <tr>
   <td>"._("Окончание")."</td><td>";
   $tmp.="<SELECT name=stop_h id=\"stop_h\" onChange='hoursCalculate();'>";

   for ($i=0; $i<=23; $i++) $tmp.="<option".($i==$stoptimeH?" selected":"")." value=\"$i\">$i";

   $tmp.="</select> "._("часов")." <SELECT name=stop_m id=\"stop_m\" onChange='hoursCalculate();'>";
   for ($i=0; $i<=59; $i++) $tmp.="<option".($i==$stoptimeI?" selected":"")." value=\"$i\">$i";
   $tmp.="</select> "._("мин.");

   $tmp.="</td>
   </tr>
   <tr>
    <td>"._("Количество академических часов")."</td>
    <td>
    <input type='text' name='count_hourz' id='count_hourz' size='1' value='$count_hours' disabled='disabled' />
    <input type='hidden' name='count_hours' id=\"count_hours\" size='1' value='$count_hours' maxlength='2' />
    </td>
   </tr>
   </table>
   ";
   $tmp.="
<table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" width=\"100%\">
      <tr>
        <td align=\"right\" width=\"99%\">
        ".okbutton()."
        </td>
        <td align=\"right\" width=\"1%\">
        <div style='float: right;' class='button'><a href='javascript:history.back();'>"._("Отмена")."</a></div><input type='button' value='отмена' style='display: none;'/><div class='clear-both'></div>
        </td>
      </tr>
</table>
   </form>";
   $tmp.="<script type=\"text/javascript\">
          <!--
            function hoursCalculate(){
                var beginH = document.getElementById('start_h').value;
                var beginM = document.getElementById('start_m').value;
                var endH   = document.getElementById('stop_h').value;
                var endM   = document.getElementById('stop_m').value;
                var hoursCount       = document.getElementById('count_hourz');
                var hoursCountHidden = document.getElementById('count_hours');                

                var hours = Math.round(((Number(endH)*60+Number(endM))-(Number(beginH)*60+Number(beginM)))/45);
                if (hours < 0) hours = 0;
                
                hoursCount.value = hours;                
                hoursCountHidden.value = hours;                
            } 
            //-->       
          </script>   
   ";      
   echo $tmp;
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;

case "post_edit":
   $lid = intval($_POST['lid']);
   
   if (strlen(trim($name)) <= 0) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_('Вы не указали название сетки занятий'),JS_GO_URL, $sitepath.'periods.php?c=edit&lid='.(int) $lid);
       $GLOBALS['controller']->terminate();
       exit();       
   }
   
   $startime = $start_h*60+$start_m;
   $stoptime = $stop_h*60+$stop_m;
   if ($lid) {
       $rq="UPDATE periods
                   SET name=".$GLOBALS['adodb']->Quote($_POST['name']).",
                       starttime = '$startime',
                       stoptime = '$stoptime',
                       count_hours = '".intval($count_hours)."'
                   WHERE lid=$lid";
   }
   else {
       $rq="INSERT INTO periods (name, starttime, stoptime, count_hours) 
            VALUES (".$GLOBALS['adodb']->Quote($_POST['name']).", '$startime', '$stoptime', '".intval($count_hours)."')";       
   }
   $res=sql($rq,"errGR138");
   sqlfree($res);
   refresh("$PHP_SELF?$sess");
   return;
}

?>