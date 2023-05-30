<?
/*include "../../application/modules/els/subject/controllers/ListController.php";
    if (isset($_REQUEST['lng'])) {
        $lng = $_REQUEST['lng'];
    }*/
	$lng = $_COOKIE['lng'];
    require_once ("guest_redirector.php");
    include("1.php");

    if (isset($_REQUEST['mode'])) {
        $mode = $_REQUEST['mode'];
    }

    if (isset($_REQUEST['tid'])) {
        $tid = (int) $_REQUEST['tid'];
    }

    if (isset($_REQUEST['sheid'])) {
        $sheid = (int) $_REQUEST['sheid'];
    }

   include("test.inc.php");
   
   // в test.inc.php переменная sheid инициализируется каким-то значением из сессии
   // не удаляю это из test.inc - возможно для чего-то это нужно
   // при старте теста sheid может прийти только из $_REQUEST и никаких сессий!
   $sheid = (int) $_REQUEST['sheid'];

//   if (!$stud) login_error();
//   if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь!","./?$sess");
   intvals("tid teachertest");
   randomize();

   if (!isset($mode)) $mode="start";

//Если есть незавершенный сеанс теста в сессии, то переходим к нему, а не начинаем новый тест.
if(isset($_SESSION['s']['me']) && $_SESSION['s']['me'] == 1){	
	header("Location: " . $GLOBALS['sitepath']."test_vopros.php");
}

# Проверка на соответствияе: тест -> занятие -> сессия
$sql = 'SELECT cid FROM test WHERE tid=' . intval($tid);
$res = sql($sql);
$row = sqlget($res);
$cid_test   = (int)$row['cid']; 
$cid_lesson = intval(getField('schedule', 'CID', 'SHEID', $sheid));
if($cid_test != $cid_lesson){
	header("Location: /");
	die;
}		
		
