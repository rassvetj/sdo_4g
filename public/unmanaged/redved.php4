<?php
// version 2.1 DK. Добвлено
// автоматическое выставление оценок с помощью формулы
//
// define all varibles and settings

//require_once("1.php");
include("formula_calc.php");
include("test.inc.php");
include("lib/scorm/scorm.lib.php");

if(!$stud)
     login_error();
if(empty($courses))
     login_error();
$SHEID=(isset($_GET['SHEID'])) ? intval($_GET['SHEID']) : 0;
if(isset($_GET['SHEID']) && isset($_POST['mid']) && (isset($_POST['mark']) || isset($_POST['alt_mark']))) {
     if(!isset($CID)) {
       $CID=getField("schedule","CID","SHEID",$SHEID);
     }
     if(isset($_POST['method_id'])) { // если в параметрах передан метод оценивания
       $method_id=$_POST['method_id'];
     }
     $SHEID=$_GET['SHEID'];
     foreach ($_POST['alt_mark'] as $k => $am) {
       $arrMarks[] = ($am == '-1') ? array_shift($_POST['mark']) : $am;
     }
     if($method_id=='mark2all' && is_array($arrMarks) && count($arrMarks)) {
         foreach($arrMarks as $k=>$v) {
             $arrMarks[$k] = $_POST['mark2all'];
         }
         $method_id=-1;
     }
     if ($method_id=='comment2all' && !empty($_POST['comment2all']) && is_array($_POST['mid']) && count($_POST['mid'])) {
         $comment = trim(strip_tags($_POST['comment2all']));
         $sql  = "UPDATE scheduleID 
                  SET comments=".$GLOBALS['adodb']->Quote($comment)." 
                  WHERE SHEID='".(int) $_GET['SHEID']."' AND MID IN ('".join("','",$_POST['mid'])."')";
         sql($sql);
         $method_id=0;
     }
     if($method_id == -1) {
          updateRes($_GET['SHEID'], $_POST['mid'], $arrMarks, $_POST['gid'], $_POST['group_mark'], $_POST['description']);
          $GLOBALS['controller']->setMessage(_("Оценки успешно сохранены"),JS_CLOSE_SELF_REFRESH_OPENER);
          $GLOBALS['controller']->terminate();
          exit(); 
     }    
}

