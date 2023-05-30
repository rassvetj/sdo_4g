<?php
define("DIR_SKINS", "/template/smarty/skins");
define("URL_SKINS", "{$protocol}://" . $HTTP_HOST . DIR_SKINS);
define("URL_ROOT", "{$protocol}://" . $HTTP_HOST);

class View {

	var $smarty;
	var $theme;
	var $skin;
	var $skin_dir;
	var $skin_url;
	var $template;
	var $title;
	var $children;
	var $objects;
	var $content_common;
	var $content;
    var $curTab = 'default';
    var $disable_copy_material = false;
    var $disable_breadcrumbs = false;
    var $placeholders = array();

	function View(){
		$this->smarty = new Smarty_els();
		$this->children = array();
		$this->objects = array();
		$this->template = strtolower(get_class($this));
		$skin = ($value = CRegistry::get('config')->skin) ? $value : 'default';
		$this->setSkin($skin);
		$this->root_url = "{$GLOBALS['protocol']}://" . $_SERVER['HTTP_HOST'];

 	}

 	function setSkin($value) {
		$this->skin = $this->theme = $value;
        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . DIR_SKINS . '/' . $this->skin)) {
            $this->skin = 'default';
        }

		$this->skin_dir = $_SERVER['DOCUMENT_ROOT'] . DIR_SKINS . '/' . $this->skin;
		$this->skin_url = "{$GLOBALS['protocol']}://" . $_SERVER['HTTP_HOST'] . DIR_SKINS . '/' . $this->skin;
 	}

	function display(){
		if (is_array($this->content)) $this->content = implode("<br /><br />", $this->content);
		$this->smarty->set_template_dir($this->skin_dir);

        if (($GLOBALS['s']['perm']==1) && DISABLE_COPY_MATERIAL)
            $this->disable_copy_material = true;

        ob_start();
		$this->smarty->assign_by_ref("this",$this);
		$this->smarty->display($this->template . ".tpl");
		$str = ob_get_clean();
		foreach ($this->placeholders as $name => $value) {
			$str = str_replace("<!--placeholder:{$name}-->", $value, $str);
		}	
		echo $str;	
	}

	function fetch(){
		$this->smarty->set_template_dir($this->skin_dir);

        if (($GLOBALS['s']['perm']==1) && DISABLE_COPY_MATERIAL)
            $this->disable_copy_material = true;

		$this->smarty->assign_by_ref("this",$this);
		return $this->smarty->fetch($this->template . ".tpl");
	}

	function enabled(){
		return count($this->children);
	}

	function displayChild($name){
		if (isset($this->children[$name])) {
			$this->children[$name]->display($this->template);
		}
	}

	function displayChildArray($name, $delimiter = ''){
		$buffer = array();
		if (count($this->children[$name])) {
			for ($i = 0; $i < count($this->children[$name]); $i++) {
				$buffer[] = $this->children[$name][$i]->fetch($this->template);
			}
		}
		echo implode($delimiter, $buffer);
	}

	function setTitle($title){
		$this->title = $title;
	}

	function _sortChildren($name){
		if (is_array($this->children[$name]) && count($this->children[$name])) {
			$arr = &$this->children[$name];
//			bundle.)
			for ($i = count($arr)-1; $i >= 0; $i--) {
				for ($j = 0; $j < $i; $j++) {
					if ($arr[$j]->order > $arr[$j+1]->order) {
						$tmp = $arr[$j];
						$arr[$j] = $arr[$j+1];
						$arr[$j+1] = $tmp;
					}
				}
			}
		}

	}

	function parseVariables($variable_attributes, $replacement){
		if (is_array($variable_attributes)){
			foreach ($variable_attributes as $attribute) {
				if (isset($this->$attribute)){
					for ($i = 1; $i <= count($replacement); $i++){
						$this->$attribute = str_replace(STR_VARIABLE . $i, $replacement[$i-1], $this->$attribute);
					}
				}
			}
		}
	}

	function parseWords(){
		global $words;
		$this->content = words_parse($this->content, $words);
		$this->content = str_replace(array('[PATH]', '[SESSID]'), array(URL_ROOT . '/', ''), $this->content);
	}

	function initialize($id = false){}

	function disableBreadCrumbs() {
	    $this->disable_breadcrumbs = true;
	}
	
	function addPlaceholder($name, $value)
	{
		$this->placeholders[$name] = $value;
	}
}

