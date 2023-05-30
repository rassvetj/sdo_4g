<?  require_once('1.php');?>
<?  require_once('move2.lib.php');?>
<?  require_once('positions.lib.php');?>
<?
require_once('lib/classes/Chain.class.php');

//$s['mid'] = 2;

   $s[user][assort]=(isset($s[user][assort])) ? $s[user][assort] : 1;
   $s[user][corder]=(isset($s[user][corder])) ? $s[user][corder] : 1;

   $assort=(isset($_GET['assort'])) ? intval($_GET['assort']) : "";

   if ($assort==$s[user][assort]) $s[user][corder]=($s[user][corder]==1) ? 2 : 1;
   if ($assort) $s[user][assort]=$assort;

$s[user][tfull]=(isset($s[user][tfull])) ? $s[user][tfull] : 1;


$tfull=(isset($_GET['all'])) ? intval($_GET['all']) : "";
if ($tfull) $s[user][tfull]=$tfull;

$tfull=$s[user][tfull];


istest();

if (isset($_GET['CID'])) {
        $CID = $_GET['CID'];
} elseif (isset($_POST['CID'])) {
        $CID = $_POST['CID'];
} else {
        $CID = -1;
}

if (isset($_GET['cgid'])) {
        $gr = $_GET['cgid'];
} elseif (isset($_POST['cgid'])) {
        $gr = $_POST['cgid'];
} else {
        $gr = -1;
}
//$gr = -1;

$pm=(isset($_POST['pm'])) ? $_POST['pm'] : array();
$pc=(isset($_POST['pc'])) ? $_POST['pc'] : array();

$go=(isset($_POST['c'])) ? $_POST['c'] : "";

if (/*!$dean &&*/ (!isset($s[tkurs]) && !isset($s[skurs]))) login_error();
if ($CID && !isset($s[tkurs][$CID]) && !isset($s[skurs][$CID]) && ($CID != -1)) login_error();

/*switch ($go) {
   case "stud" : moveStud($pm,$pc); break;
   case "abitur" : moveAbit($pm,$pc); break;
   case "grad" : moveGrad($pm,$pc); break;
   case "other" : moveToStud($pm,$pc); break;
}
*/
$arrTargets = array('student', 'abiturient', 'graduated');
$arrActions = array('tost' => "<option value='tost'>"._("перевести в слушатели"), 'toab' => "<option value='toab'>"._("перевести в претенденты"), 'togr' => "<option value='togr'>"._("перевести в прошедшие обучение"), 'delfromabitur' => "<option value='delfromabitur'>"._("удалить из претендентов"),'delfromgrad' => "<option value='delfromgrad'>"._("удалить из прошедших обучение"),'del' => "<option value='del'>"._("удалить навсегда"));

moveSimple();

if ($go) {
//   exit(location("abitur.php4?CID=$CID$sess"));
   //echo "abitur.php4?CID=$CID";
//   phpinfo();
   //exit();
}


$curcid=$CID;
$html=show_tb(1);

$org_name = getOrgNameByMid($s['mid']);
$string_title = strlen($org_name) ? " ({$org_name})" : "";

$allheader=ph(student_alias_parse("[STUDENT_ALIAS-IMEN-MORE]") . $string_title );

if (($GLOBALS['controller']->enabled) && ($CID>0)) $GLOBALS['controller']->setLink('m210404',array($CID));
$import="<a href='people_import.php?CID=$CID$sess'>".getIcon("import",student_alias_parse(_("импорт")." [sTUDENT_ALIAS-ROD-MORE"))."</a>";

$hstud=ph(student_alias_parse("[STUDENT_ALIAS-IMEN-MORE] <FONT SIZE=0>>>[<a href=#in>"._("Претенденты")."</a>] >>[<a href=#out>"._("Прошедшие обучение")."</a>]>>$import</FONT>"));

$habitur=ph("<a name=in></a>"._("Претенденты"));

$hgrad=ph("<a name=out></a>"._("Прошедшие обучение"));
$hot="";

$allcontent=loadtmpl("abitur-main.html");
$alltr=loadtmpl("abitur-tr.html");

$strSel = ($CID === "0") ? "selected" : "";
$cselect="<option value=\"-1\">- "._("выберите курс")." -</option>";
$cselect.="<option value=\"0\" {$strSel}>- "._("все")." -</option>";

