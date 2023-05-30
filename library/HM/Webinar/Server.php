<?php
// Класс вбит во флешку(
class Webinar_Server{
    
      
    /**
     * Возвращает список юзеров онлайн вебинара
     * @param int $pointId
     * @return array
     */
    public function getUserList($pointId) {
        $list        = array();
        
        if(strpos($pointId, 'webinar_') === 0){
            $webinarUserVO = new HM_Webinar_User_ItemVO();
            $user = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUser();
            $webinarUserVO->id         = $user->MID;
            $webinarUserVO->lastName   = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $user->LastName);
            $webinarUserVO->firstName  = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $user->FirstName);
            $webinarUserVO->middleName = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $user->Patronymic);
            $webinarUserVO->current = true;
            $webinarUserVO->status = 'online';
            $webinarUserVO->role = 'leader';
            $list[] = $webinarUserVO;
            return $list;
        }
        
        
        
        $currentUser = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
       
        $teacher   = Zend_Registry::get('serviceContainer')->getService('Lesson')->find($pointId);
        
        if(!$teacher){
            $teacherId = 0;
            $moderatorId = 0;
        }else{
            //$teacherId = $teacherId[0]->moderator ? $teacherId[0]->moderator : $teacherId[0]->teacher;
            $teacherId = $teacher[0]->teacher;
            $moderatorId = $teacher[0]->moderator;
        }
        
        Zend_Registry::get('serviceContainer')->getService('WebinarUser')->ping($pointId, $currentUser);
               
        $online = Zend_Registry::get('serviceContainer')->getService('WebinarUser')->getUserListOnline($pointId);

        $userList = Zend_Registry::get('serviceContainer')->getService('WebinarUser')->getUserList($pointId, $teacherId );

        foreach($userList as $user) {
        	$webinarUserVO = new HM_Webinar_User_ItemVO();
        	
            $webinarUserVO->id         = $user->MID;
            $webinarUserVO->lastName   = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $user->LastName);
            $webinarUserVO->firstName  = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $user->FirstName);
            $webinarUserVO->middleName = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $user->Patronymic);
            if ($webinarUserVO->id == $currentUser) {
                $webinarUserVO->current = true;
            }
            if (isset($online[$webinarUserVO->id])) {
            	$webinarUserVO->status = 'online';
            }
            if ($webinarUserVO->id == $moderatorId) {
                $webinarUserVO->role = 'moderator';
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

        if(strpos($pointId, 'webinar_') === 0){
            $pointId = (int)str_replace('webinar_', '', $pointId);
            $list = Zend_registry::get('serviceContainer')->getService('WebinarFiles')->getFilesForWebinar($pointId);
        }else{
            $list = Zend_registry::get('serviceContainer')->getService('WebinarFiles')->getFilesForLesson($pointId);
        }
        return $list;
    }

    /**
     * Сохраняет порядок элементов плана вебинара
     * @param array $plan
     * @return void
     */
    public function setPlan($pointId, $plan)
    {
         if(strpos($pointId, 'webinar_') === 0){
             $pointId = (int) str_replace('webinar_', '', $pointId);
         } else {
             $lesson = Zend_Registry::get('serviceContainer')->getService('Lesson')->getOne(Zend_Registry::get('serviceContainer')->getService('Lesson')->find($pointId));
             if ($lesson) {
                 $pointId = $lesson->getModuleId();
             }
         }

         if ($pointId && is_array($plan) && count($plan)) {
             foreach($plan as $item) {
                 if ($item->id > 0) {
                     Zend_registry::get('serviceContainer')->getService('WebinarFiles')->updateWhere(
                         array('num' => $item->num),
                         sprintf('webinar_id = %d AND file_id = %d', $pointId, $item->id)
                     );
                 }
             }
         }
    }
    
    /**
     * 
     * @param int $pointId
     * @param int $itemId
     * @return int
     */
    public function setCurrentItem($pointId, $itemId) {
        
        if(strpos($pointId, 'webinar_') === 0){
            return 0;
        }
        // add history item
        Zend_Registry::get('serviceContainer')
            ->getService('WebinarHistory')
            ->insertCurrentItem($pointId, $itemId);

        // set current item
        if (Zend_Registry::get('serviceContainer')
            ->getService('WebinarHistoryCurrent')
            ->setCurrentItem($pointId, $itemId)) {
                
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
        
        if(strpos($pointId, 'webinar_') === 0){
            return 0;
        }
        return Zend_Registry::get('serviceContainer')
            ->getService('WebinarHistoryCurrent')
            ->getCurrentItem($pointId);
    }

    /**
     * @param int $pointId
     * @param string $filename
     * @return int
     */
    public function recordStart($pointId, $filename = '') {
        /* Запись в библиотеку
    	if (strlen($filename)) {
    		$catId = Webinar_Library_Category_Service::getInstance()->insertIfNotExists(array('catid' => Webinar_Library_Category_Service::WEBINAR_LIBRARY_CATEGORY_ID));
    		Webinar_Library_Service::getInstance()->insertIfNotExists(array('pointId' => $pointId, 'cats' => '#'.$catId.'#', 'is_active_version' => true, 'upload_date' => date('Y-m-d H:i:s', time()+3600)));
    	}
    	/**/
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $userId = $userService->getCurrentUserId();
        if ($userId > 0) {
            $history = array(
                    'userId' => $userId,
                    'pointId' => $pointId,
                    'action' => 'record start',
                    'item' => $filename,
                    'datetime' => date('Y-m-d H:i:s')
            );

            return Zend_Registry::get('serviceContainer')->getService('WebinarHistory')->insert($history);
        }
        //return Webinar_History_Service::getInstance()->insertRecordStart($pointId, $filename);
        return false;
    }

    /**
     * @param int $pointId
     * @return int
     */
    public function recordStop($pointId, $xml_id) {
        //$ret = Webinar_History_Service::getInstance()->insertRecordStop($pointId);
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $userId = $userService->getCurrentUserId();
        if ($userId > 0) {
            $history = array(
                    'userId' => $userId,
                    'pointId' => $pointId,
                    'action' => 'record stop',
                    'datetime' => date('Y-m-d H:i:s')
            );
            $ret = Zend_Registry::get('serviceContainer')->getService('WebinarHistory')->insert($history);

            /* Формирование информационного ресурса */        
            Zend_Registry::get('serviceContainer')->getService('WebinarRecords')->createRecord($pointId,$xml_id);
            /**/
            return $ret;
        }

        return false;
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
        
        if(strpos($pointId, 'webinar_') === 0){
            return 0;
        }
        
        
        
        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $userId = $userService->getCurrentUserId();
        if ($userId > 0) {
            $message = 
            array(
                'userId' => $userId,
                'pointId' => $pointId,
                'message' => iconv(Zend_Registry::get('config')->webinar->charset, Zend_Registry::get('config')->charset, $message),
                'datetime' => date('Y-m-d H:i:s')
            );

            Zend_Registry::get('serviceContainer')->getService('WebinarChat')->insert($message);            
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
    	
        if(strpos($pointId, 'webinar_') === 0){
            return $list;
        }
    	
    	
    	$dataList = Zend_Registry::get('serviceContainer')
                        ->getService('WebinarChat')
                        ->getList($pointId);
    	
    	foreach($dataList as $item) {
    	    $message = new HM_Webinar_Chat_itemVO($item->getData());
    	   // pr($item->getData());
            if ($item->datetime instanceof DateTime) {
                $message->datetime = $item->datetime->format('Y-m-d H:i:s');
            }
    		$message->message = iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, $message->message);
    		
    		$list[] = $message;
    	}
    	return $list;
    }

    /**
     * 
     * @param void
     * @return string
     */
    public function getDbId($pointId) {
		return '1234';
    }
    
}