class DocumentCss extends DocumentAbstract {}
class DocumentMail extends DocumentAbstract {}
class DocumentPrint extends DocumentAbstract {}

class DocumentContent extends DocumentAbstract {
    function initialize(){
		$message = new Message();
		$message->initialize();
		$this->children = array('message' => &$message);
	}

    function initializeAfter(){

    }
}

class DocumentTab extends DocumentAbstract {
    function initialize(){
		$message = new Message();
		$message->initialize();
		$this->children = array('message' => &$message);
	}
}

class DocumentBlank extends DocumentAbstract {

	function initialize(){
		$message = new Message();
		$message->initialize();
		$this->children = array('message' => &$message);
	}
}

class DocumentFrame extends DocumentAbstract {

    var $enable_navigation = true;

	var $content_expanded;
	var $content_collapsed;
	var $return_path;

	function initialize(){

	    parent::initialize();

		$link_group = new LinkGroup();
		$link_group->initialize();

		$filter_group = new FilterGroup();
		$filter_group->initialize();

		$this->children = array('link_group' => &$link_group, 'filter_group' => &$filter_group);
	}
}

class DocumentFrameOffline extends View {

	var $return_path;

	function initialize(){
		$this->template = 'documentframe';
		$this->root_url = $this->skin_url = '../common';
        $this->skin_url .= '/template';

		$message = new Message();
		$message->initialize();

		$link_group = new LinkGroup();

		$filter_group = new FilterGroup();

		$this->children = array('link_group' => &$link_group, 'filter_group' => &$filter_group, 'message' => &$message, 'help' => &$help, 'menu_breadcrumbs' => &$menu_bc);
	}
}

class DocumentFrameMenu extends Document { }

class DocumentPopupOffline extends View {

	var $level;

	function initialize($level){
		$this->template = 'documentpopup';
		$this->level = $level;
		switch ($this->level) {
			case 3:
				$this->root_url = $this->skin_url = '../../common';
                $this->skin_url .= '/template';
				break;
			case 2:
				$this->root_url = $this->skin_url = '../common';
                $this->skin_url .= '/template';
				break;
			case 1:
				$this->root_url = $this->skin_url = './common';
				$this->skin_url .= '/template';
				break;
			default:
				break;
		}

		$menu_bc = new MenuBreadcrumbsOffline();
		$menu_bc->initialize($this->level);
		$help = new Help();
		$help->initialize_file(DIR_HELP . '/offline.inc.tpl');

		$filter_group = new FilterGroup();
        //$filter_group->initialize();

        $link_group = new LinkGroup();
        //$link_group->initialize();

        $tab_group = new TabGroup();
        //$tab_group->initialize();

		$message = new Message();
		$message->initialize();
//		$this->children = array('message' => &$message, 'help' => &$help, 'menu_breadcrumbs' => &$menu_bc);
        $this->children = array('message' => &$message, 'help' => &$help,  'menu_breadcrumbs' => &$menu_bc, 'filter_group' => &$filter_group, 'link_group' => &$link_group, 'tab_group' => &$tab_group);

	}
}

class DocumentPopup extends DocumentAbstract {
    var $enable_navigation = false;
    var $return_path;

	function initialize(){
        $filter_group = new FilterGroup();
		$filter_group->initialize();

        $link_group = new LinkGroup();
        $link_group->initialize();

        $tab_group = new TabGroup();
        $tab_group->initialize();

		$message = new Message();
		$message->initialize();
		$this->children = array('message' => &$message, 'filter_group' => &$filter_group, 'link_group' => &$link_group, 'tab_group' => &$tab_group);
	}

