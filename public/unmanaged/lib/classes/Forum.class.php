<?php

class CForumController {
    var $_action;
    var $_type;
    var $_category;
    var $_thread;
    var $_blank;
    var $_msg;
    var $_ajax = false;

    var $_view = false;

    var $_form_data;

    var $_lessonInfo = null;

    function init() {
        $this->_prepare_variables();
        if (!$this->_category && !$this->_thread){
            $this->_view = new CForumCategoryView();
        }
        if ($this->_category){
            $GLOBALS['controller']->setHelpSection('theme');
            $this->_view = new CForumThreadView();
        }
        if ($this->_category && $this->_action) {
            if (!count($_POST)) {
            	if($this->_action == 'edit_thread')
                	$GLOBALS['controller']->setHeader(_("Редактирование темы"));
                else
                	$GLOBALS['controller']->setHeader(_("Создание темы"));
                $this->_view = new CForumCreateThreadView();
            }
        }
        if ($this->_thread){
            $GLOBALS['controller']->setHelpSection('post');
            $this->_view = new CForumMessageView();
        }

        $this->_view->init(&$this);

        $class_name = "CForumAction_{$this->_action}";
        if (class_exists($class_name)) {
            if ($this->_action) {
                $GLOBALS['controller']->setHelpSection($this->_action);
            }
            $action = new $class_name;
            $action->setBlank($this->_blank);
            $action->setAjax($this->_ajax);
            $action->init($this->_get_form_data());
        }

    }

    static function isModerator()
    {
        $subject = $subjectId = null;
        if (isset($_REQUEST['thread'])) {
            $category = (int) getField('forumthreads', 'category', 'thread', (int) $_REQUEST['thread']);
            if ($category) {
                $sql = "SELECT name, cid FROM forumcategories WHERE id = '".$category."'";
                $res = sql($sql);
                if ($row = sqlget($res)) {
                    $subject   = $row['name'];
                    $subjectId = $row['cid'];
                }
            }
        }

        if (isset($_REQUEST['category'])) {
            $sql = "SELECT name, cid FROM forumcategories WHERE id = '".(int) $_REQUEST['category']."'";
            $res = sql($sql);
            if ($row = sqlget($res)) {
                $subject   = $row['name'];
                $subjectId = $row['cid'];
            }
        }

        if (isset($_REQUEST['lesson_id']) && $_REQUEST['lesson_id'] > 0) {
            if ($_SESSION['s']['perm'] == 2) {
                if ($_SESSION['s']['mid'] == getField('schedule', 'teacher', 'SHEID', $_REQUEST['lesson_id'])) {
                    return true;
                }
            }
        }

        if (null !== $subject && null !== $subjectId) {
            switch(strtolower($subject)) {
                case 'subject':
                    if ($_SESSION['s']['perm'] == 2) {
                        $sql = "SELECT * FROM Teachers WHERE MID = '".(int) $_SESSION['s']['mid']."' AND CID = '".(int) $subjectId."'";
                        $res = sql($sql);
                        return sqlrows($res);
                    }
                    break;
                case 'course':
                    return in_array($_SESSION['s']['perm'], array(3.3, 3.6));
                    break;
                case 'resource':
                    return (3.6 == $_SESSION['s']['perm']);
                    break;
                default:
                    return in_array($_SESSION['s']['perm'], array(3,4));
            }
        }

        return false;

    }

    function display() {
        $this->_view->assign('category',$this->_category);
        $this->_view->assign('thread',$this->_thread);
        $this->_view->display();
    }

    function _prepare_variables() {
        $this->_ajax = $GLOBALS['controller']->isAjaxRequest();
        $this->_action = trim(strip_tags($_REQUEST['action']));
        $this->_category = (int) $_REQUEST['category'];
        $this->_thread = (int) $_REQUEST['thread'];
        $this->_form_data = $_POST['data'];
        $this->_blank = (boolean) ($_REQUEST['view']=='blank');
        $this->_msg = (int) $_REQUEST['msg'];
        $this->_type = $_REQUEST['type'];
    }

    function _get_form_data() {
        return $this->_form_data;
    }

    function get_category() {
        return $this->_category;
    }

    function get_thread() {
        return $this->_thread;
    }

    function setLessonInfo($lessonInfo) {
        $this->_lessonInfo = $lessonInfo;
    }

    function getLessonInfo() {
        return $this->_lessonInfo;
    }

}

class CForumAction_move_thread extends CForumAction {
    function init($data) {
        $lessonIDUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        $thread= new  CForumThread();
        $thread->init($data);
        if ($thread_new = $thread->copy()) {
            refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?thread='.(int) $thread_new.$this->getParams());
            exit();
        }
    }
}