switch ($mode) {
case "start":

    $ok_url = "test_start.php?mode=starttest";

    unset($_SESSION['s']['test']['current']['ModID']);
    if (isset($_GET['ModID']) && $_GET['ModID']) {
        $_SESSION['s']['test']['current']['ModID'] = (int) $_GET['ModID'];
    }

    if (is_array($_GET) && count($_GET)) {
        foreach($_GET as $k=>$v) {
            if ($k!='mode') {
                $ok_url .= '&'.urlencode($k).'='.urlencode($v);
            }
        }
    }

//    if ($GLOBALS['s']['perm']!=1) header("Location: ".$sitepath.$ok_url);

    $GLOBALS['controller']->setView('DocumentBlank');
    if ($tid) {        
        $sql = "
        SELECT test.*, test.startlimit as startlimit_clean, testquestions.questions
        FROM test
        LEFT JOIN testquestions ON (testquestions.tid=test.tid)
        WHERE test.tid='".(int) $tid."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) {

            $allQuestions = test_getkod($row);
            $needQuestions = test_getneedkod($row);
            $testQuestions = test_evalkod($row, $allQuestions, $needQuestions);
            
            $questions = explode($GLOBALS['brtag'],$row['data']);
            if (is_array($questions) && count($questions)) {
                $sql = "SELECT DISTINCT kod FROM list WHERE kod IN ('".join("','",$questions)."')";
                $res = sql($sql);
                $data = $questions = sqlrows($res);
            }

            if ($row['lim'] && ($row['lim']<$questions)) $questions = $row['lim'];

            if (!empty($row['questions'])) {
                $row['questions'] = unserialize($row['questions']);
                if (is_array($row['questions']) && count($row['questions'])) {
                    $questions = 0;
                    foreach($row['questions'] as $theme=>$count) {
                        $questions += $count;
                        if ($questions>$data) $questions = $data;
//                        if ($theme!='Без названия')
                        if($count)
                            $themes[] = $theme?$theme:'Без темы';
                    }
                }
            }            
            if (!$row['timelimit']) $row['timelimit'] = _("нет");
            if (!$row['startlimit']) $row['startlimit'] = _("не ограничено");
            $TEST_MODES[$row['mode']] = str_replace(', ', ',<br>', $TEST_MODES[$row['mode']]);
            $TEST_MODES[$row['mode']] = str_replace(' ', '&nbsp;', $TEST_MODES[$row['mode']]);
            $row['mode'] = $TEST_MODES[$row['mode']];
            $row['themes'] = false;
            $row['sheid'] = $sheid;
			
			if(intval($row['startlimit']) > 0){
				# default
				$row['attempts_spent'] 		= 0;
				$row['attempts_remaining'] 	= $row['startlimit'];
				
				$cid = intval(getField('schedule', 'CID', 'SHEID', $sheid));				
				$last=sqlval("SELECT qty FROM testcount WHERE mid={$s[mid]} AND tid={$tid} AND cid={$cid} AND lesson_id = '".(int) $sheid."'","errTS169");
				
				if (is_array($last)) {
					# если не осталось попыток, то открепляем все записи с 0 временем и 0 отвеченных попросов, добавляя к id студента префикс
					if($row['startlimit'] <= $last[qty]){
						$res=sql("UPDATE loguser SET mid=RIGHT('100000000' + mid, 9) WHERE questdone=0 AND mid=$s[mid] AND tid=$tid AND sheid = '".(int) $sheid."'","errTS185");							
					}
					
					# корректируем кол-во попыток: кол-во сеансов не может быть меньше израсходованных попыток. Если это так, значит или студент напортачил, или проблемы с сетью у него, или изменялось макс. мол-во попыток.
					$resLog = sqlval("SELECT COUNT(*) AS 'rowCount' FROM loguser WHERE mid=".$s[mid]." AND sheid='".(int) $sheid."' AND tid=".$tid, "errTS169");
					if($resLog['rowCount'] < $last['qty'])	{ 
						$qty = $resLog['rowCount'];
						$res=sql("UPDATE testcount SET qty=$qty, last=".time()." WHERE mid=$s[mid] AND tid=$tid AND lesson_id = '".(int) $sheid."'","errTS185");
					} else {
						$qty = $last[qty];
					}	
					
					$row['attempts_spent'] 	   = $qty; 	# израсходовано попыток 
					$row['attempts_remaining'] = ($row['startlimit'] > $qty) ? ($row['startlimit'] - $qty) : (0); # осталось попыток
				}
			}
			
			

            if (is_array($themes) && count($themes)) $row['themes'] = join('<br>',$themes);

            $row['questions'] = count($testQuestions);
            $smarty = new Smarty_els();
            $smarty->assign('data',$row);
            if (TEST_TYPE == _('Задание')){
            	if ($row['startlimit_clean']) {
                	$msg = $smarty->fetch('test_start_task_info.tpl');
            	} else {
	            	// не показывает окно для заданий если попытки не ограничены - бояться нечего;
	            	// то же самое нужно будет сделать для тестов (после решения #5494)
            		header("Location: {$ok_url}");
            		exit();
            	}
            }else{
                $msg = $smarty->fetch('test_start_info.tpl');
            }
        } else {
            $GLOBALS['controller']->setMessage(_(TEST_TYPE.' не найден'), JS_GO_URl, $_SESSION['default']['lesson']['execute']['returnUrl']);
            $GLOBALS['controller']->terminate();
            exit();
        }
    } else {
            $GLOBALS['controller']->setMessage(_(TEST_TYPE.' не прикреплен к занятию'), JS_GO_URl, $_SESSION['default']['lesson']['execute']['returnUrl']);
            $GLOBALS['controller']->terminate();
            exit();
    }

    if ($_SESSION['closeSheduleWindow'] || isset($s['old_mid'])) {
        $cancel_url = "javascript:window.close();";
        if (isset($s['old_mid'])) $s['mid'] = $s['old_mid'];
    } else {
        $cancel_url = "{$sitepath}";
    }

    if (isset($_SESSION['s']['test']['current']['ModID'])) {
        $cancel_url = $GLOBALS['sitepath'].'teachers/show_org_metadata.php?ModID='.(int) $_SESSION['s']['test']['current']['ModID'];
    }

    $cancel_url = $_SESSION['default']['lesson']['execute']['returnUrl'];

    $GLOBALS['controller']->setMessage($msg,JS_GO_URL,$ok_url,false,$cancel_url);
    $GLOBALS['controller']->terminate();
    exit();
    break;
//////////////////////////////////////////////////////////////////
case "starttest": // запустить тестирование
   // на вход поступает идентификатор теста $tid
   // добавим еще идентификатор занятия $sheid

   $GLOBALS['controller']->setView('DocumentBlank');

   $message = false;

   if ( isset($sheid) ) $sheid=intval($sheid);
