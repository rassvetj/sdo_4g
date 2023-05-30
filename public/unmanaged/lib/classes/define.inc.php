<?
define("QUIET", true);
define("TRASH", 'trash');
define("CONTENT", 'content');
define("CONTENT_COLLAPSED", 'contentcollapsed');
define("CONTENT_EXPANDED", 'contentexpanded');
define("MESSAGE", 'message');
define("NEWS", 'news');

////ELS
//define('DEFAULT_COLOR1','#3490c5');
//define('DEFAULT_COLOR2','#f78f15');


define("PREFIX_PROFILE", "profile_");

define("PROFILE_STUDENT", 'student');
define("PROFILE_TEACHER", 'teacher');
define("PROFILE_TUTOR", 'tutor');
define("PROFILE_DEAN", 'dean');
define("PROFILE_ADMIN", 'admin');
define("PROFILE_GUEST", 'guest');
//define('PROFILE_AUTHOR', 'author');
define('PROFILE_DEVELOPER', 'developer');
//define('PROFILE_EXPERT', 'expert');
//define('PROFILE_METODIST', 'metodist');
//define('PROFILE_METODIST_CDO', 'metodistCDO');
define('PROFILE_MANAGER', 'manager');
define('PROFILE_USER', 'user');
define('PROFILE_SUPERVISOR', 'supervisor');
define('PROFILE_ENDUSER', 'enduser');
define('PROFILE_EMPLOYEE', 'employee');

/* define("JS_CLOSE_SELF_REFRESH_OPENER", 'closeSelfRefreshOpener');
define("JS_CLOSE_SELF_GO_URL_OPENER", 'closeSelfGoUrlOpener');
define("JS_REFRESH_SELF", 'refreshSelf');
define("JS_GO_BACK", 'goBack'); */
define("JS_CLOSE_SELF_REFRESH_OPENER", 'function () { if(window.opener) { window.opener.location.reload(); _.defer(function () { window.close(); }) } }');
define("JS_CLOSE_SELF_GO_URL_OPENER", 'eLS.utils.closeSelfGoUrlOpener');
define("JS_REFRESH_SELF", 'eLS.utils.refreshSelf');
define("JS_GO_BACK", 'eLS.utils.goBack');

define("JS_GO_URL", false);

define('FILTER_SELECT_FIRST_REFRESH_SELF','selectFirstRefreshSelf');

//define("GROUP_INDEX", 'm01');
define("PAGE_INDEX", 'm0101');

define("DIR_TEMPLATES_QUIET", 'template/quiet');
define("PATH_TEMPLATES_QUIET", $_SERVER['DOCUMENT_ROOT'] . '/' . DIR_TEMPLATES_QUIET);

define("FILE_ACTIONS", APPLICATION_PATH . '/settings/actions.xml');
//define("FILE_ACTIONS", $_SERVER['DOCUMENT_ROOT'] /*. DIR_LANG */ . '/config/actions.xml');
define("DELIMITER_ACTIONS", ",");
define("STR_VARIABLE", "%");

if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIR_LANG . '/help/context')) {
    define("DIR_HELP", $_SERVER['DOCUMENT_ROOT'] . DIR_LANG . '/help/context');
} else {
    define("DIR_HELP", $_SERVER['DOCUMENT_ROOT'] . '/help/context');
}
define("DIR_MENU_ICONS", $_SERVER['DOCUMENT_ROOT'] . "/images/menu");

define("STR_OPTIONS_ALL", "---" . _("все") . "---");
define("STR_OPTIONS_SELECT", "---" . _("необходимо выбрать элемент") . "---");
define("DELIMITER_TITLE", " › ");

define("REQUIRED", true);

$profiles_basic = array(
    PROFILE_ENDUSER, 
    PROFILE_GUEST, 
    PROFILE_USER, 
    PROFILE_EMPLOYEE, 
    PROFILE_STUDENT,
    PROFILE_SUPERVISOR,         
    PROFILE_TEACHER,
    PROFILE_TUTOR,
    PROFILE_DEAN, 
    PROFILE_DEVELOPER, 
    PROFILE_MANAGER, 
    PROFILE_ADMIN
);
$profiles_basic_aliases = array(
    PROFILE_GUEST => _("Гость"), 
    PROFILE_ENDUSER=> _("Пользователь"),
    PROFILE_USER => _('Пользователь'), 
    PROFILE_EMPLOYEE => _("Сотрудник"), 
    PROFILE_STUDENT => _("Слушатель"),
    PROFILE_SUPERVISOR => _('Наблюдатель'),
    PROFILE_TEACHER => _("Преподаватель"),
    PROFILE_TUTOR => _("Тьютор"),
    PROFILE_DEAN => _("Организатор обучения"), 
    PROFILE_DEVELOPER => _('Разработчик ресурсов'), 
    PROFILE_MANAGER => _('Менеджер базы знаний'), 
    PROFILE_ADMIN => _("Администратор")
);
$profiles_basic_ids = array(
    PROFILE_GUEST => '0', 
    PROFILE_USER => '0.5', 
    PROFILE_ENDUSER => '0.45', 
    PROFILE_EMPLOYEE => '0.65', 
    PROFILE_STUDENT => '1',
    PROFILE_TUTOR => '1.3',
    PROFILE_SUPERVISOR => '0.75',
    PROFILE_TEACHER => '2', 
    PROFILE_DEAN => '3', 
    PROFILE_DEVELOPER => '3.3', 
    PROFILE_MANAGER => '3.6', 
    PROFILE_ADMIN => '4'
);

