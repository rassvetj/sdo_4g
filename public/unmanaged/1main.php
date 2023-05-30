<?
// require_once ('1702alert.php');
// Константа, включающая debug-режим gettext
define('DEBUG_GETTEXT_ENABLE', false);
define('DEBUG_GETTEXT_FILE', $_SERVER['DOCUMENT_ROOT'].'/../../data/temp/messages.txt');
define('DEBUG_GETTEXT_PO_FILE', $_SERVER['DOCUMENT_ROOT'].'/../../data/temp/messages.po');

define('DEBUG_SELENIUM_ENABLE', false);
if (DEBUG_SELENIUM_ENABLE) {
    define('DEBUG_SELENIUM_ONKEYUP', 'onChange');
    define('DEBUG_SELENIUM_RANDOMANSWERS', true);
} else {
    define('DEBUG_SELENIUM_ONKEYUP', 'onKeyUp');
}
// PHP 4 => PHP 5 Converters
$version = explode('.', phpversion());
$php_version = (int) $version[0];
if ($php_version>=5) {
    require_once('lib/php5/xslt-php4-to-php5.php');
    require_once('lib/php5/domxml-php4-to-php5.php');
}

if (defined('DEBUG_GETTEXT_ENABLE') && DEBUG_GETTEXT_ENABLE) {
    if (!function_exists('_')){
        function _($str){
            if (!empty($str) && defined('DEBUG_GETTEXT_FILE') && strlen(DEBUG_GETTEXT_FILE)) {
                $s = str_replace(array("\n","\r","\"","\t"),array("\\n", "\\n", "\\\"", "\\t"),$str);
                if (!isset($GLOBALS['gettext'])) $GLOBALS['gettext'] = array();
                $GLOBALS['gettext'][$s] = true;
            }
            return $str;
        }
    }

    if (!function_exists('gettext')) {
        function gettext($str) {
            return _($str);
        }
    }

    if (!function_exists('ngettext')) {
        function ngettext($str) {
            return _($str);
        }
    }
}

if (!defined('APPLICATION_PATH')) { // pure unmanaged
    define('APPLICATION_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../../application/');
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path(),
    )));
    
}

//$ini = parse_ini_file(APPLICATION_PATH . '/settings/config.ini');
//$ini = $GLOBALS['ini'];

$languages = $GLOBALS['ini']['languages'];
$locale = $GLOBALS['ini']['resources']['locale']['default'];

if ($_SESSION['s']['mid'] > 0) {
    require_once($_SERVER['DOCUMENT_ROOT']."/adodb_func.php");

    $sql = "SELECT lang FROM People WHERE MID = '".$_SESSION['s']['mid']."'";
    $res = sql($sql);
    if ($row = sqlget($res)) {
        if (isset($languages[$row['lang']])) {
            $locale = $languages[$row['lang']]['locale'];
        }
    }
} elseif (isset($_COOKIE['hmlang'])) {
    if (isset($languages[$_COOKIE['hmlang']])) {
        $locale = $languages[$_COOKIE['hmlang']]['locale'];
    }
} elseif (!$GLOBALS['ini']['resources']['locale']['force']) {
    require_once('Zend/Locale.php');
    $l = new Zend_Locale();
    $accepted = $l->getBrowser();
    if (is_array($accepted) && count($accepted)) {
        foreach($accepted as $acceptedLocale => $weight) {
            foreach($languages as $lang => $langLocale) {
                if (strtolower($acceptedLocale) == strtolower($langLocale['locale'])) {
                    $locale = $langLocale['locale'];
                    break 2;
                }
            }
        }
    }
}

if (!empty($locale)) {
    if (!function_exists('_') || !function_exists('_n')){
        initTranslator($locale);
    } else {
        require_once('Zend/Exception.php');
        throw new Zend_Exception('PHP gettext extension must be switched off');
    }
} else {
    if (!function_exists('_')){
        function _($str){
            return $str;
        }
    }
    if (!function_exists('_n')){
        function _n($msgid, $str, $num){
            return $str;
        }
    }
}

if (!function_exists('gettext')) {
    function gettext($str) {
        return _($str);
    }
}
if (!function_exists('ngettext')) {
    function ngettext($msgid, $str, $num) {
        return _n($msgid, $str, $num);
    }
}

if (!function_exists('ngettext')) {
    function ngettext($str) {
        return _($str);
    }
}

if (!function_exists('bindtextdomain')) {
    function bindtextdomain($domain, $path) {
        return true;
    }
}

if (!function_exists('textdomain')) {
    function textdomain($domain) {
        return true;
    }
}

