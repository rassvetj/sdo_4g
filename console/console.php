#!/usr/bin/env php
<?php
if (!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
    define('E_USER_DEPRECATED', 16384);
}
function shutdown() {
//    var_dump(xdebug_get_function_stack());
    var_dump(error_get_last());
}
// register_shutdown_function('shutdown');


//error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);// & ~E_NOTICE

// Указание пути к директории приложения
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Определение текущего режима работы приложения (по умолчанию production)
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
define('APPLICATION_MODULE', 'ES');
define('HARDCODE_WITHOUT_SESSION', true);
/** Zend_Application */
require_once 'Zend/Application.php';

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH.'/settings/config.ini'
);

$application->bootstrap();

$cliApp = new \Symfony\Component\Console\Application(
	'eLearning Server Console Application', '4.4'
);

$cliApp->addCommands(array(
		new Es_Command_Wreport()
));

$cliApp->run();
?>
