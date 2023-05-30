<?php
class Timetable_StudentController extends HM_Controller_Action
{
    public function init()
    {		
		
	}
    
    
    public function indexAction()
    {        
		$this->view->setHeader(_('Расписание занятий'));	

		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$week = trim($this->_request->getParam('week', false));
		
		$group_code = false;
		$groups 	= $this->getService('StudyGroupUsers')->getUserGroups($this->getService('User')->getCurrentUserId());		
		if($groups){
			foreach($groups as $group){
				$group_code = $group['id_external'];
			}
		}
		
		$group_code 		= trim($this->_request->getParam('group', $group_code));
		
		$serviceTimetable 	= $this->getService('Timetable');
		
		$select	= $serviceTimetable->getSelect();
		
		$fields = array(
			'Time_1' 		=> 't.Time_1',
			'Time_2' 		=> 't.Time_2',
			'Caption' 		=> 't.Caption',
			'Groups' 		=> 't.Groups',
			'group_code' 	=> 't.group_code',
			'Place' 		=> 't.Place',
			'Name' 			=> 't.Name',
			'Teacher' 		=> 't.Teacher',
			'Type' 			=> 't.Type',
			'Rank' 			=> 't.Rank',
			'Nch' 			=> 't.Nch',
			'Day' 			=> 't.Day',
			'Faculty' 		=> 't.Faculty',
			'Form' 			=> 't.Form',
			'DateZ' 		=> 't.DateZ',
			'DateStr' 		=> 't.DateStr',
			'link' 			=> 'ad.link',
			'link2' 		=> 'ad.link2',
			'link3' 		=> 'ad.link3',
			'appointment_id'=> 't.appointment_id',
		);
		$select->from(array('t' => $serviceTimetable->getMapper()->getAdapter()->getTableName()), $fields);
		$select->joinLeft(array('ad' => 'timetable_additional'), 'ad.external_id = t.external_id', array());	
		
		
		$select->where($serviceTimetable->quoteInto('t.group_code = ?', $group_code));
		
		# следующая неделя
		if($week == 'next'){
			$monday = HM_Timetable_TimetableModel::getMondayNextWeek();
			$sunday = HM_Timetable_TimetableModel::getSundayNextWeek();
			
			$this->view->show_next_week = true;
			
		} else {
			$monday = HM_Timetable_TimetableModel::getMondayCurrent();
			$sunday = HM_Timetable_TimetableModel::getSundayCurrent();
			
			$this->view->show_next_week = false;
		}
		
		
		
		$select->where($serviceTimetable->quoteInto(array('t.DateZ BETWEEN ?', ' AND ?'), array($monday, $sunday)));
		
		$select->order(array('t.Nch', 't.Day', 't.Time_1', 't.Time_2'));
		
		$res = $select->query()->fetchAll();
		$this->view->timetable = $this->prepareData($res);
		
		$this->view->list_week_day	= HM_Timetable_TimetableModel::getListWeekDays();
		$this->view->form 			= new HM_Form_Timetable();
		$this->view->has_even		= $this->isHasDataEven($this->view->timetable); 
		$this->view->has_odd		= $this->isHasDataOdd($this->view->timetable); 
		
	}
	
	private function prepareData($raw)
	{
		if(empty($raw)){ return false; }
		$list_time_begin		= HM_Timetable_TimetableModel::getListTimeBegin();
		$list_time_end			= HM_Timetable_TimetableModel::getListTimeEnd();
		$list_academic_hours	= HM_Timetable_TimetableModel::getListAcademicHours();
		$list_discipline_types	= HM_Timetable_TimetableModel::getListDisciplineTypes();
		$list_even_odd			= HM_Timetable_TimetableModel::getListEvenOdd();
		$list_week_day			= HM_Timetable_TimetableModel::getListWeekDays();
		$list_faculties			= HM_Timetable_TimetableModel::getListFaculties();
		$list_study_forms		= HM_Timetable_TimetableModel::getListStudyForms();
		
		$data = array();
		foreach($raw as $i){
			$item	= new stdClass;
			$item->time 				= $i['Time_1'] . ' - ' . $i['Time_2']; #$list_time_begin[intval($i['Time_1'])] . ' - ' . $list_time_end[intval($i['Time_2'])];
			$item->academic_hour_name 	= $list_academic_hours[intval($i['Caption'])];
			$item->group_name		 	= $i['Groups'];
			$item->classroom		 	= $i['Place'];
			$item->discipline		 	= $i['Name'];
			$item->teacher		 		= $i['Teacher'];
			$item->discipline_type 		= $list_discipline_types[intval($i['Type'])];
			$item->rank		 			= $i['Rank'];
			$item->even_odd 			= $list_even_odd[intval($i['Nch'])];
			$item->even_odd_id 			= intval($i['Nch']);
			
			$item->week_day				= $list_week_day[intval($i['Day'])];
			$item->week_day_id			= intval($i['Day']);
			
			$item->faculty				= $list_faculties[intval($i['Faculty'])];
			$item->study_form			= $list_study_forms[intval($i['Form'])];
			$item->link					= $this->prepareLink($i['link']);
			$item->link2				= $this->prepareLink($i['link2']);
			$item->link3				= $this->prepareLink($i['link3']);
			$item->linkTrueConf         = $this->prepareLinkTrueConf($i['appointment_id']);
			
			$data[] = $item;	
		}
		return $data;
	}
	
	public function prepareLink($link)
	{
		$link = trim($link);
		if(empty($link)){ return false; }
		if(mb_stripos($link, 'http') === false){
			return '//'.$link;
		}
		return $link;
	}

	public function prepareLinkTrueConf($appointmentId)
	{
		$appointmentId = trim($appointmentId);
		if(empty($appointmentId)){ return false; }
		return 'https://vcs.rgsu.net/c/' . $appointmentId;		
	}

	public function isHasDataEven($data)
	{
		if(empty($data)){ return false; }
		foreach($data as $i){
			if($i->even_odd_id == HM_Timetable_TimetableModel::TYPE_EVEN){ return true; }
		}
		return false;
	}
	
	public function isHasDataOdd($data)
	{
		if(empty($data)){ return false; }
		foreach($data as $i){
			if($i->even_odd_id == HM_Timetable_TimetableModel::TYPE_ODD){ return true; }
		}
		return false;
	}
    
}