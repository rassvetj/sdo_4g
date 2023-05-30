<?php
require_once("1.php");
require_once("lib/classes/Option.class.php");
require_once("../../application/model/HM/Currency/CurrencyModel.php");

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if (!$admin) login_error();

$action = $_REQUEST['action'];

//$GLOBALS['controller']->setHeader(_("Общие настройки"));
$smarty = new Smarty_els();

switch($action) {
    case 'save':
    	$message = _("Настройки успешно сохранены");
		if (in_array($_POST['tab'], array('cms', 'at', 'sis'))) {
			$GLOBALS['table_options'] = "options_{$_POST['tab']}";
			$subdir = "/{$_POST['tab']}/";
		} else {
			$subdir = '/';
		}
		
		#7057
		if(!$_POST['options']['headStructureUnitName']['text']){
		    $_POST['options']['headStructureUnitName']['text'] = _('Организационная структура');
		}
        if(!$_POST['options']['edo_subdivision_root_name']['text']){
		    $_POST['options']['edo_subdivision_root_name']['text'] = _('Учебная структура');
		}
		
        COption::save_array($_POST['options']);
		$GLOBALS['table_options'] = 'OPTIONS'; // на всякий случай вернуть

		$color1 = trim($_POST['options']['color1']['text']);
        $color2 = trim($_POST['options']['color2']['text']);
        if (!strlen($color1) || strlen($color2)) {
            if (file_exists($GLOBALS['wwf'] . $subdir . 'skin.css') && is_writable($GLOBALS['wwf'] . $subdir . 'skin.css')) {
                @unlink($GLOBALS['wwf'] . $subdir . 'skin.css');
            }
        }
        $skin = trim($_POST['options']['skin']['text']);
        if ($skin && ($skin != $controller->view_root->skin)) {
            $controller->view_root->setSkin($skin);
            @unlink($GLOBALS['wwf'] . $subdir . 'skin.css');
            $dirname = $GLOBALS['wwf'] . $subdir . '/smarty/templates_c';
            $cache = dir($dirname);
            while ($entry = $cache->read()) {
                if (is_file($dirname . '/' . $entry)) {
                    @unlink($dirname . '/' . $entry);
                }
            }
        }

        $GLOBALS['controller']->setMessage($message ,JS_GO_URL,$sitepath.'preferences.php' . ((!isset($_REQUEST['skin'])) ? '' : '?skin'));
        $GLOBALS['controller']->terminate();
        exit();
    break;
    default:
    break;
}


$options = COption::get_all_as_array();

$currencyList = HM_Currency_CurrencyModel::getFullNameList();

$GLOBALS['application_metadata'] = array(_("Текстовое поле")=>'add_info',_("Контакты")=>'contacts',_("Почтовый адрес")=>'address_postal',_("Паспортные данные")=>'passport',_("Дата рождения")=>'dateB');

@include_once('metadata_custom.lib.php');

if (!empty($options['regform_items'])) {
    $addons = explode(';',$options['regform_items']);
    if (is_array($addons) && count($addons)) {
        foreach($addons as $v) {
            $tmp[array_search($v,$GLOBALS['application_metadata'])] = $v;
        }
        $addons = $tmp;
        $GLOBALS['application_metadata'] = array_diff($GLOBALS['application_metadata'],$addons);
    }
}
$smarty->assign('all_addons',$GLOBALS['application_metadata']);
$smarty->assign('addons',$addons);

if (!defined("USE_ACTIVE_DIRECTORY_SUPPORT")) define("USE_ACTIVE_DIRECTORY_SUPPORT", is_active_directory_support_exists());

$tab_number = 1;

$smarty->assign('options',$options);
$smarty->assign('currencies',$currencyList);
$smarty->assign('okbutton',okbutton());
$smarty->assign('sitepath',$sitepath);
$smarty->assign('current_skin',$controller->view_root->skin);
$template = (!isset($_REQUEST['skin'])) ? 'preferences_tab_1.tpl' : 'preferences_skin_tab_1.tpl';
$html = $smarty->fetch($template);
if (USE_SIS_INTEGRATION) {
    $html = $smarty->fetch('preferences_tab_1.tpl');
    $GLOBALS['controller']->captureFromReturn('m180301',$html);
}
$GLOBALS['controller']->captureFromReturn(CONTENT,$html);

$GLOBALS['controller']->terminate();

?>