	function initializeAfter(){

		$this->setHeader();

		$this->title = APPLICATION_TITLE . DELIMITER_TITLE . $this->getPageTitle();

		//$menu_bc = new MenuBreadcrumbsPassive();
                //вызываем хлебные крошки в активном виде
                $menu_bc = new MenuBreadcrumbs();

                $menu_bc->initialize();

		$help = new Help();
		$help->initialize();

		$this->children = array_merge($this->children, array('help' => &$help, 'menu_breadcrumbs' => &$menu_bc));
	}

}

class DocumentAbstract extends View {

	var $page_id;
	var $title;
	var $header;
	var $encoding;
	var $logo_url;
	var $disable_search;

	function DocumentAbstract(){
	    $this->encoding = $GLOBALS['controller']->lang_controller->lang_current->encoding;
	    parent::View();
		$filename = COption::get_value('logo');
		$this->logo_url = (!empty($filename) && file_exists(OPTION_FILES_REPOSITORY_PATH . $filename)) ? OPTION_FILES_REPOSITORY_URL . $filename : $this->skin_url . "/images/logo.gif";
	}

	function setHeader(){
		if (($current = &$GLOBALS['controller']->menu->group_selected->element_selected)){
//			РїРѕРґ unix РјРѕР¶РµС‚ РЅРµ СЂР°Р±РѕС‚Р°С‚СЊ. todo
			if (empty($this->header)){
            	$this->header = (strlen($current->title_full)) ? $current->title_full : $current->title;
			}
			//$this->header = ucfirst($this->header);
		}
	}

	function getPageTitle(){
	    if (!empty($this->header) && is_object($this->header) && method_exists($this->header, 'getTitle')) {
	        return $this->header->getTitle();
	    } else {
	        return strip_tags($this->header);
	    }
	}
	
}
class Document extends DocumentAbstract {

	var $header;

	function initialize(){

	    parent::initialize();

		$link_group = new LinkGroup();
		$link_group->initialize();

		$filter_group = new FilterGroup();
		$filter_group->initialize();

		$tab_group = new TabGroup();
		$tab_group->initialize();

		$news = new News();
		$news->initialize();

		$message = new Message();
		$message->initialize();

		$this->children = array('link_group' => &$link_group, 'tab_group' => &$tab_group, 'filter_group' => &$filter_group, 'news' => &$news, 'message' => &$message);
	}

	function initializeAfter(){

		$this->setHeader();

		$this->title = APPLICATION_TITLE . DELIMITER_TITLE . $this->getPageTitle();

		$this->_sortChildren('tabs');

		$lang_chooser = new LangChooser();
		$lang_chooser->initialize();
		$this->children['lang_chooser'] = &$lang_chooser;

		$help = new Help();
		$help->initialize();
		$this->children['help'] = &$help;

		$user_home = new UserHome();
		$user_home->initialize();
		$this->children['user_home'] = &$user_home;

		$menu_main = new MenuMain();
		$menu_main->initialize();
		$this->children['menu_main'] = &$menu_main;

		$menu_bc = new MenuBreadcrumbs();
		$menu_bc->initialize();
		$this->children['menu_breadcrumbs'] = &$menu_bc;
		
	}
}

class TabHelp extends Help {

    var $id;

    function initialize($id) {
        $this->id = $id;
        $this->content = $this->getHelp();
    }

    function getHelp() {
        return $GLOBALS['controller']->getHelp($this->id); //РЅРµРїРѕРЅСЏС‚РЅРѕ Р·Р°С‡РµРј РЅСѓР¶РµРЅ РєРѕРґ РЅРёР¶Рµ, РµСЃР»Рё РІ РєРѕРЅС‚СЂРѕР»Р»РµСЂРµ РµСЃС‚СЊ С‚РѕС‡РЅРѕ С‚Р°РєР°СЏ Р¶Рµ С„СѓРЅРєС†РёСЏ
        /*

        $subject = $this->id;

        $file = DIR_HELP . "/{$subject}-{$GLOBALS['controller']->user->profile_current->basic_name}.inc.tpl";
        if (!file_exists($file)) {
            $file = DIR_HELP . "/{$subject}.inc.tpl";
            if (!file_exists($file)) {
                return false;
                $file = DIR_HELP."/not_available.inc.tpl";
            }
        }

        $smarty = new Smarty_els();
        $str = $smarty->fetch($file);

        return $str;
        */
    }

