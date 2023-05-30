<?php
require_once('1.php');

if (!$_SESSION['s']['login']) {
    exitmsg(_("Пожалуйста, авторизуйтесь"), $GLOBALS['sitepath']);
    exit();
}

$mid = (int) $_SESSION['s']['mid'];

$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->addFilter(_('поставщик'),'provider',getProvidersList(),$_REQUEST['provider']);
if ($_REQUEST['action']) {
    $GLOBALS['controller']->setHelpSection($_REQUEST['action']);
}

$smarty = new Smarty_els();

switch($_REQUEST['action']) {
    case 'edit_post':
        $id = (int) $_POST['id'];

        if ($id) {
            $sql = "UPDATE providers
                    SET
                        title = ".$GLOBALS['adodb']->Quote($_POST['title']).",
                        address = ".$GLOBALS['adodb']->Quote($_POST['address']).",
                        contacts = ".$GLOBALS['adodb']->Quote($_POST['contacts']).",
                        `description` = ".$GLOBALS['adodb']->Quote($_POST['description'])."
            WHERE id = '$id'";

            $res = sql($sql);

            $GLOBALS['controller']->setMessage(_('поставщик успешно отредактирован'), JS_GO_URL, $sitepath.'providers.php');

        } else {
            if (strlen($_POST['title'])) {
                $res = sql("INSERT INTO providers (title, address, contacts, `description`) VALUES (
                ".$GLOBALS['adodb']->Quote($_POST['title']).",
                ".$GLOBALS['adodb']->Quote($_POST['address']).",
                ".$GLOBALS['adodb']->Quote($_POST['contacts']).",
                ".$GLOBALS['adodb']->Quote($_POST['description'])."
                )");

                $id = sqllast();

            } else {
                $GLOBALS['controller']->setMessage(_('Введите название поставщика'), JS_GO_BACK);
                $GLOBALS['controller']->terminate();
                exit();
            }
            $GLOBALS['controller']->setMessage(_('поставщик успешно создан'), JS_GO_URL, $sitepath.'providers.php');
        }
        $GLOBALS['controller']->terminate();
        exit();
        break;
    case 'edit':

        $id = (int) $_REQUEST['id'];

        $sql = "SELECT * FROM providers WHERE id = '$id'";
        $res = sql($sql);

        $cert = false;
        if ($row = sqlget($res)) {
            $cert = $row;
        }

        if ($_POST['title']) {
            $cert = array();
            $cert['title'] = $_POST['title'];
        }

        $smarty->assign('cert', $cert);
        $smarty->assign('sitepath', $sitepath);
        $smarty->assign('okbutton', okbutton());
        echo $smarty->fetch('providers_edit.tpl');

        $GLOBALS['controller']->captureStop(CONTENT);
        $GLOBALS['controller']->terminate();
        exit();
        break;
    case 'delete':
        $id = intval($_GET['id']);
        $sql = "DELETE FROM providers WHERE id = {$id}";
        sql($sql);
        $GLOBALS['controller']->setMessage(_('поставщик успешно удален'), JS_GO_URL, $sitepath.'providers.php');
        $GLOBALS['controller']->terminate();
        exit();
        break;
}

$sql = "SELECT * FROM providers ".($_REQUEST['provider'] ? "WHERE id='".(int)$_REQUEST['provider']."'" : '')." ORDER BY title";
$res = sql($sql);

$certs = array();
while($row = sqlget($res)) {
    $certs[$row['id']] = $row;
}

$smarty->assign('providers', $certs);
$smarty->assign('icon_delete', getIcon('delete'));
$smarty->assign('icon_edit', getIcon('edit'));
$smarty->assign('sitepath', $sitepath);
$smarty->assign('okbutton', okbutton());
echo $smarty->fetch('providers.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>