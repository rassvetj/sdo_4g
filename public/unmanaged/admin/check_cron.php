<?php

require ("../1.php");
require_once ("{$wwf}/cron/cron.class.php");

if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
if ($s[perm]<4) exitmsg(_("К этой странице могут обратится только администраторы"),"/?$sess");

$GLOBALS['controller']->captureFromOb(CONTENT);

$arr_tasks = array();

if ($GLOBALS['domxml_object']){
    if (is_array($tasks = $GLOBALS['domxml_object']->get_elements_by_tagname("crontask"))) {
        $i = 0;
        while(list(,$task) = each($tasks)) {
            $arr_tasks[$i]['id'] = $task->get_attribute('id');
            $arr_tasks[$i]['name'] = iconv("UTF-8", "cp1251", $task->get_attribute('name'));
            $arr_tasks[$i]['launch'] = $task->get_attribute('launch');
            $arr_tasks[$i]['runperiod'] = intval($task->get_attribute('runperiod'));
            $row = sqlget(sql("SELECT crontask_runtime FROM crontask WHERE crontask_id = '" . addslashes($arr_tasks[$i]['id']) . "'"));
            $arr_tasks[$i]['runtime'] = intval(@$row['crontask_runtime']);
            $i++;
        }
    }
}

require_once($wwf.'/lib/sajax/Sajax.php');    
sajax_init();
sajax_export("run_cron");
sajax_handle_client_request();
$sajax_javascript = sajax_get_javascript();

$smarty = new Smarty_els();
$smarty->assign('Tasks', $arr_tasks);
$smarty->assign('Sajax', $sajax_javascript);
echo $smarty->fetch('check_cron.tpl');

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function run_cron($task_id) {
    $taskFile = $GLOBALS['wwf'].'/cron/tasks/'.$task_id.'.class.php';
    require_once($taskFile);

    $taskClass = 'CCronTask_'.$task_id;
    if (class_exists($taskClass)) {
        $currentTask = new $taskClass();
        $currentTask->init();
        $currentTask->run();
    }

    return true;
}

?>