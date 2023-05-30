<?php
class HM_Webinar_History_Current_CurrentService extends HM_Service_Abstract
{


    public function setCurrentItem($pointId, $itemId){
        $this->delete($pointId);
        return $this->insert(array('pointId' => $pointId, 'currentItem' => $itemId));
    }
    
    public function getCurrentItem($pointId) {
        $current = $this->getOne($this->find($pointId));

        if (isset($current)) {
            return $current->currentItem;
        }
        
    }
        
   
    
    
}