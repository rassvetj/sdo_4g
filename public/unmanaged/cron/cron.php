<?php
require_once('../1.php');
require_once('cron.class.php');

$cron = new CCron();
$cron->init();
$cron->run();

?>