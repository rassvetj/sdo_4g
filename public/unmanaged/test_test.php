<?php

   require_once("1.php");
   require_once("test.inc.php");
   define("SORT_KODS", true);
   $GLOBALS['controller']->setView('DocumentContent');

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<2) {
      include("test_test_stud.php");
      exit;
   }
   

   if ($controller->enabled) $s[usermode]=1;

   //if (count($s[tkurs])==0) exitmsg(_("Вы зарегистрированы в статусе преподавателя, но на данный момент вы не преподаете ни на одном из курсов."),"/?$sess");

   $ss="test_e1";

  // if (empty($s[skurs]) && empty($s[tkurs])) login_error();
   if (!isset($s[$ss][type])) $s[$ss][type]=1;
   if (!isset($s[$ss][ttorder])) $s[$ss][ttorder]="kod";
   if (!isset($s[$ss][ttdesc])) $s[$ss][ttdesc]=0;


   
   
   if(!isset($s['random_vars']))
         {

         // <ЗАМЕНА>
         // в $vv['qdata']
         // [X] на случайное значение из диапазона test.random_vars
		 $res=sql("SELECT * FROM test WHERE tid='$s[tid]'");
		 $r=sqlget($res);
		 $random_vars = $r['random_vars']; // строка с описанием случайных величин
		 sqlfree($res);

   		 if(eregi("\;", $random_vars))
   		 {
   			$vars_array = explode(";", $random_vars);
   		 }
   		 else
   		 {
	   		$vars_array[0] = $random_vars;
   		 }
   		 foreach ($vars_array as $value)
   		 {
   		 	$value = trim($value);
   		 	if($value!="")
   		 	{
   		 		$value_title = array();
   		 		ereg(":([A-Z]+)", $value, $value_title);
   		 		$value_title[0] = substr($value_title[0],1); // строка с названием переменной
   		 		$value = ereg_replace(":([A-Z]+)", "", $value); // строка -1.2-2 || 12,23,12 || "12","23","12"
   		 		$value = trim($value);
   		 		if(eregi("\,", $value)) // перечисление строк или чисел
   		 		{
   		 			$i = 0;
   					$value_vars = explode(",", $random_values);
   					foreach ($value_vars as $tt_key => $tt_value)
   					{
   						$tt_value = trim($tt_value);
   						if(eregi("^\-?[0-9]*\.?[0-9]*$", $tt_value))
   						{
   							$array[$i] = $tt_value;
   						}
   						else
   						{
   							$array[$i] = substr($tt_value, 1, strlen($tt_value)-2);
   						}
   						$i++;
   					}
   					// $array содержит список значений случайной переменной $value_title[0]
   					$s['random_vars'][$value_title[0]] = $array[rand(0, count($array)-1)];
   					//var_dump($array);
   					//echo count($array)-1;
   					//die();
   		 		}
   		 		else // диапазон чисел
   		 		{
   		 			eregi("^(\-?[0-9]*\.?[0-9]*)-(\-?[0-9]*\.?[0-9]*)$", $value, $tt_value);
   		 			// $tt_value[1] содержит нижний предел, $tt_value[2] содержит верхний предел случайной переменной $value_title[0]

					$val_1 = $tt_value[1];
					$val_2 = $tt_value[2];


					$val_1_dot = substr($val_1, strpos($val_1, ".")+1);
					$val_2_dot = substr($val_2, strpos($val_2, ".")+1);

					if(strlen($val_1_dot)>strlen($val_2_dot))
						$val_2_dot = str_pad($val_2_dot, strlen($val_1_dot), "0", STR_PAD_RIGHT);
					elseif(strlen($val_1_dot)<strlen($val_2_dot))
						$val_1_dot = str_pad($val_1_dot, strlen($val_2_dot), "0", STR_PAD_RIGHT);

					$rand_st = rand((int)$val_1, (int)$val_2);

					if($rand_st==(int)$val_1 && $rand_st==(int)$val_2)
					{
						$rand_nd = rand($val_1_dot, $val_2_dot);
					}
					elseif($rand_st==(int)$val_1)
					{
						$rand_nd = rand(0, $val_1_dot);
					}
					elseif($rand_st==(int)$val_2)
					{
						$rand_nd = rand(0, $val_2_dot);
					}
					else
					{
						$rand_nd = rand(0, pow(10, strlen($val_1_dot)));
					}

					if(strlen($val_1_dot)>strlen($rand_nd))
					{
						$rand_nd = str_pad($rand_nd, strlen($val_1_dot), "0", STR_PAD_LEFT);
					}

					/*//echo strlen($rand_nd)." ".$rand_nd{strlen($rand_nd)-1}."<br>";
					while($rand_nd{strlen($rand_nd)-1}=="0")
					{
						echo strlen($rand_nd).$rand_nd{strlen($rand_nd)-1}."<br>";
						$rand_nd = substr($rand_nd, 0, strlen($rand_nd)-1);
					}*/

					if($rand_nd=="")
						$res = $rand_st;
					else
						$res = $rand_st.".".$rand_nd;



   		 			$s['random_vars'][$value_title[0]] = $res;
   		 		}
   		 	}
   		 }
         // </ЗАМЕНА>
         }
         if(count(@$s['random_vars']))
         foreach ($s['random_vars'] as $var_key => $var_value)
         	$vv['qdata'] = str_replace("[". $var_key ."]", $var_value, $vv['qdata']);