    function enabled() {
        return strlen($this->content);
    }
}

class Help extends View {

    var $enabled = false;
    var $opened = false;

	function initialize(){
		$this->content = &$GLOBALS['controller']->getHelp();

		if (isset($GLOBALS['s']['user']['helpAlwaysShow']) && $GLOBALS['s']['user']['helpAlwaysShow']) $this->opened = true;

		$tabs = &$GLOBALS['controller']->view_root->children['tab_group'];
        if (is_array($tabs->children) && count($tabs->children)) {
            foreach($tabs->children as $tab) {
                $tabHelp = new TabHelp();
                $tabHelp->initialize($tab->id);
                if ($tabHelp->enabled()) $this->enabled = true;
                $this->children['tab_help'][] = $tabHelp;
            }
        }
	}

	function initialize_file($file){
		if ($f = fopen($file, 'r')){
			$this->content = fread($f, filesize($file));
			fclose($f);
		}
	}

	function enabled(){
		return (strlen($this->content) || $this->enabled);
	}
}

class LangChooser extends View {

	function initialize(){
		$this->objects['lang_controller'] = &$GLOBALS['controller']->lang_controller;
	}

	function enabled(){
		return (count($this->objects['lang_controller']->langs) > 1);
	}
}

class UserHome extends View {

	function initialize(){
		$this->objects['user'] = &$GLOBALS['controller']->user;

		if (isset($_SESSION['default']['userRestore'])) $this->objects['userRestore'] = &$_SESSION['default']['userRestore'];

        $lang_chooser = new LangChooser();
        $lang_chooser->initialize();
        $this->objects['lang_chooser'] = &$lang_chooser;

	}
}

class TabGroup extends View{}
class LinkGroup extends View{}

class Tab extends View {

	var $id;
	var $name;
	var $name_full;
	var $content;
	var $order;
	var $href = '';
	var $current = false;

	function initialize($id){
		if (is_array($arr = ActionsUtil::getTab($id))) {
			$this->id = $id;

			list($this->name, $this->name_full, $this->order, $this->href) = $arr;
		}
	}

	function parse_variables($replacement){
		$variable_attributes = array('name_full');
		$this->_parse_variables($variable_attributes, $replacement);
	}
}

define("STYLE_SUCCESS", 'success');
define("STYLE_ERROR", 'error');
define("STYLE_PROMPT", 'prompt');
define("DIR_MESSAGE_ICONS", URL_ROOT . "/images/message");

class Message extends View {

	var $url;
	var $opener_url;
	var $onclick;
	var $cancel_url;
	var $selenium_feedback;
	var $target;

	function enabled(){
		return strlen($this->content);
	}
}

class Link extends View {
	var $title;
	var $url;
	var $target;
	var $alt;
	var $params;
	var $order;
    var $hide;
    var $confirm;
    var $anchor;

	function initialize($id){
		if (is_array($arr = ActionsUtil::getLink($id))) {
			$this->id = $id;
			list($this->title, $this->url, $this->target, $this->alt, $this->params, $this->order, $this->hide, $this->confirm, $this->anchor) = $arr;
		}
	}

	function parse_variables($replacement){
		$variable_attributes = array('url', 'params');
		$this->parseVariables($variable_attributes, $replacement);
	}

	function setConfirm($confirm) {
	    $this->confirm = $confirm;
	}

}

class FilterGroup extends View {

	var $scope;
	var $opened;
	var $checked;
	var $action;
    var $target;

	function initialize(){
		$this->children = array();
		$this->scope = $GLOBALS['controller']->page_id;
		$this->target = $_SERVER['PHP_SELF'];
	}

	function addFilter(&$filter){
		$this->children[] = $filter;
		if ($filter->required || ($filter->selected && $filter->selected != $filter->default_value)){
			$this->opened = true;
		}
	}
}

class Filter {

