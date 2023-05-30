<?

// Порт чат-сервера. Дополнительно скопировать порт в ../javaserver/chat/ChatServer.cfg
// и переименовать файл chatserver_***.jar в соответствии с его номером!
// Для запуска чата запустить - ../javaserver/chat/runchat

// !!! define("chatport",50011);           // любой(выбранный вами) не занятый порт

// Порт доски рисования. Дополнительно скопировать порт в ../javaserver/kserver/runkserver
// и переименовать файл kserver_***.jar в соответствии с его номером!
// Для запуска доски запустить - ../javaserver/chat/runkserver
// !!! define("kclientport",50012);        // любой другой не занятый порт

// Remote Debug with FirePHP
//require('lib/FirePHPCore/FirePHP.class.php');
//$firephp = FirePHP::getInstance(true);

// в шапке под логотипом
define("LOCAL_SLOGAN", "Учебный Центр");
// в шапке после логотипа
define("LOCAL_LOGO_TEXT", "");

// Формат курсов при импорте IMS_COMPATIBLE - это когда курс состоит из item'ов
// !!! define("LOCAL_IMPORT_IMS_COMPATIBLE",true);

// Показывать всю регистрационную информацию (вроде phone, icq..)
define("LOCAL_REGINFO_CIVIL",true);

//константа определяет вид формы регистрации
// !!! define("REGISTRATION_FORM", "add_info;speciality");
//константа определяет будет ли в обязательном блоке регистрации поле e-mail
// !!! define("NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL", true);

//количество дополнительных полей для вопросов в заданиях
// !!! define("NUMBER_ADDITIONAL_ROWS_IN_QUESTION", 3);

// Отображать форму "зарегистрироваться на курс"; иначе - только "изменение регистрационных данных"
// Внимание! Имеет смысл только при LOCAL_REGINFO_CIVIL = true
define("LOCAL_FREE_REGISTRATION",true);

// Отображать ссылку "сменить пароль"
define("LOCAL_ALLOW_CHANGE_PW",true);

// альтернативный шаблон для страницы с новостями
define("LOCAL_NEWS_TEMPLATE",false);

// позволять пользователю управлять отображением новостей
define("LOCAL_NEWS_ALLOW_PREFERENCES",false);

// кафедры
define("LOCAL_WORDS_DEPARTMENT","Кафедры");

// отображать ли в логе выполнения теста полный текст вопроса
// !!! define("LOCAL_ANSWERS_LOG_FULL",true);

//
define("LOCAL_ALLOW_SCHEDULE_GEN",true);

// таймер при выполнении задания
define("LOCAL_ALLOW_CLIENT_TIMER",false);

// Вывод структуры курса как дерева с раскрывающимися ветвями (Javascript Tree)
// !!! define("СOURSE_ORGANIZATION_TREE_VIEW",false);

//Константа опрделяет как зовется студент
$_STUDENT_ALIAS  = array("IMEN"  => array("ONE" => "слушатель",  "MORE" => "слушатели"),
"ROD"   => array("ONE" => "обучаемого", "MORE" => "обучаемых"),
"DAT"   => array("ONE" => "обучаемому", "MORE" => "обучаемым"),
"VIN"   => array("ONE" => "обучаемого", "MORE" => "обучаемых"),
"TVOR"  => array("ONE" => "обучаемым",  "MORE" => "обучаемыми"),
"PREDL" => array("ONE" => "обучаемом",  "MORE" => "обучаемых"));

//Константа определяющая описание курса значения: simple (произвольный текст), standart (авторы, количество модулей, ... ), military, rgha
// !!! define("COURSES_DESCRIPTION", "simple");

//Константа определяет используется ли 2-ух мониторная версия сервера
define("IS_TWO_DISPLAYS", false);

//Константа, определяющая будет ли в xml файле структуры курса в артибуте src происходить транслитерация
// !!! define("IS_TRANSLITERATE_SRC_VALUE", false);


//***************************************
//
//  Definitions end. don't edit the following
//
//***************************************

define("OS", ((strpos($_SERVER['SERVER_SOFTWARE'], "Win") !== false) || (strpos($_SERVER['SERVER_SOFTWARE'], "Microsoft") !== false)) ? 'win' : '*nix');

$arrPathAbsolute = (OS == 'win') ? pathinfo(!empty($_SERVER['ORIG_PATH_TRANSLATED']) ? $_SERVER['ORIG_PATH_TRANSLATED'] : (!empty($_SERVER['PATH_TRANSLATED']) ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME'])) :  pathinfo($_SERVER['SCRIPT_FILENAME']);

$PHP_SELF = $_SERVER['PHP_SELF'];
$PHP_SELF = str_replace('/unmanaged', '', $PHP_SELF);
$_SERVER['PHP_SELF'] = str_replace('/unmanaged', '', $_SERVER['PHP_SELF']);

$arrPathRelative = pathinfo($_SERVER['PHP_SELF']);
$arrElsDirs = array('', 'admin', 'help', 'reports', 'teachers', 'cron', 'js', 'install', 'informers');

$arrNestedDirsAbsolute = explode_by_slashes($arrPathAbsolute['dirname']);
$arrNestedDirsRelative = explode_by_slashes($arrPathRelative['dirname']);

if ($GLOBALS['managed']) {
	$arrNestedDirsAbsolute[] = 'unmanaged';
}

if (is_array($arrNestedDirsAbsolute) && count($arrNestedDirsAbsolute) > 1) {
    if (in_array($arrNestedDirsAbsolute[count($arrNestedDirsAbsolute) - 1], $arrElsDirs)) {
        array_pop($arrNestedDirsAbsolute);
        array_pop($arrNestedDirsRelative);
    }
    if (empty($arrNestedDirsRelative[count($arrNestedDirsRelative) - 1])) {
        array_pop($arrNestedDirsRelative);
    }
    
    $strPathAbsolute = implode("/", $arrNestedDirsAbsolute);
    $strPathRelative = implode("/", $arrNestedDirsRelative);
    $_SERVER['DOCUMENT_ROOT'] = $strPathAbsolute;
    $DOCUMENT_ROOT = $strPathAbsolute;
    $_SERVER["HTTP_HOST"] .= $strPathRelative;
    if (isset($HTTP_HOST)) $HTTP_HOST .= $strPathRelative;
    else $HTTP_HOST = $_SERVER['HTTP_HOST'];

}

function explode_by_slashes($str) {
    if (strpos($str, "/") !== false) {
        return explode("/", $str);
    } else {
        return explode("\\", $str); 
    }
}

if(!realpath($_SERVER['DOCUMENT_ROOT'])){	
	$_SERVER['DOCUMENT_ROOT'] = APPLICATION_PATH . "/../public/unmanaged/";	
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/config.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/1main.php");

// db connect using .ini-file. for els instance, installed via msi
$strIniFile = $_SERVER['DOCUMENT_ROOT'] . "/1.ini";
if (!defined("dbhost") && @fopen($strIniFile, "r")) {
    $ini = parse_ini_file($strIniFile);
    foreach($ini as $key => $value) {
        define($key, $value);
    }
}
//checkPermitionToWatch($PHP_SELF);

?>