class CForumAction_create_message extends CForumAction {
    function init($data) {
        $lessonIDUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        $message = new CForumMessage();
        if (isset($data['message']['string']) || isset($data['message']['html'])) {
            if (empty($data['message']['string']) && empty($data['message']['html'])) {
                $thread = $_GET['thread'];
                $message = _('Необходимо ввести текст сообщения');
                $GLOBALS['controller']->setMessage($message, JS_GO_URL, $GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart .'?thread='.$thread.$this->getParams());
                $GLOBALS['controller']->terminate();
                exit();
            }
        }
        if (isset($data['message_answer']['string']) || isset($data['message_answer']['html'])) {
            if (empty($data['message_answer']['string']) && empty($data['message_answer']['html'])) {
                $thread = $_GET['thread'];
                $message = _('Необходимо ввести текст сообщения');
                $GLOBALS['controller']->setMessage($message, JS_GO_URL, $GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart .'?thread='.$thread.$this->getParams());
                $GLOBALS['controller']->terminate();
                exit();
            }
            $data['message']['string'] = $data['message_answer']['string'];
            $data['message']['html']   = $data['message_answer']['html'];
            unset($data['message_answer']);
        }
        $message->init($data);
        $thread = $message->create();

        refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart .'?thread='.(int) $thread.$this->getParams());
        exit();
    }
}

class CForumAction_delete_message extends CForumAction {
    function init($data) {
        CForumMessage::delete($_GET['id']);
        $lessonIDUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?thread='.$_GET['thread'].$this->getParams());
        exit();
    }
}

class CForumAction_create_thread extends CForumAction {
    function init($data) {
        $lessonIDUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        $thread = new CForumThread();
        $thread->init($data);
        if(count($_POST)) {
        	if (empty($data['name']['string']) || (empty($data['message']['string']) && empty($data['message']['html']))) {
                $message = _('Необходимо ввести название темы и текст сообщения');
                $GLOBALS['controller']->setMessage($message, JS_GO_URL, $GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?action=create_thread&category='.$_GET['category'].$this->getParams());
                $GLOBALS['controller']->terminate();
        	    exit();
        	}
            $thread_id = $thread->create();
            refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?thread='.(int) $thread_id.$this->getParams());
            exit();
        }
    }
}

//CForumView
class CForumAction_edit_thread extends CForumAction {
    function init($data) {
        $lessonIDUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        $thread = new CForumThread();
        $thread->init($data);
        if(count($_POST)) {
        	if (empty($data['name']['string']) || (empty($data['message']['string']) && empty($data['message']['html']))) {
                $message = _('Необходимо ввести название темы и текст сообщения');
                $GLOBALS['controller']->setMessage($message, JS_GO_URL, $GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?action=create_thread&category='.$_GET['category'].$this->getParams());
                $GLOBALS['controller']->terminate();
        	    exit();
        	}
        	$thread = $thread->edit($_GET['id']);
     	    refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?thread='.(int) $_GET['id'].$this->getParams());
        	exit();
        }
    }
}

class CForumAction_delete_thread extends CForumAction {
    function init($data) {
        $lessonIDUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        $category = CForumThread::delete($_GET['id']);
        refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/'.$lessonIDUrlPart.'?category='.(int) $category.$this->getParams());
        exit();
    }
}

class CForumAction_create_category extends CForumAction {
    function init($data) {
        if(count($_POST)) {
            $category = new CForumCategory();
            $category->init($data);
            if (empty($data['name']['string'])) {
                $GLOBALS['controller']->setMessage(_('Необходимо ввести название категории'), JS_GO_URL, $GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/?'.$this->getParams().'&action=create_category');
                $GLOBALS['controller']->terminate();
                exit();
            }
            $category->create();
            refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/?'.$this->getParams());
            exit();
        }
        $GLOBALS['controller']->setHeader(_("Создание категории"));
        $view = new CForumCreateCategoryView();
        $view->init(&$this);
        $view->display();
    }
}

class CForumAction_delete_category extends CForumAction {
    function init($data) {
        CForumCategory::delete($_GET['id']);
        refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/');
        exit();
    }
}

class CForumAction_edit_category extends CForumAction {
    function init($data) {
        if(count($_POST)) {
            $category = new CForumCategory();
            $category->init($data);
            if (empty($data['name']['string'])) {
                $GLOBALS['controller']->setMessage('Необходимо ввести название категории', JS_GO_URL, $GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/?'.$this->getParams().'&action=edit_category&id='.$_GET['id']);
                $GLOBALS['controller']->terminate();
                exit();
            }
            $category->edit($_GET['id']);
            refresh($GLOBALS['sitepath'].'forum/index/index/subject/'.$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id'].'/?'.$this->getParams());
            exit();
        }
        $category = CForumCategory::get($_GET['id']);
        $GLOBALS['controller']->setHeader(_("Редактирование категории"));
        $view = new CForumCreateCategoryView();
        $view->init(&$this);
        $view->assign("data", $category);
        $view->assign("action", "edit_category");
        $view->display();
    }
}

class CForumAction {
    var $_blank;
    var $_ajax = false;
    function init($data) {

    }

    function setBlank($blank) {
        $this->_blank = $blank;
    }

    function setAjax($ajax) {
        $this->_ajax = $ajax;
    }

    function getParams() {
        $ret = '';
        if ($this->_blank) {
            $ret .= '&view=blank';
        }
        return $ret;
    }

    function sendJson($data)
    {
        header(sprintf('Content-type: text/javascript; charset=%s;', 'UTF-8'));
        if (isset($data['message'])) {
            $data['message'] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'UTF-8', $data['message']);
        }
        echo json_encode($data);
        exit();
    }

    function sendRefresh($url)
    {
        $GLOBALS['controller']->sendRefresh($url);
    }
}

class CForumMessageView extends CForumView {
    var $_template = "forum_messages.tpl";

