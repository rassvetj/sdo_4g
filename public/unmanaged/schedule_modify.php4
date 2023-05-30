<?

if (!defined("dima")) exit("?");
if (!$teach) login_error();


if (($c == 'modify')
    && !$GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS)
    && (!$GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN)
    || ($GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN)
    && getField('schedule','createID','SHEID',(int) $sheid)!=$GLOBALS['s']['mid']))
    && $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS_PEOPLE)) {
    // редактировать только слушателей
    $c = 'modify_people';
}

require_once($GLOBALS['wwf'].'/lib/classes/CCourseAdaptor.class.php');

switch ($c) {

    case "modify":
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $GLOBALS['sitepath'] . "schedule.php4";

    // SAJAX BEGIN
    require_once($wwf.'/lib/sajax/Sajax.php');

    sajax_init();
    sajax_export("get_teacher_select", "get_room_select", "edit_schedule_unused_people", "edit_schedule_used_people", 'webinar_getmaterials'); // funcitions exists in func.lib.php4
    sajax_handle_client_request();
    $ajax_javascript = sajax_get_javascript();

    // SAJAX END

    $fn_q = "SELECT * FROM scheduleID WHERE SHEID = $sheid";
    $fn_res = sql($fn_q,"fn_error");

    if(sqlrows($fn_res)==0) {
        $no_student = true;
        $q = "SELECT * FROM schedule WHERE sheid = $sheid";

    }
    else {
        $no_student = false;
        $q = "SELECT
             schedule.`SHEID`, schedule.`title`, schedule.`url`, schedule.`descript`, schedule.`createID`, schedule.`typeID`, schedule.`vedomost`, schedule.`CID`, schedule.`CHID`, schedule.`startday`, schedule.`stopday`, schedule.`timetype`, schedule.`isgroup`, schedule.`cond_sheid`, schedule.`cond_mark`, cond_progress, cond_avgbal, cond_sumbal, cond_operation, schedule.`period`, schedule.`rid`,
             scheduleID.toolParams, scheduleID.MID, schedule.`teacher` as teacher,
             " . $adodb->SQLDate("Y-m-d H:i:s", "begin") . " as begin, " . $adodb->SQLDate("Y-m-d H:i:s", "end") . " as end
           FROM
             schedule
           INNER JOIN
             scheduleID ON (schedule.SHEID = scheduleID.SHEID)
           WHERE
             schedule.sheid='$sheid'";
        //     $q = "SELECT
        //             schedule.*, scheduleID.toolParams, scheduleID.MID,
        //             UNIX_TIMESTAMP(schedule.begin) as `begin`, UNIX_TIMESTAMP(schedule.end) as `end`
        //           FROM
        //             schedule
        //           INNER JOIN
        //             scheduleID ON (schedule.SHEID = scheduleID.SHEID)
        //           WHERE
        //             schedule.sheid='$sheid'";
    }
    $res=sql($q,"err200");
    while ($tmpRow=sqlget($res)) {

        // Относительное задание даты
        $startday = $tmpRow['startday'];
        $stopday = $tmpRow['stopday'];
        $timetype = $tmpRow['timetype'];
        $relative_class = "hidden2";
        if ($timetype == 1) {
            $stopday = $stopday-$startday + 1 - 23*60*60 - 59*60 - 59;
            $startday = (int) ((($startday-1)/24/60/60)+1);
            $stopday = (int) (($stopday/24/60/60)+1);
            $absolute_class="hidden2";
            $relative_class="";
        }

        if (!(($tmpRow['createID']==$GLOBALS['s']['mid']) && $GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OWN))
            && !$GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS)) {
            $GLOBALS['controller']->setView('Document');
            $GLOBALS['controller']->setMessage(_("У вас нет прав на редактирование данного занятия"),JS_GO_BACK);
            $GLOBALS['controller']->terminate();
            exit();
        }

        $rr = $tmpRow;
        $kurs=$rr[CID];

        $typename=sqlvalue("SELECT TypeName FROM EventTools WHERE TypeID='$rr[typeID]'","err202");
        $typetools=sqlvalue("SELECT tools FROM EventTools WHERE TypeID='$rr[typeID]'","err203");
        $strGroupSuffix = ($rr['isgroup']) ? "-group" : "";
        $strNum = ($rr['isgroup']) ? "3" : "4";

        $toolsparam=explode(";",$rr['toolParams']);

        if (count($toolsparam))
        foreach ($toolsparam as $v) {
            if (!strlen(trim($v))) continue;
            $tmp=explode("=",$v);
            $tp[trim($tmp[0])]=trim($tmp[1]);
        }

        $tool=explode(",",$typetools);

        $toolstmp="";
        if (count($tool))
        foreach ($tool as $v) {
            $tools[trim($v)]=$v;
        }

        $main2="";
        //pr($toolsparam);
        $vname="externalURLs";
        if (isset($tools[$vname])) {
            $val=(isset($tp[$partparams[$vname][0]])) ? $tp[$partparams[$vname][0]] : "";
            //echo "$tm-$strNum$vname$strGroupSuffix.html";
            $html2=gf("$tm-$strNum$vname$strGroupSuffix.html");
            $main2.=str_replace(
            array("[toolname]","[toolsval]"),
            array($vname,$val),
            $html2);
        }

        $vname="module";
        if (isset($tools[$vname])) {
            $val=(isset($tp[$partparams[$vname][0]])) ? $tp[$partparams[$vname][0]] : "";
            $html2=gf("$tm-$strNum$vname$strGroupSuffix.html");

//            $mods = CCourseAdaptor::getMeterials($kurs);
            $tmp = '';
            $sql = "SELECT oid FROM organizations WHERE cid = '".(int) $kurs."' LIMIT 1";
            $res = sql($sql);
            if (sqlrows($res)) {
//            if (count($mods)) {

/*                foreach($mods as $mod_id => $mod_title) {
                    $tmp.="<option value=\"{$mod_id}\"";
                    if  ($val == $mod_id) $tmp.=" selected ";
                    $tmp.=">".htmlspecialchars($mod_title, ENT_QUOTES);
                }
*/

                $parent = 0;
                $level = (int) getField('organizations', 'level', 'oid', (int) $val);
                $items = array();
                //$sql = "SELECT oid, prev_ref, level FROM organizations WHERE cid = '".(int) $kurs."' AND level IN ('$level','".($level-1)."')";
                $sql = "SELECT oid, prev_ref, level FROM organizations WHERE cid = '".(int) $kurs."'";
                $res = sql($sql);

                while($row = sqlget($res)) {
                    $items[$row['oid']] = array('oid' => $row['oid'], 'prev_ref' => $row['prev_ref'], 'level' => $row['level']);
                }

                if (isset($items[$val])) {
                    $item = $items[$val];
                    while($item['level'] >= $level) {
                        if (!isset($items[$item['prev_ref']])) break;
                        $item = $items[$item['prev_ref']];
                    }
                }

                if ($item['oid'] != $val) {
                    $parent = $item['oid'];
                }

                $_smarty = new Smarty_els();

                $_smarty->assign('list_name','form[module]');
                $_smarty->assign('container_name','container_module');
                $_smarty->assign('list_extra'," style=\"width: 300px;\" ");
                $_smarty->assign('list_default_value', $parent);
                $_smarty->assign('list_selected', $val);
                $_smarty->assign('url',$GLOBALS['sitepath'].'course_structure_toc_xml.php?cid='.$kurs);
                $tmp .= $_smarty->fetch('control_treeselect.tpl');


            } else {
                $GLOBALS['controller']->setView('Document');
                $GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного учебного модуля"), JS_GO_BACK);
                $GLOBALS['controller']->terminate();
                exit();
            }

/*            $res1=sql("SELECT mod_list.ModID as modid, mod_list.Title as title
                        FROM mod_list
                        WHERE mod_list.CID='$kurs'","err41");
            if (!sqlrows($res1)) {
            	//exit("unknown mod_list");
		       	$GLOBALS['controller']->setView('DocumentBlank');
   	   			$GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного учебного модуля"), JS_GO_URL,'javascript:window.close();');
   	   			$GLOBALS['controller']->terminate();
   	   			exit();
            }
            $tmp="";
            while ($r=sqlget($res1))
            {
                $tmp.="<option value='$r[modid]'";
                if  ($val == $r['modid']) $tmp.=" selected ";
                $tmp.=">$r[title]</option>";
            }
            sqlfree($res1);
*/

            $main2.=str_replace(
		         array("[toolname]","[select]", "[TT_MODULE]"),
		         array($vname,$tmp, $GLOBALS['tooltip']->display('schedule_edit_module')),
            $html2);
        }

       // RUN BEGIN
       $vname="run";
       if (isset($tools[$vname])) {
          $val=(isset($tp[$partparams[$vname][0]])) ? $tp[$partparams[$vname][0]] : "";
          $html2=gf("$tm-$strNum$vname$strGroupSuffix.html");

          $mods = CCourseAdaptor::getRuns($kurs);
          $tmp = '';
          if (count($mods)) {
              foreach($mods as $mod_id => $mod_title) {
                  $tmp.="<option value=\"{$mod_id}\" ";
                  if ($val==$mod_id) $tmp .= " selected ";
                  $tmp.=">".htmlspecialchars($mod_title, ENT_QUOTES);
              }
          } else {
              $GLOBALS['controller']->setView('Document');
              $GLOBALS['controller']->setMessage(_("На данном курсе нет ни одной внешней программы"), JS_GO_URL,'javascript:window.close();');
              $GLOBALS['controller']->terminate();
              exit();
          }
          $main2.=str_replace(
             array("[toolname]","[select]"),
             array($vname,$tmp),
             $html2);
       }
       // RUN END


        $vname="tests";
        if (isset($tools[$vname])) {
            $val=(isset($tp[$partparams[$vname][0]])) ? $tp[$partparams[$vname][0]] : "";
            //echo "$tm-$strNum$vname$strGroupSuffix.html";
            $html2=gf("$tm-$strNum$vname$strGroupSuffix.html");

            $mods = CCourseAdaptor::getTasks($kurs);
            $tmp = '';
            if (count($mods)) {
                foreach($mods as $mod_id => $mod_title) {
                    $tmp.="<option value=\"{$mod_id}\"";
                    if  ($val==$mod_id) $tmp.=" selected ";
                    $tmp.=">".htmlspecialchars($mod_title, ENT_QUOTES);
                }
            } else {
                $GLOBALS['controller']->setView('Document');
                $GLOBALS['controller']->setMessage(_("На данном курсе нет ни одного задания"), JS_GO_URL,'javascript:window.close();');
                $GLOBALS['controller']->terminate();
                exit();
            }

/*
            $sq="SELECT tid , title FROM test WHERE test.cid=$kurs";
            $res1=sql($sq,"err42");
            $tmp="";
            if (!sqlrows($res1)) $tmp="<option value='0'>no tests</option>";
            while ($r=sqlget($res1)) {
                $tmp.="<option value=$r[tid]";
                if  ($val==$r[tid]) $tmp.=" selected ";
                $tmp.=">$r[title]</option>";
            }
            sqlfree($res1);
*/

            $res1=sql("SELECT * FROM formula WHERE (CID='$kurs' OR CID=0) AND type=1","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
            $strArrayFormulas = (isset($_POST['isgroup'])) ? "[]" : "";
            $str="<select onChange=\"var elm = jQuery('#penaltyFormula').get(0); if (elm && (this.value == 0)) elm.disabled = true; else elm.disabled = false;\" name='form[formula_id]{$strArrayFormulas}'><option value=0 selected> --- </option>";     // ПОДКЛЮЧЕНИЕ ОБРАБОТКИ ОЦЕНОК АВТОМАТИЧЕСКИ ФОРМУЛОЙ
            $intFormulaId = (int)$tp['formula_id'];
            while ($r = sqlget($res1)) {                                    //ВЫВОДИМ КАЖДУЮ ФОРМУЛУ В СПИСОК
            $str.="<option value=".$r[id];
            if( $intFormulaId == $r[id])
            $str.=" selected";     // ПОДКЛЮЧЕНИЕ ОБРАБОТКИ ОЦЕНОК АВТОМАТИЧЕСКИ ФОРМУЛОЙ
            $str.=">".$r[name]."</option>";
            }
            $str.="</select>";

            $intGrFormulaId = (int)$tp['formulagr_id'];
            $res2=sql("SELECT * FROM formula WHERE (CID='$kurs' OR CID='0') AND type=3");
            $grstr="<select name='form[formulagr_id]'><option value=0 selected> --- </option>";
            while($r2 = sqlget($res2)) {
                $grstr.="<option value =".$r2[id];
                if($intGrFormulaId == $r2[id]) {
                    $grstr.=" selected";
                }
                $grstr.=">".$r2[name]."</option>";
            }
            $grstr.="</select>";


            $intPenaltyFormulaId = (int)$tp['penaltyFormula_id'];
            $res2=sql("SELECT * FROM formula WHERE (CID='$kurs' OR CID='0') AND type=5");
            $penaltyFormulaSelect="<select ".($intFormulaId ? '' : 'disabled')." id=\"penaltyFormula\" name='form[penaltyFormula_id]'><option value=0 selected> --- </option>";
            while($r2 = sqlget($res2)) {
                $penaltyFormulaSelect.="<option value =".$r2[id];
                if($intPenaltyFormulaId == $r2[id]) {
                    $penaltyFormulaSelect.=" selected";
                }
                $penaltyFormulaSelect.=">".$r2[name]."</option>";
            }
            $penaltyFormulaSelect.="</select>";

            $main2.=str_replace(
            array("[toolname]","[select]","[formula]","[grformula]",'[penaltyFormula]', '[TT_FORMULA_GRADE]', '[TT_FORMULA_GROUPS]', '[TT_FORMULA_PENALTY]'),
            array($vname,$tmp,$str, $grstr,$penaltyFormulaSelect, $GLOBALS['tooltip']->display('schedule_formula_grade'), $GLOBALS['tooltip']->display('schedule_formula_groups'), $GLOBALS['tooltip']->display('schedule_formula_penalty')),
            $html2);
        }

        $vname = "collaborator";

        if(isset($tools[$vname])) {
            $query = "SELECT * FROM scheduleID WHERE SHEID=$sheid";
            $result = sql($query);
            $row = sqlget($result);
            $toolParams = explode(";", $row['toolParams']);
            if(is_array($toolParams)) {
                foreach($toolParams as $key => $value) {
                    if(strpos($value, "collaborator") !== false) {
                        $collaborator = explode("=", $value);
                        $val = $collaborator[1];
                    }
                }
            }
            //$val = (isset($tp[$partparams[$vname][0]))?$tp[$partparams[$vname][0]]:"";
            $html2=gf("$tm-$strNum$vname$strGroupSuffix.html");
            $sq="SELECT MID, LastName, FirstName, Patronymic FROM People ORDER BY LastName";
            $res1=sql($sq,"err42");
            $tmp="";
            while($r=sqlget($res1)) {
                $tmp.="<option value=$r[MID]";
                if($val == $r[MID]) $tmp .= " selected";
                $tmp.=">$r[LastName] $r[FirstName] $r[Patronymic]</option>";
            }
            sqlfree($res1);
            $main2.=str_replace(

            array("[toolname]","[select]"),
            array($vname,$tmp),
            $html2);
        }
        
	    $vname = 'webinar';
	   if (isset($tools[$vname])) {
	       $html = gf("$tm-3$vname$strGroupSuffix.html");
	       
	       $smarty = new Smarty_els();
	       
	       $materials = '';
	       $sql = "SELECT * FROM webinar_plan WHERE pointId = '$sheid' AND bid > 0 ORDER BY title";
	       $res = sql($sql);
	       while($row = sqlget($res)) {
	           $materials .= "<option value=\"{$row['bid']}\"> ".htmlspecialchars($row['title'], ENT_QUOTES)."</option>";
	       }
	       
	       $smarty->assign('list1_options', webinar_getmaterials($kurs, '', $sheid));
	       $smarty->assign('list2_options', $materials);
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
	    
	                   x_webinar_getmaterials('$kurs', str, '$sheid', show_webinar_list_options);
	               }                   
	       ");
	       $str = $smarty->fetch('control_list2list.tpl');       
	       
	       $main2.=str_replace(
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
	            $templateId = $cp->getMeetingTemplate(getField('schedule', 'connectId', 'SHEID', $sheid));
	        } catch (Exception $e) {
	        	$GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage(_("Ошибка изменения занятия Adobe Connect Pro.").' '.htmlspecialchars($e->getMessage()), JS_GO_URL, $GLOBALS['sitepath']);
                $GLOBALS['controller']->terminate();
                exit(); 
	        }
	        $str="<select name=\"template\"><option value=0 selected> "._("Без шаблона")." </option>"; 
	        if (is_array($templates) && count($templates)) {
	            foreach($templates as $template) {
	                $str .= "<option value=\"{$template->sco_id}\" ".(($templateId == $template->sco_id) ? 'selected' : '')."> {$template->name}</option>";
	            }
	        }
	        $str .= "</select>";       
	       
	        $main2.=str_replace(
	            array("[toolname]", "[select]", '[TT_MODULE]'),
	            array($vname, $str, ''), $html);
 	    }


        $vname="redirectURL";
        if (isset($tools[$vname])) {
            $val=(isset($tp[$partparams[$vname][0]])) ? $tp[$partparams[$vname][0]] : "";
            $html2=gf("$tm-$strNum$vname$strGroupSuffix.html");
            $main2.=str_replace(
            array("[toolname]","[rurl]"),
            array($vname,$val),
            $html2);
        }

        if ($rr['isgroup']) {
            $q = "SELECT gid FROM scheduleID WHERE scheduleID.sheid='$sheid' AND scheduleID.gid IS NOT NULL";
            $r = sql($q);
            if ($a = sqlget($r)) {
                $intGid = $a['gid'];
            }
            if ($rr['MID']) {
                $arrTools[$rr['MID']] = $main2;
            }
        } else {
            break;
        }
    }

    $strValidTemplate = ($rr['isgroup']) ? "{$tm}-4main-groups.html" : "{$tm}-4main.html";
    $html=gf($strValidTemplate);
    $thtml=gf("$tm-4tr-tools.html");

    $html=str_replace("[toolslist]",$thtml,$html);
    $html=str_replace("[tools]",$main2,$html);
    $html=str_replace("[typeID]",$rr['typeID'],$html);

    $intValidGroup = (isset($_POST['group'])) ? $_POST['group'] : $intGid;

