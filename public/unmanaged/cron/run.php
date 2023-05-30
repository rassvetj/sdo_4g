<?php
require_once('../1.php');
require_once('cron.class.php');

$task = "mma_export";

$taskFile = $GLOBALS['wwf'].'/cron/tasks/'.$task.'.class.php';
require_once($taskFile);

$taskClass = 'CCronTask_'.$task;
if (class_exists($taskClass)) {
    $currentTask = new $taskClass();
    $currentTask->init();
    $currentTask->run();
}

?>