    function _get_messages($messages, $all, $level) {
        static $processed = array();

        $ret = array();
        if (is_array($messages) && count($messages)) {
            foreach($messages as $message) {
                if ($processed[$message['id']]) continue;

                    $ret[$message['id']] = $message;
                    $ret[$message['id']]['level'] = $level;

                    if(isset($message['children']) && is_array($message['children']) && count($message['children'])) {
                        foreach($message['children'] as $child) {
                            if (isset($all[$child])) {
                                $ret = array_merge($ret, $this->_get_messages(array($all[$child]),$all, $level+1));
                                $processed[$child] = $child;
                            }
                        }
                    }
                $processed[$message['id']] = $message['id'];
            }
        }
        return $ret;
    }


    function init($controller) {
        parent::init($controller);

        $messages = array();
        foreach(CForumMessage::get_as_array($this->_controller->get_thread()) as $msg) {
            $messages[$msg['id']] = $msg;
            if (isset($messages[$msg['parent']])) $messages[$msg['parent']]['children'][$msg['id']] = $msg['id'];
        }

        $messages = $this->_get_messages($messages,$messages,0);

        $this->_smarty->assign('ICONS',$this->_get_icons_array());
        $this->_smarty->assign('VISIBILITY_TYPES',$this->_get_visibility_types());
        $this->_smarty->assign('VISIBILITY_DEPENDENCES',$this->_get_visibility_dependences());
        $this->_smarty->assign('toolTip',$this->_get_visibility_tooltip());
        $this->_smarty->assign('categories', CForumCategory::get_as_array());
        $this->_smarty->assign('departments',get_needed_departments($GLOBALS['s']['mid']));
        $this->_smarty->assign('courses',$this->_get_courses());
        $this->_smarty->assign('teachers',$this->_get_teachers());
        $this->_smarty->assign('messages', $messages);
        $this->_smarty->assign('lessonInfo', $controller->getLessonInfo());
        $this->_smarty->assign('iconDelete', getIcon('delete'));
        $this->_smarty->assign('iconAnswer', getIcon('answer'));
        $this->_smarty->assign('fckeditor', $this->_get_fckeditor('data[message][html]',''));
        $this->_smarty->assign('fckeditor_answer', $this->_get_fckeditor('data[message_answer][html]',''));
    }
}

class CForumThreadView extends CForumView {
    var $_template = "forum_threads.tpl";

    function init($controller) {
        parent::init($controller);
        $thread_info = CForumThread::get($_GET['id']);
        $this->_smarty->assign('ICONS', $this->_get_icons_array());
        $this->_smarty->assign('VISIBILITY_TYPES',$this->_get_visibility_types());
        $this->_smarty->assign('VISIBILITY_DEPENDENCES',$this->_get_visibility_dependences());
        $this->_smarty->assign('toolTip',$this->_get_visibility_tooltip());
        $this->_smarty->assign('departments',get_all_departments()/*get_needed_departments($GLOBALS['s']['mid'])*/);
        $this->_smarty->assign('courses',$this->_get_courses());
        $this->_smarty->assign('teachers',$this->_get_teachers());
        $this->_smarty->assign('threads', CForumThread::get_as_array($this->_controller->get_category()));
        $this->_smarty->assign('thread_info', $thread_info);
        $this->_smarty->assign("action", $_GET['action']);
        $this->_smarty->assign('fckeditor', $this->_get_fckeditor('data[message][html]', $thread_info['message']));
    }

}

class CForumCategoryView extends CForumView {
    var $_template = "forum_categories.tpl";

    function init($controller) {
        parent::init($controller);
        $this->_smarty->assign('categories', CForumCategory::get_as_array());
    }
}

class CForumView {
    var $_controller;
    var $_smarty;
    var $_template = "forum_categories.tpl";