/*
if (($GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OTHERS)
    || $GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OWN))
    && !is_course_locked($_REQUEST['CID']) && !is_course_locked($_REQUEST['cid'])) {
    $GLOBALS['controller']->setLink('m160101');
}
*/
switch ($c) {
case "copytest":
    $cid = (int) $_GET['cid'];
    $tid = (int) $_GET['tid'];
    if ($cid && $tid) {
        $sql = "SELECT * FROM test WHERE tid = '".$tid."'";
        $res = sql($sql);

        if ($row = sqlget($res)) {
            $row['cid'] = $row['cidowner'] = $cid;
            $row['title'] = _('Копия: ').$row['title'];
            unset($row['tid']);
            /* При копировании теста вопросы копировать нельзя
            if (!empty($row['data'])) {
                $questions = explode($GLOBALS['brtag'], $row['data']);
                if (count($questions)) {
                    $kods = array();
                    // Копируем вопросы
                    foreach($questions as $question) {
                        $_sql = "SELECT * FROM list WHERE kod LIKE ".$GLOBALS['adodb']->Quote($question);
                        $_res = sql($_sql);

                        if ($_row = sqlget($_res)) {
                            $_row['kod'] = newQuestion($cid);
                            $fields = $values = array();
                            foreach($_row as $key => $value) {
                                $fields[] = $key;
                                $values[] = $GLOBALS['adodb']->Quote($value);
                            }

                            if (count($fields) && count($values)) {
                                $sql = "INSERT INTO list (".join(",",$fields).") VALUES (".join(",",$values).")";
                                if (sql($sql)) {
                                    $kods[] = $_row['kod'];
                                }

                            }

                        }
                    }
                    if (count($kods)) {
                        $row['data'] = join($GLOBALS['brtag'], $kods);
                    }
                }
            }
            */
            $fields = $values = array();
            foreach($row as $key => $value) {
                $fields[] = $key;
                $values[] = $GLOBALS['adodb']->Quote($value);
            }

            if (count($fields) && count($values)) {
                $sql = "INSERT INTO test (".join(",",$fields).") VALUES (".join(",",$values).")";
                sql($sql);
            }

        }
    }
    $GLOBALS['controller']->setMessage(_('Тест успешно скопирован'), JS_GO_URL, $GLOBALS['sitepath']."test_test.php?cid=$cid");
    $GLOBALS['controller']->terminate();
    exit();
    break;
case "":

   if ($GLOBALS['controller']->enabled) $s[$ss][cid] = $_GET['cid'];

   $cid=(isset($_GET['CID'])) ? intval($_GET['CID']) : $s[$ss]['cid'];

   if (!isset($s[tkurs][$cid]) && (int)$cid!=0){
        //exit("HackDetect: "._("ошибочный номер курса")." (errTT333): [$cid]");
        $GLOBALS['controller']->_clearFilter();
        $GLOBALS['controller']->setMessage(_('Выбран некорректный номер курса'), JS_GO_URL, 'test_test.php');
        $GLOBALS['controller']->terminate();
        exit();
   }

   $s[$ss][cid]=$cid;

   $tests = $test2course = array();
   $sql = "SELECT vol1, cid FROM organizations WHERE vol1 > 0";
   if ($cid==0) {
       $sql .= " AND cid IN ('".join("','", $s['tkurs'])."')";
   } else {
       $sql .= " AND cid = '".(int) $cid."'";
   }
   $res = sql($sql);

   while($row = sqlget($res)) {
       $tests[$row['vol1']] = $row['vol1'];
       $test2course[$row['vol1']][$row['cid']] = $row['cid'];
   }

   if (count($tests)) {
       $tests = array_chunk($tests, 50);
   }

   $arrCourses = array();
   $sql = "SELECT CID, Title FROM Courses WHERE `type` = '0'";
   $res = sql($sql);

   while($row = sqlget($res)) {
       $arrCourses[$row['CID']] = $row['Title'];
   }

   $qsql="SELECT test.cid,test.tid,test.status,test.title, test.last, test.cidowner,
            test.data, test.datatype, Courses.title as ctitle, Courses.status as cstatus,
            test.cache_qty as qcount, test.created_by
          FROM test
          LEFT JOIN Courses ON test.cid=Courses.CID";
   if ($cid==0)
       $qsql.=" WHERE test.cid IN (".implode(",",$s['tkurs']).")";
   else $qsql.=" WHERE test.cid='".$cid."'";
   if (count($tests)) {
       $qsql .= " OR (";
       for($i=0;$i<count($tests);$i++) {
           if ($i > 0) {
               $qsql .= " OR ";
           }
           $qsql .= "tid IN ('".join("','",$tests[$i])."')";
       }
       $qsql .= ")";
   }
   $qsql.=" ORDER BY test.cid, test.tid";
   $res=sql($qsql,"errTT29");
   $lastcid=-9999;
   $html=show_tb(1);
   $page=$PHP_SELF;
//   if( ($s[perm] == 2)&&(check_teachers_permissions(21, $s[mid])) )
   	$allcontent=loadtmpl("ttasks-main.html");
//   else
//   	$allcontent=loadtmpl("ttasks-main_without_new_task.html");

   $allwords=loadwords("ttasks-words.html");
   $cheader=ph($allwords[0]);
   $words['addnew']=$allwords[1];
   $words['ID']=$allwords[2];
   $words['task']=$allwords[3];
   $words['status']=$allwords[4];
   $words['change']=$allwords[5];
   $words['course']=$allwords[6];
   $words['edit']=$allwords[11];
   $words['delete']=$allwords[12];
   $words['choosecourse']=$allwords[14];
   $words['nonewname']=$allwords[15];
   $words['newname']=$allwords[13];
   $words['qlib']=$allwords[18];
   $tm=str_replace('[CID]', $cid, loadtmpl("ttasks-tr.html"));
   $tm1=loadtmpl("ttasks-tr1.html");
   $hcid=loadtmpl("ttasks-hidcid.html");
   if ($cid!=0)
       $hcid="<input type=\"hidden\" name=\"cid\" value=\"".$cid."\">";
   $tmain="";
   $courselist="";
   $tmp="";
   $tt=array();

   $tasks = array();
   while ($r=sqlget($res)) {
      $r['allowEdit'] = true;
      $r['cidTrue'] = $r['cid'];
      if (count($s['tkurs'])) {
          if (!in_array($r['cid'], $s['tkurs'])) {
              if (isset($test2course[$r['tid']])) {
                  foreach($test2course[$r['tid']] as $testCourse) {
                      if (isset($arrCourses[$testCourse])) {
                          $r['ctitle'] = $arrCourses[$testCourse];
                      }
                      $r['cid'] = $r['cidowner'] = $testCourse;
                      $r['allowEdit'] = false;
                      $tasks[$testCourse][$r['tid']] = $r;
                  }
                  continue;
              }
          }
      }
      $tasks[$r['cid']][$r['tid']] = $r;
   }
   $toolTip = new ToolTip();
   foreach($tasks as $courseTasks) {
   foreach($courseTasks as $r) {

          $is_locked_course = is_course_locked($r['cid']);
          $is_perm_edit = ($GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OTHERS)
            || ($GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OWN)
            && ($r['created_by']==$_SESSION['s']['mid'])));

          if (!$r['allowEdit']) {
              $is_perm_edit = false;
          }

          if ($lastcid!=$r['cid']) {
              if ($lastcid>0) {
                  $tmain.=str_replace("[TEST-LIST]",$tmp,$tmp2);
                  $tmp="";
              }
              $tt['coursename'] = (strlen($r['ctitle']) > 60) ? '<strong>'.substr($r['ctitle'], 0, 60) . "...</strong>" : '<strong>'.$r['ctitle'].'</strong>';
              $tmp2=words_parse($tm,$tt,"TT-");
              $lastcid=$r['cid'];
          }
          $tt['ID']=$r['tid'];
          $tt['CID']=$r['cid'];
          $tt['task']='';
          if ($is_perm_edit) {
            $tt['task']= "<a href=\"test_test.php?c=edit&tid=".(int) $r['tid']."&cid=".(int) $r['cidTrue']."\" title=\""._("Редактировать вопросы")."\">";
          }
          $tt['task'].=($r[status]) ? "<b>".$r['title']."</b>" : $r['title'];
          if ($is_perm_edit) {
            $tt['task'].="</a>";
          }
          $tt['notes']=($r[status]) ? $allwords[9] : $allwords[10];

          $tt['launch'] = "<a href='{$sitepath}test_test.php?c=start&tid={$r['tid']}&teachertest=1' title='" . _('Открыть задание') . "' target='_blank'><img src='{$sitepath}images/icons/look.gif' border='1'></a>";

          $tmps = '';

          $sss =($r[status]) ? $allwords[16] : $allwords[17];
          $tt['status'] = $sss;

          $tt['owner']=($r[cid]!=$r[cidowner])?helptitle($allwords[8],$allwords[7]):"";
          //$tt['ELCOL']=($r[qcount]) ? "( ".$r[qcount]." )" : "";
          $tt['ELCOL'] = sqlrows(test_getkod($r));
          $tt['change']=date("d-m-y",$r[last]);
          if ($s['perm']>1) {
          $tt['delete'] = $tt['edit'] = $tt['copy'] = '';
          if ($is_perm_edit && !$is_locked_course) {
              	$tt['delete']="<a href=\"[PAGE]?[SESSID]c=del&cid=".$r['cid']."&tid=".$r['tid']."\"
              	onClick=\"return confirm('[W-delete]')\" title=\"[W-delete]\"> ".getIcon("delete", _("Удалить задание"))." </a>";
              	$tt['copy']="<a href=\"[PAGE]?[SESSID]c=copytest&cid=".$r['cid']."&tid=".$r['tid']."\" title=\""._('Копировать задание')."\"
              	onClick=\"if (!confirm('"._('Скопировать тест?')."')) return false; return true;\"> ".getIcon("copy", _("Копировать задание"))." </a>";
                $tt['edit']="<a href=\"[PAGE]?[SESSID]c=edit2&cid=".$r['cid']."&tid=".$r['tid']."\" title=\"[W-edit]\"> ".getIcon("edit", _("Редактировать задание"))." </a>";
          } else {
            $tt['delete'] = "";
            $tt['edit'] = "";
            if ($r['cidTrue'] != $r['cid']) {
                $tt['delete'] = $toolTip->display('is_cms_test');
            	if (!$is_locked_course) {
                    $tt['copy']="<a href=\"[PAGE]?[SESSID]c=copytest&cid=".$r['cid']."&tid=".$r['tid']."\" title=\""._('Копировать задание')."\"
                    onClick=\"if (!confirm('"._('Скопировать задание?')."')) return false; return true;\"> ".getIcon("copy", _("Копировать задание"))." </a>";
                } else {
                    $tt['copy'] = '';
                }
            } else {
                $tt['copy'] = '';
            }
          }
   		  }
   		  else {
   		    $tt['delete'] = "";
            $tt['edit'] = "";
            $tt['copy'] = "";
   		  }
          $tmp.=words_parse($tm1,$tt,"TT-");
   }
   }
   if ($lastcid>0) {
   $tmain.=str_replace("[TEST-LIST]",$tmp,$tmp2);
   }else {
       $tmain .= str_replace("[TEST-LIST]","<tr><td colspan=99 align='center'>"._("нет данных для отображения")."</td></tr>",$tm);
   }
   $tmain=str_replace("[SESSID]",'',$tmain);
   $tmain=str_replace("[PATH]",$sitepath,$tmain);
   $tmain=str_replace("[PAGE]",$page,$tmain);
   $tmain=str_replace("[PATH-CORNER]",$GLOBALS['controller']->enabled ? $GLOBALS['controller']->view_root->skin_url . '/' : $sitepath, $tmain);

   $courseall="<option value=\"\">"._("Все курсы")."</option>";
   $courselist=selCourses($s[tkurs],$cid);
   $courselist_arr=selCourses($s[tkurs],$cid, $GLOBALS['controller']->enabled);

   $courselist = '';
   foreach($courselist_arr as $k=>$v) {
   	   $v_short = (strlen($v) > 60) ? substr($v, 0, 60) . "..." : $v;
       if (is_course_locked($k)) continue;
       $courselist .= "<option value=\"{$k}\"";
       if ($k==$cid) $courselist .= " selected ";
       $courselist.= " title='{$v}'>{$v_short}</option>";
   }

   $GLOBALS['controller']->addFilter(_("Курс"), 'CID', $courselist_arr, $cid, true);
   $GLOBALS['controller']->setFilterScope('m16', $str, $gr);
   if (!$cid) {
       $GLOBALS['controller']->terminate();
       exit();
   }

   $html=str_replace("[ALL-CONTENT]",$allcontent,$html);
   // заголовок вставки
   $html=str_replace("[TTASKS-HEADER]",$cheader,$html);
   // содержимое вставки
   $html=str_replace("[TTASKS-MAIN]",$tmain,$html);
   // выпадающий список курсов
   $html=str_replace("[HIDDEN-CID]",$hcid,$html);
   // добавлят внизу помощь
   $html=str_replace("[TTASKS-NOTES]",$tnotes,$html);
   // список курсов имя - курcid для выбора при создании нового занятия
   $html=str_replace("[SELECT-COURSES]",$courseall.$courselist,$html);
   $html=str_replace("[COURSES-LIST]",$courselist,$html);
   // заменяет [PAGE] на $PHP_SELF
   $html=str_replace("[PAGE]",$page,$html);
   $html=str_replace("[OKBUTTON]",okbutton(),$html);

   // вывод всей html ки на экран с обрабокой [SESSID] [PATH] [W-]
   if ($s[usermode]) {
       $tmp1=array_keys($s[tkurs]);
       if (isset($s[cid]))
          $tmpcid=$s[cid];
       else
          $tmpcid=$s[tkurs][$tmp1[0]];
          $html=str_replace("[URLLOG]",
          "<a href=test_log.php?cid={$s[$ss][cid]}$sess>"._("Отчеты о тестированиях")."</a><br>".
          "<a href=test_util.php?c=limitclean&cid=$tmpcid$sess>"._("Попытки тестирований")."</a><br>".
          "<!--a href=test_stat.php?c=stat&tid=$tid&cid=$tmpcid$sess>"._("Статистика вопросов")."</a-->",
          $html);
   }
   else {
       $html=str_replace("[URLLOG]","",$html);
   }

   $GLOBALS['controller']->captureFromReturn(CONTENT, $html);

   printtmpl($html);
   //define("dimatestcron",1);
   //include("test_cron.php");
   exit;

