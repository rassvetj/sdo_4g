<?php
class User_EmailExtController extends HM_Controller_Action_User {

    
	
	public function indexAction()
    {		
		$this->_userService = $this->getService('User');
		
		//$this->getHelper('viewRenderer')->setNoRender();
		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Добавление E-Mail адреса'));
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$user = $this->_userService->getCurrentUser();
	
		
		if($user->mid_external){
			$email = (!empty($user->EMail)) ? ($user->EMail) : (false);
			//$email = false;
				
			if(!$email){ //--Если поле в профиле пустое, то ищем во внешней таблице
				$select = $this->_userService->getSelect()->from('student_ext_emails')->where('mid_external=?',$user->mid_external);
				$r = $select->query()->fetch(); 			
				
				$email = isset($r['email']) ? $r['email'] : false;
			}
			
			if(!$email){
				//--выводим форму для изменения email				
				$form = new HM_Form_EmailExt();
			
				$this->view->form = $form;						
				$this->view->render('email-ext/email_ext_form.tpl');
				
			} else {
				$this->view->content = _('Ваш E-Mail: <b>'.$email.'</b>');				
			}			
		}
	}
	
	public function saveAction()
    {
		$this->getHelper('viewRenderer')->setNoRender();
        
        $form = new HM_Form_EmailExt();
		
		$request = $this->getRequest();

        if ($request->isPost() || $request->isGet()) {
			if ($form->isValid($request->getParams())) {
				
				$email = filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL) ? $request->getParam('email') : false;	
				
				$isSave = $this->saveEmail($email);
				
				if($isSave){
					$return['code']    = 1;
					$return['message'] = _('E-Mail успешно изменен. Теперь Вы можете пользоваться всем функционалом сайта. Через 5 секунд Вы будете перенаправлены на домашнюю страниццу');					
				} else {
					$return['code']    = 0;
					$return['message'] = _('Ошибка сохранения E-mail.');	
				}
			} else {
				$return['code']    = 0;
				$return['message'] = _('Неверный E-Mail.');	
			}
			
			if($return['code'] == 1){				
				echo $this->view->notifications(array(array(
					'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
					'message' => $return['message']
				)), array('html' => true));
				
				echo '<script>				
					setTimeout(function(){
					  window.location = "/";
					}, 5000);								
				</script>';
								
			} else {
				echo $this->view->notifications(array(array(
					'type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => $return['message']
				)), array('html' => true));
				
				echo $form->render();
			}
			
		} 
		
		//echo $form->render();
			
	}
	
	public function saveEmail($email, $is_update_profile = true)
	{
		return $this->getService('User')->saveEmail($email, $is_update_profile);
	}
}