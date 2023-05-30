<?php
class Internships_IndexController extends HM_Controller_Action
{
    public function init()
    {
		parent::init();
    }
    
    
    public function indexAction()
    {
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Стажировка'));
		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		$this->view->headScript()->appendFile($config->url->base.'themes/rgsu/js/dev.js');
		
		$current_user				= $this->getService('User')->getCurrentUser();
		
		
		$type_list_allow = HM_Internships_InternshipsModel::getTypeListAllow();
		if(empty($type_list_allow)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Нет доступных стажировок'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'services');
			die;
		}
		
		
		if(!$this->getService('Internships')->isHasAccess()){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Вы не можете просматривать этот раздел'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'services');
			die;
		}
		
		
		$this->view->form 			= new HM_Form_Internship();	


		$types = HM_Internships_InternshipsModel::getTypeList();
		
		$description = '';
		foreach($types as $type_code => $name){
			try {
				$description .= $this->view->render('index/description/type_'.$type_code.'.tpl');
			} catch (Exception $e) {
				continue;
			}
		}
		$this->view->description = $description;
		
		
		
	}

}