/*    if (!$rr['isgroup']) {
        $sql="SELECT
                    DISTINCT People.MID, LastName, FirstName, Patronymic, login, EMail
                  FROM People
                    INNER JOIN Students ON Students.MID=People.MID
                  WHERE Students.CID='$rr[CID]'
                  ORDER BY People.LastName
                     ";
        $res=sql($sql,"err205");
        $tmp=gf("$tm-4tr-people.html");
        $tr="";
        $count_people = sqlrows($res);
        while ($r=sqlget($res)) {
            $fn_sql = "SELECT * FROM scheduleID WHERE SHEID = $sheid AND MID = ".$r['MID'];
            $fn_res = sql($fn_sql,"fed bug");
            if(sqlrows($fn_res)==0) $inlist = "";
            else $inlist = " checked";
            $tr.=str_replace(
            array("[lastname]","[patronymic]","[firstname]","[login]","[mail]","[mid]","[checked]"),
            array($r[LastName],$r[Patronymic],$r[FirstName],$r[login],$r[EMail],$r[MID],
            $inlist),
            $tmp);
        }
        $strStudList = "[studlist]";
    }
    else {
        $sql="SELECT DISTINCT
                    People.MID, LastName, FirstName, Patronymic, login, EMail
                  FROM
                    People
                  INNER JOIN groupuser ON (People.`MID` = groupuser.`mid`)
                  INNER JOIN Students ON (People.`MID` = Students.`mid`)
                  WHERE
                    groupuser.gid='{$intValidGroup}'
                  ORDER BY People.LastName

           ";
        $res=sql($sql,"err205");
        $tmp=gf("$tm-3tr-people-group.html");
        $tr="";
        $count_people = sqlrows($res);
        while ($r=sqlget($res)) {
            $srtToolSelect = (!isset($_POST['group'])) ?  $arrTools[$r['MID']] : $main2;
            $tr.=str_replace(
            array("[lastname]","[patronymic]","[firstname]","[login]","[mail]","[mid]","[checked]", "[TASK_SEL]"),
            array($r[LastName],$r[Patronymic],$r[FirstName],$r[login],$r[EMail],$r[MID]," checked",$srtToolSelect), $tmp);
        }
        $strStudList = "[tr-people]";
    }
*/
    // ===============================================================================
    $smarty = new Smarty_els();
    $smarty->assign('list1_options',edit_schedule_unused_people('',$rr['CID'],$intValidGroup,$sheid));
    $smarty->assign('list2_options',edit_schedule_used_people($rr['CID'],$sheid));
    $smarty->assign('list1_name','list1');
    $smarty->assign('list2_name','list2');
