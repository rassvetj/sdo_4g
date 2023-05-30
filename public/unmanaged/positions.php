<?php
   require_once('lib/classes/Positions.class.php');
   require_once("1.php");
   require_once('lib/classes/Position.class.php');
   require_once('lib/classes/Roles.class.php'); 
   require_once('lib/classes/CompetenceRole.class.php');   
   require_once('courses.lib.php');
   require_once('schedule.lib.php');
   require_once('competence.lib.php');
   require_once('positions.lib.php');
   require_once('move2.lib.php'); 
   require_once('lib/classes/Chain.class.php');
   require_once('lib/classes/CCourseAdaptor.class.php');
   require_once('lib/classes/ProgressBar.class.php');
   
   require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
   $sajax_javascript = CSajaxWrapper::init(array('search_people_unused')).$js;
    
        
   if (!$dean) login_error();
   
   
   $strHeader = "<a href='positions.php'>"._("Структура организации")."</a>";
   
   if (isset($_POST['doaction'])) {
       $_POST['action'] = $_POST['doaction'];
   }

switch ($c) {

case "":
    
   $orgunit_names = get_own_orgunit_names_by_person($s['mid']);      

   if (is_array($orgunit_names) && count($orgunit_names)) {
       $GLOBALS['controller']->setHeader(_("Структура организации").' ('.join(', ',$orgunit_names).')');
   }
   
   $soidFilter = new CSoidFilter($GLOBALS['SOID_FILTERS']); 

   echo show_tb();
   echo ph($strHeader);
   $GLOBALS['controller']->captureFromOb(CONTENT);

   if (is_array($_POST['che']) && count($_POST['che']) && in_array($_POST['action'],array(4,5))) {
       $sql = "SELECT structure_of_organ.*, People.LastName, People.FirstName, People.Login 
               FROM structure_of_organ
               LEFT JOIN People ON (People.MID=structure_of_organ.mid)
               WHERE structure_of_organ.soid IN ('".join("','",array_keys($_POST['che']))."')";
       $res = sql($sql);
           
       while($row = sqlget($res)) {
           $soids[$row['soid']] = $row;
       }
       switch($_POST['action']) {
           case 4: // анализ компетенций 
               $columnName = _("Соответствие критериям");
               foreach($_POST['che'] as $k=>$v) {
                   if (!$soidFilter->is_filtered($k)) continue;
                   $results[$k] = check_mid_udv_str($k, $soids[$k]['mid']) ? "<img height=11 src='images/icons/ok.gif'/>" : "<img height=11 src='images/icons/cancel.gif'/>";
               }
           break;
           case 5: // анализ обученности
               $columnName = _("Обученность");
               foreach($_POST['che'] as $k=>$v) {
                    if (!$soidFilter->is_filtered($k)) continue;
                    if (!isset($study_ranks[$k])) {
                        $study_ranks[$k] = (int) get_study_rank($k, $soids[$k]['type'], $soids[$k]['mid']);
                    }                   
                    $results[$k] = $study_ranks[$k]."% ";                    
               }               
           break;
       }
       
       $GLOBALS['controller']->setHeader($columnName);
       
       echo "<table width=100% class=main cellspacing=0>";
       echo "<tr><th>Тип</th><th>Название</th><th>Номер отделения</th><th>"._("В должности")."</th><th>Описание</th><th>$columnName</th></tr>";
       foreach($_POST['che'] as $k=>$v) {
           if (!$soidFilter->is_filtered($k)) continue;
           echo "<tr>";
           echo "<td><img border=0 align=absmiddle alt=\"{$GLOBALS['positions_types'][$r['type']]}\" src=\"{$GLOBALS['sitepath']}images/icons/positions_type_".(int) $soids[$k]['type'].".gif\"></td>";
           echo "<td>".htmlspecialchars($soids[$k]['name'],ENT_QUOTES)."</td>";
           echo "<td align=center>{$soids[$k]['soid_external']}</td>";
           echo "<td align=center>".htmlspecialchars($soids[$k]['LastName'].' '.$soids[$k]['LastName'].' '.(($soids[$k]['Login']) ? '('.$soids[$k]['Login'].')' : ''),ENT_QUOTES)."</td>";
           echo "<td>".htmlspecialchars($soids[$k]['info'],ENT_QUOTES)."</td>";
           echo "<td align=center>{$results[$k]}</td>";
           echo "</tr>";
       }
       echo "</table><br>";
       $GLOBALS['controller']->captureStop(CONTENT);
       $GLOBALS['controller']->terminate();
       exit();
   }   
   
   /**
   * POST от Назначение на курсы
   */
   if (isset($_POST['assign_courses']) && ($_POST['assign_courses']=='assign_courses')) {
       
       $GLOBALS['controller']->setView('DocumentPopup');
       
        if (isset($_POST['people']) && is_array($_POST['people']) && count($_POST['people'])) {        
            while(list($k,$m) = each($_POST['people'])) {
                if (isset($_POST['courses']) && is_array($_POST['courses']) && count($_POST['courses'])) {
                    reset($_POST['courses']);
                    while(list($kk,$vv) = each($_POST['courses'])) {
//                        $m = get_mid_by_soid($v);
                        if ($m > 0) {
/*                            $flag = true;
                            
                            $sql = "SELECT * FROM claimants WHERE MID='".(int) $m."' AND CID='".(int) $vv."' AND Teacher='0'";
                            $res = sql($sql);
                            $flag = sqlrows($res);

                            $sql = "SELECT * FROM Students WHERE MID='".(int) $m."' AND CID='".(int) $vv."'";
                            $res = sql($sql);
                            $flag = sqlrows($res);
*/
                                                        
                            if ($_POST['delete']=='delete') {
                                CCourseAdaptor::drop($vv,$m);
/*                                sql("DELETE FROM claimants WHERE MID='".(int) $m."' AND CID='".(int) $vv."'");
                                sql("DELETE FROM Students WHERE MID='".(int) $m."' AND CID='".(int) $vv."'");
*/                            
                            } else {
                                CCourseAdaptor::assign($vv,$m);
                                CChainLog::email($vv,$m);
/*                                if (get_course_typedes($vv)>0) {
                                    if (!$flag) {
                                        sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ('".(int) $m."', '".(int) $vv."', 0)");
                                    }
                                } else {
                                    if (!$flag) {
                                        sql("INSERT INTO Students (MID, CID, cgid) VALUES ('".(int) $m."', '".(int) $vv."', 0)");
                                        clear_shedule((int) $m, (int) $vv);
                                        add_stud_tasks((int) $m, (int) $vv);
                                    
                                    }
                                }                                                                
*/                            }
                            
                        }
                    }
                }
            }
        }
        
        $GLOBALS['controller']->setMessage(_('Данные изменены успешно'), JS_GO_URL, 'javascript:window.close()');
        $GLOBALS['controller']->terminate();
        exit();        
   }
   /**
   * Копирование элемента структуры организации
   */
   if (isset($_POST['copySoid']) && isset($_POST['che']) && is_array($_POST['che']) && count($_POST['che'])) {
       
       foreach($_POST['che'] as $k => $v) {
           
           copyStructure($k);
           
       }
       
       $GLOBALS['controller']->setMessage(_("Объекты структуры организации успешно скопированы"),JS_GO_URL,'positions.php');
       $GLOBALS['controller']->terminate();
       exit();
       
   }
   
   /**
   * Транзакции обучения
   */
   if (is_array($_POST['set_studying']) && count($_POST['set_studying']) 
   && isset($_POST['do_transacts']) && ($_POST['do_transacts'] == 'do_transacts')) {
              
       foreach($_POST['set_studying'] as $v_mid => $v_courses) {

            foreach($v_courses as $v_cid => $agreem) {
                
                if (get_course_typedes($v_cid)>0) {                    
                    sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ({$v_mid}, '{$v_cid}', 0)");                    
                } else {                    
                    sql("INSERT INTO Students (MID, CID, cgid) values ({$v_mid}, '{$v_cid}', 0)");
                    clear_shedule((int) $v_mid, (int) $v_cid);
                    add_stud_tasks((int) $v_mid, (int) $v_cid);
                }
            
            }           
           
       }
       
   }
   
   /**
   * Создание и назначение опроса (само назначение)
   */
   if (isset($_POST['assign_quiz_go']) && ($_POST['assign_quiz_go']=='assign_quiz_go') 
   && is_array($_POST['assigned_mids']) && count($_POST['assigned_mids'])) {
                     
       require_once('lib/classes/Person.class.php');
       require_once('lib/classes/Competence.class.php');
       require_once('lib/classes/Formula.class.php');
       require_once('lib/classes/CCourseAdaptor.class.php');
       require_once('lib/classes/PollCourse.class.php');
       require_once('lib/classes/Poll.class.php');
       require_once('lib/classes/Question.class.php');
       require_once('lib/classes/PollQuestion.class.php');  
       require_once('lib/classes/Task.class.php');
       require_once('lib/classes/CSchedule.class.php');
       require_once('lib/classes/Group.class.php');

       $dateBegin = mktime($_POST['hh1'], $_POST['mm1'], 0, $_POST['MM1'], $_POST['DD1'], $_POST['YY1']);
       $dateEnd   = mktime($_POST['hh2'], $_POST['mm2'], 0, $_POST['MM2'], $_POST['DD2'], $_POST['YY2']);

       // Создание курса опросов если такого не существует
       $aAttributes['Title'] = _('Аттестация');
       $aAttributes['TypeDes'] = -1; // назначаемый без согласования       
       $aAttributes['chain'] = 0;
       $aAttributes['cBegin'] = date('Y-m-d',$dateBegin-24*60*60);
       $aAttributes['cEnd']   = date('Y-m-d',$dateEnd+24*60*60);
       $aAttributes['Status'] = 2;
       $aAttributes['createby'] = $_SESSION['s']['mid'];
       $aAttributes['createdate'] = date('Y-m-d');
       $aAttributes['is_poll'] = 1;
       
       //$pid = (int) $_POST['pid'];
       $pollName = trim(strip_tags($_POST['poll_name']));       
       $event = (int) $_POST['event'];
       $mids = $_POST['assigned_mids'];
       
       // Если назначение опроса группе
       $groupMids = CGroup::getMidsArray($_POST['group']);
       
       $pollCourse = new CPollCourse($aAttributes);
       if (($cid = $pollCourse->create()) 
            && is_array($mids) && count($mids)
            && $event) {
                           
           $mids2course = array(); $soids2poll = array(); $mids2poll = array();           
           
           foreach($mids as $soid => $mids2vote) {
               $soids2poll[$soid] = $soid;
               
               if (is_array($groupMids) && count($groupMids)) {
                   foreach($groupMids as $mid2vote) {
                       if ($mid2vote>0) {
                           $mids2course[$mid2vote] = $mid2vote;
                           $mids2poll[$mid2vote][$soid] = $soid;
                       }
                   }
               }
               
               if (is_array($mids2vote) && count($mids2vote)) {
                   foreach($mids2vote as $mid2vote) {
                       if ($mid2vote>0) {
                           $mids2course[$mid2vote] = $mid2vote;                       
                           $mids2poll[$mid2vote][$soid] = $soid;
                       }
                   }
               }
           }
           
           $msg = _('Ошибка при создании аттестации');
           $createPollError = true;
           // Создание опроса
           if (is_array($mids2poll) && count($mids2poll)) {
               $poll = new CPoll(array('name'=>$pollName, 'begin'=>date("Y-m-d H:i:s",$dateBegin), 'end' =>date("Y-m-d H:i:s",$dateEnd)/*, 'event' => $event, 'formula' => $formula*/));
               if ($pid = $poll->create()) {
                   if (count($kods = $poll->createQuestions($cid, $soids2poll))) {
                       if (count($tests = $poll->createTasks($cid, $mids2poll, $kods, ' ('.$poll->attributes['name'].')'))) {
                           CPollCourse::assignPeople($cid, $mids2course, false); // должно выполняться перед saveDependences
                           if (count($sheids = $poll->createSchedules($cid, $mids2poll, $tests, $dateBegin, $dateEnd, $event, ' ('.$poll->attributes['name'].')'))) {
                               $poll->saveDependences($pid, $mids2course, $kods, $tests, $sheids);
                               $msg = _('Аттестация успешно создана');
                               $createPollError = false;
                           } else {
                               $msg = _('Ошибка при создании занятий аттестации.');
                           }
                       } else {
                           $msg = _('Ошибка при создании заданий аттестации.');
                       }
                   } else {
                       $msg = _('Ошибка при создании вопросов аттестации. Проверьте правильность видов оценки (необходимые критерии, способы оценки)  и назначение видов оценок элементам структуры организации.');
                   }
               }
           }
           
           if ($pid && $createPollError) {
               $poll->deleteResults($pid);
               $poll->delete($pid, true);
           }
            
       }
              
       
       $GLOBALS['controller']->setView('DocumentBlank');
       $GLOBALS['controller']->setMessage($msg,JS_GO_URL,$sitepath.'positions.php');
       $GLOBALS['controller']->terminate();
       exit();                               
       //////////////////////////////////////////////////////////////////////////////////////////////////
       
       $cid = isset($_POST['cid']) ? (int) $_POST['cid'] : 0;
       $tid = isset($_POST["tid_cid_$cid"]) ? (int) $_POST["tid_cid_$cid"] : 0;
       $typeID = (int) $_POST["event_cid_$cid"];

       $date1 = mktime($_POST['hh1'], $_POST['mm1'], 0, $_POST['MM1'], $_POST['DD1'], $_POST['YY1']);
       $date2 = mktime($_POST['hh2'], $_POST['mm2'], 0, $_POST['MM2'], $_POST['DD2'], $_POST['YY2']);

//       pr($_POST['assigned_mids']);
//       die();
       
       if (($cid>0) && ($tid >0) && ($typeID>0)) {
           
           foreach($_POST['assigned_mids'] as $k=>$v) {
               
               $about = get_lastname_and_firstname_by_mid($k);
               
               $s_title = _("Опрос по сотруднику")." {$about['LastName']} {$about['FirstName']}";
               
               $sql = "INSERT INTO schedule (title, begin, end, createID, typeID, vedomost, CID, timetype,CHID) VALUES
                   (".$GLOBALS['adodb']->Quote($s_title).",
                   ".$adodb->DBTimeStamp($date1).",
                   ".$adodb->DBTimeStamp($date2).",
                   '".$s['mid']."',
                   '$typeID',
                   1,
                   '".(int) $cid."',
                   0,
                   0)";
                   
               $res = sql($sql);
               
               $intSheID = sqllast();
               
               if ($intSheID) {
                   
               /**
               * Создание формулы автоматической выставления оценки
               */
               $sql = "SELECT id FROM formula WHERE CID='".(int) $cid."' AND formula = '0-50:1;50-100:1;'";
               $res = sql($sql);
               if (sqlrows($res)) {
                
                    $row = sqlget($res);
                    $formulaID = $row['id'];   
                   
               } else {
                   
                   $sql = "INSERT INTO formula (name,formula,type,CID) VALUES 
                   ('"._("Формула автоформирования оценки за опрос")."', '0-50:1;50-100:1;', '1', '".(int) $cid."')";
                   
                   $res = sql($sql);
                   
                   $formulaID = sqllast();
                   
               }
                   
               foreach($v as $mid2vote) {
                                      
                        $sql = "SELECT * FROM Students WHERE MID='".(int) $mid2vote."' AND CID='".(int) $cid."'";
                        $res = sql($sql);
                        if (!sqlrows($res)) {
                       
                            sql("INSERT INTO Students (MID, CID, cgid) VALUES ('".(int) $mid2vote."','".(int) $cid."',0)");                      
                            if (sqllast()) {
                                mailTostud('tost', (int) $mid2vote, (int) $cid, 'Назначено: '.$s_title, true, $return);                            
                            }
                       
                        }
                        
                        // todo: проверку на существование уже данного опроса у чела
                        
                        $sql = "SELECT * FROM scheduleID WHERE SHEID='".(int) $intSheID."' AND MID='".(int) $mid2vote."'";
                        $res = sql($sql);
                        if (!sqlrows($res)) {
                        
                            $sql = "INSERT INTO scheduleID (SHEID, MID, isgroup, V_STATUS, toolParams) VALUES
                            ('".(int) $intSheID."',
                            '".(int) $mid2vote."',
                            '0',
                            '-1',
                            'tests_testID=".(int) $tid."; formula_id=".(int) $formulaID.";')";
                            sql($sql);

                            /**
                            * Добавление ограничений кол-ва попыток в schedulecount                        
                            */
                            $sql = "INSERT INTO schedulecount (mid,sheid,qty) 
                            VALUES ('".(int) $mid2vote."','".(int) $intSheID."','0')";
                            sql($sql);
                        
                        }
                                                             
               }
               /**
               * Добавление инфы об опросе к пользователю
               */
               $polls = get_polls_by_mid($k);
               
               if (!$polls && !is_array($polls)) {
                   
                   $polls[] = $intSheID.'#'.$tid;
                   
               } else {
                   
                   if (!in_array($intSheID.'#'.$tid,$polls)) $polls[] = $intSheID.'#'.$tid;
                   
               }
               set_polls_by_mid($k,$polls);
               // =======================================
               
               } // if intSheID
               
           }
       
       
       /**
       * Выставления счётчиков testcounts
       */
       //pr($shedsCount);
       /*
       if (isset($shedsCount) && is_array($shedsCount) && count($shedsCount)) {
        
            foreach ($shedsCount as $m=>$tidcount) {
            
                $sql = "SELECT * FROM testcount WHERE mid='".(int) $m."' AND tid='".(int) $tid."'
                       AND cid='".(int) $cid."'";
                $res = sql($sql);
                if (sqlrows($res)) {
                    
                    $row = sqlget($res);
                            
                    $sql = "UPDATE testcount SET qty='".(int) ($row['qty']+1-$tidcount)."' WHERE mid='".(int) $m."' AND tid='".(int) $tid."'
                           AND cid='".(int) $cid."'";
                    sql($sql);
                            
                } else {
                    
                    $sql = "INSERT INTO testcount (mid, tid, cid, qty) 
                            VALUES ('".(int) $m."','".(int) $tid."','".(int) $cid."','".(int) (1-$tidcount)."')";
                    sql($sql);                    
                    
                }
            
            }
           
       }
       */
       // =========================
                      
       }
              
   }

   /**
   * Групповое удаление
   */
   if (is_array($_POST['che']) && count($_POST['che']) && isset($_POST['action']) && ($_POST['action']==6)) {
       foreach($_POST['che'] as $k=>$v) {
           delete_soid($k);
       }
       delete_soid_flush();
   }

   /**
   * Промежуточная страница назначения курсов
   */
   if (isset($_POST['action']) && (($_POST['action']==7) || ($_POST['action']==8))) {
       
       $GLOBALS['controller']->setView('DocumentPopup');
       $GLOBALS['controller']->captureFromOb(CONTENT);

       if (!is_array($_POST['che']) || !count($_POST['che'])) {
           $GLOBALS['controller']->setMessage(_('Ничего не выбрано'), JS_GO_URL, 'javascript:window.close()');
           $GLOBALS['controller']->terminate();
           exit();
       }
       if ($_POST['action']==8) $GLOBALS['controller']->setHeader(_("Удалить с курсов"));
       else $GLOBALS['controller']->setHeader(_("Назначить курсы"));
       $all_courses = get_all_courses_array();
       
       echo "<form action='' method='POST'>";
       if ($_POST['action']==8) echo "<input type=hidden name=delete value=delete>";
       echo "<input type='hidden' name='assign_courses' value='assign_courses'>";
       echo "<table width=100% class=main cellspacing=0>";
       echo "<tr><th><input checked type=\"checkbox\" title=\""._("Отметить всех")."\" onClick=\"selectAll('people_',this.checked);\"> "._("Слушатель")."</th><th><input type=\"checkbox\" title=\""._("Отметить все курсы")."\" onClick=\"selectAll('courses_',this.checked);\" checked>"._("Курсы")."</th></tr>";
       echo "<tr><td valign=top>";
       $i=1;
       
       $names = array();
       while(list($soid, $v) = each($_POST['che'])) {
           $names = array_merge($names, get_people_recursive_down($soid, array(), array()));
       }

       $name_prev = '';
       foreach ($names as $mid => $name) {
           $mid = (integer)str_replace('mid', '', $mid);
          if ($mid > 0) {
               if (substr($name, 0, strrpos($name, '&laquo;')) != substr($name_prev, 0, strrpos($name, '&laquo;'))) {
                   echo "<br>";
                   $name_prev = $name;
               } 
               echo "<input type=\"checkbox\" name=\"people[]\" id=\"people_{$i}\" value=\"{$mid}\" checked>{$name}<br>";
              $i++;
          }          
       }
       echo "</td>";
       echo "<td valign=top>";
       //$all_courses_by_mid = get_courses_array_by_mid($mid);
       if (is_array($all_courses) && count($all_courses)) {
           reset($all_courses);
           $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
           $i=1;
           while(list($kk,$vv) = each($all_courses)) {
               if (!$courseFilter->is_filtered($kk)) continue;
               //if (!isset($all_courses_by_mid[$kk]))
               echo "<input type=\"checkbox\" name=\"courses[]\" id=\"courses_{$i}\" value=\"{$kk}\" checked> $vv<br>";
               $i++;
           }
       }
       echo "</td></tr>";
       
       echo "</table><p>";
       echo okbutton();
       echo "</form>";
       echo "
       <script language=\"JavaScript\" type=\"text/javascript\">
       <!--
           function selectAll(prefix,checked) {
             var elm;
             
             var j=1;
             while(elm=document.getElementById(prefix+j)) {
                 elm.checked=checked;
                 j++;
             }
           }
       //-->
       </script>";
       //$GLOBALS['controller']->terminate();
       //exit();
   }
   // =======================================================================================================   
   /**
   * Промежуточная страница назначения опроса
   */
   elseif (is_array($_POST['che']) && count($_POST['che']) && isset($_POST['action']) && ($_POST['action']==2)) {
       
       require_once('lib/classes/Formula.class.php');
       
       if ($GLOBALS['controller']->enabled)
       $GLOBALS['controller']->setHeader(_("Назначить аттестацию"));
       else
       echo "<b>"._("Назначить аттестацию")."</b>";
       $cont .= "
       <script type=\"text/javascript\">
       <!--
       
       function hideAttributes(value) {
          var disable = true;
          if (value=='0') disable=false;
          document.getElementById('poll_name').disabled = disable; 
          document.getElementById('event').disabled = disable; 
          document.getElementById('formula').disabled = disable; 

          document.getElementById('DD1').disabled = disable; 
          document.getElementById('DD2').disabled = disable; 
          document.getElementById('MM1').disabled = disable; 
          document.getElementById('MM2').disabled = disable; 
          document.getElementById('YY1').disabled = disable; 
          document.getElementById('YY2').disabled = disable; 
          document.getElementById('hh1').disabled = disable; 
          document.getElementById('hh2').disabled = disable; 
          document.getElementById('ii1').disabled = disable; 
          document.getElementById('ii2').disabled = disable; 
       }
       
       function checkForm() {
           var elm;
           
           if (elm = document.getElementById('poll_name')) {
               if ((elm.value == '') && (!elm.disabled)) {
                   alert('"._("Введите название аттестации")."');     
                   return false;
               }
           }
           
           if (elm = document.getElementById('event')) {
               if ((elm.value == 0) && (!elm.disabled)) {
                   alert('"._("Выберите тип занятия")."');     
                   return false;
               }
           }

           /**
           if (elm = document.getElementById('formula')) {
               if ((elm.value == 0) && (!elm.disabled)) {
                   alert('"._("Выберите способ оценки компетенции")."');     
                   return false;
               }
           }
           **/
           return true;
       }
       //-->
       </script>
       <form action='' method='POST' onSubmit=\"return checkForm();\">
       <input type='hidden' name='assign_quiz_go' value='assign_quiz_go'>";
       $cont .= "<table border=0 cellpadding=2 cellspacing=0 border=0>";
/*       $cont .= "<tr><td>"._("Выберите курс:")." </td>";
       $query = "SELECT * FROM Courses";
       $result = sql($query, "err");
       $cont .= "<td><select name='cid' onChange=\"ShowSelect(this.value);\">";
       $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
       while($row = sqlget($result)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            if (!isset($cid)) $cid = $row['CID'];
            $selected = '';
            if ($cid == $row['CID']) $selected = 'selected';
            $cont .=  "<option value='".$row['CID']."' $selected>".$row['Title']."</option>";
            $cidsList[] = $row['CID'];
            $js .= "ArrayCids[".(int) $i++."] = {$row['CID']};";
       }       
       $cont .= "</select></td></tr>";
*/
/*       $cont .= "<tr><td>"._("Выберите опрос:")." </td><td>";
       
       foreach($cidsList as $v) {
        
            $sql = "SELECT tid, title FROM test WHERE cid='".(int) $v."'";
            $res = sql($sql);
            
            if (!isset($show)) $show = "style='display: block;'";
            else $show = "style='display: none;'";
            $cont .= "<div id='tests_cid_".(int) $v."' $show>";
            $cont .= "<select name='tid_cid_".(int) $v."'>";

            $cont .= "<option>---</option>";
            if (sqlrows($res)) {                
                
                while ($row = sqlget($res)) {
                    $cont .= "<option value='{$row['tid']}'> {$row['title']}</option>";
                }
                
            } else {
                
            //    $cont .= "<option>Нет заданий</option>";
                
            }
            $cont .= "</select>";
            $cont .= "</div>";
            
            // Типы занятий
       }
              
       
       $cont .= "</td></tr>";
       
       unset($show);
*/

/*       
       require_once('lib/classes/Poll.class.php');
       $cont .= "<tr><td>"._("Аттестация").": </td><td><select name=\"pid\" id=\"pid\" onChange=\"hideAttributes(this.value);\">
       <option value=\"0\"> "._('Создать новый опрос')."</option>";
       if (is_array($polls = CPolls::get()) && count($polls)) {
           foreach($polls as $poll) {
               if (!$poll->attributes['deleted'])
               $cont .= "<option value=\"{$poll->attributes['id']}\"> {$poll->attributes['name']}</option>";
           }
       }
       $cont .= "</select></td></tr>"; 
*/
       $cont .= "<tr><td>"._("Название аттестации").": </td><td><input type=\"text\" value=\"\" style=\"width: 300px;\" name=\"poll_name\" id=\"poll_name\"></td></tr>"; 
       require_once('lib/classes/EventWeight.class.php');
       $cont .= "<tr><td>"._("Выберите тип занятия:")." </td><td><select name=\"event\" id=\"event\">";
       $cont .= "<option value=\"0\"> "._("Выберите тип занятия")."</option>";
       $events = CEventWeight::get_events();
       if (is_array($events) && count($events)) {
           foreach($events as $event) {
               $tools = explode(',',$event['tools']);
               if (in_array('tests',$tools)) {
                   $cont .= "<option value=\"{$event['id']}\"> {$event['name']}</option>";
               }
           }
       }
       $cont .= "</select></td></tr>";
       /**
       $cont .= "<tr><td>"._("Выберите способ оценки компетенции").": </td><td><select name=\"formula\" id=\"formula\">";
       $cont .= "<option value=\"0\"> "._("Выберите способ оценки компетенции")."</option>";
       $formulas = CFormula::get_as_array(6);
       if (is_array($formulas) && count($formulas)) {
           foreach($formulas as $formula) {
               $cont .= "<option value=\"{$formula->attributes['id']}\"> {$formula->attributes['name']}</option>";
           }
       }
       $cont .= "</select></td></tr>";
       **/
       
/*       foreach($cidsList as $v) {
           $events = CEventWeight::get_as_array($v);
           if (!isset($show)) $show = "style='display: block;'";
           else $show = "style='display: none;'";
           $cont .= "<div id='events_cid_".(int) $v."' $show>";
           $cont .= "<select name='event_cid_".(int) $v."'>";
           $cont .= "<option>---</option>";
           if (is_array($events) && count($events)) {
               foreach($events as $event) {
                   $tools = explode(',',$event['tools']);
                   if (in_array('tests',$tools) && ($event['weight']!=-1)) {
                       $cont .= "<option value=\"{$event['id']}\"> {$event['name']}</option>";
                   }
               }
           }
           $cont .= "</select>";
           $cont .= "</div>";               
       }
*/
       $cont .= "</td></tr>";
       
       echo
       "<script language='JavaScript'>
       <!--
       ArrayCids = new Array();
       $js
       function ShowSelect(cid) {
       
       for(i=0;i<ArrayCids.length;i++) {
       
            if (cid==ArrayCids[i]) {
                document.getElementById('tests_cid_'+ArrayCids[i]).style.display = 'block';
                document.getElementById('events_cid_'+ArrayCids[i]).style.display = 'block';
            }
            else {            
                document.getElementById('tests_cid_'+ArrayCids[i]).style.display= 'none';
                document.getElementById('events_cid_'+ArrayCids[i]).style.display= 'none';
            }
       
       }
       
       }
       
       function SelectAllPeople(mark) {
              
            for (j = 1; j <= document.getElementById('peopleCount').value; j++) {
            
            if (document.getElementById('mid_'+j+'_1_0'))
            document.getElementById('mid_'+j+'_1_0').checked = mark;                
            
            }
       
       }

       function SelectAllHeads(mark) {
              
            for (j = 1; j <= document.getElementById('peopleCount').value; j++) {
            
            if (document.getElementById('mid_'+j+'_2_0'))
            document.getElementById('mid_'+j+'_2_0').checked = mark;                
            
            }
       
       }

       function SelectAllColl(mark) {
              
            for (j = 1; j <= document.getElementById('peopleCount').value; j++) {
            
            var i = 0;
       
            while (document.getElementById('mid_'+j+'_3_'+i)) {
            document.getElementById('mid_'+j+'_3_'+i).checked = mark;
            i++;
            }
            
            }
       
       }

       function SelectAllSub(mark) {
              
            for (j = 1; j <= document.getElementById('peopleCount').value; j++) {
            
            var i = 0;
       
            while (document.getElementById('mid_'+j+'_4_'+i)) {
            document.getElementById('mid_'+j+'_4_'+i).checked = mark;
            i++;
            }
            
            }
       
       }
       
       function SelectAllByPeople(people, mark) {
       
       document.getElementById('mid_'+people+'_0').checked = mark;
       
       if (document.getElementById('mid_'+people+'_1_0'))
       document.getElementById('mid_'+people+'_1_0').checked = mark;

       if (document.getElementById('mid_'+people+'_2_0'))
       document.getElementById('mid_'+people+'_2_0').checked = mark;
       
       var j = 0;
       
       while (document.getElementById('mid_'+people+'_3_'+j)) {
       document.getElementById('mid_'+people+'_3_'+j).checked = mark;
       j++;
       }

       j=0;       
       while (document.getElementById('mid_'+people+'_4_'+j)) {
       document.getElementById('mid_'+people+'_4_'+j).checked = mark;
       j++;
       }
       
       }              
       
       function SelectAll(mark) {

       document.getElementById('select_all_people').checked = mark;
       document.getElementById('select_all_heads').checked = mark;
       document.getElementById('select_all_coll').checked = mark;
       document.getElementById('select_all_sub').checked = mark;
       
       for (j = 1; j <= document.getElementById('peopleCount').value; j++) {
       
       SelectAllByPeople(j,mark);
       
       }
       
       
       }
       
       // -->
       </script>
       ";
       
       echo $cont;
       
       $html = 
       "
                     <tr><td>"._("Начало:")." </td><td><span id=absolute11>
                        <select name=\"DD1\" id=\"DD1\" size=\"1\">[DD1]</select>:
                        <select name=\"MM1\" id=\"MM1\" size=\"1\">[MM1]</select>:
                        <select name=\"YY1\" id=\"YY1\" size=\"1\">[YY1]</select>&nbsp;&nbsp;&nbsp;
                        <span id=time1>
                          <select name=\"hh1\" id=\"hh1\" size=\"1\">[hh1]</select>:
                          <select name=\"mm1\" id=\"ii1\" size=\"1\">[mm1]</select>
                        </span>
                      </span></td></tr><tr><td>
                        "._("Окончание:")." </td><td><span id=absolute22>
                        <select name=\"DD2\" id=\"DD2\" size=\"1\">[DD2]</select>:
                        <select name=\"MM2\" id=\"MM2\" size=\"1\">[MM2]</select>:
                        <select name=\"YY2\" id=\"YY2\" size=\"1\">[YY2]</select>&nbsp;&nbsp;&nbsp;
                        <span id=time2>
                          <select name=\"hh2\" id=\"hh2\" size=\"1\">[hh2]</select>:
                          <select name=\"mm2\" id=\"ii2\" size=\"1\">[mm2]</select>
                        </span>
                        </span></td></tr>
       ";

       $dd=date("d",$day);
       $mm=date("m",$day);
       $yy=date("Y",$day);
       $tmp="";
       for ($i=1; $i<=31; $i++)
           $tmp.="<option".($i==$dd?" selected":"").">$i";
       $html=str_replace("[DD1]",$tmp,$html);
       $html=str_replace("[DD2]",$tmp,$html);
       $tmp="";
       for ($i=1; $i<=12; $i++)
           $tmp.="<option".($i==$mm?" selected":"").">$i";
       $html=str_replace("[MM1]",$tmp,$html);
       $html=str_replace("[MM2]",$tmp,$html);
       $tmp="";
       for ($i=2004; $i<=2030; $i++)
           $tmp.="<option".($i==$yy?" selected":"").">$i";
       $html=str_replace("[YY1]",$tmp,$html);
       $html=str_replace("[YY2]",$tmp,$html);
       $tmp="";
       for ($i=0; $i<=23; $i++)
           $tmp.="<option value={$i}".($i==0?" selected":"").">$i";
       $html=str_replace("[hh1]",$tmp,$html);
       $tmp="";
       for ($i=0; $i<=23; $i++)
           $tmp.="<option value={$i}".($i==23?" selected":"").">$i";
       $html=str_replace("[hh2]",$tmp,$html);
       $tmp="";
       for ($i=0; $i<=59; $i++)
           $tmp.="<option value={$i}".($i==0?" selected":"").">$i";
       $html=str_replace("[mm1]",$tmp,$html);
       $tmp="";
       for ($i=0; $i<=59; $i++)
           $tmp.="<option value={$i}".($i==59?" selected":"").">$i";
       $html=str_replace("[mm2]",$tmp,$html);       
  
       echo $html;

/*       // Назначение опроса группе
       echo "<tr><td>";
       echo _("Назначить аттестацию группе:")."</td><td>";
       echo "<select id=\"group\" name=\"group\">";
       echo "<option value=\"0\"> "._('Нет')."</option>";
       echo selGroups();
       echo "</select>";
       echo "</td></tr>";
*/                     
       
       echo "</table><br>";
       
       echo "<table width=100% class=main cellspacing=0>";
       echo "<tr>
       <th><input checked type='checkbox' onClick=\"SelectAll(checked);\"></th>
       <th><input checked type='checkbox' id='select_all_people' onClick=\"SelectAllPeople(checked);\">"._("Самому работнику")."</th>
       <th><input checked type='checkbox' id='select_all_heads' onClick=\"SelectAllHeads(checked);\">"._("Его начальнику")."</th>
       <th><input checked type='checkbox' id='select_all_coll' onClick=\"SelectAllColl(checked);\">"._("Коллегам")."</th>
       <th><input checked type='checkbox' id='select_all_sub' onClick=\"SelectAllSub(checked);\">"._("Подчиненным")."</th></tr>";
       
       require_once('lib/classes/Position.class.php');
       
       $che = array();
       foreach(CUnitPosition::getSlavesAll(array_keys($_POST['che'])) as $position) {
           if ($position->attributes['mid']>0) {
               $_POST['che'][$position->attributes['soid']] = 'on';
           }
       }
       
       $i = 1;      
       foreach($_POST['che'] as $k => $v) {
           
           $j = 1;      
           
           if ($mid2vote = get_mid_by_soid($k)) {
                          
           $people = get_lastname_and_firstname_by_mid($mid2vote);
           
           $head2vote = get_head_by_soid($k);
           $colleagues2vote = get_colleagues_by_soid($k);
           $subordinates2vote = get_subordinates_by_soid($k);
           
           echo "<tr><td colspan=5>"._("По работнику")." {$people['LastName']} {$people['FirstName']} "._("назначить:")." </td></tr>";
           echo "<tr>";
           echo "<td><input checked id='mid_".(int) $i."_0' type='checkbox' name='assigned_mids[$k][]' value='0' onClick=\"SelectAllByPeople(".(int) $i.",checked)\"></td>";
           echo "<td><input checked id='mid_".(int) $i."_1_0' type='checkbox' name='assigned_mids[$k][]' value='$mid2vote'>".$people['LastName']." ".$people['FirstName']."</td>";
           echo "<td>";
           if ($head2vote && $head2vote['mid'])
           echo "<input checked id='mid_".(int) $i."_2_0' type='checkbox' name='assigned_mids[$k][]' value='{$head2vote['mid']}'>".$head2vote['lastname']." ".$head2vote['firstname'];
           echo "</td>";
           echo "<td>";
           $j=0;
           if ($colleagues2vote)
           foreach($colleagues2vote as $key => $value) {
           echo "<input checked id='mid_".(int) $i."_3_".(int) $j++."' type='checkbox' name='assigned_mids[$k][]' value='".$key."'>".$value['lastname']." ".$value['firstname']."<br />";
           }
           echo "</td>";
           echo "<td>";
           $j=0;
           if ($subordinates2vote)
           foreach($subordinates2vote as $key => $value) {
           echo "<input checked id='mid_".(int) $i."_4_".(int) $j++."' type='checkbox' name='assigned_mids[$k][]' value='".$key."'>".$value['lastname']." ".$value['firstname']."<br />";
           }
           echo "</td></tr>";
           
           $peopleCount[$i] = $j-1;
           
           $i++;
           
           }
                      
       }
       
       echo "
       <tr><td colspan=5>";
       if (is_array($peopleCount)){
       foreach($peopleCount as $k=>$v) echo "<input type='hidden' id='peopleCount_".(int) $k."' name='peopleCount_".(int) $k."' value='".(int) $v."'>";
       }
       echo "<input type='hidden' id='peopleCount' name='peopleCount' value='".(int) --$i."'>";;
       if ($GLOBALS['controller']->enabled) echo okbutton();
       else
       echo "
       <input type=\"image\" name=\"ok\"
       onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
       onmouseout=\"this.src='".$sitepath."images/send.gif';\"
       src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
       echo "
       </td></tr>
       </table>
       </form>
       ";
       
   }   
   /**
   * Промежуточные результаты операции обучение
   */
   elseif (is_array($_POST['che']) && count($_POST['che']) && isset($_POST['action']) && ($_POST['action']==3)) {
       
       echo "<form action=$GLOBALS[PHP_SELF] method=post name=PrepareForm>   
             <input type=hidden name=c value=''>
             <input type=hidden name='do_transacts' value='do_transacts'>
             <table width=100% class=main cellspacing=0>
             <tr><th><input type=\"checkbox\" checked onClick=\"SelectAllSetStudying(checked); getTotalCost();\" name=\"set_studying_all\" id=\"set_studying_all\"></th><th>"._("Обучаемый")."</th><th>"._("Действие")."</th><th>"._("Курс")."</th><th>"._("Стоимость")."</th></tr>";

       $js = "
       <script language=\"JavaScript\">
       ArrayMidSetStudying = new Array();
       ArrayMidStudyingCost = new Array();";
       
       foreach($_POST['che'] as $v_soid => $v) {
           
            $v_mid = get_mid_by_soid($v_soid);
        
            if(!check_mid_udv_str($v_soid, $v_mid)) {
                
                prepare_set_mid_studying($v_soid, $v_mid);
                
            }
           
       }
       
       echo "<tr><td colspan=4 align=right>"._("Итого:")."</td><td><div id='totalCost'></div></td></tr>
             <tr><td colspan=5>";
             if ($GLOBALS['controller']->enabled) echo okbutton();
             else
             echo "
             <input type=\"image\" name=\"ok\"
             onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
             onmouseout=\"this.src='".$sitepath."images/send.gif';\"
             src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
             echo "
             </td></tr>
             </table>
             </form>";
       
       $js .= "
       function SelectAllSetStudying(mark) {
       for (i = 0; i < ArrayMidSetStudying.length; i++) {
         document.getElementById('set_studying_' + ArrayMidSetStudying[i]).checked = mark;
       }
       return true;
       }
       function getTotalCost() {
       var totalCost = 0;
       for(i=0;i<ArrayMidStudyingCost.length; i++) {
             if (document.getElementById('set_studying_'+ArrayMidSetStudying[i]).checked) 
             totalCost += ArrayMidStudyingCost[i];
       }
       document.getElementById('totalCost').innerHTML = totalCost;
       return totalCost;
       }
       getTotalCost();
       </script>
       ";
     
        echo $js;
          
   } else {
   // =========================================================================
   
//   $divs = get_structure();
//   set_structure_levels( $divs );
   
	define('STRUCTURE_OF_ORGAN_PERM_EDIT_ON', $GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT));

	$position_tpl = new Smarty_els();
	$position_tpl->assign('sitepath', $sitepath);	
	echo "<form action=$GLOBALS[PHP_SELF] method=post name=mainFrm id=\"mainFrm\" onSubmit=\"if (elm = document.getElementById('actionlist')) {if (elm.value == '6') {if (confirm('Удалить?')) return true; else return false;}} return true;\" target=\"_blank\">   
          <input type=hidden name=c value=''>";
	echo $position_tpl->fetch('structure.tpl');
	
/*	$model = new StructureModel();
	$model->initialize(true);
	$js_code = array();
	$js_code_cnt = 0;
	
	
   echo "
   <form action=$GLOBALS[PHP_SELF] method=post name=mainFrm id=\"mainFrm\">   
   <input type=hidden name=c value=''>    
    <table width=100% class=main cellspacing=0 id='tablePositions'>
     <tr>
     <th width=20>
     <a style='display:none;' id='pos_0_minus' href='javascript:void(0);' onClick=\"removeTreeElementsByPrefixAll('pos_0',0);\"><img align=absmiddle border=0 src=\"{$sitepath}images/ico_minus.gif\"></a>
     <a id='pos_0_plus' href='javascript:void(0);' onClick=\"putTreeElementsByPrefixAll('pos_0','table-row');\"><img align=absmiddle border=0 src=\"{$sitepath}images/ico_plus.gif\"></a>
     </th>";
    if (STRUCTURE_OF_ORGAN_PERM_EDIT_ON)
    echo "<th width=20><input type=checkbox onclick=\"SelectAll(checked);\"></th>";
    if (($_POST['action'] == 4) || ($_POST['action'] == 5))
    echo "<th title='"._("Результаты анализа")."'>Рез.</th>";
    echo "<th title='"._("Тип")."'>"._("Тип")."</th>";
    echo "<th>"._("Название")."</th><th>"._("Номер отделения")."</th><th>"._("В должности")."</th><th>"._("Описание")."</th>";
    if (STRUCTURE_OF_ORGAN_PERM_EDIT_ON) {
        echo "<th width='100px' align='center' colspan=2></th>";
    }
    echo "</tr>
    ";
    $model->display_structure_existing();*/
//       echo show_sublevel( $divs, 0 );
/*   echo "</table>";
*/
       
/*  $js = "
<script language=\"JavaScript\">
ArrayMid = new Array();";
*///  if(count($divs)) {
//      $i = 0;
//      foreach ($divs as $value) {
//          if($value['mid'] || ($value['type']==2)) {
//              $js.= "ArrayMid[{$i}] = {$value['soid']};";
//              $i++;
//          }
//      }
//  }
/*  $js .= implode(" ", $js_code);  
  $js .= "  
function SelectAll(mark) {
  
  for (i = 0; i < ArrayMid.length; i++) {
         elm = document.getElementById('che_' + ArrayMid[i]);
         if (elm)
         elm.checked = mark;
     }
  return true;
}
</script>
   ";
     
   echo $js;
*/      
   if ($_POST['action'] == 4) $selected_4 = 'selected';
   if ($_POST['action'] == 5) $selected_5 = 'selected';
   
   if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT)) {
   echo "
   <script type=\"text/javascript\" language=\"JavaScript\">
   <!--
   function changeFormAction(value) {
       if (typeof(value)=='undefined') {
           var value=0;
       }
       if (elm = document.getElementById('mainFrm')) {
           elm.action = '';
           switch(value) {
               case '9': // удалить результаты опросов
                   elm.action='clean_vote_result.php';
               break; 
           }
       }
       return true;
   }
   //-->
   </script>
   ";
   echo "  
   <table width=100% border=0 cellspacing=1 cellpadding=3 class=addnew>
   <tr>
   <td>
   <!--input type=\"submit\" name=\"copySoid\" value=\""._("Копировать")."\" onClick=\"submit();\"-->
   </td>
   <td width=100% align=right valign=middle>
   "._("Выполнить:")."&nbsp; 
   <select name=doaction class=lineinput onChange=\"changeFormAction(this.value);\">
   <option value=0>---</option>
   <option value=2>"._("назначить аттестации")."</option>
   <option value=9>"._("удалить результаты аттестаций")."</option>
   <!--option value=3>"._("назначить необходимые курсы")."</option-->
   <option value=7>"._("назначить курсы")."</option>
   <option value=8>"._("удалить с курсов")."</option>
   <!--option value=4 $selected_4>"._("анализ компетенции")."</option>
   <option value=5 $selected_5>"._("анализ обученности")."</option-->
   </select>
   </td>
   <td>";
//   if ($GLOBALS['controller']->enabled) {
//       echo "</td></tr><tr><td colspan=3>";
//       echo okbutton();
//   }
//   else
   
//   echo "<input type=\"image\" name=\"ok\" onmouseover=\"this.src='".$sitepath."images/send_.gif';\" onmouseout=\"this.src='".$sitepath."images/send.gif';\" src=\"".$sitepath."images/send.gif\"  alt=\"ok\" border=\"0\">";
   echo "
   </td></tr>
   <tr><td colspan=3>".okbutton()."</td></tr>
   </table>
   </form>
   "; 
             
   echo "
   <form action=$GLOBALS[PHP_SELF] method=post onSubmit=\"";
   $head_soid = is_head_orgunit_exists();
   if ($head_soid) {
       echo "if (document.getElementById('owner_soid').value==0) {if (confirm('"._("Корневой элемент уже присутствует. Создать в корневом элементе?")."')) document.getElementById('owner_soid').value={$head_soid}; else return false;}";
   }
   echo "\">
   <input type=hidden name=c value='new'>
   <table width=100% class=main cellspacing=0>
      <tr>
         <th colspan=2>"._("Добавить")."</th>
      </tr>
      <tr>
         <td>"._("Название")." </td>
         <td>
         <input type=text name=name size=40 value=\""._("введите название")."\">
         </td>
      </tr>
      <tr>
         <td>"._("Тип")." </td>
         <td>
         ";
   echo "<input checked type=\"radio\" name=\"type\" value=\"0\"> <img border=0 align=absmiddle alt=\""._("должность")."\" src=\"{$GLOBALS['sitepath']}images/icons/positions_type_0.gif\"> "._("должность")." &nbsp;";
   echo "<input type=\"radio\" name=\"type\" value=\"1\"> <img border=0 align=absmiddle alt=\""._("рук. должность")."\" src=\"{$GLOBALS['sitepath']}images/icons/positions_type_1.gif\"> "._("рук. должность")." &nbsp;";
   if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_ADD_ORGUNIT))
   echo "<input type=\"radio\" name=\"type\" value=\"2\"> <img border=0 align=absmiddle alt=\""._("оргединица")."\" src=\"{$GLOBALS['sitepath']}images/icons/positions_type_2.gif\"> "._("оргединица");
