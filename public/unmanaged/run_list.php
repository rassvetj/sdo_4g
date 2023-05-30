<?php
require_once('1.php');
require_once($wwf.'/lib/classes/Module.class.php');
require_once('Pager/examples/Pager_Wrapper.php');

$pagerOptions = array(
    'mode'    => 'Sliding',
    'delta'   => 5,
    'perPage' => 25,
);

if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"), $GLOBALS['sitepath']);

$GLOBALS['controller']->captureFromOb(CONTENT);

$courses = selCourses($_SESSION['s']['tkurs'],$cid, $GLOBALS['controller']->enabled, false, 0);
if (!$_REQUEST['action']) {
    $GLOBALS['controller']->addFilter(_("Курс"), 'cid', $courses, $cid, true);
}

$GLOBALS['controller']->setHelpSection($_REQUEST['action']);

switch($_REQUEST['action']) {
    case 'copy':
        $cid = (int) $_POST['cid'];
        if (sqlvalue("SELECT locked FROM Courses WHERE CID = '$cid'")) {
            $GLOBALS['controller']->setMessage(_("Курс").' `'.$courses[$cid].'` '._("заблокирован, копирование невозможно"), JS_GO_BACK);
            $GLOBALS['controller']->terminate();
            exit();
        }
        if ($cid && is_array($_POST['run_id']) && count($_POST['run_id'])) {
            foreach($_POST['run_id'] as $id) {
                $sql = "SELECT * FROM training_run WHERE run_id = '".(int) $id."'";
                $res = sql($sql);

                while($row = sqlget($res)) {
                    unset($row['run_id']);
                    $row['cid'] = $cid;
                    $row['name'] = _('Копия: ').$row['name'];
                    $sql = "INSERT INTO training_run (name, cid, path) VALUES (".$GLOBALS['adodb']->Quote($row['name']).", '".(int) $cid."', ".$GLOBALS['adodb']->Quote($row['path']).")";
                    sql($sql);
                }
            }
        }
        $GLOBALS['controller']->setMessage(_('Программы успешно скопированы'), JS_GO_URL, $GLOBALS['sitepath'].'run_list.php?cid='.$cid.'&pageID='.$_REQUEST['pageID']);
        $GLOBALS['controller']->terminate();
        exit();
        break;
    case 'add':
        $GLOBALS['controller']->setHeader(_("Создание программы"));
    case 'edit':
        if (!$GLOBALS['controller']->getHeader()) {
            $GLOBALS['controller']->setHeader(_("Редактирование программы"));
        }        
        $smarty = new Smarty_els();

        $smarty->assign('run', CRunModuleItem::get(array('name' => 'run_id', 'value' => $_REQUEST['id']), 'training_run', 'CRunModuleItem'));
        $smarty->assign('sitepath', $sitepath);
        $smarty->assign('cid', $cid);
        $smarty->assign('cencelbutton', button(_("Отмена"), '', 'cancel', '', $GLOBALS['sitepath'].'run_list.php?cid='.$cid));
        $smarty->assign('okbutton', okbutton());
        echo $smarty->fetch('run_list_edit.tpl');

        $GLOBALS['controller']->captureStop(CONTENT);
        $GLOBALS['controller']->terminate();
        exit();
        break;
    case 'update':
        $GLOBALS['controller']->setView('DocumentBlank');

        $id = (int) $_REQUEST['id'];
        $cid = (int) $_REQUEST['cid'];        
        $name = trim($_POST['name']);
        $file = trim($_POST['filename']);

        if ($id) {
            $sql = "UPDATE training_run SET name=".$GLOBALS['adodb']->Quote($name).", path = ".$GLOBALS['adodb']->Quote($file)." WHERE run_id = '".$id."'";
            $message = _("Программа успешно обновлена");
            $lastAction = 'edit';
        }else {
            $sql = "INSERT INTO training_run (name, cid, path) VALUES (".$GLOBALS['adodb']->Quote($name).", '".(int) $cid."', ".$GLOBALS['adodb']->Quote($file).")";
            $message = _("Программа успешно создана");
            $lastAction = 'add';
        }
        
        if (strlen($name)) {
            if (strlen($file)) {                
                sql($sql);
                $GLOBALS['controller']->setMessage($message, JS_GO_URL, $GLOBALS['sitepath'].'run_list.php?cid='.$cid.'&pageID='.$_REQUEST['pageID']);
            }else {
                $GLOBALS['controller']->setMessage(_('Выберите программу'), JS_GO_URL, $GLOBALS['sitepath']."run_list.php?action=$lastAction&id=$id");
            }
        }else {
            $GLOBALS['controller']->setMessage(_('Введите название программы'), JS_GO_URL, $GLOBALS['sitepath']."run_list.php?action=$lastAction&id=$id");
        }
        $GLOBALS['controller']->terminate();
        exit();
        break;
    case 'delete':
        $GLOBALS['controller']->setView('DocumentBlank');

        if ($_REQUEST['id']) {
            sql("DELETE FROM training_run WHERE run_id = '".(int) $_REQUEST['id']."'");
            sql("UPDATE organizations SET vol2 = '0' WHERE vol2 = '".(int) $_REQUEST['id']."'");
        }

        $GLOBALS['controller']->setMessage(_('Программа успешно удалена'), JS_GO_URL, $GLOBALS['sitepath'].'run_list.php?cid='.$cid.'&pageID='.$_REQUEST['pageID']);
        $GLOBALS['controller']->terminate();
        exit();
        break;
}

$smarty = new Smarty_els();

$items = $modules = array();

if (is_array($courses) && count($courses)) {
    $sql = "SELECT DISTINCT vol2 FROM organizations WHERE vol2 > 0 AND cid IN ('".join("','", array_keys($courses))."')";
    $res = sql($sql);

    while($row = sqlget($res)) {
        $modules[$row['vol2']] = $row['vol2'];
    }
}

$where = '';
if (count($modules)) {
    $modules = array_chunk($modules, 50);
    $where = ' OR (';
    for($i=0;$i<count($modules);$i++) {
        if (count($modules[$i])) {
            if ($i>0) {
                $where .= ' OR ';
            }
            $where .= "run_id IN ('".join("','", $modules[$i])."')";
        }
    }
    $where .= ')';
}

$sql = "SELECT * FROM training_run WHERE cid = '".(int) $cid."' $where ORDER BY name";
$page = Pager_Wrapper_Adodb($adodb, $sql, $pagerOptions);
if ($page) {
    while($row = sqlget($page['result'])) {
        $row['perm_edit'] = false;
        if (isset($courses[$row['cid']])) {
            $row['perm_edit'] = true;
        }
        $items[$row['run_id']] = new CRunModuleItem($row);
    }
}
$smarty->assign('perm_edit', $GLOBALS['controller']->checkPermission(RUNLIST_PERM_EDIT));
$smarty->assign('page', $page);
$smarty->assign('items', $items);
$smarty->assign('icon_edit',   getIcon('edit', _('Редактировать свойства программы')));
$smarty->assign('icon_delete', getIcon('delete', _('Удалить программу')));
$smarty->assign('cid', $cid);
$smarty->assign('sitepath', $sitepath);
$smarty->assign('okbutton', okbutton());
$smarty->assign('courses', $courses);
//отображаем тело страницы только при выбранном фильтре
if ($_GET['cid']) {
    echo $smarty->fetch('run_list.tpl');
}

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>