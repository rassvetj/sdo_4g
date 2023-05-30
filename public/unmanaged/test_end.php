<?

   include("1.php");

   if (isset($_GET['c'])) {
       $c = $_GET['c'];
   }

   if (isset($_GET['cnf'])) {
       $cnf = $_GET['cnf'];
   }

   if (isset($_GET['vorpos'])) {
       $vopros = $_GET['vopros'];
   }

   include("test.inc.php");
   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess",0,$constTarget);

   $GLOBALS['controller']->captureFromOb(CONTENT);

   $GLOBALS['controller']->setView('DocumentBlank');

   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];//." mid=".$s[mid].", cid=".$s[cid].", tid=".$s[tid];

   if ($s[me]!=2) exitmsg(_("Сеанс не завершен."),"./?$sess",0,$constTarget);

   if (isset($c) && $c=="end_submit") {
   	  unset($s[random_vars]);
      if ($cnf!=1) {
          if (!$GLOBALS['controller']->enabled) {
          exitmsg(_("Пожалуйста, поставьте галочку в checkbox для подтверждения ознакомления с результатми и еще раз нажмите на кнопку"),
         "$PHP_SELF?$sess&vopros=".md5(microtime()),0,$constTarget);
          } else {
              $GLOBALS['controller']->setView('DocumentBlank');
              $GLOBALS['controller']->setMessage(_("Пожалуйста, поставьте галочку в checkbox для подтверждения ознакомления с результатми и еще раз нажмите на кнопку"),JS_GO_URL,"{$sitepath}test_end.php?$sess&vopros=".md5(microtime()),0,$constTarget);
              $GLOBALS['controller']->terminate();
              exit();
          }
      }
      $s[me]=0;
      if(isset($s[old_mid])) {
         $s[mid] = $s[old_mid];
        // $s[login] = $s[old_login];
      }

      //alert("Сеанс завершен. Спасибо за уделенное время!");

      //die();
//    $s[jsclose]=1; // debug

//pr($s[jsclose]);
//pr($_SESSION['boolInFrame']);
//die();
// ================================================

if ($_SESSION['s']['sheid'] && $_SESSION['s']['cid']) {
    $typeID = (int) getField('schedule', 'typeID', 'SHEID', $_SESSION['s']['sheid']);
    if (in_array($typeID, array(EVENT_TYPE_POLL, TYPE_DEAN_POLL_FOR_STUDENT, TYPE_DEAN_POLL_FOR_LEADER, TYPE_DEAN_POLL_FOR_TEACHER))) {
        sql("UPDATE quizzes_feedback SET status = 4 WHERE user_id = '".$_SESSION['s']['mid']."' AND lesson_id = '".$_SESSION['s']['sheid']."'");

        if (in_array($typeID, array(TYPE_DEAN_POLL_FOR_STUDENT, TYPE_DEAN_POLL_FOR_LEADER, TYPE_DEAN_POLL_FOR_TEACHER))) {
            sql("DELETE FROM scheduleID WHERE SHEID = '".(int) $_SESSION['s']['sheid']."' AND MID = '".(int) $_SESSION['s']['mid']."'");
        }
    }
    if (in_array($typeID, array(3,4))) {
        $rating = 0;
        if ($_SESSION['s']['balmax2']) {
            $rating = round((($_SESSION['s']['bal'] - $_SESSION['s']['balmin2']) / ($_SESSION['s']['balmax2'] - $_SESSION['s']['balmin2'])) * 100,2);
        }
        // 3 рейтинг курсов
        if ($typeID == 3) {
            sql("INSERT INTO ratings (cid, teacher, mid, rating) VALUES ('{$_SESSION['s']['cid']}', '0', '{$_SESSION['s']['mid']}', '{$rating}')");
        }
        // 4 рейтинг преподов
        if ($typeID == 4) {
            $teacher = (int) getField('schedule', 'teacher', 'SHEID', $_SESSION['s']['sheid']);
            sql("INSERT INTO ratings (cid, teacher, mid, rating) VALUES ('{$_SESSION['s']['cid']}', '".(int) $teacher."', '{$_SESSION['s']['mid']}', '{$rating}')");
        }
    }
}

if (isset($_SESSION['s']['sheid'])) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL, $_SESSION['default']['lesson']['execute']['returnUrl']);
    session_unregister("boolInFrame");
    session_unregister("closeSheduleWindow");
    unset($s[jsclose]);

//     define('HARDCODE_WITHOUT_SESSION', true);
//     require "../../application/cmd/cmdBootstraping.php";

//     $services = Zend_Registry::get('serviceContainer');


//     $less = $services->getService('Lesson')->getOne(
//         $services->getService('Lesson')->find($_SESSION['s']['sheid'])
//     );