/*
   <select name=\"type\" onChange=\"if (this.value==1) document.getElementById('agreem').disabled = false; else document.getElementById('agreem').disabled = true;\">";
   while(list($k,$v) = each($GLOBALS['positions_types'])) echo "<option value=\"{$k}\"> {$v}</option>";
   echo "</select>
*/
   echo "
         </td>
      </tr>
      <tr>
         <td>"._("Входит в")." </td>
         <td>".getDivs(0, 0)."</td>   
      </tr>
      <tr>
        <td>"._("Код оргединицы/табельный номер")."</td>
        <td><input type=text name=\"soid_external\" value=\"\"></td>
      </tr>
      <!--
      <tr>
         <td colspan=2><input id=\"agreem\" type=checkbox name=\"agreem\" value=\"1\" disabled> "._("Согласовывает обучение сотрудников")."</td>
      </tr>
      -->
   </table>";

   echo "<br>
   <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
   <tr>
      <td align=\"right\" valign=\"top\">";
      if ($GLOBALS['controller']->enabled) echo okbutton();
      else
      echo "<input type=\"image\" name=\"ok\"
      onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
      onmouseout=\"this.src='".$sitepath."images/send.gif';\"
      src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
      echo "</td>
   </tr>
   </table>
   </form>
";
   }

   } 

   $arrGroups = array();
   $arrDepartments = array();
   $arrSelected = array();
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   break;

