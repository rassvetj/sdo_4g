<?php
class HM_Form_Timetable extends HM_Form
{
	public function init()
	{
	    $this->setMethod(Zend_Form::METHOD_GET);
        $this->setName('timetable');
        $this->setAction($this->getView()->url());
		
		$group_code = false;
		$groups 	= $this->getService('StudyGroupUsers')->getUserGroups($this->getService('User')->getCurrentUserId());		
		if($groups){
			foreach($groups as $group){
				$group_code = $group['id_external'];
			}
		}
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$group_code 		= trim($request->getParam('group', $group_code));
		
		$monday = HM_Timetable_TimetableModel::getMondayCurrent();
		$sunday = HM_Timetable_TimetableModel::getSundayNextWeek();
		$serviceTimetable 	= $this->getService('Timetable');
		$criteria 			= $serviceTimetable->quoteInto(array('DateZ BETWEEN ?', ' AND ?'), array($monday, $sunday));
		
       
		$this->addElement('select','group', array( 
			'label' 		=> _('Выберите группу'),
			'value' 		=> $group_code, 
			'multiOptions' 	=> array('' => '-- выбрать --') + $serviceTimetable->fetchAll($criteria, 'Groups')->getList('group_code', 'Groups'),
		));
		

        parent::init(); // required!
	}

}