<?php
class Timetable_TeacherController extends HM_Controller_Action
{
    public function init()
    {		
		
	}
    
    
    public function indexAction()
    {        
		$this->view->setHeader(_('Расписание занятий'));
		
		$week = trim($this->_request->getParam('week', false));

		$user = $this->getService('User')->getCurrentUser();	

		$serviceTimetable 	= $this->getService('Timetable');
		
		$select	= $serviceTimetable->getSelect();
		
		$fields = array(
			'timetable_id' 	=> 't.timetable_id',
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
			
			'users' 		=> 'ad.users',
			'file_path' 	=> 'ad.file_path',
			'subject_path' 	=> 'ad.subject_path',
			'appointment_id'=> 't.appointment_id',
			
		);
		$select->from(array('t' => $serviceTimetable->getMapper()->getAdapter()->getTableName()), $fields);
		$select->joinLeft(array('ad' => 'timetable_additional'), 'ad.external_id = t.external_id', array());		
		$select->where($serviceTimetable->quoteInto('t.mid_external = ?', $user->mid_external));
		
		
		if($week == 'next'){
			$monday = HM_Timetable_TimetableModel::getMondayNextWeek();
			$sunday = HM_Timetable_TimetableModel::getSundayNextWeek();
			
			$this->view->show_next_week = true;
			
		} else {
			$monday = HM_Timetable_TimetableModel::getMondayCurrent();
			$sunday = HM_Timetable_TimetableModel::getSundayCurrent();
			
			$this->view->show_next_week = false;
		}
		
		
		if($week == 'prev' && $user->MID == 64931){
			$monday = '2022-05-23'; #HM_Timetable_TimetableModel::getMondayPrevWeek();
			$sunday = '2022-05-29'; #HM_Timetable_TimetableModel::getSundayPrevWeek();
			
			#pr($monday);
			#pr($sunday);
		}
		
		
		
		$select->where($serviceTimetable->quoteInto(array('t.DateZ BETWEEN ?', ' AND ?'), array($monday, $sunday)));
		$select->order(array('t.Day', 't.Time_1', 't.Time_2'));
		
		$res = $select->query()->fetchAll();
		$this->view->timetable 	= $this->prepareData($res);
		$this->view->form 		= new HM_Form_Additional();
		$this->view->monday		= $monday;
		$this->view->sunday		= $sunday;
	}
	
	public function saveLinkAction()
	{
		
		$timetable_id 	= (int)$this->_request->getParam('timetable_id', false);
		$link 			= trim($this->_request->getParam('link', false));
		$link2 			= trim($this->_request->getParam('link2', false));
		$link3 			= trim($this->_request->getParam('link3', false));
		$user 			= $this->getService('User')->getCurrentUser();	
		
		if(empty($timetable_id)){
			echo Zend_Json::encode(array('error' => _('Некорректные данные')));
			die;
		}
		
		if(empty($link) && empty($link2) && empty($link3)){
			echo Zend_Json::encode(array('error' => _('Укажите ссылку')));
			die;
		}
		
		$timetable = $this->getService('Timetable')->getById($timetable_id);
		
		if(!$timetable){
			echo Zend_Json::encode(array('error' => _('Не найдены данные по расписанию')));
			die;
		}
		
		if($timetable->mid_external != $user->mid_external){
			echo Zend_Json::encode(array('error' => _('Вы не являетесь преподавателем в выбранной дисциплине')));
			die;
		}
		
		$data = array(
			'external_id' 	=> $timetable->external_id,
			'link' 			=> $link,
			'link2' 		=> $link2,
			'link3' 		=> $link3,
		);
		$save_info = $this->getService('TimetableAdditional')->save($data);
		
		if(!$save_info){
			echo Zend_Json::encode(array('error' => _('Не удалось сохранить')));
			die;
		}
		
		echo Zend_Json::encode(array('message' => _('Сохранено')));
		die;
	}
	
	public function saveAdditionalAction()
	{
		$timetable_id 	= (int)$this->_request->getParam('timetable_id', false);
		$users			= (int)$this->_request->getParam('users', false);
		$file_path		= trim($this->_request->getParam('file_path', false));
		$subject_path	= trim($this->_request->getParam('subject_path', false));
		$user 			= $this->getService('User')->getCurrentUser();	
		$params 		= $this->_request->getParams();
		
		if(empty($timetable_id)){
			echo Zend_Json::encode(array('error' => _('Некорректные данные')));
			die;
		}
		
		if(empty($users) && empty($file_path) && empty($subject_path) ){
			echo Zend_Json::encode(array('error' => _('Заполните поля')));
			die;
		}
		
		$timetable = $this->getService('Timetable')->getById($timetable_id);
		
		if($timetable->mid_external != $user->mid_external){
			echo Zend_Json::encode(array('error' => _('Вы не являетесь преподавателем в выбранной дисциплине')));
			die;
		}
		
		if(!$timetable){
			echo Zend_Json::encode(array('error' => _('Не найдены данные по расписанию')));
			die;
		}
		
		$data = array(
			'external_id' 	=> $timetable->external_id,
		);
		
		if(array_key_exists('users', $params)){
			$data['users'] = $users;
		}
		
		if(array_key_exists('file_path', $params)){
			$data['file_path'] = $file_path;
		}
		
		if(array_key_exists('subject_path', $params)){
			$data['subject_path'] = $subject_path;
		}
		
		$save_info = $this->getService('TimetableAdditional')->save($data);
		
		if(!$save_info){
			echo Zend_Json::encode(array('error' => _('Не удалось сохранить')));
			die;
		}
		
		$data['message'] = _('Сохранено');
		
		echo Zend_Json::encode($data);
		die;
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
			$item->timetable_id 		= $i['timetable_id'];
			$item->time 				= $i['Time_1'] . ' - ' . $i['Time_2']; #$list_time_begin[intval($i['Time_1'])] . ' - ' . $list_time_end[intval($i['Time_2'])];
			$item->academic_hour_name 	= $list_academic_hours[intval($i['Caption'])];
			$item->group_name		 	= $i['Groups'];
			$item->classroom		 	= $i['Place'];
			$item->discipline		 	= $i['Name'];
			$item->teacher		 		= $i['Teacher'];
			$item->discipline_type 		= $list_discipline_types[intval($i['Type'])];
			$item->rank		 			= $i['Rank'];
			$item->even_odd 			= $list_even_odd[intval($i['Nch'])];
			$item->week_day				= $list_week_day[intval($i['Day'])];
			$item->faculty				= $list_faculties[intval($i['Faculty'])];
			$item->study_form			= $list_study_forms[intval($i['Form'])];
			$item->link		 			= $i['link'];
			$item->link2	 			= $i['link2'];
			$item->link3	 			= $i['link3'];
			$item->users		 		= (int)$i['users'];
			$item->file_path		 	= $i['file_path'];
			$item->subject_path		 	= $i['subject_path'];
			
			$appointment_id = trim($i['appointment_id']);
			$item->linkTrueConf		 	= empty($appointment_id) ? '' : 'https://vcs.rgsu.net/c/' . $appointment_id;
			
			
			
			$data[] = $item;	
		}
		return $data;
	}
    
}