case "synchronize":
    $sc = new SynchronizeControllerUpload();
    $sc->initialize();
    $model = &$sc->model;
    $sc->execute();
    exit();
    break;
    
case "new":
   if (!empty($name)) {
       /*
       if (($_POST['type'] == 1) && ($_POST['owner_soid']>0)) {
           $sql = "SELECT soid FROM structure_of_organ WHERE type='1' AND owner_soid='".(int) $_POST['owner_soid']."'";
           $res = sql($sql);
           if (sqlrows($res)) {
               $GLOBALS['controller']->setMessage('У '.get_info_by_soid($_POST['owner_soid'],'name').' уже присутствует руководящая должность',JS_GO_URL,"$PHP_SELF?$sess");
               $GLOBALS['controller']->terminate();
               exit();
           }
           $res=sql("INSERT INTO structure_of_organ (name,type,owner_soid,agreem) 
                     VALUES ('".addslashes($name)."','".(int) $_POST['type']."','".(int) $_POST['owner_soid']."','".(int) $_POST['agreem']."')","errFM185");
       } else {
           if ($_POST['owner_soid']==0) {
               if (is_head_orgunit_exists()) {
                   $GLOBALS['controller']->setMessage('Корневой элемент уже присутствует',JS_GO_URL,"$PHP_SELF?$sess");
                   $GLOBALS['controller']->terminate();
                   exit();
               }
               if ($_POST['type']!=2) {
                   $GLOBALS['controller']->setMessage('Корневым элементом может быть только оргединица',JS_GO_URL,"$PHP_SELF?$sess");
                   $GLOBALS['controller']->terminate();
                   exit();               
               }
           }
           $res=sql("INSERT INTO structure_of_organ (name,type,owner_soid,agreem) 
                     VALUES ('".addslashes($name)."','".(int) $_POST['type']."','".(int) $_POST['owner_soid']."','".(int) $_POST['agreem']."')","errFM185");
       }
       */
       $msg = check_logic_of_structure($_POST['owner_soid'],$_POST['type']);
       if (!empty($msg)) {
            $GLOBALS['controller']->setMessage($msg,JS_GO_URL,"$PHP_SELF?$sess");
            $GLOBALS['controller']->terminate();
            exit();
       }

       if (($_POST['type']==2) && !$GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_ADD_ORGUNIT)) {
           refresh("$PHP_SELF?$sess");
           $GLOBALS['controller']->terminate();
       }

       $res=sql("INSERT INTO structure_of_organ (name,type,owner_soid,agreem,code) 
                 VALUES (".$GLOBALS['adodb']->Quote($name).",'".(int) $_POST['type']."','".(int) $_POST['owner_soid']."','".(int) $_POST['agreem']."', ".(strlen($_POST['code']) ? $GLOBALS['adodb']->Quote($_POST['code']) : 'NULL').")","errFM185");
       if ($id = sqllast()) {

           if (strlen($_POST['soid_external'])) {
           $sql = "UPDATE structure_of_organ SET soid_external = ".$GLOBALS['adodb']->Quote($_POST['soid_external'])."
                    WHERE soid = ".(int) $id;
           sql($sql);
           }

           $sql = "SELECT did FROM departments WHERE mid='".(int) $GLOBALS['s']['mid']."'";
           $res = sql($sql);            
           if (sqlrows($res) && ($row = sqlget($res))) {
                $sql = "INSERT INTO departments_soids (did,soid) VALUES ('".(int) $row['did']."','".(int) $id."')";
                sql($sql);
           }           
       }
   }
   sqlfree($res);
   refresh("$PHP_SELF?$sess");
