<?php
require_once("1.php");
require_once($wwf.'/lib/classes/xml2array.class.php');
require_once($wwf.'/lib/classes/TimelineXMLParser.class.php');

if (!$_SESSION['s']['login']) {
	exitmsg(_("Пожалуйста, авторизуйтесь"), "/?$sess");
}
if ($_SESSION['s']['perm'] < 2) {
	exitmsg(_("К этой странице могут обратится только: представители учебной администрации"), "/?$sess");
}

ob_start();
if (isset($_POST['xml']) && !empty($_POST['xml'])) {
    $timeline_parser = new CTimelineXMLParser();
    $timeline_parser->init_string($_POST['xml']);
    $timeline_parser->parse();
       
    if (is_array($timeline_parser->shedules) && count($timeline_parser->shedules)) {
        $sql = "SELECT MIN(cBegin) AS cBegin FROM Courses WHERE type <> 1 AND cEnd >= ".$adodb->DBDate(time()-60*60*24*7)."";
        $res = sql($sql);
        
        $course_start_date = 0;
        while($row = sqlget($res)) {
            $course_start_date = strtotime($row['cBegin']);
        }
        $course_start_date -= 60*60*24*7; //добавим неделю к началу
       
        if ($course_start_date) {
        
            foreach($timeline_parser->shedules as $module) {                        
                $sql = "UPDATE Courses SET cBegin = ".$GLOBALS['adodb']->DBDate($course_start_date+($module->startdate/1000)).", cEnd = ".$GLOBALS['adodb']->DBDate($course_start_date+($module->enddate/1000))." WHERE CID = '".(int) $module->id."'";
                sql($sql);
                if (is_array($module->conditions) && count($module->conditions)) {
                    sql("DELETE FROM courses_links WHERE cid = '".(int) $module->id."'");
                    foreach($module->conditions as $condition) {
                        sql("INSERT INTO courses_links (cid, `with`) VALUES ('".(int) $module->id."','".(int) $condition->linkwith."')");
                    }
                }
            }
        }
    }
    
    if ($fp = fopen('timeline.log','w+')) {
        pr($_POST);
        pr($timeline_parser);
        $content = ob_get_contents();
        fwrite($fp,$content);
        fclose($fp);
    }

}
ob_clean();

$CID = isset($_GET['CID']) ? intval($_GET['CID']) : 0;

if (isset($_GET['msg'])){
    $controller->setView('DocumentBlank');
    $controller->setMessage(_("Параметры сохранены"),JS_GO_URL,$GLOBALS['sitepath'].'courses_base_timeline.php');
    $controller->terminate();
    exit();
}

//$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->captureFromOb(CONTENT);	

$smarty = new Smarty_els();

$modules = $conditions = array();
/*
$sql = "SELECT MIN(cBegin) as start, MAX(cEnd) as stop FROM Courses WHERE `type` = '1'";
$res = sql($sql);

while($row = sqlget($res)) {
    $start = strtotime($row['start'])-60*60*24*30;
    $stop  = strtotime($row['stop'])+60*60*24*30;
}
*/

$start = $stop = time();

$sql = "SELECT * FROM Courses WHERE type <> 1 AND cEnd >= ".$adodb->DBDate(time()-60*60*24*7)." ORDER BY Title";

$res = sql($sql);
while($row = sqlget($res)) {
    
    $cBegin = strtotime($row['cBegin']);
    $cEnd   = strtotime($row['cEnd']);
    
    $row['start_date'] = date('M d, Y H:i:s',$cBegin);
    $row['stop_date']  = date('M d, Y H:i:s',$cEnd);
    
    //if ($start == 0) $start = $row['start_date'];
    //if ($stop == 0)  $stop = $row['stop_date'];
        
    if ($start > $cBegin) $start = $cBegin;
    if ($stop < $cEnd)    $stop  = $cEnd;
    
    /*
    $sql = "SELECT t2.MID, t2.LastName, t2.FirstName
            FROM developers t1
            INNER JOIN People t2 ON (t2.MID = t1.MID)
            WHERE t1.cid = '".(int) $row['CID']."'";
    $_res = sql($sql);
    while($_row = sqlget($_res)) {
        $row['developers'][] = htmlspecialchars($_row['LastName'].' '.$_row['FirstName'],ENT_QUOTES);
    }
    
    $row['developers'] = @join('<br>',$row['developers']);
    */
    //заполненность
    $sql = "SELECT SID FROM claimants WHERE Teacher='0' AND claimants.CID = '".$row['CID']."'";
    $row['claimants_num'] = ($row['max_student_num'])?sqlrows(sql($sql)).'/'.$row['max_student_num']:sqlrows(sql($sql));
    $row['progress'] = ($row['max_student_num'])?($row['claimants_num']/$row['max_student_num'])*100:'';
    
    //студенты курса
    $row['students_num'] = sqlrows(sql("SELECT MID FROM Students WHERE CID = '".$row['CID']."'"));
    
    //преподователи курса
    $sql = "SELECT p.LastName, p.FirstName, p.Patronymic 
            FROM Teachers t
            LEFT JOIN People p ON (p.MID = t.MID)
            WHERE t.CID = '".$row['CID']."'";
    $rs = sql($sql);
    $row['developers'] = '';
    while ($r = sqlget($rs)) {
        $row['developers'] .= ($row['developers'])?', ':'';
        $row['developers'] .= $r['LastName'].' '.$r['FirstName'].' '.$r['Patronymic'];
    }
        
    $modules[$row['CID']] = $row;
}

$stop  += 60*60*24*7; //добавим неделю к концу
$start -= 60*60*24*7; //добавим неделю к началу

if (count($modules)) {
    $sql = "SELECT * FROM courses_links WHERE cid IN ('".join("','",array_keys($modules))."')";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $conditions[$row['cid']][] = $row;
    }
}

$smarty->assign('sitepath', $sitepath);
$smarty->assign('start',date('M d, Y H:i:s',$start));
$smarty->assign('stop',date('M d, Y H:i:s',$stop));
$smarty->assign('modules',$modules);
$smarty->assign('conditions', $conditions);
$smarty->assign('okbutton',okbutton('ok',"onClick=\"t_absolute.sendTo('{$GLOBALS['sitepath']}courses_base_timeline.php','{$GLOBALS['sitepath']}courses_base_timeline.php?msg=true'); return true;\""));
//$smarty->assign('okbutton',okbutton('ok',"onClick=\"t_absolute.sendTo('javascript:window.close();', '{$GLOBALS['sitepath']}timeline.php?CID={$CID}');\""));
echo $smarty->fetch('courses_base_timeline.tpl');

$GLOBALS['controller']->captureStop(CONTENT);

$GLOBALS['controller']->terminate();
?>