    function init($controller) {
        $this->_controller = $controller;
        $this->_smarty = new Smarty_els();
        $this->_smarty->assign('BREADCRUMBS',$this->_get_breadcrumbs());
        $this->_smarty->assign('MODERATE', CForumController::isModerator());
        $this->_smarty->assign('MID', $GLOBALS['s']['mid']);
        $this->_smarty->assign('PERM',$GLOBALS['s']['perm']);
        $this->_smarty->assign('COURSES',selCourses($GLOBALS['s']['tkurs'],0,true));
        $this->_smarty->assign('SITEPATH',$GLOBALS['sitepath']);
        $this->_smarty->assign('OKBUTTON',okbutton());
        $this->_smarty->assign('SUBJECT', $_REQUEST['subject']);
        $this->_smarty->assign('SUBJECT_ID', $_REQUEST['subject_id']);
        $this->_smarty->assign('LESSON_ID_URL_PART', (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '');
        $this->_smarty->assign('IS_RESULT', (isset($_REQUEST['is_result']))? TRUE : FALSE);

        $GLOBALS['controller']->setView('DocumentContent');

        if ($GLOBALS['controller']->isAjaxRequest()) {
            header(sprintf('Content-type: text/html; charset=%s;', $GLOBALS['controller']->lang_controller->lang_current->encoding));
            //$GLOBALS['controller']->setView('DocumentContent');
        }
        if ($this->_controller->_blank) {
        //    $GLOBALS['controller']->setView('DocumentBlank');
        }
        $this->_smarty->assign('BLANK',$this->_controller->_blank);
        $this->_smarty->assign('msg', $this->_controller->_msg);
    }

    function display() {
        $GLOBALS['controller']->captureFromReturn(CONTENT,$this->_smarty->fetch($this->_template));
        $GLOBALS['controller']->terminate();
    }

    function assign($name, $value) {
        $this->_smarty->assign($name,$value);
    }

    function _get_breadcrumbs() {
        $breadcrumbs = new CForumBreadCrumbs();
        $breadcrumbs->init($this->_controller->_category, $this->_controller->_thread);
        return $breadcrumbs->get_as_string();
    }

    function _get_courses() {
        if ($category = $this->_controller->get_category()) {
            $cid = CForumCategory::getCourse($category);
        } else {
            if ($thread = $this->_controller->get_thread()) {
                $cid = CForumThread::getCourse($thread);
            }
        }
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $sql = "SELECT DISTINCT Courses.CID, Courses.Title
                FROM Students INNER JOIN Courses ON (Courses.CID=Students.CID)
                WHERE Students.MID='".(int) $GLOBALS['s']['mid']."'
                ";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            if (isset($cid) && ($cid>0) && ($row['CID']!=$cid)) continue;
            $courses[$row['CID']] = $row['Title'];
        }
        return $courses;
    }

    function _get_teachers() {
        if ($category = $this->_controller->get_category()) {
            $cid = CForumCategory::getCourse($category);
        } else {
            if ($thread = $this->_controller->get_thread()) {
                $cid = CForumThread::getCourse($thread);
            }
        }
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        $sql = "SELECT DISTINCT Courses.CID, Courses.Title
                FROM Teachers INNER JOIN Courses ON (Courses.CID=Teachers.CID)
                WHERE Teachers.MID='".(int) $GLOBALS['s']['mid']."'
                ";
        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$courseFilter->is_filtered($row['CID'])) continue;
            if (isset($cid) && ($cid>0) && ($row['CID']!=$cid)) continue;
            $courses[$row['CID']] = $row['Title'];
        }
        return $courses;
    }

    function _get_icons_array() {
        $icons = array(
        1 => "images/forum/1.gif",
        2 => "images/forum/2.gif",
        3 => "images/forum/3.gif",
        4 => "images/forum/4.gif",
        5 => "images/forum/5.gif",
        6 => "images/forum/6.gif",
        7 => "images/forum/7.gif",
        8 => "images/forum/8.gif",
        );
        return $icons;
    }

    function _get_visibility_dependences() {
        $dependences = array(
        4=>array('departments')/*,
        5=>array('courses'),
        6=>array('courses'),
        7=>array('teachers')*/
        );
        return $dependences;
    }

    function _get_visibility_types() {
        if ($category = $this->_controller->get_category()) {
            $cid = CForumCategory::getCourse($category);
        } else {
            if ($thread = $this->_controller->get_thread()) {
                $cid = CForumThread::getCourse($thread);
            }
        }

        if ($cid) {
            $types = ($GLOBALS['s']['perm']<2)?
                array(
                    5=>_("Одногруппники"),
                    6=>_("Однокурсники"),
                    7=>_("Преподаватели"),
                    2=>_("Кураторы")
                    ):
                array(
                0=>_("Все"),
                /*2=>_("Куратор")*/
                7=>_("Преподаватели")
                );
        }
        else {
            $types = array(
                0=>_("все пользователи"),
                /*1=>_("Руководитель"),*/
                /*2=>_("Куратор"),*/
                3=>_("учебная администрация"),
                4=>_("конкретый представитель учебной администрации"),
            );
        }

        return $types;
    }

    function _get_visibility_tooltip() {
        if ($category = $this->_controller->get_category()) {
            $cid = CForumCategory::getCourse($category);
        } else {
            if ($thread = $this->_controller->get_thread()) {
                $cid = CForumThread::getCourse($thread);
            }
        }

        $toolTip = '';

        if ($cid) {
            $toolTip = $GLOBALS['s']['perm']<2?'forum_visibility4course_student':'forum_visibility4course_teacher';
        } else {
            $toolTip = 'forum_visibility4all';
        }

        return $toolTip;
    }

    function _get_fckeditor($name, $value) {
        ob_start();
        $oFCKeditor = new FCKeditor($name) ;
        $oFCKeditor->BasePath   = "{$GLOBALS['sitepath']}lib/FCKeditor/";
        $oFCKeditor->Value      = $value;
        $oFCKeditor->Width      = 500;
        $oFCKeditor->Height     = 300;
        $oFCKeditor->ToolbarSet = 'ForumToolbar';
        $fck_code = $oFCKeditor->Create() ;
        $fck_code = ob_get_contents();
        ob_end_clean();

        return $fck_code;
    }

}

class CForumCreateCategoryView extends CForumView {
    var $_template = "forum_create_category.tpl";

    function init($controller) {
        parent::init($controller);
    }
}

class CForumCreateThreadView extends CForumThreadView {
    var $_template = "forum_create_thread.tpl";

