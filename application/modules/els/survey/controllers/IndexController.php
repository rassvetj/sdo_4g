<?php
class Survey_IndexController extends HM_Controller_Action_Crud
{
    public function init(){	
		parent::init();		
	}
	
	public function indexAction()
    {			
		$this->getService('Unmanaged')->setHeader(_('Анкетирование'));
		$this->_redirect('/');
		
    }
	
	public function startAction()
    {			
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js');
		$request		= $this->getRequest();
		$form_type		= $request->getParam('type', false);		
		$user 			= $this->getService('User')->getCurrentUser();
		
		switch ($form_type) {			
			case 'vpo':
				$this->getService('Unmanaged')->setHeader(_('Анкета студента выпускного курса ВПО РГСУ'));
				$fields_type = HM_Survey_SurveyModel::TYPE_FIELDS_PO;
				$type = HM_Survey_SurveyModel::TYPE_VPO;
				break;			
		}		
		
		$result = $this->getService('Survey')->getOne($this->getService('Survey')->fetchAll($this->quoteInto(array('mid_external = ?', ' AND type = ?'), array($user->mid_external, $type))));
		if($result->survey_id){
			$this->view->isVoted = true;
		} else {
			$form = new HM_Form_Base();	
			$this->view->form 			= $form;	
			$this->view->form_area_id 	= 'survey';
		}
    }
   
}