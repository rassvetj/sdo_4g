<?php
require_once('1.php');
require_once('lib/HTML/MetaForm.php');
require_once('lib/HTML/MetaFormAction.php');
require_once('lib/HTML/FormPersister.php');


$GLOBALS['controller']->captureFromOb(CONTENT);
$smarty = new Smarty_els();

ob_start(array('HTML_FormPersister', 'ob_formpersisterhandler'));
$metaForm =& new HTML_MetaForm('elearning_server_secret_digital_signature');
ob_start(array(&$metaForm, 'process'));
$metaFormAction =& new HTML_MetaFormAction($metaForm);

switch ($metaFormAction->process()) {
    case 'INIT':
        // Called when script is called via GET method.
        // No buttons are pressed yet, initialize form fields.
        
        break;
        
    case 'ok':
        // Called when doSend is pressed and THERE ARE 
        // NO VALIDATION ERRORS! Process the form.
        
        break;
}

$smarty->assign('metaFormErrors',$metaFormAction->getErrors());
$smarty->assign('sitepath',$GLOBALS['sitepath']);
$smarty->assign('okbutton',okbutton());
echo $smarty->fetch('html_metaform.tpl');

ob_end_flush();
ob_end_flush();

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>