//   echo "ЗАНЯТИЕ $sheid <BR>";
   istest_noexit();

   $res=sql("SELECT test.*, testquestions.questions
             FROM test
             LEFT JOIN testquestions ON (testquestions.tid=test.tid)
             WHERE test.tid='".(int) $tid."'","err1");
   if (sqlrows($res)!=1) $message = _("Извините, задание")." N$tid "._("не существует.")."<P>";
   $rtest=sqlget($res);

   sqlfree($res);

   if ($rtest[status]==0) $message = _("Извините, это задание не опубликовано (заблокировано)").".<P>";

   if (!$teach) {

           $current_datetime = date("Y-m-d H:i");
           $time_registered = get_time_registered($s['mid'],getField('schedule','CID','SHEID',$sheid));
           $q = "SELECT * FROM schedule
                 INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
                        WHERE
                          (schedule.SHEID = '{$sheid}') AND
                          (scheduleID.MID = '{$s['mid']}') AND
                          (schedule.params LIKE '%module_id={$tid};%') AND ((schedule.timetype = 0 AND
                          (" . $adodb->SQLDate("Y-m-d H:i:s", "begin") . " < '{$current_datetime}') AND
                          (" . $adodb->SQLDate("Y-m-d H:i:s", "end") . " > '{$current_datetime}')) OR
                          (schedule.timetype = 1) OR
                          schedule.timetype = 2)

                   ";
                   $r = sql($q);
                if (!sqlrows($r)) {
                     $query = "SELECT vol1 FROM organizations WHERE vol1='".(int) $tid."'";
                     $result = sql($query);
                     if(!sqlrows($result)) {
                         //exit("");
                     }
                }
                // exit("Извините, это задание назначено на другое время.<P>".backbutton());
   }
   // является ли это демо проход (для преподов)
   //$teachertest=intval($teachertest)%2;
    $teachertest = ($_SESSION['s']['perm'] > 1);
    
   //$testOrgCid = sqlvalue("SELECT cid FROM organizations WHERE vol1 = '{$rtest['tid']}'");//Курс теста в структуре организации

    if ($teachertest) {
      $flag = false;
      if (isset($s[tkurs][$rtest[cid]])) {
          $flag = true;
      }
//      if (!isset($s[tkurs][$rtest[cid]]) && !isset($s[tkurs][$testOrgCid])) {
      $sql = "SELECT cid FROM organizations WHERE vol1 = '{$rtest['tid']}'";
      $res = sql($sql);
      while($row = sqlget($res)) {
          if (isset($s[tkurs][$row['cid']])) {
              $flag = true;
              //$message = _("Вы не являтесь преподавателем на этом курсе.");
          }
      }

      if ($_SESSION['s']['perm'] == 2) {
          // types: test - 0, poll - 1, exercise = 2
          switch($rtest['type']) {
              case 1:
              $_sql = "SELECT * FROM subjects_quizzes WHERE quiz_id = '".$rtest['test_id']."'";
              $_res = sql($_sql);
              while($_row = sqlget($_res)) {
                  if (isset($s['tkurs'][$_row['subject_id']])) {
                      $flag = true;
                      break;
                  }
              }
              break;
              case 2:
              $_sql = "SELECT * FROM subjects_exercises WHERE exercise_id = '".$rtest['test_id']."'";
              $_res = sql($_sql);
              while($_row = sqlget($_res)) {
                  if (isset($s['tkurs'][$_row['subject_id']])) {
                      $flag = true;
                      break;
                  }
              }
              break;
              default:
              $_sql = "SELECT * FROM subjects_tests WHERE test_id = '".$rtest['test_id']."'";
              $_res = sql($_sql);
              while($_row = sqlget($_res)) {
                  if (isset($s['tkurs'][$_row['subject_id']])) {
                      $flag = true;
                      break;
                  }
              }
          }
      }

      if (!$flag) {
          $flag = ($_SESSION['s']['perm'] > 2);
          if (!$flag && $sheid) {
              $_sql = "SELECT MID FROM scheduleID WHERE SHEID = '".(int) $sheid."' AND MID = '".(int) $_SESSION['s']['mid']."'";
              $_res = sql($_sql);
              $flag = sqlrows($_res);
          }
      }

      if (!$flag) {
          $sql = "SELECT * FROM Tutors WHERE MID = '".(int) $_SESSION['s']['mid']."'";
          $res = sql($sql);
          while($row = sqlget($res)) {
              if (isset($row['TID'])) {
                  $flag = true;
                  break;
              }
          }
      }
      if (!$flag) {
          $message = _("Вы не являтесь преподавателем на этом курсе.");
      }
   }
   
   #
   # ограничение кол-ва попыток
   #
   if (($s['perm'] < 2) && $rtest[startlimit]) {
//   if (!$teachertest && $rtest[startlimit]) {

      $sheid_last = sqlval("SELECT * FROM schedulecount WHERE mid=$s[mid] AND sheid='".(int) $sheid."'");
      if (is_array($sheid_last)) {
      /**
      * Если присутствует ограничение кол-ва попыток по sheid  (прохождения ЗАНЯТИЯ)
      */

         if ($rtest[limitclean]>0 && $sheid_last[last]+$rtest[limitclean]*24*60*60<time()) {
            $sheid_last[qty]=0;
            putlog("test_start.php: "._("Обнуление счетчика SCHEDULECOUNT:qty у юзера")." $s[mid] "._("по давности."));
         }
         if ($sheid_last[qty]>=$rtest[startlimit]) {

			$smarty_tpl = new Smarty_els;

            if (isset($_SESSION['s']['test']['current']['ModID'])) {
                $smarty_tpl->assign('location',$GLOBALS['sitepath'].'teachers/show_org_metadata.php?ModID='.(int) $_SESSION['s']['test']['current']['ModID']);
            } else {
			    $smarty_tpl->assign("location", "index.php");
            }

			$message = _("Вы израсходовали все попытки прохожения этого занятия.<br>Для занятия было отпущено попыток:")."
			 <b>{$rtest['startlimit']}</b>";

             if (in_array(LESSON_TYPE_ID, array(2055, 2056, 2057)))  {
                 // Удаляем назначение
                 sql("DELETE FROM scheduleID WHERE SHEID = '".(int) $sheid."' AND MID = '".(int) $_SESSION['s']['mid']."'");
             }

			if(!$GLOBALS['controller']->enabled){
				$smarty_tpl->assign("message", $message);
	            exit($smarty_tpl->fetch("schedule_error.tpl"));
			}
         }
         $qty=$last[qty]+1;
         $res=sql("UPDATE schedulecount
                   SET qty=$qty, last=".time()."
                   WHERE mid=$s[mid] AND sheid='".(int) $sheid."'","errTS185");

      // ====================================================
      } elseif(LESSON_TYPE_ID != 2056) {

      /**
      * Иначе ограничение колва попыток по tid (прохождения ЗАДАНИЯ)
      */
      $cid = intval(getField('schedule', 'CID', 'SHEID', $sheid));
      $last=sqlval("SELECT * FROM testcount WHERE mid={$s[mid]} AND tid={$tid} AND cid={$cid} AND lesson_id = '".(int) $sheid."'","errTS169");
      //pr($rtest);
      $qty=1;
      if (is_array($last)) {
         $what="UPDATE";
         if ($rtest[limitclean]>0 && $last[last]+$rtest[limitclean]*24*60*60<time()) {
            $last[qty]=0;
            putlog("test_start.php: "._("Обнуление счетчика TESTCOUNT:qty у юзера")." $s[mid] "._("по давности."));
         }
         if ($last[qty]>=$rtest[startlimit]) {

			$smarty_tpl = new Smarty_els;
			$smarty_tpl->assign("location", "index.php");

			$message = _("Вы израсходовали все попытки прохожения этого занятия.<br>Было отпущено попыток:")."
			 <b>{$rtest['startlimit']}</b>";

             if (in_array(LESSON_TYPE_ID, array(2055, 2056, 2057)))  {
                 // Удаляем назначение
                 sql("DELETE FROM scheduleID WHERE SHEID = '".(int) $sheid."' AND MID = '".(int) $_SESSION['s']['mid']."'");
             }

			if(!$GLOBALS['controller']->enabled){
				$smarty_tpl->assign("message", $message);
	            exit($smarty_tpl->fetch("schedule_error.tpl"));
			}
         }
         if ($last['qty']<$rtest['startlimit']) {
			 # корректируем кол-во попыток: кол-во сеансов не может быть меньше израсходованных попыток. Если это так, значит или студент напортачил, или проблемы с сетью у него, или изменялось макс. мол-во попыток.
			 $resLog = sqlval("SELECT COUNT(*) AS 'rowCount' FROM loguser WHERE mid=".$s[mid]." AND sheid='".(int) $sheid."' AND tid=".$tid, "errTS169");
			 if($resLog['rowCount'] < $last['qty'])	{ $qty = $resLog['rowCount'];	}
			 else 									{ $qty = $last[qty]+1;			}			
            
             $res=sql("UPDATE testcount
                       SET qty=$qty, last=".time()."
                       WHERE mid=$s[mid] AND tid=$tid AND lesson_id = '".(int) $sheid."'","errTS185");
         }
      }
      else {
         $res=sql("INSERT INTO testcount (mid, tid, cid, qty, last, lesson_id) values (
                   {$s[mid]}, {$tid}, {$cid}, {$qty}, ".time().", '".(int) $sheid."')","errTS185");
      }
      sqlfree($res);
      // ====================================================
      }
   }



   $res=test_getkod($rtest); // взяли все вопросы
   $resneed=test_getneedkod($rtest); // взяли обязятельные вопросы
   $kods=test_evalkod($rtest,$res,$resneed); // получаем массив кодов

    if (!count($kods)) $message = sprintf(_("Извините, %s не содержит ни одного вопроса."), strtolower(TEST_TYPE));

    $go_url = ($_SESSION['goto']) ? $_SESSION['goto'] : 'index.php';
    if (isset($_SESSION['s']['test']['current']['ModID'])) {
        $go_url = $GLOBALS['sitepath'].'teachers/show_org_metadata.php?ModID='.(int) $_SESSION['s']['test']['current']['ModID'];
    }

    $go_url = $_SESSION['default']['lesson']['execute']['returnUrl'];

	$GLOBALS['controller']->setMessage($message, JS_GO_URL, $go_url);
	$GLOBALS['controller']->terminate();
	if ($message) {
		if (!$GLOBALS['controller']->enabled) {
			exit($message);
		} else {
            if(isset($s[old_mid])) $s[mid] = $s[old_mid];
			exit();
		}
	}

   // Убираем вопросы по которым нет чуваков для ответов (Кураторские опросы для руководителей)
   if ((LESSON_TYPE_ID == 2056) && $sheid && count($kods)) {
       foreach($kods as $index => $kod) {
           $res = sql('SELECT DISTINCT People.MID
                        FROM dean_poll_users
                        INNER JOIN People ON (People.MID = dean_poll_users.student_mid)
                        WHERE
                            dean_poll_users.lesson_id = '.$sheid.' AND dean_poll_users.head_mid = '.$s['mid'].'
                            AND People.MID NOT IN (
                                SELECT DISTINCT junior_id FROM quizzes_results
                                WHERE lesson_id = '.$sheid.' AND user_id = '.$s['mid'].' AND question_id = '.$GLOBALS['adodb']->Quote($kod).'
                            )'
                        );
           if (!sqlrows($res)) unset($kods[$index]);
       }
       if (!count($kods)) {

           if(isset($s[old_mid])) $s[mid] = $s[old_mid];

           sql("UPDATE quizzes_feedback SET status = 4 WHERE user_id = '".$_SESSION['s']['mid']."' AND lesson_id = '".$sheid."'");

           sql("DELETE FROM scheduleID WHERE SHEID = '".(int) $sheid."' AND MID = '".(int) $_SESSION['s']['mid']."'");

           $GLOBALS['controller']->setMessage(_('Вопросы для оценки отсутствуют. Все анкеты по сотрудникам заполнены.'), JS_GO_URl, $go_url);
           $GLOBALS['controller']->terminate();
           exit();
       }
   }
//   foreach ($kods as $value) {
//		$q = "SELECT qtema FROM list WHERE kod='{$value}'";
//		$r = sql($q);
//		while($a = sqlget($r)){
//			echo $a['qtema']."<br>";
//		}
//	}
//exit();
   $jsclose=1;

   $s[me]      =1;
   $s[akod]    =array();
   $s[aneed]   =array();
   $s[adone]   =array();
   $s[agood]   =array();
   $s[abal]    =array();
   $s[aotv]    =array();
   $s[abalmax] =array();
   $s[abalmin] =array();
   $s[abalmax2]=array();
   $s[abalmin2]=array();
   $s[ainfo]   =array();
   $s[qty]     =$rtest[qty];
   $s[moder]   =0;
   $s[bal]     =0;
   $s[balmax]  ='undefined';
   $s[balmin]  ='undefined';
   $s[balmax2] ='undefined';
   $s[balmin2] ='undefined';
   $s[free]    =$rtest[free];
   $s[skip]    =$rtest[skip];
   $s[cid]     =$rtest[cid];
   $s[tid]     =$rtest[tid];
   $s[ttitle]  =$rtest[title];
   $s[ttitle_translation] =$rtest[title_translation];
   $s[questres]=$rtest[questres];
   $s[endres]  =$rtest[endres];
   $s[showurl] =$rtest[showurl];
   $s['mode']  = $rtest['mode']; // режим прохождения тестирования
   $s[timelimit]=$rtest[timelimit];
   $s[start]   =time();
   $s[rating]  =$rtest[status];
   $s[ckod]    =array();
   $s[jsclose] =abs(intval($jsclose))%2;
   $s[teachertest]=$teachertest;
   $s[sheid] =  $sheid;
   $s['test_id'] = $rtest['test_id'];
   $s['test_type'] = $rtest['type'];
   $s['test_position'] = 0;
   $s[jsclose] =abs(intval($jsclose))%2;
   $s['adaptive'] = $rtest['adaptive'];

   foreach ($kods as $k=>$v) {
      $s[akod][]=$v;
      $s[aneed][]=$v;
   }

   $log=array(
      "akod"=>$s[akod],
   );

   /**
   * Подсчитывает макс бал для текущего tid с вопросами $s['akod']
   */
   $s['balmax2_true'] = get_maxbal_by_kods($s['akod']);
   $s['balmax_true'] = get_nomoder_maxbal_by_kods($s['akod']);
   $s['balmin2_true'] = get_minbal_by_kods($s['akod']);
   $s['balmin_true'] = get_nomoder_minbal_by_kods($s['akod']);


   $rq="INSERT INTO loguser (mid, cid, tid, balmax, balmin, balmax2, balmin2, bal, qty, free, skip, start, stop,
   fulltime, moder, needmoder, status, teachertest, `log`, sheid) values (
   '$s[mid]',
   '$s[cid]',
   '$s[tid]',
   '" . floatval($s[balmax_true]) . "',
   '" . floatval($s[balmin_true]) . "',
   '" . floatval($s[balmax2_true]) . "',
   '" . floatval($s[balmin2_true]) . "',
   '" . floatval($s[bal]) . "',
   '" . (int)$s[qty] . "',
   '$s[free]',
   '$s[skip]',
   '$s[start]',
   '$s[start]',
   0,
   0,
   0,
   0,
   '".(int) $teach."',
   '".serialize($log)."',
   '$s[sheid]')
   ";
//   if($s[sheid] && $s[mid])
//   {
//       $updSchedule = "UPDATE scheduleID SET V_STATUS=-1 WHERE SHEID=$s[sheid] AND MID=$s[mid]";
//       sql($updSchedule);
//   }
   /*if($s[sheid] && $s[mid]) {
       $updSchedule = "UPDATE scheduleID SET V_STATUS=1 WHERE SHEID=$s[sheid] AND MID=$s[mid] AND V_STATUS=-1";
       sql($updSchedule);
   }*/
   
   $res=sql($rq,"err5");
   
   $s[stid]=sqllast();
   sqlfree($res);

   if ($s[stid]<=0) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_("Произошла ошибка базы данных. Обратитесь в службу технической поддержки."),JS_GO_URL, $_SESSION['default']['lesson']['execute']['returnUrl']);
       $GLOBALS['controller']->terminate();
       $s['me'] = 0;
       exit();
   }

   if ($_SESSION['s']['sheid'] > 0) {
       if (in_array(getField('schedule', 'typeID', 'SHEID', $_SESSION['s']['sheid']), array(EVENT_TYPE_POLL, TYPE_DEAN_POLL_FOR_STUDENT, TYPE_DEAN_POLL_FOR_LEADER, TYPE_DEAN_POLL_FOR_TEACHER))) {
           sql("UPDATE quizzes_feedback SET status = 2 WHERE user_id = '".$_SESSION['s']['mid']."' AND lesson_id = '".$_SESSION['s']['sheid']."'");
       }
   }

   test_switch();

