<?php 

class Videochat_IndexController extends HM_Controller_Action{


	public function activityAction()
	{
	    

	    
	    $subject  = $this->_getParam('subject', 'subject');
	    $subjectId = $this->_getParam('subject_id', 0);
	    
	    
	    $pointId = $subject . '_' . $subjectId;
/*	    $rr=new Webinar_Server();
	    
	    $rr->getUserList($pointId);
	    exit;*/
/*        $user = $this->getOne($this->getService('WebinarUser')->find($pointId, $this->getService('User')->getCurrentUserId()));
        if (!$user) {
            $this->getService('WebinarUser')->insert(
                array(
                    'pointId' => $pointId,
                    'userId' => $this->getService('User')->getCurrentUserId(),
                    'last' => Zend_Date::now()
                )
            );
        }*/

        $this->view->pointId = $pointId;
        $this->view->userId = $this->getService('User')->getCurrentUserId();
        //$this->view->content = '';
        $this->view->media = Zend_Registry::get('config')->videochat->media;
        $this->view->server = Zend_Registry::get('config')->videochat->server;

        //if (defined('WEBINAR_MEDIA')) $this->view->media = WEBINAR_MEDIA;
        //if (defined('WEBINAR_SERVER')) $this->view->server = WEBINAR_SERVER;
	
	
	
	
	}
}

