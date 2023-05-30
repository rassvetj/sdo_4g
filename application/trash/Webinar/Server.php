<?php

class Webinar_Server {
    
    /**
     * Возвращает список юзеров онлайн вебинара
     * @param int $pointId
     * @return array
     */
    public function getUserList($pointId) {
        $list        = array();
        $currentUser = Library::getAuth('default')->getIdentity();
        $teacherId   = Task_Service::getInstance()->getTeacherId($pointId);
        if ($currentUser['user_id'] > 0) {
            Webinar_User_Service::getInstance()->pingUser($pointId, $currentUser['user_id']);
        }
        
        $online = Webinar_User_Service::getInstance()->getUserListOnline($pointId);
        
        foreach(Webinar_User_Service::getInstance()->getUserList($pointId) as $user) {
        	$webinarUserVO = new Webinar_UserVO();
            $webinarUserVO->id         = $user->MID;
            $webinarUserVO->lastName   = $user->LastName;
            $webinarUserVO->firstName  = $user->FirstName;
            $webinarUserVO->middleName = $user->Patronymic;
        	
            /*
            $webinarUserVO->lastName   = iconv(Zend_Registry::get('config')->common->charset, Zend_Registry::get('config')->webinar->charset, $webinarUserVO->lastName);
            $webinarUserVO->firstName  = iconv(Zend_Registry::get('config')->common->charset, Zend_Registry::get('config')->webinar->charset, $webinarUserVO->firstName);
            $webinarUserVO->middleName = iconv(Zend_Registry::get('config')->common->charset, Zend_Registry::get('config')->webinar->charset, $webinarUserVO->middleName);
            */
            if ($webinarUserVO->id == $currentUser['user_id']) {
                $webinarUserVO->current = true;
            }
            if (isset($online[$webinarUserVO->id])) {
            	$webinarUserVO->status = 'online';
            }
            if ($webinarUserVO->id == $teacherId) {
                $webinarUserVO->role = 'leader';
            }
            $list[] = $webinarUserVO;
        }
        
        return $list;
    }

    /**
     * Возвращает план вебинара
     * @param int $pointId
     * @return array
     */
    public function getPlan($pointId) {
    	$list = array();
        $items = Webinar_Plan_Service::getInstance()->getItemList($pointId);
        if (is_array($items) && count($items)) {
        	foreach($items as $item) {
        		$list[$item->id] = new Webinar_Plan_ItemVO($item->toArray());
        	}
        }
        return $list;
    }
    
    /**
     * 
     * @param int $pointId
     * @param int $itemId
     * @return int
     */
    public function setCurrentItem($pointId, $itemId) {

        // add history item
        Webinar_History_Service::getInstance()->insertCurrentItem($pointId, $itemId);

        // set current item
        if (Webinar_Plan_Service::getInstance()->setCurrentItem($pointId, $itemId)) {
            return $itemId;
        }
        
        return 0;
    }
    
    /**
     * 
     * @param int $pointId
     * @return int
     */
    public function getCurrentItem($pointId) {
        return Webinar_Plan_Service::getInstance()->getCurrentItem($pointId);
    }

    /**
     * @param int $pointId
     * @param string $filename
     * @return int
     */
    public function recordStart($pointId, $filename = '') {
    	if (strlen($filename)) {
    		$catId = Webinar_Library_Category_Service::getInstance()->insertIfNotExists(array('catid' => Webinar_Library_Category_Service::WEBINAR_LIBRARY_CATEGORY_ID));
    		Webinar_Library_Service::getInstance()->insertIfNotExists(array('pointId' => $pointId, 'cats' => '#'.$catId.'#', 'is_active_version' => true, 'upload_date' => date('Y.m.d H:i:s')));
    	}
        return Webinar_History_Service::getInstance()->insertRecordStart($pointId, $filename);
    }

    /**
     * @param int $pointId
     * @return int
     */
    public function recordStop($pointId) {
        return Webinar_History_Service::getInstance()->insertRecordStop($pointId);
    }

    /**
     *
     * @param int $pointId
     * @return array
     */
    public function getRecords($pointId) {
    	$list = array();
        $items = Webinar_History_Service::getInstance()->getList($pointId);
        if (count($items)) {
        	foreach($items as $item) {
	            $historyItem = new Webinar_History_ItemVO($item->toArray());
	            if ($item->datetime instanceof DateTime) {
	                $historyItem->datetime = $item->datetime->format('Y-m-d H:i:s');
	            }
	            
	            $list[] = $historyItem;
        	}
        }
        return $list;
    }
    
    /**
     * 
     * @param int $pointId
     * @param string $message
     * @return int
     */
    public function addChatMessage($pointId, $message) {
        $userId = Library::getUserId();
        if ($userId > 0) {
            $message = 
            array(
                'userId' => $userId,
                'pointId' => $pointId,
                'message' => iconv(Zend_Registry::get('config')->webinar->charset, Zend_Registry::get('config')->common->charset, $message),
                'datetime' => date('Y-m-d H:i:s')
            );
            Webinar_Chat_Service::getInstance()->insert($message);            
        }
        return Webinar_Server::getChatMessages($pointId);
    }

    /**
     * 
     * @param int $pointId
     * @return array
     */
    public function getChatMessages($pointId) {
    	$list = array();
    	foreach(Webinar_Chat_Service::getInstance()->getList($pointId) as $item) {
    	    $message = new Webinar_Chat_MessageVO($item->toArray());
            if ($item->datetime instanceof DateTime) {
                $message->datetime = $item->datetime->format('Y-m-d H:i:s');
            }
    		$message->message = iconv(Zend_Registry::get('config')->common->charset, Zend_Registry::get('config')->webinar->charset, $message->message);
    		$list[] = $message;
    	}
    	return $list;
    }

}