$profiles_inheritance = array(
    PROFILE_USER => array(PROFILE_ENDUSER), 
    PROFILE_EMPLOYEE => array(PROFILE_ENDUSER), 
    //PROFILE_SUPERVISOR => array(PROFILE_ENDUSER),//supervisor != enduser
    PROFILE_STUDENT => array(PROFILE_ENDUSER)
);


if (class_exists('Zend_Registry') && Zend_Registry::isRegistered('serviceContainer')) {
    $event = new sfEvent(null, HM_Extension_ExtensionService::EVENT_ADDING_ROLES_UNMANAGED, array('profiles' => $profiles_basic));
    Zend_Registry::get('serviceContainer')->getService('EventDispatcher')->notify($event);
    $profiles_basic = $event->getReturnValue();
}

// Задания
define('TESTS_VIEW_RESULTS', 'm150106');
define('TESTS_PERM_EDIT_OWN','m160107');
define('TESTS_PERM_EDIT_OTHERS','m160108');
// Обьявления
define('GUESTBOOK_PERM_EDIT_OWN','m100402');
define('GUESTBOOK_PERM_EDIT_OTHERS','m100403');
// Вопросы
define('FORUM_PERM_EDIT_OWN','m100102');
define('FORUM_PERM_EDIT_OTHERS','m100103');
define('FORUM_PERM_MODERATE','m100104');
// Занятия
define('SHEDULE_PERM_EDIT_OWN','m190102');
define('SHEDULE_PERM_EDIT_OTHERS','m190103');
define('SHEDULE_PERM_EDIT_OTHERS_PEOPLE','m190104');
// Законы
define('LAWS_PERM_EDIT_OWN','m170203');
define('LAWS_PERM_EDIT_OTHERS','m170204');
// Библиотека
define('LIB_PERM_EDIT_OWN','m170103');
define('LIB_PERM_EDIT_OTHERS','m170104');
define('LIB_PERM_EDIT_GIVE','m170105');
// Материалы
define('LIB_CMS_PERM_EDIT_OWN','m160403');
define('LIB_CMS_PERM_EDIT_OTHERS','m160404');
define('LIB_CMS_PERM_EDIT_GIVE','m160405');
// Аудиторный фонд
define('ROOMS_PERM_EDIT','m0601');
// Справочник школ
//define('SHCOOL_PERM_EDIT','m080103');
// Сетки занятий
define('PERIODS_PERM_EDIT','m080201');
// Добавлять, Редактировать курсы
define('COURSE_PERM_MANAGE','m060105');
define('COURSE_PERM_MANAGE_CONTENT','m060106');
// Заявки согласование/отклонение
define('ORDERS_PERM_AGREEM','m210301');
// Структура организации - добавление оргединиц
define('STRUCTURE_OF_ORGAN_PERM_ADD_ORGUNIT','m070108');
define('STRUCTURE_OF_ORGAN_PERM_EDIT','m070109');
// Компетенции
define('COMPETENCE_PERM_EDIT','m070205');
// Редактировать специальности
define('TRACK_PERM_EDIT','m230101');
// Вопросы
define('QUESTION_PERM_EDIT_OWN','m160108');
define('QUESTION_PERM_EDIT_OTHERS','m160109');
// Расписание - График загрузки
define('SHEDULE_ROOMS_PERM_ALL_COURSES','m190301');

define('RUNLIST_PERM_EDIT', 'm160501');

define('PERSISTENT_VAR_USE_COOKIE',true);

define('TITLE_SLOGAN','eLearning Server 3000');

define('EVENT_TYPE_POLL', 2053);
define('TYPE_DEAN_POLL_FOR_STUDENT', 2055);
define('TYPE_DEAN_POLL_FOR_LEADER',  2056);
define('TYPE_DEAN_POLL_FOR_TEACHER', 2057);

?>