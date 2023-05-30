<?php
class HM_Form_Journal extends HM_Form
{
	private $_subjectList = array();
	
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'journal', 'action' => 'get')));
        $this->setName('journal');
		$programmList = array();
		$groupList    = array();
		
		$programmsRaw = $this->getService('Programm')->fetchAll(null, array('name'));
		foreach($programmsRaw as $programm){
			$programmList[$programm->programm_id] = $programm->name . ( empty($programm->id_external) ? '' : '(' . $programm->id_external . ')' );
		}
		
		$groupsRaw = $this->getService('StudyGroup')->fetchAll(null, array('name'));
		foreach($groupsRaw as $group){
			$groupList[$group->group_id] = $group->name . ( empty($group->id_external) ? '' : '(' . $group->id_external . ')' );
		}
		
		$this->addElement('select', 'programmId', array(
			'label' 		=> _('Выберите программу'),
			'required' 		=> false,
			'value'         => 0,
			'multiOptions' 	=> array(0 => '- выберите -') + $programmList,
		));
		
		$this->addElement('select', 'groupId', array(
			'label' 		=> _('Выберите группу'),
			'required' 		=> false,
			'value'         => 0,
			'multiOptions' 	=> array(0 => '- выберите -') + $groupList,
		));
		
		$this->addElement('select', 'subjectId', array(
			'label' 		=> _('Выберите сессию'),
			'required' 		=> false,
			'value'         => 0,
			'multiOptions' 	=> array(0 => '- выберите -'),
		));
		
		$this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}
	

}