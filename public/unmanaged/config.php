<?php

if (!defined('APPLICATION_PATH')) { // pure unmanaged
    define('APPLICATION_PATH', $_SERVER['DOCUMENT_ROOT'] . '/../../application/');
    set_include_path(implode(PATH_SEPARATOR, array(
        realpath(APPLICATION_PATH . '/../library'),
        get_include_path(),
    )));
}

if (!defined('APPLICATION_ENV')) {
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
}

/*function configIniProcessSection($iniArray, $section, $config = array())
{
    $thisSection = $iniArray[$section];
    foreach ($thisSection as $key => $value) {
        if (strtolower($key) == ';extends') {
            if (isset($iniArray[$value])) {
               $config = configIniProcessSection($iniArray, $value, $config);
            }
        } else {

            $parts = explode('.', $key);

            $array = array($parts[0] => array());

            $pointer = &$array[$parts[0]];

            for($i = 1; $i < count($parts); $i++) {
                $pointer[$parts[$i]] = array();
                $pointer = &$pointer[$parts[$i]];
            }

            $pointer = $value;

            $config = array_merge_recursive($config, $array);

        }
    }
    return $config;
}

$loaded = parse_ini_file(APPLICATION_PATH . '/settings/config.ini', true);
$iniArray = array();
foreach($loaded as $key => $data) {
    $pieces = explode(':', $key);
    $thisSection = trim($pieces[0]);
    switch (count($pieces)) {
        case 1:
            $iniArray[$thisSection] = $data;
            break;

        case 2:
            $extendedSection = trim($pieces[1]);
            $iniArray[$thisSection] = array_merge(array(';extends'=>$extendedSection), $data);
            break;
    }
}

$GLOBALS['ini'] = configIniProcessSection($iniArray, APPLICATION_ENV);*/

require_once('Zend/Config/Ini.php');
require_once('HM/Config/Ini.php');

$GLOBALS['ini'] = new HM_Config_Ini(APPLICATION_PATH . '/settings/config.ini', APPLICATION_ENV, false, APPLICATION_PATH . '/settings/config.dev.ini');
$GLOBALS['ini'] = $GLOBALS['ini']->toArray();

if (!defined("dbdriver"))   define("dbdriver",  $GLOBALS['ini']['resources']['db']['unadapter']);
if (!defined("dbhost"))     define("dbhost",    $GLOBALS['ini']['resources']['db']['params']['unhost']);
if (!defined("dbuser"))     define("dbuser",    $GLOBALS['ini']['resources']['db']['params']['username']);
if (!defined("dbpass"))     define("dbpass",    $GLOBALS['ini']['resources']['db']['params']['password']);
if (!defined("dbbase"))     define("dbbase",    $GLOBALS['ini']['resources']['db']['params']['dbname']);
if (!defined("httphost"))   define("httphost",  "http://");

// // Server in the this format: <computer>\<instance name> or 
// // <server>,<port> when using a non default port number
// $server = '127.0.0.1';

// // Connect to MSSQL
// $link = mssql_connect($server, 'root', '1');

// if (!$link) {
    // die('Something went wrong while connecting to MSSQL');
// }

?>