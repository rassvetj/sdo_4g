<?php

require_once('1.php');
require_once('lib/library/book.class.php');
require_once('lib/library/ruslom.class.php');
require_once('lib/library/package.class.php');
require_once('lib/library/copy.class.php');
require_once('lib/library/category.class.php');
require_once('lib/library/library.class.php');
require_once('lib/library/vcard.class.php');
require_once('lib/library/library.lib.php');
require_once('lib/json/json.class.php');
require_once('lib/json/json.lib.php');

if (ereg('[0-9a-zA-Z]',$action)){
    $GLOBALS['controller']->setHelpSection($action);
}
        
$json_callback_function = (json_callback_function_valid($_GET['json_callback'])) ? $_GET['json_callback'] : false;
$json_id = (json_id_valid($_GET['json_id'])) ? $_GET['json_id'] : '';

if (!$s[login]) {
	if (!$json_callback_function)
		exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
	else {
		$exitStatus['loggedIn'] = false;
		$exitStatus['json_id'] = $json_id;
		$json = new Services_JSON();
		$json_output = $json->encode($exitStatus);
		$smarty = new Smarty_els();
		$smarty->assign('JSONOutput',$json_callback_function.'('.$json_output.')');
		echo $smarty->fetch('lib_json.tpl');
		die();
	}
}
if (isset($_GET['check_login_status']) && isset($_GET['json_callback']) && isset($_GET['json_id'])) {
	$exitStatus['loggedIn'] = true;
	$exitStatus['json_id'] = $json_id;
	$json = new Services_JSON();
	$json_output = $json->encode($exitStatus);
	$smarty = new Smarty_els();
	$smarty->assign('JSONOutput',$json_callback_function.'('.$json_output.')');
	echo $smarty->fetch('lib_json.tpl');
	die();
}

$library = new CLibrary();

/**
* Если добавление в модуль курса
*/
if (isset($_GET['ModID']) && ($_GET['ModID']>0)) $ModID = (int) $_GET['ModID'];
if (isset($_POST['ModID']) && ($_POST['ModID']>0)) $ModID = (int) $_POST['ModID'];
if (isset($ModID)) $action = 'get';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
if (isset($_GET['sort'])) $s['library']['sort'] = (int) $_GET['sort'];
$json_search_string = (isset($_GET['json_search_string'])) ? $_GET['json_search_string'] : '';

if (!$json_callback_function) {
/**
* Импортирование рубрик
*/
if (isset($_POST['import_rubrics']) && ($_POST['import_rubrics']=='import_rubrics') && ($s['perm']>1)) {    
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->importRubrics($_FILES);
    $GLOBALS['controller']->setMessage(_('Импортирование разделов успешно завершено'),JS_GO_URL,"{$sitepath}lib.php?page=$page");
    $GLOBALS['controller']->terminate();
    exit();    
/*
    refresh("{$sitepath}lib.php?page=$page");        
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
*/
}

/**
* Изменение карточки выдачи  материала
*/
if (isset($_POST['update_assign']) && ($_POST['update_assign']=='update_assign') && ($s['perm']>1)) {    
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->updateAssign($_POST);
    refresh("{$sitepath}lib.php?bid=".(int) $_POST['bids'][0]."&action=history&page=$page");    
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
}

/**
* Назначение копии материала конкретному пользователю
*/
if (isset($_POST['assign']) && ($_POST['assign']=='assign') && $s['mid']) {
    $_POST['type'] = 1;
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->assignItems($_POST);
    refresh("{$sitepath}lib.php?page=$page");    
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
}

// Добавление элемента к модулю
if (isset($_GET['ModID']) && isset($_GET['itemToMod']) && ($s['perm']>1)) {    
    $GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->addItemToMod((int)$_GET['ModID'],(int)$_GET['itemToMod']);
    $GLOBALS['controller']->setMessage(_("Материал успешно добавлен"),JS_GO_URL,'javascript:window.close();');
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
    exit();
}
}

/**
* Обработка входных параметров поиска
*/
if ( (isset($_POST['search_items']) && ($_POST['search_items']=='search_items')) || (isset($_GET['search_items']) && ($_GET['search_items']=='search_items'))) {
	if (!$json_callback_function) {
		$s['library']['search'] = $library->parseSearch($_POST['search']);
	} else {
		$s['library']['search'] = $library->parseSearch($_GET['search'],true);
	}
}

if (empty($s['library']['search']['categories'])) $s['library']['search']['categories'] = (int) 0;