	var $title;
	var $name;
	var $options;
	var $selected;
	var $required;
    var $default_value;
    var $enable_first;
    var $extra_html;
    var $prepend_html;
    var $javascript;

	function setOptions($array, $selected = false){
		if (isset($array[0])){
			unset($array[0]);
		}
		if (isset($array[-1])){
			unset($array[-1]);
		}
		$first = ($this->required) ? STR_OPTIONS_SELECT : STR_OPTIONS_ALL;
		if ($this->enable_first)
        $this->options[$this->default_value] = $first;
        if (is_array($array)) {
			foreach ($array as $key => $value) {
				$this->options[$key] = (strlen($value) > 60) ? substr($value, 0, 60) . "..." : $value;
			}
        }
		if (!isset($array['-1'])) {
            //$this->selected = (in_array($selected, array('0', '-1'))) ? 0 : $selected;
            $this->selected = (in_array($selected, array('0', '-1'))) ? $this->default_value : $selected;
		} else {
        $this->selected = (in_array($selected, array('0'))) ? 0 : $selected;
		}
	}
}

class Menu extends View {

	var $groups;
	var $group_selected;

	function initialize(){
		$this->objects['user'] = &$GLOBALS['controller']->user;
		$this->groups = array();
		$this->_setGroups();
		$this->_orderGroupsEx();
		$this->_orderGroups();
	}

	function _setGroups(){
		if (isset($this->objects['user']) && is_array($actions = &$this->objects['user']->profile_current->actions)) {
			foreach ($actions as $action) {
				switch ($action->type){
//					case 'tab':
//					case 'link':
//						if ($page_id = ActionsUtil::getMenuElementPageId($action->id)){
//							$action->id = $page_id;
//							$action->type = 'page';
//						}
                    case 'custom':
                        $group_id = substr($action->id,0,3);
                        switch($group_id) {
                            case 'm13':
						        $group = new MenuGroup();
						        $group->initialize($group_id);
						        $group->addChildren();
                                if (empty($group->hide) || ($group->hide == 'false'))
						        $this->groups[$group->id] = $group;
                            break;
                        }
                    break;
					case 'page':
						$page = new MenuElement();
						$page->initialize($action->id);
						$page->setGroup();

						/*if (!isset($this->groups[$page->group_id])) {
							$group = new MenuGroup();
							$group->initialize($page->group_id);
                            if (empty($group->hide) || ($group->hide == 'false'))
							$this->groups[$group->id] = $group;
						}
						if (!isset($group) || empty($group->hide) || ($group->hide == 'false')) {
                            $this->groups[$page->group_id]->addChild($page);
                        }*/
						break;
                    case 'group':
						$group = new MenuGroup();
						$group->initialize($action->id);
						$group->addChildren();
                        if (empty($group->hide) || ($group->hide == 'false'))
                        if ($group->id == 'm99') {
                            if (!is_array($group->elements) || !count($group->elements)) continue;
                        }

                        if ($group->id == 'm05') {
                            $perm = $GLOBALS['s']['perm'];
                            if (!in_array($perm, array($GLOBALS['profiles_basic_ids'][PROFILE_DEAN],$GLOBALS['profiles_basic_ids'][PROFILE_TUTOR], $GLOBALS['profiles_basic_ids'][PROFILE_MANAGER], $GLOBALS['profiles_basic_ids'][PROFILE_ADMIN])) && !hasReports($GLOBALS['s']['perm'])) {
                                continue;
                            }
                        }
						$this->groups[] = $group;
						break;
					default:
						break;
				}
			}
		}
	}

	function _orderGroupsEx() {
        ActionsUtil::order($this->groups);
        return true;
        if (isset($this->objects['user']) && is_array($actions = &$this->objects['user']->profile_current->actions) &&
        is_array($this->objects['user']->profile_current->extended) && is_array($this->groups)) {
	        if ($GLOBALS['domxml_object']){
	            if (is_array($groups = $GLOBALS['domxml_object']->get_elements_by_tagname("group"))) {
	            	$newOrder = array();
	                foreach ($groups as $group) {
	                	if ($id = $group->get_attribute('id')) {
		                	if (isset($this->groups[$id])) {
		                		$newOrder[$id] = $this->groups[$id];
		                	}
	                	}
	                }
                    $this->groups = $newOrder;
	            }
	        }
        }
	}

