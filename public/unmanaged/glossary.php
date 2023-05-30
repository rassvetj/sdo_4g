<?php
require_once('1.php');
require_once('lib/classes/Glossary.class.php');
require_once('lib/classes/FormParser.class.php');

$GLOBALS['controller']->setHelpSection('glossary');
if (!$_SESSION['s']['login']) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

if (isset($_REQUEST['mini'])) {
    $GLOBALS['controller']->setView('DocumentBlank');
} else {
    $GLOBALS['controller']->setHeader(_('Глоссарий'));
}

$GLOBALS['controller']->captureFromOb(CONTENT);

if (isset($_REQUEST['mini'])) {
    $glossary = new MiniGlossaryController();
} else {
    $glossary = new GlossaryController();
}
$glossary->initialize(CONTROLLER_ON);

$glossary->terminate();

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

?>