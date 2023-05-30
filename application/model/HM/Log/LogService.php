<?php
class HM_Log_LogService extends HM_Service_Abstract
{

    protected $_subjects = array();

    public function log($userId, $action, $status, $priority, $slaveType = '', $slaveId = 0){
        
		$enable =  $this->getService('Option')->getOption('security_logger');

        if(!$enable){
            return false;
        }

        if($userId == ''){
            $userId = 'Guest';    
        }
        
        if($slaveId > 0){
            Zend_Registry::get('log_security')->log(
                sprintf(
                   	" User Id: %s| Action: %s| Status: %s| Class Name: %s| Item Id: %s| Ip: %s",
                    $userId,
                    $action,
                    $status,
                    $slaveType,
                    $slaveId,
                    $_SERVER['REMOTE_ADDR']
                ),
                $priority
            );
        }elseif($slaveType !=''){
            Zend_Registry::get('log_security')->log(
                sprintf(
                   	" User Id: %s| Action: %s| Status: %s| Description: %s| Ip: %s",
                    $userId,
                    $action,
                    $status,
                    $slaveType,
                    $_SERVER['REMOTE_ADDR']
                ),
                $priority
            );
        }else{
            Zend_Registry::get('log_security')->log(
                sprintf(
                   	" User Id: %s| Action: %s| Status: %s| Ip: %s",
                    $userId,
                    $action,
                    $status,
                    $_SERVER['REMOTE_ADDR']
                ),
                $priority
            );
        }
    }

}