if (!function_exists('bind_textdomain_codeset')) {
    function bind_textdomain_codeset($domain, $codeset='') {
        return true;
    }
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';

$strSep = (OS == 'win') ? ";" : ":";
$strPath = ini_get("include_path");
ini_set("include_path", $strPath . $strSep . $_SERVER['DOCUMENT_ROOT']."/template_test$strSep".$_SERVER['DOCUMENT_ROOT']."/$strSep".$_SERVER['DOCUMENT_ROOT']."/lib/classes".$strSep.$_SERVER['DOCUMENT_ROOT']."/lib/PEAR");

if (!$GLOBALS['managed']) {
    $strPath = ini_get("include_path");
    ini_set("include_path", $strPath . $strSep . realpath($_SERVER['DOCUMENT_ROOT']."/../../library"));
}

$strPath = ini_get("include_path");

require_once($_SERVER['DOCUMENT_ROOT']."/_def.php");
require_once($_SERVER['DOCUMENT_ROOT']."/adodb_func.php");
require_once($_SERVER['DOCUMENT_ROOT']."/smarty/Smarty.class.php");
require_once($_SERVER['DOCUMENT_ROOT']."/smarty/Smarty_els.class.php");

$wwf=$_SERVER['DOCUMENT_ROOT'];
$templdir="$wwf/template/";
$testdir="$wwf/template_test/";
$logdir= APPLICATION_PATH . "/../data/log/zlog";
$flog=$logdir."/php-error.log";
$tmpdir="$wwf/temp";

if (defined('dbdriver') && (strstr(strtolower(dbdriver),'mysql'))) {
    sql("SELECT 2+2"); // ы?
    $dbServerInfo = $adodb->ServerInfo();
    if (isset($dbServerInfo['version']) && $dbServerInfo['version'][0]>3) {
        sql("set CHARACTER SET utf8");
    }
}

/**
 * Установка констант из значений options
 */
// =====================================
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/classes/Option.class.php');
$options = $GLOBALS['options'] = COption::get_all_as_array();
if (!$options['question_edit_additional_rows']) {
    $options['question_edit_additional_rows'] = 3;
}
require_once($_SERVER['DOCUMENT_ROOT']."/user_login_custom.php");

if (!defined("chatport")) define("chatport",(int) $options['chat_server_port']);
if (!defined("kclientport")) define("kclientport",(int) $options['drawboard_port']);
if (!defined("LOCAL_IMPORT_IMS_COMPATIBLE")) define("LOCAL_IMPORT_IMS_COMPATIBLE",(bool) $options['import_ims_compatible']);
if (!defined("NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL")) define("NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL",(bool) $options['regform_email_required']);
if (!defined("REGISTRATION_FORM")) define("REGISTRATION_FORM", $options['regform_items']);
if (!defined("NUMBER_ADDITIONAL_ROWS_IN_QUESTION")) define("NUMBER_ADDITIONAL_ROWS_IN_QUESTION", (int) $options['question_edit_additional_rows']);
if (!defined("LOCAL_ANSWERS_LOG_FULL")) define("LOCAL_ANSWERS_LOG_FULL", (boolean) $options['answers_local_log_full']);
if (!defined("СOURSE_ORGANIZATION_TREE_VIEW")) define("СOURSE_ORGANIZATION_TREE_VIEW", (boolean) $options['course_organization_tree_view']);
if (!defined("COURSES_DESCRIPTION")) define("COURSES_DESCRIPTION", $options['course_description_format']);
if (!defined("IS_TRANSLITERATE_SRC_VALUE")) define("IS_TRANSLITERATE_SRC_VALUE", (boolean) $options['transliterate']);
if (!defined("DISABLE_COPY_MATERIAL")) define("DISABLE_COPY_MATERIAL", (boolean) $options['disable_copy_material']);
if (!defined("ENABLE_CHECK_SESSION_EXIST")) define("ENABLE_CHECK_SESSION_EXIST", (boolean) $options['enable_check_session_exist']);
if (!defined("ENABLE_EAUTHOR_COURSE_NAVIGATION")) define("ENABLE_EAUTHOR_COURSE_NAVIGATION", (boolean) $options['enable_eauthor_course_navigation']);
if (!defined("ELS_VERSION")) define("ELS_VERSION", $options['version']);
if (!defined("ELS_BUILD")) define("ELS_BUILD", $options['build']);
if (!defined("ELS_REGNUM")) define("ELS_REGNUM", $options['regnumber']);
if (!defined("ENABLE_FORUM_RICHTEXT")) define("ENABLE_FORUM_RICHTEXT", (boolean) $options['enable_forum_richtext']);
if (!defined('WELCOME_TEXT')) define('WELCOME_TEXT',$options['welcomeText']);

// Webinars

if (!defined('WEBINAR_MEDIA')) define('WEBINAR_MEDIA', $options['webinar_media']);

// Connect Pro

if (!defined('CONNECT_PRO_HOST')) define('CONNECT_PRO_HOST', $options['cp_host']);
if (!defined('CONNECT_PRO_ADMIN_LOGIN')) define('CONNECT_PRO_ADMIN_LOGIN', $options['cp_admin_login']);
if (!defined('CONNECT_PRO_ADMIN_PASSWORD')) define('CONNECT_PRO_ADMIN_PASSWORD', $options['cp_admin_password']);
if (!defined('CONNECT_PRO_DEFAULT_PASSWORD')) define('CONNECT_PRO_DEFAULT_PASSWORD', $options['cp_default_password']);

if (strlen($options['windowTitle'])) {
    if (!defined('APPLICATION_TITLE')) define('APPLICATION_TITLE', $options['windowTitle']);
}
if (!defined('APPLICATION_COLOR_1')) define('APPLICATION_COLOR_1', isset($options['color1']) ? $options['color1'] : '');
if (!defined('APPLICATION_COLOR_2')) define('APPLICATION_COLOR_2', isset($options['color2']) ? $options['color2'] : '');

//unset($options);
// =====================================

if (!defined('DEPARTMENT_APPLICATION')) define('DEPARTMENT_APPLICATION', 0); // els

define('USE_NEW_METADATA',true); // не использовать ';' как признак конца значений в метаданных

define('COURSES_DIR_PREFIX', '');
define('ITEMS_TO_ALTERNATE_SELECT',100);
define('COURSES_PER_PAGE',25);
define('TRACKS_PER_PAGE',25);
define('COMPETENCE_ROLES_PER_PAGE',25);
define('POLLS_PER_PAGE',25);

define('HACP_DEBUG', true);

ini_set("display_errors", 0);
ini_set("display_startup_errors", 0);

if (getenv('APPLICATION_ENV') == 'development') {
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
}

@ini_set("session.use_trans_sid",0);

error_reporting(2039);
ini_set("error_reporting",2039);

set_magic_quotes_runtime(0);
$self=$HTTP_SERVER_VARS['PHP_SELF'];
$self = str_replace('/unmanaged', '', $self);

define("debug",0);

$wwwhost = $_SERVER["HTTP_HOST"];
$wwp="";
$wwf=$_SERVER['DOCUMENT_ROOT'];
$cookiehost=$_SERVER["HTTP_HOST"];

$sitepath = "{$protocol}://{$wwwhost}/";

if (!defined('WEBINAR_SERVER')) define('WEBINAR_SERVER', $GLOBALS['sitepath'].'webinar/server');

$foto_image_maxx=100;
$foto_image_maxy=150;

// любая строка из случайных символов для шифровки параметров
$cryptpass="@#)(sefn@#%NsgfnwgeWEN@";

$templdir="$wwf/template/";
$testdir="$wwf/template_test/";
$logdir= APPLICATION_PATH . "/../data/log/zlog";
$flog=$logdir."/php-error.log";
$tmpdir="$wwf/temp";

define('OPTION_FILES_REPOSITORY_PATH',$GLOBALS['wwf'].'/options/');
define('OPTION_FILES_REPOSITORY_URL',$sitepath.'/options/');

// teachers/file_up.php4
$file_up_log=$logdir."/file_log.txt";

// teachers/live_up.php4
$cam_up_log=$logdir."/cam_log.txt";


$meta_charset='<META HTTP-EQUIV="content-type" CONTENT="text/html; charset='.$GLOBALS['controller']->lang_controller->lang_current->encoding.'">';

$nameweek=array(_("воскресенье"),_("понедельник"),_("вторник"),_("среда"),_("четверг"),
                _("пятница"),_("суббота"),_("воскресенье"));

$mysessid=session_name()."2";

//   if (isset($$mysessid)) {
//      if (!preg_match("!^[a-f0-9]{32}$!s",$$mysessid)) exit("err1226: hack detect");
//      session_id($$mysessid);
//   }

unset($s,$admin,$dean,$stud,$teach,$boolInFrame);

ini_set("session.cookie_lifetime",0);      // кука умирает при закрытии браузера
//ini_set("session.gc_maxlifetime",60*60*8); // время жизни сессии
ini_set("session.auto_start",0);           // автостарт сессий не нужен
ini_set("session.cookie_path","/");        // путь для кук
ini_set("session.cookie_domain",$cookiehost);        // путь для кук

//   ini_set("session.use_cookies",0);          // использовать куки для сессий
@session_start();
@session_register("s");
@session_register("boolInFrame");
@setcookie(session_name(),session_id(),0,"/");
//@setcookie($mysessid,session_id(),time()+5*60,"/");
@setcookie($mysessid,session_id(),0,"/");

$s = &$_SESSION['s'];

/*$res = sql(sprintf("SELECT lang FROM People WHERE MID = %d", $_SESSION['s']['mid']));
if ($row = sqlget($res)) {
    $lang = $row['lang'];
    if ($lang && isset($languages[$lang]) && ($lang != 'rus')) {
        $locale = $languages[$lang]['locale'];

        initTranslator($locale);

    }
}*/

define("CONTROLLER_ON", true);
define("CONTROLLER_OFF", false);

require_once(__DIR__."/lib/classes/Registry.class.php");
require_once(__DIR__."/lib/classes/Lang.class.php");
require_once(__DIR__."/lib/classes/Controller.class.php");

//
//require_once("Registry.class.php");
//require_once("Lang.class.php");
//require_once("Controller.class.php");

if (!$GLOBALS['managed']) {
    require_once(realpath($GLOBALS['wwf'].'/../../library/Zend/Config/Ini.php'));
}

$appEnv = getenv('APPLICATION_ENV') ?  getenv('APPLICATION_ENV') : 'production';
CRegistry::set('config', new HM_Config_Ini($GLOBALS['wwf'].'/../../application/settings/config.ini', $appEnv, false, $GLOBALS['wwf'].'/../../application/settings/config.dev.ini'));

if (!$GLOBALS['managed']) {
    require_once(realpath($GLOBALS['wwf'].'/../../library/Zend/Registry.php'));
    Zend_Registry::set('config', CRegistry::get('config'));
}

if (!$GLOBALS['managed'] && isset(CRegistry::get('config')->iecompatmode)) {
    header('X-UA-Compatible: IE='.CRegistry::get('config')->iecompatmode);
}

//require_once("View.class.php");
require_once(__DIR__."/lib/classes/View.class.php");

$controller = new Controller();
$controller->initialize(CONTROLLER_ON);
if (!defined('APPLICATION_TITLE')) {
    define('APPLICATION_TITLE',_('eLearning Server'));
}
if (!defined('APPLICATION_ROLE_ALIAS')) {
    define('APPLICATION_ROLE_ALIAS', 0);
}
define('APPLICATION_COPYRIGHT', _("Разработка ЗАО \"ГиперМетод\" 2007"));

//require_once("define.inc.php");
//require_once("Util.class.php");
//require_once("Model.class.php");

require_once(__DIR__."/lib/classes/define.inc.php");
require_once(__DIR__."/lib/classes/Util.class.php");
require_once(__DIR__."/lib/classes/Model.class.php");


CRegistry::set('wwwroot', $GLOBALS['wwf']);

if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {
    if (!isset($_GET['exit'])) {
        define('ALLOW_SWITCH_2_LMS', false);
    } else {
        define('ALLOW_SWITCH_2_LMS', false);
    }
}

require_once(APPLICATION_PATH . '/../library/HM/Model/Abstract.php');
require_once(APPLICATION_PATH . '/model/HM/Htmlpage/HtmlpageModel.php');
$fname = HM_Htmlpage_HtmlpageModel::getActionsPath();
if (!file_exists($fname)) {
    $domxml_object = get_domXML_object();
    $actions = fopen($fname, 'w');
    fwrite($actions, $domxml_object->dump_node());
    fclose($actions);
}
$domxml_object = domxml_open_file($fname);

/**
 * HACP Datamodel url
 */
define('AICC_URL',"http://".$wwwhost."/hacp_datamodel.php");

define('APPLICATION_BRANCH_CORPORATE','corporate');
define('APPLICATION_BRANCH_ACADEMIC','academic');
define('APPLICATION_BRANCH',get_application_branch());

$preferences = get_application_preferences();
if (!defined("USE_BOLOGNA_SYSTEM"))  define("USE_BOLOGNA_SYSTEM", (boolean)$preferences['use_bologna_system']);
if (!defined('USE_WEBINAR'))         define('USE_WEBINAR', (boolean) $preferences['use_webinar']);
if (!defined('USE_CONNECT_PRO'))     define('USE_CONNECT_PRO', (boolean) $preferences['use_connect_pro']);
if (!defined('USE_CMS_INTEGRATION')) define('USE_CMS_INTEGRATION', (boolean) $preferences['use_cms_integration']);
if (!defined('USE_AT_INTEGRATION'))  define('USE_AT_INTEGRATION', (boolean) $preferences['use_at_integration']);
if (!defined('USE_SIS_INTEGRATION')) define('USE_SIS_INTEGRATION', (boolean) $preferences['use_sis_integration']);
if (!defined("USE_SPECIALITIES"))    define("USE_SPECIALITIES", is_specialities_exists());
//if (!defined("USE_SPECIALITIES")) define("USE_SPECIALITIES", (boolean)$preferences['use_specialities']);


if (USE_CMS_INTEGRATION) {
    if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {                  //
        if (!isset($_GET['exit'])) {                                         //
            if (!isset($_GET['chLevel']) && !isset($_GET['lms'])) {          //
                define('ALLOW_SWITCH_2_CMS', User::allowSwitch2CMS());       //
            }                                                                //
        } else {                                                             //
            define('ALLOW_SWITCH_2_CMS', false);                             //
        }                                                                    //
        if (defined('ALLOW_SWITCH_2_CMS') && ALLOW_SWITCH_2_CMS) {           //
            if (($_SESSION['s']['perm'] > 1) && !User::allowSwitch2LMS()) {  //
                //header("location: {$protocol}://{$wwwhost}/cms/");           //
                //die();                                                       //
            }                                                                //
        }                                                                    //
    }
}

if (USE_SIS_INTEGRATION) {
    if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {
        if (!isset($_GET['exit']) && !isset($_GET['lms'])) {
            define('ALLOW_SWITCH_2_SIS', User::allowSwitch2SIS());
        } else {
            define('ALLOW_SWITCH_2_SIS', false);
        }
        if (defined('ALLOW_SWITCH_2_SIS') && ALLOW_SWITCH_2_SIS) {
            if (($_SESSION['s']['perm']) && !User::allowSwitch2LMS()) {
                //header("location: {$protocol}://{$wwwhost}/sis/index.php?sis");
                //die();
            }
        }
    }
}

if (USE_AT_INTEGRATION) {
    if ($_SESSION['s']['login'] && $_SESSION['s']['mid']) {
        if (!isset($_GET['exit'])) {
            if (!isset($_GET['chLevel']) && !isset($_GET['lms'])) {
                define('ALLOW_SWITCH_2_AT', User::allowSwitch2AT());
            }
        } else {
            define('ALLOW_SWITCH_2_AT', false);
        }
        if (defined('ALLOW_SWITCH_2_AT') && ALLOW_SWITCH_2_AT) {
            if (!User::allowSwitch2LMS()) {
                //header("location: {$protocol}://{$wwwhost}/at/");
                //die();
            }
        }
    }
}

//require_once("Object.class.php");
//require_once("PeopleFilter.class.php");
//require_once("EventWeight.class.php");
//require_once('Roles.class.php');
require_once(__DIR__."/lib/classes/Object.class.php");
require_once(__DIR__."/lib/classes/PeopleFilter.class.php");
require_once(__DIR__."/lib/classes/EventWeight.class.php");
require_once(__DIR__."/lib/classes/Roles.class.php");





//   if (strlen(session_id())!=32 || !preg_match("!^[a-f0-9]{32}$!s",session_id()))
//      exit("err1225: hack detect");

$Aasess="";
$asess="&".session_name()."=".session_id()."&";
$asessf="<input type=hidden name=\"".session_name()."\" value=\"".session_id()."\">";
if (isset($HTTP_COOKIE_VARS[session_name()])) {
    $sess="";
    $sessf="";
}
else {
    $sess=$asess;
    $sessf=$asessf;
}

// дебаг для тестов:
if ($s['login']==='andyT')
    define("tdebug",0);
else
    define("tdebug",0);


$randurl="&rnd=".time()."&"; // для добавления в URL против кеширования
$mailSep="<!--  {SEP} -->";

//  уровни доступа

// $operm=0;

// студент
unset($access);


$access['s']=1;

// учитель
$access['t']=2;

//  декан
$access['d']=3;

//  админ
$access['a']=4;

$lang="ru";

// OLD from phplib and mysqlconfig.php4


// $servletpath = $sitepath."servlet/"; // Скоро умрёт ! =)

$GuestBookShownRows = 20; //

require_once($wwf."/fun_lib.inc.php4");
require_once($wwf."/design.inc.php4");
require_once($wwf."/mail.lib.php");

$controller->setUser();

if (!defined('APPLICATION_VERSION')) { // pure unmanaged
	
	// HM_Controller_Plugin_Unmanaged не отрабатывает, placeholder'ы не подставляются
	// @todo: добавить сюда остальные placeholders
	require_once 'HM/View/Helper/Abstract.php';
    require_once 'HM/View/FrontendVersion.php';
	require_once 'HM/View/Helper/HmBootstrap.php';
	
	$hmBootstrap = new HM_View_Helper_HmBootstrap();
    $hmBootstrap->hmBootstrap();

    $viewRoot = $controller->view_root;
    $viewRoot->addPlaceholder('hmJsBootstrap', $hmBootstrap->getJS());
    $viewRoot->addPlaceholder('hmCssBootstrap', $hmBootstrap->getCss());
}

//table names
$forumthreads = "forumthreads";
$forummessages = "forummessages";
$guestbook = "posts3";
$studentstable = "Students";
$claimtable = "claimants";
$fintable = "graduated";
$teacherstable = "Teachers";
$vedtable = "scheduleID";
$scheduletable = "schedule";
$coursestable = "Courses";
$peopletable = "People";
$optionstable = "OPTIONS";
$eventstable = "EventTools";
$newstable = "news2";
$mod_list_table="mod_list";
$mod_cont_table="mod_content";
$test_title_table="TestTitle";

//  $info_block="-~-"; // признак того,что новость никакая не новость а информационный блок

// 21 09 2002

$cam_table="cam_casting";
$file_transfer_table="file_tranfer";

// 27 09 2002

$scoursestable= "Courses_stat";

// 30 09 2002
$bookstable="Knigi";

include_once($wwf."/_dima_timestat.php");

//   $switch0="#FEFDF5"; // цвет фона выключенной галочки "вариант включен"
//   $switch1="#FEFDF5"; // цвет фона активной галочки "вариант включен"
//   $border0="#FEFDF5"; // цвет фона бордюра неактивных элементов ввода текста
//   $border1="#E6DED1"; // цвет фона бордюра активных элементов ввода текста


//
// Валюта и ее автоопределение
//

$valuta=array(
    0=>array("n/a",_("Не задано"),""),
    1=>array(_("руб"),_("Рубли/RUS"),_("руб")),
    2=>array("\$",_("Доллары/USA"),"\$"),
    3=>array("EUR",_("Евро/EUR"),"EUR"),
);

$GLOBALS['TEST_MODES'] = $TEST_MODES = array(0=>_("нельзя пропускать вопросы, нельзя возвращаться назад"),1=>_("с возможностью возврата к предыдущим вопросам"),2=>_("с возможностью пропускать вопросы"));

$s['infopages'] = getInfopages();
$s['infopages_with_url'] = getInfopages(true);
/**
 * Добавлен опциональный параметр для возможности возвращения url
 * @author Artem Smirnov <tonakai.personal@gmail.com>
 * @date 25.01.2013
 *
 * @param boolean $returnWithUrl [false
 *
 * @return array
 */
function getInfopages($returnWithUrl = false){
    $res = array();
    $sqlres = sql("SELECT `page_id`, `name`, `url` FROM `htmlpage` WHERE `group_id` = '0'");
    while($row = sqlget($sqlres)){
        if($returnWithUrl){
            $res[$row['page_id']] = array('name' => $row['name'],'url' => $row['url']);
        }else{
            $res[$row['page_id']] = $row['name'];
        }
    }
    return $res;
}

function detectvaluta($s) {
    $s=sl(trim($s));
    if (strpos($s,"eur")!==false || strpos($s,_("евр"))!==false || strpos($s,_("ЕВР"))!==false ||
        $s[0]=="е")
    {
        return 3;
    }
    if (strpos($s,"\$")!==false || strpos($s,"usd")!==false || strpos($s,_("долл"))!==false ||
        strpos($s,"doll")!==false || strpos($s,_("у.е"))!==false || strpos($s,_("уе"))!==false)
    {
        return 2;
    }
    if ($s[0]=="р" || $s[0]=="Р" || $s[0]=="r")
    {
        return 1;
    }
    return 0;
}


function get_course_status($i)
{
    $status=array(_("Не опубликован"),_("Опубликован для преподавателей"),_("Опубликован"));
    if (isset($status[$i])) return $status[$i];
    return "error status";

}

function get_course_type($i,$chain=0)
{
    $ret = _("Свободный").'. ';
    $free = true;
    if ($i<0) {
        $free = false;
        $ret = _("Назначаемый").'. ';
        $i = $chain;
    }
    if ($i) {
        $sql = "SELECT name FROM chain WHERE id='".(int) $i."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) {
            if ($free) $ret = '';
            return $ret._("Цепочка согласований:").' '.$row['name'];
        }
    }

    return $ret;

    //$type=array("Свободный доступ","Регистрация преподавателем","Регистрация учебной администрацией");
    //if (isset($type[$i])) return $type[$i];
    //return "error type";
}