case "stat":
    $GLOBALS['controller']->captureFromOb(CONTENT);
    echo  _("СТАТИСТИКА по");
    intvals("tid cid");
    echo "TID=$tid CID=$cid<BR>";
    $s=showTaskStatistic( $TID, $CID );
    echo $s;
    $GLOBALS['controller']->captureStop(CONTENT);
exit;
case "edit":
   $GLOBALS['controller']->setHelpSection('test');
    // вывод вопросов теста
   intvals("tid cid");

   //if (!isset($s[tkurs][$cid])) exitmsg("HackDetect: "._("нет прав редактировать данное задание"));
   $s[$ss][cid]=$cid;
   $s[$ss][tid]=$tid;

   $obz=array();
   $res=sql("SELECT * FROM testneed WHERE tid=$tid","errTT919");
   while ($r=sqlget($res)) {
      $obz[$r[kod]]=1;
   }
   sqlfree($res);

   $res=sql("SELECT * FROM test WHERE tid=$tid AND cid=$cid","errTT71");
   if (sqlrows($res)==0) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
   $r=sqlget($res);
   if (!$GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OTHERS)
       && !($GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OWN) && ($r['created_by']==$_SESSION['s']['mid']))) {
       exitmsg(_("У вас не хватает привилегий"),"$PHP_SELF?$sess");
   }
   sqlfree($res);
   $isedit=0;
   if ($r[cid]==$r[cidowner]) $isedit=1;
   $s[$ss][tid_title]=$r[title];

   $kodlist=explode($brtag,$r[data]);
   $kodlist=implode("\r\n",$kodlist);
   $kodlist=str_replace("%","*",$kodlist);
   $kodlist=str_replace("_","?",$kodlist);

   echo show_tb();
   if( ($s[perm] == 2)&&(check_teachers_permissions(21, $s[mid])) ) {
   	$str_tmp =
   	"<a href='$PHP_SELF?$sess' title='"._("Перейти к другому заданию")."'>"._("Задание")."</a>
   	<A href='#' onclick=\"wopen('$PHP_SELF?c=edit2&cid=$cid&tid=$tid$sess')\"
   	title='"._("Редактировать параметры задания")."'>$r[title]</a>
   	<font class=s8 style='text-decoration:none;font-weight:normal;font:8pt Tahoma'>(".($r[status]?_("опубликовано"):_("не опубликовано")).")</small>";
   }
   else {
   	$str_tmp =
   	"<a href='$PHP_SELF?$sess' title='"._("Перейти к другому заданию")."'>"._("Задание")."</a>
   	<A href='#'>$r[title]</a>
   	<font class=s8 style='text-decoration:none;font-weight:normal;font:8pt Tahoma'>(".($r[status]?_("опубликовано"):_("не опубликовано")).")</small>";
   }

   echo ph($str_tmp);

	if(($s[perm] == 2)&&(check_teachers_permissions(21, $s[mid])) )
   		html_new_question($cid,$tid,1,0);

   //
   // Основная таблица
   //

   $is_locked_course = is_course_locked($cid);

   $GLOBALS['controller']->setHeader($r[title]);
   $GLOBALS['controller']->captureFromOb(CONTENT);
   if (($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)
       || $GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN))
       && !$is_locked_course) {
       $GLOBALS['controller']->setLink('m160102', array($tid));
       $GLOBALS['controller']->setLink('m160104', array($cid, $tid));
   }
   $GLOBALS['controller']->setLink('m160103', array($tid));
   $GLOBALS['controller']->setLink('m160106', array($tid));
   //$GLOBALS['controller']->setLink('m160109', array($tid));

   if ($tid)
   echo "<div style=\"padding-bottom: 5px;\">
	<div style=\"float: left;\">
	    <img src=\"images/icons/small_star.gif\"/>
	</div>
	<div>
	    <a style=\"text-decoration: none;\" href=\"{$sitepath}test_list.php?c=add&tid={$tid}\">создать вопрос</a>
   </div>         
   </div>";
   
   echo "
   <form action=$PHP_SELF method=post>$sessf
   <input type=hidden name=c value='edit_post'>
   <input type=hidden name=tid value=\"$tid\">
   <input type=hidden name=cid value=\"$cid\">";

   echo
   "<table width=100% class=main cellspacing=0>
   <tr>
   ".($s[usermode]?"<th>".
       sortrow(2,_("Код"),"kod",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>":"")."
   <th width=4%>"._("Обязат. вопрос")."</th>
   <th width=40%>".sortrow(2,_("Текст вопроса"),"vopros",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>
   <th width=5%>".sortrow(2,_("Тип"),"type",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>
   ".(!$is_locked_course?"<th width=8%>"._("Действия")."</th>":"")."
   </tr>";


   if (trim($r[data])=="") $mylist=array();
   else {
      $mylist=str_replace("%","*",$r[data]);
      $mylist=str_replace("_","?",$mylist);
      $mylist=explode($brtag,$mylist);
   }

   if (!count($mylist)) {
      echo "<tr><td colspan=100 align=center>"._("в это задание еще не включено ни одного вопроса")."</td></tr>";
   }
   else {
      //
      // Цикл основной таблицы - подготовка массива
      //

      $show=array();
      foreach ($mylist as $v) {
         $good=0;
         $kod="";
         if (strpos($v,"?")===false && strpos($v,"*")===false) {
            $sqlarr=sqlval("SELECT * FROM list WHERE kod='".addslashes($v)."'","errTT490");
            $kod=$sqlarr[kod];
            if (!$sqlarr['qtype']) continue;
            if (!is_array($sqlarr)) {
               $showvopros=_("Вопрос не найден в базе данных");
               $showlast=0;
               $showtype=-2;
               $kod=$v;

            }
            else {
               include_once("template_test/$sqlarr[qtype]-v.php");
               $func='v_sql2php_'.$sqlarr[qtype];
               $vopros=$func($sqlarr);
//               echo $vopros;
               $showvopros=strip_tags(wordwrap(qdata2text($sqlarr[qdata]),20," ",1));
               $showlast=$sqlarr[last];
               //$showlast=date("d-m-Y",$sqlarr[last]);
               $showtype=$sqlarr[qtype];
               //$showtype=$visualchar[$sqlarr[qtype]];
               $showtypehelp=$GLOBALS['v_edit_'.$sqlarr[qtype]]['title'];
               $good=1;
            }
         }
         else {
            $showvopros="- "._("маски не отображаются")." -";
            $showlast=0;
            $showtype=-1;
            $kod=$v;
            //$showtype="<font face=system>n/a</font>";
         }
         $show[]=array(
            "kod"=>$kod,
            "vopros"=>$showvopros,
            "last"=>$showlast,
            "type"=>$showtype,
            "typehelp"=>$showtypehelp,
            "good"=>$good,
            "qtema"=>$sqlarr[qtema],
            'created_by'=>$sqlarr['created_by'],
         );
      }

      sortarray($show,$s[$ss][ttorder],$s[$ss][ttdesc], SORT_KODS);

      //
      // Цикл основной таблицы - показ на экране
      //
      foreach ($show as $k=>$v) {
      $question_perm_edit =
           ($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)
           || ($GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN)
           && ($v['created_by']==$_SESSION['s']['mid'])));

         $k=$v[kod];

         echo "<tr>";
         if ($s[usermode]) echo "<td align=left>".htmlwordwrap($k,7,"\n")."</td>";

         // обязательнный ли вопрос
         echo "<td ><input type=checkbox name='obz[]' value=\"".html($k)."\" ".(isset($obz[$k])?"checked":"")."></td>";

         // текст вопроса
         echo "<td width=100%>";
         if ($question_perm_edit) {
             echo "<a href=\"#\" target='preview' ".
                "onclick=\"wopen('test_vopros.php?kod=".ue($k)."&cid=$cid&mode=2$asess','preview'); return false;\" title=\""._("Просмотреть вопрос")."\">";
         }
         /*nl2br(strip_tags($v[vopros])); - так нельзя ибо текст вопроса может сожержать ХТМЛ*/
         $question = nl2br($v[vopros]);
         if(strlen($question) > 200)
         	$question = substr($question, 0, 200).'...';
         echo $question;
         if ($v[qtema]!="") echo "<br><b>&lt;</b>" . _("Тема") . "<b>&gt;</b>: $v[qtema]";

         if ($question_perm_edit) {
            echo "</a>";
         }
         echo "</td>";

         // тип вопроса:
         echo "<td width=5% align=center style=\"cursor:hand\" title=\"$v[typehelp]\"><span class=sym>".
            (!empty($v[type])?getVisualChar( $v[type], $v[typehelp] ):"")."</span></td>";

         // изменен:
/*         echo "<td width=8% nowrap style='font: 8pt Tahoma'>".
            ($v[last]?date("d-m-Y",$v[last]):"")."</td>";
*/
         // команды
         if ($question_perm_edit && !$is_locked_course) {
         echo "<td width=8% align=center nowrap>";
             //   ссылка: править
             if ($v[good]) {
                 list($questionCid, $dummy) = explode('-', $k);
                if( ($s[perm] >= 2)) {
                	echo "<a href='#' onclick=\"wopen('test_vopros.php?kod=".ue($k)."&cid=$cid&mode=2$asess','preview'); return false;\" title='" . _('Просмотреть вопрос') . "'><img src='{$sitepath}images/icons/look.gif' border='1'></a>&nbsp;";
                }
                if( ($s[perm] >= 2)&&(check_teachers_permissions(21, $s[mid])) && in_array($questionCid, $GLOBALS['s']['tkurs']) ) {
             		echo "<a href='test_list.php?c=b_edit&che[]=".ue($k)."&goto=2&gotourl=".ue("test_test.php?tid=$tid&cid=$cid&c=edit$asess")."$asess'
                       class=\"wing\" title='" . _("Править") . "'>".getIcon("edit")."</a>";
                }
             }
             if( ($s['perm'] >= 2)&&(check_teachers_permissions(21, $s[mid])) && in_array($questionCid, $GLOBALS['s']['tkurs']) ) {
    			echo "<a href='$PHP_SELF?c=delete&cid=$cid&tid=$tid&jsclose=1&che[]=".ue($k).$asess."i=1#$k'
          		onclick='return confirm(\"" . _("Вы действительно желаете удалить вопрос из задания?") . "\")'>
          		".getIcon("delete", _("Удалить"))."</a>";
             }
             echo "</td>";
         }

         echo "</tr>";
      }
   }

   if(0 && ($s[perm] >= 2)&&(check_teachers_permissions(21, $s[mid])) ) {

         echo "<tr><td colspan=2>"._("маска:")."</td><td colspan=4>
      <textarea name=form[data] rows=3 cols=40 style='width: 100%' ".($isedit?"":"disabled").
      ">".html($kodlist)."</textarea></td></tr>";

   	if (!$GLOBALS['controller']){
   	echo "<tr>
   	<td nowrap colspan='50' class=shedadd1>
   	<table width=100% border=0 cellspacing=0 cellpadding=0><tr>
   	<td nowrap><a href=\"javascript:wopen('test_test.php?c=start&tid=$r[tid]&teachertest=1$asess','schedul')\">"._("Выполнить это задание")."</a></td>
   	<td nowrap align=right><b><a href='test_list.php?$sess'>"._("Добавить из всех вопросов")."</a></b></td>
   	</tr></table>
   	</td></tr>";
   	}
   }
   echo "</table><P>";

   if ($s[usermode]) {
//      echo "<b>Расширенное редактирование</b><P>";

      if (isset($err)) echo "<table width=100% border=1 cellspacing=0 cellpadding=20 bgcolor=#ffdddd><tr><td>
      <font color=red><b>"._("Внимание!")."</b></font> "._("Из этого списка были
      автоматически удалены следующие ошибочные маски:")."
      <ul><pre>".implode("\n",explode("~",$err))."</pre></ul>
      "._("Данные маски начинаются не на номер вашего курса и символ тире.
      Если вы опять введете какую-либо маску, не начинающуюся на")."
      \"<tt><nobr><b>$cid-</b></nobr></tt>\", "._("то она будет повторно удаляться.")."</td></tr></table><P>";

//      echo "В этом поле вы можете редактировать/добавлять/удалять
//      любые вопросы в более удобном виде. Самое главное: так вы можете добавить
//      маску вопросов, которая будет включать в себя сразу группу вопросов! ";

/*      helpalert("РЕДАКТИРОВАНИЕ КОДОВ И МАСОК ВОПРОСОВ

      В этом поле содержится список кодов вопросов и масок вопросов,
      которые будут включены в данное задание. Таблица выше содержит
      тоже самое, но она не позволяет редактировать как угодно вопросы
      и быстро вводить любые коды вопросов (или маски).Таблица со
      списком выше позволяет только лишь удалять вопросы. А в данном
      поле вы можете приписать, изменить или стереть любые коды/маски
      вопросов.

      МАСКИ ВОПРОСОВ: напишите через пробел или новую строку несколько
      масок вопросов, допускается '*' и '?'. Символ звездочки подразу-
      мевает (замещает) любое число символов любой длины (от 0 и более
      любых символов). Символ вопроса подразумевает ровно один любой
      символ.

      Маска должна начинаться на номер текущего курса ($cid - число) и
      символ минус/тире (-).

      Далее можно использовать большие/маленькие английские буквы (они
      различаются!), цифры и тире. Начало кода вопроса на текущем
      курсе: '$cid-'. Если вы впишите маску вопроса, которая начинается
      не с '$cid-' или содержит посторонние символы (не буквы/цифры/тире),
      то все такие маски не будут сохранены.

      Например, маска '$cid-*' поместит в этот тест ВСЕ ваши вопросы
      (с текущего курса). А маска '$cid-tema20-*' пометит все вопросы,
      которые начинаются на данную строчку.","[Справка]");

*/      if (!$isedit) {
         echo "<br><font color=red><b>"._("Расширенное редактирование задания запрещено.")."</b></font> ";
         helpalert(_("Вы не можете редактировать вопросы, т.к. этот тест создан
         на другом курсе")." '".cid2title($r[cidowner])."' (ID=$r[cidowner]).
         "._("Вы можете лишь удалить целиком тест, изменить другие его параметры
         или перенести еще раз на другой курс. Если вернуть тест на прежний курс
         то редактирование будет доступно. Вы можете лишь удалять с этой страницы
         любые вопросы."),"["._("Справка")."]");
      }
      echo "<P>";
   }


   if (!$is_locked_course) {
       echo okbutton();
   }
   echo "
   </form>
   ";

   $GLOBALS['controller']->captureStop(CONTENT);

   if ($s[usermode]) {
      if( ($s[perm] == 2)&&(check_teachers_permissions(21, $s[mid])) ) {
   	  	echo "
      	<P><li><a href=test_util.php?c=limitclean&cid=$cid>"._("Управление кол-вом попыток тестирований")."</a>

      	";
      }
      echo "<li><a href=test_log.php?cid={$s[$ss][cid]}&tid={$s[$ss][tid]}$sess>"._("Отчеты о тестированиях на этом задании")."</a>
      ";
   }
   echo "<P><font size=-2>"._("Режим редактирования:")." ".
   ($s[usermode]==0?"<b><u>":"")."<a href=$PHP_SELF?c=setmode&tid=$tid&cid=$cid&mode=0>["._("базовый")."]</a>".($s[usermode]==0?"</u></b>":"")." ".
   ($s[usermode]==1?"<b><u>":"")."<a href=$PHP_SELF?c=setmode&tid=$tid&cid=$cid&mode=1>["._("расширенный]")."</a>".($s[usermode]==1?"</u></b>":"").
   " - "._("только для опытных пользователей!");
   echo show_tb();
   exit;


case "start":

   $s[jsclose]=1;
   if ($s[me]) exit("
   <center><table width=70% height=99% border=0 cellspacing=0 cellpadding=0><tr><td align=center>
   "._("Вы не можете начать это задание, т.к. еще не закончили
   выполнять предыдущее. Вы можете либо пройти до конца предыдущий
   сеанс тестирования, либо прервать его.")."<br><br><br><br>
   <b><a href='".($s[me]==1?"test_vopros.php":"test_end.php")."?$asess'>"._("Вернуться к предыдущему сеансу тестирования")."</a></b> &gt;&gt;
   </td></tr></table><script>window.focus()</script>
   ");
   $closeSheduleWindow = true;
   session_register("closeSheduleWindow");
   refresh("test_start.php?tid=$tid&teachertest=1&jsclose=1$asess");
   exit;



case "edit2":
//    $GLOBALS['controller']->setView('DocumentPopup');
    $GLOBALS['controller']->setHeader(_("Редактирование задания"));
case "add"://если !$tid

    if (!strlen($GLOBALS['controller']->getHeader())) {
//       $GLOBALS['controller']->setView('DocumentPopup');
       $GLOBALS['controller']->setHeader(_("Добавление задания"));
   }
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $toolTip = new ToolTip();
   $GLOBALS['controller']->setHelpSection('edit');
   intvals("tid cid");

   //выбираем список курсов
   $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
   $sql = "SELECT CID, Title FROM Courses ORDER BY Title";
   $res = sql($sql);
   $courses = array();
   while($row = sqlget($res)) {
       //if (!$courseFilter->is_filtered($row['CID'])) continue;
       $courses[$row['CID']] = $row['Title'];
   }

   if ($tid) {

/*   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав редактировать данный тест"));
           $GLOBALS['controller']->setMessage(_('нет прав редактировать данный тест'), JS_CLOSE_SELF_REFRESH_OPENER, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }*/
   $s[$ss][cid]=$cid;

   $res=sql("SELECT * FROM test WHERE tid=$tid AND cid=$cid","errTT71");
   if (sqlrows($res)==0) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);

   // =========================================================================================
   /**
   * Дополнительно селектим распределение вопросов по темам если есть
   */
   $r[questions] = false;
   $res = sql("SELECT questions FROM testquestions WHERE tid=$tid AND cid=$cid");
   if (sqlrows($res) == 1) {$questions = sqlget($res); $r[questions] = unserialize($questions[questions]);}
   sqlfree($res);
   // =========================================================================================

   $isedit=0;
   if ($r[cid]==$r[cidowner]) $isedit=1;

   $kodlist=explode($brtag,$r[data]);
   $kodlist=implode("\r\n",$kodlist);
   $kodlist=str_replace("%","*",$kodlist);
   $kodlist=str_replace("_","?",$kodlist);

//   echo show_tb();
//   echo "<h3>Редактирование задания</h3>
   $html=path_sess_parse(create_new_html(0,0));
   $html=explode("[ALL-CONTENT]",$html);
   echo $html[0];
    }else {
        //Значения по умолчанию
        $r = array(
            'title'         => _("Задание"),
            'datatype'      => 1,
            'lim'           => 0,
            'qty'           => 1,
            'sort'          => 0,
            'free'          => 0,
            'skip'          => 0,
            'rating'        => 0,
            'random'        => 1,
            'startlimit'    => 1,
            'limitclean'    => 0,
            'timelimit'     => 0,
            'showurl'       => 1,
            'endres'        => 1,
            'allow_view_log'=> 1,
            'status'        => 0
            );
    }

   /**
   * JavaScripts
   */
   echo
   "<script language=JavaScript>
   <!-- //
   function test_showDiv(showId, hideId) {

       document.getElementById(showId).style.display = 'block';
       document.getElementById(hideId).style.display = 'none';

   }
   // -->
   </script>";

   echo "<form action=\"$PHP_SELF\" method=\"post\">
   <input type=hidden name=c value='edit_post2'>
   <input type=hidden name=tid value=\"$tid\">
   <input type=hidden name=cid value=\"$courseId\">
   ".($tid ? "<input type=hidden name=cid value=\"$cid\" />" : '')."
   <table width=100% class=main cellspacing=0>
   <!--tr><th colspan=2>"._("Редактирование задания")."</th></tr-->
   <tr><td width='25%'>
   "._("Название задания")."</td><td>
   <input type=text name=form[title] value=\"".html($r[title])."\" style='width:100%'></td></tr>";

   $coursesSelect = "<select ".($tid ? 'disabled=disabled' : '')." name='cid'>";
   if (is_array($courses) && count($courses)) {
       foreach ($courses as $courseId => $courseTitle) {
           $selected = $courseId==$cid ? 'selected=selected' : '';
           $coursesSelect .= "<option $selected value='$courseId'>$courseTitle</option>";
       }
   }
   $coursesSelect .= '</select>';

   if (!$tid) {
   /*echo "<tr>
            <td>"._("Курс")."</td>
            <td>$coursesSelect</td>";*/
   }

   echo "<tr><td></td><td>
   <input type=checkbox name=form[status] id=st1 value=1 ".($r[status]==1?'checked':'')."><label for=st1>
   <b>"._("Задание опубликовано")."</b> ("._("доступно слушателю для выполнения").")</label></td></tr>";



   echo "<tr><td>"._("Режим прохождения")."</td>";
   echo "<td><input type=\"radio\" name=\"form[mode]\" value=\"0\"";
   if (!$r['mode']) echo " checked ";
   echo "> {$GLOBALS['TEST_MODES'][0]} &nbsp;";
   echo $toolTip->display('test_mode_0');
   echo "<br>";
   echo "<input type=\"radio\" name=\"form[mode]\" value=\"1\"";
   if ($r['mode']==1) echo " checked ";
   echo "> {$GLOBALS['TEST_MODES'][1]} &nbsp;";
   echo $toolTip->display('test_mode_1');
   echo "<br>";
   echo "<input type=\"radio\" name=\"form[mode]\" value=\"2\"";
   if ($r['mode']==2) echo " checked ";
   echo "> {$GLOBALS['TEST_MODES'][2]} &nbsp;";
   echo $toolTip->display('test_mode_2');
   echo "</td></tr>";

   //echo "<br /><input type='radio' name='form[without_answer]' value='1' ".($r[without_answer] == 1?'checked':'')."> Без ответа&nbsp;&nbsp;<input type='radio' name='form[without_answer]' value='0' ".($r[without_answer]==0?'checked':'')."> С ответом";

   echo "<!--tr><th colspan=2>"._("Вопросы задания")."</th></tr-->
        <tr>
            <td>"._("Способ выбора вопросов")."</td><td>";

   $genq = array('',''); $style = array('','');
   if (!$r[questions]) {
       $genq[0] = 'checked'; $style[1] = "style=\"display: none;\"";
   } else {
       $genq[1] = 'checked'; $style[0] = "style=\"display: none;\"";
   }

   //echo "<input onClick=\"putElement('gen0','table-row'); removeElem('gen1'); /*test_showDiv('gen0','gen1');*/\" type=radio name=form[genq] {$genq[0]} value=\"0\"> "._("Одинаковое количество вопросов из каждой темы")."&nbsp;";
   echo "<input onClick=\"$('#gen0').show();
                          $('tr.qthemes').hide();\"
                type=radio
                name=form[genq] {$genq[0]} value=\"0\"> "._("Одинаковое из каждой темы")."&nbsp;";
   echo $toolTip->display('topic_sort_1');
   echo "<br>";
   //echo "<input onClick=\"putElement('gen1','table-row'); removeElem('gen0'); /*test_showDiv('gen1','gen0');*/\" type=radio name=form[genq] {$genq[1]} value=\"1\"> "._("Задать количество вопросов из каждой темы")."&nbsp;";
   echo "<input onClick=\"$('tr.qthemes').show();
                          $('#gen0').hide();\"
                 type=radio
                 name=form[genq] {$genq[1]} value=\"1\"> "._("Задать из каждой темы")."&nbsp;";
   echo $toolTip->display('topic_sort_2');
   echo "<br>";

   echo "
   <tr id=\"gen0\" {$style[0]}><td>
   "._("Ограничить количество вопросов в задании до")."</td><td>
   <input size=6 type=text name=form[lim] value=\"$r[lim]\" maxsize=6> &nbsp;";
   echo $toolTip->display('quest_in_test');
   echo "<br>(0 - "._("показывать все").")";
   echo "</td></tr>";


   /**
   * Выбор количества вопрос из конкретных тем
   */
   if ($testThemes = test_getThemesArray($r)) {
        $tmp_flag = 0;
        while ($theme = sqlget($testThemes)) {

            $tmp_flag = 1;
            $theme[qtema] = strlen($theme[qtema]) ? $theme[qtema] : 'Без названия';

            echo "<tr class=\"qthemes\" {$style[1]}>";
            echo "<td>"._("Ограничить количество вопросов из темы")." \"".$theme[qtema]."\":</td>
                  <td>
                    <input type=text size=6 name=\"form[questions][{$theme[qtema]}]\" value=\"{$r[questions][$theme[qtema]]}\" maxsize=6></td>
                  </tr>";

        }

        if (!$tmp_flag) {
            echo "<tr {$style[1]} class=\"qthemes\"><td colspan=2>"._("Нет тем вопросов в задании")."</td></tr>";
        }

        echo "</tr>";
   }

   //echo "</td></tr>";
   echo "<tr><td>
   "._("Количество вопросов на страницу")."</td><td>
   <input size=6 type=text name=form[qty] value=\"$r[qty]\"> &nbsp;";
   echo $toolTip->display('quest_on_page');
   echo "</td></tr>";

   echo "<tr><td></td><td>
   <input size=6 type=checkbox name=form[random] value=1 id=ra1 ".($r[random]==1?'checked':'')."><label for=ra1>
   "._("Перемешивать вопросы при прохождении задания")."
   </label>";
   echo $toolTip->display('mix_quest');
   echo "</td></tr>";

   echo "<!--tr><th colspan=2>"._("Выполнение задания")."</th></tr-->
        <tr>
            <td>
   "._("Сколько раз учащийся может выполнять это задание")."</td><td>
   <input size=6 type=text name=form[startlimit] value=\"$r[startlimit]\"> &nbsp;";
   echo $toolTip->display('number_of_pass');
   echo "<br>(0 - "._("кол-во не ограничено").") ";
   echo "</td></tr>";


   echo "<tr><td>
   "._("Через сколько дней сбрасывать данный счетчик прохождений")."</td><td>
   <input size=6 type=text name=form[limitclean] value=\"$r[limitclean]\"> &nbsp;";
   echo $toolTip->display('clear_counter');
   echo"<br>(0 - "._("никогда не сбрасывать").")";
   echo "</td></tr>";

   echo "<tr><td>
   "._("Ограничение времени в минутах для прохождения задания")."</td><td>
   <input size=6 type=text name=form[timelimit] value=\"$r[timelimit]\"> "._("мин")."&nbsp;";
   echo $toolTip->display('time_limit');
   echo "<br>(0 - "._("без ограничений").") ";
   echo "</td></tr>";

   echo "<!--tr><th colspan=2>"._("Дополнительно")."</th></tr-->
   <tr><td rowspan=5></td><td>
   <input type=checkbox name=form[questres] value=1 id=qe1 ".($r[questres]==1?'checked':'')."><label for=qe1>
  "._("Показывать страницу промежуточных результатов учащегося, по итогам последних вопросов")."</label> &nbsp;";
   echo $toolTip->display('intermediate_result');
   echo "</td></tr>";

   echo "<tr><td>
   <input type=checkbox value=1 name=form[showurl] id=so1 ".($r[showurl]==1?'checked':'')."><label for=so1>
   "._("Там же показывать ссылку")."</label> &nbsp";
   echo $toolTip->display('url_on_page');
   echo "</td></tr>";

   /*echo "<!--
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=checkbox value=1 name=form[showotvet] id=sot1 ".($r[showotvet]==1?'checked':'')."><label for=sot1>
   "._("Правильные ответы")."</label>
   <br>--><br>";
   */


   echo "<tr><td>
   <input type=checkbox name=form[endres] id=en1 value=1 ".($r[endres]==1?'checked':'')."><label for=en1>
   "._("Показывать результаты тестирования в конце задания (общие результаты)")."</label> &nbsp;";
   echo $toolTip->display('final_results');
   echo "</td></tr>";

   echo "<tr><td>
   <input type=checkbox name=form[skip] id=sk1 value=1 ".($r[skip]==1?'checked':'')."><label for=sk1>
   "._("Позволять досрочно завершать тестирование с получением оценки")."</label> &nbsp;";
   echo $toolTip->display('pre-term_ending');
   echo "</td></tr>";

/*   echo "<tr><td>
   Cлучайные величины</td><td>
   <input size=30 style='width: 100%' type=text name=form[random_vars] value=\"".html($r[random_vars])."\"> ";
   helpalert(_("Позволяет вводить случайные величины путем перечисления или задания диапазона.
   Названия переменных должны состоять из прописных букв латинского алфавита.
   Если необходимо задать несколько переменных, то между их описанием следует использовать символ &quot;;&quot;
   ПРИМЕРЫ: -12.1-15:X;14-16.75:Y;&quot;sin&quot;,&quot;cos&quot;,&quot;tg&quot;,&quot;ctg&quot;:FUNC "),"["._("Справка")."]");
   echo "</td></tr>";
*/
   echo "<tr><td>
   <input type=checkbox name=form[allow_view_log] value=1 id=qe1 ".($r['allow_view_log']==1?'checked':'')."><label for=qe1>
   "._("Разрешить просмотр подробного отчета слушателем")."</label> &nbsp;";
   echo $toolTip->display('detailed_report');

   echo "<tr><td>"._("Комментарий к заданию").": </td><td width=\"50%\"><textarea name=\"form[comments]\" style=\"width: 100%\" rows=5>{$r['comments']}</textarea></td></tr>";

   echo "</td></tr>
    <tr>
        <td colspan=2>
            <table align='right'>
                <tr>
                    <td>".button(_("Отмена"), '', 'cancel', '', $GLOBALS['sitepath'].'/test/list/index/course_id/'.$cid)."</td>
                    <td>".okbutton()."</td>
                </tr>
            </table>
        </td>
    </tr>
    </table>
    </form>";
   $GLOBALS['controller']->captureStop(CONTENT);
   $GLOBALS['controller']->terminate();
   echo $html[1];
   break;




case "edit_post":
//  pr($_POST);
//  die();

   intvals("tid cid");
   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав редактировать данный тест"));
       $GLOBALS['controller']->setMessage(_('нет прав редактировать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();

   }

   $res=sql("SELECT * FROM test WHERE tid=$tid AND cid=$cid","errTT71");
   if (!sqlrows($res)) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
   $rr=sqlget($res);
   sqlfree($res);
   $isedit=0;
//   if ($rr[cid]==$rr[cidowner] && $s[usermode]) $isedit=1;

   if ($isedit) {

      $kodlist=$form[data]=trim($form[data]);
      if ($kodlist!="") {
         $form[data]=preg_replace("![^a-zA-Z0-9*? \n-]!","",$form[data]);
         $form[data]=trim(preg_replace("![ \n]+!"," ",$form[data]));
         $form[data]=explode(" ",$form[data]);
         $tmp="$cid-";
         $err=array();
         foreach ($form[data] as $k=>$v) {
            if (substr($v,0,strlen($tmp))!=$tmp) {
               unset($form[data][$k]);
               $err[]=$v;
            }
         }
         $form[data]=implode(($brtag),$form[data]);
         $form[data]=str_replace("*","%",$form[data]);
         $form[data]=str_replace("?","_",$form[data]);
         $kodlist=trim($form[data]);
      }

      global $adodb;
      $rq="UPDATE test SET
         data=".$adodb->Quote($kodlist).",
         last=".time()."
         WHERE cid=$cid AND tid=$tid
      ";
      $res=sql($rq,"errTT166");
      sqlfree($res);

   }

   $res=sql("DELETE FROM testneed WHERE tid=$tid","errTT909");
   sqlfree($res);
   if (is_array($obz) && count($obz)) {
   	foreach ($obz as $v) {
               $rq="INSERT INTO testneed (tid,kod) VALUES ";
               $rq.="($tid,'".addslashes($v)."'),";
               $rq=substr($rq,0,-1);
               $res=sql($rq,"errTT915");
               sqlfree($res);
   	}
   }

   if (count($err)) exit(refresh("$PHP_SELF?c=edit&cid=$cid&tid=$tid&err=".ue(implode("~",$err))."$sess"));
   exit(refresh("$PHP_SELF?c=edit&tid=$tid&cid=$cid$sess"));


case "edit_post2":

   intvals("tid cid");

   /*if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав редактировать данный тест"));
       $GLOBALS['controller']->setMessage(_('нет прав редактировать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       
       exit();
   }*/

   if ($tid) {
       $res=sql("SELECT * FROM test WHERE tid=$tid","errTT71");
   if (!sqlrows($res)) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
   $rr=sqlget($res);
/*   if (!$GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OTHERS)
       && !($GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OWN) && ($rr['created_by']==$_SESSION['s']['mid']))) {
       exitmsg(_("У вас не хватает привилегий"),"javascript:window.close();");
   }*/
   if (is_course_locked($rr['cid'])) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_("Курс заблокирован. Данная операция невозможна"),JS_GO_URL,"javascript:window.close();");
       $GLOBALS['controller']->terminate();
       exit();
   }

   //прооверка лимита времени
   if ($form['timelimit']) {
       $totalTime = 0;
       $dummy = explode($brtag, $rr['data']);
       $sql = "SELECT timetoanswer FROM list WHERE kod IN ('".implode("','",$dummy)."')";
       $res = sql($sql);
       while ($row = sqlget($res)) {
           $totalTime += (int)$row['timetoanswer'];
       }
       if ($totalTime && $totalTime > $form['timelimit']) {
           $GLOBALS['controller']->setView('DocumentBlank');
           $GLOBALS['controller']->setMessage(_("Ограничение времени выполнения задания меньше чем суммарное ограничение времени ответа на вопросы."),JS_GO_BACK);
           $GLOBALS['controller']->terminate();
           exit();
       }
   }
   sqlfree($res);
   $isedit=0;
   if ($rr[cid]==$rr[cidowner]) $isedit=1;

   // =======================================================================================
   /**
   * Обработка распределения вопросов по темам
   * тема => кол-во вопросов из темы
   */

   // Если выбран способ задания количества вопросов по темам
   if ($form[genq] == 1) {

       while(list($k,$v) = each($form[questions]))
       if (!$v) $form[questions][$k] = 0;

       $tmpl = "SELECT * FROM testquestions WHERE tid=$tid AND cid=$cid";
       $tres = sql($tmpl);

       if (sqlrows($tres) == 1)
       $tmpl = "UPDATE testquestions SET questions=".$adodb->Quote(serialize($form[questions]))
               ." WHERE tid=$tid AND cid=$cid";
       else
       $tmpl = "INSERT INTO testquestions (tid,cid,questions)
                VALUES ($tid,$cid,".$adodb->Quote(serialize($form[questions])).")";

       sqlfree($tres);

       $resultat = sql($tmpl);

   } else {

       $tmpl = "DELETE FROM testquestions WHERE tid=$tid AND cid=$cid";
       $resultat = sql($tmpl);

   }
   sqlfree($resultat);
   }
   // =======================================================================================

   // <Проверка формулы>
   $bool = true;
   $test_vars_1 = array();
   $test_vars_2 = array();

   if(eregi("\;", $form[random_vars]))
   {
   		$test_vars_1 = explode(";", $form[random_vars]);

		foreach ($test_vars_1 as $key => $vars_named_1)
   		{
   			$test_vars_1[$key] = trim($test_vars_1[$key]);
   			if($vars_named_1!="")
   			{
   				ereg(":([A-Z]+)", $test_vars_1[$key], $vars_named_2);
   				//$vars_named_2[1]{0} = " ";
   				$test_vars_1[$key] = trim($vars_named_2[1]);
				$test_vars_2[$key] = $test_vars_1[$key];
				echo $test_vars_1[$key].$test_vars_2[$key]."<br>";
				unset($var_named_2);
   			}
   		}

   		for ($i=0; $i<count($test_vars_1); $i++)
   			for ($j=$i+1; $j<count($test_vars_2); $j++)
   	 			if($test_vars_1[$i]==$test_vars_2[$j])
   	  				$bool = false;
   }


   $random_values = ereg_replace(":([A-Z]+)", "", $form[random_vars]);

   if(eregi("\;", $random_values))
   {
   		$vars_array = explode(";", $random_values);
   }
   else
   {
   		$vars_array[0] = $random_values;
   }

   foreach ($vars_array as $value)
   {
   		$value = trim($value);
   		if($value!="")
   		{
   			// "abc","def", -1, -1.2
   			if(eregi("\,", $value))
   			{
   				$string_array = explode(",", $value);
   				foreach ($string_array as $string_value)
   				{
   					$string_value = trim($string_value);
	   				if((!eregi("^\"", $string_value) || !eregi("\"$", $string_value)) && !eregi("^\-?[0-9]*\.?[0-9]*$", $string_value))
   						$bool = false;
   				}
   			}
   			// -1.2--1
			else
			{
	   			if(!eregi("^(\-?[0-9]*\.?[0-9]*)-(\-?[0-9]*\.?[0-9]*)$", $value))
   					$bool = false;
			}
   		}
   }



   if(!$bool)
   		$form[random_vars] = _("Неправильная формула");

   	echo $form[random_vars];
   // </Проверка формулы>

   $form[random]=abs(intval($form[random]))%2;
   $form[qty]=abs(intval($form[qty]))%10000;
   $form[lim]=abs(intval($form[lim]))%10000;
   $form[status]=abs(intval($form[status]))%2;
   $form[questres]=abs(intval($form[questres]))%2;
   $form[endres]=abs(intval($form[endres]))%2;
   $form[showurl]=abs(intval($form[showurl]))%2;
   $form[showotvet]=abs(intval($form[showotvet]))%2;
   $form[timelimit]=abs(intval($form[timelimit]))%(21*24*60+1); // не более 21-го дня
   $form[startlimit]=abs(intval($form[startlimit]));
   $form[skip]=abs(intval($form[skip]))%2;
   $form[limitclean]=abs(intval($form[limitclean]));

   global $adodb;
   if ($tid) {
   $rq="UPDATE test SET
          cid=           ".$adodb->Quote($cid).",
      title=         ".$adodb->Quote($form[title]).",
      random=        ".$adodb->Quote($form[random]).",
      lim=           ".$adodb->Quote($form[lim]).",
      qty=           ".$adodb->Quote($form[qty]).",
      status=        ".$adodb->Quote($form[status]).",
      questres=      ".$adodb->Quote($form[questres]).",
      endres=        ".$adodb->Quote($form[endres]).",
      showurl=       ".$adodb->Quote($form[showurl]).",
      showotvet=     ".$adodb->Quote($form[showotvet]).",
      timelimit=     ".$adodb->Quote($form[timelimit]).",
      startlimit=    ".$adodb->Quote($form[startlimit]).",
      skip=          ".$adodb->Quote($form[skip]).",
      limitclean=    ".$adodb->Quote($form[limitclean]).",
   	  random_vars=   ".$adodb->Quote($form[random_vars]).",
       	  allow_view_log=   ".$adodb->Quote((int)$form[allow_view_log]).",
   	  comments=      ".$adodb->Quote($form['comments']).",
   	  mode=          '".(int) $form['mode']."',
      last=".time()."
          WHERE tid=$tid
       ";
       $message = "Настройки успешно сохранены";
   }else {
       $rq = "INSERT INTO `test` (
                  cid,
                  cidowner,
                  datatype,
                  data,
                  sort,
                  free,
                  rating,
                  title,
                  random,
                  lim,
                  qty,
                  status,
                  questres,
                  endres,
                  showurl,
                  showotvet,
                  timelimit,
                  startlimit,
                  skip,
                  limitclean,
               	  random_vars,
               	  allow_view_log,
               	  comments,
               	  mode,
                  last,
                  created_by
                  )
              VALUES (
                  ".$adodb->Quote($cid).",
                  ".$adodb->Quote($cid).",
                  1,
                  '',
                  0,
                  0,
                  0,
                  ".$adodb->Quote($form[title]).",
                  ".$adodb->Quote($form[random]).",
                  ".$adodb->Quote($form[lim]).",
                  ".$adodb->Quote($form[qty]).",
                  ".$adodb->Quote($form[status]).",
                  ".$adodb->Quote($form[questres]).",
                  ".$adodb->Quote($form[endres]).",
                  ".$adodb->Quote($form[showurl]).",
                  ".$adodb->Quote($form[showotvet]).",
                  ".$adodb->Quote($form[timelimit]).",
                  ".$adodb->Quote($form[startlimit]).",
                  ".$adodb->Quote($form[skip]).",
                  ".$adodb->Quote($form[limitclean]).",
               	  ".$adodb->Quote($form[random_vars]).",
               	  ".$adodb->Quote((int) $form[allow_view_log]).",
               	  ".$adodb->Quote($form['comments']).",
               	  ".(int) $form['mode'].",
                  ".time().",
                  '".(int) $_SESSION['s']['mid']."'
                  )
   ";
       $message = "Задание добавлено";
   }

   $res=sql($rq,"errTT166");
   $tid = $tid ? $tid : sqllast();
   sqlfree($res);
   
   //Добавляем
   
   return 'Ok';
   //
   