//     $subject = $services->getService('Subject')->getOne(
//         $services->getService('Subject')->find($less->CID)
//     );

    // свободного режима для тестов больше нет
    /*if($subject->access_mode == 1){
        $lesson = $services->getService('LessonAssign')->getOne(
            $services->getService('LessonAssign')->fetchAll(
                array(
                	'SHEID = ?' => $_SESSION['s']['sheid'],
                    'MID = ?'   => $_SESSION['s']['mid']
                )
            )
        );

        //$lesson->V_STATUS = 100;
        $data["V_STATUS"] = 100;
        $data["SSID"]     = $lesson->SSID;
        $services->getService('LessonAssign')->update($data);
    }*/
    header('Location: ' . $_SESSION['default']['lesson']['execute']['returnUrl']);
    // $GLOBALS['controller']->terminate();
    exit();
}

if ($_SESSION['s']['test']['current']['ModID']) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,$GLOBALS['sitepath']."teachers/show_org_metadata.php?ModID={$_SESSION['s']['test']['current']['ModID']}");
    unset($_SESSION['s']['test']['current']['ModID']);
    session_unregister("boolInFrame");
    session_unregister("closeSheduleWindow");
    unset($s[jsclose]);
    header('Location: ' . $GLOBALS['sitepath']."teachers/show_org_metadata.php?ModID={$_SESSION['s']['test']['current']['ModID']}");
//    $GLOBALS['controller']->terminate();
    exit();
}

if ($_SESSION['goto']) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,$_SESSION['goto']);
    session_unregister("boolInFrame");
    session_unregister("closeSheduleWindow");
    unset($s[jsclose]);
    header('Location: ' . $_SESSION['goto']);
//    $GLOBALS['controller']->terminate();
    exit();
}

if ($_SESSION['closeSheduleWindow'] || isset($s['old_mid'])) {
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_CLOSE_SELF_REFRESH_OPENER);
    session_unregister("boolInFrame");
    session_unregister("closeSheduleWindow");
    unset($s[jsclose]);
    exit('<script>window.close();</script>');
//    $GLOBALS['controller']->terminate();
    exit();
}

$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"{$sitepath}index.php?$sess");
session_unregister("boolInFrame");
session_unregister("closeSheduleWindow");
unset($s[jsclose]);
header('Location: ' . "{$sitepath}index.php?$sess");
//$GLOBALS['controller']->terminate();
exit();
// ================================================

      if (!$s[jsclose] || $_SESSION['boolInFrame']) {
                        if(isset($s[old_mid])) {
                           $GLOBALS['controller']->setView('DocumentBlank');
                           $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"javascript:window.close();");
                           unset($s[jsclose]);
                           exit('<script>window.close();</script>');
                           //$GLOBALS['controller']->terminate();
                           exit();
                           echo "<script>
                                  window.close();
                                 </script>";
                        }
                        else {
                           $GLOBALS['controller']->setView('DocumentBlank');
                           $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"{$sitepath}index.php?$sess");
//                           $GLOBALS['controller']->setMessage("Сеанс завершен. Спасибо за уделенное время!",JS_GO_URL,"javascript:window.close();");
							header('Location: ' . "{$sitepath}index.php?$sess");
                           //$GLOBALS['controller']->terminate();
                           exit();
                           refresh("{$sitepath}index.php?$sess");
                        }
      }
      else {
           if($s[perm] != 1) {
              $GLOBALS['controller']->setView('DocumentBlank');
              if (!$_SESSION['boolInFrame'])
              $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"javascript:window.close();");
              else
              $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"{$sitepath}index.php?$sess");
              unset($s[jsclose]);
              header('Location: ' . "{$sitepath}index.php?$sess");
              //$GLOBALS['controller']->terminate();
              exit();
              winclose();
           }
           else  {
              $GLOBALS['controller']->setView('DocumentBlank');
              $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"{$sitepath}index.php?$sess");
              header('Location: ' . "{$sitepath}index.php?$sess");
              //$GLOBALS['controller']->terminate();
              exit();
              refresh("{$sitepath}index.php?$sess");
           }
      }

      unset($s[jsclose]);
      exit;

   }