    function init($controller) {
        parent::init($controller);
    }
}

class CForumBreadCrumbs {
    var $_category;
    var $_thread;
    var $_breadcrumbs;

    function init($category, $thread) {
        $this->_category = $category;
        $this->_thread = $thread;
        $this->parse();
    }

    function parse() {
        if ($this->_category)
            $this->_breadcrumbs = CForumCategory::get($this->_category);
        if ($this->_thread)
            $this->_breadcrumbs = CForumThread::get($this->_thread);
    }

    function get_as_string() {
        $this->_breadcrumbs['category']['name'] = strip_tags(str_replace('<br>','',substr($this->_breadcrumbs['category']['name'],0,80)));
        $this->_breadcrumbs['message'] = strip_tags(str_replace('<br>','',$this->_breadcrumbs['theme']));
        $lessonIdUrlPart = (isset($_REQUEST['lesson_id']))? 'lesson_id/' . $_REQUEST['lesson_id'] . '/' : '';
        if ($this->_thread)
            $breadcrumbs = "
            <table width=100% class=main cellspacing=0>
                <tr><td>
            <b><a href=\"{$GLOBALS['sitepath']}forum/index/index/subject/".$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id']."/" . $lessonIdUrlPart . "?category={$this->_breadcrumbs['category']['id']}\">"._('Все темы')."</a>
                            <img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'> <a href=\"{$GLOBALS['sitepath']}forum/index/index/subject/".$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id']."/" . $lessonIdUrlPart . "?thread={$this->_breadcrumbs['thread']}\">{$this->_breadcrumbs['message']}</a></b>
            </td></tr>
            </table>";
        if ($this->_category) {
//            $breadcrumbs = "<b><a href=\"{$GLOBALS['sitepath']}forum/index/index/?category={$this->_breadcrumbs['id']}\">"._('Все темы')."</a></b>";
        }
        return $breadcrumbs;
    }

}

class CForumFormDataParser {
    var $_data;

    function init($array) {
        if (is_array($array) && count($array)) {
            foreach($array as $k=>$v) {
                $k = addslashes(trim(strip_tags($k)));
                if (isset($v['string'])) {
                    if ($GLOBALS['controller']->isAjaxRequest()) {
                        $v['string'] = iconv('UTF-8', $GLOBALS['controller']->lang_controller->lang_current->encoding, $v['string']);;
                    }
                    $this->_data[$k] = nl2br($GLOBALS['adodb']->Quote(trim(strip_tags($v['string']))));
                }
                if (isset($v['html'])) {
                    if ($GLOBALS['controller']->isAjaxRequest()) {
                        $v['html'] = iconv('UTF-8', $GLOBALS['controller']->lang_controller->lang_current->encoding, $v['html']);;
                    }
                    $this->_data[$k] = $GLOBALS['adodb']->Quote(trim($v['html']));
                }
                if (isset($v['int'])) $this->_data[$k] = (int) $v['int'];
            }
        }
    }

    function get_as_array() {
        return $this->_data;
    }
}

class CForumMessage extends CForumItem {

    function create($sendmail=true) {
        if (is_array($this->_attributes) && count($this->_attributes)
            && $this->_attributes['thread'] && !empty($this->_attributes['message'])) {

                if ($this->_attributes['courses']) $this->_attributes['oid'] = $this->_attributes['courses'];
                if ($this->_attributes['departments']) $this->_attributes['oid'] = $this->_attributes['departments'];
                if ($this->_attributes['teachers']) $this->_attributes['oid'] = $this->_attributes['teachers'];
                if (!$this->_attributes['oid'] && in_array($this->_attributes['type'],array(4,5,6,7)))
                    $this->_attributes['type'] = 0;
                if (!in_array($this->_attributes['type'],array(4,5,6,7))) $this->_attributes['oid']=0;

                unset($this->_attributes['category']);
                unset($this->_attributes['courses']);
                unset($this->_attributes['departments']);
                unset($this->_attributes['teachers']);

                $this->_attributes['sendmail'] = (int) $this->_attributes['sendmail'];
                $this->_attributes['is_topic'] = (int) $this->_attributes['is_topic'];

                if (!$this->_attributes['is_topic'] && $sendmail) $this->send_mail();

                if (!isset($this->_attributes['mid'])) {
                    $sql_addon .= ", mid";
                    $sql_addon_values .= ", '".(int) $GLOBALS['s']['mid']."'";
                }

                if (!isset($this->_attributes['posted'])) {
                    $sql_addon .= ", posted";
                    $sql_addon_values .= ", '".time()."'";
                }

                $sql = "INSERT INTO forummessages
                        (".join(',',array_keys($this->_attributes))." {$sql_addon})
                        VALUES
                        (".join(",",array_values($this->_attributes))." {$sql_addon_values})";
                sql($sql);

                $this->_attributes['msg_id'] = sqllast();
                CForumThread::update_lastpost($this->_attributes['thread']);
                CForumCategory::update_lastpost(CForumThread::get_category($this->_attributes['thread']));

                return $this->_attributes['thread'];
        }
    }

