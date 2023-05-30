<?php

class HM_Forum_Showed_ShowedService extends HM_Forum_Library_ServiceAbstract
{
   
    public function addShowed($userId, $messageId){
        if(is_array($messageId)){
            foreach($messageId as $msgId) $this->addShowed($userId, $msgId);
            return;
        }
        $items = $this->fetchAll(array('user_id=?' => $userId, 'message_id=?' => $messageId));
        if (!count($items)) {
            $data = array(
                'user_id'    => $userId,
                'message_id' => $messageId,
                'created'    => $this->getDateTime()
            );
            $this->insert($data);
        }
    }
    
    public function getShowed($userId, $messageId){
        return (bool) $this->fetchRow(array('user_id = ?' => $userId, 'message_id = ?' => $messageId));
    }
    
    public function getShowedList($userId, $messageId){
        $where = array();
        
        if(is_array($userId)) $where['user_id IN(?)'] = $userId;
        elseif(is_numeric($userId)) $where['user_id = ?'] = $userId;
        
        if(is_array($messageId)) $where['message_id IN(?)'] = $messageId;
        elseif(is_numeric($messageId)) $where['message_id = ?'] = $messageId;
        
        return $this->fetchAll($where);
    }
    
}