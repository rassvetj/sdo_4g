<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
//require_once APPLICATION_PATH . '/modules/els/order/forms/Order.php';	
//require_once APPLICATION_PATH . '/modules/els/certificate/forms/Certificate.php';	
require_once APPLICATION_PATH . '/modules/els/student-certificate/forms/StudentCertificate.php';	
require_once APPLICATION_PATH . '/modules/els/student-certificate/forms/StudentQuestion.php';	
require_once APPLICATION_PATH . '/modules/els/student-certificate/forms/StudentSendDocument.php';	

class HM_View_Infoblock_CertificateBlock extends HM_View_Infoblock_ScreenForm
{
	//protected $id = 'order';
	protected $id = 'certificate';
	
	 public function certificateBlock($title = null, $attribs = null, $options = null)
    {		
		
		$this->_userService = $this->getService('User');		
		$user = $this->_userService->getCurrentUser();		
		$len = strlen($user->mid_external);
		if($len != 6 ){ //--только студент может иметь 6-значный код
			return false; 
		}
		
		//$form = new HM_Form_Order();
		
		//$form = new HM_Form_Certificate();
		$form = new HM_Form_StudentCertificate();
        $this->view->form = $form;
		
		
		$form_q = new HM_Form_StudentQuestion();
        $this->view->form_q = $form_q;
		
		$form_sd = new HM_Form_StudentSendDocument();
        $this->view->form_sd = $form_sd;
		
		
		
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$content = $this->view->render('certificateBlock.tpl');
		
        /*
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/claims/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/claims/script.js');
		*/
		
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
	}
}