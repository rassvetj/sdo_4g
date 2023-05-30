<?
class Controller {

    var $enabled;
    var $view_root;
    var $lang_controller;
    var $persistent_vars;
    var $user;
    var $menu;
    var $page_id;
    var $link_id;
    var $buffer;
    var $help_section;

    function isAjaxRequest()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_REQUEST['ajax']));
    }

    function sendRefresh($url)
    {
        while(ob_get_contents()) ob_clean();
        header('HTTP/1.1 302 Found');
        //header('X-Requested-With: XMLHttpRequest');
        header('Location: '.$url.'&ajax=true');
        exit();
    }
    
    /**
     * инициализирует все что нужно инициализировать до выполнения тела страницы.
     * в случае enabled=false контроллер ничего не делает
     *
     * @param bool  $enabled
     */
    function initialize($enabled = CONTROLLER_OFF){
        $this->enabled = $enabled;
        if ($this->enabled) {
            ob_start();
            $this->buffer = array();

            $this->persistent_vars = new PersistentVars();
            $this->persistent_vars->initialize();

            $this->lang_controller = new LangController();
            $this->lang_controller->initialize();

            $this->_getMenuSelected();
            $this->_setViewDefault();

            $this->_applyFilter();

        } else define('DIR_LANG','');
    }

    static function finish_gettext_debug_mode($output = '') {
        if (defined('DEBUG_GETTEXT_ENABLE') && DEBUG_GETTEXT_ENABLE) {
            if (defined('DEBUG_GETTEXT_FILE') && strlen(DEBUG_GETTEXT_FILE)) {
                if ($fp = fopen(DEBUG_GETTEXT_FILE,'a+')) {
                    if (rewind($fp)) {
                        while(!feof($fp)) {
                            $line = trim(fgets($fp));
                            if (isset($GLOBALS['gettext'][$line])) {
                                unset($GLOBALS['gettext'][$line]);
                            }
                        }
                        if (is_array($GLOBALS['gettext']) && count($GLOBALS['gettext'])) {
                            foreach($GLOBALS['gettext'] as $s => $true) {
                                if (strpos($output, $s) !== false) {
                                    fputs($fp, $s."\r\n");
                                }
                            }
                        }
                    }
                }
                fclose($fp);
            }
        }
    }

    /**
     * завершает работу контроллера.
     * делает все, что нужно сделать по
     * завершению выполнения тела страницы
     *
     */
    function terminate(){
        ob_clean();
        ob_start();
        if ($this->enabled && $this->view_root) {
            switch (strtolower(get_class($this->view_root))) {
                case 'document':
                case 'documentframemenu':
                case 'documentcontent':
                    $this->_terminate_document();
                    break;
                case 'documentpopup':
                    $this->_terminate_popup();
                    break;
                case 'documentmail':
                    return $this->_terminate_mail();
                case 'documenttab':
                case 'documentframe':
                case 'documentprint':
                case 'documentblank':
                case 'documentcss':
                    $this->_terminate_simple();
                    break;
                default:
                    $this->_clean();
                    break;
            }
        }
        if (defined('DEBUG_GETTEXT_ENABLE') && DEBUG_GETTEXT_ENABLE) {
            $output = ob_get_flush();
            $this->finish_gettext_debug_mode($output);
        }

    }

    function _terminate_document(){
        $this->_setLinksDefault();
        $this->_setMenu();
        $this->_clean();
        $this->_setMenuSelected();
        $this->_saveFilter();
        $this->view_root->initializeAfter();
        $this->view_root->display();
        $this->persistent_vars->terminate();
    }

    function _terminate_popup(){
        $this->_setMenu();
        $this->_clean();
        $this->_setMenuSelected();
        $this->_saveFilter();
        $this->view_root->initializeAfter();
        $this->view_root->display();
        $this->persistent_vars->terminate();
    }

    function _terminate_mail(){
        $this->_clean();
        return $this->view_root->fetch();
    }

    function _terminate_simple(){
        $this->_clean();
        if (isset($_SESSION['s']['flash_messages'])) {
            foreach($_SESSION['s']['flash_messages'] as $message) {
                $this->setMessage($message);
            }
            //$this->view_root->flash_messages = $_SESSION['s']['flash_messages'];
            unset($_SESSION['s']['flash_messages']);
        }
        $this->view_root->display();
    }

    function terminate_flush(){
        while(strlen(ob_get_contents())){
            ob_end_flush();
        }
    }

    function _applyFilter(){
        $ch_condition = false;
        if (!isset($_REQUEST['hid_filter'])) {
            $remember_filter = $this->persistent_vars->get('remember_filter');
            if (is_array($remember_filter)){
                foreach ($remember_filter as $scope => $filter_group_arr) {
                    if (strpos($this->page_id, $scope) !== false){
                        foreach($filter_group_arr as $key => $val){
                            $GLOBALS[$key] = $val;
                            $_GET[$key] = $val;
                            $_POST[$key] = $_POST[$key] ? $_POST[$key] : $val;
                            $_REQUEST[$key] = $val;
                            $key = strtoupper($key);
                            $GLOBALS[$key] = $val;
                            $_GET[$key] = $val;
                            $_POST[$key] = $val;
                            $_REQUEST[$key] = $val;
                        }
                        $ch_condition = true;
                    }
                }
            }
        } else {
            $ch_condition = !empty($_REQUEST['ch_remember_filter']);
        }
        $this->view_root->children['filter_group']->checked = ($ch_condition) ? 'checked="checked"' : '';
    }

    function _clearFilter($name = false, $scope = false){
        $scope = $scope?$scope:$this->page_id;

        $remember_filter = $this->persistent_vars->get('remember_filter');

        if (is_array($remember_filter)){
            foreach ($remember_filter as $crntScope => $filter_group_arr) {
                if (strpos($scope, $crntScope) !== false){
                    if ($name) {
                        foreach($filter_group_arr as $key => $val){
                            if ($key == $name) {
                                unset($filter_group_arr[$key]);
                            }
                        }
                        $remember_filter[$crntScope] = $filter_group_arr;
                    } else {
                        unset($remember_filter[$crntScope]);
                    }
                }
            }
        }

        $this->persistent_vars->set('remember_filter', $remember_filter, true);

    }

    function _saveFilter(){
        $remember_filter = $this->persistent_vars->get('remember_filter');
        $scope = $_REQUEST['hid_scope'];
        if (isset($_REQUEST['hid_filter']) && count($this->view_root->children['filter_group']->children)){
            if (empty($_REQUEST['ch_remember_filter'])) {
                unset($remember_filter[$scope]);
            } else {
                foreach ($this->view_root->children['filter_group']->children as $filter) {
                    if ($filter->selected != '0') {
                        if ($filter->options == 'div') {
                            if ($filter->required != '0') {
                                if (isset($_GET[$filter->name])) {
                                    $remember_filter[$scope][$filter->name] = $_GET[$filter->name];
                                } else {
                                    $remember_filter[$scope][$filter->name] = $filter->required;
                                }
                            }
                        } else {
                            $remember_filter[$scope][$filter->name] = $filter->selected;
                        }
                    } else {
                        if (is_array($remember_filter[$scope][$filter->name])) unset($remember_filter[$scope][$filter->name]);
                        if (!count($remember_filter[$scope])) {
                            unset($remember_filter[$scope]);
                        }
                    }
                }
            }
            $this->persistent_vars->set('remember_filter', $remember_filter);
        }

        /**
         * Если обязательный фильтр содержит один элемент, то происходит выбор первого значения элемента фильтра и рефреш
         */

        if ((count($this->view_root->children['filter_group']->children)==1)
            && (count($this->view_root->children['filter_group']->children[0]->options) == 2)
            && $this->view_root->children['filter_group']->children[0]->required
            && $this->view_root->children['filter_group']->children[0]->enable_first
            && ($this->view_root->children['filter_group']->children[0]->selected == '')) {

            $keys = array_keys($this->view_root->children['filter_group']->children[0]->options);
            $this->view_root->children['filter_group']->children[0]->selected = $keys[1];
            $this->view_root->children['filter_group']->action = FILTER_SELECT_FIRST_REFRESH_SELF;
            $this->view_root->children['filter_group']->checked = 'checked="checked"';
        }

    }

    /**
     * задает область применимости фильтра.
     * например, когда фильтр на странице
     * нужно расширить до всего MenuGroup
     *
     * @param string    $id
     */
    function setFilterScope($id){
            if (in_array(ActionsUtil::getType($id), array('group'))) {
                $this->view_root->children['filter_group']->scope = $id;
            }
    }

    function _setViewDefault(){
        if (!isset($this->view_root)) {
            if ($this->view_root = new Document()) {
                $this->view_root->initialize();
            }
        }
    }

    function setHeader($title) {
        // отныне title это объект HM_View_PageHeader 
        $this->view_root->header = $title;
    }

    function getHeader() {
        return $this->view_root->header;
    }

    function setSubHeader($title) {
        $this->view_root->subheader = ucfirst($title);
    }

    /**
    * @param int $curTab первый таб - 1 (а не 0)
    */
    function setCurTab($id) {
        $this->view_root->children['tab_group']->current = $id;
        if (isset($this->view_root->children['tab_group']) && is_array($this->view_root->children['tab_group']->children)) {
            foreach($this->view_root->children['tab_group']->children as $key => $tab) {
                if ($tab->id == $id){
                    $this->view_root->children['tab_group']->children[$key]->current = true;
                    return;
                }
            }
        }
        //$this->view_root->curTab = (int) $curTab;
    }

    /**
     * переопределяет тип документа.
     * по умолчанию - Document
     * если, например, DocumentPopup -
     * необходимо вызывать этот метод.
     *
     * @param unknown_type $class
     */
    function setView($class, $bodyClass = null) {
        if (!isset($this->view_root)) {
            unset($this->view_root);
        }
        if ($this->view_root = new $class()) {
            $this->view_root->initialize();
            $this->_applyFilter();
            if ($bodyClass) {
                $this->view_root->bodyClass = $bodyClass;
            }
        }
    }

    function setContent($content){
        $this->view_root->content = $content;
    }

    function setContentCommon($str){
        $this->view_root->content_common = $str;
    }

    function setHelpSection($section) {
        $this->help_section = $section;
    }

    /**
     * формирует контекстный help по данной странице
     * по следующему алгоритму:
     * ID - page_id
     * HS - help_section
     * ROLE - текущая роль пользователя
     * PID - page_id без двух последних цифр (id родителя)
     *
     * 1 при наличии help_section
     *   текст помощи берётся из файла с именем ID-HS-ROLE.inc.tpl либо ID-HS.ink.tpl
     *   если вышеуказанных файлов нет, то текст помощи берётся из HS
     *
     * 2 при отсутствии help_section
     *   текст помощи берётся из первого существующего файла
     *   ID-ROLE.inc.tpl
     *   ID.inc.tpl
     *   PID-ROLE.inc.tpl
     *   PID.inc.tpl
     *
     * @return string
     */
    function getHelp($helpId = false){
        if (!$helpId) {
        if ($this->page_id) {
            // элементы типа custom имеют общий help
            $subject = (!empty($this->menu->group_selected->element_selected) && $this->menu->group_selected->element_selected->custom) ? $this->menu->group_selected->id : $this->page_id;
        } else {
            //$subject = PAGE_INDEX;
        }

            /*
        if (($this->page_id != 'm0101') && $this->link_id) {
            $subject = $this->link_id;
        }
            */
        }else {
            $subject = $helpId;
        }

        if (empty($subject)) {
            $subject = PAGE_INDEX;
        }

        if (strlen($this->help_section)<255 && !empty($this->help_section) &&
            (file_exists(DIR_HELP."/{$subject}-{$this->help_section}-{$this->user->profile_current->basic_name}.inc.tpl") ||
            file_exists(DIR_HELP."/{$subject}-{$this->help_section}.inc.tpl"))) {
                $subject = $subject.'-'.$this->help_section;
            }

        $file = DIR_HELP . "/{$subject}-{$this->user->profile_current->basic_name}.inc.tpl";
        if (!file_exists($file)) {
            $file = DIR_HELP . "/{$subject}.inc.tpl";
            if (!file_exists($file)) {
                if ($this->help_section){
                    //динамически формируемая помощь
                    $str = $this->help_section;
                }elseif(strlen($subject)>=5) {
                //проверим помощь родителя
                    $file = DIR_HELP . "/".substr($subject,0,strlen($subject)-2)."-{$this->user->profile_current->basic_name}.inc.tpl";
                    if (!file_exists($file)) {
                        if (!file_exists($file = DIR_HELP . "/".substr($subject,0,strlen($subject)-2).".inc.tpl")) {
                            return false;
                        }
                    }
                }else{
                    return false;
                }
                //$file = DIR_HELP."/not_available.inc.tpl";
            }

        }

        //управление поведением помощи
        if ($this->help_section && strlen($this->help_section)<3) {
            switch ((int) $this->help_section) {
                case -1:
                    //принудительное отключение помощи
                    return false;
                    break;
            }
        }

        if (!$str){
            $smarty = new Smarty_els();
            $smarty->assign('sitepath', $GLOBALS['sitepath']);
            $str = $smarty->fetch($file);
        }

        $footer = DIR_HELP.'/footer.inc.tpl';
        if (file_exists($footer)) {
            $smarty = new Smarty_els();
            $smarty->assign('sitepath', $GLOBALS['sitepath']);
            $str .= $smarty->fetch($footer);
        }

/*      if (@!$f = fopen($file, 'r')) {
            $file = DIR_HELP . "/{$subject}.inc.tpl";
            if (@!$f = fopen($file, 'r')) {
                $file = DIR_HELP . "/not_available.inc.tpl";
                @$f = fopen($file, 'r');
            }
        }
        $str = fread($f, filesize($file));
        fclose($f);
*/
        return $str;
    }

    /**
     * Стартует захват контент из переменной.
     * когда много контента копится в одной переменной
     * и нужно захватить только часть его по ходу присвоения.
     *
     * @param unknown_type $id
     * @param unknown_type $var_name
     * @param unknown_type $var
     */
    function captureFromVar($id, $var_name, &$var){
        if ($this->enabled){
            $buf = new BufferVar();
            $buf->initialize($id);
            $buf->start($var_name, $var);
            array_push($this->buffer, &$buf);
        }
    }

    /**
     * Стартует захват контент из буфера
     *
     * @param int   $id
     */
    function captureFromOb($id){
        if ($this->enabled){
            $buf = new BufferOb();
            $buf->initialize($id);
            $buf->start();
            array_push($this->buffer, &$buf);
        }
    }

    /**
     * Контент целиком берется из из переменной.
     * не требует вызова captureStop()
     *
     * @param int       $id
     * @param string    $id
     * @param int       $id
     */
    function captureFromReturn($id, $value, $variables = false){
        if ($this->enabled){
            $buf = new BufferReturn();
            $buf->initialize($id);
            $buf->start($value, $variables);
            array_push($this->buffer, &$buf);
            $this->captureStop($buf->object->id);
        }
    }

    /**
     * Стопит захват контента, запущенный с помощью любого из CaptureFrom..
     * $id - что именно захватываем (tab либо content).
     * может принимать значения: 'm0203',
     * CONTENT - когда на странице нет табов,
     * TRASH - когда нужно просто подавит вывод какого-то куска,
     * 'trash_blabla_001' - когда надо захватить несколько trash'ей
     * должен следовать за открывающим методом с тем же id
     * возможна вложенность
     *
     * @param unknown_type $id
     */
    function captureStop($id){
        if ($this->enabled){
            if ($buf = &array_pop($this->buffer)){
                if (isset($buf->object->id) && ($buf->object->id == $id)) {
                    $buf->stop();
                    switch (strtolower(get_class($buf->object))) {
                        case 'tab':
                            if ($this->checkPermission($id)){
                                $this->view_root->children['tab_group']->children[] = @$buf->object;
                            }
                            break;
                        case CONTENT:
                            if (strlen($buf->object->content)) {
                                if (is_string($this->view_root->content)) {
                                    $this->view_root->content = array($this->view_root->content);
                                }
                                $this->view_root->content[] = @$buf->object->content;
                            }
                            break;
                        case CONTENT_COLLAPSED:
                            if (strlen($buf->object->content) && (in_array(strtolower(get_class($this->view_root)), array('documentframe')))) {
                                $this->view_root->content_collapsed = @$buf->object->content;
                            }
                            break;
                        case CONTENT_EXPANDED:
                            if (strlen($buf->object->content) && (in_array(strtolower(get_class($this->view_root)), array('documentframe')))) {
                                $this->view_root->content_expanded = @$buf->object->content;
                            }
                            break;
                        case NEWS:
                            $this->view_root->children['news']->content = @$buf->object->content;
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    function _setLinksDefault(){
        if (!is_array($this->view_root->children['link_group']->children))
        $this->view_root->children['link_group']->children = array();
        $links_default = ActionsUtil::getLinksDefault($this->page_id);
        foreach ($links_default as $id) {
            $this->setLink($id);
        }
    }

    /**
     * добавляет link на страницу.
     * все доступные link'и добавляются автоматически методом _setDefaultLinks
     * есть смысл добавлять вручную, когда есть переменные атрибуты -  url и params.
     * в этом случае передается массив ($variables), подменяющий соответственно
     * "%1", "%2",.. в actions.xml
     *
     * @param string    $id
     * @param array     $variables
     */
    function setLink($id, $variables = false, $confirm = ""){
        $link = new Link();
        $link->initialize($id);
        if ($confirm != "") {
            $link->setConfirm($confirm);
        }
        if ($this->checkPermission($id)) {
            if (is_array($variables)) {
                $link->parse_variables($variables);
            }
            $this->view_root->children['link_group']->children[] = @$link;
        }
    }

    function setTab($id, $params = array()) {
        $tab = new Tab();
        $tab->initialize($id);

        if (isset($params['href'])) {
            $tab->href = $params['href'];
        }

        if ($this->checkPermission($id)) {
            $this->view_root->children['tab_group']->children[] = @$tab;
        }
    }

    function enableNavigation() {
        $this->view_root->enable_navigation = true;
    }

    function disableNavigation() {
        $this->view_root->enable_navigation = false;
    }

    /**
     * Проверяет права текущего ползователя на данный action, tab...
     *
     * @param string    $id
     * @return bool
     */
    function checkPermission($id){
        return ($this->checkPermissionTemporary($id) || ((in_array($this->user->profile_current->name, $GLOBALS['profiles_basic'])) ? ActionsUtil::checkPermissionBasic($id, $this->user->profile_current) : ActionsUtil::checkPermissionExtended($id, $this->user->profile_current)));
    }

    function checkPermissionTemporary($id){
        foreach ($this->user->permissions_temporary as $permission) {
            if (strpos($id, $permission) !== false) return true;
        }
        return false;
    }

    function setPermissionTemporary($id){
        $this->persistent_vars->append('permissions_temporary', $id);
    }

    /**
     * Когда очень мешает темплейт, но удалить все из него нельзя
     * (чтобы работало и без контроллера)
     *
     * @param unknown_type $template
     */
    function substituteTemplate(&$template){
        if ($this->enabled) {
            $template_file = array_pop(explode("/", $template));
            $template_quiet = DIR_TEMPLATES_QUIET . "/{$template_file}";
            $template_quiet_path = PATH_TEMPLATES_QUIET . "/{$template_file}";
            if (file_exists($template_quiet_path)){
                $template = $template_quiet_path;
            }
        }
    }

    function _setMenu(){
        $menu = new Menu();
        $menu->initialize();
        $this->menu = &$menu;
    }

    function _getMenuSelected(){
        $this->link_id = false;
        if (isset($_GET['page_id'])){
            $this->page_id = $_GET['page_id'];
            $this->link_id = false;
            $this->persistent_vars->set('page_id', $_GET['page_id']);
            $this->persistent_vars->set('link_id', $this->link_id);
        } elseif ($page_id = $this->persistent_vars->get('page_id')) {
            $this->page_id = $page_id;
        }

        if (isset($_GET['link_id'])) {
            $this->link_id = $_GET['link_id'];
            $this->persistent_vars->set('link_id', $_GET['link_id']);
        } elseif ($link_id = $this->persistent_vars->get('link_id')) {
            $this->link_id = $link_id;
        }
    }

    function _setMenuSelected(){
        if ($this->page_id && is_array($this->menu->groups)){
            foreach($this->menu->groups as $tmp){
                if (strpos($this->page_id, $tmp->id) !== false){
                    $selected_group = &ActionsUtil::getSelectedGroupById($tmp->id);
                    $selected_group->selected = true;
                    $this->menu->group_selected = &$selected_group;
                    if (is_array($tmp->elements)){
                        foreach ($tmp->elements as $index => $tmp_el) {
                            if ($tmp_el->getChildren()) {
                                foreach($tmp_el->getChildren() as $child) {
                                    if ($child->id === $this->page_id) {
                                        $selected_group->elements[$index]->elements[$child->id]->selected = true;
                                        $selected_group->element_selected = &$selected_group->elements[$index]->elements[$child->id];
                                    }                                
                                }
                            } else {
                                if ($tmp_el->id === $this->page_id) {
                                    $selected_group->elements[$tmp_el->id]->selected = true;
                                    $selected_group->element_selected = &$selected_group->elements[$tmp_el->id];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    function _clean(){
//      $str = "!" . $_SERVER['PHP_SELF'] . "\n";
        while(strlen($ob = ob_get_contents())){
//          $str .= $ob;
            ob_end_clean();
        }
//      $str .= "\n\n";
//        $f = fopen($_SERVER['DOCUMENT_ROOT'] . "/zlog/buffer.txt", 'a+');
//        fwrite($f,$str);
//        fclose($f);
        ob_end_clean();
    }

    /**
     * Добавляет message на страницу.
     * например "действие успешно".
     *
     * @param unknown_type $text
     */
    function setMessage($text, $onclick = false, $url = false, $opener_url = false, $cancel_url = false, $selenium_feedback = false, $target = '_self'){

            if ($this->isAjaxRequest()) {
                if (($onclick == JS_GO_URL) && $url) {
                    if (!isset($_SESSION['s']['flash_messages'])) {
                        $_SESSION['s']['flash_messages'][] = $text;                        
                    }
                    $this->sendRefresh($url);
                }
            }

            $this->view_root->children['message']->url = $url;
            $this->view_root->children['message']->opener_url = $opener_url;
            if (($onclick == JS_CLOSE_SELF_GO_URL_OPENER) && !$opener_url) {
                $opener_url = $url;
                $this->view_root->children['message']->opener_url = $url;
                $this->view_root->children['message']->url = false;                
            }
            $this->view_root->children['message']->onclick = ($onclick === false) ?
                $onclick :
                "function(){".$onclick."(". ($opener_url === false ? "" : "'".$opener_url."'") .");}";
            $this->view_root->children['message']->cancel_url = $cancel_url;
            $this->view_root->children['message']->content = $text; //hi mirgor!
            $this->view_root->children['message']->selenium_feedback = strip_tags($selenium_feedback);
            $this->view_root->children['message']->target = $target;

    }

    /**
     * добавляет фильтр на страницу
     * name - имя select'а, options - массив вида value=>label
     * eckb установлен required, то фильтр будет открыт по умолчанию и
     * вместо элемента "все" будет элемент "необходимо выбрать.."
     *
     * @param string    $name
     * @param array     $options: false - textbox, array - select
     * @param string    $selected
     * @param bool      $required
     */
    function addFilter($title, $name, $options, $selected, $required = false, $default='0', $enable_first=true, $extra_html='', $prepend_html=''){
        if ($this->enabled) {
            $filter = new Filter();
            $filter->title = $title;
            $filter->name = $name;
            $filter->required = $required;
            $filter->default_value = $default;
            $filter->enable_first = $enable_first;
            $filter->extra_html = $extra_html;
            $filter->prepend_html = $prepend_html;
            if ($options!==false && !in_array($options,array('hidden','javascript','div'))) {
                $filter->setOptions($options, $selected);
            }else {
                $filter->options = $options;
                $filter->selected = $selected;
            }
            $this->view_root->children['filter_group']->addFilter($filter);
        }
    }

    function addFilterJavaScript($javascript) {
        if (!empty($javascript)) {
            $this->AddFilter('','','javascript',$javascript);
        }
    }

    function setFilterTarget($url) {
        $this->view_root->children['filter_group']->target = $url;
    }

    /**
     * переопределяет user'а, установленного в initialize
     * нужно, например, при logout'е
     *
     */
    function setUser(){
        if ($this->enabled){
            if (!$_SESSION['s']['mid']) {
    //          logout
                if (isset($this->persistent_vars)) $this->persistent_vars->destroy_all();
//              unset($_SESSION['s']['mid']);
            }
            $this->user = new User();
            $this->user->initialize($_SESSION['s']['mid']);
        }
    }
    
    function changePageId($id) {
        if ($GLOBALS['controller']->page_id != $id) {
            $GLOBALS['controller']->page_id = $id;
            $GLOBALS['controller']->persistent_vars->set('page_id', $id);
        }
    }
}

class Buffer{

    var $object;

    function initialize($id){
        $type = ucfirst(ActionsUtil::getType($id));
        if (class_exists($type)) {
            $this->object = new $type();
            $this->object->initialize($id);
        }
    }

    function start(){
    }

    function stop(){
    }
}

class BufferVar extends Buffer {

    var $var;
    var $var_name;
    var $buffer_str;

    function start($var_name, &$var){
        $this->var = &$var;
        $this->buffer_str = $var;
        $this->var_name = $var_name;
        $this->var = "";
    }

    function _flushVar(){
        if (strtolower(get_class($this->object)) == TRASH) {
            $this->var = $this->buffer_str;
        }
    }

    function stop(){
            $this->object->content = $this->var;
            $this->_flushVar();
            $this->object->parseWords();
    }
}

class BufferOb extends Buffer {

    function start(){
        ob_start();
    }

    function stop(){
        $this->object->content = ob_get_contents();
        ob_end_clean();
        $this->object->parseWords();
    }
}

class BufferReturn extends Buffer {

    function start($value, $variables){
        $this->object->content = $value;
        if ($variables) {
            $this->object->parseVariables($variables);
        }
        $this->object->parseWords();
    }
}

class PersistentVars{

    var $vars;
    var $cookie_keys = array();
    var $exclusion = array('lang');

    function set($name, $value, $use_cookie = false){
        $this->vars[$name] = $value;
        if ($use_cookie) {
            setcookie($name, $value);
        }
    }

    function append($name, $value){
        if (!is_array($this->vars[$name])){
            $this->vars[$name] = array();
        }
        if (!in_array($value, $this->vars[$name])){
            $this->vars[$name][] = $value;
        }
    }

    function get($name){
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }
        else return false;
    }

    function destroy($name){
        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
        }
        else return false;
    }

    function destroy_all(){
        foreach (array_keys($this->vars) as $key) {
            if (!in_array($key, $this->exclusion)) {
                unset($this->vars[$key]);
            }
        }
        $this->terminate();
    }

    function initialize(){
        $cookie = array();
        $session = (is_array($_SESSION['els_vars'])) ? $_SESSION['els_vars'] : array();
        if (is_array($_COOKIE['els_vars'])){
            $cookie = $_COOKIE['els_vars'];
            $this->cookie_keys = array_keys($_COOKIE);
        }
        $this->vars = array_merge($session, $cookie, array());
    }

    function terminate(){
        $els_vars = &$this->vars;
        //session_register('els_vars');
        $_SESSION['els_vars'] = $els_vars;
    }

}

?>