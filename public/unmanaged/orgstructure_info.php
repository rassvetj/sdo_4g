<?php
require_once('1.php');
require_once("metadata.lib.php");
require_once("positions.lib.php");
require_once('lib/classes/SoidInfo.class.php');
require_once('lib/classes/Orgstructure.class.php');
require_once('lib/classes/CompetenceRole.class.php');

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),$GLOBALS['sitepath']);
$id = (int) $_REQUEST['id'];

if (!$id) {
    if (isset($_SESSION['s']['orgstructure']['current']) && $_SESSION['s']['orgstructure']['current']) {
        $id = $_SESSION['s']['orgstructure']['current'];
    }
}

require_once('lib/sajax/SajaxWrapper.php');
$smarty = new Smarty_els();
$sajaxJavascript = CSajaxWrapper::init(array('getCheckedItems', 'unCheckItems'));

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_('Операции с элементами структуры'));
$GLOBALS['controller']->enableNavigation();
$GLOBALS['controller']->view_root->disableBreadCrumbs();
$GLOBALS['controller']->captureFromOb(CONTENT);


//$GLOBALS['controller']->setTab('m070610');

if ($id > 0) {
//    $GLOBALS['controller']->setLink('m070601', array($id));

//    $GLOBALS['controller']->setTab('m070611', array('href' => "orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=$id"));
//    $GLOBALS['controller']->setTab('m070612', array('href' => "orgstructure_main.php?page_id={$GLOBALS['controller']->page_id}&id=$id&type=add"));
}
//$GLOBALS['controller']->setTab('m070613');
//$GLOBALS['controller']->setTab('m070614');

//$GLOBALS['controller']->setCurTab('m070610');

if (isset($_GET['checked_items_actions'])) {
	switch ($_GET['checked_items_actions']) {
        case "set_mark":
            header("location: orgstructure_polls.php");
            exit();
            break;
        case "add_mark":
        case "delete_mark":
            $smarty->assign("roles", CCompetenceRoles::get_as_array(true));
            echo $smarty->fetch('orgstructure_info_actions.tpl');
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->terminate();
            exit();
            break;
        case "add_courses":
            header("location: orgstructure_courses.php");
            break;
        case "delete_courses":
            break;
        case "reports":
            header("location: rep.php?type=5");
            exit();
            break;
	}
}
elseif (isset($_POST['checked_items_actions'])) {
    $soids = array();
    if (isset($_SESSION['s']['orgstructure']['checked']) && count($_SESSION['s']['orgstructure']['checked'])) {
        $soids = $_SESSION['s']['orgstructure']['checked'];
        foreach ($_SESSION['s']['orgstructure']['checked'] as $value) {
            $soids = array_merge($soids, getChildrenIdArray($value));
        }
        $soids = array_unique($soids);
        if (isset($_SESSION['s']['orgstructure']['filtered']) && count($_SESSION['s']['orgstructure']['filtered'])) {
            $soids_filtered = array();
            foreach ($_SESSION['s']['orgstructure']['filtered'] as $soid) {
                if (in_array($soid, $soids)) {
                	$soids_filtered[] = $soid;
                }
            }
            $soids = $soids_filtered;
        }
    }

	switch ($_POST['checked_items_actions']) {
        case "set_mark":
            break;
        case "add_mark":
            $roles = isset($_POST['roles']) ? $_POST['roles'] : array();
            if (count($soids) && count($roles)) {
                $soids_sql = array();
                foreach ($soids as $soid) {
                    foreach ($roles as $role) {
                        $soids_sql[] = "('{$soid}', '{$role}')";
                    }
                }

		        sql("DELETE FROM structure_of_organ_roles WHERE soid IN ('".implode("', '", $soids)."') AND role IN ('".implode("', '", $roles)."')");
	            sql("INSERT INTO structure_of_organ_roles (soid,role) VALUES " . implode(", ", $soids_sql));
	            $GLOBALS['controller']->setMessage(_("Виды оценки успешно добавлены"),JS_GO_URL, 'orgstructure_info.php');
	            $GLOBALS['controller']->terminate();
	            exit();
            }
            break;
        case "delete_mark":
            $roles = isset($_POST['roles']) ? $_POST['roles'] : array();
            if (count($soids) && count($roles)) {
		        sql("DELETE FROM structure_of_organ_roles WHERE soid IN ('".implode("', '", $soids)."') AND role IN ('".implode("', '", $roles)."')");
	            $GLOBALS['controller']->setMessage(_("Виды оценки успешно удалены"),JS_GO_URL, 'orgstructure_info.php');
	            $GLOBALS['controller']->terminate();
	            exit();
            }
            break;
        case "add_courses":
            break;
        case "delete_courses":
            break;
	}
}

$smarty->assign('sajaxJavaScript', $sajaxJavascript);
$smarty->assign('checked_items', getCheckedItems());
$smarty->assign('itemCard', getStructureItemCard($id));
$smarty->assign('id', $id);
$itemType = 0;
if ($id) {
    $itemType = (int) getField('structure_of_organ','type','soid',(int) $id);
}
$smarty->assign('type',$itemType);
$smarty->assign('skin_url',$GLOBALS['controller']->view_root->skin_url);
$smarty->assign('sitepath', $sitepath);
echo $smarty->fetch('orgstructure_info.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function getCheckedItems() {
    /* Выбранные элементы структуры организации */
    $checked = array();
    if (isset($_SESSION['s']['orgstructure']['checked']) && count($_SESSION['s']['orgstructure']['checked'])) {
    	$sql = "SELECT * FROM structure_of_organ WHERE soid IN ('".join("','", $_SESSION['s']['orgstructure']['checked'])."')";
    	$res = sql($sql);

    	while($row = sqlget($res)) {
    	    $checked[$row['soid']] = $row;
    	}
    }

    /* Оценки */
    //$roles = CCompetenceRoles::get_as_array(true);

    $checked_items = new Smarty_els();
    //$checked_items->assign("sitepath", $GLOBALS['sitepath']);
    $checked_items->assign("checked", $checked);
    //$checked_items->assign("roles", $roles);
    $checked_items->assign("teacher", $_SESSION['s']['perm'] == 2);

    if (!empty($_SESSION['s']['orgstructure']['filters']['specialization'])){
		if ($specializations = COrgstructureTree::getSpecializations()){
	    	$filters['specialization']['title'] = _('Фильтр по специализации');
	    	$filters['specialization']['value'] = $specializations[$_SESSION['s']['orgstructure']['filters']['specialization']];
		    $checked_items->assign("filters", $filters);
		}
    }

	return $checked_items->fetch("orgstructure_checked_items.tpl");
}

function unCheckItems() {
    $_SESSION['s']['orgstructure']['checked'] = array();
    $_SESSION['s']['orgstructure']['filters'] = array();
    return "";
}

function getChildrenIdArray($id) {
    $return_array = array();
    $query = "SELECT soid FROM structure_of_organ WHERE owner_soid = '".(int) $id."'";
    $res = sql($query);
    while ($row = sqlget($res)) {
        $return_array[] = $row['soid'];
        $tmp_array = getChildrenIdArray($row['soid']);
        foreach ($tmp_array as $tmp_value) {
            $return_array[] = $tmp_value;
        }
    }
    return $return_array;
}

?>