break;

case "delete":
   intvals("soid");
   delete_soid($soid);
   delete_soid_flush();  
   
/*   if (!empty($soid)) {
        $sql = "SELECT owner_soid FROM structure_of_organ WHERE soid='".(int) $soid."'";
        $res = sql($sql);
        if (sqlrows($res)) {
            $row = sqlget($res);
            $res=sql("DELETE FROM structure_of_organ WHERE soid='$soid'","errFM185");
            sqlfree($res);
            $res=sql("DELETE FROM str_of_organ2competence WHERE soid='$soid'");
            sqlfree($res);
            $res=sql("UPDATE structure_of_organ 
                      SET owner_soid='".(int) $row['owner_soid']."' 
                      WHERE owner_soid='".(int) $soid."'");
            sqlfree($res);
        }
   }*/
   refresh("$PHP_SELF?$sess");
break;

case "delcomp":
    intvals("soid coid");
    if ($soid && $coid) {
        
        $res = sql("DELETE FROM str_of_organ2competence WHERE soid='$soid' AND coid='$coid'");
        sqlfree($res);
        
    }
    refresh("{$sitepath}positions.php?c=edit&soid={$soid}");
break;

case "delrole":
    intvals("soid role");
    if ($soid && $role) {
        $sql = "DELETE FROM structure_of_organ_roles WHERE soid='".(int) $soid."' AND role='".(int) $role."'";
        sql($sql);
    }
    refresh("{$sitepath}positions.php?c=edit&soid=".(int) $soid);
