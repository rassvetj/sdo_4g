<?php
/**
 * Возвращает ajax информацию при выводе списка сессий
 * Реализован кэш в 15 минут 
*/

class Subject_InfoController extends HM_Controller_Action {
	
	private $_serviceSubject 	= null;
	private $_session_namespace = 'person-subject-marks';
	private $_lifetime 			= 54000; # 15 минут
	
	/**
	 * Только для студентов. Возвращает набранные баллы по сессиям.
	*/
    public function getMarksAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
		
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$user_id = $this->getService('User')->getCurrentUserId();
		
		if(empty($user_id)){
			header('HTTP/1.0 404 Not Found');
            exit;
		}
		
		if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
			header('HTTP/1.0 404 Not Found');
            exit;
		} 
		
        $subject_ids = $this->_getParam('ids', 0);		
		$subject_ids = array_map('intval', $subject_ids);
		$subject_ids = array_filter($subject_ids);
		
		if(empty($subject_ids)){
			header('HTTP/1.0 404 Not Found');
            exit;
		}
		
		# Кэш в авторизационной сессии
		# Студенты заходят с одного ПК, поэтому и нужно делать разделение по user_id
        $user_marks     = new Zend_Session_Namespace($this->_session_namespace.'_'.$user_id);        
		if($user_marks->lifetime < time()){
			$user_marks->marks    = false;
			$user_marks->lifetime = time() + $this->_lifetime;
		}
		
		$result = array();
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject'); }
		
		foreach($subject_ids as $id){
			if(isset($user_marks->marks[$id])){
				$result[$id] 			= $user_marks->marks[$id];	
			} else {
				$result[$id] 			= $this->_serviceSubject->getUserBallsDetail($id, $user_id);
				$user_marks->marks[$id] = $result[$id];
			}
		}
		#$user_marks->marks = false;
		# получаем баллы, поимещаем их в кэш на 10 минут.
		echo json_encode($result);
        die;
    }
	
	
	
} 