//   if (!tdebug) refresh("test_vopros.php?$sess&vopros=".md5(microtime()));
   if (!tdebug) {
//header("Location:test_vopros.php?$sess&vopros=".md5(microtime()));
                refresh("test_vopros.php?$sess&vopros=".md5(microtime()));
   }
   else {
      echo "<span style='color: white'>"._("начать тест:")."</span> <a href=test_vopros.php?$sess&vopros=".md5(microtime()).">test_vopros.php?$sess</a>";
      pr($s);
   }

   return;





//////////////////////////////////////////////////////////////////
case "break": // прервать тестирование - результаты записываются, но не показываются!

   if ($s[me]==0) exitmsg(_("Невозможно прервать сеанс."),"./?$sess");
   if ($s[me]==2) exitmsg(_("Нажмите кнопку, чтобы закончить сеанс."),"end.php?$sess");

   result_test(3);
   $s[me]=0;
   if (isset($s['old_mid'])) $s['mid'] = $s['old_mid'];
   unset($s[random_vars]);

   if (isset($_SESSION['s']['sheid'])) {
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL, $_SESSION['default']['lesson']['execute']['returnUrl']);
        session_unregister("boolInFrame");
        session_unregister("closeSheduleWindow");
        unset($s[jsclose]);
        
   		header("Location: {$_SESSION['default']['lesson']['execute']['returnUrl']}");