//   pr($rq);
//   break;
   if (count($err)) exit(refresh("$PHP_SELF?c=edit&cid=$cid&tid=$tid&err=".ue(implode("~",$err))."$sess"));
   $GLOBALS['controller']->setView('DocumentBlank');
   $GLOBALS['controller']->setMessage(_($message), '', "$PHP_SELF?cid=$cid");
   $GLOBALS['controller']->terminate();
   break;

case "del":

   intvals("tid cid");
/*   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав редактировать данный тест"));
       $GLOBALS['controller']->setMessage(_('нет прав редактировать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }
   if (!get_test_perm_edit($tid)) {
       exitmsg(_("У вас не хватает привилегий"),"$PHP_SELF?$sess");
   }*/

   if (is_course_locked($cid)) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_("Курс заблокирован. Данная операция невозможна"),JS_GO_URL,"$PHP_SELD?$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }

   $res=sql("DELETE FROM test WHERE tid=$tid AND cid=$cid","errTT201");
   sqlfree($res);

   /**
   * Удаление распределения вопросов по темам если есть
   */
   $res=sql("DELETE FROM testquestions WHERE tid=$tid AND cid=$cid");
   sqlfree($res);

//   refresh($PHP_SELF);
   exitmsg(_("Задание удалено"),"$PHP_SELF?cid=$cid");

   break;