function is_course_free($i,$chain=0)
{
    $free = true;
    if ($i<0) {
        $free = false;
        $i = $chain;
    }
    if ($i) {
        $sql = "SELECT name FROM chain WHERE id='".(int) $i."'";
        $res = sql($sql);
        if (sqlrows($res) && ($row = sqlget($res))) {
            $free = false;
        }
    }
    return $free;
}

function get_teachers_list($CID, $ratings = false) {
    global $coursestable;
    global $teacherstable;
    global $peopletable;
    $ret="";
    $courseCreateby=getField($coursestable,"createby","CID",$CID);
    $q = "SELECT CID,LastName,FirstName,Patronymic,EMail,People.MID FROM $teacherstable, $peopletable WHERE CID=$CID AND $teacherstable.MID=$peopletable.MID";
    if ($r=@sql($q))
        if (sqlrows($r)>0) {
            if ($ratings) $ret .= '<table width=100% border=0 cellpadding=0 cellspacing=0>';
            while ($rr=sqlget($r)){
                if ($ratings) $ret .= '<tr><td>';
                $ret.=($rr['EMail'] == $courseCreateby) ? "<b><a href=\"javascript:void(0);\" onClick=\"wopen('userinfo.php?mid={$rr['MID']}','user_{$rr['MID']}', '400', '300')\">{$rr['LastName']}&nbsp;{$rr['FirstName']}</a></b><br>" : "<a href=\"javascript:void(0);\" onClick=\"wopen('userinfo.php?mid={$rr['MID']}','user_{$rr['MID']}', '400', '300')\">{$rr['LastName']}&nbsp;{$rr['FirstName']}&nbsp;{$rr['Patronymic']}</a><br>";
                if ($ratings) $ret .= '</td>';
                if ($ratings) {
                    $ret.= '<td>'.getProgressBar((int) getAvgRating($CID, $rr['MID'])).'</td>';
                }
                if ($ratings) $ret .= '</tr>';
                //          $ret.=($rr['EMail'] == $courseCreateby) ? "<b>{$rr['LastName']}&nbsp;{$rr['FirstName']}</b><br>" : "{$rr['LastName']}&nbsp;{$rr['FirstName']}<br>";
            }
            if ($ratings) $ret .= '</table>';
        } else {
            $ret.="";
            //            $ret.="Добавить / удалить<br> преподавателей";
        }
    return stripslashes($ret);
}

