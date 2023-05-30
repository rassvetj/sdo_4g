<?php
class EntryEvent_InnerController extends HM_Controller_Action_Crud
{
    public function init(){		
		parent::init();		
	}
	
	public function indexAction()
    {			
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$request  			= $this->getRequest();
		$event_id 			= intval($request->getParam('event_id', false));
		$this->view->form 	= new HM_Form_Entry();
		
		echo $this->view->render('inner/event_'.$event_id.'.tpl');		
    }
	
	
	
	
    
   
}