//case "new":
//
//   echo show_tb();
//   echo "<h3>Создание нового теста</h3>";
//   $GLOBALS['controller']->captureFromOb(CONTENT);
//   $GLOBALS['controller']->setHeader("Создание нового задания");
//   echo "
//   <form action=$PHP_SELF method=post name=m>$sessf
//   <input type=hidden name=c value=\"new_submit\">
//   Название нового теста:<br>
//   <input type=text name=name value=\"\" style='width:100%'><br><br>
//   На каком из ваших курсов создать новый тест:<br>
//   <select name=cid size=1>";
//
//   $res=sql("SELECT * FROM Courses WHERE cid IN (".implode(",",$s[tkurs]).") ORDER BY Title","errTT243");
//   while ($r=sqlget($res)) echo "<option value=$r[CID]>$r[Title] &nbsp;($r[CID])";
//
//   echo "</select><P>
//   <input type=submit value='Создать новый тест'
//   onClick=\"if (document.m.name.value=='') {alert('Имя теста не указано!'); return false;}\"></form>";
//   $GLOBALS['controller']->captureStop(CONTENT);
//   echo show_tb();
//   break;

case "new_submit":
   intvals("cid");
   if (!isset($s[tkurs][$cid])) exit(_("Укажите учебный курс"));
   if (!$GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OWN)
       && !$GLOBALS['controller']->checkPermission(TESTS_PERM_EDIT_OTHERS)) {
       exitmsg(_("У вас не хватает привилегий"),"$PHP_SELF?$sess");
   }
   if (is_course_locked($cid)) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_("Курс заблокирован. Данная операция невозможна"),JS_GO_URL,"$PHP_SELF?$sess");
       $GLOBALS['controller']->terminate();
       exit();
   }
   global $adodb;
   $rq="INSERT INTO test (cid, cidowner, title, datatype, data, random, lim, qty, sort, free, skip, rating, status, last, created_by)
      values (
      $cid,
      $cid,
      ".$adodb->Quote($name).",
      1,
      '',
      1,
      0,
      1,
      0,
      0,
      0,
      0,
      0,
      ".time().",
      '".(int) $_SESSION['s']['mid']."')
   ";
   $res=sql($rq,"errTT229");
   sqlfree($res);

   $tid=sqllast();
   refresh($PHP_SELF."?".$sess);
   exit();
