<?php
require_once('1.php');
require_once('lib/classes/xml2array.class.php');
require_once('lib/classes/EventWeight.class.php');
if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
$cid = (in_array($_REQUEST['cid'], $s['tkurs'])) ? $_REQUEST['cid'] : false;

if (isset($_POST['xml']) && !empty($_POST['xml'])) {

    $shedule_event_parser = new CEventWeightXMLParser();    
    $shedule_event_parser->init_string($_POST['xml']);
    $shedule_event_parser->parse();
    $shedule_event_parser->update_events($_GET['cid']);
    die();

}




$GLOBALS['controller']->addFilter(_('Курс'), 'cid', CEventWeight::get_courses_assoc($s['tkurs']), $cid, REQUIRED);
$GLOBALS['controller']->captureFromOb(CONTENT);
$smarty = new Smarty_els();
$smarty->assign('action',$action);
$smarty->assign('sitepath',$sitepath);
$smarty->assign('okbutton',okbutton());
$smarty->assign('cancelbutton',button(_("Отмена"), '', 'cancel', '', $GLOBALS['sitepath']."shedule_weight.php?cid=$cid"));
$smarty->assign('course',array('id' => $cid, 'title' => cid2title($cid)));

switch($_POST['post']) {
    case 'edit':
        CEventWeight::replace($_POST);
        refresh("{$sitepath}shedule_weight.php?cid={$cid}");        
    break;
    case 'add':
        CEventWeight::add($_POST);
        refresh("{$sitepath}shedule_weight.php?cid={$cid}");        
    break;
}

switch($action) {
    case 'delete':
        CEventWeight::delete($_GET['id']);
        refresh("{$sitepath}shedule_weight.php?cid={$cid}");        
    break;
    case 'edit':
        $smarty->assign('weight',CEventWeight::get($_GET['id'], $cid));
        $controller->setHeader(_('Веса типов занятий'));
        $GLOBALS['controller']->setHelpSection('edit');
    case 'add':
        $smarty->assign('courses',CEventWeight::get_courses($s['tkurs']));
        $smarty->assign('events',CEventWeight::get_events());

        $html = $smarty->fetch('shedule_weight_edit.tpl');    
    break;
    default:
        if ($cid){
            $smarty->assign('cid',$cid);
            $weights = CEventWeight::get_as_array($cid);
            if (!CEventWeight::check_sum($weights,$cid))
                $GLOBALS['controller']->setMessage(_('Сумма весов, действующих на курсе типов занятий, должна быть равна 100'),JS_REFRESH_SELF);
                
            
            $smarty->assign('weights',CEventWeight::get_as_array($cid));
            $html = $smarty->fetch('shedule_weight.tpl');    
        }
}

echo $html;

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>