function setAutoMark( $SHEID, $method_id ) {
    // должна автоматически выставлять оценку за тест сразу по сдаче теста
    // надо бы еще гдениь хранить метод выставления оцеки и его задавать при создании занятия
      $res=sql("SELECT stid, balmax, balmin,
                    balmax2, balmin2, bal, start,
                    fulltime, moder, needmoder,
                    moderby, modertime, status,
                    questall, questdone, mid
             FROM loguser
             WHERE loguser.tid=".$tid."
             ORDER BY stid DESC","ettTL72");


      $mark=viewFormula($formula,$text,0,$rowt['balmax2'],$rowt['bal']);

}
////////////////////////////////////////////////
function teachRedVed($SHEID, $method_id) {
      global $sitepath, $s;  
    
      $owner_soid = (int) $_REQUEST['owner_soid'];
      $people_filter = false;
      if ($owner_soid > 0) {
      	  require_once($GLOBALS['wwf'].'/lib/classes/Position.class.php');
      	  if ($people = CUnitPosition::getSlavesPeopleId(array($owner_soid))) {
      	      foreach($people as $mid) {
      	      	  $people_filter[$mid] = true;
      	      }
      	  }
      }
      $CID=getField("schedule","CID","SHEID",$SHEID);
      $q = "SELECT * FROM scheduleID WHERE SHEID='{$SHEID}' AND gid IS NOT NULL";
      $r = sql($q);
      if ($a = sqlget($r)) {
              $intGid = $a['gid'];
      }
      //$gr = $GLOBALS['controller']->persistent_vars->vars[remember_filter][$GLOBALS['controller']->persistent_vars->vars[page_id]][gr];
      $gr = $s[user][vsortg];
      $gnum=substr($gr, 1);
      if (!$gr) {
          $sqlq2="SELECT People.MID as MID, People.FirstName as fn, People.LastName as ln FROM People, Students
                  WHERE Students.CID='".$CID."' AND Students.MID=People.MID
                  ORDER BY People.LastName ASC";
      } else {
          if ($gr[0]=="g") {
            $sqlq2="SELECT People.MID as MID, People.FirstName as fn, People.LastName as ln
                    FROM People, Students, groupuser
                    WHERE Students.CID='".$CID."'
                    AND Students.MID=People.MID
                    AND groupuser.mid=People.MID
                    AND groupuser.gid='".$gnum."'
                    ORDER BY People.LastName ASC";
          } else {
              $sqlq2="SELECT People.MID as MID, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered
                      FROM People, Students, cgname
                      WHERE Students.CID='".$CID."'
                      AND Students.MID=People.MID
                      AND  Students.cgid=cgname.cgid
                      AND cgname.cgid='".$gnum."'
                      ORDER BY People.LastName ASC";
          }
      }
      $res  = sql($sqlq2,"RVQTer1");
      if ( $method_id > 0 ){ // ЕСЛИ АВТОМАТИОМ СТАВИМ ОЦЕНКИ
           $rrr=sql("SELECT * FROM formula WHERE ID=".$method_id,"errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
           $rr=sqlget($rrr);                                    //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
           $formula=$rr[formula];
           $formula_type = $rr['type'];
      }
      while ($row = sqlget($res)) { // формирует список учащихся
      	 if ($people_filter && !isset($people_filter[$row['MID']])) continue;
         $rmid[]= $row['MID'];                // идентификатор
         $rname[]= $row['ln']." ".$row['fn']; // имя + фамилия
         $r_mark[]="?";
      }
      $pc=count($rmid);  // кол-во учащихся
      $q = "SELECT * FROM scheduleID WHERE SHEID='{$SHEID}' AND (isgroup <> '1' OR gid IS NOT NULL)";
      $r = sql($q);
      if ($a = sqlget($r)) {
          $tools = $a['toolParams'];
      }
      $istest = (strstr($tools, "tests_testID=")>-1) ? 1 : 0;
      $sqlq2="SELECT
                `People`.`MID` as MID ,
                `scheduleID`.`V_STATUS` as mark,
                `scheduleID`.`toolParams`,
                `scheduleID`.`SHEID` as SHEID
              FROM
                `scheduleID`, `People`
              WHERE
                `scheduleID`.`SHEID`='".$SHEID."' AND
                 scheduleID.MID=People.MID";
      $res  = sql($sqlq2,"RVQTer2");
      while ($row = sqlget($res)) {
         $rmark[$row['MID']] =  $row['mark'];

         $toolsparam=explode(";",$row['toolParams']);
         if (count($toolsparam))
            foreach ($toolsparam as $v) {
                     $tmp=explode("=",trim($v));
                     $tp[$tmp[0]]=$tmp[1];
            }
            
            if (isset($tp['module_moduleID'])) {
                $tp['module_moduleID'] = (int) getField('organizations', 'module', 'oid', $tp['module_moduleID']);
            }
            
            $is_test = false;
            if ($tp['tests_testID']) {
               $q = "SELECT * FROM test WHERE tid='{$tp['tests_testID']}'";
               $r = sql($q);
               if ($a = sqlget($r)) {
                  $rnames[$row['MID']] = $a['title'];
               }
               else {
                  $rnames[$row['MID']] = "";
               }
               $is_test = true;
            }
            if ($tp['module_moduleID']) {
               $q = "SELECT * FROM library WHERE bid='{$tp['module_moduleID']}'";
               $r = sql($q);
               if ($a = sqlget($r)) {
                  $rnames[$row['MID']] = $a['title'];
               }
               else {
                  $rnames[$row['MID']] = "";
               }
               $isScorm = is_scorm_module($tp['module_moduleID']); // Присутствуют ли в уч модуле скорм материалы
               if (!$isScorm) {
                   $isScorm = isModuleStatExists($tp['module_moduleID']);
                   $tids = explode(';',$a['test_id']);
                   $istest = true;
               }               
            }
            if ($isScorm) {
                $rtest[$row['MID']] = 
                get_scorm_tracks_by_mid($row['MID'],$tp['module_moduleID'],$CID,&$a_mark,&$additional_col,array('formula'=>$formula,'text'=>$text));
                $istest = true;
            }
            if ( $istest ) {
              
              /**
              * Формирование групп по формуле - если выбранно
              */
              if ($method_id>0) {
                   $formula_query = "SELECT * FROM formula WHERE id = ".$method_id;
                   $formula_result = sql($formula_query, "errfn4435");
                   $formula_row = sqlget($formula_result);
                   if ($formula_row['type']==3) {
                       add_people_to_group_by_formula($SHEID, $row['MID'], $method_id);
                   }
              }
                
              $tid=$tp['tests_testID'];
              if (!isset($tids)) $tids = array($tid);
              $rest=sql("SELECT loguser.stid, loguser.balmax, loguser.balmin,
                            loguser.balmax2, loguser.balmin2, loguser.bal, loguser.start, loguser.stop,
                            loguser.fulltime, loguser.moder, loguser.needmoder,
                            loguser.moderby, loguser.modertime, loguser.status,
                            loguser.questall, loguser.questdone, loguser.mid, loguser.log,
                            test.title
                     FROM loguser
                     LEFT JOIN test ON (test.tid=loguser.tid)
                     WHERE loguser.tid IN ('".join("','",$tids)."') AND loguser.mid='{$row['MID']}'
                     ORDER BY loguser.stid DESC","ettTL72");

              //echo $balmax."<br />";
              $ii=0;  // считает кол-в студентов у которых есть результаты
              $a_mark[0]="?";
              $counter_in_cycle = 0;
              while ($rowt = sqlget($rest)) { // для всех результатов тестов
                 $test_titles[$rowt['start']] = $rowt['title'];
                 $counter_in_cycle++;
                 $balmax_by_stid = get_maxbal_by_stid($rowt['stid']);
                 $balmin_by_stid = get_minbal_by_stid($rowt['stid']);
                 switch ($rowt['status']) {
                       case 0: $status="<font color=green>"._("идет тестирование")."</font>"; break;
                       case 1: $status=_("выполнен"); break;
                       case 2: $status="<font color=red>"._("брошен")." (timeout)</font>"; break;
                       case 3: $status="<font color=blue>"._("прерван по команде")."</font>"; break;
                       case 4: $status=_("досрочно завершен"); break;
                       case 5: $status="<font color=blue>"._("прерван лимитом времени")."</font>"; break;
                    }// case
                 if ($val = ($balmax_by_stid-$balmin_by_stid)) {
                    //$est=@sprintf("%1.0f",(($rowt['bal']-$balmin_by_stid)*100)/$val);
                    $est = round((($rowt['bal']-$balmin_by_stid)*100)/$val);
                 }
                 else $est="-";
                 $str = ($intGid)? "<td class='small'>&nbsp;</td>":"";
                 $str .=
                       "<td class='small'>".date("d.m.y H:i",$rowt['start'])."</td>
                       <td class='small'><FONT SIZE=+1>".$rowt['bal']."</FONT></td>
                       <td class='small'>".$balmax_by_stid."</td>
                       <td class='small'>".$rowt['questall']."</td>
                       <td class='small'><a href='[PATH]test_log.php?[SESSID]c=mini&stid=".$rowt['stid']."
                             ' onclick='wopen(\"\",\"log\",790,575,1)' target='log'> ".$status." (".$est."%)>></a>
                       </td>";
                 if ( $method_id > 0 ){ // ЕСЛИ АВТОМАТИОМ СТАВИМ ОЦЕНКИ
                        // формируем массив оценок

                   //$formula_query = "SELECT * FROM formula WHERE id = ".$method_id;
                   //$formula_result = sql($formula_query, "errfn4435");
                   //$formula_row = sqlget($formula_result);

                   switch($formula_row['type']) {
                          case "1":
                            $mark = viewFormula($formula,$text,0,$balmax_by_stid,$rowt['bal']);
                            if ($tp['penaltyFormula_id']) {
                                $sql = "SELECT end FROM schedule WHERE SHEID='".(int) $SHEID."'";
                                $tmp_res = sql($sql);
                                
                                if (sqlrows($tmp_res) && $tmp_row = sqlget($tmp_res)) {                                    
                                    $days = getPenaltyDays($rowt['stop'], strtotime($tmp_row['end']));
                                    $penaltyFormula = getPenaltyFormula($tp['penaltyFormula_id']);
                                    $penalty = viewPenaltyFormula($penaltyFormula,$days);
                                    if ($penalty) { 
                                        $mark = round($mark*$penalty,2);
                                        $penalty = (int) (100 - $penalty*100);
                                        if ($penalty<0) $penalty = 0;
                                        //$penalties[$rowt['mid']] = $penalty;
                                    }
                                }
                                
                            }
                            if($counter_in_cycle == 1) {
                                $a_mark[$rowt['mid']] = $mark;
                            }
                            $str.="<td class='small' nowrap><FONT COLOR=red><B>".$mark."</B>";
                            if ($penalty) $str.=" (-$penalty%)";
                            $str.="</FONT></td>";
                            $ii++;
                            $additional_col = true;
                            
                          break;
                          //case "3":
                            /*
                            if($counter_in_cycle == 1) {
                               add_people_to_group_by_formula($SHEID, $rowt['mid'], $method_id);
                            }
                            */
//                            $str.="<td class='small'>&nbsp;</td>";

                          //break;
                   }


                 }
                 else {
//                   $str.="<td class='small'>&nbsp;</td>";
                 }

                 $rtest[$rowt['mid']][$rowt['start']] =  $str;
              }// end while  для всех результатов тестов

              $ttitle=getField("test","title","tid",$tid);
              }
      }
      $str="
      <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"  valign=\"top\" border=\"0\">\n
      <tr>\n
         <td width='100px'>"._("Курс:")."</td>\n
         <td nowrap>".getField("Courses","Title","CID",$CID)."</td>
         <td width=100% rowspan=3 align=right valign=top><a href='{$sitepath}reports/output.php?arr_sheid[]={$SHEID}&from_ved=1'><img src='{$sitepath}images/icons/print.gif' border=0 alt='"._("Печатать ведомость")."' title='"._("Печатать ведомость")."'></a></td>
      </tr>\n
      <tr>\n
         <td width='100px'>"._("Занятие:")."&nbsp;&nbsp;</td>\n
         <td nowrap>".getField("schedule","Title","SHEID",$SHEID)."</td>
      </tr>\n";
         $strTitle = max($a['Title'],$a['title']);
     if (!$intGid) $str.="<tr>\n
         <td width='100px'>"._("Задание:")."&nbsp;&nbsp;</td>\n
         <td nowrap>".$strTitle."</td>\n
      </tr>
      <tr>
       <td></td>
       <td nowrap><br /><a href='redved.php4?show=stat&SHEID=$SHEID'>"._('Статистика ответов')." >>></a></td>
      </tr>";
     $str.="</table><br>\n";
//
// ВСЕ ОЦЕНКИ НАХОДЯТСЯ В ФОРМЕ

      $str.="<form action='[PATH]redved.php4?SHEID=$SHEID' name='markup' method='POST' id='markup'>\n
              <input type='hidden' name='CID' value='".$CID."'>
              <input type='hidden' name='owner_soid' value='".$owner_soid."'>";
      $str.="<table width=\"100%\" cellspacing=\"1\" cellpadding=\"2\" border=\"0\"  valign=\"top\" class=\"main\">\n";
      $strAltTh = ($intGid) ? "<th>"._("Индивидуально")."</th>" : "";
      $str .= "<tr><th>"._("ФИО")."</th>{$strAltTh}";
      if ($istest || $isScorm) {
              $str.="<th>"._("Дата")."</th><th>"._("Балл")."</th><th>"._("Макс.")."&nbsp;"._("балл")."</th><th>"._("Вопросов")."</th>";
      }
      $str.="<th>"._("Оценка")."</th></tr>\n";

      $colsp=($istest || $isScorm) ? "<td colspan='4'>&nbsp;</td>" :  "";

      for($i=0;$i<$pc;$i++) { // перебор всех СТУДЕНТОВ
      if (isset($rmark[$rmid[$i]])) { // если такой студент есть?
        $str.="<tr>\n";
        $str.="<td><a href='".$sitepath."reg.php4?showMID=".$rmid[$i]."' target='_blank'>".$rname[$i]."</a></td>";
        if ($intGid) {
                $str .= "<td>{$rnames[$rmid[$i]]}</td>";
        }
        $str .= $colsp."<td nowrap>";

        $intMark = (int)$rmark[$rmid[$i]];
        if ( $method_id >0 ){ // выставить оценку - какую именно? попыток то несколько
         
          $formula_query = "SELECT * FROM formula WHERE id = ".$method_id;
          $formula_result = sql($formula_query);
          $formula_row = sqlget($formula_result);

          switch($formula_row['type']) {
                 case "1":
                  $val = $a_mark[$rmid[$i]] ; // забираем из массива оценок оценку текущего студента
                 break;
                 case "3":
                  $val = ( $intMark > -1) ? $rmark[$rmid[$i]] : "";
                 break;
          }
        }
        else {
          $val=( $intMark > -1) ? $rmark[$rmid[$i]] : "";
        }

        //echo $val."<br />";

        $str.="<input type=hidden name=mid[] value='".$rmid[$i]."'>\n";
                $strDisabled = ($intMark < -1) ? "disabled" : "";
        $str.="<input type=text name=mark[] id=mark{$rmid[$i]} value='".$val."' style='width:30px' {$strDisabled}>\n&nbsp;"._("или")."&nbsp;";
                $r = sql("SELECT * FROM alt_mark","blabla");
                $strJs = "javascript:document.all.mark{$rmid[$i]}.disabled = (this.value!=-1);";
        $str .= "<select name='alt_mark[]' onClick='{$strJs}'>";
                $str .= "<option value=-1> .";
        while ($arrTmp = sqlget($r)) {
                $intArrTmpInt = (int)$arrTmp['int'];
                        $strSel = ($intArrTmpInt == $intMark) ? "selected" : "";
                        $str .= "<option value={$arrTmp['int']} {$strSel}> {$arrTmp['char']}";
                }
        $str .= "</select>";
        if ($is_test) {
            $str.="&nbsp;&nbsp;&nbsp;&nbsp;<a href='#' target='preview' onClick=\"wopen('schedule.php4?ch_sid=1&sheid=$SHEID&c=go&mode_frames=1&mid=".$rmid[$i]."','preview'); return false;\">"._("пройти")."</a>";
        }
        $str.="&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" onClick=\"window.open('shedule_comments.php?sheid={$SHEID}&amp;mid={$rmid[$i]}','','status=no,toolbar=no,menubar=no,scrollbars=yes,resizable=no,width=600,height=400');\" title=\"".Schedule::get_comments($SHEID,$rmid[$i])."\">"._("коммент.")."</a></td>";
        if (@$additional_col) $str.="<td>&nbsp;</td>";
        $str.="</tr>\n";
        if ($istest || $isScorm) {
           $tc=count($rtest[$rmid[$i]]);
           if ($tc) {
               krsort($rtest[$rmid[$i]]);
//              for ($j=0;$j<$tc;$j++) {
                foreach ($rtest[$rmid[$i]] as $k=>$v) {
                   $test_attempt_counter[$test_titles[$k]]++;
                   $str.="<tr>\n<td  class='small' align='right'>".(int) $test_attempt_counter[$test_titles[$k]].": {$test_titles[$k]}</td>";
                   $str .= $v;
                   //$str.=$rtest[$rmid[$i]][$j];
                   //$str.="<td>&nbsp;</td>\n</tr>\n";
                   $str.="</tr>";
                }
//              }
           }
           /*else {
               for ($j=0;$j<$tc;$j++) {
                   $str.="<tr>\n<td  class='small' align='right'>".($j+1).":</td>";
                   $str.=$rtest[$rmid[$i]][$j];
                   //$str.="<td>&nbsp;</td>\n</tr>\n";
                   $str.="</tr>";
              }
           }*/
        }

       }
      }
               if ($intGid) {
                        $q = "
                                SELECT
                                  groupname.gid as gid,
                                  groupname.name,
                                  scheduleID.V_STATUS,
                                  scheduleID.V_DESCRIPTION
                                FROM
                                  scheduleID
                                  INNER JOIN groupname ON (scheduleID.gid = groupname.gid)
                                WHERE
                                  scheduleID.SHEID={$SHEID}
                        ";
                        $r = sql($q);
                        if ($a = sqlget($r)) {
                                $strDesc = stripslashes($a['V_DESCRIPTION']);
                                $strMark = ($a['V_STATUS'] != '-1') ? $a['V_STATUS'] : "";
                                if (!$istest){
                                        $strColspan = "1";
                                } else {
                                        if ($intGid) {
                                                $strColspan = "5";
                                        } else {
                                                $strColspan = "4";
                                        }
                                }
                                $str .= "<tr><th><b>"._("Группа")."</th><th colspan={$strColspan}>"._("комментарий")."</th><th>"._("оценка")."</th></tr>";
                            $str .= "<tr><td valign=top><b>{$a['name']}</b></td><td colspan={$strColspan}><textarea name='description'>{$strDesc}</textarea></td><td><input type='hidden' name='gid' value='{$a['gid']}'><input type='text' size=4 name='group_mark' value='{$strMark}'></td></tr>";
                        }
                }
      $str.="</table>\n";
///////////////
      $str.="<p align=right>"._("Выполнить:")." <select id=\"method_id\" name='method_id' onChange=\"elm=document.getElementById('method_id'); document.getElementById('mark2all').style.display='none'; document.getElementById('comment2all').style.display='none'; if (elm.value=='mark2all') document.getElementById('mark2all').style.display='block'; if (elm.value=='comment2all') document.getElementById('comment2all').style.display='block';\">
      <option value=-1 >"._("сохранить")."</option>

      <option value=0";
      if( $method_id==0 ) $str.=" selected";     // ПОДКЛЮЧЕНИЕ ОБРАБОТКИ ОЦЕНОК АВТОМАТИЧЕСКИ ФОРМУЛОЙ
      $str.=">"._("обновить")."</option>";

      $res=sql("SELECT * FROM formula WHERE (CID='".$CID."' OR CID=0) AND type=1","errFM50fed"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
      
      while ($r=sqlget($res)) {                                    //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
        $str.="<option value=".$r[id];
        if( $method_id == $r[id]) $str.=" selected";     // ПОДКЛЮЧЕНИЕ ОБРАБОТКИ ОЦЕНОК АВТОМАТИЧЕСКИ ФОРМУЛОЙ
        $str.=">"._("Выставить:")." ".$r[name]."</option>";
       }
//////////////
      $res = sql("SELECT * FROM formula WHERE (CID='".$CID."' OR CID=0) AND type=3", "errfn448");
      while($r = sqlget($res)) {
            $str.="<option value=".$r[id];
            if($method_id == $r[id]) $str.=" selected";
            $str.=">"._("Распределить:")." ".$r[name]."</option>";
      }
      $str .= "<option value=\"mark2all\">"._("Выставить всем")." </option>";
      $str .= "<option value=\"comment2all\">"._("Добавить всем комментарий")."</option>";
      $str.="</select>";
      $str.="<div align=right id=\"mark2all\" class=hidden2>"._("оценка:")." <input  type=\"text\" name=\"mark2all\" size=2></div>";
      $str.="<div align=right id=\"comment2all\" class=hidden2><textarea name=\"comment2all\" cols=32 rows=5></textarea></div>";

/*     $str.="</p><table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
                        <tr>
                            <td align=\"right\" valign=\"top\">
<input type=\"image\" name=\"ok\"
onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
onmouseout=\"this.src='".$sitepath."images/send.gif';\" src=\"".$sitepath."images/send.gif\"
 align=\"right\" alt=\"выполнить выбранное действие\"
onclick=\"if (!confirm('Выполнить ?')) return false;\" border=\"0\"></td>
                        </tr>
            </table>
   </form>
   ";
   */
   $str .= '<p>'.okbutton('ok'," onclick=\"if (!confirm('"._("Выполнить")." ?')) return false;\" ")."</form>";
   



   if ( $method_id > 0 ) // ЕСЛИ АВТОМАТИОМ СТАВИМ ОЦЕНКИ
           $mark=viewFormula($formula,$text,0,100,50,$formula_type);
    $str.=$text;
    return $str;
   } ///////////////////////////////////////////////////////////////////////////////

function studRedVed($SHEID) {
}

function updateRes($sheid,$mids,$marks,$gid=0,$group_mark='',$desc='') {
      /*
      echo "<pre>sheid:";
      print_r($sheid);
      echo "</pre>";
      echo "<pre>mids:";
      print_r($mids);
      echo "</pre>";
      */
      $ms=count($mids);
      for($i=0;$i<$ms;$i++) {
         if ((empty($marks[$i]))&&($marks[$i] !== "0")) $marks[$i]="-1";
         $sql="UPDATE `scheduleID` SET `V_STATUS`='".$marks[$i]."' WHERE MID='".$mids[$i]."' AND SHEID='".$sheid."'";
         $res=sql($sql);
      }
      if ($gid) {
                $sql="UPDATE `scheduleID` SET `V_STATUS`='".$group_mark."', `V_DESCRIPTION`='".return_valid_value($desc)."' WHERE gid='".$gid."' AND SHEID='".$sheid."'";
                $res=sql($sql);
      }
}


//   $html=show_tb(1);

   $html=create_new_html(0,0);

   $allheader=ph(_("Выставить оценки"));
   $allcontent=loadtmpl("redved-main.html");
   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   $html=str_replace("[HEADER]","",$html);

   if ($teach) {
           if((isset($_GET['show']))&&($_GET['show'] == "stat")) {
                   $query = "SELECT CID FROM schedule WHERE SHEID=$SHEID";
                   $result = sql($query, "err");
                   $row = sqlget($result);
                   $CID = $row['CID'];

                   $query = "SELECT toolParams FROM scheduleID WHERE SHEID=$SHEID";
                   $result = sql($query, "err");
                   $row = sqlget($result);

                   $toolParams = explode(";", $row['toolParams']);

                   foreach($toolParams as $key => $value) {
                           if(strpos($value, "tests_testID") !== false) {
                              $temp = explode("=", $value);
                              $test_id = $temp[1];
                              $tids[] = $test_id;
                           }
                           if (strpos($value, "module_moduleID") !== false) {
                               $temp = explode('=',$value);
                               if ($module_id = $temp[1]) {
                                   $sql = "SELECT * FROM mod_list WHERE ModID='".(int) $module_id."'";
                                   $res = sql($sql);
                                   if (sqlrows($res) && $res_row = sqlget($res)) {
                                       if ($res_row['test_id']) {
                                           $tids = explode(';',$res_row['test_id']);
                                       }
                                   }
                               }
                           }
                   }
                   
                   if((!is_array($tids))||(!count($tids))) {
                        $temp = _("Это задание не связано с ни с каким тестом");                        
                        $temp.= "<br /><br /><a href='redved.php4?SHEID=$SHEID'><<< "._("Вернуться к ведомости")."</a>";
                   }
                   else {                            
                         $is_quiz = is_quiz_by_sheid($SHEID);
                         $GLOBALS['controller']->setView('DocumentPopup');
                         $GLOBALS['controller']->captureFromOb(CONTENT);
                         
                         $temp = '';
                         foreach ($tids as $test_id) {
                             
                            if ($test_id > 0) {
                                $temp .= showQuestionStatistic($test_id, $CID, "",$is_quiz);
                            }
                            
                         }
                         $temp .= "<a href='redved.php4?SHEID=$SHEID'><<< "._("Вернуться к ведомости")."</a>";
                         if ($GLOBALS['controller']->enabled) echo "<p align=center>".$temp;
                         $GLOBALS['controller']->captureStop(CONTENT);
                         $GLOBALS['controller']->terminate();
                         if ($GLOBALS['controller']->enabled) exit();
                   }

                   $html = str_replace("[MAINT]", $temp, $html);

           }
           else {
                 $html=str_replace("[MAINT]",teachRedVed($SHEID, $method_id),$html);
           }

   }
   elseif($stud) {
           $html=str_replace("[MAINT]",studRedVed($SHEID),$html);
   }
    if ($GLOBALS['controller']->enabled) {
        $GLOBALS['controller']->setView('DocumentPopup');
        $html=words_parse($html,$words);
        $html=path_sess_parse($html);
        $GLOBALS['controller']->captureFromReturn(CONTENT,$html);
    }
    printtmpl($html);
   
function isModuleStatExists($module_id) {
    if ($module_id) {
        $sql = "SELECT * FROM scorm_tracklog WHERE ModID='".(int) $module_id."'";
        $res = sql($sql);
        return sqlrows($res);
    }
}
   
?>