break;

case "edit":
   $GLOBALS['controller']->setView('DocumentPopup');
   intvals("soid");
   echo show_tb();
   echo ph("{$strHeader}: "._("редактирование свойств"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(strip_tags("{$strHeader}: "._("редактирование свойств")));
   $tmp="SELECT soid, name, mid, info, owner_soid, agreem, type, code, soid_external FROM structure_of_organ WHERE soid='$soid'";
   $r=sql( $tmp );
   $res=sqlget( $r );
   sqlfree($r);
   $tmp="<script type=\"text/javascript\" language=\"JavaScript\" src=\"{$sitepath}js/roles.js\"></script>";   
   $tmp.="<form action=$PHP_SELF method=post onSubmit=\"if (document.getElementById('roles')) select_list_select_all('roles');\">
   <input type=hidden name=c value=\"post_edit\">
   <input type=hidden name=soid value='$soid'>";

   $divs=getDivs( $res[ soid ], $res[ owner_soid ] );

   $tmp.="<table width=100% class=main cellspacing=0>
   <tr>
   <td>"._("Название")."</td><td ><input type=text name=name value='".$res[ name ]."' size='40'></td>
   </tr>
   <tr>
       <td>"._("Тип")."</td><td>
       <select name=\"type\" onChange=\"javascript:document.getElementById('tr_code').style.display=(this.value==2)?'':'none'\">";
   if (is_array($positions_types) && count($positions_types)) 
        while(list($k,$v)=each($positions_types)) {
            $tmp .= "<option value=\"{$k}\" ";
            if ($k==$res['type']) $tmp .= "selected";
            $tmp .="> {$v}</option>";
        }
   $str_display = ($res['type'] != 2) ? "style=\"display:none\"" : "";
   $tmp .= "
       </select>
       </td>
   </tr>
   <tr>
   <td>"._("Входит в")."</td><td > $divs </td>
   </tr>
   <!--tr id='tr_code' {$str_display}>
   <td>"._("Номер отделения")."</td><td><input type=text name=\"code\" value=\"{$res['code']}\"></td-->
   </tr>
   <tr>
   <td>"._("Код оргединицы/табельный номер")."</td><td><input type=text name=\"soid_external\" value=\"{$res['soid_external']}\"></td>
   </tr>
   ";
   
   //$checked = $res['agreem'] ? "checked" : "";
   //if ($res['type']==1)
   //$tmp.="<tr><td></td><td><input type=checkbox name=agreem $checked> Согласовывает обучение сотрудников</td></tr>";
   
   $tmp.="<tr>
   <td>"._("Описание")."</td>
   <td ><textarea name='info' cols='60' rows='5'>".$res[ info ]."</textarea></td>
   </tr>";
   $tmp.="</table>";

   
   // Roles
   
   if (!in_array($res['type'],array(2))) {
   
       $roles = CCompetenceRoles::get_as_array_by_soid($soid,true);
       
       if (is_array($all = CCompetenceRoles::get_as_array(true))) {
           foreach($all as $role) {
               if (!isset($roles[$role['id']])) {
                   $allRoles[$role['id']] = $role['name'];
               }
           }
       }
       
       if (is_array($roles)) {
           foreach($roles as $role) {
               $usedRoles[$role['id']] = $role['name'];
           }
       }
    
       $smarty = new Smarty_els();
       $smarty->assign('list1_title', _('Виды оценки'));
       $smarty->assign('list2_title', _('Назначенные оценки'));
       $smarty->assign('list1_name', 'allroles');
       $smarty->assign('list2_name', 'roles');
       $smarty->assign('list1_data', $allRoles);
       $smarty->assign('list2_data', $usedRoles);
       
       $tmp .= "<p><table width=100% class=main cellspacing=0><tr><th>"._('Виды оценки')."</th></tr><tr><td>";   
    
       $tmp .= $smarty->fetch('control_list2list_simple.tpl');
       
       $tmp .= "</td></tr></table>";
   
   }
   
   // Roles
   
/*   $tmp .= "<p><table width=100% class=main cellspacing=0><tr><th>Назначенные роли</th></tr>";
   $roles = CCompetenceRoles::get_as_array_by_soid($soid,true);
   if (is_array($roles) && count($roles))
   foreach($roles as $role) {
       $tmp .= "<tr><td width=99%>{$role['name']}</td>
       <td><a onClick=\"if (confirm('"._("Удалить?")."')) return true; return false;\" href=\"{$sitepath}positions.php?c=delrole&soid=".(int) $soid."&amp;role=".$role['id']."\">".getIcon('delete')."</a></td></tr>";
   }
   $tmp.="</table>";
   
   $tmp .= "<p><table width=100% class=main cellspacing=0><tr><th colspan=2>Все роли</th></tr>";
   $all_roles = CCompetenceRoles::get_as_array(true);
   if (is_array($all_roles) && count($all_roles))
   foreach($all_roles as $role) {
       $tmp .= "<tr><td nowrap><input type='radio' name=\"roles[]\" value=\"{$role['id']}\"";
       if (is_array($roles) && count($roles)) {
           if (in_array($role,$roles)) $tmp .= "checked";
       }
       $tmp .= ">&nbsp;</td>
       <td width=99%>{$role['name']}</td></tr>";
   }
   $tmp.="</table>";   
*/
   
/*   $query = "SELECT coid, percent FROM str_of_organ2competence WHERE soid = $soid";
   $result = sql($query);
   $tmp .="
          <br />
          <table width=100% class=main cellspacing=0>
           <th colspan='3'>
           "._("Требуемые компетенции:")."
           </th>
           ";
   $usedCoids = array();
   if(sqlrows($result)>0) {
      while($row = sqlget($result)) {
            $usedCoids[]=$row['coid'];
            $tmp.= "<tr><td width=99%>"
            .get_competence_name_by_id($row['coid'])
            ."</td><td nowrap><input size=1 disabled type=text name='che[".$row['coid']."]' value='".$row['percent']."'> %</td><td nowrap><a href='{$sitepath}positions.php?c=delcomp&soid={$soid}&coid={$row['coid']}'><img border=0 src='images/icons/delete.gif' /></a></td></tr>";
      }
   }
   $tmp.="</table><p><table width=100% class=main cellspacing=0><tr><th colspan=3>"._("Все компетенции")."</th></tr>";
*/
/*   $tmp.="
            <font class=sym id=tshow>4</font><a href='#' onClick=\"
            if(competenceBlock.style.display == 'none') {
               competenceBlock.style.display = 'block';
               tshow.innerHtml = 6;
            }
            else {
              competenceBlock.style.display = 'none';
              tshow.innerHtml = 4;
            }
            \" style='font-size: 11'>Все компетенции:</a><br />

            <div style='display:none;' id='competenceBlock'>
            <ul>
            ";*/
/*   $query = "SELECT coid, name FROM competence";
   $result = sql($query);
   while($row = sqlget($result)) {
         $sub_query = "SELECT coid, soid, percent FROM str_of_organ2competence WHERE coid=".$row['coid']." AND soid=$soid";
         $sub_result = sql($sub_query);
         if(sqlrows($sub_result)>0) {
            $sel = "checked";
            if ($r = sqlget($sub_result) && $r['percent']) $percent = $r['percent']; else $percent = '75';
         }
         else {
            $sel = "";
            $percent = '75';
         }
         if (!in_array($row['coid'],$usedCoids))
         $tmp.="<tr><td nowrap><input type='checkbox' name='comp[".$row['coid']."]' $sel />&nbsp;</td><td width=99%>".$row['name'].
         "&nbsp;</td><td nowrap><input type='text' name='comprate[".$row['coid']."]' size=1 value='{$percent}'>% </td></tr>";
   }
   $tmp.=" </table>";
*/   
           //</div>";
//   $tmp.=" </ul>
//           </div>";

/*         
   $tmp.="           
           </td>
          </tr>";
   $tmp.="
   <tr>
      <td colspan=2 align=\"right\" valign=\"top\"><br><br>";
*/
      $tmp .= "<br><br>";
      $tmp .= okbutton();
/*      
      $tmp .="</td>
   </tr>";


   $tmp.="</table>
   </form>";
*/
    $tmp .= "</form>";   
   echo $tmp;
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;

case "post_edit":

   $GLOBALS['controller']->setView('DocumentPopup');
   intvals("did");
   $agreem = isset($agreem) ? 1 : 0;
   
   $child_soids = get_array_child_soids($soid);
   $tmp_q = in_array($owner_soid, $child_soids) ? "" : "owner_soid = '".(int) $owner_soid."',";
   if ($owner_soid == $soid) $tmp_q = '';
   
   /**
   * Проверки логики структуры организации
   */
   
   $msg = check_logic_of_structure($_POST['owner_soid'],$_POST['type'],'edit',$soid);
   
   $rq="UPDATE structure_of_organ
               SET name='$name',
               info = '$info',
               type = '".(int) $_POST['type']."',
               code = ".(strlen($_POST['code']) ? $GLOBALS['adodb']->Quote($_POST['code']) : 'NULL').",
               soid_external = ".(strlen($_POST['soid_external']) ? $GLOBALS['adodb']->Quote($_POST['soid_external']) : 'NULL').",
               $tmp_q
               agreem = $agreem
         WHERE soid= $soid";
   if (empty($msg)) $res=sql($rq,"errGR138");
   
   sql("DELETE FROM structure_of_organ_roles WHERE soid='".(int) $soid."'");
   if (isset($_POST['roles']) && count($_POST['roles'])) {
       foreach($_POST['roles'] as $role) {
           sql("INSERT INTO structure_of_organ_roles (soid,role) VALUES ('".(int) $soid."','".(int) $role."')");
       }
   }

   if((isset($comp))&&(!empty($comp))) {
       $query = "DELETE FROM str_of_organ2competence WHERE soid=$soid";
       $result = sql($query);

       foreach($comp as $key => $value) {
           $rate = isset($che[$key]) ? (int) $che[$key] : (int) $comprate[$key];
           $query = "INSERT INTO str_of_organ2competence
                     (coid, soid, percent) VALUES
                     ($key, $soid, '{$rate}')";
//                     ($key, $soid, '{$che[$key]}')";
           $result = sql($query);

       }
   }

   if (empty($msg)) {
       $msg = _('Данные изменены успешно');
       $js_go_url = 'javascript:window.close()';
   } else {
       $js_go_url = $_SERVER['HTTP_REFERER'];
   }
    $GLOBALS['controller']->setMessage($msg,JS_GO_URL,"{$js_go_url}");
        $GLOBALS['controller']->terminate();
        exit();

   sqlfree($res);
   refresh("{$sitepath}positions.php?c=edit&soid=".(int) $soid);
   return;
   
case "oldpolls":

    $mid = $_GET['mid'];
    
    $query = "SELECT LastName, FirstName FROM People WHERE MID='".(int) $mid."'";
    $result = sql($query);
    if (sqlrows($result)) {
        $row = sqlget($result);
        $tmp .= ph(_("Архив результатов опросов для")." {$row['LastName']} {$row['FirstName']}");   
        $GLOBALS['controller']->setHeader(_("Архив результатов опросов для")." {$row['LastName']} {$row['FirstName']}");
    }
    $GLOBALS['controller']->captureFromVar(CONTENT,'tmp',$tmp);

    $tmp .= get_polls($mid);

    echo show_tb();
    echo $tmp;
    $GLOBALS['controller']->captureStop(CONTENT);
    echo show_tb();
    return;

case "assignement":

   $mid = 0; 

   if(isset($_POST['assign_courses'])) {

      $mid = $_POST['mid'];
      if(count($course_for_assign)>0) {
         foreach($course_for_assign as $cid => $value) {

                  $query = "INSERT INTO Students (CID, MID)
                                VALUES
                                ($cid, $mid)";
                  $result = sql($query);
                  if(mysql_errno() != 0) {
                     echo $query."<br>";
                     echo mysql_error();
                  }
         }
      }
      if((isset($course_for_assign_for_abitur))&&(is_array($course_for_assign_for_abitur))&&(!empty($course_for_assign_for_abitur))) {
          foreach($course_for_assign_for_abitur as $cid => $value) {
                  tost($mid, $cid);
          }
      }
   }
   elseif($_POST['action'] == 'check') {
       
      $mid = $_REQUEST['mid']; 

      if($mid == 0) {
         $check_message = "<span style='color: red'>"._("Не выбран претендент")."</span>";
      }
      else {/*
         $query = "SELECT * FROM str_of_organ2competence WHERE soid=$soid";
         $result = sql($query);
         while($row = sqlget($result)) {

               $arr_courses[$row['coid']] = get_not_enough_courses_by_people($mid, $row['coid']);
               if(count($arr_courses['coid']) > 0) {
               }
         }*/
        $check_message = check_mid_udv_str($soid, $_REQUEST['mid']) ? "<img height=11 src='images/icons/ok.gif' />&nbsp;&nbsp;&nbsp;<span style='color: green' align='top'>"._("Соответствует должности")."</span>" : "<img height=11 src='images/icons/cancel.gif' />&nbsp;&nbsp;&nbsp;<span style='color: red' align='top'>"._("Не соответствует должности")."</span>";

      }

      $satisfy = true;
      if(count($arr_courses)>0)
      foreach($arr_courses as $key => $value) {
              if(!empty($value)) {
                  $satisfy = false;
                  $check_message = "<img src='images/icons/attention.gif' />&nbsp;&nbsp;&nbsp;<span style='color: red' align='top'>"._("Рекомендуется обучение по следующим курсам")."</span>";
              }
      }

   }
   elseif($_POST['action'] == 'assign'){
          $mid = $_REQUEST['mid'];
          $query = "UPDATE structure_of_organ SET mid='".$mid."' WHERE soid='".$soid."'";
          $result = sql($query);
          header('Location:positions.php');

   }

   if(isset($_GET['mid']))
      $mid = $_GET['mid'];

   if(isset($_POST['mid']))
      $mid = $_POST['mid'];
   
   $q="SELECT soid,name,mid,info,owner_soid,agreem,type,code FROM structure_of_organ WHERE soid=$soid";
   $r=sql( $q );
   $res=sqlget( $r );
   sqlfree($r);

   /**
   $query1 = "SELECT * FROM structure_of_organ WHERE soid='{$res[owner_soid]}'";
   
   $result1 = sql($query1);

   $row1 = sqlget($result1);
   */
   $row1 = get_boss($soid); 

   echo show_tb();

   echo ph("{$strHeader}: {$res['name']}");
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(strip_tags("{$strHeader}: {$res['name']}"));
   
   if((!isset($mid))||($mid == 0)) {
      $query = "SELECT mid FROM structure_of_organ WHERE soid='{$soid}'";
      $result = sql($query);
      if(sqlrows($result)>0) {
         $row = sqlget($result);
         if($row['mid'] != "") {
            $mid = $row['mid'];
         }
      }
   }


   $query = "SELECT Login, LastName, FirstName FROM People WHERE MID='".$res['mid']."'";
   $result = sql($query);
   $row = sqlget($result);     
   
   $GLOBALS['controller']->captureFromVar('m070101','tmp',$tmp);
   
   $sajax_javascript .= 
    "
    function show_user_select(html) {
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select id=\"mid\" name=\"mid\" style=\"width: 100%\">'+html+'</select>';
    }
        
    function get_user_select(str) {
   
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
            
        x_search_people_unused(str, ".(int) $mid.", show_user_select);
    }           
   ";
   $tmp .= "<script type=\"text/javascript\">
            <!--
            {$sajax_javascript}
            //-->
            </script>";
   $tmp.="<table width=100% class=main cellspacing=0>
   <tr>
   <td>"._("Название")."</td><td >".$res[ name ]."</td><td></td>
   </tr>
   <tr>
   <td>"._("Подчиненность")."</td><td >";
   if (is_array($row1) && count($row1))
//   $tmp.="<a href='{$sitepath}positions.php?c=assignement&soid={$res['owner_soid']}'>$row1[name] >></a>";
   $tmp.="<a href='{$sitepath}positions.php?c=assignement&soid={$row1['soid']}'>$row1[name] >></a>";
   $tmp.="</td><td></td></tr>";
   $tmp.="<tr>
   <td>"._("Описание")."</td>
   <td >".nl2br($res[ info ])."</td><td></td>
   </tr>";
   $tmp.="<tr>
   <td>"._("В должности:")."</td>
   <td><a href=\"javascript:void(0)\" onClick=\"wopen('{$sitepath}userinfo.php?mid={$res['mid']}');\">".((!empty($row['LastName']) || !empty($row['FirstName'])) ? ($row['LastName']." ".$row['FirstName']) : ($row['Login']))."</a></td><td align='center' width='80'><a href='#' onClick=\"javascript:window.open('assign_quiz.php?soid=$soid','quiz','titlebar=no,toolbar=no,width=600,height=600,statusbar=no');\"><!-- "._("опрос")." >>> --></a></td>
   </tr>";
   $search = '';
   if (get_people_count()<ITEMS_TO_ALTERNATE_SELECT) $search = '*';
   $tmp.="
   <tr>
   <td>"._("Назначение")."</td>
   <td valign=top align=right>
   <form action='positions.php' method='POST'>
   <input type='hidden' name='c' value='assignement' />
   <input type='hidden' name='soid' value='$soid' />   
   <table border=0 cellpadding=0 cellspacing=0><tr><td width=50% valign=top>
   <input type=\"button\" value=\""._("Все")."\" style=\"width: 10%\" onClick=\"if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');\">
   <input type=\"text\" id=\"search_people\" value=\"{$search}\" style=\"width: 88%\" onKeyUp=\"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);\"><br>
   <div id=\"people\">
   <SELECT id=\"mid\" name='mid' style=\"width: 100%\">"
   .search_people_unused($search,$mid)."
   </SELECT>
   </div>
   </td>
   <td valign=top>
   &nbsp;
   <select name='action'>
   <option value='assign'>" . _("Назначить") . "
   <option value='check'>" . _("Проверить соответствие") . "
   <option value='view'>" . _("Просмотреть результаты") . "
   </select>&nbsp;
   <input type='submit' value='"._("Выполнить")."' name='submit' />
   </td></tr></table>
   </form>
   </td>
   <td valign=top></td>
   </tr>
   ";
   
   if (!empty($check_message))
   $tmp .= "<tr><td colspan=3>$check_message</td></tr>";
   
//   $query = "SELECT coid, percent FROM str_of_organ2competence WHERE soid = $soid";
//   $result = sql($query);
   $tmp.="</table>";
   $GLOBALS['controller']->captureStop('m070101');
   $GLOBALS['controller']->captureFromVar('m070102','tmp',$tmp);
          $tmp.="<br />
          <table width=100% class=main cellspacing=0>
           <tr>
           <th>"._("Компетенции")."</th>
           <th>"._("Требуемые")." <span style='background: #DDDDDD;'>&nbsp;&nbsp;</span></th>
           <th>"._("Имеющиеся")." <span style='background: #999999;'>&nbsp;&nbsp;</span></th>
           <th></th>
           </tr>
           <tr>";
//   if(sqlrows($result)>0) {
//      $count=1;
//      while($row = sqlget($result)) {      
    $count = 1;
    foreach ($roles = CCompetenceRoles::getCompetencesBySoid($soid) as $role) {
        foreach($role as $comp) {
            if ($comp['threshold'] > 0) {
                $complpercent = get_competence_by_mid($comp['competence'], get_mid_by_soid($soid));
                $compname = get_competence_name_by_id($comp['competence']);
                $competences[] = array('name' => $compname,'need'=>$comp['threshold'],'current'=>$complpercent);
                $tmp.= "<tr><td>".$count.'. '.$compname."</td><td>".$comp['threshold']."%</td><td>".
                $complpercent."%</td><td>".makeProgressBar($complpercent,$comp['threshold'])."</td></tr>";
                $count++;
            }
        }
    }
//      }
//   }
   $tmp.="</table><br>";
   
   /**
   * Radar graph of Competences
   */
   if (is_array($competences) && count($competences)) {
       $tmp .= "<img src=\"{$sitepath}positions_competences_graph.php?competences=".urlencode(serialize($competences))."\" border=0>";
   }
   
   $GLOBALS['controller']->captureStop('m070102');
   //$tmp.="</ul>";
/*   if((!isset($mid))||($mid == 0)) {
      $query = "SELECT mid FROM structure_of_organ WHERE soid=$soid";
      $result = sql($query);
      if(sqlrows($result)>0) {
         $row = sqlget($result);
         if($row['mid'] != "") {
            $mid = $row['mid'];
         }
      }
   }
*/
   /*
   $tmp.="
   <form action=''>
    <input type='hidden' name='c' value='assignement' />
    <input type='hidden' name='soid' value='$soid' />


   <table width=100% class=main cellspacing=0>
    <tr>
     <td>Претендент:</td>
     <td align='center'>

     <SELECT name='mid'>
      <option value=0>- укажите -</option>".peopleSelect4Position("Students", $mid, $soid)."
     </SELECT><br />
     <input type='submit' value='Назначить' name='assign_to_position_go' value='go' />
     <input type='submit' value='Проверить соответствие' name='check_go' value='go' />
     <input type='submit' value='Просмотреть результаты' name='view_result_go' value='go' />
     </td>
    </tr>
    ";
   if (!empty($check_message))
   $tmp .= " 
    <tr><td colspan=2>$check_message</td></tr>";
    
   $tmp .=" 
   </table>
   </form>
   ";
  */ 
//
   $tmp.="  
            <!--<a href='#' onClick=\"
            if(employeesBlock.style.display == 'none') {
               employeesBlock.style.display = 'block';
               tshow2.innerHtml = 6;
            }
            else {
              employeesBlock.style.display = 'none';
              tshow2.innerHtml = 4;
            }
            \" style='font-size: 11px;'>--><span style='font-size: 11px;'>"._("Сотрудники:")."</span><!--</a>--><br />

            <!-- <div style='display:none;' id='employeesBlock'>--><br />";
   $GLOBALS['controller']->captureFromVar('m070103','tmp',$tmp);
            $tmp.="
            <table width=100% class=main cellspacing=0>
            <tr><th>"._("Должность")."</th><th>"._("ФИО")."</th></tr>
            ";           
   $tmp.= print_child_organizations($soid);   
   $tmp.=" </table><br />
           <!--</div>-->";           
   $GLOBALS['controller']->captureStop('m070103');
//           
   
   /**
   * Вывод результатов опросов по сотруднику
   */
   $tmp_polls = get_polls($mid,true);
   if ($GLOBALS['controller']->enabled && $tmp_polls) {
        $GLOBALS['controller']->setLink('m070106',array($mid));
        $GLOBALS['controller']->captureFromReturn('m070104',$tmp_polls);
   }
   $tmp .= $tmp_polls;
   if((isset($satisfy))&&(!$satisfy)) {
      $tmp.="
      <form action='' method='POST'>
      <input type='hidden' name='c' value='assignement' />
      <input type='hidden' name='mid' value='$mid' />
      <input type='hidden' name='soid' value='$soid' />
      <table width=100% class=main cellspacing=0>
       <th>"._("Компетенции")."</th><th>"._("Курсы")."</th>
      ";
      if(count($arr_courses)>0)
      foreach($arr_courses as $coid => $courses) {
           if(count($courses) > 0) {
              $tmp.="<tr>";
              $tmp.="<td valign='top'>".get_competence_name_by_id($coid)."</td><td valign='top'>";
              foreach($courses as $key => $cid) {
                      $course_title = get_title_course_by_id($cid);
                      $tmp.="
                      <input type='checkbox' name='course_for_assign[$cid]' />$course_title<br />
                      ";
              }
              $tmp.="</td></tr>";

           }
      }
      $tmp.="
       <tr>
        <td colspan='2' align='right'>        
         <input type='submit' name='assign_courses' value='"._("Назначить на курсы")."' />         
        </td>
       </tr>
      </table>
      </form>";
   }
   if($_POST['action'] == 'view') {
       $GLOBALS['controller']->captureFromVar('m070105','tmp',$tmp);
       $tmp .= "
       <form action='' method='POST'>
        <input type='hidden' name='c' value='assignement' />
        <input type='hidden' name='mid' value='$mid' />
        <input type='hidden' name='soid' value='$soid' />
       <table width=100% class=main cellspacing=0>";
       $tmp .= " <th>"._("дисциплина")."</th><th>"._("статус")."</th><th>"._("назначить")."</th>";
       $query = "select Courses.CID as CID, Title, cBegin, cEnd from Teachers, Courses where MID=$mid AND Teachers.CID=Courses.CID";
       $result1=sql($query,"regERR532");
       if (sqlrows($result1)>0){
           while ($row=sqlget($result1)) {
                  $tmp .= "<tr class=schedule  >
                  <td CLASS=CMAINBG><a href='teachers/manage_course.php4?CID={$row['CID']}'>{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Преподает")."</td><td></td></tr>";
           }
       }
       $query = "select Title, cBegin, cEnd from Students, Courses where MID=$mid AND Students.CID=Courses.CID";
       $result1=sql($query,"errREG534");
       if (sqlrows($result1)>0)
           while ($row=sqlget($result1))
                  $tmp .= "<tr class=schedule  ><td CLASS=CMAINBG>{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Обучается")."</td><td></td></tr>";
       $query = "select Title, cBegin, cEnd, Courses.CID as cid from $fintable, $coursestable where MID=$mid AND $fintable.CID=Courses.CID";
       $result1=sql($query,"errREG536");
       if (sqlrows($result1)>0)
           while ($row=sqlget($result1))
                  $tmp .= "<tr  class=schedule ><td CLASS=CMAINBG>{$row['Title']}</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG>"._("Закончил")."</td><td><input type='checkbox' name='course_for_assign[".$row['cid']."]'/></td></tr>";
       $query = "select Title, cBegin, cEnd, Courses.CID as cid, Teacher from $claimtable, $coursestable where MID=$mid AND $claimtable.CID=$coursestable.CID";
       $result1=sql($query,"errREG536");
       if (sqlrows($result1)>0)
           while ($row=sqlget($result1)) {
                  $tmp .= "<tr class=schedule><td CLASS=CMAINBG>".$row['Title']."</td><!--td CLASS=CMAINBG>".mydate($row['cBegin'])."</td><td CLASS=CMAINBG>".mydate($row['cEnd'])."</td--><td CLASS=CMAINBG nowrap>"._("Заявлен")." ";
                  $tmp .= ($row['Teacher']==1)? _("преподавателем"):_("учеником");
                  $tmp .= "</td><td><input type='checkbox' name='course_for_assign_for_abitur[".$row['cid']."]' value='on' /></td></tr>";
           }
           $tmp .= "
            <tr>
             <td colspan='3' align='right'><br />
              <!-- <input type='submit' name='assign_courses' value='"._("Назначить на курсы")."' /> -->
             </td>
            </tr>
           </table>
          </form><br><br>";
   $GLOBALS['controller']->captureStop('m070105');
   $GLOBALS['controller']->setCurTab('m070105');
   }
   echo $tmp;
   echo show_tb();
   return;

}

function getDivs( $self_soid, $owner_soid ) {
    $smarty = new Smarty_els();
    
    $defaultValue = 0;
    if ($owner_soid > 0) {
        $defaultValue = getField('structure_of_organ','owner_soid','soid',$owner_soid);
    }
    
    $smarty->assign('list_name','owner_soid');
    $smarty->assign('container_name','container_owner_soid');
    $smarty->assign('list_extra'," style=\"width: 300px;\" ");
    $smarty->assign('list_default_value',(int) $defaultValue);
    $smarty->assign('list_selected', (int) $owner_soid);
    $smarty->assign('url',$GLOBALS['sitepath'].'structure.php');
    return $smarty->fetch('control_treeselect.tpl');
    
 //$tmp="SELECT * FROM structure_of_organ";
 $tmp="SELECT 
           t1.soid as soid, 
           t1.name as name, 
           t1.mid as mid, 
           t1.info as info, 
           t1.owner_soid as owner_soid, 
           t1.agreem as agreem, 
           t1.type as type,
           t1.code as code
       FROM structure_of_organ t1
       WHERE t1.type='2' 
       ORDER BY t1.name";
 $r=sql( $tmp );

// $soidFilter = new CSoidFilter($GLOBALS['SOID_FILTERS']);

 $soids = array();

 $divs="<SELECT name='owner_soid' id=\"owner_soid\">";
 $divs.="<option value=0> - "._("укажите")." -</option>";
  while( $res=sqlget( $r ) ){
      if( $res[ soid ] != $self_soid ){
          if( $res[ soid ] == $owner_soid ) $sel=" selected "; else $sel="";
//     if ($soidFilter->is_filtered($res['soid']))
          if (!isset($soids[$res['soid']])) {
              $divs.="<option value=".$res[ soid ] ." $sel>".$res[ name ].(!empty($res['owner_name']) ? ' ('.$res['owner_name'].')' : '')."</option>";
              $soids[$res['soid']] = $res['soid'];
          }
      }
  }
  $divs.="</SELECT>";// как задать только одного?";
  sqlfree($r);
  return( $divs );
}


function show_structure( $divs ){
if (is_array($divs)) {
  foreach( $divs as $div ){
    $sh="";
    for($i=0;$i<$div[ level ];$i++)
      $sh.="--";

    $tmp.=$sh.$div[ name ]."<BR>";
  }
}
  return( $tmp );
}

function check_mid_udv_str($soid, $mid) {
    $arrCoids = array();
    $arrMarks = array();
    foreach(CCompetenceRoles::getCompetencesBySoid($soid) as $role) {
        if (is_array($role) && count($role)) {
            foreach($role as $comp) {
                if ($comp['threshold'] > 0) {
                    $arrCoids[] = $comp['competence'];
                    $arrMarks[] = $comp['threshold'];
                }
            }
        }
    }
/*    $query = "SELECT * FROM str_of_organ2competence WHERE soid='{$soid}'";
    $result = sql($query);
    while($row = sqlget($result)) {
        $arrCoids[] = $row['coid'];
        $arrMarks[] = $row['percent'];
    }
*/    
    // Если нет компетенций, любой человек соответствует
    if (!count($arrCoids)) {
        return true;
    }
    
    $arrPeople = getPeopleByCompetences( $arrCoids, $arrMarks ); 
        
    foreach ($arrPeople as $value) {
        if($value['people_mid'] == $mid) {
            return true;
        }
    }
    return false;
}

function set_mid_studying($soid, $mid) {
    $arrCoids = array();
    $arrCids = array();
    $arrMidCids = array();
    
    $query = "SELECT * FROM str_of_organ2competence WHERE soid='{$soid}'";
    $result = sql($query);
    while($row = sqlget($result)) {
        $arrCoids[] = $row['coid'];
    }
    
    $coid_str = implode(", ", $arrCoids);
    $query = "SELECT DISTINCT cid FROM comp2course WHERE coid IN ({$coid_str})";
    $result = sql($query);
    while($row = sqlget($result)) {
        $arrCids[] = $row['cid'];
    }   
    
//  $query = "SELECT agreem FROM structure_of_organ WHERE soid='{$soid}'";
//  $result = sql($query);
//  $row = sqlget($result);
//  $agreem = $row['agreem'];
    $agreem = isAgreem($soid);
        
    $query = "SELECT * FROM Students WHERE MID = '{$mid}'";
    $res = sql($query);
    while($row = sqlget($res)) {
        $arrMidCids[] = $row['CID'];
    }   
    
    foreach ($arrCids as $key => $value) {
        if(in_array($value, $arrMidCids)) {
            unset($arrCids[$key]);
        }
    }

    if ($agreem) {
        
        $query = "SELECT * FROM claimants WHERE MID = '{$mid}'";
        $res = sql($query);
        while($row = sqlget($res)) {
            $arrMidCids[] = $row['CID'];
        }   
    
        foreach ($arrCids as $key => $value) {
            if(in_array($value, $arrMidCids)) {
                unset($arrCids[$key]);
            }
        }
    
    }
        
    if(count($arrCids)) {       
        foreach ($arrCids as $cid) {
                if($agreem) {
                    sql("INSERT INTO claimants (MID,CID,Teacher) VALUES ({$mid}, '{$cid}', 0)");
                }
                else {
                    sql("INSERT INTO Students (MID, CID, cgid) values ({$mid}, '{$cid}', 0)");
                }
        }       
    }
}

function prepare_set_mid_studying($soid, $mid) {
    global $js, $i;
    $arrCoids = array();
    $arrCids = array();
    $arrMidCids = array();
    
    $query = "SELECT * FROM str_of_organ2competence WHERE soid='{$soid}'";
    $result = sql($query);
    while($row = sqlget($result)) {
        $arrCoids[] = $row['coid'];
    }
    
    $coid_str = implode(", ", $arrCoids);
    $query = "SELECT DISTINCT cid FROM comp2course WHERE coid IN ({$coid_str})";
    $result = sql($query);
    while($row = sqlget($result)) {
        $arrCids[] = $row['cid'];
    }   
    
//  $query = "SELECT agreem FROM structure_of_organ WHERE soid='{$soid}'";
//  $result = sql($query);
//  $row = sqlget($result);
//  $agreem = $row['agreem'];
    $agreem = isAgreem($soid);
        
    $query = "SELECT * FROM Students WHERE MID = '{$mid}'";
    $res = sql($query);
    while($row = sqlget($res)) {
        $arrMidCids[] = $row['CID'];
    }   
    
    foreach ($arrCids as $key => $value) {
        if(in_array($value, $arrMidCids)) {
            unset($arrCids[$key]);
        }
    }

    //if ($agreem) {
        
        $query = "SELECT * FROM claimants WHERE MID = '{$mid}'";
        $res = sql($query);
        while($row = sqlget($res)) {
            $arrMidCids[] = $row['CID'];
        }
    
        foreach ($arrCids as $key => $value) {
            if(in_array($value, $arrMidCids)) {
                unset($arrCids[$key]);
            }
        }
    
    //}
        
    if(count($arrCids)) {       
        foreach ($arrCids as $cid) {
                if ($cid>0) {
                    
                    $cost = get_course_cost($cid);
                    
                    if(get_course_typedes($cid)>0) {
                        echo "<tr><td><input onClick='getTotalCost();' checked type=\"checkbox\" id=\"set_studying_".(int) $i."\" name=\"set_studying[$mid][$cid]\" value=\"1\"></td><td>".mid2name($mid)."</td><td>"._("зачислить как претендента")."</td><td>".cid2title($cid)."</td><td>".$cost['cost']."</td></tr>";
                    }
                    else {
                        echo "<tr><td><input onClick='getTotalCost();' checked type=\"checkbox\" id=\"set_studying_".(int) $i."\" name=\"set_studying[$mid][$cid]\" value=\"0\"></td><td>".mid2name($mid)."</td><td>"._("зачислить как обучаемого")."</td><td>".cid2title($cid)."</td><td>".$cost['cost']."</td></tr>";
                    }
                    $js .= "ArrayMidSetStudying[".(int) $i."] = ".(int) $i.";";
                    $js .= "ArrayMidStudyingCost[".(int) $i."] = ".(int) $cost['Fee'].";";
                    $i++;
                }
        }       
    }
}

function show_sublevel( $divs, $soid, $sh = "", $level=0 ){
  global $study_ranks; // массив уровня обученности  
    
  if (is_array($divs)) {
          foreach( $divs as $r ) {
            if( $r[ owner_soid ] == $soid ) {
                                
                     // ====================================
                     if ($GLOBALS['soidFilter']->is_filtered($r['soid'])) {
                     
                     if (!isset($GLOBALS['temporary_levels']['level'])) {
                         $GLOBALS['temporary_levels']['level'] = 0;
                         $GLOBALS['temporary_levels']['true_level'] = $level;
                     } else {                         
                                                  
                         if ($level>$GLOBALS['temporary_levels']['true_level'])
                             $GLOBALS['temporary_levels']['level'] += ($level-$GLOBALS['temporary_levels']['true_level']);
                         if ($level<$GLOBALS['temporary_levels']['true_level'])
                             $GLOBALS['temporary_levels']['level'] -= ($GLOBALS['temporary_levels']['true_level']-$level);
                             
                         $GLOBALS['temporary_levels']['true_level'] = $level;                         
                     }
                                          
                     $level = (int) $GLOBALS['temporary_levels']['level'];                     
                                          
                     $GLOBALS['levelCount'][$level];
        
                     if (!isset($GLOBALS['itemIDs'])) {
                         $itemID = "{$level}_1";
                         $GLOBALS['itemCount'][$GLOBALS['itemIDs'][$level-1]] = 1;
                     }
                     else {
                         if (!isset($GLOBALS['itemCount'][$GLOBALS['itemIDs'][$level-1]]))
                         $GLOBALS['itemCount'][$GLOBALS['itemIDs'][$level-1]] = 1;
                         $itemID = $GLOBALS['itemIDs'][$level-1]."_".(int) $GLOBALS['itemCount'][$GLOBALS['itemIDs'][$level-1]];
            
                     }
        
                     $GLOBALS['itemIDs'][$level] = $itemID;
                     $GLOBALS['itemCount'][$GLOBALS['itemIDs'][$level-1]]++;
                     
                     $level = $GLOBALS['temporary_levels']['true_level'];
                     
                     }
                     
                     // ====================================
                
                     if( $soid == 0 ) {
                         $b="<B>";
                         $bb="</b>";
                     }
                     else {
                        $b="";
                         $bb="";
                     }

                    $check_message = "";            
                    if ($r['mid'] && isset($_POST['che'][$r['soid']]) && $_POST['action'] == 4) {
                        $check_message = check_mid_udv_str($r['soid'], $r['mid']) ? "<img height=11 src='images/icons/ok.gif'/>" : "<img height=11 src='images/icons/cancel.gif'/>";
                    }
                    //if ($r['mid'] && isset($_POST['che'][$r['soid']]) && $_POST['action'] == 3) {
                        //if(!check_mid_udv_str($r['soid'], $r['mid'])) {
                                //set_mid_studying($r['soid'], $r['mid']);
                            //}
                    //}  
                    
                    if (($r['mid'] || ($r['type']==2)) && isset($_POST['che'][$r['soid']]) && $_POST['action'] == 5) {
                    /**
                    * Анализ уровня обученности                  
                    */

                        if (!isset($study_ranks[$r['soid']])) {
                            $study_ranks[$r['soid']] = (int) get_study_rank($r['soid'], $r['type'], $r['mid']);
//                            $study_ranks[$r['soid']] = (int) ($rank['total_rank'] / $rank['count']);
                        }
                        $check_message = $study_ranks[$r['soid']]."% ";
                        
                    }
                    
                    if ($GLOBALS['soidFilter']->is_filtered($r['soid'])) {
                    
                    $checked = '';
                    if (isset($_POST['che'][$r['soid']])) $checked = 'checked'; 
                    $checkbox = ($r['mid'] || ($r['type']==2)) ? "<input type=checkbox id='che_".$r['soid']."' $checked name=che[".$r['soid']."]>" : "";
                    //$hidden = "";
                    if ($r['level']>0) {
                        if ($GLOBALS['one_item_done'])
                            $hidden="style='display: none;'";                    
                    }
                    $GLOBALS['one_item_done'] = true;
                    $tmp.="\n<tr id=\"pos_$itemID\" $hidden>\n
                        <td>";
                    if ($r['type']==2)
                    $tmp .="    
                        <a style='display:none;' id='pos_{$itemID}_minus' href='javascript:void(0);' onClick=\"removeTreeElementsByPrefix('pos_{$itemID}');\"><img align=absmiddle border=0 src=\"".$GLOBALS['sitepath']."images/ico_minus.gif\"></a>
                        <a id='pos_{$itemID}_plus' href='javascript:void(0);' onClick=\"putTreeElementsByPrefix('pos_{$itemID}','table-row');\"><img align=absmiddle border=0 src=\"".$GLOBALS['sitepath']."images/ico_plus.gif\"></a>                        
                        ";
                    $tmp .="</td>";
                    
                    if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT))
                    $tmp .= "<td>$checkbox</td>";
                        
                    if (($_POST['action'] == 4) || ($_POST['action'] == 5))    
                    $tmp.="<td align=center>$check_message</td>";
                        
                    $tmp.="<td><img border=0 align=absmiddle alt=\"{$GLOBALS['positions_types'][$r['type']]}\" src=\"{$GLOBALS['sitepath']}images/icons/positions_type_".(int) $r['type'].".gif\"></td>
                           <td> $sh ";
                    $tmp .= "<a href=\"javascript:void(0);\" onClick=\"wopen('{$sitepath}soid_info.php?soid={$r['soid_external']}','soid_{$r['soid_external']}', '450', '350')\" style=\"font-size:11px;\">";                           
                    $tmp .= "$b $r[name] $bb";
                    $tmp .= "</a>";
                    $tmp .= "</td>
                            <td>{$r['code']}</td>
                            <td><a href=\"javascript:void(0);\" onClick=\"wopen('{$sitepath}userinfo.php?mid={$r['mid']}','user_{$r['mid']}', '400', '300')\" style=\"font-size:11px;\">".getpeoplename( $r[mid] )."</a></td>
                            <td>$r[info]</td>";
                    if ($GLOBALS['controller']->checkPermission(STRUCTURE_OF_ORGAN_PERM_EDIT)) {                            
                    $tmp .="<td  align='left'>";
                    if (in_array($r['type'],array(0,1))) {
                    $tmp .= "<a href=$PHP_SELF?c=assignement&soid=$r[soid]$sess style=\"font-size:11px;\"><img src=\"{$GLOBALS['sitepath']}images/icons/people.gif\" alt=\""._("Назначить")."\" border=0 width=15></a></td><td>";
                    } else $tmp.="</td><td>";
                    $tmp .= "
                             <a href='$PHP_SELF?c=edit&soid=$r[soid]$sess'>".getIcon("edit")."</a>&nbsp;&nbsp;
                             <a href=$PHP_SELF?c=delete&soid=$r[soid]$sess
                                    onclick=\"if (!confirm('"._("Удалить?")."')) return false;\" >".getIcon("delete")."</a></td>";
                    }
                    $tmp .="</tr>";
                    }
                    $tmp .= show_sublevel( $divs, $r[soid], $sh."..", $level+1 )."<P/>";
            }
        }
  }
  
  return( $tmp );
}