function get_stud_list($CID) {
    global $studentstable;
    global $peopletable;
    $ret="";
    $query = "SELECT $studentstable.MID FROM $studentstable, $peopletable WHERE CID='".$CID."' AND $peopletable.MID = $studentstable.MID";
    //echo $query;
    if ($r=@sql($query))
        $ret=sqlrows($r);
    return $ret;
}

function get_last_acces($CID) {
    global $scoursestable;
    $ret="never";
    $sql="SELECT UNIX_TIMESTAMP(last_access) as last_access FROM $scoursestable WHERE CID='".$CID."' AND teacher='1'  ORDER BY `last_access` DESC";
    if ($r=@sql($sql)) {
        if (sqlrows($r)>0) {
            $rr=sqlget($r);
            $ret=mydate(date("Y-m-d",$rr['last_access']))."<br>".date("G:i",$rr['last_access']);
        }
    }
    return $ret;
}




#
# Обновление времени последнего входа авторизованных юзеров
# (происходит каждые 60 секунд)
# + получение значения уровня доступа
#
if ($s[login] && $s[mid]>0) {
    if (!isset($s[lasttime]) || $s[lasttime]+60<time()) {
        $s[lasttime]=date("YmdHis", time());
        $res=sql("UPDATE People SET last='$s[lasttime]', `countlogin`=`countlogin`+1 WHERE MID=".$s[mid]."","err1php160");
        sqlfree($res);
    }

//     moved to HM_Controller_Plugin_Session
//     $res=sql("UPDATE sessions SET stop=".$adodb->DBTimeStamp(time())." WHERE mid='".(int) $s['mid']."' AND sessid='".(int) $s['sessid']."'");
//     sqlfree($res);

    $s['user']['meta']['access_level'] = get_access_level($s['mid']);

    $s['tkurs']=array();
    $s['skurs']=array();
    $s['user']['scourse']="";
    if (2==$s['perm']) {
        $s['tkurs']= get_courses_by_status($s['mid'],$s['perm']);
        if (is_array($s['tkurs'])) $s['user']['scourse']=reset($s['tkurs']);
    }
    if (1==$s['perm']) {
        $s['skurs'] = get_courses_by_status($s['mid'],$s['perm']);
        if (is_array($s['skurs'])) $s['user']['scourse']=reset($s['skurs']);
    }
    if (3<=$s['perm']) {
        $s['tkurs'] = get_courses_by_status($s['mid'],$s['perm']);
        if (is_array($s['tkurs'])) $s['user']['scourse']=reset($s['tkurs']);
    }
}

