<?
   require_once("lib/classes/WeekSchedule.class.php");

   if (!defined("dima")) exit("?");
   if(!$stud) login_error();
   if(empty($s[skurs])) login_error();

   $ss="test_s1";
   echo show_tb();
   echo ph("<FONT SIZE=+1>"._("Задания")."</FONT>");
   $GLOBALS['controller']->setView('DocumentPopup');
   $GLOBALS['controller']->setHeader(_("Попытки выполнения задания"));
   $GLOBALS['controller']->setHelpSection('popitki');
   $GLOBALS['controller']->captureFromOb(CONTENT);

   if (count($s[skurs])==0) {
      if ($GLOBALS['controller']->enabled) {
          $GLOBALS['controller']->setMessage(_("Вы не зарегистрированы ни на одном из курсов"));
      }
      else {
        echo _("Вы не зарегистрированы ни на одном из курсов.");
        echo show_tb();
      }
   }
   $current_unixtime = time();
   $current_time = date("Y-m-d H:i:s", $current_unixtime);

   $strCourses = implode(", ", $s[skurs]);

   //$kurses = selCourses($s['tkurs'], $CID , $GLOBALS['controller']->enabled);
//   $GLOBALS['controller']->addFilter(_('курс'), 'CID', selCourses($s['skurs'], $CID , $GLOBALS['controller']->enabled), $CID, true);

   global $adodb;

   $res=sql("
   SELECT schedule.SHEID as sheid, scheduleID.V_STATUS, schedule.cond_mark, schedule.cond_sheid,
   schedule.cond_progress, schedule.cond_avgbal, schedule.cond_sumbal, schedule.cond_operation
   FROM scheduleID
   INNER JOIN
     schedule ON schedule.SHEID = scheduleID.SHEID
   WHERE
     scheduleID.MID=$s[mid] AND
     schedule.SHEID = '".(int) $_GET['sheid']."'
   ORDER BY schedule.begin
   ","errTTS26");
//     schedule.begin <= ".$adodb->DBTimeStamp($current_unixtime)." AND
//     schedule.end >= ".$adodb->DBTimeStamp($current_unixtime)." AND
   if (sqlrows($res)==0) {
//      echo "Нет заданий на текущий день";
//      echo show_tb();
   }

   $id=array();
   $ocenka=array();
   while ($r=sqlget($res)) {
   	  if (!WeekSchedule::check_cond($r)) continue;
      $id[$r[sheid]]=$r[sheid];
      $ocenka[$r[sheid]]=$r[V_STATUS];
   }
   sqlfree($res);

   $strRange = (count($id)) ? implode(",",$id) : "0";

   $regStamp = get_registered_unixtime($_SESSION['s']['mid'],$CID);

   $res=sql("
   SELECT
       Courses.CID,
       schedule.sheid,
       scheduleID.toolparams,
       vedomost,
       UNIX_TIMESTAMP(schedule.begin) AS begin,
       UNIX_TIMESTAMP(schedule.end) as end,
       schedule.startday,
       schedule.stopday,
       schedule.timetype
   FROM schedule
   INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
   INNER JOIN Courses ON Courses.CID = schedule.CID
   WHERE
   Courses.Status = 2 AND
   schedule.sheid IN ({$strRange})
   ","errTTS43"); // здесь выбрали все занятия с тестом среди занятий для данного человека
   if (sqlrows($res)==0) {
      if (!$CID) {
      	//echo _("Выберите курс");
      }
      else {
      	echo _("Нет заданий");
      }
      //echo show_tb();
   }
   // в ID находятся все sheid
   $testid=array();
   $sheid = array();
   $allow_to_view = array();
   while ($r=sqlget($res)) {
       if (preg_match("!tests_testID=([0-9]+)!",$r[toolparams],$ok)) {
           $tid = $ok[1];
       } elseif (preg_match("!module_moduleID=([0-9]+)!",$r[toolparams],$ok)) {
           $qq = "SELECT * FROM mod_list WHERE ModID='{$ok[1]}'";
           $rr = sql($qq);
           if ($aa = sqlget($rr)) {
               $tid = array_shift(explode(';', $aa['test_id']));
           }
       }
       if (intval($tid)){
           $testid[$tid] = $tid;
           if ($r[vedomost]) $testocenka[$tid][]=$ocenka[$r[sheid]];
           $sheid[$tid]=$r[sheid]; // под номером теста зеписываем номер занятия

           if ($r['timetype'] == 1) {
           	if ((time() >= ($regStamp+$r['startday'])) && (time() <= ($regStamp+$r['stopday']))) {
           		$allow_to_view[$tid] = $r['sheid'];
           	}
           } else {
           	if ((time()>=$r['begin']) && (time()<=$r['end'])) {
           		$allow_to_view[$tid] = $r['sheid'];
           	}
           }
       }

   }
   sqlfree($res);
   if (!count($testid)) {
   }

   $strRange = (count($testid)) ? implode(",",$testid) : "0";

   $res=sql("
   SELECT tid, cid, title, startlimit, allow_view_log, limitclean
   FROM test
   WHERE tid IN ({$strRange})
   ORDER BY cid,tid
   ","errTTS91");
   if (!sqlrows($res)) {
   		$GLOBALS['controller']->setMessage('Данное занятие не связано ни с одним тестом. Невозможно отобразить статистику тестирования.', JS_CLOSE_SELF_REFRESH_OPENER);
   		$GLOBALS['controller']->terminate();
   		exit();
   }
   $testbuf=array();
   while ($r=sqlget($res)) {
      $testbuf[$r[cid]][$r[tid]]=$r;
   }
   sqlfree($res);

   $res=sql("
   SELECT tid,cid,bal,balmax,balmax2,start,status,stid
   FROM loguser
   WHERE mid=$s[mid] AND tid IN ({$strRange})
   ORDER BY stid
   ","errTTS108");

   $buf=array();
   while ($r=sqlget($res)) {
      $buf[$r[tid]][$r[stid]]=$r;
   }
   sqlfree($res);

   $testcount=array();
   $res=sql("SELECT * FROM testcount WHERE mid=$s[mid]","errTTS160");
   while ($r=sqlget($res)) {
       $testcount[$r[cid]][$r[tid]]=$r[qty];
       $lastattempt[$r[cid]][$r[tid]] = $r['last'];
   }
   sqlfree($res);


   $corner_path = $GLOBALS['controller']->enabled ? $GLOBALS['controller']->view_root->skin_url . '/' : $sitepath;

   foreach ($testbuf as $k=>$v) {
      // для текущего курса
      echo "
         <table width=100% class=main cellspacing=0>
            <tr>
               <th width='100%' nowrap>"._("Задание")."</th>
               <th nowrap>"._("Дата попытки")."</th>
               <th nowrap>"._("Статус")."</th>
               <th nowrap>"._("Набранный балл")."</th>
               <th nowrap>"._("Отчет о прохождении")."</th>
               </tr>";


      foreach ($v as $kk=>$vv) {  // для каждого задания
         $limit=-1;
         if ($vv[startlimit]) {
            if (!isset($testcount[$k][$kk])) $limit=$vv[startlimit];
            else $limit=$vv[startlimit]-$testcount[$k][$kk];
            if (($vv['limitclean']>0) && $lastattempt[$k][$kk]) {
                if (($lastattempt[$k][$kk] + ($vv['limitclean']*24*60*60))<time()) {
                    $limit = $vv[startlimit];
                }
            }
         }
         $quest=_("Действительно хотите пройти это задание?");
         if ($vv['startlimit']) {
             if ($limit<=0) $quest=_("К сожалению, у вас уже не осталось попыток на прохождение этого задания. Все равно открыть?");
             if ($limit>0) $quest.="\\n\\n"._("Внимание! У вас осталось попыток для прохождения:")." $limit";
         }

         echo "<tr><td ".(isset($buf[$kk])?"rowspan=".count($buf[$kk]):"")." valign=top>";
         echo $vv[title];
         $tmp="";
         if (isset($testocenka[$kk])) {
            $tmp.="<br>"._("Оценка:")." ";
            $jjj=0;
            $fk=0;
            foreach ($testocenka[$kk] as $kkk=>$vvv) {
               if ($vvv==-1){
                       $tmp.="?";
                       break;
               }
               else {
                       $tmp.=$vvv;
                       $fk=1;
                       break;
               }
               if( ++$jjj < count($testocenka[$kk]) )
                       $tmp.=", ";
            }
            if($fk)
                    echo $tmp;
         }
         if ($vv['startlimit']) {
             if ($limit<=0) echo "<br>"._("Попытки прохождения исчерпаны.");
             if ($limit>0) echo "<br>"._("Осталось попыток:")." $limit";
         } else echo "<br>"._("Попытки не лимитированы");
         echo "</td>";
         if (!isset($buf[$kk]))
            echo "
            <td>&nbsp;</td>
            <td>"._("еще не сдан")."</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            </tr>";
         else {
            $i=0;
            foreach ($buf[$kk] as $kkk=>$vvv) {
               $tmp_avl = false;
			   $tmp_arr = $testbuf[$vvv['cid']];
			   if(is_array($tmp_arr) && count($tmp_arr)) {
			   	  foreach ($tmp_arr as $tmp_value) {
					$tmp_avl = (($tmp_value['tid'] == $vvv['tid']) && ($tmp_value['allow_view_log'] == "1")) ? true : $tmp_avl;
			   	  }
			   }
               if ($i>0) echo "<tr>";
               echo "<td nowrap>".date("d.m.Y H:i",$vvv[start])."</td><td nowrap>";

               switch ($vvv[status]) {
                  case 0: echo "<font color=green>"._("идет тестирование")."</font>"; break;
                  case 1: echo _("закончен"); break;
                  case 2: echo "<font color=red>"._("брошен")." (timeout)</font>"; break;
                  case 3: echo "<font color=blue>"._("прерван по команде")."</font>"; break;
                  case 4: echo _("досрочно завершен"); break;
                  case 5: echo "<font color=blue>"._("прерван лимитом времени")."</font>"; break;
               }
               echo "<td>".round($vvv[bal],2)." "._("из")." {$vvv['balmax2']}</td>";
              if($tmp_avl && $GLOBALS['controller']->checkPermission(TESTS_VIEW_RESULTS)) {
              	echo "<td align='center'><a href='test_log.php?c=mini&stid=$kkk' target='_blank' title=\""._("Показать отчёт")."\">" . getIcon('print') . "</a></td>";
              } else {
              	echo "<td align='center'>" . _('отключен преподавателем') . "</td>";
              }

               $i++;
            }
         }
      }

      echo "</table><P>";

   }
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
?>