/**
* Выбор режима в зависимости от статуса
*/
//$mode = (int) $_GET['mode'];
switch($s['perm']) {
    case 1:
        $tmpl = "_2";
        $mode=2;
    break;
    case 2:
        $tmpl = "";
        $mode=0;
        if ($GLOBALS['controller']->page_id == 'm2104') $mode = 1;
    break;
    case 4:
    case 3:
        $tmpl = "_1";
        $mode=1;
    break;
    default:
        $tmpl = "_2";
        $mode=2;
    break;
}

switch($mode) {
    case 0:
        $tkurs = $s['tkurs'];
    break;
    case 1:
        $tkurs = $s['tkurs'];
    break;
    case 2:
        $tkurs = $s['skurs'];
    break;
}

//if ($GLOBALS['controller']->enabled) {
//$filter_kurses = selCourses($s['tkurs'],$CID,true);

if ($GLOBALS['controller']->page_id == 'm2104') {    
    $whereClause = "chain>0 AND";
}else {    
    $whereClause = '';
    unset($arrActions['toab']);
}
//Отфильтруем курсы препода
if (is_array($tkurs) && count($tkurs)) {
    $sql = "SELECT `CID`, `Title`
            FROM `Courses`
            WHERE $whereClause CID IN ('".implode("','", $tkurs)."') 
            ORDER BY `Title`";
}

$courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);

$filter_kurses = array();
$res = sql($sql);

while ($row = sqlget($res)) {
    if (!$courseFilter->is_filtered($row['CID'])) continue;
    $filter_kurses[$row['CID']] = $row['Title'];
}

//$filter_kurses = selCourses($tkurs,$CID,true);

if ($CID<=0) $CID=0;
$GLOBALS['controller']->addFilter(_("Курс"),'CID',$filter_kurses,$CID,true);
//} //else $cselect.=selCourses($s['tkurs'],$CID);

if (empty($CID)) {
	$controller->terminate();
	exit();
}

$strCoursesAction = selCourses($tkurs,$CID);

//$strCoursesAction = selCourses($s['tkurs'],$CID);
/////
//$gselect = selOrganization($s['mid']);
//$gselect.=selGrved($CID, $gr);

//if ($GLOBALS['controller']->enabled) {
//    $filter_groups = selGrved($CID,$gr,true);
    //$GLOBALS['controller']->addFilter('Группа','cgid',$filter_groups,$gr,false);
//}

    $html_comments = '';

    $GLOBALS['peopleFilter'] = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

    switch($mode) {
        case 0:
            /**
            * Обучаемые, Претенденты и тд на курсе
            */
            if ($CID) {
                $sql = "SELECT TypeDes, chain FROM Courses WHERE CID='".(int) $CID."'";
                $res = sql($sql);
                if (sqlrows($res)) $row = sqlget($res);
                if ($row['TypeDes']<0) $row['TypeDes'] = $row['chain'];
                if ($row['TypeDes']>0) {
                    $GLOBALS['chainFilter'] = new CChainFilter();
                    $GLOBALS['chainFilter']->init($CID,$GLOBALS['s']['mid']);
	                $cabitur=abiturList_Chain($CID);
                    $arrActions['tost'] = "<option value='accept'> "._("Перевести в слушатели");
                    unset($arrActions['del']);
                    $arrActions['delfromabitur'] = "<option value='delfromabitur'> "._("Отклонить");
                } else {
	                $cabitur=abiturList($CID);
                }
	            $cgraduat=gradList($CID);
	            $cstudents=studList($CID, $gr);
                $GLOBALS['controller']->setHeader(_("Распределение учащихся"));
            }

        break;
        case 1:
            /**
            * Вывод в соотвествии с цепочками согласования
            */
            //меняем шаблон препода на шаблон учадмина
            $tmpl = "_1";
            $GLOBALS['chainFilter'] = new CChainFilter();
            $GLOBALS['chainFilter']->init($CID,$GLOBALS['s']['mid']);
	        //$cabitur = abiturList_Chain($CID); // использует chainFilter
	        $cstudents = studList_Chain($CID, $gr); // используется peopleFilter
            $arrActions['tost'] = "<option value='accept'> "._("принять заявку");
            unset($arrActions['togr']);
            unset($arrActions['del']);
            $arrActions['delfromabitur'] = "<option value='delfromabitur'> "._("отклонить заявку");
            $html_comments = _("Комментарий:")." <input type=\"text\" name=\"comment\" style=\"width: 150px;\">";

            /**
            * Подчинённые и не входящие в организацию
            */

            /*
            $organizations_array = getOrganizations2Agreem($s['mid']);
            $mids[0] = 0;
            foreach ($organizations_array as $value) {
	            $mids[] = get_mid_by_soid($value);
            }
	        $cabitur = abiturList_mid($CID, $mids);
	        //$cabitur .= abiturList_not_in_org($CID, $mids);
	        $cgraduat=gradList_mid($CID, $mids);
	        //$cgraduat .= gradList_not_in_org($CID, $mids);
	        $cstudents=studList_mid($CID, $mids, $gr);
            //$cstudents.=studList_not_in_org($CID,$mids, $gr);

            if (!empty($org_name)) $org_name = "({$org_name})";
            $GLOBALS['controller']->setHeader(_("Мои подчиненные")." ".$org_name);
            */

        break;
        case 2:
            /**
            * Однокурсники (режим для обучаемого)
            */
            if ($CID) {
                $cstudents = get_people_who_study_on_cid($CID);
                $GLOBALS['controller']->setHeader(_("Мои однокурсники"));
            }
        break;
    }
