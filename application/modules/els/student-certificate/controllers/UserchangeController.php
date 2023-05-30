<?php
class StudentCertificate_UserchangeController extends HM_Controller_Action_Crud  {

	protected $_userService = null;

    //protected $_studentCertificateID  = 0;
	

	public function init()
    {	
		$this->_userService = $this->getService('User');
		parent::init();
    }

    public function indexAction()
    {				
		
		$this->view->setHeader(_('Изменение профиля пользователей для загрузки из CSV без дублей.'));
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$config = Zend_Registry::get('config');
		
		$rows = $this->_userService->fetchAll($this->_userService->quoteInto('role_1c IS NULL'));
		
		
		//--Проверяем на приналдежность к тьютеру. Админов не берем.
		$tutorID = array();
		$sdudID = array();
		foreach($rows as $v){
			$roles = $this->_userService->getUserRoles($v->MID);				
			//var_dump($roles);			
			//echo $v->LastName;
			if(in_array('tutor',$roles) && !in_array('admin', $roles)){
				//$tutorID[$v->LastName] = $v->MID;				
				$tutorID[] = $v->MID;				
			}			
			elseif( //--Если это студент или конечный юзер
			(	in_array('student',$roles) ||
				in_array('enduser',$roles)
			) && ( //--И это не админ и не тьютор
				!in_array('admin', $roles) 		&&
				!in_array('tutor', $roles)		&&
				!in_array('dean', $roles)		&&
				!in_array('developer', $roles)	&&
				!in_array('manager', $roles)	&&
				!in_array('supervisor', $roles)	&&
				!in_array('teacher', $roles)				
			)){ 
				$len = strlen($v->mid_external);	
				if($len == 6 ){ //--у студента код 6 значный.
					$sdudID[] = $v->MID;					
				}			
			}
		}
		if(count($tutorID) > 0){
			echo 'Тьюторы:<hr>';
			foreach($tutorID as $t){
				//$this->_userService->update(array('role_1c' => 2, 'MID' => $t));				
				echo $t.'<br>';
			}
		}
		else {
			echo 'Нет тьюторов для обновления<hr>';			
		}
		
		
		if(count($sdudID) > 0){
			echo '<hr>Студенты:<hr>';
			foreach($sdudID as $s){
				//$this->_userService->update(array('role_1c' => 1, 'MID' => $s));				
				echo $s.'<br>';
			}
		}
		else {
			echo 'Нет студентов для обновления<hr>';			
			
		}
		
		//--проверяем на приналдежность к обычному студенту
		/*
		$sdudID = array();
		foreach($rows as $v){
			$len = strlen($v->mid_external);								
			//if($len == 6 && (!in_array($v->MID, $tutorID))){ //--Если код студента 6 цифр и не принадлежит к тьютерам, то это студент или enduser
			if($len == 6 ){ //--Если код студента 6 цифр и не принадлежит к тьютерам, то это студент или enduser
				$sdudID[] = $v->MID;					
			}				
		}
		*/
		
		
		//$this->_userService->
		
		//echo 'Студенты:<br>';
		//var_dump($sdudID);
		//echo '<br><br>Тьютеры:<br>';
		//var_dump($tutorID);
		
		
		
		//$userId = 6884;
		//$role = $this->_userService->getUserRoles($userId);
		
		//var_dump($role);
		
	}
	

}