	function _orderGroups(){
		ActionsUtil::order($this->groups);
	}
}

class MenuGroup {

	var $id;
	var $title;
	var $icon;
	var $elements;
	var $element_selected;
	var $selected;
	var $order;
    var $hide;

	function initialize($id){
		$this->id = $id;
		$this->order = 1000;
		list($this->title, $this->icon, $this->order, $this->hide) = ActionsUtil::getMenuGroup($this->id);
	}

	function addChildren(){
		$this->elements = ActionsUtil::getMenuElements($this->id);
	}

	function addChild($child){
		$child->appendMenuId();
		$this->elements[$child->id] = $child;
	}

}

class MenuSubGroup extends View {
    var $elements;

	function addChild($child) {
		$child->appendMenuId();
		$this->elements[$child->id] = $child;
	}

    function getChildren()
    {
        return $this->elements;
    }
}

class MenuElement extends View {

	var $id;
	var $title;
	var $title_full;
	var $url;
	var $alt;
	var $group_id;
	var $selected;
	var $custom;
	var $target;

	function initialize($id){
		if (is_array($arr = ActionsUtil::getMenuElement($id))){
			$this->id = $id;
			list($this->title, $this->url, $this->alt, $this->title_full, $this->target) = $arr;
		}
	}

	function initializeCustom($arr){
		if (is_array($arr)){
			list($custom_id,$this->title, $this->url, $this->alt, $this->title_full) = $arr;
			$this->id = $custom_id;
			$this->custom = true;
		}
	}

	function setGroup($force = false){
		if ($force) {
			$this->group_id = $force;
		} else {
			$this->group_id = ActionsUtil::getMenuElementGroupId($this->id);
		}
	}

	function appendMenuId(){
		$this->url .= (strpos($this->url, "?") !== false) ? "&" : "?";
		$this->url .= "page_id={$this->id}";
	}

    function getChildren()
    {
        return false;
    }

}

class MenuBreadcrumbsOffline extends View {

	var $level;

	function initialize($level){
		$this->level = $level;
	}

	function enabled(){
		return ($this->level > 1);
	}

	function set_titles($link_titles, $course_id = false){
		switch ($this->level) {
			case 3:
				$this->links[0] = array('url' => '../../index.html', 'title' => $link_titles[0]);
				$this->links[1] = array('url' => "../index_{$course_id}.html", 'title' => $link_titles[1]);
				$this->links[2] = array('url' => '#', 'title' => $link_titles[2]);
				break;
			case 2:
				$this->links[0] = array('url' => '../index.html', 'title' => $link_titles[0]);
				$this->links[1] = array('url' => "#", 'title' => $link_titles[1]);
				break;
			default:
				break;
		}
	}
}

class MenuAbstract extends View {

	function initialize(){
		$this->objects['menu'] = &$GLOBALS['controller']->menu;
	}

	function enabled(){
		return count($GLOBALS['controller']->menu->groups);
	}


}

class MenuMain extends MenuAbstract {}

class MenuBreadcrumbs extends MenuAbstract {

	function enabled(){
		return (!empty($this->objects['menu']->objects['user']->profile_current) && !empty($GLOBALS['controller']->page_id) && ($GLOBALS['controller']->page_id !== PAGE_INDEX) && ($this->objects['menu']->objects['user']->profile_current->name!==PROFILE_GUEST) && (!$GLOBALS['controller']->view_root->disable_breadcrumbs));
	}
}

class MenuBreadcrumbsPassive extends MenuBreadcrumbs { }

class ContentAbstract extends View {

	var $id;
	var $content;

	function initialize($id = false){
		$this->id = $id;
	}
}

class Content extends ContentAbstract {}
class ContentCollapsed extends ContentAbstract {}
class ContentExpanded extends ContentAbstract {}
class Trash extends ContentAbstract {}

