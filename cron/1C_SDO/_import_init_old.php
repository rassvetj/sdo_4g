<?php 
var_dump(2);
die;
//--Инициализация общих параментов для работы приложения.



require_once  '_import_function.php'; //--функции для работы скрипта.

$FTP_SERVER_NAME = 'srv-fs-1';
$FTP_LOGIN = '1c';
$FTP_PASS = 'Z931am008';
//$FTP_DIR = '1c_obmen';
$reportToEmail = '112@rgsu.net'; //--Все остальные студенты
$reportToEmailDo = '112@rgsu.net'; // --Студенты ДО и преподаватели. Лашин Е.О.
$reportCommonToEmail = '112@rgsu.net'; //--технические логи.


if (!defined('REAL_PATH_TO_LOGS')) {
    define('REAL_PATH_TO_LOGS', realpath(dirname(__FILE__) . '/files/logs/'));
}

if (!defined('PATH_TO_FILES')) {
    define('PATH_TO_FILES', dirname(__FILE__) . '/files/');
}

if (!defined('REAL_PATH_TO_FILES')) {
    define('REAL_PATH_TO_FILES', realpath(PATH_TO_FILES));
}

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}

if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);// & ~E_NOTICE

// Указание пути к директории приложения
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
defined('PUBLIC_PATH') || define('PUBLIC_PATH', dirname(__FILE__));

/**
 * Фича для тестовых серверов
 */
if (false !== strstr($_SERVER['HTTP_HOST'], '-mssql')) {
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'mssql');
}

if (false !== strstr($_SERVER['HTTP_HOST'], '-oracle')) {
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'oracle');
}

if (false !== strstr($_SERVER['HTTP_HOST'], '-mysql')) {
    defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'mysql');
}

// Определение текущего режима работы приложения (по умолчанию production)
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

if (!isset($_SERVER['REQUEST_URI'])) // IIS HACK
{
    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
    if (isset($_SERVER['QUERY_STRING'])) { $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; }
    //this is same as HTTP_X_ORIGINAL_URL
}

if (preg_match("/^\/cms\/.*/i", $_SERVER['REQUEST_URI'])) {
    defined('APPLICATION_MODULE') || define('APPLICATION_MODULE', 'CMS');
}

if (preg_match("/^\/at\/.*/i", $_SERVER['REQUEST_URI'])) {
    defined('APPLICATION_MODULE') || define('APPLICATION_MODULE', 'AT');
}

if (preg_match("/^\/wrapper\/.*/i", $_SERVER['REQUEST_URI'])) {
    defined('APPLICATION_MODULE') || define('APPLICATION_MODULE', 'WRAPPER');
}

defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', '4');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


// Zend_Application 
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH.'/settings/config.ini'
);


/** eLearning Server */
$paths = get_include_path();
set_include_path(implode(PATH_SEPARATOR, array($paths, APPLICATION_PATH . "/../public/unmanaged/")));

$GLOBALS['managed'] = true;
ob_start();

$_SERVER['DOCUMENT_ROOT'] = APPLICATION_PATH . "/../public/unmanaged/";

require_once APPLICATION_PATH . "/../public/unmanaged/1.php";

Zend_Registry::set('unmanaged_controller', $controller);
Zend_Registry::set('baseUrl', $GLOBALS['sitepath']);
ob_end_clean();

set_include_path(implode(PATH_SEPARATOR, array($paths)));

/** Enjoy */
$application->bootstrap()->run();
//======================INIT ENDED=========================================
ob_end_clean();

