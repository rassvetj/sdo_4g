<?php
require_once('1.php');
require_once('lib/library/library.lib.php');
require_once('lib/classes/xml2array.class.php');
require_once('lib/library/category.class.php');
require_once('lib/laws/laws.lib.php');
require_once('lib/laws/law.class.php');
require_once('lib/laws/laws.class.php');

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

if (isset($_GET['ModID']) && ($_GET['ModID']>0)) $ModID = (int) $_GET['ModID'];
if (isset($_POST['ModID']) && ($_POST['ModID']>0)) $ModID = (int) $_POST['ModID'];
if (isset($ModID) && ($ModID>0)) {
    $GLOBALS['controller']->setView('DocumentPopup');
    $GLOBALS['controller']->addFilter('','ModID','hidden',$ModID);
    $action = 'get';
}

$smarty = new Smarty_els();
$smarty->assign('perm',$s['perm']);
$smarty->assign('page',(int) $page);
$smarty->assign('sitepath',$sitepath);
$smarty->assign('sort',(int) $sort);

if ($GLOBALS['controller']->checkPermission(LAWS_PERM_EDIT_OWN) || 
    $GLOBALS['controller']->checkPermission(LAWS_PERM_EDIT_OTHERS))
    $GLOBALS['controller']->setLink('m170201',array($page,$sort));
if ($GLOBALS['controller']->checkPermission(LAWS_PERM_EDIT_OTHERS))    
    $GLOBALS['controller']->setLink('m170202',array($page,$sort));

$GLOBALS['controller']->addFilter(_("Слова (через пробел)"),'search_string',false,$search_string);
$GLOBALS['controller']->addFilter(_("Условие"),'search_fulltext_condition',array(0,_("И"),_("ИЛИ")),$search_fulltext_condition,false,0,false);
//$GLOBALS['controller']->addFilter('Автор','search_author',false,$seatch_author);
$GLOBALS['controller']->addFilter(_("Автор"),'search_author',CLaws::get_authors_array(),$search_author);
$GLOBALS['controller']->addFilter(_("Инициатор"),'search_initiator',CLaws::get_initiators_array(),$search_initiator);
$GLOBALS['controller']->addFilter(_("Тип"),'search_type',$law_types,$search_type);
$GLOBALS['controller']->addFilter(_("Регион"),'search_region',$law_regions,$search_region);
$GLOBALS['controller']->addFilter(_("Область применения"),'search_area',false,$search_area);
$GLOBALS['controller']->addFilter(_("Раздел"),'search_category',CLaws::get_categories_options(),$search_category);
$GLOBALS['controller']->addFilter(_("Ур. доступа"),'search_access_level',CLaws::get_access_levels_array(),$search_access_level);
//$GLOBALS['controller']->addFilter('Полнотекстовый поиск','search_fulltext',false,$search_fulltext);

$search_array = array(
'string' => addslashes($search_string),
'author' => addslashes($search_author),
'initiator' => addslashes($search_initiator),
'type' => addslashes($search_type),
'area' => addslashes($search_area),
'region' => addslashes($search_region),
'access_level' => addslashes($search_access_level),
'category' => addslashes($search_category),
'fulltext' => addslashes($search_fulltext),
'fulltext_condition' => (int) $search_fulltext_condition
);

echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
switch($_POST['post_action']) {
    case 'add':
        CLaws::add($_POST,$_FILES);
        refresh("{$sitepath}laws.php?page=$page&sort=$sort");
    break;
    case 'edit':
        CLaws::update($_POST,$_FILES);
        refresh("{$sitepath}laws.php?page=$page&sort=$sort");
    break;
    case 'import':
        CLaws::import($_FILES);
        $GLOBALS['controller']->setMessage(_('Импортирование разделов успешно завершено'),JS_GO_URL,"{$sitepath}laws.php?page=$page&sort=$sort");
        $GLOBALS['controller']->terminate();
        exit();
        //refresh("{$sitepath}laws.php?page=$page&sort=$sort");
    break;
    case 'add_version':
        CLaws::add_version($_POST,$_FILES);
        refresh("{$sitepath}laws.php?page=$page&sort=$sort");
    break;
}

switch($action) {
    case 'get':
        if (isset($_GET['id']) && ($_GET['id']>0)) {
            CLaws::export_to_module($_GET['id'],$ModID);
            $GLOBALS['controller']->setMessage(_("Документ успешно добавлен"),JS_GO_URL,'javascript:window.close();');
        }
        $laws = CLaws::get_list($search_array,$sort,$page,LAWS_PER_PAGE);
        if (!$GLOBALS['search_exists']) {
            if ($GLOBALS['s']['user']['meta']['access_level']>0) 
                $sql_access_level = " access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";
            $smarty->assign('pages',doPerPages("laws.php?ModID={$ModID}&",$page,LAWS_PER_PAGE,'laws.id','laws'," WHERE parent='0' AND ({$sql_access_level} access_level='0')"));
        }
        $smarty->assign('laws',$laws);
        $smarty->assign('ModID',$ModID);
        $html = $smarty->fetch('laws.tpl');
    break;
    case 'delete':
        CLaw::del($_GET['id']);
        refresh("{$sitepath}laws.php?page=$page");        
    break;
    case 'add':
        $smarty->assign('categories',CLaws::get_categories());
        $smarty->assign('types',$law_types);
        $smarty->assign('regions',$law_regions);
        $html = $smarty->fetch('laws_add.tpl');  
    break;
    case 'edit':
        $law = CLaw::get_item($_GET['id']);
        $smarty->assign('law',$law);        
        $categories = CLaws::get_categories();
/*        if (is_array($categories) && count($categories) && is_array($law['cats']) && count($law['cats'])) {
            $categories = array_diff($categories,$law['cats']);
        }
*/
        $smarty->assign('categories',$categories);
        $smarty->assign('types',$law_types);
        $smarty->assign('regions',$law_regions);
        $html = $smarty->fetch('laws_edit.tpl');  
    break;
    case 'import':
        $html = $smarty->fetch('laws_categories.tpl');  
    break;
    default:
        $laws = CLaws::get_list($search_array,$sort,$page,LAWS_PER_PAGE);
        if (!$GLOBALS['search_exists']) {
            if ($GLOBALS['s']['user']['meta']['access_level']>0) 
                $sql_access_level = " access_level>='".$GLOBALS['s']['user']['meta']['access_level']."' OR ";
            $smarty->assign('pages',doPerPages('laws.php?',$page,LAWS_PER_PAGE,'laws.id','laws'," WHERE parent='0' AND ({$sql_access_level} access_level='0')"));
        }
        $smarty->assign('laws',$laws);
        $html = $smarty->fetch('laws.tpl');
    break;
}
echo $html;
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();

?>