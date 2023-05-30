<?
   define("MAX_SCHEDULE_GEN", 10);

   $vars=array("tests"=>"tests_testID",
               "formula_id"=>"formula_id",
               "module"=>"module_moduleID",
               'run' => 'run_id',
               "externalURLs"=>"externalURLs_urls",
               "redirectURL"=>"redirectURL_url",
               "sAddToAllnew"=>"sAddToAllnew",
               "collaborator"=>"collaborator",
               "formulagr_id" => "formulagr_id",
               "books" => "books",
               "chatApplet" => "chatApplet",
               "kpaint" => "kpaint",
               'penaltyFormula_id' => 'penaltyFormula_id',
              );

   if (!defined("dima")) exit("?");

   $sheid=intval($sheid);
   if (!$teach) login_error();

   $s[user][addtoved]=(isset($s[user][addtoved])) ? intval($s[user][addtoved]) : 0;
   if ($c=="add") $s[user][addtoved]=(isset($_GET['addtoved'])) ? intval($_GET['addtoved']) : 0;

   $CID=(isset($_GET['CID'])) ? intval($_GET['CID']) : 0;

require_once($GLOBALS['wwf'].'/lib/classes/CCourseAdaptor.class.php');

switch ($c) {

case "add":
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $GLOBALS['sitepath'] . "schedule.php4";

    // SAJAX BEGIN
    require_once($wwf.'/lib/sajax/Sajax.php');

    sajax_init();
    sajax_export("get_room_select","get_teacher_select"); // funcitions exists in func.lib.php4
    sajax_handle_client_request();
    $ajax_javascript = sajax_get_javascript();

    // SAJAX END

   $html=gf("$tm-2main.html");
   $html=str_replace("[self]",$self,$html);
   $wk=date("w",$day);
   $html=str_replace("[day]",$GLOBALS["nameweek"][$wk].date(" d.m.Y",$day),$html);
   $tmp="";
   //$res=sql("SELECT * FROM EventTools ORDER BY TypeID","err20");
   //while ($r=sqlget($res))
          //$tmp.="<option value=$r[TypeID]>$r[TypeName]";
   //$html=str_replace("[type]",$tmp,$html);
   $html=str_replace("[tweek]",$day,$html);
   $html=str_replace("[ref]",$ref,$html);
   $html=str_replace("[OKBUTTON]",okbutton(_("Далее") . " &#8594;"),$html);
   $html=str_replace("[CANCELBUTTON]",button(_("Отмена"),"","cancel","document.location.href=\"{$ref}\"; return false;"),$html);
   // cond nado sdelat выбрав (это я винца попил - регистры забываю переключать ляляля)
   // из списка уже сущ занятия а потом при наличии оценки за них публиковать
//////////////////////////
//   if ( isset($vedomost) ) {
//    echo "ВЕДОМОСТЬ";
   /*$rq="SELECT sheid, title, vedomost
           FROM  schedule
           WHERE schedule.CID=".$CID." AND schedule.vedomost=1";
           //fnGROUP BY begin";

    $res=sql($rq,"errLISTOFSHEID");

    while ($r=sqlget($res)) {
      $cond.="<option value=".$r['sheid'].">".$r['title']."</option>";

    }*/
    $a_schedules = array();
   $rq="SELECT sheid, title, CID
        FROM  schedule
        WHERE schedule.CID IN (";
   if (is_array($cids)) {
           foreach ($cids as $v) {
            $rq.="'".ad($v)."',";
            $a_schedules[$v] = array();
           }
   }
    $rq=substr($rq,0,-1).")";

    $res=sql($rq,"errLISTOFSHEID");

    while ($r=sqlget($res)) {
        $a_schedules[$r['CID']][$r['sheid']] = $r['title'];
    }

    $s_inv = "";
    foreach ($a_schedules as $key=>$value) {
        $s_inv .= "<div style=\"position:absolute; left:-200px; top:-200px; \" id='inner_select_{$key}' style=\"visibility:hidden\">";
        $s_inv .= "<select name=\"condition\" size=\"1\"  class='sel100'><option value=-1 selected>"._("неважно")."</option>";
        if(is_array($value) && count($value)) {
            foreach ($value as $key2 => $value2) {
                $s_inv .= "<option value={$key2}>{$value2}</option>";
            }
        }
        $s_inv .= "</select></div>";
    }

// ДАЛЕЕ НАДО
// хранитьв базе условие (id занятия) - вопрос где и в какой форме если потом условия будут сложные
// видимо в описании занятия
// публиковать в списке заданий у студента только те, у кого удовлетворяется условие
////////////////////
   $html=str_replace("[condition]",$cond,$html);  // заменим в шаблоне на указанный набор условий
   $html=str_replace("[invi]",$s_inv,$html);
   if ( ! $CID ) $html=str_replace("[HIDCLASS]","hidden2",$html);
//   }
   $tmp="";

   /*
   $rq="SELECT CID, Title
         FROM Courses
         WHERE CID IN (";
   if (is_array($cids)) {
           foreach ($cids as $v) $rq.="'".ad($v)."',";
   }
    $rq=substr($rq,0,-1).")";
    $rq.=" AND Status>0
          ORDER BY Title";
   */

   $events = array();
   $events[] = array('id'=>0, 'name'=>_('---необходимо выбрать элемент---'));
   $sql = "SELECT CID AS cid, Title AS title
           FROM Courses
           WHERE
               CID IN ('".join("','",$cids)."') AND
               Status > 0
           ORDER BY Title";
   $res=sql($sql);
   while($row = sqlget($res)) {
       if ($row['cid']) {
           $tmp[$row['cid']]['title']    = $row['title'];

           $sql = "SELECT EventTools.TypeName as name, EventTools.TypeID as id, eventtools_weight.weight, EventTools.Description
                   FROM EventTools
                   LEFT JOIN eventtools_weight
                   ON (eventtools_weight.event=EventTools.TypeID AND eventtools_weight.cid='".(int) $row['cid']."')
                   WHERE eventtools_weight.event IS NULL OR
                   (eventtools_weight.event IS NOT NULL AND eventtools_weight.weight<>-1)
                   ORDER BY EventTools.TypeName";
           $res2 = sql($sql);
           while($row2 = sqlget($res2)) {
               if (!in_array(array('id'=>$row2['id'], 'name'=>$row2['name']),$events)) {
                   $events[] = array('id'=>$row2['id'], 'name'=>$row2['name'], 'description' => $row2['Description']);
                   $tmp[$row['cid']]['events'][] = (int) (count($events)-1);
               } else {
                   $tmp[$row['cid']]['events'][] = (int) array_search(array('id'=>$row2['id'], 'name'=>$row2['name']),$events);
               }
           }
       }
   }

   $dynamicSelectJS = "<script type=\"text/javascript\">\n";
   $dynamicSelectJS .= "
   function getEventDescription(event) {
   ";
   $processedEvents = array();
   foreach($events as $v) {
       if (isset($processedEvents[$v['id']])) continue;
       $dynamicSelectJS .= "if (event == Number('".$v['id']."')) return String('".htmlspecialchars($v['description'])."');";
       $processedEvents[$v['id']] = $v['id'];
   }
   $dynamicSelectJS .= "
     return '';
     
   }
   ";

   $dynamicSelectJS .= "var a1 = [\n['--"._("необходимо выбрать элемент")."--',0,[]],";
   $i = 1;
   foreach($tmp as $k=>$v) {
       if ($i>1) $dynamicSelectJS.=",";
       $v['title'] = str_replace(array("\r\n", "\n"), '', addslashes($v['title']));
       $v['title'] = (strlen($v['title']) > 60) ? substr($v['title'], 0, 60) . "..." : $v['title'];
       $dynamicSelectJS.="['{$v[title]}','{$k}'";
       if (is_array($v['events']) && count($v['events'])) $dynamicSelectJS.=",[0,".join(',',$v['events'])."]";
       $dynamicSelectJS.="]\n";
       $i++;
   }

   $dynamicSelectJS .= "];\n";

   $dynamicSelectJS .= "var a2 = [\n";
   $i = 1;
   foreach($events as $v) {
       if ($i>1) $dynamicSelectJS.=",";
       $dynamicSelectJS.="['{$v[name]}','{$v[id]}']\n";
       $i++;
   }
   $dynamicSelectJS .= "];\n";


   $dynamicSelectJS .= "dynamicSelect('courses', 'events', a1, a2, 'get_teacher_select();get_room_select');\n";

   if (isset($_GET['course'])) $selectedCID = $_GET['course'];
   if (isset($_GET['CID'])) $selectedCID = $_GET['CID'];

   if ($selectedCID>0) {
       $dynamicSelectJS .= "
       if (elm = document.getElementById('courses')) {
           for(var i=0;i<elm.options.length;i++) {
               if (elm.options[i].value==".(int) $selectedCID.") {
                   elm.options[i].selected = true;
               }
           }
           elm.onchange();
       }";
   }
   $dynamicSelectJS .= "</script>\n";

   $html = str_replace("[dynamicSelectJS]",$dynamicSelectJS,$html);

   if ($CID) {
       $html=str_replace("[DISKURS]","disabled",$html);
       $html=str_replace("[HIDKURS]","<input type='hidden' name='kurs' value='".$CID."'>",$html);
   } else {
       $html=str_replace("[DISKURS]","",$html);
       $html=str_replace("[HIDKURS]","",$html);
   }
//////////////////
//////////////////

   //$rooms=getRooms( $CID?$CID:$kurs, false);

   //if( count ($rooms ) > 0 ){
     //$tmp="<select id='room' name='room' size='1' class='sel100' onChange=\"get_room_select();\">";
     $tmp = get_room_select($_GET['room']?$_GET['room']:0,0,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59'),0,false,0,sqlvalue("SELECT CID, Title FROM Courses WHERE CID IN ('".implode("','",$courses)."') ORDER BY Title ASC LIMIT 1"));
     $tmp = $tmp?$tmp:"";
/*
     $tmp.="<option value=-1>-- место --</options>";
     foreach( $rooms as $room )
      $tmp.="<option value=".$room[ rid ].">".$room[ name ]."</options>";
*/
     //$tmp.="</select>";
   //}else
      //$tmp="";
   //if (!$tmp) $ajax_javascript = '';
   $html=str_replace('[AJAX_JAVASCRIPT]', $ajax_javascript, $html);
   $html=str_replace("[ROOMS]",$tmp,$html);
   $html=str_replace("[TEACHERS]",get_teacher_select($CID),$html);
//////////////////// tooltips

   $html=str_replace("[TT_EVENTS]", $GLOBALS['tooltip']->display('schedule_edit_events'), $html);
   $html=str_replace("[TT_DATETYPE]", $GLOBALS['tooltip']->display('schedule_edit_datetype'), $html);
   $html=str_replace("[TT_PERIOD]", $GLOBALS['tooltip']->display('schedule_edit_period'), $html);
   $html=str_replace("[TT_CYCLE]", $GLOBALS['tooltip']->display('schedule_edit_cycle'), $html);
   $html=str_replace("[TT_ROOMS]", $GLOBALS['tooltip']->display('schedule_edit_rooms'), $html);


////////////////// СЕТКА ПАР

   $periods=getallperiods();
   $crntPeriodStartH = 0;
   $crntPeriodStartM = 0;
   $crntPeriodStopH  = 23;
   $crntPeriodStopM  = 59;

   if( count ( $periods ) > 0 ) {
     $tmp="<select name='period' id='period' size='1' class='sel100' onchange=\"javascript: if (this.value != -1) {document.getElementById('hh1').value = periodStartH[this.value]; document.getElementById('ii1').value = periodStartI[this.value];document.getElementById('hh2').value = periodStopH[this.value]; document.getElementById('ii2').value = periodStopI[this.value];} get_room_select(); get_teacher_select();\">";
     $tmp.="<option value=-1>---</options>";
     $strJs = "
             <script>
                        var periodStartH=Array();
                        var periodStopH=Array();
                        var periodStartI=Array();
                        var periodStopI=Array();
        ";
     foreach( $periods as $period ){
             $strStartH = (int)min2hours($period[ starttime ], "H");
             $strStartI = (int)min2hours($period[ starttime ], "I");
             $strStopH = (int)min2hours($period[ stoptime ], "H");
             $strStopI = (int)min2hours($period[ stoptime ], "I");
             $strJs .= "periodStartH[{$period[ lid ]}] = {$strStartH}\n";
             $strJs .= "periodStartI[{$period[ lid ]}] = {$strStartI}\n";
             $strJs .= "periodStopH[{$period[ lid ]}] = {$strStopH}\n";
             $strJs .= "periodStopI[{$period[ lid ]}] = {$strStopI}\n";
              $tmp.="<option value=".$period[ lid ]."";
              if (isset($_GET['period']) && ($period['lid'] == $_GET['period'])) {
                  $tmp .= " selected";
                  $crntPeriodStartH = (int) ($period['starttime']/60);
                  $crntPeriodStartM = $period['starttime'] - $crntPeriodStartH*60;
                  $crntPeriodStopH  = (int) ($period['stoptime']/60);
                  $crntPeriodStopM  = $period['stoptime'] - $crntPeriodStopH*60;
              }
              $tmp .= ">".$period[ name ].
      " (".min2hours($period[starttime])."..".min2hours($period[ stoptime ]).")</options>";
     }
      $tmp.="</select>";
      $strJs .= "</script>\n";
   }
   else
      $tmp="";
          $html=str_replace("[PERIODS]",$strJs.$tmp,$html);
  $dd=date("d",$day);
  $mm=date("m",$day);
  $yy=date("Y",$day);
  $tmp="";
  for ($i=1; $i<=31; $i++)
          $tmp.="<option".($i==$dd?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[DD1]",$tmp,$html);
  $html=str_replace("[DD2]",$tmp,$html);
  $tmp="";
  for ($i=1; $i<=12; $i++)
          $tmp.="<option".($i==$mm?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[MM1]",$tmp,$html);
  $html=str_replace("[MM2]",$tmp,$html);
  $tmp="";
  for ($i=2004; $i<=2030; $i++)
          $tmp.="<option".($i==$yy?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[YY1]",$tmp,$html);
  $html=str_replace("[YY2]",$tmp,$html);
  $tmp="";
  for ($i=0; $i<=23; $i++)
          $tmp.="<option value={$i}".($i==$crntPeriodStartH?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[hh1]",$tmp,$html);
  $tmp="";
  for ($i=0; $i<=23; $i++)
          $tmp.="<option value={$i}".($i==$crntPeriodStopH?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[hh2]",$tmp,$html);
  $tmp="";
  for ($i=0; $i<=59; $i++)
          $tmp.="<option value={$i}".($i==$crntPeriodStartM?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[mm1]",$tmp,$html);
  $tmp="";
  for ($i=0; $i<=59; $i++)
          $tmp.="<option value={$i}".($i==$crntPeriodStopM?" selected":"")." value=\"$i\">$i";
  $html=str_replace("[mm2]",$tmp,$html);
  $mhtml=create_new_html(0,0);
  $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);

    if ($GLOBALS['controller']->enabled) {
        $mhtml=words_parse($mhtml,$words);
        $mhtml=path_sess_parse($mhtml);
        $GLOBALS['controller']->setView('Document');
        $GLOBALS['controller']->captureFromReturn(CONTENT,$mhtml);
        $GLOBALS['controller']->setHeader(_("Добавление занятия"));
        $GLOBALS['controller']->setSubHeader(_("Шаг 1. Настройка общих свойств занятия"));
    }

  printtmpl($mhtml);
break;

case "gen_schedule":

        $colors = array('#FFFFFF','#EEEEEE');

        $GLOBALS['controller']->setHeader(_("Генерация занятий"));
        $GLOBALS['controller']->setHelpSection('gen');
        $html = ph("Автоматическое создание занятий");
        $html .= '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="form_gen" id="form_gen">';
        $html .= "
        <table width=100% class=main cellspacing=0>
        <tr>
        <th colspan=2>" . _('Свойства занятий') . "</th>
        </tr>
        <tr>
        <td valign='top' nowrap>"._("Дата начала"). "</td><td width='100%'><input type=radio name=timetype value='0' checked onClick=\"removeElem('relative_start'); removeElem('relative_stop'); putElement('absolute','table-row');\">&nbsp;"._("абсолютно"). "&nbsp;&nbsp;<input type=radio name=timetype value='1' onClick=\"removeElem('absolute');putElement('relative_start','table-row'); putElement('relative_stop','table-row');\">&nbsp;"._("относительно")."&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('schedule_edit_datetype') . "</td></tr>
        <tr id='absolute'><td valign=top>"._("Начало")." </td><td valign='top'>".schedule_add_dates()."</td></tr>
        <tr id='relative_start' style='display: none;'><td valign=top>"._("Начало на:"). " </td><td valign='top'><input type=text name=startday size=3 value='1'>&nbsp;"._("-ый день занятий"). "</td></tr>
        <tr id='relative_stop' style='display: none;'><td valign=top>"._("Продолжит.:"). " </td><td valign='top'><input type=text name=stopday size=3 value='1'>&nbsp;"._("(дн.)"). "</td></tr>
        </table>";
        $strOk = "
        <table border=\"0\" cellspacing=\"5\" cellpadding=\"0\" width=\"100%\">
        <tr>
        <td align=\"right\" width=\"99%\">
        <div style='float: right;' class='button'><a href='javascript:void(0);' onclick='window.close(); return false;'>"._("Отмена")."</a></div><input type='button' value='"._("отмена")."' style='display: none;'/><div class='clear-both'></div>
        </td>
        <td align=\"right\" width=\"1%\">
        ".okbutton()."
        </td>
        </tr>
        </table>";
        $arrModules = array();
        $arrTypes = array();

        $mods = CCourseAdaptor::getMeterials($CID);
        if (count($mods)) {
            foreach($mods as $mod_id => $mod_title) {
                $arrModules[$mod_id] = $mod_title;
            }
        }

/*
        $r=sql("SELECT mod_list.ModID as modid, mod_list.Title as title
                   FROM mod_list
                   WHERE mod_list.CID=$CID
                   ORDER BY mod_list.ModID","err41");
        while ($a = sqlget($r)) {
                $arrModules[$a['modid']] = $a['title'];
        }
*/


        $r=sql("SELECT TypeID as id, TypeName as title, Icon as icon
                   FROM EventTools
                   WHERE tools LIKE '%module%'
                   ORDER BY TypeID","err41");
        while ($a = sqlget($r)) {
                $arrTypes[$a['id']]['title'] = $a['title'];
                $arrTypes[$a['id']]['icon'] = $a['icon'];
        }
        if (count($arrModules) && count($arrTypes)) {
        $html .= '<TABLE width="100%" cellspacing="0" cellpadding="0">
                             <tr>
                                     <th bgcolor="#cccccc" width="100%" class="intermediate"><img src="images/spacer.gif" width="1" height="20" align="absmiddle">'._("Создать занятия на основе элементов программы курса").'</th>
                             </tr>
                        <tr>
                                <td>';
                $html .= '<table width="100%"  border="0" cellspacing="0" cellpadding="5">
                              <tr><td>&nbsp;</td>';
                foreach ($arrTypes as $typeId=>$val) {
                        $html .= '<td valign="top"><a href="javascript:void(0);" onclick="var i=0; while(elm=document.getElementById(\'txt_modid_arr_'.$typeId.'_\'+i)) {if (elm.value) elm.value=\'\'; else elm.value=\'1\';i++;}">' . (($val['icon'] == 'no_image.gif') ? $val['title'] : '<img border=0 src="images/events/'.$val['icon'].'" alt="'.$val['title'].'">') .'</a></td>';
                }
                $html .= '</tr>';
                unset($i);
                $color = 0;
                foreach ($arrModules as $modId => $modTitle) {
                        $html .= '<tr bgcolor="'.$colors[$color].'"><td width="100%"><b>'.$modTitle.'</b></td>';
                        foreach ($arrTypes as $typeId => $typeTitle) {
                                $html .= '<td nowrap><input id="txt_modid_arr_'.$typeId.'_'.(int) $i[$typeId].'" name="txt_modid_arr['.$modId.']['.$typeId.']" type="text" size="1" title="'._("количество занятий").'">&nbsp;' . ((!$flag_module++) ? $GLOBALS['tooltip']->display('schedule_gen') : '') . '</td>';
                                $i[$typeId] = (int) $i[$typeId]+1;
                        }
                        $color++;
                        if ($color > 1) $color = 0;
                        $html .= '</tr>';
                }
                $html .= '</table>';
        $html .= '</td></tr></TABLE>';
        }
        $arrTests = array();
        $arrTypes = array();

        $mods = CCourseAdaptor::getTasks($CID);
        if (count($mods)) {
            foreach($mods as $mod_id => $mod_title) {
                $arrTests[$mod_id] = $mod_title;
            }
        }
/*
        $r=sql("SELECT tid, title
                   FROM test
                   WHERE cid=$CID AND status>0
                   ORDER BY tid","err41");
        while ($a = sqlget($r)) {
                $arrTests[$a['tid']] = $a['title'];
        }
*/
        $r=sql("SELECT TypeID as id, TypeName as title, Icon as icon
                   FROM EventTools
                   WHERE tools LIKE '%tests%'
                   ORDER BY TypeID","err41");
        while ($a = sqlget($r)) {
                $arrTypes[$a['id']]['title'] = $a['title'];
                $arrTypes[$a['id']]['icon'] = $a['icon'];
        }
        if (count($arrTests) && count($arrTypes)) {
        $html .= '<table width=100% class=main cellspacing=0>
                             <tr>
                                     <th width="100%" class="intermediate"><img src="images/spacer.gif" width="1" height="20" align="absmiddle">'._("Создать занятия на основе заданий").'</th>
                             </tr>
                        <tr>
                                <td>';
                $html .= '<table width="100%"  border="0" cellspacing="0" cellpadding="5">
                              <tr bgcolor="#FFFFFF"><td>&nbsp;</td>';
                foreach ($arrTypes as $typeId=>$val) {
                        $html .= '<td valign="top"><a href="javascript:void(0);" onclick="var i=0; while(elm=document.getElementById(\'txt_tid_arr_'.$typeId.'_\'+i)) {if (elm.value) elm.value=\'\'; else elm.value=\'1\';i++;}">' . (($val['icon'] == 'no_image.gif') ? $val['title'] : '<img border=0 src="images/events/'.$val['icon'].'" alt="'.$val['title'].'">') . '</a></td>';
                }
                $html .= '</tr>';
                unset($i);
                $color = 0;
                foreach ($arrTests as $tid => $testTitle) {
                        $html .= '<tr bgcolor="'.$colors[$color].'"><td width="100%"><b>'.$testTitle.'</b></td>';
                        foreach ($arrTypes as $typeId => $typeTitle) {
                                $html .= '<td nowrap><input id="txt_tid_arr_'.$typeId.'_'.(int) $i[$typeId].'" name="txt_tid_arr['.$tid.']['.$typeId.']" type="text" size="1" title="'._("количество занятий").'">&nbsp;' . ((!$flag_test++) ? $GLOBALS['tooltip']->display('schedule_gen') : '') . '</td>';
                                $i[$typeId] = (int) ($i[$typeId]+1);
                        }
                        $color++;
                        if ($color > 1) $color = 0;
                        $html .= '</tr>';
                }
                $html .= '</table>';
        $html .= '</td></tr></TABLE>';
        }
        $arrTypes = array();
        $r=sql("SELECT TypeID as id, TypeName as title, Icon as icon
                   FROM EventTools
                   WHERE (tools NOT LIKE '%module%') AND (tools NOT LIKE '%tests%')" ,"err41");
        while ($a = sqlget($r)) {
                $arrTypes[$a['id']]['title'] = $a['title'];
                $arrTypes[$a['id']]['icon'] = $a['icon'];
        }
        if (count($arrTypes)) {
                $html .= '<table width=100% class=main cellspacing=0>
                                 <tr>
                                         <th width="100%" class="intermediate"><img src="images/spacer.gif" width="1" height="20" align="absmiddle">'._("Создать занятия других типов").'</th>
                                 </tr>
                            <tr>
                                    <td>';
                $html .= '<table width="100%"  border="0" cellspacing="0" cellpadding="5">
                                 <tr bgcolor="#FFFFFF">';
                foreach ($arrTypes as $typeId=>$val) {
                        $html .= '<td valign="top"><a href="javascript:void(0);" onclick="var i=0; while(elm=document.getElementById(\'txt_misc_arr_'.$typeId.'_\'+i)) {if (elm.value) elm.value=\'\'; else elm.value=\'1\';i++;}">' . (($val['icon'] == 'no_image.gif') ? $val['title'] : '<img border=0 src="images/events/'.$val['icon'].'" alt="'.$val['title'].'">') . '</a></td>';
                }
                $html .= '</tr><tr bgcolor="#FFFFFF">';
                unset($i);
                foreach ($arrTypes as $typeId => $val) {
                        $html .= '<td valign="top"><input id="txt_misc_arr_'.$typeId.'_'.(int) $i[$typeId]++.'" name="txt_misc_arr['.$typeId.']" type="text" size="1" title="'._("количество занятий").'"></td></td>';
                }
                $html .= '</tr>';
                $html .= '</table></td></tr>';
                $html .= '</TABLE>';
        }

        if (isset($_GET['CID'])) {
                $sqlSelectStudents = "SELECT * FROM     People, Students
                                      WHERE
                                              People.MID=Students.MID AND
                                        Students.CID='$CID' ORDER BY People.LastName, People.FirstName";
                $res = sql($sqlSelectStudents,"err42");
                $tmp = gf("$tm-3tr-people.html");
                while ($r=sqlget($res)) {
                        $ppl .= str_replace(
                        array("[mid]","[login]","[lastname]","[firstname]","[mail]","[checked]"),
                        array($r[MID],$r[Login],$r[LastName],$r[FirstName],$r[EMail],"checked"),
                        $tmp);
                }
                sqlfree($res);
                $html .= gf("$tm-3main-gen.html");
                $html = str_replace("[GROUP_SEL]",selGr($CID),$html);
                $html = str_replace("[TT_GRADE]",$GLOBALS['tooltip']->display('schedule_edit_grade'), $html);
                $html = str_replace("[GROUP_ARRAY]",grArray($CID),$html);
                $html = str_replace("[tr-people]",$ppl,$html);
        }

                $html .= '<input name="CID" type="hidden" value="'.$CID.'">';
                $html .= '<input name="c" type="hidden" id="c" value="gen_make"><br>'.$strOk.'</form>';

                $mhtml=create_new_html(0,0);
                $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);
    if ($GLOBALS['controller']->enabled) {
        $mhtml=words_parse($mhtml,$words);
        $mhtml=path_sess_parse($mhtml);
        $GLOBALS['controller']->setView('DocumentPopup');
        $GLOBALS['controller']->captureFromReturn(CONTENT,$mhtml);
        $GLOBALS['controller']->setHeader(_("Генерация занятий"));
    }
                printtmpl($mhtml);
break;

case "gen_make":
    //$date_offset = time() - 24*60*60;
    $date1 = mktime("0", "0", "0", $_POST['mm'], $_POST['dd'], $_POST['yyyy']);
    $date2 = mktime("23", "59", "59", $_POST['mm'], $_POST['dd'], $_POST['yyyy']);
    //$date2 = $date1 + 23*60*60+59*60+59;

    $_sql_fields = '';
    $_sql_values = '';
    if ($_POST['timetype']==1) {
        $startday=(intval($_POST['startday'])-1)*24*60*60 + 1; // В СЕКУНДАХ
        $stopday=$startday + (intval($_POST['stopday'])-1)*24*60*60 - 1 + 23*60*60 + 59*60 + 59; // В СЕКУНДАХ
        $_sql_fields = ', startday, stopday';
        $_sql_values = ", '".(int) $startday."', '".(int) $stopday."'";
    }


                $begin_schedule = date("Y-m-d 00:00:00", time() - 24*60*60);
                $end_schedule = date("Y-m-d 23:59:59", time() - 24*60*60);

                if (is_array($_POST['txt_modid_arr'])) {
                $arrTmp = $_POST['txt_modid_arr'];
                $arrTypes = array_shift($arrTmp);
                if (is_array($arrTypes)) {
                        foreach ($arrTypes as $typeid => $tmp) {
                                $r = sql("SELECT * FROM EventTools WHERE TypeID='{$typeid}'");
                                if ($a = sqlget($r)) $arrTypes[$typeid] = $a;
                        }
                }
                foreach ($_POST['txt_modid_arr'] as $modid => $arrMod) {
                        if (is_array($arrMod)) {
                                foreach ($arrMod as $typeid => $num) {
                                        $intVal = intval($num);
                                        if (($intVal > 0) && ($intVal < MAX_SCHEDULE_GEN)) {
                                                $intSchCount = 0;
                                                for ($i = 0; $i < $intVal; $i++) {
                                                        $intSchCount++;

                                                        $r=sql("SELECT title
                                                                FROM organizations
                                                                WHERE oid=$modid","err41");
                                                        $item = sqlget($r);
                                                        $title = $item['title'];


                                                        $r = sql("
                                                                INSERT
                                                                INTO schedule (title, begin, end, createID, typeID, vedomost, CID, CHID, timetype, isgroup $_sql_fields)
                                                                values (
                                                                ".$adodb->Quote($title).",
                                                                ".$adodb->DBTimeStamp($date1).",
                                                                ".$adodb->DBTimeStamp($date2).",
                                                                '{$mid}',
                                                                '{$typeid}',
                                                                '{$_POST['vedomost']}',
                                                                '{$_POST['CID']}',
                                                                '0',
                                                                '".(int) $_POST['timetype']."',
                                                                '0' $_sql_values
                                                                )
                                                        ");
                                                        $intSheid = sqllast();
                                                        if (is_array($_POST['select'])) {
                                                                foreach ($_POST['select'] as $intMid) {
                                                                        $r = sql("
                                                                        INSERT
                                                                        INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                                        VALUES
                                                                        ('{$intSheid}',
                                                                        '{$intMid}',
                                                                        '0',
                                                                        '-1',
                                                                        'module_moduleID={$modid};')
                                                                        ","err");
                                                                }
                                                        }
                                                        else {
                                                                $query = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                                      VALUES ('{$intSheid}','{$s['mid']}','0','-1','module_moduleID={$modid};')";
                                                                $result = sql($query,"err45");

                                                        }
                                                        $query = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                                  VALUES ('{$intSheid}','-1','0','-1','module_moduleID={$modid};')";
                                                        $result = sql($query,"err45");
                                                }
                                        }
                                }
                        }
                }
        }
        if (is_array($_POST['txt_tid_arr'])) {
                 $arrTmp = $_POST['txt_tid_arr'];
             $arrTypes = array_shift($arrTmp);
             if (is_array($arrTypes)) {
                     foreach ($arrTypes as $typeid => $tmp) {
                        $r = sql("SELECT * FROM EventTools WHERE TypeID='{$typeid}'");
                    if ($a = sqlget($r)) $arrTypes[$typeid] = $a;
                }
             }
             foreach ($_POST['txt_tid_arr'] as $tid => $arrTid) {
                        if (is_array($arrTid)) {
                                foreach ($arrTid as $typeid => $num) {
                                        $intVal = intval($num);
                                        if (($intVal > 0) && ($intVal < MAX_SCHEDULE_GEN)) {
                                                $intSchCount = 0;
                                                for ($i = 0; $i < $intVal; $i++) {
                                                        $intSchCount++;

                                                       $r=sql(" SELECT title
                                                                FROM test
                                                                WHERE tid=$tid","err41");
                                                        $item = sqlget($r);
                                                        $title = $item['title'];

                                                       $r = sql("
                                                                INSERT
                                                                INTO schedule (title, begin, end, createID, typeID, vedomost, CID, timetype, isgroup, CHID $_sql_fields)
                                                                values (
                                                                ".$adodb->Quote($title).",
                                                                ".$adodb->DBTimeStamp($date1).",
                                                                ".$adodb->DBTimeStamp($date2).",
                                                                '{$mid}',
                                                                '{$typeid}',
                                                                '{$_POST['vedomost']}',
                                                                '{$_POST['CID']}',
                                                                '".(int) $_POST['timetype']."',
                                                                '0',
                                                                '0' $_sql_values)
                                                        ");
                                                        $intSheid = sqllast();
                                                        if (is_array($_POST['select'])) {
                                                                foreach ($_POST['select'] as $intMid) {
                                                                        $r = sql("
                                                                        INSERT
                                                                        INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                                        VALUES
                                                                        ('{$intSheid}',
                                                                        '{$intMid}',
                                                                        '0',
                                                                        '-1',
                                                                        'tests_testID={$tid};')
                                                                        ","err");
                                                                }
                                                        }
                                                        else {
                                                                $query = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                                      VALUES ('{$intSheid}','{$s['mid']}','0','-1','tests_testID={$tid};')";
                                                                $result = sql($query,"err45");

                                                        }

                                                        $query = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                                  VALUES ('{$intSheid}','-1','0','-1','tests_testID={$tid};')";
                                                        $result = sql($query,"err45");
                                                }
                                        }
                                }
                        }
                }
        }
        if (is_array($_POST['txt_misc_arr'])) {
                 $arrTypes = $_POST['txt_misc_arr'];
             if (is_array($arrTypes)) {
                     foreach ($arrTypes as $typeid => $tmp) {
                        $r = sql("SELECT * FROM EventTools WHERE TypeID='{$typeid}'");
                    if ($a = sqlget($r)) $arrTypes[$typeid] = $a;
                }
             }
             foreach ($_POST['txt_misc_arr'] as $type_id => $num) {
                        $tmp_tools = explode(",", $arrTypes[$type_id]['tools']);
                        $str_toolParams = "";
                        if(is_array($tmp_tools)) {
                                foreach($tmp_tools as $tool) {
                                        $str_toolParams .= $vars[trim($tool)]."=;";
                                }
                        }
                        $str_toolParams = trim($str_toolParams, ",");
                                  if ($num > 0) {
                               $intSchCount = 0;
                                             for ($i = 0; $i < $num; $i++) {
                                       $intSchCount++;
                                $r = sql("INSERT
                                          INTO schedule (title, begin, end, createID, typeID, vedomost, CID, timetype, isgroup, CHID $_sql_fields)
                                          VALUES (
                                          '{$arrTypes[$type_id]['TypeName']} №{$intSchCount}',
                                          ".$adodb->DBTimeStamp($date1).",
                                          ".$adodb->DBTimeStamp($date2).",
                                          '{$mid}',
                                          '{$type_id}',
                                          '{$_POST['vedomost']}',
                                          '{$_POST['CID']}',
                                          '".(int) $_POST['timetype']."',
                                          '0',
                                          '0' $_sql_values)");
                                $intSheid = sqllast();
                                if (is_array($_POST['select'])) {
                                        foreach ($_POST['select'] as $intMid) {
                                            $r = sql("INSERT
                                                  INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                                  VALUES
                                                  ('{$intMid}',
                                                  '{$s['mid']}',
                                                  '0',
                                                  '-1',
                                                  '$str_toolParams')
                                                  ","err");
                                    }
                                }
                                else {
                                        $query = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                              VALUES ('{$intSheid}','{$s['mid']}','0','-1','$str_toolParams')";
                                    $result = sql($query,"err45");
                                }
                                        $query = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams)
                                              VALUES ('{$intSheid}','-1','0','-1','$str_toolParams')";
                                    $result = sql($query,"err45");

                               }
                        }
             }
        }
        $shewords=loadwords("schedule-words.html");
        $html=loadtmpl("schedule-okc.html");
        $mhtml=create_new_html(0,0);
        $words['ok']=$shewords[14];
        $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);
    if ($GLOBALS['controller']->enabled) {
        $mhtml=words_parse($mhtml,$words);
        $mhtml=path_sess_parse($mhtml);
        $GLOBALS['controller']->setView('Document');
        //$GLOBALS['controller']->captureFromReturn(CONTENT,$mhtml);
        $GLOBALS['controller']->setMessage(_('Новые занятия успешно добавлены'),JS_CLOSE_SELF_REFRESH_OPENER);
        $GLOBALS['controller']->terminate();
        exit();
    }
        printtmpl($mhtml);

break;
case "add2":
	
    // SAJAX BEGIN
    require_once($wwf.'/lib/sajax/Sajax.php');

    $GLOBALS['controller']->setHelpSection('add2');
    sajax_init();
    sajax_export("edit_schedule_unused_people", 'webinar_getmaterials');
    sajax_handle_client_request();
    $GLOBALS['sajax_remote_uri'] = $GLOBALS['sitepath'].'schedule.php4?c=add2';
    $ajax_javascript = sajax_get_javascript();

    // SAJAX END
   $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

   $type=intval($type);
   $kurs=intval($kurs);
   if (debug) {
       echo "<li>TYPE=$type, CID=$kurs";
   }

   /**
    * Проверка дат начала занятий, если даты не соответствуют датам начала и конца курса =>
    * неверные даты. Проверка на статус курса.
    */
   $sql = "SELECT Status, cBegin, cEnd, sequence FROM Courses WHERE CID='".(int) $kurs."'";
   $res = sql($sql);

   if ($row = sqlget($res)) {
       // Проверка статуса курса
       if ($row['Status'] < 1) {
		   $GLOBALS['controller']->setView('Document');
   	       $GLOBALS['controller']->setMessage(
   	           "Доступ к курсу запрещён. Курс является закрытым.",
   	           JS_GO_URL,
   	           'javascript:window.close();'
   	       );
   	       $GLOBALS['controller']->terminate();
   	   	   exit();
       }
       // Проверка режима прохождения курса
       if ($row['sequence']) {
		   $GLOBALS['controller']->setView('Document');
   	       $GLOBALS['controller']->setMessage(
   	           _("Создание занятия на основе данного курса невозможно, необходимо изменить режим прохождения в свойствах курса."),
   	           JS_GO_BACK,
   	           'javascript:window.close();'
   	       );
   	       $GLOBALS['controller']->terminate();
   	   	   exit();
       }
       // Проверка дат занятий и дат курса
       $bDate = date('Y-m-d H:i:s',mktime($_POST['hh1'],$_POST['mm1'],0,$_POST['MM1'],$_POST['DD1'],$_POST['YY1']));
       $eDate = date('Y-m-d H:i:s',mktime($_POST['hh2'],$_POST['mm2'],0,$_POST['MM2'],$_POST['DD2'],$_POST['YY2']));
       if (($bDate < $row['cBegin'].' 00:00:00') || ($eDate > $row['cEnd'].'23:59:59') || $bDate > $eDate) {
		   $GLOBALS['controller']->setView('Document');
		   $GLOBALS['controller']->setMessage(
   	           ($bDate > $eDate)?_('Начало занятия позже конца'):_("Даты занятия выходят за рамки начала и окончания курса"),
   	           JS_GO_URL,
   	           $sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs : '&amp;course='.(int) $kurs)
   	       );
   	       $GLOBALS['controller']->terminate();
   	   	   exit();
       }
   }

   //Проверка типа занятия
   if (!$type) {
       $GLOBALS['controller']->setView('Document');
       $GLOBALS['controller']->setMessage(
           _("Вы не выбрали тип создаваемого занятия"),
           JS_GO_URL,
           $sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs : '&amp;course='.(int) $kurs)
       );
       $GLOBALS['controller']->terminate();
       exit();
   }
   if (sqlvalue("SELECT status FROM Courses WHERE cid=$kurs","err40.1")<1)
       exit("Access denied: this course closed (CID=$kurs).");
   $res=sql("SELECT * FROM EventTools WHERE TypeID=$type","err40");
   if (!sqlrows($res)) {
        		//exit("unknown type");
		       	$GLOBALS['controller']->setView('Document');
       //$GLOBALS['controller']->setMessage(_("На данном курсе невозможно создать занятие данного типа"), JS_GO_URL,'javascript:window.close();');
       $GLOBALS['controller']->setMessage(
           _("На данном курсе невозможно создать занятие данного типа"),
           JS_GO_URL,
           $sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs : '&amp;course='.(int) $kurs)
       );
   	   			$GLOBALS['controller']->terminate();
   	   			exit();
   }
   $r=sqlget($res);
   sqlfree($res);
   $tools=array();
   foreach (explode(",",$r[tools]) as $v) {
      $tools[trim($v)]=$v;
   }
   if (debug) echo "<li>".implode(" - ",$tools);
   $main="";
   $strGroupSuffix = ($_POST['group']) ? "-group" : "";
   $vname="externalURLs";
   if (isset($tools[$vname])) {
      $html=gf("$tm-3$vname$strGroupSuffix.html");
      $main.=str_replace(
         array("[toolname]"),
         array($vname),
         $html);
   }
   
   // RUN BEGIN
   $vname="run";
   if (isset($tools[$vname])) {
      $html=gf("$tm-3$vname$strGroupSuffix.html");

      $mods = CCourseAdaptor::getRuns($kurs);
      $tmp = '';
      if (count($mods)) {
          foreach($mods as $mod_id => $mod_title) {
              $tmp.="<option value=\"{$mod_id}\">".htmlspecialchars($mod_title, ENT_QUOTES);
          }
      } else {
          $GLOBALS['controller']->setView('Document');
          //$GLOBALS['controller']->setMessage(_("На данном курсе нет ни одной внешней программы"), JS_GO_URL,'javascript:window.close();');
          $GLOBALS['controller']->setMessage(
             _("На данном курсе нет ни одной внешней программы"),
             JS_GO_URL,
             $sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs : '&amp;course='.(int) $kurs)
          );
          $GLOBALS['controller']->terminate();
          exit();
      }
      $main.=str_replace(
         array("[toolname]","[select]"),
         array($vname,$tmp),
         $html);
   }
   // RUN END

   $vname="module";
   if (isset($tools[$vname])) {
      $html=gf("$tm-3$vname$strGroupSuffix.html");


      //$mods = CCourseAdaptor::getMeterials($kurs);
      $tmp = '';
      $sql = "SELECT oid FROM organizations WHERE cid = '".(int) $kurs."' LIMIT 1";
      $res = sql($sql);
      if (sqlrows($res)) {
//          foreach($mods as $mod_id => $mod_title) {
//              $tmp.="<option value=\"{$mod_id}\">".htmlspecialchars($mod_title, ENT_QUOTES);
//          }

          $_smarty = new Smarty_els();

          $_smarty->assign('list_name','form[module]');
          $_smarty->assign('container_name','container_module');
          $_smarty->assign('list_extra'," style=\"width: 300px;\" ");
          $_smarty->assign('list_default_value',0);
          $_smarty->assign('list_selected', 0);
          $_smarty->assign('url',$GLOBALS['sitepath'].'course_structure_toc_xml.php?cid='.$kurs);
          $tmp .= $_smarty->fetch('control_treeselect.tpl');

      } else {
          $GLOBALS['controller']->setView('Document');
          //$GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного учебного модуля"), JS_GO_URL,'javascript:window.close();');
          $GLOBALS['controller']->setMessage(
             _("На данном курсе нет ни одного учебного модуля"),
             JS_GO_URL,
             $sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs : '&amp;course='.(int) $kurs)
          );
          $GLOBALS['controller']->terminate();
          exit();
      }

/*
      $res=sql("SELECT mod_list.ModID as modid, mod_list.Title as title
                FROM mod_list
                WHERE mod_list.CID='$kurs'","err41");

      if (!sqlrows($res)) {
            	//exit("unknown mod_list");
		       	$GLOBALS['controller']->setView('DocumentBlank');
   	   			$GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного учебного модуля"), JS_GO_URL,'javascript:window.close();');
   	   			$GLOBALS['controller']->terminate();
   	   			exit();
      }
      $tmp="";
      while ($r=sqlget($res)) $tmp.="<option value=$r[modid]>$r[title]";
      sqlfree($res);
*/
      $main.=str_replace(
         array("[toolname]","[select]", "[TT_MODULE]"),
         array($vname,$tmp, $GLOBALS['tooltip']->display('schedule_edit_module')),
         $html);
   }
   $vname="tests";      // ЕСЛИ В ЗАДАНИ ЕСТЬ ТЕСТЫ
   if (isset($tools[$vname])) {
      $html=gf("$tm-3$vname$strGroupSuffix.html");

      $mods = CCourseAdaptor::getTasks($kurs);
      $tmp = '';
      if (count($mods)) {
          foreach($mods as $mod_id => $mod_title) {
              $tmp.="<option value=\"{$mod_id}\">".htmlspecialchars($mod_title, ENT_QUOTES);
          }
      } else {
          $GLOBALS['controller']->setView('Document');
          //$GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного задания"), JS_CLOSE_SELF_REFRESH_OPENER, 'javascript:void(0);');
          $GLOBALS['controller']->setMessage(
             _("На данном курсе нет ни одного задания"),
             JS_GO_URL,
             $sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs : '&amp;course='.(int) $kurs)
          );
          $GLOBALS['controller']->terminate();
          exit();
      }

/*      $res=sql("SELECT tid , title
                FROM test
                WHERE test.cid=$kurs","err42");
      if (!sqlrows($res)) {
      			//exit("unknown test");
		       	$GLOBALS['controller']->setView('DocumentBlank');
   	   			$GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного задания"), JS_GO_URL,'javascript:window.close();');
   	   			$GLOBALS['controller']->terminate();
   	   			exit();
      }
      $tmp="";
      while ($r=sqlget($res)) $tmp.="<option value=$r[tid]>$r[title]";
      sqlfree($res);
*/
      /////////////////////// добавить выбор формулы
      $res=sql("SELECT * FROM formula WHERE (CID='$kurs' OR CID=0) AND type=1","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
      $strArrayFormulas = (isset($_POST['isgroup'])) ? "[]" : "";
      $str="<select onChange=\"var elm = jQuery('#penaltyFormula').get(0); if (elm && (this.value == 0)) elm.disabled = true; else elm.disabled = false;\" name='form[formula_id]{$strArrayFormulas}'><option value=0 selected>---</option>";     // ПОДКЛЮЧЕНИЕ ОБРАБОТКИ ОЦЕНОК АВТОМАТИЧЕСКИ ФОРМУЛОЙ
      while ($r = sqlget($res)) {                                    //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
        $str.="<option value=".$r[id];
        if( $method_id == $r[id]) $str.=" selected";     // ПОДКЛЮЧЕНИЕ ОБРАБОТКИ ОЦЕНОК АВТОМАТИЧЕСКИ ФОРМУЛОЙ
        $str.=">".$r[name]."</option>";
      }
      $str.="</select>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('schedule_formula_grade');

      $res = sql("SELECT * FROM formula WHERE type = 3 AND (CID = $kurs OR CID = 0)");
      $str_gr = "<select name='form[formulagr_id]'><option value=0 selected> --- </option>";
      while($r = sqlget($res)) {
            $str_gr .= "<option value=".$r[id].">".$r[name]."</option>";
      }
      $str_gr .= "</select>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('schedule_formula_groups');
      sqlfree($res);

      $res = sql("SELECT * FROM formula WHERE type = 5 AND (CID = $kurs OR CID = 0)");
      $penaltyFormulaSelect = "<select disabled id=\"penaltyFormula\" name='form[penaltyFormula_id]'><option value=0 selected> --- </option>";
      while($r = sqlget($res)) {
            $penaltyFormulaSelect .= "<option value=".$r[id].">".$r[name]."</option>";
      }
      $penaltyFormulaSelect .= "</select>&nbsp;&nbsp;" . $GLOBALS['tooltip']->display('schedule_formula_penalty');
      sqlfree($res);

      $main.=str_replace(
         array("[toolname]", "[select]", "[formula]", "[grformula]", '[penaltyFormula]'),
         array($vname, $tmp, $str, $str_gr, $penaltyFormulaSelect),
         $html);
   }

   $vname="collaborator";
   if(isset($tools[$vname])) {
      $html = gf("$tm-3$vname$strGroupSuffix.html");
      $res=sql("SELECT MID,LastName,FirstName, Patronymic FROM People ORDER BY LastName", "errselectmids");
      $str="<option value=0 selected>-- "._("выберите")." --</option>";
      while($r = sqlget($res)) {
            if (($GLOBALS['s']['perm']!=3) || $peopleFilter->is_filtered($r['MID']))
            $str.="<option value=".$r['MID'].">".$r['LastName']." ".$r['FirstName']." ".$r['Patronymic']."</option>\n";
      }
      $str.="";
      sqlfree($res);
      $main.=str_replace(
             array("[toolname]", "[select]"),
             array($vname, $str), $html);
   }
   
   $vname = 'webinar';
   if (isset($tools[$vname])) {
       $html = gf("$tm-3$vname$strGroupSuffix.html");
       
	   $smarty = new Smarty_els();
	   $smarty->assign('list1_options', webinar_getmaterials($kurs, ''));
	   $smarty->assign('list2_options', '');
	   $smarty->assign('list1_name','webinar_list1');
	   $smarty->assign('list2_name','webinar_list2');
       $smarty->assign('button_all_click',"if (elm = document.getElementById('webinar_editbox_search')) elm.value='*'; get_webinar_list_options('*');");
       $smarty->assign('editbox_search_name','webinar_editbox_search');
	   $smarty->assign('editbox_search_text','');
	   $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_webinar_list_options(\''+this.value+'\');',1000);");
	   $smarty->assign('list1_container_id','webinar_list1_container');
	   $smarty->assign('list2_container_id','webinar_list2_container');
	   //$smarty->assign('list3_name','list3');
	   //$smarty->assign('list3_options',selGrved($rr['CID'],0));
	   //$smarty->assign('list3_change',"if (elm = document.getElementById('editbox_search')) get_list_options(elm.value);");
	   //$smarty->assign('list1_list2_click','get_room_select();');
	   //$smarty->assign('list2_list1_click','get_room_select();');
	   $smarty->assign('javascript', "
	           function show_webinar_list_options(html) {
	               var elm = document.getElementById('webinar_list1_container');
	               if (elm) elm.innerHTML = '<select size=10 id=\"webinar_list1\" name=\"webinar_list1[]\" multiple style=\"width:100%\">'+html+'</select>';
	               //prepare_options('list1', dropped, assigned);
	           }
	
	           function get_webinar_list_options(str) {
	               var current = 0;	               
	
                   var select = document.getElementById('webinar_editbox_search');
	               if (select) current = select.value;	
	
	               var elm = document.getElementById('webinar_list1_container');
	               if (elm) elm.innerHTML = '<select size=10 id=\"webinar_list1\" name=\"webinar_list1[]\" multiple style=\"width:100%\"><option>"._("Загружаю данные...")."</option></select>';
	
	               x_webinar_getmaterials('$kurs', str, show_webinar_list_options);
	           }		           
	   ");
	   $str = $smarty->fetch('control_list2list.tpl');       
       
       $main.=str_replace(
             array("[toolname]", "[select]", '[TT_MODULE]'),
             array($vname, $str, ''), $html);
       
   }
      
   $vname='connectpro';
   if (isset($tools[$vname])) {
       $html = gf("$tm-3$vname$strGroupSuffix.html");
       
       require_once($GLOBALS['wwf'].'/lib/classes/Curl.class.php');
       require_once($GLOBALS['wwf'].'/lib/classes/Connect.class.php');
       
       try {
           $cp = new ConnectProXMLApiAdapter(CONNECT_PRO_HOST, CONNECT_PRO_ADMIN_LOGIN, CONNECT_PRO_ADMIN_PASSWORD);
           $templates = $cp->getTemplatesList();
       } catch(Exception $e) {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_("Ошибка создания занятия Adobe Connect Pro.").' '.htmlspecialchars($e->getMessage()), JS_GO_URL, $GLOBALS['sitepath']);
            $GLOBALS['controller']->terminate();
            exit();   
       }
       
       $str="<select name=\"template\"><option value=0 selected> "._("Без шаблона")." </option>"; 
       if (is_array($templates) && count($templates)) {
       	   foreach($templates as $template) {
       	   	   $str .= "<option value=\"{$template->sco_id}\"> {$template->name}</option>";
       	   }
       }
       $str .= "</select>";       
       
       $main.=str_replace(
             array("[toolname]", "[select]", '[TT_MODULE]'),
             array($vname, $str, ''), $html);
   }

   $vname="redirectURL";

   if (isset($tools[$vname])) {
      $html=gf("$tm-3$vname$strGroupSuffix.html");
      $main.=str_replace(
         array("[toolname]"),
         array($vname),
         $html);
   }

   $ppl="";
/*   if (!isset($_POST['isgroup'])) {
           $sqlSelectStudents = "
                           SELECT DISTINCT People.MID, People.Login as Login, People.LastName as LastName,
                           People.FirstName as FirstName, People.EMail as EMail
                           FROM
                             People, Students
                           WHERE
                             People.MID=Students.MID AND
                             Students.CID=$kurs
                           ORDER BY People.LastName
           ";
           $res=sql($sqlSelectStudents,"err42");
           $html=gf("$tm-3tr-people.html");
           while ($r=sqlget($res)) {
              if (($GLOBALS['s']['perm']!=3) || $peopleFilter->is_filtered($r['MID']))
              $ppl .= str_replace(
                 array("[mid]","[login]","[lastname]","[firstname]","[mail]","[checked]"),
                 array($r[MID],$r[Login],$r[LastName],$r[FirstName],$r[EMail],"checked"),
                 $html);
           }
           sqlfree($res);
              $html=gf("$tm-3main.html");
   } else {
               $html=gf("$tm-3tr-people-group.html");
                   if (isset($_POST['group'])) {
                      $gid = intval($_POST['group']);
                   $sqlSelectStudents = "
                                   SELECT DISTINCT People.MID, People.Login as Login, People.LastName as LastName,
                                   People.FirstName as FirstName, People.EMail as EMail
                                   FROM
                                     People
                                   INNER JOIN groupuser ON (groupuser.mid = People.MID)
                                   WHERE
                                     groupuser.cid='-1' AND
                                     groupuser.gid = {$gid}
                                   ORDER BY People.LastName
                   ";

                   $res=sql($sqlSelectStudents,"err42");
                   while ($r=sqlget($res)) {
                       if (($GLOBALS['s']['perm']!=3) || $peopleFilter->is_filtered($r['MID']))
                       $ppl .= str_replace(
                         array("[mid]","[login]","[lastname]","[firstname]","[mail]","[checked]", "[TASK_SEL]"),
                         array($r[MID],$r[Login],$r[LastName],$r[FirstName],$r[EMail],"checked",$main),
                         $html);
                   }
                   sqlfree($res);
                   }

              $html=gf("$tm-3main-group.html");
   }
*/
    $html=gf("$tm-3main.html");
    // ===============================================================================
    $smarty = new Smarty_els();
    $smarty->assign('list1_options',edit_schedule_unused_people('',$kurs,$gid));
    $smarty->assign('list2_options','');
    $smarty->assign('list1_name','list1');
    $smarty->assign('list2_name','list2');
//    $smarty->assign('list1_title',_('Студенты'));
//    $smarty->assign('list2_title',_('Занятие назначено'));
    $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
    $smarty->assign('editbox_search_name','editbox_search');
    $smarty->assign('editbox_search_text','');
    $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
    $smarty->assign('list1_container_id','list1_container');
    $smarty->assign('list2_container_id','list2_container');
    $smarty->assign('list3_name','list3');
    $smarty->assign('list3_options',selGrved((int) $kurs,0));
    $smarty->assign('list3_change',"if (elm = document.getElementById('editbox_search')) get_list_options(elm.value);");
    $smarty->assign('list1_list2_click','');
    $smarty->assign('list2_list1_click','');
    $smarty->assign('javascript', $ajax_javascript."
            function show_list_options(html) {
                var elm = document.getElementById('list1_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list1\" name=\"list1[]\" multiple style=\"width:100%\">'+html+'</select>';
                select_list_clear('list1','list2');
            }

            function get_list_options(str) {
                var current = 0;
                var gid = 'g0';

                var select = document.getElementById('editbox_search');
                if (select) current = select.value;

                if (select = document.getElementById('list3')) {
                    gid = select.value;
                }


                var elm = document.getElementById('list1_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list1\" name=\"list1[]\" multiple style=\"width:100%\"><option>"._("Загружаю данные...")."</option></select>';

                x_edit_schedule_unused_people(str, '".(int) $kurs."', gid, show_list_options);
            }

    ");
    $ppl = $smarty->fetch('control_list2list.tpl')."<script type=\"text/javascript\">showHideString(jQuery('#editbox_search').get(0), false)</script>";
    // ===============================================================================

   foreach (explode(" ","DD1 MM1 YY1 hh1 mm1 DD2 MM2 YY2 hh2 mm2 kurs type isgroup name desc self") as $k=>$v) {
      $html=str_replace("[$v]",htmlspecialchars($$v),$html);
   }
   $html=str_replace("[tr-main]",$main,$html);

   $html=str_replace("[tr-people]",$ppl,$html);

   $html=str_replace("[tweek]",$day,$html);

   $addvedparam=($s[user][addtoved]) ? " readonly='1' checked onclick=\"document.forms['add2'].elements['vedomost'].checked=1;\""  : "checked";

   if (!isset($_POST['isgroup'])) {
       //$html=str_replace("[GROUP_SEL]",selGr($kurs),$html);
       $html=str_replace("[GROUP_SEL]",selGr($kurs),$html);
   } else {
//                   $html=str_replace("[GROUP_SEL]",selGrRestricted($kurs, $cgid),$html);
       $html=str_replace("[GROUP_SEL]",selAutoGroups($gid),$html);
       if (isset($_POST['vedomost'])) $addvedparam = "checked";
   }

   $ref = isset($_POST['ref']) && strlen($_POST['ref']) ? $_POST['ref'] : $GLOBALS['sitepath'] . "schedule.php4";
   
   $html=str_replace("[OKBUTTON]",okbutton(_("Готово")),$html);
   $html=str_replace("[ref]",$ref, $html);
   $html=str_replace("[CANCELBUTTON]",button(_("Отмена"),"","cancel","document.location.href=\"{$ref}\";"),$html);
   $html=str_replace("[BACKBUTTON]",button("&#8592; " . _("Назад"),"","back",
   "document.location.href=\"".$sitepath.'schedule.php4?c=add'.((strstr($_SERVER['HTTP_REFERER'],'addtoved')!==false) ? '&amp;addtoved=1&amp;CID='.(int) $kurs."\";" : '&amp;course='.(int) $kurs."\";")),$html);
   $html=str_replace("[GROUP_ARRAY]",grArray($kurs),$html);
   $html=str_replace("[REBILD]",$rebild,$html);// ????
   $html=str_replace("[period]", $period, $html);
   $room_capacity = '';
   if ($room) $room_capacity = get_room_capacity($room);
   $html=str_replace("[room]", $room, $html);
   $html=str_replace("[room_capacity]", $room_capacity, $html);
   $html=str_replace("[teacher]",$_POST['teacher'],$html);
   $html=str_replace("[CONDITION]",$condition,$html); // ЭТО СФОРМИРОВАННЫЙ ПАРАМЕТР СНАЧАЛА ПЕРЕДАЕТСЯ ВО ВТОРУЮ ФОРМУ
   $html=str_replace("[datetype]",$datetype,$html); // ЭТО СФОРМИРОВАННЫЙ ПАРАМЕТР СНАЧАЛА ПЕРЕДАЕТСЯ ВО ВТОРУЮ ФОРМУ
   $html=str_replace("[relative_day1]",$relative_day1,$html); // ЭТО СФОРМИРОВАННЫЙ ПАРАМЕТР СНАЧАЛА ПЕРЕДАЕТСЯ ВО ВТОРУЮ ФОРМУ
   $html=str_replace("[relative_day2]",$relative_day2,$html); // ЭТО СФОРМИРОВАННЫЙ ПАРАМЕТР СНАЧАЛА ПЕРЕДАЕТСЯ ВО ВТОРУЮ ФОРМУ
   $html=str_replace("[MARK]",$mark,$html); // ЭТО СФОРМИРОВАННЫЙ ПАРАМЕТР СНАЧАЛА ПЕРЕДАЕТСЯ ВО ВТОРУЮ ФОРМУ

  //   $html=str_replace("[day]",$day,$html);
//   $html=str_replace("[day]",date("d/m/Y",$day),$html);
   $wk=date("w",$day);
   $html=str_replace("[day]",$GLOBALS["nameweek"][$wk].date(" d.m.Y",$day),$html);

   $html=str_replace("[ADDPARAMVED]",$addvedparam,$html);
   $html=str_replace("[TT_GRADE]", $GLOBALS['tooltip']->display('schedule_edit_grade'), $html);
//   $html=str_replace("[TT_ALLNEW]", $GLOBALS['tooltip']->display('schedule_edit_allnew'), $html);

/*////////////////////////////
   $rq="SELECT sheid, title, vedomost
           FROM  schedule
           WHERE schedule.CID=".$CID." AND schedule.vedomost=1
           GROUP BY begin";

    $res=sql($rq,"errLISTOFSHEID");

    while ($r=sqlget($res)) {
      $cond.="<option value=".$r['sheid'].">".$r['title']."</option>";

    }

// ДАЛЕЕ НАДО
// хранитьв базе условие (id занятия) - вопрос где и в какой форме если потом условия будут сложные
// видимо в описании занятия
// публиковать в списке заданий у студента только те, у кого удовлетворяется условие
////////////////////
   $html=str_replace("[condition]",$cond,$html);  // заменим в шаблоне на указанный набор условий

*//////////////////////////////



   $mhtml=create_new_html(0,0);

   $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);

    if ($GLOBALS['controller']->enabled) {
        $mhtml=words_parse($mhtml,$words);
        $mhtml=path_sess_parse($mhtml);
        $GLOBALS['controller']->setView('Document');
        $GLOBALS['controller']->captureFromReturn(CONTENT,$mhtml);
        $GLOBALS['controller']->setHeader(_("Добавление занятия"));
        $GLOBALS['controller']->setSubHeader(_("Шаг 2. Выбор модуля и слушателей"));
    }
   printtmpl($mhtml);

//   s_timeprint();

   break;





case "add_submit":      // Выполняется когда всесформировано ()
	
   $message = '';
	
   $ref = isset($_POST['ref']) && strlen($_POST['ref']) ? $_POST['ref'] : $GLOBALS['sitepath'] . "schedule.php4";

   $typeTool = getField('EventTools','tools','TypeID',$_POST['type']);
   if (strpos($typeTool,',')) {
       $typeTool = substr($typeTool,0,strpos($typeTool,','));
   }
   if (!in_array($typeTool, array('nothing', 'connectpro', 'webinar'))) {
       if (!$form[$typeTool]) {
           $GLOBALS['controller']->setView('Document');
           $GLOBALS['controller']->setMessage(_('Вы не выбрали модуль'),JS_GO_BACK);
           $GLOBALS['controller']->terminate();
           exit();
       }
   }
   
   $type=intval($type);
   $kurs=intval($kurs);

   if (debug) echo "<li>TYPE=$type, CID=$kurs";

   if (sqlvalue("SELECT status FROM Courses WHERE cid=$kurs","err40.1")<1)
      exit("Access denied: this course closed (CID=$kurs).");

   $res=sql("SELECT * FROM EventTools WHERE TypeID=$type","err40");
   if (!sqlrows($res)) {
        //echo sqlrows($res)."<hr />";
        		//exit("unknown type");
		       	$GLOBALS['controller']->setView('Document');
   	   			$GLOBALS['controller']->setMessage(_("На данном курсе невозможно создать занятие данного типа"), JS_GO_URL, $ref);
   	   			$GLOBALS['controller']->terminate();
   	   			exit();
   }
   $r=sqlget($res);
   sqlfree($res);

   $select = $_POST['list2'];

   $tools=array();
   foreach (explode(",",$r[tools]) as $v) {
      $tools[trim($v)]=trim($v);
   }
   
   if (isset($tools['video_live']) || isset($tools['webinar']) || isset($tools['connectpro'])) {
       //$message = _('Внимание! Для корректной работы занятий данного типа необходимо настроить связь с медиа-сервером');
   }
   
   if (debug) echo "<li>".implode(" - ",$tools);

   // описание полей, которые можно вводить в поле TOOLS базы данных

   if(isset($_POST['period']) && !empty($_POST['period']) ) {
           $period = $_POST['period'];
   }
   else {
           $period = "-1";
   }

   if( isset($_POST['room']) && !empty($_POST['room']) && ($_POST['room'] != "-1") ) {
           $room = $_POST['room'];
   }
   else {
           $room = 0;
   }   
   
   if (!$_POST['isgroup']) {
           $arrPostExternalURLs[0] = $form[externalURLs];
           $arrPostFormulas[0] = $form[formula_id];
           $arrPostGrFormulas[0] = $form[formulagr_id];
           $arrPostPenaltyFormulas[0] = $form['penaltyFormula_id'];
           $arrPostModule[0] = $form[module];
           $arrPostRedirectURL[0]= $form[redirectURL];
           $arrPostTests[0] = $form[tests];
           if(isset($form[collaborator]))
              $arrPostCollaborator[0] = $form[collaborator];
           $arrPostMids[0] = "blabla";
   } else {
           foreach ($vars as $k => $v) {
                    $sql .= (isset($form[$k])) ? "{$v}=0;" : "";
           }
           $strToolParams = ad($sql);
           if( $datetype == "relative" ){
             $startday=($relative_day1-1)*24*60*60 + 1; // В СЕКУНДАХ
             $stopday=$startday + ($relative_day2-1)*24*60*60 - 1 + 23*60*60 + 59*60 + 59; // В СЕКУНДАХ
             //$startday=($relative_day1-1)*24*60*60 + 1; // В СЕКУНДАХ
             //$stopday=$startday + ($relative_day2-1)*23*60*60+59*60+59; // В СЕКУНДАХ

             $date1 = mktime($hh1, $mm1, 0, $MM1, $DD1, $YY1);
             $date2 = mktime($hh2, $mm2, 0, $MM2, $DD2, $YY2);
             global $adodb;

             // $s_cond
             $rel="R";
             $rq="INSERT INTO schedule (title, descript, begin, end, createID, typeID, CID, CHID, startday, stopday, timetype, vedomost, isgroup, period, rid, teacher)
               values (
               ".$adodb->Quote($name).",
               ".$adodb->Quote($desc).",
               ".$adodb->DBTimeStamp($date1).",
               ".$adodb->DBTimeStamp($date2).",
               $mid,
               $type,
               $kurs,
               $rebild,
               $startday,
               $stopday,
               1,
               ".(isset($vedomost)?"1":"0").",
               '{$_POST['isgroup']}', '$period', $room, '{$_POST['teacher']}')
           ";
           } else {

             $date1 = mktime($hh1, $mm1, 0, $MM1, $DD1, $YY1);
             $date2 = mktime($hh2, $mm2, 0, $MM2, $DD2, $YY2);
             global $adodb;

             // $s_cond
             $rq="INSERT INTO schedule (title, descript, begin, end, createID, typeID, CID, CHID, timetype, vedomost, isgroup, period, rid, teacher)
               values (
               ".$adodb->Quote($name).",
               ".$adodb->Quote($desc).",
               ".$adodb->DBTimeStamp($date1).",
               ".$adodb->DBTimeStamp($date2).",
               $mid,
               $type,
               $kurs,
               $rebild,
               0,
               ".(isset($vedomost)?"1":"0").",
               '{$_POST['isgroup']}','$period', $room, '{$_POST['teacher']}')
                           ";
           }


           sql($rq,"err60");
           $intLastInsert = sqllast();

           $rq="INSERT INTO scheduleID (sheid, gid, isgroup, toolParams) VALUES ({$intLastInsert}, {$_POST['group']}, '1', '{$strToolParams}')";
           sql($rq,"err61");


           $arrPostExternalURLs = $form[externalURLs];
           $arrPostFormulas = $form[formula_id];
           $arrPostFormulas = $form[formulagr_id];
           $arrPostPenaltyFormulas = $form['penaltyFormula_id'];
           $arrPostModule = $form[module];
           $arrPostRedirectURL= $form[redirectURL];
           $arrPostTests = $form[tests];
           if(isset($form[collaborator]))
              $arrPostCollaborator = $form[collaborator];
           $arrPostMids = $_POST['select_group'];
   }

   if(!empty($arrPostMids))
   foreach ($arrPostMids as $k => $m) {

           $form[externalURLs] = $arrPostExternalURLs[$k];
           $form[formula_id] = $arrPostFormulas[$k];
           $form[formulagr_id] = $arrPostGrFormulas[$k];
           $form['penaltyFormula_id'] = $arrPostPenaltyFormulas[$k];
           $form[module] = $arrPostModule[$k];
           $form[redirectURL] = $arrPostRedirectURL[$k];
           $form[tests] = $arrPostTests[$k];
           if(isset($form[collaborator]))
              $form[collaborator] = $arrPostCollaborator[$k];

           if ($_POST['isgroup']) {
                           $select[0] = $m;
           }

           if (isset($form[tests])) $form[tests]=intval($form[tests]);
           if (isset($form[formula_id])) $form[formula_id]=intval($form[formula_id]);
           if (isset($form[formulagr_id])) $form[formulagr_id] = intval($form[formulagr_id]);
           if (isset($form['penaltyFormula_id'])) $form['penaltyFormula_id'] = intval($form['penaltyFormula_id']);
           if (isset($form[collaborator])) $form[collaborator] = intval($form[collaborator]);
           $form[sAddToAllnew] = (isset($form[sAddToAllnew])) ? 1 : 0;
           if (isset($form[module])) $form[tests]=intval($form[tests]);
           if (isset($form[externalURLs])) {
              preg_match_all("~[a-z0-9._:/?&%\~`!@#\$^*()=+-]{2,100}~i",$form[externalURLs],$ok);
              $form[externalURLs]="";
              foreach ($ok[0] as $v)  {
                 $form[externalURLs].="$v ";
              }
              $form[externalURLs]=trim($form[externalURLs]);
           }
           if (isset($form[redirectURL])) {
              preg_match_all("~[a-z0-9._:/?&%\~`!@#\$^*()=+-]{2,100}~i",$form[redirectURL],$ok);
              $form[redirectURL]="";
              $form[redirectURL]=$ok[0][0];
              $form[redirectURL]=trim($form[redirectURL]);
           }
                $sql = "";
                foreach ($tools as $v) {
                  if (isset($vars[$v])) {
//                     if (!isset($form[$v])) exit("Value \$form[$v] not found");
                     $tmp=$vars[$v];
                     $$tmp=$form[$v];
                  }
                  if (is_array($partparams[$v])) {
                          foreach ($partparams[$v] as $vv=>$kk) {
                             if (in_array($kk,$vars)) {
                                $sql.="$kk=".$$kk."; ";
                             }
                             else $sql.="$kk=".$partvalues[$v][$vv]."; ";
                          }
                  }
               }

           if ($form[sAddToAllnew]) $sql.="sAddToAllnew=1;";
           unset($form[sAddToAllnew]);
           if ($form[formula_id]) $sql.=" formula_id=$form[formula_id];"; // Это не совсем правильно - надо передавать как то через эти KK
           if ($form[collaborator]) $sql.=" collaborator=$form[collaborator];";
           if ($form[formulagr_id]) $sql.=" formulagr_id = $form[formulagr_id]";
           if ($form['penaltyFormula_id']) $sql.=" penaltyFormula_id=$form[penaltyFormula_id];";
           if (debug) echo "<li>SQL TOOLS: $sql";

           intvals("DD1 MM1 YY1 hh1 mm1 DD2 MM2 YY2 hh2 mm2 type kurs rebild");

           $shewords=loadwords("schedule-words.html");

           $name=(empty($name)) ? $shewords[8] : $name;
           $desc=(empty($desc)) ? "" : $desc;
           $mark=(empty($mark)) ? "" : ad($mark);

        //
        //   по id занятия (в condition) взять название занятия
        // вывести его наименование
           if ( $condition > 0 ){
             $res=sql("SELECT title FROM  schedule WHERE schedule.sheid=$condition","errLISTOFSHEID");
             $r=sqlget($res);
             $s_cond=$r['title'];

             $s_cond=writeCond( $s_cond ,$condition, $mark );

           }
        //   echo "<H1>TYPE=$datetype</H1>";
                if (!$_POST['isgroup']) {

                 if( $datetype == "relative" ){

                     $date1 = mktime($hh1, $mm1, 0, $MM1, $DD1, $YY1);
                     $date2 = mktime($hh2, $mm2, 0, $MM2, $DD2, $YY2);

                     if(is_quiz_by_type_id($type)) {
                        $query = "SELECT LastName, FirstName FROM People WHERE MID=".$form[collaborator];
                        $result = sql($query);
                        $row = sqlget($result);
                        $name = $name." (".$row['LastName']." ".$row['FirstName'].")";
                     }

                     $startday=($relative_day1-1)*24*60*60 + 1; // В СЕКУНДАХ
                     $stopday=$startday + ($relative_day2-1)*24*60*60 - 1 + 23*60*60 + 59*60 + 59; // В СЕКУНДАХ
                     //$startday=($relative_day1-1)*24*60*60 + 1; // В СЕКУНДАХ
                     //$stopday=$startday + ($relative_day2-1)*23*60*60+59*60+59; // В СЕКУНДАХ

                    // $s_cond
                     $rel="R";
                     $rq="INSERT INTO schedule (title, descript, begin, end, createID, typeID, CID,
                     CHID, startday, stopday, timetype, vedomost, isgroup,cond_sheid,cond_mark,period,rid, teacher)
                       values (
                       ".$adodb->Quote($name).",
                       ".$adodb->Quote($desc).",
                       ".$adodb->DBTimeStamp($date1).",
                       ".$adodb->DBTimeStamp($date2).",
                       $mid,
                       $type,
                       $kurs,
                       $rebild,
                       $startday,
                       $stopday,
                       1,
                       ".(isset($vedomost)?"1":"0").",
                       '0','-1','-','$period',$room, '{$_POST['teacher']}')
                   ";
//                       '0',$condition,\"$mark-\",'$period',$room)
//                   ";
                   //  echo $rq;
                   } else {

                     if(is_quiz_by_type_id($type)) {
                        $query = "SELECT LastName, FirstName FROM People WHERE MID=".$form[collaborator];
                        $result = sql($query);
                        $row = sqlget($result);
                        $name = $name." (".$row['LastName']." ".$row['FirstName'].")";
                     }

                $date1 = mktime($hh1, $mm1, 0, $MM1, $DD1, $YY1);
                $date2 = mktime($hh2, $mm2, 0, $MM2, $DD2, $YY2);
                global $adodb;

                    // $s_cond
                     $rq="INSERT INTO schedule (title, descript, begin, end, createID, typeID, CID, CHID,
                     timetype, vedomost, isgroup, cond_sheid, cond_mark,period,rid, teacher)
                       values (
                       ".$adodb->Quote($name).",
                       ".$adodb->Quote($desc).",
                       ".$adodb->DBTimeStamp($date1).",
                       ".$adodb->DBTimeStamp($date2).",
                       '$mid',
                       '$type',
                       '$kurs',
                       '$rebild',
                       '0',
                       '".(isset($vedomost)?"1":"0")."',
                       '0', '-1', '-','$period','$room','{$_POST['teacher']}')
                   ";
//                       '0', '$condition', '$mark-','$period','$room')
//                   ";
                   }
                   if (debug) echo "<hr>".nl2br($rq)."<hr>";
                   sql($rq,"err60");
                   $s[user][addtoved]=0;
                   $intLastInsert=sqllast();
                   if (debug) echo "<li>SHEID=$last<br><br>";
           }
           if (count($select)) {
                 $strToolParams = ad($sql);
              foreach ($select as $v) {
                 $rq="INSERT INTO scheduleID (sheid,mid,isgroup,toolParams) VALUES ";
                 $rq.="\n($intLastInsert,$v,'0','$strToolParams'),";
                 $rq=substr($rq,0,strlen($rq)-1);
                 if (debug) echo "<hr>".nl2br($rq)."<hr>";
                 sql($rq,"err61");
              }
           }
           //else {
                 $strToolParams = ad($sql);
                 $rq="INSERT INTO scheduleID (sheid,mid,toolParams) VALUES ('$intLastInsert','-1','$strToolParams')";
                 sql($rq,"err62");
           //}
        }
        
   if ($intLastInsert) {
	   $connectId = '';
	   
	   // Adobe Connect Pro
	   if ($typeTool == 'connectpro') {
	       require_once($GLOBALS['wwf'].'/lib/classes/Curl.class.php');
	       require_once($GLOBALS['wwf'].'/lib/classes/Connect.class.php');
	       
	       try {
	           $cp = new ConnectProXMLApiAdapter(CONNECT_PRO_HOST, CONNECT_PRO_ADMIN_LOGIN, CONNECT_PRO_ADMIN_PASSWORD);
	           $sco_id = $cp->addMeeting(array('id' => $intLastInsert, 'title' => $_POST['name'], 'begin' => date('Y-m-d H:i', $date1), 'end' => date('Y-m-d H:i', $date2), 'source-sco-id' => $_POST['template']));
	           if ($sco_id) {
	           	   if (is_array($_POST['list2']) && count($_POST['list2'])) {
	           	       $cp->addMeetingUserList($sco_id, $_POST['list2']);	
	           	   }
	           	   $cp->addMeetingUserList($sco_id, array($_POST['teacher']), 'host');
	           	   
	           	   sql("UPDATE schedule SET connectId = ".$GLOBALS['adodb']->Quote($sco_id)." WHERE SHEID = '$intLastInsert'");
	           } else {
	           	   throw new Exception(_('Занятие не создано'));
	           }
	       
	       } catch(Exception $e) {
	           sql("DELETE FROM schedule WHERE SHEID = '$intLastInsert' LIMIT 1");
			   $GLOBALS['controller']->setView('DocumentBlank');
			   $GLOBALS['controller']->setMessage(_("Ошибка создания занятия Adobe Connect Pro.").' '.htmlspecialchars($e->getMessage()), JS_GO_URL, '/schedule.php4');
			   $GLOBALS['controller']->terminate();
	           exit();          
	       }
	   }
   }

   if ($intLastInsert) {
       // webinar
       if (is_array($_POST['webinar_list2']) && count($_POST['webinar_list2'])) {
           array_walk($_POST['webinar_list2'], 'intval');

           $sql = "SELECT bid, filename, title FROM library WHERE bid IN ('".join("','", $_POST['webinar_list2'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               if (strlen($row['filename'])) {
                   $fname = $GLOBALS['sitepath'].'library'.$row['filename'];
                   sql("INSERT INTO webinar_plan (pointId, href, title, bid) VALUES ('$intLastInsert', ".$GLOBALS['adodb']->Quote($fname).", ".$GLOBALS['adodb']->Quote($row['title']).", '{$row['bid']}')");	
               }	
           }
       }
   }

   if ($intLastInsert > 0) {
       // sharepoint integration
       require_once $GLOBALS['wwf'].'/lib/sharepoint/BootStrap.class.php';
       require_once $GLOBALS['wwf'].'/lib/sharepoint/Schedule.class.php';
       
       $schedule = new SharePointSchedule(
           array(
              'title'       => $name,
              'description' => $desc,
              'begin'       => date('Y-m-d H:i:s', $date1),
              'end'         => date('Y-m-d H:i:s', $date2),
              'rebild'      => $rebild
           )
       );
       $schedule->create($intLastInsert);
       
   }

   $html=loadtmpl("schedule-okc.html");
   $mhtml=create_new_html(0,0);
   $words['ok']=$shewords[9];
   $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);
   $GLOBALS['controller']->setView('Document');
   $GLOBALS['controller']->setMessage(_("Новое занятие успешно добавлено").(!empty($message) ? '. '.$message : '').'. '.checkSchedule4SynhCourse($kurs), JS_GO_URL, $ref);
   printtmpl($mhtml);

   break;

case "delete":
    if (isset($_GET['rp']) && ($_GET['rp']=='ved')) {
        $js_go_url = $sitepath."ved.php4?CID={$_GET['CID']}&gr={$_GET['gr']}";
    } else if (isset($_GET['rp']) && ($_GET['rp']=='adaptive')) {
        $js_go_url = $sitepath."schedule_adaptive.php?CID={$_GET['CID']}";
    } else {
        if( isset($_GET['tweek'])) {
            $js_go_url = $sitepath."schedule.php4?tweek=".$_GET['tweek'];
        } else {
            $js_go_url = $sitepath."schedule.php4";
        }
    }

    if (@strpos($_SERVER['HTTP_REFERER'], "schedule_adaptive.php") !== false) {
        $js_go_url = $_SERVER['HTTP_REFERER'];
    }

   $res=sql("SELECT * FROM schedule WHERE sheid=$sheid","err_d1");
   if (sqlrows($res)==0) {
       $GLOBALS['controller']->setMessage(_("Занятие")." $sheid "._("не найдено"),JS_GO_URL,$js_go_url);
       $GLOBALS['controller']->terminate();
       exit();
   }
   //if ($dean) $mid=sqlres($res,0,'createID');
   //if (sqlres($res,0,'createID')!=$mid) exit("Удаление невозможно, т.к. назначено другим преподавателем. Влалец занятия: ".getName(sqlres($res,0,'createID')));
   if (!((sqlres($res,0,'createID')== $GLOBALS['s']['mid'])
       && $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN))
       && !($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS))) {

       $GLOBALS['controller']->setMessage(_("У вас нет прав на удаление данного занятия"),JS_GO_URL,$js_go_url);
       $GLOBALS['controller']->terminate();
       exit();

   }
   sqlfree($res);
   
   
   $connectId = getField('schedule', 'connectId', 'SHEID', $sheid);
   if ($connectId) {
       require_once($GLOBALS['wwf'].'/lib/classes/Curl.class.php');
       require_once($GLOBALS['wwf'].'/lib/classes/Connect.class.php');

       try {
       	    $cp = new ConnectProXMLApiAdapter(CONNECT_PRO_HOST, CONNECT_PRO_ADMIN_LOGIN, CONNECT_PRO_ADMIN_PASSWORD);
            if (!$cp->deleteMeeting($connectId)) {
            	throw new Exception(_('Невозможно удалить занятие'));
            }
       } catch (Exception $e) {
           $GLOBALS['controller']->setView('DocumentBlank');
           $GLOBALS['controller']->setMessage(_("Ошибка удаления занятия Adobe Connect Pro.").' '.htmlspecialchars($e->getMessage()), JS_GO_URL, $js_go_url);
           $GLOBALS['controller']->terminate();
           exit();          	
       }
       
   } 
   
   require_once $GLOBALS['wwf'].'/lib/sharepoint/BootStrap.class.php';
   require_once $GLOBALS['wwf'].'/lib/sharepoint/Schedule.class.php';
   SharePointSchedule::delete($sheid);
      
   //sql("DELETE FROM schedule WHERE sheid=$sheid AND createID=$mid","err_d2");
   sql("DELETE FROM schedule WHERE sheid=$sheid","err_d2");
   sql("DELETE FROM scheduleID WHERE sheid=$sheid","err_d2");
    if (@strpos($_SERVER['HTTP_REFERER'], "schedule_adaptive.php") !== false) {
        $js_go_url = $_SERVER['HTTP_REFERER'];
        header("Location: {$js_go_url}");
        break;
    }

   if (isset($_GET['rp'])) {
        if ($_GET['rp']=='ved') {
            header("Location: ".$sitepath."ved.php4?CID={$_GET['CID']}&gr={$_GET['gr']}");
            break;
        }
        if ($_GET['rp']=='rooms') {
            header("Location: ".$sitepath."schedule_rooms.php?tweek=".$_GET['tweek']);
            break;
        }
        if ($_GET['rp']=='adaptive') {
            header("Location: ".$sitepath."schedule_adaptive.php?CID=".$_GET['CID']);
            break;
        }
      }
   if( isset($_GET['tweek']) ) {
      header("Location: ".$sitepath."schedule.php4?tweek=".$_GET['tweek']);
   }
   else {
      header("Location: ".$sitepath."schedule.php4");
   }
   break;


}

function schedule_add_dates() {
    $str = "<select name='dd'>\n";
    for ($i = 1; $i <= 31; $i++) {
        $sel = ($i == date('d')) ? "selected" : "";
        $str .= "<option value={$i} {$sel}>".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>\n";
    }
    $str .= "</select>&nbsp;<select name='mm'>\n";
    for ($i = 1; $i <= 12; $i++) {
        $sel = ($i == date('m')) ? "selected" : "";
        $str .= "<option value={$i} {$sel}>".str_pad($i, 2, "0", STR_PAD_LEFT)."</option>\n";
    }
    $str.= "</select>&nbsp;<select name='yyyy'>\n";
    for ($i = (int)date("Y", time()) - 2; $i <= (int)date("Y", time()) + 2; $i++) {
        $sel = ($i == date('Y')) ? "selected" : "";
        $str .= "<option value={$i} {$sel}>{$i}</option>\n";
    }
    $str .= "</select><br><br>";
    return $str;
}

function edit_schedule_unused_people($search='', $cid=0, $gid=0) {
    $html = '';
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $people = array();

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        if ($gid[0]=='g') {
            $gid = substr($gid,1);
        }
        if ($gid > 0) {
            $group_join = " INNER JOIN groupuser ON (People.MID = groupuser.mid) ";
            $group_where = " AND groupuser.gid = '".(int) $gid."' ";
        }

        $sql = "
        SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login, People.EMail
        FROM People
        INNER JOIN Students ON (Students.MID = People.MID)
        $group_join
        WHERE Students.CID = '".(int) $cid."'
        AND (People.LastName LIKE '%".addslashes($search)."%' OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')
        $group_where
        ORDER BY People.LastName, People.FirstName, People.Login
        ";

        $res = sql($sql);

        while($row = sqlget($res)) {
            if (!$peopleFilter->is_filtered($row['MID'])) continue;
            if (isset($people[$row['MID']])) continue;

            $people[$row['MID']] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].($row['Login'] ? ' ('.$row['Login'].')' : ''),ENT_QUOTES);

        }

        if (is_array($people) && count($people)) {
            foreach($people as $mid => $name) {
                $html .= "<option value=\""
                .(int) $mid."\" title='$name'> "
                .$name."</option>";
            }
        }

    }
    return $html;
}

?>