<?php

class HM_User_Loginlog_LoginlogModel extends HM_Model_Abstract
{
  
    const EVENT_LOGIN = 0;
    const EVENT_EXIT  = 1;
    
    const STATUS_FAIL = 0;
    const STATUS_OK   = 1;
    
    
    public function getIp()
    {
        return long2ip($this->ip);
    }
    
    
}