//   add_people_to_group_by_formula($s[sheid], $s[mid]);

   $query = "SELECT endres as endres FROM test WHERE tid = ".$s['tid'];
   $result = sql($query);
   $test = sqlget($result);

   if(!$test['endres']) {

        add_people_to_group_by_formula($s[sheid], $s[mid]);

        if ($_SESSION['s']['sheid'] && $_SESSION['s']['cid']) {
		    $typeID = (int) getField('schedule', 'typeID', 'SHEID', $_SESSION['s']['sheid']);

            if (in_array($typeID, array(EVENT_TYPE_POLL, TYPE_DEAN_POLL_FOR_STUDENT, TYPE_DEAN_POLL_FOR_LEADER, TYPE_DEAN_POLL_FOR_TEACHER))) {
                sql("UPDATE quizzes_feedback SET status = 3 WHERE user_id = '".$_SESSION['s']['mid']."' AND lesson_id = '".$_SESSION['s']['sheid']."'");

            // Удаляем чела из назначения на кураторские опросы
                if (in_array($typeID, array(TYPE_DEAN_POLL_FOR_STUDENT, TYPE_DEAN_POLL_FOR_LEADER, TYPE_DEAN_POLL_FOR_TEACHER))) {
                    sql("DELETE FROM scheduleID WHERE SHEID = '".(int) $_SESSION['s']['sheid']."' AND MID = '".(int) $_SESSION['s']['mid']."'");
                }
            }

			if (in_array($typeID, array(3,4))) {
		        $rating = 0;
		        if ($_SESSION['s']['balmax2']) {
		            $rating = round((($_SESSION['s']['bal'] - $_SESSION['s']['balmin2']) / ($_SESSION['s']['balmax2'] - $_SESSION['s']['balmin2'])) * 100,2);
		        }
		        // 3 рейтинг курсов
		        if ($typeID == 3) {
		            sql("INSERT INTO ratings (cid, teacher, mid, rating) VALUES ('{$_SESSION['s']['cid']}', '0', '{$_SESSION['s']['mid']}', '{$rating}')");
		        }
		        // 4 рейтинг преподов
		        if ($typeID == 4) {
		            $teacher = (int) getField('schedule', 'teacher', 'SHEID', $_SESSION['s']['sheid']);
		            sql("INSERT INTO ratings (cid, teacher, mid, rating) VALUES ('{$_SESSION['s']['cid']}', '".(int) $teacher."', '{$_SESSION['s']['mid']}', '{$rating}')");
		        }
		    }
		}

      $s[me] = 0;
      if(isset($s[old_mid])) $s[mid] = $s[old_mid];
      unset($s[jsclose]);
      unset($s[random_vars]);

      // ================================================
      if (isset($_SESSION['s']['sheid'])) {
          $GLOBALS['controller']->setView('DocumentBlank');
          $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL, $_SESSION['default']['lesson']['execute']['returnUrl']);
          session_unregister("boolInFrame");
          session_unregister("closeSheduleWindow");
          unset($s[jsclose]);
          if($test['endres']) {
			// показывается страница с результатами - не выводим после нее алерт
          	header('Location: ' . $_SESSION['default']['lesson']['execute']['returnUrl']);
          } else {
			// если страницы с результатами нет - фиксируем окончанеи теста/опроса этим сообщением
      		$GLOBALS['controller']->terminate();
          }
          exit();
      }

      if ($_SESSION['closeSheduleWindow'] || isset($s['old_mid'])) {
          $GLOBALS['controller']->setView('DocumentBlank');
          $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"javascript:window.close();");
          session_unregister("boolInFrame");
          session_unregister("closeSheduleWindow");
          unset($s[jsclose]);
          exit('<script>window.close();</script>');
//          $GLOBALS['controller']->terminate();
          exit();
      }

      $GLOBALS['controller']->setView('DocumentBlank');
      $GLOBALS['controller']->setMessage(_("Сеанс завершен. Спасибо за уделенное время!"),JS_GO_URL,"{$sitepath}index.php?$sess");
      session_unregister("boolInFrame");
      unset($s[jsclose]);
      header('Location: ' . "{$sitepath}index.php?$sess");
//      $GLOBALS['controller']->terminate();
      exit();
        // ================================================

      /*
      $GLOBALS['controller']->setMessage("Спасибо! Прохождение задания завершено!",JS_GO_URL,"{$sitepath}index.php?$sess");
      //alert("Спасибо! Прохождение задания завершено!");
      if($s['perm'] == 2) {
        if(isset($s[old_mid])) $s[mid] = $s[old_mid];
      	$s[me] = 0;
      	unset($s[jsclose]);
//      	echo "<script language='javascript'>
//      		  	window.close();
//      		  </script>";
      	$GLOBALS['controller']->terminate();
      	exit();
      }

      $s[me] = 0;

      echo "<script language='javascript'>
                obj_frameset = top.document.getElementById('mainFrameset');
                if (obj_frameset)  obj_frameset.cols = '*';
                obj_mainFrame = top.document.getElementById('mainFrame');
            </script>";
      refresh("{$sitepath}index.php?$sess");
      unset($s[jsclose]);
      exit;
      */
   }
   $GLOBALS['controller']->setHeader(_("Сеанс завершен. Спасибо за уделенное время!"));
   $GLOBALS['controller']->setHelpSection('test_end');
   $GLOBALS['controller']->captureFromOb(TRASH);
   echo ph(_("Выполнение задания завершено!"));
   $GLOBALS['controller']->captureStop(TRASH);
   if ($_SESSION['s']['test']['current']['ModID']) {
       echo "<table width=100% border=0 cellpadding=15 cellspacing=0><tr><td>";
   }

   $res = sql(sprintf("SELECT kod, qtema from list WHERE kod IN ('%s') ORDER BY qtema", implode("','", $s[akod])));
   while ($row = sqlget($res)) {
       if (empty($row['qtema'])) $row['qtema'] = _('[без темы]');
       $themes[trim($row['qtema'])][] = $row['kod'];
   }

   include("template_test/end-main.html");
   if ($_SESSION['s']['test']['current']['ModID']) {
       echo "</td></tr></table>";
   }
   add_people_to_group_by_formula($s[sheid], $s[mid]);

   if (tdebug) {
      pr($s);
   }

   echo $html[1];
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   //estimateResults();

?>