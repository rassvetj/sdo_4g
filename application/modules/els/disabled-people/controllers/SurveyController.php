<?php
class DisabledPeople_SurveyController extends HM_Controller_Action_Crud
{
    public function init(){		
		parent::init();		
	}
	
	public function indexAction()
    {			
		$this->getService('Unmanaged')->setHeader(_('Кабинет ОВЗ: анкетирование'));
		
		$user = $this->getService('User')->getCurrentUser();
		
		$result = $this->getService('Survey')->getOne($this->getService('Survey')->fetchAll($this->quoteInto(array('mid_external = ?', ' AND type = ?'), array($user->mid_external, HM_Survey_SurveyModel::TYPE_DISABLED_PEOPLE))));
		
		if($result->survey_id){
			$this->view->isVoted = true;
		} else {
			$this->view->form 			= new HM_Form_Survey();
			$this->view->form_area_id = 'survey_area';
		}		
    }
   
}