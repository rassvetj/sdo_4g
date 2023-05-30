<?php
class HM_Form_Recruits  extends HM_Form
{
    public function init()
	{
		$user = $this->getService('User')->getCurrentUser();
		$recruitInfo = $this->getService('Recruits')->getRecruitInfo($user->mid_external);
		
		$this->setAction($this->getView()->url(array('module' => 'recruits', 'controller' => 'ajax', 'action' => 'confirmed')));
		
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('recruit');
		
		$beginElement = array('' => _('-- Выберите --'));
		
		$this->addElement('hidden', 'mid', array(                        
			'Value'		=> $user->mid,			
			'disabled'	=> 'disabled',
        ));
		
		$statuses = HM_Recruits_RecruitsModel::getStatuses();
		$this->addElement('select','status_id', array( 
			'label' 		=> _('Статус воинского учета'),
			'value' 		=> $recruitInfo->status, 
			'multiOptions' 	=> $beginElement + $statuses,
		));
		
		
		$attitudes = HM_Recruits_RecruitsModel::getAttitudes();
		$this->addElement('select','attitude_id', array( 
			'label' 		=> _('Отношение к воинской обязанности'),
			'value' 		=> $recruitInfo->attitude,  
			'multiOptions' 	=> $beginElement + $attitudes,
		));
		
		$categories = HM_Recruits_RecruitsModel::getCategories();
		$this->addElement('select','category_id', array( 
			'label' 		=> _('Категория годности к воинской службе'),
			'value' 		=> $recruitInfo->category,
			'multiOptions' 	=> $beginElement + $categories,
		));
		
		$this->addElement('select','recruitment_office_code', array( 
			'label' => _('РВК'),
			'value' => $recruitInfo->recruitment_office_code, 
			'multiOptions' => $beginElement + $this->getService('Recruits')->getRecruitmentOffices(),
		));
		
		$reserve_categories = HM_Recruits_RecruitsModel::getReserveCategories();
		$this->addElement('select','reserve_category_id', array( 
			'label' 		=> _('Категория запаса'),
			'value' 		=> $recruitInfo->reserve_category,
			'multiOptions' 	=> $beginElement + $reserve_categories,
		));
		
		$ranks = $this->getService('Recruits')->getRanks();		
		$this->addElement('select','rank_id', array( 
			'label'			=> _('Воинское звание'),
			'value' 		=> intval(array_search(trim($recruitInfo->rank), $ranks)),
			'multiOptions' 	=> $beginElement + $ranks,
		));
		
		$profiles = $this->getService('Recruits')->getProfiles();
		$this->addElement('select','profile_id', array( 
			'label'			=> _('Состав (профиль)'),
			'value'			=> intval(array_search(trim($recruitInfo->profile), $profiles)),
			'multiOptions'	=> $beginElement + $profiles,
		));
				
		$this->addElement('text', 'specialty', array(
            'Label'		=> _('Полное кодовое обозначение ВУС'),            
			'Value'		=> $recruitInfo->specialty,
			'maxlength' => 15,
        ));
	
		$this->addElement('submit', 'submit', array(
            'Label' => _('Подтвердить и сохранить'),
        ));
		
		parent::init();
	}
}