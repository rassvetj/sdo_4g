<?php

class CCronTask_dummy extends CCronTask_interface {
    function init() {
        
    }
    
    function run() {
        echo "Cron task is launched...";
    }
}

?>