define("MODE_SHOW_FRAMES", 1);
define("MODE_SHOW_NOFRAMES", 0);
define("MODE_IN_FRAME", 1);
define("DB_NULL", 1);

/*$query = "
    SELECT
      permission_groups.`pmid`,
      permission_groups.`type`
    FROM
      permission_groups
    WHERE
      permission_groups.`default` = '1'";
$result = sql($query);
while ($arr = sqlget($result)) {
    $default_groups[$arr['type']] = $arr['pmid'];
}*/

function get_access_level($mid) {
    return 0;
    if ($mid > 0)
        $sql="SELECT Access_Level FROM People WHERE MID='".(int) $mid."'";
    if (!$result=sql($sql)) return 10;
    if (sqlrows($result)<1) return 10;
    $row=sqlget($result);
    return $row['Access_Level'];
}

/**
 * branches:
 * unknown, corporate, academic
 */
function get_application_branch() {
    $ret = 'unknown';
    if ($GLOBALS['domxml_object']) {
        $nodes = $GLOBALS['domxml_object']->get_elements_by_tagname('actions');
        reset($nodes);
        while(list($k,$v) = each($nodes))
            if ($v->has_attribute('branch'))
                $ret = $v->get_attribute('branch');
    }
    return $ret;
}

function get_application_preferences() {
    $ret = array();
    if ($GLOBALS['domxml_object']) {
        $nodes = $GLOBALS['domxml_object']->get_elements_by_tagname('preference');
        reset($nodes);
        while(list($k,$v) = each($nodes))
            if ($v->has_attribute('value') && $v->get_attribute('id'))
                $ret[$v->get_attribute('id')] = $v->get_attribute('value');
    }
    return $ret;
}

