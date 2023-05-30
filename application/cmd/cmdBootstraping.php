<?php
 //ini_set('display_errors', 1);
// error_reporting(E_ALL);


#error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Указание пути к директории приложения
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));

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

defined('APPLICATION_MODULE') || define('APPLICATION_MODULE', 'ELS');

defined('APPLICATION_VERSION') || define('APPLICATION_VERSION', '4');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    realpath(APPLICATION_PATH.'/settings/config.ini')
);




$application->getBootstrap()->bootstrap('db');
$application->getBootstrap()->bootstrap('loader');
$application->getBootstrap()->bootstrap('config');
$application->getBootstrap()->bootstrap('cache');
$application->getBootstrap()->bootstrap('container');
$application->getBootstrap()->bootstrap('view');
$application->getBootstrap()->bootstrap('log');
$application->getBootstrap()->bootstrap('oracleAdapter');
$application->getBootstrap()->bootstrap('modules');
//ini_set('display_errors', 0);
//error_reporting(E_ALL);

include APPLICATION_PATH . "/../public/unmanaged/adodb_field_names.inc.php";

if (!isset($locale)) {
    $locale = Zend_Registry::get('config')->resources->locale->default;
}
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

Zend_Registry::set('translate', $translate);
Zend_Registry::set('Zend_Translate', $translate);

if (HARDCODE_WITHOUT_GETTEXT !== true) {
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