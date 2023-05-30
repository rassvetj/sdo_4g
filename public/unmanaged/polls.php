<?php

require_once('1.php');
require_once('lib/classes/Poll.class.php');
require_once('Pager/examples/Pager_Wrapper.php');
require_once('lib/classes/FormParser.class.php');
require_once('lib/classes/CCourseAdaptor.class.php');
require_once('lib/classes/PollCourse.class.php');
require_once('lib/classes/State.class.php');
require_once('positions.lib.php');

if (!$_SESSION['s'][login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($_SESSION['s']['perm']<2) login_error();
$action = $_REQUEST['action'];

$GLOBALS['controller']->captureFromOb(CONTENT);

$smarty = new Smarty_els();
$smarty->assign('okbutton',okbutton());

switch($action) {
    case 'update':
        $formParser = new CFormParser();
        $form = $formParser->get();
        $poll = new CPoll(
            array(
                'name'=>$form['name'],
                'begin'=>date('Y-m-d H:i:s', $form['begin']),
                'end'=>date('Y-m-d H:i:s', $form['end']),
                ));
        if (!empty($form['name'])) {
            $poll->update($form['id']);
            refresh($sitepath.'polls.php');
        } else {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->setMessage(_('Введите название').'<br><br>',JS_GO_URL,$sitepath.'polls.php?action=edit&amp;id='.(int) $form['id']);
        }
    break;
    case 'edit':
        $poll = CPoll::get($_GET['id']);
        $smarty->assign('poll',$poll);
        $html = $smarty->fetch('polls_edit.tpl');
    break;
    /*
    case 'delete':
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_('Удалить все результаты аттестации?').'<br><br>',JS_GO_URL,$sitepath.'polls.php?action=deleteall&amp;id='.(int) $_GET['id'],false,$sitepath.'polls.php?action=deletepoll&amp;id='.(int) $_GET['id']);
    break;
    */
    case 'delete':
        $poll = new CPoll(array());
        $poll->deleteResults($_GET['id']);
        $poll->delete($_GET['id'], true);
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_('Аттестация успешно удалена').'<br><br>',JS_GO_URL,$sitepath.'polls.php');
    break;
    /*
    case 'deletepoll':
        $poll = new CPoll(array());
        $poll->delete($_GET['id']);
        $GLOBALS['controller']->setView('DocumentBlank');
        $GLOBALS['controller']->setMessage(_('Аттестация успешно удалена').'<br><br>',JS_GO_URL,$sitepath.'polls.php');
    break;
    */
    case 'change_state':
        $GLOBALS['controller']->setView('DocumentBlank');
		$poll_state = new CStatePolls($_REQUEST['pid']);
		$poll_state->setConditions(array('mid' => $_REQUEST['mid'], 'pid' => $_REQUEST['pid']));
		$method = ($_REQUEST['state'] != STATE_POLLS_CANCELED) ? 'setState' : 'unsetStates';
		if ($poll_state->$method($_REQUEST['state'])) {
	        $GLOBALS['controller']->setMessage(_('Состояние аттестации изменено').'<br><br>',JS_GO_URL, "subordinates.php?poll={$_REQUEST['pid']}&pag_id=m2204");
		} else {
	        $GLOBALS['controller']->setMessage(_('Невозможно изменить состояние аттестации').'<br><br>',JS_GO_URL, "subordinates.php?poll={$_REQUEST['pid']}&pag_id=m2204");
		}

    break;
    default:
        $sql = "SELECT id as pid FROM polls ORDER BY name";
        $pagerOptions =
           array(
               'mode'    => 'Sliding',
               'delta'   => 5,
               'perPage' => POLLS_PER_PAGE,
               'urlVar' => 'pageID',
           );
        if ($page = Pager_Wrapper_Adodb($GLOBALS['adodb'], $sql, $pagerOptions)) {
           while($row = sqlget($page['result'])) {
               $pids[] = $row['pid'];
           }
        }
        if (is_array($pids) && count($pids))
        $sql = "SELECT id, name, event, formula, deleted, begin, end, data FROM polls WHERE id IN ('".join("','",$pids)."') ORDER BY name";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $polls[] = new CPoll($row);
        }
        $smarty->assign('icon_edit',getIcon('edit'));
        $smarty->assign('icon_delete',getIcon('delete'));
        $smarty->assign('page',$page);
        $smarty->assign('polls',$polls);
        $html = $smarty->fetch('polls.tpl');
}

echo $html;

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>