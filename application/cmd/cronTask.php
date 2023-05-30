<?php
include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');
//@var HM_Crontask_CrontaskService
$cronService = $services->getService('CronTask');

$cronService->init()->run();