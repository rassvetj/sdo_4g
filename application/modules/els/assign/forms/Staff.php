<?php
class HM_Form_Staff extends HM_Form
{
    public function init()
    {
        
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('staff_report');
        $this->setAction($this->getView()->url(array('module' => 'assign', 'controller' => 'staff', 'action' => 'get-report')));
		
		
		//$userList = $this->getService('Orgstructure')->getUserDepartamentList($this->getService('User')->getCurrentUserId()); //--студент => департамент		
		//$usersGroupList = $this->getService('StudyGroup')->getGroupListOnUserIDs(array_keys($userList)); //--список групп.		
		$usersGroupList = $this->getService('StudyGroup')->getGroupsByResponsibility($this->getService('User')->getCurrentUserId());
				
		if($usersGroupList && count($usersGroupList)){
			$list = array('0' => _('Все'));
			$list = $list + $usersGroupList;
		} else {
			$list = array('' => _('Нет доступных групп'));
		}
		
		
		$this->addElement(
			'select',
			'period',
			array(
				'Label' 		=> 'Сессии',
				'Required' 		=> false,
				'multiOptions' 	=> HM_Subject_SubjectModel::getPeriodsTime(),
				'class' 		=> 'h25',
			));
		
		
		$this->addElement(
			'select',
			'group_id',
			array(
				'Label' => 'Группа',
				'Required' => false,
				'multiOptions' => $list,				
				'multiple' => 'multiple',
			));
			
		
		
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать отчет')));

		
	}

}