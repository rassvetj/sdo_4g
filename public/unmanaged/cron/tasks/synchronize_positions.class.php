<?php

class CCronTask_synchronize_positions extends CCronTask_interface {
    function init() {
        
    }
    
    function run() {
        require_once('../lib/classes/Positions.class.php');
        require_once('../metadata.lib.php');
        $GLOBALS['sc'] = new SynchronizeControllerFilesystem();
        $GLOBALS['sc']->initialize($GLOBALS['wwf'].'/positions.csv');
        $model = &$GLOBALS['sc']->model;
        $GLOBALS['sc']->execute();
    }
}

?>