/*
    $organizations_array = getOrganizations2Agreem($s['mid']);
    $mids[0] = 0;
    foreach ($organizations_array as $value) {
	    $mids[] = get_mid_by_soid($value);
    }
    ////
    if($dean && !count($organizations_array)) {
	    $cabitur=abiturList($CID);
	    $cgraduat=gradList($CID);
	    $cstudents=studList($CID, $gr);
    }
    else {
	    $cabitur = abiturList_mid($CID, $mids);
	    $cabitur .= abiturList_not_in_org($CID, $mids);
	    $cgraduat=gradList_mid($CID, $mids);
	    $cgraduat .= gradList_not_in_org($CID, $mids);
	    $cstudents=studList_mid($CID, $mids, $gr);
        $cstudents.=studList_not_in_org($CID,$mids, $gr);
    }
*/

/////
//echo $cstudents;

$other="";
$NULLrow = "<tr clas='tests'><td colspan='99' align='center'>"._("Нет данных")."</td></tr>";
//if($CID && $tfull==2) $other.=studListOther($CID);

$deangr=/*($dean) ? */loadtmpl("abitur-gr.html") /*: ""*/;

$html=str_replace("[ALL-CONTENT]",$allcontent,$html);
$html=str_replace("[DEANGR]",$deangr,$html);
$html=str_replace("[CUR-CID]",$CID,$html);
$html=str_replace("[HEADER]",$allheader,$html);
$html=str_replace("[SELECT-COURSES]",$cselect,$html);
$html=str_replace("[SELECT-COURSES-ACTION]", $strCoursesAction, $html);
$html=str_replace("[SELECT-GROUPS]",$gselect,$html);
$html=str_replace("[CURCID]",$curcid,$html);
$html=str_replace("[HGRAD]",$hgrad,$html);
$html=str_replace("[HABITUR]",$habitur,$html);
$html=str_replace("[HSTUD]",$hstud,$html);
$html=str_replace("[HOT]",$hot,$html);
$html=str_replace("[FULLOT]",(($tfull==2) ? "1" : "2"),$html);

//слушатели
$cstudents = $cstudents?$cstudents:$NULLrow;
$html=str_replace("[STUDENTS]",$cstudents,$html);
$arrTmp = $arrActions;
unset($arrTmp['tost']);
unset($arrTmp['delfromabitur']);
unset($arrTmp['delfromgrad']);
unset($arrTmp['del']);
$html=str_replace("[STUDENTS-ACTIONS]",implode("\n", $arrTmp),$html);

if ($GLOBALS['controller']->enabled) {
    $contr_stud = loadtmpl("abitur-stud".$tmpl.".html");
    $contr_stud =str_replace("[STUDENTS]",$cstudents,$contr_stud);
    $contr_stud=str_replace("[HSTUD]",$hstud,$contr_stud);
    $contr_stud=str_replace("[CURCID]",$CID,$contr_stud);
    $contr_stud=str_replace("[CGID]",$cgid,$contr_stud);
    $contr_stud=str_replace("[STUDENTS-ACTIONS]",implode("\n", $arrTmp),$contr_stud);
    $contr_stud=str_replace("[SELECT-COURSES-ACTION]", $strCoursesAction, $contr_stud);
    $contr_stud=str_replace("[OKBUTTON]",okbutton(),$contr_stud);
    $contr_stud=showSortImg($contr_stud,$s[user][assort]);
    $contr_stud=words_parse($contr_stud,$words);
    $contr_stud=path_sess_parse($contr_stud);
    if ($GLOBALS['controller']->page_id == 'm1211') {
        $GLOBALS['controller']->setHelpSection('assignment');
    }
    // Если студент, то возможность видеть своих однокурсников
    if (($s['perm']==1) || ($s['perm']==2)) {
        $GLOBALS['controller']->setPermissionTemporary('m210401');
    }
    $GLOBALS['controller']->captureFromReturn('m210401',$contr_stud);
}

