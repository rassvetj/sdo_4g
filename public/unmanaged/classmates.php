<?php
//Однокурсники

require_once('1.php');

$filter_kurses = selCourses($s['skurs'],$cid,true);
$GLOBALS['controller']->addFilter(_("Курс"),'cid', $filter_kurses, $cid, true);

if ($cid) {
    $sql = "SELECT DISTINCT
                People.Login,
                People.MID,
                People.Patronymic,
                People.FirstName,
                People.LastName
            FROM People
            INNER JOIN Students ON (Students.MID=People.MID)
            WHERE Students.CID='".(int) $cid."'";

    $classmates = array();
    $res = sql($sql);
    while ($row = sqlget($res)) {
        $classmates[$row['MID']] = $row;
    }


    $smarty = new Smarty_els();
    $smarty->assign('classmates',$classmates);
    $smarty->assign('sitepath',$sitepath);
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $smarty->display('classmates.tpl');
    //$GLOBALS['controller']->captureFromOb($smarty->fetch('classmates.tpl'));
    $GLOBALS['controller']->captureStop(CONTENT);
}

$GLOBALS['controller']->terminate();
exit();


?>