if (!$json_callback_function) {
/**
* Добавление нового материала
*/
if (isset($_POST['add_lib_item']) && ($_POST['add_lib_item']=='add_lib_item') && ($s['perm']>1)) {                
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->addItem($_POST,$_FILES);
    refresh("{$sitepath}lib.php?page=$page");
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
}

// Импортирование учебных материалов из IMS Manifest Package
if (isset($_POST['import_items_to_lib']) && ($_POST['import_items_to_lib']=='import_items_to_lib') && ($s['perm']>1)) {    
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->importItems($_POST,$_FILES);
    refresh("{$sitepath}lib.php?page=$page");    
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
}


// Изменение информации материала
if (isset($_POST['edit_lib_item']) && ($_POST['edit_lib_item']=='edit_lib_item') && ($s['perm']>1)) {    
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $library->updateItem($_POST,$_FILES);    
    refresh("{$sitepath}lib.php?page=$page");    
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
}
}

$smarty = new Smarty_els();

if (!$json_callback_function) {
$smarty->assign('lo_types',$lo_types);
$smarty->assign('sitepath',$sitepath);
$smarty->assign('page',$page);
$smarty->assign('sort',$s['library']['sort']);
$smarty->assign('perm',$s['perm']);
$smarty->assign('ModID',$ModID);
$smarty->assign('can_assign',$GLOBALS['controller']->checkPermission(LIB_PERM_EDIT_GIVE));
}

