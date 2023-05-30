<?php
require_once('1.php');
require_once('lib/classes/Orgstructure.class.php');

header('Content-Type: text/html; charset='.$GLOBALS['controller']->lang_controller->lang_current->encoding);

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

require_once('lib/sajax/SajaxWrapper.php');
$sajaxJavascript = CSajaxWrapper::init(array('structureItemClick'));
if ($_REQUEST['id']) {
	list($prefix, $id) = @explode('_',$_REQUEST['id']);

    if ($_REQUEST['action'] == 'collapse') {
        unset($_SESSION['s']['orgstructure']['opened'][$id]);
        exit('1');
    }

    $tree = new COrgstructureTree();
    $tree->initialize($id);
    $_SESSION['s']['orgstructure']['opened'][$id] = $id;
    echo $tree->fetch(true);
	exit();
}

if (!defined("LOGO_URL")) {
	define("LOGO_URL", true);
}
$GLOBALS['controller']->setView('DocumentFrame');
$GLOBALS['controller']->disableNavigation();
$GLOBALS['controller']->view_root->return_path = $GLOBALS['sitepath'];
$GLOBALS['controller']->captureFromOb(CONTENT_EXPANDED);

$GLOBALS['controller']->addFilter(_('специализация'), 'specialization', COrgstructureTree::getSpecializations(), $_REQUEST['specialization']);

$smarty = new Smarty_els();
$smarty->assign('sajaxJavascript',$sajaxJavascript);
$smarty->assign('sitepath',$sitepath);

$tree = new COrgstructureTree();

if (isset($_SESSION['s']['orgstructure']['filtered'])) {
    unset($_SESSION['s']['orgstructure']['filtered']);
}
/*
if (isset($_POST['position'])) {
    $_SESSION['s']['orgstructure']['filters']['position'] = trim($_POST['position']);
}
if (isset($_POST['login'])) {
    $_SESSION['s']['orgstructure']['filters']['login'] = trim($_POST['login']);
}
if (isset($_SESSION['s']['orgstructure']['filters']['position']) && strlen($_SESSION['s']['orgstructure']['filters']['position'])) {
	$tree->addFilter("position", $_SESSION['s']['orgstructure']['filters']['position']);
	$smarty->assign('position', $_SESSION['s']['orgstructure']['filters']['position']);
}

*/
if (isset($_REQUEST['specialization'])) {
    $_SESSION['s']['orgstructure']['filters']['specialization'] = $_REQUEST['specialization'];
}
if (isset($_SESSION['s']['orgstructure']['filters']['specialization']) && strlen($_SESSION['s']['orgstructure']['filters']['specialization'])) {
	$tree->addFilter("specialization", $_SESSION['s']['orgstructure']['filters']['specialization']);
//	$smarty->assign('specialization', $_SESSION['s']['orgstructure']['filters']['specialization']);
}
if (isset($_POST['login'])) {
    $_SESSION['s']['orgstructure']['filters']['login'] = trim($_POST['login']);
}
if (isset($_SESSION['s']['orgstructure']['filters']['login']) && strlen($_SESSION['s']['orgstructure']['filters']['login'])) {
	$tree->addFilter("login", $_SESSION['s']['orgstructure']['filters']['login']);
	$smarty->assign('login', $_SESSION['s']['orgstructure']['filters']['login']);
}
if (!count($tree->filters)) {
	if (isset($_SESSION['s']['orgstructure']['opened_filter']) && count($_SESSION['s']['orgstructure']['opened_filter'])) {
		foreach ($_SESSION['s']['orgstructure']['opened_filter'] as $soid) {
			if (isset($_SESSION['s']['orgstructure']['opened'][$soid])) {
			    unset($_SESSION['s']['orgstructure']['opened'][$soid]);
			}
		}
		unset($_SESSION['s']['orgstructure']['opened_filter']);
	}
}

$tree->initialize(0);
$tree->initializeOpened($_SESSION['s']['orgstructure']['opened']);


$smarty->assign('skin_url', $GLOBALS['controller']->view_root->skin_url);
$smarty->assign('tree2', $tree->fetch(true));
//$smarty->assign('specializations', COrgstructureTree::getSpecializations());
echo $smarty->fetch('orgstructure_toc2.tpl');

if (isset($_SESSION['s']['orgstructure']['current']) && $tree->isCurrentDisplayed()) {
	echo "<script type=\"text/javascript\" language=\"JavaScript\">
	      <!--
	      parent.mainFrame.location.href = '{$GLOBALS['sitepath']}orgstructure_info.php?page_id={$GLOBALS['controller']->page_id}&id=".(int) $_SESSION['s']['orgstructure']['current']."';
	      //-->
	      </script>";
} else {
	unset($_SESSION['s']['orgstructure']['current']);
}

if (isset($_REQUEST['specialization'])) {
	echo "<script type=\"text/javascript\" language=\"JavaScript\">
	      <!--
			if (parent.mainFrame.get_checked_items) parent.mainFrame.getCheckedItems();
	      //-->
	      </script>";
} else {
	unset($_SESSION['s']['orgstructure']['current']);
}

$GLOBALS['controller']->captureStop(CONTENT_EXPANDED);

$GLOBALS['controller']->captureFromOb(CONTENT_COLLAPSED);
/*
$smarty->assign('tree2', $tree->fetch(true));
echo $smarty->fetch('orgstructure_toc2.tpl');
*/
$GLOBALS['controller']->captureStop(CONTENT_COLLAPSED);

//unset($_SESSION['s']['orgstructure']['checked']);

$GLOBALS['controller']->terminate();

// sajax functions
function structureItemClick($id, $checked = true) {
	intvals('id');

    if ($id > 0) {
    	if ($checked) {
    		$_SESSION['s']['orgstructure']['checked'][$id] = $id;
    	} else {
    		if (isset($_SESSION['s']['orgstructure']['checked'][$id])) {
    		    unset($_SESSION['s']['orgstructure']['checked'][$id]);
    		}
    	}
    }
}
?>