//претенденты
if ($mode==1 && $s['perm']==2) {
     $tmpl = "";
}
$cabitur = abiturList_Chain($CID); // использует chainFilter
$cabitur = $cabitur?$cabitur:$NULLrow;
$html=str_replace("[ABITUR]",$cabitur,$html);
$arrTmp = $arrActions;
unset($arrTmp['toab']);
unset($arrTmp['togr']);
unset($arrTmp['tocourse']);
unset($arrTmp['delfromgrad']);
unset($arrTmp['del']);
$html=str_replace("[ABITUR-ACTIONS]", implode("\n", $arrTmp),$html);

if ($GLOBALS['controller']->enabled && ($mode != 2)) {
    $contr_stud = loadtmpl("abitur-abitur".$tmpl.".html");
    $contr_stud =str_replace("[ABITUR]",$cabitur,$contr_stud);
    $contr_stud=str_replace("[HABITUR]",$habitur,$contr_stud);
    $contr_stud=str_replace("[CURCID]",$CID,$contr_stud);
    $contr_stud=str_replace("[CGID]",$cgid,$contr_stud);
    $contr_stud=str_replace("[ABITUR-ACTIONS]", implode("\n", $arrTmp),$contr_stud);
    $contr_stud=str_replace("[OKBUTTON]",okbutton(),$contr_stud);
    $contr_stud=str_replace("[COMMENTS]",$html_comments,$contr_stud);
    $contr_stud=showSortImg($contr_stud,$s[user][assort]);
    $contr_stud=words_parse($contr_stud,$words);
    $contr_stud=path_sess_parse($contr_stud);
    if ($s['perm']==2) {
        $GLOBALS['controller']->setPermissionTemporary('m210402');
    }
    if ($mode || $s['perm']!=2) {
    $GLOBALS['controller']->captureFromReturn('m210402',$contr_stud);
    }
}

//прошедшие обучение
$cgraduat = $cgraduat?$cgraduat:$NULLrow;
$html=str_replace("[GRADUATED]",$cgraduat,$html);
$arrTmp = $arrActions;
unset($arrTmp['toab']);
unset($arrTmp['togr']);
unset($arrTmp['tocourse']);
unset($arrTmp['delfromabitur']);
unset($arrTmp['del']);
$html=str_replace("[GRADUATED-ACTIONS]",implode("\n", $arrTmp),$html);

if ($GLOBALS['controller']->enabled && ($mode == 0)) {
    $contr_stud = loadtmpl("abitur-grad.html");
    $contr_stud=str_replace("[GRADUATED]",$cgraduat,$contr_stud);
    $contr_stud=str_replace("[HGRAD]",$hgrad,$contr_stud);
    $contr_stud=str_replace("[CURCID]",$CID,$contr_stud);
    $contr_stud=str_replace("[CGID]",$cgid,$contr_stud);
    $contr_stud=str_replace("[GRADUATED-ACTIONS]",implode("\n", $arrTmp),$contr_stud);
    $contr_stud=str_replace("[OKBUTTON]",okbutton(),$contr_stud);
    $contr_stud=showSortImg($contr_stud,$s[user][assort]);
    $contr_stud=words_parse($contr_stud,$words);
    $contr_stud=path_sess_parse($contr_stud);
    if ($s['perm']==2) {
        $GLOBALS['controller']->setPermissionTemporary('m210403');
    }

    $GLOBALS['controller']->captureFromReturn('m210403',$contr_stud);
}

$html=str_replace("[OTHERSTUD]",$other,$html);
$html=str_replace("[CURCID]",$CID,$html);
$html=str_replace("[CGID]",$cgid,$html);
$html=showSortImg($html,$s[user][assort]);

if ($GLOBALS['controller']->enabled) {
    $html=words_parse($html,$words);
    $html=path_sess_parse($html);
}

printtmpl($html);

?>