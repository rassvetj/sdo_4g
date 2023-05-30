<?php
require_once('1.php');
require_once($wwf.'/lib/classes/Competence.class.php');
require_once($wwf.'/lib/classes/CompetenceRole.class.php');
require_once($wwf.'/lib/PEAR/Pager/examples/Pager_Wrapper.php');
require_once('lib/classes/Formula.class.php');

$competence_role_controller = new CCompetenceRoleController();
$competence_role_controller->init();
$competence_role_controller->display();

?>