function get_array_child_soids($current_soid) {
    $return_array = array();
    $query = "SELECT soid FROM structure_of_organ WHERE owner_soid = '{$current_soid}'";
    $res = sql($query);
    while ($row = sqlget($res)) {
        $return_array[] = $row['soid'];
        $tmp_array = get_array_child_soids($row['soid']);
        foreach ($tmp_array as $tmp_value) {
            $return_array[] = $tmp_value;
        }       
    }
    return $return_array;
}

function delete_soid($soid) {
    if ($soid>0) {
        $sql = "SELECT soid,type FROM structure_of_organ WHERE owner_soid='".(int) $soid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if ($row['type']==2) delete_soid($row['soid']);
            //sql("DELETE FROM structure_of_organ WHERE soid='".(int) $row['soid']."'");
            //sql("DELETE FROM str_of_organ2competence WHERE soid='".(int) $row['soid']."'");
            $GLOBALS['soids2delete'][] = $row['soid'];
        }        
        $GLOBALS['soids2delete'][] = $soid;
        
        // Удаление из цепочек согласований
        $sql = "SELECT chain FROM chain_item WHERE `type` = '1' AND item = '".(int) $soid."'";
        $res = sql($sql);
            
        while($row = sqlget($res)) {
            if ($row['chain']) {
                $place = 0;
                sql("DELETE FROM chain_item WHERE `type` = '1' AND item = ".(int) $soid);
                $sql = "SELECT id as id, place FROM chain_item WHERE chain = '".(int) $row['chain']."' ORDER BY place";
                $res2 = sql($sql);
                     
                while($row2 = sqlget($res2)) {
                    sql("UPDATE chain_item SET place = '".$place."' WHERE id = ".(int) $row2['id']);
                    $place++;
                }
            }
        }           
        //sql("DELETE FROM structure_of_organ WHERE soid='".(int) $soid."'");
        //sql("DELETE FROM str_of_organ2competence WHERE soid='".(int) $soid."'");
    }
}

function delete_soid_flush() {
    if (is_array($GLOBALS['soids2delete']) && count($GLOBALS['soids2delete'])) {
        sql("DELETE FROM structure_of_organ WHERE soid IN ('".join("','",$GLOBALS['soids2delete'])."')");
        sql("DELETE FROM str_of_organ2competence WHERE soid IN ('".join("','",$GLOBALS['soids2delete'])."')");
    }    
}

function search_people_unused($search, $current) {
    $html = '';
    $html .= "<option value=0>- "._("укажите")." -</option>";
    if ($current>0) {
        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Login
                FROM People
                WHERE People.MID = '".(int) $current."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            $html .= "<option selected value='".(int) $row['MID']."'> ".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' ('.$row['Login'].')',ENT_QUOTES)."</option>";
            $html .= "<option value=0> ------</option>";
        }
    }
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $where = "AND (People.LastName LIKE '%".addslashes($search)."%'
        OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%') AND People.MID NOT IN ('".(int) $current."')";
        $html .= peopleSelect4Position("Students", $current, $_REQUEST['soid'], '', true, $where);
    }    
    return $html;
}

?>