//        $GLOBALS['controller']->terminate();
        exit();
   }

   if (isset($_SESSION['s']['test']['current']['ModID'])) {
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,$GLOBALS['sitepath']."teachers/show_org_metadata.php?ModID={$_SESSION['s']['test']['current']['ModID']}");
        unset($_SESSION['s']['test']['current']['ModID']);
        session_unregister("boolInFrame");
        session_unregister("closeSheduleWindow");
        unset($s[jsclose]);
   		header("Location: " . $GLOBALS['sitepath']."teachers/show_org_metadata.php?ModID={$_SESSION['s']['test']['current']['ModID']}");
//        $GLOBALS['controller']->terminate();
        exit();
   }

    // ================================================
    if ($_SESSION['goto']) {
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,$_SESSION['goto']);
        session_unregister("boolInFrame");
        session_unregister("closeSheduleWindow");
        unset($s[jsclose]);
        header("Location: " . $_SESSION['goto']);
//        $GLOBALS['controller']->terminate();
        exit();
    }

    if ($_SESSION['closeSheduleWindow'] || isset($s['old_mid'])) {
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_CLOSE_SELF_REFRESH_OPENER);
        session_unregister("boolInFrame");
        session_unregister("closeSheduleWindow");
        unset($s[jsclose]);
        exit('<script>window.close();</script>');
        //$GLOBALS['controller']->terminate();
        exit();
    }

    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"{$sitepath}index.php?$sess");
    session_unregister("boolInFrame");
    session_unregister("closeSheduleWindow");
    unset($s[jsclose]);
    header("Location: " . "{$sitepath}index.php?$sess");
    //$GLOBALS['controller']->terminate();
    exit();
    // ================================================

   if ($s[jsclose] && !$_SESSION['boolInFrame']&& ($s[perm] == 2)) {
          winclose();
   }
   else {
       echo "        <script language='javascript'>
                     obj_frameset = top.document.getElementById('mainFrameset');
                     obj_frameset.cols = '*';
                     obj_mainFrame = top.document.getElementById('mainFrame');
                     </script>
                     ";

       refresh("{$sitepath}index.php?$sess");
   }
   unset($s[jsclose]);
   session_unregister("boolInFrame");

   return;


//////////////////////////////////////////////////////////////////
case "result": // завершить тестирование досрочно

   if ($s[me]==0) exitmsg(_("Невозможно прервать сеанс."),"./?$sess");
   if ($s[me]==2) exitmsg(_("Нажмите кнопку, чтобы прервать сеанс."),"end.php?$sess");

   $arr=sqlval("SELECT * FROM test WHERE tid=$s[tid]","errTS164");
   if (!is_array($arr)) err(_("Задание, которое вы проходите, не найдено в базе данных (только что стерто кем-то?)."),__FILE__,__LINE__);
   if (!$arr[skip]) exitmsg(_("Этот сеанс нельзя досрочно завершить!"));
   result_test(4);
   refresh("test_end.php?vopros=".md5(microtime()).$sess);

   return;

}




?>