class News extends ContentAbstract  {

	function enabled(){
		return (!$GLOBALS['controller']->user->isAuthorized() && strlen($this->content));
	}
}

class Indicator extends View {

    var $template;
    var $steps = array();
    var $inactive_steps = array();
    var $step_current;
    var $comments;

    function Indicator($steps, $sequence = true){
        parent::View();
        if (is_array($steps)) $this->steps = $steps;
        ksort($this->steps);
    }

    function setTemplate($template = 'full'){
        $this->template = $template;
    }

    function setCurrent($step_current){
        if (array_key_exists($step_current, $this->steps)) $this->step_current = $step_current;
    }

    function addComments($comments){
        if (is_array($comments)) $this->comments = $comments;
    }

    function fetch(){

        $return = array();
        $steps = $this->getSteps();
	        switch ($this->template) {
	         	case 'full':
         		return "<table class='indicator indicator-full' cellpadding=0 cellspacing=0 border=1>{$steps}</table>";
	         		break;
	         	case 'brief':
         		return "<table class='indicator indicator-brief' cellpadding=0 cellspacing=0><tr><td>{$steps}</td></tr></table>";
	         		break;
        }
    }

    function _getFirstStep(){
		return array_shift(array_keys($this->steps));
    }

    function _isLastStep($step){
		return ($step == array_pop(array_keys($this->steps)));
    }

    function _getNextStep($step){
    	$keys = array_keys($this->steps);
    	$i = array_search($step, $keys);
		if (isset($keys[$i + 1])) {
			return $keys[$i + 1];
		} else {
			return false;
		}
    }
}

class IndicatorSequence extends Indicator {

	function IndicatorSequence($steps){
		parent::Indicator($steps);
	}

	function getSteps(){
		$return = array();
        $step_shown_as_current = $this->_getNextStep($this->step_current);
        for($i = $this->_getFirstStep(); $i; $i = $this->_getNextStep($i)) {
            $tmp = ($step_shown_as_current && ($i > $step_shown_as_current)) ? 'next' : 'prev';
            $tmp = $i == $step_shown_as_current ? 'current' : $tmp;
            $tmp = (in_array($i, $this->inactive_steps)) ? 'inactive' : $tmp;
            $this->smarty->assign("step_comment", $this->comments[$i]);
            $this->smarty->assign("step_number", ++$cnt);
            $this->smarty->assign("step_title", $this->steps[$i]);
            $this->smarty->assign("step_arrow", $this->_isLastStep($i) ? "" : "style=\"background: url(images/arrow.gif) bottom center no-repeat; padding-top: 0;\"");
            $this->smarty->assign("step_type", $tmp);
            $return[] = $this->smarty->fetch("indicator-{$this->template}.tpl");
        }
        if (count($return)) {
			return implode($return);
        } else {
        	return "";
        }
	}

	function setInactiveSteps($inactive_steps){
		$this->inactive_steps = $inactive_steps;
	}

}

class IndicatorNoSequence extends Indicator {

	var $steps_prev = array();

	function IndicatorNoSequence($steps, $steps_prev){
		parent::Indicator($steps);
		$this->steps_prev = $steps_prev;
	}

	function getSteps(){
		$return = array();
        for($i = $this->_getFirstStep(); $i; $i = $this->_getNextStep($i)) {
            $tmp = in_array($i, array_keys($this->steps_prev)) ? 'prev' : 'next';
            $this->smarty->assign("step_comment", $this->comments[$i]);
            $this->smarty->assign("step_number", ++$cnt);
            $this->smarty->assign("step_title", $this->steps[$i]);
            $this->smarty->assign("step_arrow", $this->_isLastStep($i) ? "" : "style=\"background: url(images/spacer.gif) bottom center no-repeat; padding-top: 0;\"");
            $this->smarty->assign("step_type", $tmp);
            $return[] = $this->smarty->fetch("indicator-{$this->template}.tpl");
        }
        if (count($return)) {
			return implode($return);
        } else {
        	return '';
        }
	}
}

?>