    function update() {
        if (is_array($this->_attributes) && count($this->_attributes)
            && $this->_attributes['id'] && !empty($this->_attributes['message'])) {

                $id = $this->_attributes['id'];
                unset($this->_attributes['id']);

                foreach ($this->_attributes as $k=>$v)
                    if (!empty($k)) $sql_addons[] = "$k=$v";

                if (!isset($this->_attributes['mid']))
                    $sql_addons[] = "mid='".(int) $GLOBALS['s']['mid']."'";
                if (!isset($this->_attributes['posted']))
                    $sql_addons[] = "posted='".time()."'";

                if (is_array($sql_addons) && count($sql_addons)) {
                    $sql = "UPDATE forummessages SET ".join(',',$sql_addons)." WHERE id='".(int) $id."'";
                    sql($sql);
                }

                CForumThread::update_lastpost($this->_attributes['thread']);
                CForumCategory::update_lastpost(CForumThread::get_category($this->_attributes['thread']));

                return $this->_attributes['thread'];

        }
    }

    function _delete_recursive($id) {
        if ($id) {
            $sql = "SELECT id FROM forummessages WHERE parent = '".(int) $id."'";
            $res = sql($sql);
            while($row = sqlget($res)) {

                CForumMessage::_delete_recursive($row['id']);

                sql("DELETE FROM forummessages WHERE id='".(int) $row['id']."'");
            }
        }
    }


    function delete($id) {
        if ($id) {
            if (!CForumController::isModerator()) $sql_addon = " AND mid='".(int) $GLOBALS['s']['mid']."'";

            $sql = "SELECT id FROM forummessages WHERE id='".(int) $id."' {$sql_addon}";
            $res = sql($sql);
            if (sqlrows($res) == 1) {
                CForumMessage::_delete_recursive($id);
            }

            CForumMessage::_delete_recursive((int) $id);
            sql("DELETE FROM forummessages WHERE id='".(int) $id."' {$sql_addon}");
        }
    }

    function get_as_array($thread) {
        if ($thread) {
            $sql =  "SELECT forumthreads.thread, forummessages.*
                     FROM forummessages, forumthreads
                     WHERE forummessages.thread='".(int) $thread."'
                     AND forummessages.thread=forumthreads.thread
                     ORDER by posted ASC";
            $messageFilter = new CMessageFilter();
            $messageFilter->init();

            $res = sql($sql);
            while($row = sqlget($res)) {
                if (!$messageFilter->is_filtered($row['id'])) continue;
                if ($author = CPerson::get($row['mid'], 'LFP')) {
                    $row['author'] = $author->getNameLFP();
                }
                $row['email'] = CForumThread::get_email($row['mid']);
                $row['date'] = date(' H:i:s d.m.Y',$row['posted']);
                $row['photo'] = getPhoto($row['mid'],1,60,80,1);
                $rows[] = $row;
            }
            return $rows;
        }
    }

    function count($thread) {
        if ($thread) {
            /*
            $sql = "SELECT COUNT(*) AS count FROM forummessages WHERE thread='".(int) $thread."' AND is_topic=0";
            $res = sql($sql);
            if (sqlrows($res) && ($row=sqlget($res))) return $row['count'];
            */
            return count(CForumMessage::get_as_array($thread));
        }
    }

    /**
    * Послать новое сообщение подписчикам вопроса...
    * @todo оптимизировать и привести в порядок
    */
    function send_mail() {
        if (is_array($this->_attributes) && count($this->_attributes) && $this->_attributes['thread']) {

            $from = getDeansOptions();
            $subject = "Новое сообщение в теме ".$GLOBALS['sitepath'];
            $body = $this->_attributes['message'];

            $sql = "SELECT forummessages.* FROM forummessages WHERE is_topic=1 AND thread='".(int) $this->_attributes['thread']."'";
            $res = sql($sql);
            if (sqlrows($res) && ($question = sqlget($res))) {
                $sql = "SELECT DISTINCT forummessages.mid, People.EMail
                        FROM forummessages
                        INNER JOIN People ON (People.MID=forummessages.mid)
                        WHERE forummessages.sendmail=1 AND forummessages.thread='".(int) $this->_attributes['thread']."'";
                $res = sql($sql);
                while($row = sqlget($res)) {
                    if ($row['EMail']) {
                        sendMail($row['EMail'],$subject,$body, $from['email'], $from['name']);
                    }
                }
            }

        }
    }

}

class CForumThread extends CForumMessage {

    function create($sendmail=true) {
        if (is_array($this->_attributes) && count($this->_attributes)
            && $this->_attributes['category'] && !empty($this->_attributes['message'])) {
            $sql = "INSERT INTO forumthreads (category) VALUES (".$this->_attributes['category'].")";
            sql($sql);
            $this->_attributes['thread'] = sqllast();
            $this->_attributes['is_topic'] = 1;
            parent::create($sendmail);
            return $this->_attributes['thread'];
        }
    }