//   exitmsg("Создан новый тест с ID $tid!","$PHP_SELF?c=edit&cid=$cid&tid=$tid$sess");



case "move":

   echo show_tb();
   echo "<h3>"._("Перенос теста")." ID=$tid</h3>";
   $GLOBALS['controller']->captureFromOb(CONTENT);
   echo "
   <form action=$PHP_SELF method=post name=m>$sessf
   <input type=hidden name=c value=\"move_submit\">
   <input type=hidden name=cid value=\"$cid\">
   <input type=hidden name=tid value=\"$tid\">
   "._("На какой из ваших курсов перенести этот тест:")."<br>
   <select name=newcid size=1>";

   $res=sql("SELECT * FROM Courses WHERE cid IN (".implode(",",$s[tkurs]).") ORDER BY Title","errTT243");
   while ($r=sqlget($res)) echo "<option value=$r[CID]>$r[Title] &nbsp;($r[CID])";

   echo "</select><P><input type=submit value='"._("Перенести!")."'></form>
   <font color=red><b>"._("Внимание!")."</b></font>
   "._("После переноса теста на другой курс нельзя будет
   редактировать список вопросов теста. Однако, тест сохранит свои
   свойства и можно будет изменять все свойства теста, кроме
   добавления/удаления вопросов. Тест можно будет так же удалить
   или перенести назад на этот курс. Если в дальнейшем вы вернете тест
   на этот курс, в нем опять все будет доступно для редактирования.
   После переноса на другой курс, любой преподаватель того курса сможет
   удалить тест или изменить его свойства (но не сможет редактировать список
   вопросов).");
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   break;


