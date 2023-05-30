<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
	
require_once APPLICATION_PATH . '/modules/els/ticket/forms/Ticket.php';	

class HM_View_Infoblock_TicketBlock extends HM_View_Infoblock_ScreenForm
{
	protected $id = 'ticket';
	
	public function ticketBlock($title = null, $attribs = null, $options = null)
    {	
		$this->_userService = $this->getService('User');		
		$user = $this->_userService->getCurrentUser();		
		$len = strlen($user->mid_external);
		if($len != 6 ){ //--только студент может иметь 6-значный код
			return false; 
		}
		
		$form = new HM_Form_Ticket();
        $this->view->form = $form;
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$content = $this->view->render('ticketBlock.tpl');
        
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
	}
}