    function edit($thread) {
        if (is_array($this->_attributes) && count($this->_attributes) && $thread)
        {
        	unset($this->_attributes['category']);
        	unset($this->_attributes['courses']);
        	unset($this->_attributes['departments']);
        	unset($this->_attributes['teachers']);
            $sql = "UPDATE forummessages SET";
            foreach ($this->_attributes as $k=>$v) {
                $sql .= " ".$k." = ".$v.",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= " WHERE thread=".(int) $thread." AND is_topic = 1";
            sql($sql);
            return $thread;
        }
    }

    function delete($id) {
        if ($id) {
            $category = CForumThread::get_category($id);
//            parent::delete($id);
            sql("DELETE FROM forummessages WHERE thread='".(int) $id."'");
            sql("DELETE FROM forumthreads WHERE thread='".(int) $id."'");
            return $category;
        }
    }

    function get_as_array($category) {
        if ($category) {
            $sql = "
                SELECT
                  forumthreads.thread,
                  forummessages.*
                FROM
                  forummessages
                  INNER JOIN forumthreads ON (forumthreads.thread = forummessages.thread)
                WHERE
                  forummessages.is_topic = '1' AND
                  forumthreads.category='".(int) $category."'
                ORDER BY forumthreads.lastpost DESC";

            $messageFilter = new CMessageFilter();
            $messageFilter->init();
            $res = sql($sql);
            while($row = sqlget($res)) {
                if (!$messageFilter->is_filtered($row['id'])) continue;
                $row['email'] = CForumThread::get_email($row['mid']);
                $row['author'] = mid2name($row['mid']);
                $row['date'] = date('H:i:s d.m.Y', CForumThread::get_last_modify($row['thread']));
                $row['answers'] = CForumMessage::count($row['thread']);
                $rows[] = $row;
            }
            return $rows;
        }
    }

    function get($thread) {
        if ($thread) {
            $sql = "SELECT
                        forumthreads.category,
                        forummessages.*,
                        forummessages.name as theme
                    FROM forumthreads
                    INNER JOIN forummessages ON (forumthreads.thread = forummessages.thread)
                    WHERE forumthreads.thread='".(int) $thread."' AND forummessages.is_topic=1";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                $row['category'] = CForumCategory::get($row['category']);
                return $row;
            }
        }
    }

    function count($category) {
        if ($category) {
            /*
            $sql = "SELECT COUNT(*) AS count FROM forumthreads WHERE category='".(int) $category."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row=sqlget($res))) return $row['count'];
            */
            return count(CForumThread::get_as_array($category));
        }
    }

    function update_lastpost($thread) {
        if ($thread) {
            $sql = "UPDATE forumthreads SET lastpost='".time()."' WHERE thread=".(int) $thread."";
            sql($sql);
        }
    }

    function get_last_modify($thread) {
        if ($thread) {
            $sql = "SELECT posted FROM forummessages WHERE thread='".(int) $thread."'
                    ORDER BY posted DESC
                    LIMIT 1";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res)))
            	if(isset($row['posted']))
            		return $row['posted']; //mysql
            	else
            		return $row['POSTED']; //oci8
        }
    }

    function _is_thread_exists($thread, $category) {
        $sql = "SELECT thread FROM forumthreads
                WHERE category='".(int) $category."' AND thread='".(int) $thread."'";
        $res = sql($sql);
        return sqlrows($res);
    }

    function copy() {
        if ($this->_attributes['thread'] && $this->_attributes['category']) {

            $thread = (int) $this->_attributes['thread'];
            $category = (int) $this->_attributes['category'];
            if (!$this->_is_thread_exists($thread, $category)) {

                $sql = "SELECT * FROM forummessages WHERE thread='{$thread}' AND is_topic=1";
                $res = sql($sql);
                if (sqlrows($res) && ($row = sqlget($res))) {
                    // Создание нового вопроса
                    $id = $row['id'];
                    unset($row['id']);
                    $row['category'] = $category;
                    $thread_class = new CForumThread();

                    foreach(array_keys($row) as $rowKey) {
                        $row[$rowKey] = $GLOBALS['adodb']->Quote($row[$rowKey]);
                    }

                    $thread_class->set_attributes($row);
                    $thread_new = $thread_class->create(false);
                    unset($thread_class);

                    if ($thread_new) {

                        // Изменение текста перемещаемого вопроса
                        $message = new CForumMessage();
                        $message->set_attributes(
                            array(
                                'id'=>$id,
                                'message'=> $GLOBALS['adodb']->Quote(_("Тема &quot;").$row['message']._("&quot; была перемещёна.<br>Новый адрес темы: <a href=\"").$GLOBALS['sitepath']."forum/index/index/subject/".$_REQUEST['subject'].'/subject_id/'.$_REQUEST['subject_id']."/?thread=".(int) $thread_new."\">"._("ссылка")."</a>")
                                ));
                        $message->update();
                        unset($message);

                        // Перемещение ответов на вопрос
                        $sql = "SELECT * FROM forummessages WHERE thread='{$thread}' AND is_topic=0 ORDER BY posted ASC";
                        $res = sql($sql);
                        $ids = array();
                        while($row = sqlget($res)) {
                            $id = $row['id'];
                            unset($row['id']);
                            $row['thread'] = $thread_new;
                            $message = new CForumMessage();
                            foreach(array_keys($row) as $rowKey) {
                                $row[$rowKey] = $GLOBALS['adodb']->Quote($row[$rowKey]);
                            }
                            $message->set_attributes($row);
                            $message->create(false);
                            $ids[$id] = $message->_attributes['msg_id'];

                            unset($message);
                            // Удаление сообщения
                            CForumMessage::delete($id);
                        }
                        foreach ($ids as $oldId=>$newId) {
                            sql("UPDATE forummessages SET parent='$newId' WHERE parent='$oldId'");
                        }
                    }

                }

            } else $GLOBALS['controller']->setMessage(_('Данная тема уже существует в категории'));
        }
        return $thread_new;
    }

