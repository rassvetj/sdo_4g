<?php 

class HM_Videochat_Server {



    public function getUserList($pointId) {
        $list        = array();
        
        $serviceContainer = Zend_Registry::get('serviceContainer');
        
        
        $currentUser = $serviceContainer->getService('User')->getCurrentUserId();
               
        if ($currentUser > 0) {
           // Webinar_User_Service::getInstance()->pingUser($pointId, $currentUser['user_id']);
        }
        
        
        list($subjectName, $subjectId) = explode('_', $pointId);
        
        $users = $serviceContainer->getService('VideochatUser')->getActivityUsers($subjectName, $subjectId);
        
        foreach($users as $user) {
            
            
            
            $data = array('id' => $user->MID,
                          'lastName' => iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->videochat->charset, $user->LastName),
                          'firstName' => iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->videochat->charset, $user->FirstName),
                          'middleName' => iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->videochat->charset, $user->Patronymic),
                          'status'     => 'online'  
            );
              
            if ($user->id == $currentUser) {
                $data['current'] = true;
            }
            if ($user->isPotentialModerator == 1) {
                $data['role'] = 'leader';
            }
             

        	$videochatUserVO = new HM_Videochat_UserModel($data);
                   	
            if (isset($online[$webinarUserVO->id])) {
            	$webinarUserVO->status = 'online';
            }

            
            $list[] = $videochatUserVO;
        }
        
        //pr($list);
        return $list;
    }















}