/**
 * Установлен ли модуль СПЕЦИАЛЬНОСТИ?
 */
function is_specialities_exists() {
    if ($GLOBALS['domxml_object']) {
        if (is_array($pages = $GLOBALS['domxml_object']->get_elements_by_tagname("page"))) {
            while(list(,$page) = each($pages)) {
                if (strstr($page->get_attribute('url'),'tracks.php')!==false)
                    return true;
            }
        }
    }
}

/**
 * Установлен ли модуль испорт из Active Directory
 */
function is_active_directory_support_exists() {
    if ($GLOBALS['domxml_object']) {
        return $GLOBALS['domxml_object']->get_element_by_id('m020204');
    }
}

function get_domXML_object(){

    $domxml_object = domxml_open_file(FILE_ACTIONS);

    $actions = $domxml_object->get_elements_by_tagname('actions');
    $root = $actions[0];

    $custom_menu = array();
    $res = sql("SELECT htmlpage_groups.*, htmlpage.page_id, htmlpage.name as PAGE_NAME, htmlpage.text FROM htmlpage_groups
                INNER JOIN htmlpage ON (htmlpage.group_id = htmlpage_groups.group_id) ORDER BY htmlpage_groups.ordr, htmlpage.ordr");
    while($row = sqlget($res)){
        if(!isset($custom_menu[$row['group_id']])) $custom_menu[$row['group_id']] = array('name' => $row['name'], 'profiles' => $row['role'], 'pages' => array());
        $custom_menu[$row['group_id']]['pages'][] = $row;
    }

    $guest = false;

    $group_id = 88; // это id вкладки "Домой"
    foreach($custom_menu as $custom_group){

    	$group_id++;
/*  ничего этого не нужно, просто прописал guest в actions.xml  	
        if (($custom_group['profiles'] == 'guest') && !$guest) {

            $group = $root->new_child('group', '');
            $group->set_attribute('id', 'm'.$group_id);
            //$group->set_attribute('name', iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", APPLICATION_TITLE));
			$group->set_attribute('name', _('Главная страница'));
            $group->set_attribute('profiles', $custom_group['profiles']);

            $page_id=$group_id.'01';
            $page = $group->new_child('page', '');
            $page->set_attribute('id', 'm'.$page_id);
            //$page->set_attribute('name', iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", APPLICATION_TITLE));
			$page->set_attribute('name', _('Главная страница'));
            $page->set_attribute('url', '#');
            $group->append_child($page);

            $root->append_child($group);

            $guest = true;
        }
*/
        $group = $root->new_child('group', '');
        $group->set_attribute('id', 'm'.$group_id);
        $group->set_attribute('name', iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8",$custom_group['name']));
        $group->set_attribute('profiles', $custom_group['profiles']);

        //$subgroup = $group->new_child('subgroup', '');
        //$group->setIdAttribute('id', true);

        $page_id=$group_id.'01';
        foreach($custom_group['pages'] as $custom_page){
            $page = $group->new_child('page', '');
            $page->set_attribute('id', 'm'.$page_id);
            $page->set_attribute('name', iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8",$custom_page['PAGE_NAME']));
            $page->set_attribute('url', 'htmlpage/index/view/htmlpage_id/'.$custom_page['page_id'].'/?');
            $group->append_child($page);
            $page_id++;
        }

        //$group->append_child($subgroup);
        $root->append_child($group);
    }
    //echo $domxml_object->dump_node();
    //die();
    return $domxml_object;
}

function initTranslator($locale)
{
    require_once('Zend/Translate.php');
    require_once('Zend/Translate/Adapter/Gettext.php');
    require_once('Zend/Registry.php');

    $translate = new Zend_Translate(
        array(
            'adapter' => 'HM_Translate_Adapter_Gettext',
            'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
            'locale'  => $locale . '_unmanaged'
        )
    );

    $translate->addTranslation(
        array(
            'adapter' => 'HM_Translate_Adapter_Gettext',
            'content' => APPLICATION_PATH . '/../data/locales/' . $locale . '/LC_MESSAGES/',
            'locale'  => $locale,
        )
    );

    $translate->getAdapter()->generateJsTranslate($locale);
    Zend_Registry::set('translate', $translate);
    Zend_Registry::set('Zend_Translate', $translate);

    if (!function_exists('_')){
        function _($str){
            return Zend_Registry::get('translate')->_($str);
        }
    }
    if (!function_exists('_n')){
        function _n($msgid, $str, $num){
            return Zend_Registry::get('translate')->plural($msgid, $str, $num);
        }
    }

}

?>