//    $smarty->assign('list1_title',_(CObject::toUpperFirst("слушатели")));
//    $smarty->assign('list2_title',_('Занятие назначено'));
    $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
    $smarty->assign('editbox_search_name','editbox_search');
    $smarty->assign('editbox_search_text','');
    $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
    $smarty->assign('list1_container_id','list1_container');
    $smarty->assign('list2_container_id','list2_container');
    $smarty->assign('list3_name','list3');
    $smarty->assign('list3_options',selGrved($rr['CID'],0));
    $smarty->assign('list3_change',"if (elm = document.getElementById('editbox_search')) get_list_options(elm.value);");
    $smarty->assign('list1_list2_click','get_room_select();');
    $smarty->assign('list2_list1_click','get_room_select();');
    $smarty->assign('javascript', "
            function show_list_options(html) {
                var elm = document.getElementById('list1_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list1\" name=\"list1[]\" multiple style=\"width:100%\">'+html+'</select>';
                prepare_options('list1', dropped, assigned);
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

                get_list2_options('');
                x_edit_schedule_unused_people(str, '".(int) $rr['CID']."', gid, '".(int) $sheid."', show_list_options);
            }

            function show_list2_options(html) {
                var elm = document.getElementById('list2_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list2\" name=\"list2[]\" multiple style=\"width: 100%\">'+html+'</select>';
                prepare_options('list2', assigned, dropped);
            }

            function get_list2_options(str) {
                var elm = document.getElementById('list2_container');
                if (elm) elm.ennerHTML = '<select size=10 id=\"list2\" name=\"list2[]\" multiple style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
                x_edit_schedule_used_people('".(int) $rr['CID']."', '".(int) $sheid."', show_list2_options);
            }
    ");
    $tr = $smarty->fetch('control_list2list.tpl');
    // ===============================================================================

    $strIsgroupChecked = ($rr['isgroup']) ? "checked" : "";

    $cancel_url = "document.location.href=\"{$ref}\"; return false;";
    if (false != strstr($ref, '/ved.php4')) {
        $cancel_url = "window.close(); return false;";
    }
    $html=str_replace("[TEACHERS]",get_teacher_select($rr['CID'],$rr['teacher'],false, $rr['begin'], $rr['end'], $sheid),$html);
    $html=str_replace("[CANCELBUTTON]",button(_("Отмена"),"","cancel",$cancel_url),$html);
    $html=str_replace("[ref]",$ref,$html);
    $html=str_replace("[OKBUTTON]",okbutton(),$html);
    $html=str_replace("[self]",$self,$html);
    $html=str_replace("[title]",htmlspecialchars($rr[title],ENT_QUOTES),$html);
    $html=str_replace("[isgroup_checked]",$strIsgroupChecked,$html);
    $html=str_replace("[sheid]",$sheid,$html);
    $html=str_replace("[desc]",$rr[descript],$html);
    $html=str_replace("[cid]",$rr[CID],$html);
    $html=str_replace("[kurs]",sqlvalue("SELECT Title FROM Courses WHERE cid='$rr[CID]'","err201"),$html);
    $html=str_replace("[type]",$typename,$html);
    $html = str_replace("[STARTDAY]",$startday, $html);
    $html = str_replace("[DURATION]",$stopday, $html);
    $html = str_replace("[ABSOLUTE_CLASS]",$absolute_class,$html);
    $html = str_replace("[RELATIVE_CLASS]",$relative_class,$html);

    $tmp="";
    $rq="SELECT CID, Title
      FROM   Courses
      WHERE CID IN (";
    foreach ($cids as $v) $rq.="'".ad($v)."',";
    $rq=substr($rq,0,-1).")";
    $rq.=" AND Status>0
             ORDER BY cid";
    $res=sql($rq,"err21");
    while ($r=sqlget($res)) $tmp.="<option value=$r[cid]>$r[title]";

    $html=str_replace("[kurs]",$tmp,$html);



    if(count($toolsparam)>0) {
        foreach($toolsparam as $key => $value) {
            $toolsparam[$key] = trim($value);
        }
        if (in_array("sAddToAllnew=1",$toolsparam)) {
            $html=str_replace("[ADDTOALL]","checked",$html);
        }
        else {
            $html = str_replace("[ADDTOALL]", "", $html);
        }
    }
	$html=str_replace("[TT_GRADE]", $GLOBALS['tooltip']->display('schedule_edit_grade'), $html);

    /**
    * Назначить при условии
    */
   $cond_schedules = array();
   $cond_rq="SELECT sheid, title, CID
        FROM  schedule
        WHERE schedule.CID = '".(int) $rr['CID']."'";

    $cond_res=sql($cond_rq,"errLISTOFSHEID");

    while ($cond_r=sqlget($cond_res)) {
        $cond_schedules[$cond_r['sheid']] = $cond_r['title'];
    }

/*    $cond_html = "";
    $cond_html .= "<select name=\"condition\" size=\"1\" class='sel100'><option value=-1 selected>"._("неважно")."</option>";
    foreach ($cond_schedules as $key=>$value) {
        $cond_html .= "<option value={$key}";
        if ($rr['cond_sheid']==$key) $cond_html .= " selected ";
        $cond_html .= "> {$value}</option>";
    }
    $cond_html .= "</select>";*/

    $html = str_replace("[COND_SHEIDS]",$cond_html,$html);
    $html = str_replace("[COND_MARK]",(int) substr($rr['cond_mark'],0,-1),$html);

    // Условия назначения занятий
    $schedule_conditions = '';

    $conditions = array();

    if (!empty($rr['cond_sheid']) && ($rr['cond_sheid']!='-1')) {
        $rr['cond_sheid'] = explode('#',$rr['cond_sheid']);
        $rr['cond_mark'] = explode('#',$rr['cond_mark']);
    	array_walk($rr['cond_mark'], create_function('&$v,$k', 'if ($v[strlen($v)-1] == "-") $v = substr($v,0,-1);'));
        foreach($rr['cond_sheid'] as $cond_sheid_key => $cond_sheid) {
            if (isset($cond_schedules[$cond_sheid])) {
                $conditions[] = sprintf('<td colspan=2>'._("Выполнено задание %s на оценку %s").'</td>',$cond_schedules[$cond_sheid],$rr['cond_mark'][$cond_sheid_key]);
            }
        }
    }

    if ($rr['cond_progress']) {
        $conditions[] = '<td>'._("Процент выполненного").': </td><td>'.$rr['cond_progress'].'%</td>';
    }
    if ($rr['cond_avgbal']) {
        $conditions[] = '<td>'._("Средний бал по курсу").': </td><td>'.$rr['cond_avgbal'].'</td>';
    }
    if ($rr['cond_sumbal']) {
        $conditions[] = '<td>'._("Суммарный бал по курсу").': </td><td>'.$rr['cond_sumbal'].'</td>';
    }

    if (is_array($conditions) && count($conditions)) {

        $schedule_conditions = "
              <tr>
                  <td>
                      "._('Условия назначения')."
                  </td>
                  <td><table cellpadding=2 cellspacing=0 border=0><tr>".join('</tr><tr>',$conditions)."</tr></table>
              </tr>";
    }

    $html = str_replace("[SCHEDULE_CONDITIONS]",$schedule_conditions,$html);

    /**
    * Время - Точно, нет
    */
   $periods=getallperiods( );

   if( count ( $periods ) > 0 ) {
     $tmp="<select name='period' id='period' size='1' class='sel100' onchange=\"javascript: if (this.value != -1) {document.getElementById('hh1').value = periodStartH[this.value]; document.getElementById('ii1').value = periodStartI[this.value];document.getElementById('hh2').value = periodStopH[this.value]; document.getElementById('ii2').value = periodStopI[this.value];} get_room_select();  get_teacher_select(); \">";
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
              if ($rr['period']==$period[lid]) $tmp.=" selected ";
              $tmp.=">".$period[ name ].
      " (".min2hours($period[starttime])."..".min2hours($period[ stoptime ]).")</options>";
     }
      $tmp.="</select>";
      $strJs .= "</script>\n";
   }
   else
      $tmp="";
   $html=str_replace("[PERIODS]",$strJs.$tmp,$html);

   $html=str_replace("[TT_PERIOD]", $GLOBALS['tooltip']->display('schedule_edit_period'), $html);
   $html=str_replace("[TT_CYCLE]", $GLOBALS['tooltip']->display('schedule_edit_cycle'), $html);
   $html=str_replace("[TT_ROOMS]", $GLOBALS['tooltip']->display('schedule_edit_rooms'), $html);

    switch ($rr['CHID']) {
        case 0 : $html=str_replace("[REBILD0]","selected",$html); break;
        case 1 : $html=str_replace("[REBILD1]","selected",$html); break;
        case 2 : $html=str_replace("[REBILD2]","selected",$html); break;
        case 3 : $html=str_replace("[REBILD3]","selected",$html); break;
        case 4 : $html=str_replace("[REBILD4]","selected",$html); break;
        case 5 : $html=str_replace("[REBILD5]","selected",$html); break;
        default: $html=str_replace("[REBILD0]","selected",$html); break;
    }

    $html=str_replace(array("[REBILD0]","[REBILD1]","[REBILD2]","[REBILD3]","[REBILD4]","[REBILD5]"),array("","","","","",""),$html);
    $html=str_replace("[ADDPARAMVED]",(($rr['vedomost']) ? "checked" : "" ),$html);

    $DD1 = substr($rr['begin'], 8, 2);
    $MM1 = substr($rr['begin'], 5, 2);
    $YY1 = substr($rr['begin'], 0, 4);
    $hh1 = substr($rr['begin'], 11, 2);
    $mm1 = substr($rr['begin'], 14, 2);
    $timestamp1 = mktime($hh1, $mm1, 0, $MM1, $DD1, $YY1);

//  $DD1=date("d",$rr[begin]);
//  $MM1=date("m",$rr[begin]);
//  $YY1=date("Y",$rr[begin]);
//  $hh1=date("H",$rr[begin]);
//  $mm1=date("i",$rr[begin]);
//

    $DD2 = substr($rr['end'], 8, 2);
    $MM2 = substr($rr['end'], 5, 2);
    $YY2 = substr($rr['end'], 0, 4);
    $hh2 = substr($rr['end'], 11, 2);
    $mm2 = substr($rr['end'], 14, 2);
    $timestamp2 = mktime($hh2, $mm2, 0, $MM2, $DD2, $YY2);

//  $DD2=date("d",$rr[end]);
//  $MM2=date("m",$rr[end]);
//  $YY2=date("Y",$rr[end]);
//  $hh2=date("H",$rr[end]);
//  $mm2=date("i",$rr[end]);

    $tmp="";
    for ($i=1; $i<=31; $i++) $tmp.="<option".($i==$DD1?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[DD1]",$tmp,$html);
    $tmp="";
    for ($i=1; $i<=31; $i++) $tmp.="<option".($i==$DD2?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[DD2]",$tmp,$html);

    $tmp="";
    for ($i=1; $i<=12; $i++) $tmp.="<option".($i==$MM1?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[MM1]",$tmp,$html);
    $tmp="";
    for ($i=1; $i<=12; $i++) $tmp.="<option".($i==$MM2?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[MM2]",$tmp,$html);

    $tmp="";
    for ($i=2002; $i<=2030; $i++) $tmp.="<option".($i==$YY1?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[YY1]",$tmp,$html);
    $tmp="";
    for ($i=2002; $i<=2030; $i++) $tmp.="<option".($i==$YY2?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[YY2]",$tmp,$html);

    $tmp="";
    for ($i=0; $i<=23; $i++) $tmp.="<option".($i==$hh1?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[hh1]",$tmp,$html);
    $tmp="";
    for ($i=0; $i<=23; $i++) $tmp.="<option".($i==$hh2?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[hh2]",$tmp,$html);

    $tmp="";
    for ($i=0; $i<=59; $i++) $tmp.="<option".($i==$mm1?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[mm1]",$tmp,$html);
    $tmp="";
    for ($i=0; $i<=59; $i++) $tmp.="<option".($i==$mm2?" selected":"")." value=\"$i\">$i";
    $html=str_replace("[mm2]",$tmp,$html);


    if (!$rr['isgroup']) {
        //$strTmp = selGr($kurs, $intValidGroup,0);
        $strTmp = selGr($kurs, $intValidGroup,0);
    } else {
        $strTmp = selAutoGroups($intValidGroup, 0);
    }
    $mhtml=create_new_html(0,0);

    //$rooms=getRooms( $kurs, false );


    //if( count ($rooms ) > 0 ){
//        $tmp_room = "<select id=\"room\" name='room' size='1' class='sel100' onChange=\"get_room_select();\">";
        $tmp_room = get_room_select($rr['rid'],$rr['SHEID'],$rr['begin'],$rr['end'],$count_people,false,0,$kurs);
//        $tmp_room .= "</select>";
    /*}
    else {
        $tmp_room = "";
    }
*/

    //if (!$tmp_room) $ajax_javascript = '';
    $html = str_replace('[AJAX_JAVASCRIPT]', $ajax_javascript, $html);
    $html = str_replace("[room_select]", $tmp_room, $html);

    $html=str_replace("[room]",$tmp_room,$html);
    //fn}

    $html=str_replace('[studlist]',$tr,$html);

    $html=str_replace("[GROUP_SEL]",$strTmp,$html);
    $html=str_replace("[GROUP_ARRAY]",grArray($kurs),$html);
    // $html=str_replace("[REBILD]",$rebild,$html);

    $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);

    if ($GLOBALS['controller']->enabled) {
        $mhtml=words_parse($mhtml,$words);
        $mhtml=path_sess_parse($mhtml);
        $GLOBALS['controller']->setView('Document');
        if (false != strstr($ref, '/ved.php4')) {
            $GLOBALS['controller']->setView('DocumentPopup');
        }
        $GLOBALS['controller']->captureFromReturn(CONTENT,$mhtml);
        $GLOBALS['controller']->setHeader(_("Редактирование занятия"));
    }

    printtmpl($mhtml);

    if (debug) s_timeprint();
    break;

    case "modify_submit":
    $ref = isset($_POST['ref']) && strlen($_POST['ref']) ? $_POST['ref'] : $GLOBALS['sitepath'] . "schedule.php4";
    $goUrl = JS_GO_URL;
    if (false != strstr($ref, '/ved.php4')) {
        $goUrl = JS_CLOSE_SELF_GO_URL_OPENER;
    }

    if ($_POST['typeID']==1 && !$_POST['form']['module']) {
        $GLOBALS['controller']->setView('Document');
        $GLOBALS['controller']->setMessage(_('Вы не выбрали модуль'),JS_GO_BACK);
        $GLOBALS['controller']->terminate();
        exit();
    }
    if ($relative_day1 && $relative_day2) {
        $startday=($relative_day1-1)*24*60*60 + 1; // В СЕКУНДАХ
        $stopday=$startday + ($relative_day2-1)*24*60*60 - 1 + 23*60*60 + 59*60 + 59; // В СЕКУНДАХ
        //$stopday=$startday + $relative_day2*24*60*60 - 1; // В СЕКУНДАХ
        //$startday=($relative_day1-1)*24*60*60 + 1; // В СЕКУНДАХ
        //$stopday=$startday + ($relative_day2-1)*23*60*60+59*60+59; // В СЕКУНДАХ
    }
    $_POST['list1'] = (array)$_POST['list1'];
    $_POST['list2'] = (array)$_POST['list2'];

    $select = isset($select) ? $select : array();
    $select3 = array();

    $select = $_POST['list2'];
    $select2 = array_merge($_POST['list1'],$_POST['list2']);

    $query_s = "SELECT DISTINCT MID FROM scheduleID WHERE sheid = '{$sheid}'";
    $res_s = sql($query_s);
    while($row_s = sqlget($res_s)) {
    	$select3[] = $row_s['MID'];
    }

    $arr_1 = array(-1);
    $arr_2 = $arr_3 = array();

    // DELETE FROM ScheduleID
    if (is_array($_POST['list1']) && count($_POST['list1'])) {
        foreach($_POST['list1'] as $value) {
            $arr_1[] = $value;
        }
    }

/*    if (is_array($select3)) {
    	foreach ($select3 as $value) {
    		if (!in_array($value, $select)) {
    			$arr_1[] = $value;
    		}
    	}
    }
*/
    // UPDATE ScheduleID
    $arr_2 = array_intersect($_POST['list2'], $select3);
//    $arr_2 = array_intersect($select, $select3);

    // INSERT INTO ScheduleID
    if (is_array($_POST['list2']) && count($_POST['list2'])) {
        foreach ($_POST['list2'] as $value) {
            if (!in_array($value, $select3)) {
                $arr_3[] = $value;
            }
        }
    }
/*    if (is_array($select)) {
    	foreach ($select as $value) {
    		if (!in_array($value, $select3)) {
    			$arr_3[] = $value;
    		}
    	}
    }
*/

    if(isset($_POST['period']) && !empty($_POST['period']) ) {
        $period = $_POST['period'];
    }
    else {
        $period = "-1";
    }

    $cid=sqlvalue("SELECT cid FROM schedule WHERE sheid='$sheid'","err311");
    $isgroup=sqlvalue("SELECT isgroup FROM schedule WHERE sheid='$sheid'","err311");
    if ($cid===false) exit("schedule ID $sheid not found");

    /*if (sqlvalue("SELECT COUNT(*) FROM Teachers WHERE cid='$cid' AND mid='$mid'","err310")==0)
    exit("access denied for schedule N$sheid");*/

    if (debug) pr($_POST);
    //   die();

    intvals("YY1 YY2 MM1 MM2 DD1 DD2 hh1 hh2 mm1 mm2 rebild vedomost typeID");
    $timestamp1 = mktime($hh1, $mm1, 0, $MM1, $DD1, $YY1);
    $timestamp2 = mktime($hh2, $mm2, 0, $MM2, $DD2, $YY2);

    $res=sql("SELECT * FROM EventTools WHERE typeid='$typeID'","err40");
    if (!sqlrows($res)) {
    			//exit("unknown type");
		       	$GLOBALS['controller']->setView('Document');
   	   			$GLOBALS['controller']->setMessage(_("На данном курсе невозможно создать занятие данного типа"), $goUrl, $ref);
   	   			$GLOBALS['controller']->terminate();
   	   			exit();
    }
    $r=sqlget($res);
    sqlfree($res);

    $tools=array();
    foreach (explode(",",$r[tools]) as $v) {
        $tools[trim($v)]=trim($v);
    }          
    if (debug) echo "<li>".implode(" - ",$tools);
    
   // Adobe Connect Pro
   $connectId = getField('schedule', 'connectId', 'SHEID', $sheid);   
   
   if ($connectId) {
        require_once($GLOBALS['wwf'].'/lib/classes/Curl.class.php');
        require_once($GLOBALS['wwf'].'/lib/classes/Connect.class.php');
                
        try {
             $cp = new ConnectProXMLApiAdapter(CONNECT_PRO_HOST, CONNECT_PRO_ADMIN_LOGIN, CONNECT_PRO_ADMIN_PASSWORD);
             $cp->updateMeeting($connectId, array('id' => $sheid, 'title' => $title, 'begin' => date('Y-m-d H:i', $timestamp1), 'end' => date('Y-m-d H:i', $timestamp2), 'source-sco-id' => $_POST['template']));

             if (is_array($arr_1) && count($arr_1)) {
                 $cp->removeMeetingUserList($connectId, $arr_1);	 
             }
             
             if (is_array($arr_2) && count($arr_2)) {
             	 $cp ->addMeetingUserList($connectId, $arr_2);
             }
             
             $cp->addMeetingUserList($connectId, array($_POST['teacher']), 'host');
        } catch (Exception $e) {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_("Ошибка изменения занятия Adobe Connect Pro.").' '.htmlspecialchars($e->getMessage()), $goUrl,$ref);
            $GLOBALS['controller']->terminate();
            exit();   
        }              
    }

    if ($sheid) {
       // webinar
       if (is_array($_POST['webinar_list2']) && count($_POST['webinar_list2'])) {
           array_walk($_POST['webinar_list2'], 'intval');
           
           $webinarMaterials = array();
           $sql = "SELECT * FROM webinar_plan WHERE pointId = '$sheid' AND bid > 0";
           $res = sql($sql);
           while($row = sqlget($res)) {
               $webinarMaterials[$row['bid']] = $row;
           }

           sql("DELETE FROM webinar_plan WHERE pointId = '$sheid' AND bid NOT IN ('".join("','", $_POST['webinar_list2'])."')");
           
           $sql = "SELECT bid, filename, title FROM library WHERE bid IN ('".join("','", $_POST['webinar_list2'])."')";
           $res = sql($sql);
           while($row = sqlget($res)) {
               if (strlen($row['filename'])) {
                   $fname = $GLOBALS['sitepath'].'library'.$row['filename'];
               	   if (isset($webinarMaterials[$row['bid']])) {
                       sql("UPDATE webinar_plan SET title = ".$GLOBALS['adodb']->Quote($row['title']).", href = ".$GLOBALS['adodb']->Quote($fname)." WHERE bid = '".$row['bid']."'");               	   	
               	   } else {
                       sql("INSERT INTO webinar_plan (pointId, href, title, bid) VALUES ('$sheid', ".$GLOBALS['adodb']->Quote($fname).", ".$GLOBALS['adodb']->Quote($row['title']).", '{$row['bid']}')");
               	   }   
               }    
           }
       }
   }

    // описание полей, которые можно вводить в поле TOOLS базы данных
    $vars=array("tests"=>"tests_testID",
    "module"=>"module_moduleID",
    'run' => 'run_id',
    "externalURLs"=>"externalURLs_urls",
    "redirectURL"=>"redirectURL_url",
    "sAddToAllnew"=>"sAddToAllnew",
    "collaborator"=>"collaborator",
    "formulagr_id"=>"formulagr_id",
    'penaltyFormula_id' => 'penaltyFormula_id',
    );
    if (!$isgroup) {
        $arrPostExternalURLs[0] = $form[externalURLs];
        $arrPostFormula[0] = $form[formula_id];
        $arrPostGrFormula[0] = $form[formulagr_id];
        $arrPostPenaltyFormulas[0] = $form[penaltyFormula_id];
        $arrPostModule[0] = $form[module];
        $arrPostRun[0] = $form[run];
        $arrPostRedirectURL[0]= $form[redirectURL];
        $arrPostTests[0] = $form[tests];
        if(isset($form[collaborator]))
        $arrPostCollaborator[0] = $form[collaborator];
        $arrPostMids[0] = "blabla";
    } else {

        //             $sql = (is_array($form[tests])) ? "tests_testID=0;" : "";

        foreach ($vars as $k => $v) {
            $sql .= (isset($form[$k])) ? "{$v}=0;" : "";
        }
        sql("UPDATE schedule SET
              title=".$GLOBALS['adodb']->Quote($title).",
              descript=".$GLOBALS['adodb']->Quote($desc).",
              `begin` = " . $adodb->DBTimeStamp($timestamp1) . ",
              `end` = " . $adodb->DBTimeStamp($timestamp2) . ",
              CHID='".$rebild."',
              period = '-1',
              rid = '$room',
              vedomost='".$vedomost."',
              period='".(int) $period."',
              startday = '".(int) $startday."',
              stopday = '".(int) $stopday."',
              teacher = '".(int) $_POST['teacher']."'
              WHERE sheid='$sheid'","err300",1);

//              cond_sheid='".(int) $condition."',
//              cond_mark='".(int) $mark."-',

        $str_arr_1 = count($arr_1) ? "AND MID IN (" . implode(", ", $arr_1) . ")" : "";
        sql("DELETE FROM scheduleID WHERE sheid={$sheid} {$str_arr_1}","err301",1);

        $arrPostExternalURLs = $form[externalURLs];
        $arrPostFormula = $form[formula_id];
        $arrPostGrFormula = $form[formulagr_id];
        $arrPostPenaltyFormulas = $form[penaltyFormula_id];
        $arrPostModule = $form[module];
        $arrPostRun = $form[run];
        $arrPostRedirectURL= $form[redirectURL];
        $arrPostTests = $form[tests];
        if(isset($form[collaborator]))
        $arrPostCollaborator = $form[collaborator];
        $arrPostMids = $_POST['select_group'];
    }

    foreach ($arrPostMids as $k => $m) {
        $form[externalURLs] = $arrPostExternalURLs[$k];
        $form[formula_id] = $arrPostFormula[$k];
        $form[formulagr_id] = $arrPostGrFormula[$k];
        $form[penaltyFormula_id] = $arrPostPenaltyFormulas[$k];
        $form[module] = $arrPostModule[$k];
        $form[run] = $arrPostRun[$k];
        $form[redirectURL] = $arrPostRedirectURL[$k];
        $form[tests] = $arrPostTests[$k];
        if(isset($arrPostCollaborator[$k]))
        $form[collaborator] = $arrPostCollaborator[$k];

        if ($isgroup) {
            $select[0] = $m;
        }
        if (isset($form[tests])) $form[tests]=intval($form[tests]);
        $form[sAddToAllnew] = (isset($form[sAddToAllnew])) ? 1 : 0;
        if (isset($form[module])) $form[tests]=intval($form[tests]);
        if (isset($form[run])) $form[run]=intval($form[run]);
        if (isset($form[formulagr_id])) $form[formulagr_id] = intval($form[formulagr_id]);
        if (isset($form[penaltyFormula_id])) $form[penaltyFormula_id] = intval($form[penaltyFormula_id]);
        if (isset($form[collaborator])) $form[collaborator]=intval($form[collaborator]);
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
        $sql="";
        foreach ($tools as $v) {
            if (isset($vars[$v])) {
                if (!isset($form[$v])) exit("Value \$form[$v] not found");
                $tmp=$vars[$v];
                $$tmp=$form[$v];
            }
            if(is_array($partparams[$v]))
            foreach ($partparams[$v] as $vv=>$kk) {
                if (in_array($kk,$vars)) {
                    $sql.="$kk=".$$kk."; ";
                }
                else $sql.="$kk=".$partvalues[$v][$vv]."; ";
            }
        }
        if ($form['formula_id']) $sql.="formula_id={$form['formula_id']};";
        if ($form['formulagr_id']) $sql.="formulagr_id={$form['formulagr_id']};";
        if ($form['penaltyFormula_id']) $sql .= "penaltyFormula_id={$form['penaltyFormula_id']};";
        if ($form[sAddToAllnew]) $sql.="sAddToAllnew=1;";
        if ($form[collaborator]) $sql.="collaborator=".$form['collaborator'].";";
        if (!$isgroup) {
            $query = "UPDATE schedule
                             SET title=".$GLOBALS['adodb']->Quote($title).",
                                 descript=".$GLOBALS['adodb']->Quote($desc).",
                                 begin = " . $adodb->DBTimeStamp($timestamp1) . ",
                                 end = " . $adodb->DBTimeStamp($timestamp2) . ",
                                 CHID='".$rebild."',
                                 period = '".(int) $period."',
                                 vedomost='".$vedomost."',
                                 rid = '$room',
                                 startday = '".(int) $startday."',
                                 stopday = '".(int) $stopday."',
                                 teacher = '".(int) $_POST['teacher']."'
                             WHERE sheid='$sheid'";
//                                 cond_sheid='".(int) $condition."',
//                                 cond_mark='".(int) $mark."-'

            $res = sql($query, "err345");

            require_once $GLOBALS['wwf'].'/lib/sharepoint/BootStrap.class.php';
            if (defined(SHAREPOINT_ENABLE)) {
                include_once($GLOBALS['wwf'].'/lib/sharepoint/Schedule.class.php');
           
            $schedule = new SharePointSchedule(
                array(
                   'title'       => $title,
                   'description' => $desc,
                   'begin'       => date('Y-m-d H:i:s', $timestamp1),
                   'end'         => date('Y-m-d H:i:s', $timestamp2),
                   'rebild'      => $rebild
                )
            );
            $schedule->update($sheid);
            
                        
        }
        }

        $li=array();
        $li2=array();
        $tmp="";

        if (count($select2))   // все студенты
        foreach ($select2 as $v) {
            $li2[]=intval($v);
        }

        $tmp=substr($tmp,0,-1);

        //кастыль для уничтожения мусора
        $res = sql("SELECT scheduleID.SSID
                    FROM scheduleID
                    LEFT JOIN People ON (People.MID = scheduleID.MID)
                    WHERE scheduleID.SHEID = '$sheid' AND People.MID IS NULL"
                    );
        $SSIDSofKilledMids = array();
        while ($row = sqlget($res)) {
            $SSIDSofKilledMids[] = $row['SSID'];
        }
        if (count($SSIDSofKilledMids)) {
            sql("DELETE FROM scheduleID WHERE SSID IN ('".implode("','",$SSIDSofKilledMids)."')","err304",1);
        }


        if (count($li2) && !$isgroup) {
        	/*
            if (count($li) && 0) // удаляем у ВСЕХ студентов записи с данным занятием а надо только у тех у кого они были а теперь нету
            sql("DELETE FROM scheduleID WHERE sheid=$sheid AND
                      MID IN (".implode(",",$li2).") AND MID NOT IN (".implode(",",$li).")",
            "err301",1);
            else
            */
        	$str_arr_1 = count($arr_1) ? "AND MID IN (" . implode(", ", $arr_1) . ")" : "";
            sql("DELETE FROM scheduleID WHERE sheid={$sheid} {$str_arr_1}","err304",1);
        }
        if (count($select)) {
            foreach ($select as $v) {   // отмеченные
            	if (in_array($v, $arr_3)) {
            		$tmp = "($sheid,".intval($v).",'".(int) $isgroup."', '{$sql}')";
            		sql("INSERT INTO scheduleID ( sheid,mid,isgroup,toolParams) VALUES $tmp","err305");
            	}
            	elseif (in_array($v, $arr_2)) {
            		sql("UPDATE scheduleID SET toolParams = '{$sql}' WHERE sheid = '{$sheid}' AND mid = '{$v}'","err305");
            	}
            }
        }
        sql("DELETE FROM scheduleID WHERE sheid='$sheid' AND mid='-1'");
        $tmp = "('$sheid', '-1', '{$sql}')";
        sql("INSERT INTO scheduleID ( sheid,mid,toolParams) VALUES $tmp","err306");

    }
    if ($isgroup) {
        $sql = "";
        foreach ($vars as $k => $v) {
            $sql .= (in_array($k, array_keys($_POST['form']))) ? "{$v}=0;" : "";
        }
        $intGroupId = intval($_POST['group']);
        $rq="INSERT INTO scheduleID (sheid, gid, isgroup, toolParams) VALUES ({$sheid}, '".(int) $intGroupId."', '1', '".ad($sql)."')";
        sql($rq,"err61");
    }
        

    $mhtml=create_new_html(0,0);
    $html.="<P align=center><a href='' onclick=\"window.close();\">"._("Занятие успешно отредактировано")."</a></P>";
    $mhtml=str_replace("[ALL-CONTENT]",$html,$mhtml);

    $GLOBALS['controller']->setView('Document');
    if (false != strstr($ref, '/ved.php4')) {
        $GLOBALS['controller']->setView('DocumentPopup');
    }
    $GLOBALS['controller']->setMessage(_("Занятие успешно отредактировано").'. '.checkSchedule4SynhCourse($cid), $goUrl, $ref);

    printtmpl($mhtml);

    if (debug) s_timeprint();


    break;

    case 'modify_people':
        // SAJAX BEGIN

        if (!$GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS_PEOPLE)) {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_("У вас нет прав на редактирование данного занятия"),JS_CLOSE_SELF_REFRESH_OPENER);
            $GLOBALS['controller']->terminate();
            exit();
        }

        require_once($wwf.'/lib/sajax/Sajax.php');

        sajax_init();
        sajax_export("edit_schedule_unused_people", "edit_schedule_used_people", "get_room_condition_message");
        sajax_handle_client_request();
        $sajax_javascript = sajax_get_javascript();
        $GLOBALS['sajax_remote_uri'] = $sitepath.'schedule.php4?c=modify_people';

        $smarty = new Smarty_els();

        $sql = "SELECT * FROM schedule WHERE SHEID='".(int) $sheid."'";
        $res = sql($sql);
        $info = sqlget($res);

        $smarty->assign('list1_options',edit_schedule_unused_people('',$info['CID'],0,$sheid));
        $smarty->assign('list2_options',edit_schedule_used_people($info['CID'],$sheid));
        $smarty->assign('list1_name','list1');
        $smarty->assign('list2_name','list2');
        $smarty->assign('list1_title',_(CObject::toUpperFirst("слушатели")));
        $smarty->assign('list2_title',_('Занятие назначено'));
        $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
        $smarty->assign('editbox_search_name','editbox_search');
        $smarty->assign('editbox_search_text','');
        $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
        $smarty->assign('list1_container_id','list1_container');
        $smarty->assign('list2_container_id','list2_container');
        $smarty->assign('list3_name','list3');
        $smarty->assign('list3_options',selGrved($info['CID'],0));
        $smarty->assign('list3_change',"if (elm = document.getElementById('editbox_search')) get_list_options(elm.value);");
        $smarty->assign('list1_list2_click','get_room_condition_message();');
        $smarty->assign('list2_list1_click','get_room_condition_message();');
        $smarty->assign('javascript', $sajax_javascript."
                function show_list_options(html) {
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select size=10 id=\"list1\" name=\"list1[]\" multiple style=\"width:100%\">'+html+'</select>';
                    prepare_options('list1', dropped, assigned);
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

                    get_list2_options('');
                    x_edit_schedule_unused_people(str, '".(int) $info['CID']."', gid, '".(int) $sheid."', show_list_options);
                }

                function show_list2_options(html) {
                    var elm = document.getElementById('list2_container');
                    if (elm) elm.innerHTML = '<select size=10 id=\"list2\" name=\"list2[]\" multiple style=\"width: 100%\">'+html+'</select>';
                    prepare_options('list2', assigned, dropped);
                }

                function get_list2_options(str) {
                    var elm = document.getElementById('list2_container');
                    if (elm) elm.ennerHTML = '<select size=10 id=\"list2\" name=\"list2[]\" multiple style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
                    x_edit_schedule_used_people('".(int) $info['CID']."', '".(int) $sheid."', show_list2_options);
                }
        ");
        $smarty->assign('rid',$info['rid']);
        $smarty->assign('sheid',(int) $sheid);
        $smarty->assign('okbutton',okbutton());
        $smarty->assign('sitepath',$GLOBALS['sitepath']);
        $GLOBALS['controller']->setView('DocumentPopup');
        $GLOBALS['controller']->setHeader(_("Редактирование занятия").': '.htmlspecialchars($info['title'],ENT_QUOTES));
        $GLOBALS['controller']->captureFromReturn(CONTENT,$smarty->fetch('schedule_people.tpl'));
        $GLOBALS['controller']->terminate();
    break;

    case 'modify_people_submit':

        if (!$GLOBALS['controller']->checkPermission(SHEDULE_PERM_EDIT_OTHERS_PEOPLE)) {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_("У вас нет прав на редактирование данного занятия"),JS_CLOSE_SELF_REFRESH_OPENER);
            $GLOBALS['controller']->terminate();
            exit();
        }

        $mids = $del = $insert = array();
        $del[] = -1;
        $insert[] = -1;

        $sql = "SELECT DISTINCT MID FROM scheduleID WHERE scheduleID.SHEID = '".(int) $_POST['sheid']."'";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $mids[] = $row['MID'];
        }

        if (is_array($_POST['list1']) && count($_POST['list1'])) {
            foreach($_POST['list1'] as $v) {
                if (in_array($v,$mids)) {
                    $del[] = $v;
                }
            }
        }

        if (is_array($_POST['list2']) && count($_POST['list2'])) {
            foreach($_POST['list2'] as $v) {
                if (!in_array($v,$mids)) {
                    $insert[] = $v;
                }
            }
        }

        $toolParams = getField('scheduleID','toolParams','SHEID',(int) $_POST['sheid']);

        if (is_array($del) && count($del)) {
            sql("DELETE FROM scheduleID WHERE MID IN ('".join("','",$del)."')");
        }

        if (is_array($insert) && count($insert)) {
            foreach($insert as $v) {
                $sql = "INSERT INTO scheduleID (SHEID,MID,toolParams) VALUES ('".(int) $_POST['sheid']."','".(int) $v."','".$toolParams."')";
                sql($sql);
            }
        }

        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_("Занятие успешно отредактировано"), JS_CLOSE_SELF_REFRESH_OPENER);
        $GLOBALS['controller']->terminate();
    break;

}

function edit_schedule_unused_people($search='', $cid=0, $gid=0, $sheid=0) {
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
        LEFT JOIN scheduleID ON (scheduleID.MID = People.MID AND scheduleID.SHEID = '".(int) $sheid."')
        WHERE Students.CID = '".(int) $cid."'
        AND scheduleID.MID IS NULL
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

function edit_schedule_used_people($cid, $sheid) {
    $html = '';

    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

    $sql = "
    SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login, People.EMail
    FROM People
    INNER JOIN Students ON (Students.MID = People.MID)
    LEFT JOIN scheduleID ON (scheduleID.MID = People.MID AND scheduleID.SHEID = '".(int) $sheid."')
    WHERE Students.CID = '".(int) $cid."'
    AND scheduleID.MID IS NOT NULL
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

    return $html;
}

function get_room_condition_message($sheid, $mids) {
    if ($sheid) {
        $sql = "SELECT * FROM schedule WHERE SHEID='".(int) $sheid."'";
        $res = sql($sql);

        if ($row = sqlget($res)) {
            if ($row['rid']>0) {
                $mids = explode('#',$mids);
                if (is_array($mids) && count($mids)) {
                    // вместимость
                    $capacity = get_room_capacity($row['rid']);
                    if (count($mids)>$capacity) {
                        return sprintf(_("Превышена вместимость аудитории (макс.: %s)"),$capacity);
                    }

                    // не учатся ли эти люди в этот же момент в другой аудитории?
                    $sql =
                        "SELECT schedule.SHEID, schedule.rid, scheduleID.MID, People.LastName, People.FirstName, People.Login
                        FROM schedule
                        INNER JOIN scheduleID ON (scheduleID.SHEID=schedule.SHEID)
                        INNER JOIN People ON (People.MID = scheduleID.MID)
                        WHERE schedule.SHEID <> '".(int) $sheid."'
                        AND schedule.rid>0
                        AND schedule.rid <> '".(int) $row['rid']."'
                        AND scheduleID.MID IN ('".join("','",$mids)."')
                        AND ((((schedule.begin >= '{$row['begin']}' AND schedule.begin <= '{$row['end']}')
                            OR (schedule.end >= '{$row['begin']}' AND schedule.end <= '{$row['end']}')
                            OR (schedule.begin <= '{$row['begin']}' AND schedule.end >= '{$row['begin']}'))
                            AND schedule.timetype = '0')
                        OR (((schedule.startday >= '{$row['startday']}' AND schedule.startday <= '{$row['stopday']}')
                            OR (schedule.stopday >= '{$row['startday']}' AND schedule.stopday <= '{$row['stopday']}')
                            OR (schedule.startday <= '{$row['startday']}' AND schedule.stopday >= '{$row['startday']}'))
                            AND schedule.timetype = '1'))
                        ORDER BY People.LastName, People.FirstName, People.Login
                        ";

                    $res = sql($sql);
                    if (sqlrows($res)>0) {
                        $str = '';
                        while ($row = sqlget($res)) {
                            $str .=	htmlspecialchars($row['LastName'].' '.$row['FirstName'],ENT_QUOTES)."\n";
                        }
                        return _("Для данных слушателей в это время уже назначено занятие в другой аудитории").":\n".$str;
                    }

                }
            }
        }
    }
    return '';
}

?>