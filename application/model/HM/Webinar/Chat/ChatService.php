<?php
class HM_Webinar_Chat_ChatService extends HM_Service_Abstract
{
    
    public function getList($pointId){
        
        $fetch = $this->fetchAll(array('pointId = ?' => $pointId));
        
        
        if(!$fetch){
            return false;
        }
        return $fetch;
    }
    
    

}