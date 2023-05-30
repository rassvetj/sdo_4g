<?php
class Services_IndexController extends HM_Controller_Action
{
	
	public function init(){		
		parent::init();
	}
	
	
    public function indexAction()
    {        
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');	
		$this->getService('Unmanaged')->setHeader(_('Услуги'));	


		$current_user			= $this->getService('User')->getCurrentUser();	
		$recordbook_number  	= $this->getService('RecordCard')->getRecordbookNumber($current_user->mid_external);
		$this->view->isStudent	= empty($recordbook_number) ? false : true;
		$this->view->is_filial = $this->getService('User')->isMainOrganization() ? false : true;
		#$this->view->is_filial = false;
		
		if($this->getService('Internships')->isHasAccess()){
			$this->view->is_can_internships = true;
		}
		
		
    }
	
	
		
}