case "move_submit":

   intvals("cid tid newcid");
   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав редактировать данный тест"));
       $GLOBALS['controller']->setMessage(_('нет прав редактировать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }
   if (!isset($s[tkurs][$newcid])) {
       //exit("HackDetect: "._("нет прав перенести этот тест на чужой курс"));
       $GLOBALS['controller']->setMessage(_('нет прав перенести этот тест на чужой курс'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }

   $res=sql("UPDATE test SET cid=$newcid, last=".time()." WHERE tid=$tid AND cid=$cid");
   refresh("$PHP_SELF?$sess");
   exit;



case "copy":

   intvals("tid cid");
   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав просматривать данный тест"));
       $GLOBALS['controller']->setMessage(_('нет прав просматривать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }
   $res=sql("SELECT * FROM test WHERE tid=$tid AND cid=$cid","errTT347");
   if (!sqlrows($res)) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
   $r=sqlget($res);
   sqlfree($res);

   unset($r[tid]);
   $r[last]=time();
   $r[title]=substr($r[title],0,235)." ("._("копия от")." ".date("d/m/Y H:i:s",time()).")";

   $rq="INSERT INTO test (";
   foreach ($r as $k=>$v)  {
      $rq.="`$k`,";
      $rq=substr($rq,0,-1);
   }
   $rq.=" ) values (";
   foreach ($r as $k=>$v)  {
      $rq.="'".addslashes($v)."',";
      $rq=substr($rq,0,-1);
   }
   $rq.= ")";

   $rq=substr($rq,0,-1);
   $res=sql($rq,"errTT352");
   sqlfree($res);

   $tid=sqllast();
   exitmsg(_("Новый тест с")." ID $tid "._("скопирован! В имя нового теста автоматически дописано слово 'Копия', но вы можете изменить это имя."),
      "$PHP_SELF?c=edit&cid=$cid&tid=$tid$sess");
   exit;


case "showlist":

   intvals("tid cid");
   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав просматривать данный тест"));
       $GLOBALS['controller']->setMessage(_('нет прав просматривать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }

   echo show_tb();
   echo "<h3>"._("Список вопросов, выбранные маской теста")."</h3>";

   $rr=sqlval("SELECT * FROM test WHERE tid=$tid AND cid=$cid");
   if (!$r) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");

   $res=test_getkod($rr);
  exit;


case "setmode":

   intvals("tid cid");
   $s[usermode]=abs(intval($mode))%2;
   location("$PHP_SELF?tid=$tid&cid=$cid&c=edit$sess");
   exit;


case "sortrow":
  switch ($label) {
     case 2:
        $sortrows=explode(" ","kod type last vopros");
        if (!in_array($sortname,$sortrows)) exit("Error row name");
        if ($s[$ss][ttorder]==$sortname) {
           if ($s[$ss][ttdesc]=="") $s[$ss][ttdesc]=1; else $s[$ss][ttdesc]=0;
        }
        $s[$ss][ttorder]=$sortname;
        break;
     default:
        exit("unknown label for sortrow");
  }
  refresh($url);
  exit;


case "delete":

   intvals("cid tid");
   if (!isset($s[tkurs][$cid])) {
       //exit("HackDetect: "._("нет прав редактировать данное задание"));
       $GLOBALS['controller']->setMessage(_('нет прав просматривать данный тест'), JS_GO_URL, 'test_test.php');
       $GLOBALS['controller']->terminate();
       exit();
   }
   if (!get_test_perm_edit($tid)) {
       exitmsg(_("У вас не хватает привилегий"),"$PHP_SELF?cid=$cid&tid=$tid&c=edit");
   }

   if (is_course_locked($cid)) {
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage(_("Курс заблокирован. Данная операция невозможна"),JS_GO_URL,"$PHP_SELF?cid=$cid&tid=$tid&c=edit");
       $GLOBALS['controller']->terminate();
       exit();
   }

   $r=sqlval("SELECT * FROM test WHERE tid=$tid AND cid=$cid","errTT924");
   if (!is_array($r)) exit("errTT925: "._("Такого задания нет"));

   $kodes=array();
   $data=explode($brtag,trim($r[data]));
   foreach ($data as $v) $kodes[$v]=trim($v);

   if (!is_array($che)) exit("errTT926: "._("Не переданы коды для удаления"));
   foreach ($che as $v) {
      $v=trim($v);
      $v=str_replace("*","%",$v);
      $v=str_replace("?","?",$v);
      if ($kodes[$v]===$v) unset($kodes[$v]);
   }
   $data=implode($brtag,$kodes);

   $res=sql("UPDATE test SET data=".$GLOBALS['adodb']->Quote($data)." WHERE cid=$cid AND tid=$tid","errTT937");
   sqlfree($res);

   refresh("$PHP_SELF?cid=$cid&tid=$tid&c=edit");
   exit();

}

?>