if (!$json_callback_function || ($action == 'get_search_form' && $json_callback_function)) {
switch ($action) {
   case 'delete_assign': 
        CCopy::delReserv($_GET['bid'], $_SESSION['s']['mid']);
        refresh("{$sitepath}lib.php?page=$page");
    break;
    
    case 'rubrics':
        echo $smarty->fetch('lib_rubrics.tpl');    
    break;
    case 'add':
        $smarty->assign('title',trim(strip_tags($_POST['title'])));
        $smarty->assign('categories',CCategory::get_categories_select('categories',10,'100%','multiple'));
        echo $smarty->fetch('lib_add_item.tpl');    
    break;
    case 'view':
    case 'edit':
        // Добавление новой версии материала
        if (isset($_POST['add_item_version']) && ($_POST['add_item_version']=='add_item_version') && ($s['perm']>1)) {                
            $GLOBALS['controller']->captureFromOb(CONTENT);
            $library->addVersion($_POST,$_FILES);
            refresh("{$sitepath}lib.php?bid=".(int) $_POST['parent']."&action=edit&page=$page");
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->terminate();
        }

        // Удаление версии элемента
        if (isset($_GET['del']) && $_GET['del'] && ($s['perm']>1)) {
            $GLOBALS['controller']->captureFromOb(CONTENT);
            $library->delItem((int) $_GET['del']);
            refresh("{$sitepath}lib.php?bid={$_GET['bid']}&action=edit&page=$page");
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->terminate();
        }
    
        $smarty->assign('categories',CCategory::get_categories_select('categories',10,'100%','multiple'));
        $book = CBook::getItem((int) $_GET['bid']);
        $smarty->assign('book',$book);
        if ($action == 'view') {
            $GLOBALS['controller']->setView('DocumentBlank');
            $GLOBALS['controller']->captureFromOb(CONTENT);
            echo $smarty->fetch('lib_item_card.tpl');
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->terminate();
            exit();
        } else {
            echo $smarty->fetch('lib_edit_item.tpl');
        }
    break;
    case 'get':
        $smarty->assign('categories',CCategory::get_categories_select("search[categories]",5,'100%','',true));
        $smarty->assign('people',getPeopleList());
        $smarty->assign('books',$library->getItems($search,$page,ITEMS_PER_PAGE,$s['library']['sort']));
        $smarty->assign('pages',doPerPages("lib.php?ModID=$ModID&",$page,ITEMS_PER_PAGE,'bid','library',"WHERE parent='0' ".$library->get_where()));
        $smarty->assign('search',$s['library']['search']);
        echo $smarty->fetch('lib_get_item.tpl');    
    break;
    case 'history':   
        if (isset($_GET['assid'])) CCopy::close($_GET['assid']); // Закрытие выдачи материала

        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');

        $sajax_javascript = CSajaxWrapper::init(array('search_user_options'));
        
        $smarty->assign('list1_options', search_user_options());
        $smarty->assign('list1_name','mid');    
        $smarty->assign('list1_title','');    
        $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
        $smarty->assign('editbox_search_name','editbox_search');
        $smarty->assign('editbox_search_text','');
        $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
        $smarty->assign('list1_container_id','list1_container');
        $smarty->assign('list1_list2_click','');
        $smarty->assign('javascript', $sajax_javascript."
                function show_list_options(html) {
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select id=\"list1\" name=\"mid\" style=\"width:300px\">'+html+'</select>';
                }
                        
                function get_list_options(str) {
                    var current = 0;
                    
                    var select = document.getElementById('mid');
                    if (select) current = select.value;                                        
                    
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select id=\"list1\" name=\"mid\" style=\"width:300px\"><option>"._("Загружаю данные...")."</option></select>';
                            
                    x_search_user_options(str, current, show_list_options);
                }
                
        ");
        
        
        $book = CBook::getItem((int) $_GET['bid']);
        $smarty->assign('book',$book);
        $copy = new CCopy($_GET['bid']);
        $smarty->assign('history',$copy->getHistory());
        //$smarty->assign('people',getPeopleList());
        echo $smarty->fetch('lib_item_history.tpl');
    break;
    case 'pre_assign':
        $GLOBALS['controller']->setHelpSection('pre_assign');
        if (isset($_GET['bid'])) {
            $copy = new CCopy($_GET['bid']);
            if ($copy->countCopies()<=0) {
                $GLOBALS['controller']->setView('DocumentBlank');
                $GLOBALS['controller']->setMessage(_('Нет в наличии'), JS_GO_URL, "{$sitepath}lib.php?page=$page");
                $GLOBALS['controller']->terminate();
                exit();
            }
        }
        
        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');

        $sajax_javascript = CSajaxWrapper::init(array('search_user_options'));
        
        $sql = "SELECT Login, LastName, FirstName FROM People WHERE MID = '".(int) $_POST['mid']."'";
        $res = sql($sql);
        $login = '*';
        while($row = sqlget($res)) {
            if (!empty($row['Login'])) {
                $login = $row['Login'];
                break;
            }
            if (!empty($row['LastName'])) {
                $login = $row['LastName'];
                break;
            }
            if (!empty($row['LastName'])) {
                $login = $row['LastName'];
                break;
            }
        }
        $smarty->assign('list1_options', search_user_options(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$login),(int) $_POST['mid']));
        $smarty->assign('list1_name','mid');    
        $smarty->assign('list1_title','');    
        $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
        $smarty->assign('editbox_search_name','editbox_search');
        $smarty->assign('editbox_search_text',$login);
        $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
        $smarty->assign('list1_container_id','list1_container');
        $smarty->assign('list1_list2_click','');
        $smarty->assign('javascript', $sajax_javascript."
                function show_list_options(html) {
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select id=\"list1\" name=\"mid\" style=\"width:300px\">'+html+'</select>';
                }
                        
                function get_list_options(str) {
                    var current = 0;
                    
                    var select = document.getElementById('mid');
                    if (select) current = select.value;                                        
                    
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select id=\"list1\" name=\"mid\" style=\"width:300px\"><option>"._("Загружаю данные...")."</option></select>';
                            
                    x_search_user_options(str, current, show_list_options);
                }
                
        ");
        
        if (!$_GET['bid']) refresh("{$sitepath}lib.php?page=$page");
        $books[] = CBook::getItem((int) $_GET['bid']);
        $smarty->assign('books',$books);
        $smarty->assign('mid',(int) $_POST['mid']);
        //$smarty->assign('people',getPeopleList());
        echo $smarty->fetch('lib_assign.tpl');
    break;
    case 'edit_assign':
        require_once($GLOBALS['wwf'].'/lib/sajax/SajaxWrapper.php');
                
        $copy = CCopy::getInfo($_GET['assid']);
        $smarty->assign('copy',$copy);
        
        $sajax_javascript = CSajaxWrapper::init(array('search_user_options'));
        
        $sql = "SELECT Login, LastName, FirstName FROM People WHERE MID = '".(int) $copy['mid']."'";
        $res = sql($sql);
        $login = '*';
        while($row = sqlget($res)) {
            if (!empty($row['Login'])) {
                $login = $row['Login'];
                break;
            }
            if (!empty($row['LastName'])) {
                $login = $row['LastName'];
                break;
            }
            if (!empty($row['LastName'])) {
                $login = $row['LastName'];
                break;
            }
        }
        
        $smarty->assign('list1_options', search_user_options(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,'UTF-8',$login),$copy['mid']));
        $smarty->assign('list1_name','mid');    
        $smarty->assign('list1_title','');    
        $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
        $smarty->assign('editbox_search_name','editbox_search');
        $smarty->assign('editbox_search_text',$login);
        $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
        $smarty->assign('list1_container_id','list1_container');
        $smarty->assign('list1_list2_click','');
        $smarty->assign('javascript', $sajax_javascript."
                function show_list_options(html) {
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select id=\"list1\" name=\"mid\" style=\"width:300px\">'+html+'</select>';
                }
                        
                function get_list_options(str) {
                    var current = 0;
                    
                    var select = document.getElementById('mid');
                    if (select) current = select.value;                                        
                    
                    var elm = document.getElementById('list1_container');
                    if (elm) elm.innerHTML = '<select id=\"list1\" name=\"mid\" style=\"width:300px\"><option>"._("Загружаю данные...")."</option></select>';
                            
                    x_search_user_options(str, current, show_list_options);
                }
                
        ");
        
        //$smarty->assign('people',getPeopleList());
        echo $smarty->fetch('lib_edit_assign.tpl'); 
    break;
    case 'import':
        echo $smarty->fetch('lib_import_items.tpl'); 
    break;
	case 'get_search_form':
		$form_data['loggedIn'] = true;
		$form_data['json_search_form_id'] = $json_id;
		$form_data['form_values']['peoples_list'] = refactor_people_list_for_json_output( getPeopleList() );
		$form_data['form_values']['categories_list'] = CCategory::get_categories_list_for_json_output();

		$form_data['form_elements'] = '<?xml version="1.0" encoding="UTF-8"?><form action="lib.php" method="GET"><input type="hidden" name="search_items" value="search_items" /><input type="text" name="search[title]" value="" title="Наименование" /><input type="text" name="search[author]" value="" title="Автор" /><input type="text" name="search[description]" value="" title="Описание" /><input type="text" name="search[keywords]" value="" title="Ключевые слова" /><input type="text" name="search[publisher]" value="" title="Издатель" /><group title="Год издания"><input type="text" name="search[publish_date_from]" value="" title="С" /><input type="text" name="search[publish_date_to]" value="" title="По" /></group><input type="text" name="search[uid]" value="" title="Уникальный номер" /><input type="select" name="search[mid]" value_source="peoples_list" title="На руках у" /><input type="select" name="search[categories]" value_source="categories_list" title="Категория" /><input type="submit" name="Submit" value="Искать" /></form>';
			
		$json = new Services_JSON();
		$json_output = $json->encode(
			$form_data
		);
		$smarty->assign('JSONOutput',$json_callback_function.'('.$json_output.')');
		echo $smarty->fetch('lib_json.tpl');
	break;

    default:
    
        // Удаление элемента из библиотеки
        if (isset($_GET['del']) && $_GET['del'] && ($s['perm']>1)) {    
            $library->delItem((int) $_GET['del']);
            refresh("{$sitepath}lib.php?page=$page");    
        }

        $smarty->assign('authors', CLibrary::getAuthors());
        $smarty->assign('publishers', CLibrary::getPublishers());
        $smarty->assign('categories',CCategory::get_categories_select("search[categories]",8,'100%','',true));
        //$smarty->assign_by_ref('people',getPeopleList());
        $smarty->assign('book',false);
        $smarty->assign('books',$library->getItems($s['library']['search'],$page,ITEMS_PER_PAGE,$s['library']['sort']));
        $smarty->assign('pages',doPerPages('lib.php?',$page,ITEMS_PER_PAGE,'library.bid','library',$library->get_join()." WHERE parent='0' AND cid = '0' ".$library->get_where()));
        $smarty->assign('search',$s['library']['search']);
        $smarty->assign('can_add',$GLOBALS['controller']->checkPermission(LIB_PERM_EDIT_OWN));
        echo $smarty->fetch('lib.tpl');
    break;    
}
} else if($json_callback_function) {
	$json = new Services_JSON();
	$json_output = $json->encode(
		refactor_search_results_for_json_output(
			$library->getItems($s['library']['search'],$page,ITEMS_PER_PAGE, ($s['library']['sort']) ? ($s['library']['sort']) : 0 ),
			$page,
			getMaxPagesCount('library.bid','library',$library->get_join()." WHERE parent='0' ".$library->get_where(),ITEMS_PER_PAGE),
			$json_id,
			$json_search_string,
			$s['library']['sort']
		)
	);
	$smarty->assign('JSONOutput',$json_callback_function.'('.$json_output.')');
	echo $smarty->fetch('lib_json.tpl');
}

function get_on_hands_material($mid) {

    if (!$GLOBALS['controller']->enabled)
    $ret .= ph(_("Учебные материалы на руках"));
    $ret .= "<table width=100% class=main cellspacing=0>";
    $ret .= "<tr><th>"._("Издание")."</th><th>"._('Кол-во экземпляров')."</th><th>"._("Дата выдачи")."</th><th>"._("Дата возврата")."</th></tr>";

    $sql = "SELECT library_assign.*, library.title FROM library_assign
            INNER JOIN library ON (library_assign.bid=library.bid)
            WHERE library_assign.mid='".(int) $mid."' AND closed='0'";
    $res = sql($sql);
    while($row = sqlget($res)) {
        $out .= "<tr><td>{$row['title']}</td><td>".(int) $row['number']."</td><td>".($row['type'] ? _('резерв') : mydate($row['start']))."</td><td>".($row['type'] ? _('резерв') : mydate($row['stop']))."</td></tr>";
    }
    $ret .= $out;
    $ret .= "</table>";

    if (empty($out)) $ret = '';

    return $ret;
}

?>