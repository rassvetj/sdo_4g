<?php

class CCron {
    var $tasks = array();

    function init() {
        $this->_get_tasks();
    }

    function _check_runperiod($task_id, $task_runperiod) {
        $task_id = addslashes($task_id);
        $task_runperiod = intval($task_runperiod);
        
        $query = "SELECT crontask_runtime FROM crontask WHERE crontask_id = '{$task_id}'";
        $res = sql($query);
        if (sqlrows($res)) {
            $row = sqlget($res);
            return time() - $row['crontask_runtime'] >= $task_runperiod;
        }
        return true;
    }

    function _set_runtime($task_id) {
        $task_id = addslashes($task_id);
        
        if (sqlrows(sql("SELECT crontask_runtime FROM crontask WHERE crontask_id = '{$task_id}'"))) {
            sql("UPDATE crontask SET crontask_runtime = " . time() . " WHERE crontask_id = '{$task_id}'");
        }
        else {
            sql("INSERT INTO crontask (crontask_id, crontask_runtime) VALUES ('{$task_id}', " . time() . ")");
        }
    }

    function _get_tasks() {
        if ($GLOBALS['domxml_object']){
            if (is_array($tasks = $GLOBALS['domxml_object']->get_elements_by_tagname("crontask"))) {
                while(list(,$task) = each($tasks)) {
                    $id = $task->get_attribute('id');
                    if ($id && ($task->get_attribute('launch')=='true') && ($this->_check_runperiod($id, $task->get_attribute('runperiod')))) {
                        $this->tasks[] = $id;
                    }
                }
            }
        }
    }

    function run() {
        if (is_array($this->tasks) && count($this->tasks)) {
            foreach($this->tasks as $task) {
                $taskFile = $GLOBALS['wwf'].'/cron/tasks/'.$task.'.class.php';
                if (file_exists($taskFile)) {
                    require_once($taskFile);
                    $taskClass = 'CCronTask_'.$task;
                    if (class_exists($taskClass)) {
                        $currentTask = new $taskClass();
                        $currentTask->init();
                        $currentTask->run();
                        $this->_set_runtime($task);
                    }
                }
            }
        }
    }

}

class CCronTask_interface {
    function init() {
        // interface
    }

    function run() {
        // interface
    }
}

?>