    function get_category($thread) {
        if ($thread) {
            $sql = "SELECT forumthreads.category
                    FROM forumthreads
                    WHERE forumthreads.thread='".(int) $thread."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) {
                return $row['category'];
            }
        }
    }

    function getCourse($thread) {
        if ($thread) {
           $row = CForumCategory::get(CForumThread::get_category($thread));
           return $row['cid'];
        }
    }

}

class CForumCategory extends CForumItem {

    function create() {
        if (is_array($this->_attributes) && count($this->_attributes) &&
            CForumController::isModerator()) {
            if (dbdriver == 'oci8')
           		$sql = "INSERT INTO forumcategories
                    (".join(',',array_keys($this->_attributes)).",create_by, create_date)
                    VALUES
                    (".join(",",array_values($this->_attributes)).", '".(int) $GLOBALS['s']['mid']."', SYSDATE)";
            else
           		$sql = "INSERT INTO forumcategories
                    (".join(',',array_keys($this->_attributes)).",create_by, create_date)
                    VALUES
                    (".join(",",array_values($this->_attributes)).", '".(int) $GLOBALS['s']['mid']."', ".$GLOBALS['adodb']->DBTimestamp(time()).")";
            sql($sql);
            return sqllast();
        }
    }

    function edit($id) {
        if (is_array($this->_attributes) && count($this->_attributes) &&
            CForumController::isModerator()) {
            $sql = "UPDATE forumcategories SET";
            foreach ($this->_attributes as $k=>$v) {
                $sql .= " ".$k." = ".$v.",";
            }
            $sql = substr($sql, 0, -1);
            $sql .= " WHERE id=".(int) $id;
            sql($sql);
            return sqllast();
        }
    }

    function delete($id) {
        if ($id  && CForumController::isModerator()) {
            $sql = "SELECT thread FROM forumthreads WHERE category='".(int) $id."'";
            $res = sql($sql);
            while($row = sqlget($res))
                CForumThread::delete($row['thread']);
            sql("DELETE FROM forumcategories WHERE id='".(int) $id."'");
        }
    }

    function get_as_array() {

        $threads = array();
        $sql = "SELECT COUNT(thread) AS cnt, category FROM forumthreads GROUP BY category";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $threads[$row['category']] = $row['cnt'];
        }

        $sql = "SELECT id, name, cid, cms, create_by, ".$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "create_date")." as create_date
        		FROM forumcategories
        		WHERE cms = 0
        		ORDER BY create_date DESC";
        $res = sql($sql);
        $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
        while($row = sqlget($res)) {
            if ($row['cid'] && !$courseFilter->is_filtered($row['cid'])) continue;
            //$row['threads'] = CForumThread::count($row['id']);
            $row['threads'] = $threads[$row['id']];
            $row['course'] = cid2title($row['cid']);
            $rows[] = $row;
        }
        return $rows;
    }

    function get($category) {
        if ($category) {
            $sql = "SELECT id, name, cid, cms, create_by, ".$GLOBALS['adodb']->SQLDate("Y-m-d H:i:s", "create_date")." as create_date
            		FROM forumcategories
            		WHERE id='".(int) $category."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) return $row;
        }
    }

    function update_lastpost($category) {
        if ($category) {
        	if (dbdriver == 'oci8')
            	$sql = "UPDATE Forumcategories SET CREATE_DATE=SYSDATE WHERE id=".(int) $category."";
            else
            	$sql = "UPDATE forumcategories SET create_date=".$GLOBALS['adodb']->DBTimestamp(time())." WHERE id=".(int) $category."";
            sql($sql);
        }
    }

    function getCourse($category) {
        if ($category) {
            $row = CForumCategory::get($category);
            return $row['cid'];
        }
    }

    static public function getCategoryIdBySubject($subject, $subjectId)
    {
        $sql = "SELECT id
                FROM forumcategories
                WHERE
                    (name = ".$GLOBALS['adodb']->Quote($subject).(strlen($subject) ? '' : ' OR name IS NULL').")
                    AND cid = '".(int) $subjectId."'";
        $res = sql($sql);
        if ($row = sqlget($res)) {
            return $row['id'];
        } else {
            sql("INSERT INTO forumcategories (name, cid) VALUES (".$GLOBALS['adodb']->Quote($subject).", ".(int) $subjectId.")");
            return sqllast();
        }
    }

}

class CForumItem {
    var $_attributes = array();

    function init($attributes) {
        if (is_array($attributes) && count($attributes)) {
            $formParser = new CForumFormDataParser();
            $formParser->init($attributes);
            $this->_attributes = $formParser->get_as_array();
        }
    }

    function set_attributes($attributes) {
        $this->_attributes = $attributes;
    }

    function get_as_array() {}

    function get_email($mid) {
        if ($mid) {
            $sql = "SELECT EMail FROM People WHERE MID='".(int) $mid."'";
            $res = sql($sql);
            if (sqlrows($res) && ($row = sqlget($res))) return $row['EMail'];
        }
    }

}

?>