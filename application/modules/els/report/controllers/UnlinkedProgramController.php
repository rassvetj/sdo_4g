<?php
class Report_UnlinkedProgramController extends HM_Controller_Action_Crud
{
    
	private $_users 			= array(); # накопительный кэш инфомрации по пользователям
	private $_subjects 			= array(); # накопительный кэш инфомрации по сессиям
	private $_lessons 			= array(); # накопительный кэш инфомрации по занятиям. key = subject_id, value = array of lesson_id
	private $_groups 			= array(); # список всех групп
	private $_programs 			= array(); # список всех программ
	private $_group_links		= array(); # связь группы с программами. key - group_id, value - array of programm_ids
	private $_debug				= array(); # статистика по работе скрипта
	
	private $_serviceSubject 		= null;
	private $_serviceGroup			= null;
	private $_serviceGroupCustom	= null;
	private $_serviceLesson			= null;
	
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Назначения студентов без программы'));
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_UnlinkedProgram();
		
		# отбор - семестр сессии
		# семестр студента
		# Группа,группы
		# есть активность в сессии/нет
		
		# получить студентов по указанному отбору
		
		# поля: 
		#ФИО
		#Год обучения студента
		#группа
		#План текущий
		#Сессия
		#План сессии
		#год обучения сессии текущий
		
		#Кнопки: в файл
		#	    Удалить с курсов всех первокурсников, у которых нет активности.
		
    }
	
	public function getAction()
    {	
		$this->_debug[] = 'Память начало: '.$this->getMemory();
		
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
		
		if(!$request->isPost() && !$request->isGet()){
			die;
		}
		
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		
		$student_semester 	= (int)$request->getParam('student_semester');
		$student_activity 	= (int)$request->getParam('student_activity');
		$status_subject 	= (int)$request->getParam('status_subject'); # Прошедшая/текущая. Только по дате окончания сессии без учета дат продления
		
		$student_groups 	= $request->getParam('student_groups', array());
		if(!empty($student_groups)){
			$student_groups 	= array_map('intval', $student_groups);
			$student_groups 	= array_filter($student_groups);
		} else {
			$student_groups 	= array();
		}
		
		$subject_semester 	= (int)$request->getParam('subject_semester');
		
		$subject_programs 	= $request->getParam('subject_programs');
		if(!empty($subject_programs)){
			$subject_programs 	= array_map('intval', $subject_programs);
			$subject_programs 	= array_filter($subject_programs);
		} else {
			$subject_programs 	= array();
		}
		
		
		
		
		
		$subject_ids 		= array(); # id всех сессий
		$student_ids 		= array(); # id всех активных студентов
		$asigns 	 		= array(); # список назначений студентов по указанным сессиям и студентам. Ключ составной mid~cid
		$content 			= array(); # итоговый контент для вывода на странице
		
		# сессии
		$start = microtime(true);			
		$select 		= $this->_serviceSubject->getSelect();
		$select->from('subjects', array('subid') );
		######### Вернуть после тестов
		
		
		
		# Прошедшая. только по дате окончания сессии без учета дат продления
		if($status_subject == HM_Report_ReportModel::STATUS_TIME_PAST){
			$select->where('end < getdate()');	
		} else {
			$select->where('end >= getdate()');		
			$select->where("(time_ended_debt   IS NULL OR time_ended_debt   = '')");
			$select->where("(time_ended_debt_2 IS NULL OR time_ended_debt_2 = '')");			
		}
		
		
		if(!empty($subject_semester)){
			$select->where($this->_serviceSubject->quoteInto('semester = ?', $subject_semester));
		}
		
		#echo $select->assemble();
		#die;
		
		$res  = $select->query()->fetchAll();
		
		
		
		if(!empty($res)){ foreach($res as $i){ $subject_ids[$i['subid']] = $i['subid']; } }
		unset($res);
		unset($select);
		
		$this->_debug[] = 'Память Выборка сессий: '.$this->getMemory();
		$this->_debug[] = 'Выборка сессий: '.round(microtime(true) - $start, 4).' сек.';
		
		
		
		if(empty($subject_ids)){			
			$this->view->error = _('Нет сессий по указанным параметрам');
			echo $this->view->render('unlinked-program/get.tpl');	
			die;			
		}
		
		# студенты
		$start = microtime(true);	
		$select 		= $this->_serviceSubject->getSelect();
		$select->from(array('p' => 'People'), array('MID' => 'p.MID') );
		$select->where($this->_serviceSubject->quoteInto('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED));
		if(!empty($student_semester)){
			$select->join(array('sr' => 'student_recordbooks'), 'sr.mid_external = p.mid_external', array());			
			$select->where($this->_serviceSubject->quoteInto('sr.semester = ?', $student_semester));
		}
		
		$res  = $select->query()->fetchAll();
		if(!empty($res)){ foreach($res as $i){ $student_ids[$i['MID']] = $i['MID']; } }
		unset($res);
		unset($select);
		
		$this->_debug[] = 'Память Выборка студентов: '.$this->getMemory();
		$this->_debug[] = 'Выборка студентов: '.round(microtime(true) - $start, 4).' сек.';
		
		if(empty($student_ids)){			
			$this->view->error = _('Нет студентов по указанным параметрам');
			echo $this->view->render('unlinked-program/get.tpl');	
			die;			
		}
		
		# Отбор по программе
		if(!empty($subject_programs)){
			$start = microtime(true);	
			$select 		= $this->_serviceSubject->getSelect();
			$select->from('programm_events', array('subject_id' => 'item_id') );
			$select->where('type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT);
			$select->where($this->_serviceSubject->quoteInto('item_id IN (?)', $subject_ids));	
			$select->where($this->_serviceSubject->quoteInto('programm_id IN (?)', $subject_programs));				
			
			$res  			= $select->query()->fetchAll();
			$subject_ids 	= array(); # переопределяем список сессий в $subject_ids
			
			if(!empty($res)){ foreach($res as $i){ $subject_ids[$i['subject_id']] = $i['subject_id']; } }
			unset($res);
			unset($select);
			$this->_debug[] = 'Память Отбор по программе: '.$this->getMemory();
			$this->_debug[] = 'Отбор по программе: '.round(microtime(true) - $start, 4).' сек.';
			

			if(empty($subject_ids)){			
				$this->view->error = _('Нет сессий с указанными программами');
				echo $this->view->render('unlinked-program/get.tpl');	
				die;			
			}			
		}
		
		
		# Отбор по группам студентов		
		if(!empty($student_groups)){
			
			$start = microtime(true);		
			$select 		= $this->_serviceSubject->getSelect();
			$select->from('study_groups_custom', array('user_id') );			
			$select->where($this->_serviceSubject->quoteInto('group_id IN (?)', $student_groups));
			$res  			= $select->query()->fetchAll();
			
			$student_ids_new   = array();
			if(!empty($res)){ foreach($res as $i){ $student_ids_new[$i['user_id']] = $i['user_id']; } }
			unset($res);
			unset($select);			
			
			$student_ids = array_intersect($student_ids, $student_ids_new); # переопределяем список студентов в $student_ids
			unset($student_ids_new);			
			
			$this->_debug[] = 'Память Отбор по группам: '.$this->getMemory();
			$this->_debug[] = 'Отбор по группам: '.round(microtime(true) - $start, 4).' сек.';
			
			if(empty($student_ids)){			
				$this->view->error = _('Нет студентов с указанными группами');
				echo $this->view->render('unlinked-program/get.tpl');	
				die;			
			}
		}
		
		
		
		# Получить связь студент - сессия
		# берем только те записи, по которым есть данные в $student_ids и $subject_ids
		
		$start = microtime(true);	
		$select 		= $this->_serviceSubject->getSelect();
		$select->from('Students', array('MID', 'CID') );
		
		#$select->where("(time_ended_debtor   IS NULL OR time_ended_debtor   = '')");
		#$select->where("(time_ended_debtor_2 IS NULL OR time_ended_debtor_2 = '')");
		
		$res  			= $select->query()->fetchAll();
		
		if(!empty($res)){ 
			foreach($res as $i){
					if(
						isset($student_ids[$i['MID']])
						&&
						isset($subject_ids[$i['CID']])
					){
						$key = $i['MID'].'~'.$i['CID'];
						$asigns[$key] = array(
							'student_id' => $i['MID'],
							'subject_id' => $i['CID'],
						); 	
					}
			} 
		}
		unset($res);
		unset($select);	
		# они уже не нужны
		unset($student_ids);	
		unset($subject_ids);
		
		$this->_debug[] = 'Память Выборка назначений студентов: '.$this->getMemory();
		$this->_debug[] = 'Выборка назначений студентов: '.round(microtime(true) - $start, 4).' сек.';		
		
		if(empty($asigns)){			
			$this->view->error = _('Нет назначений по указанным параметрам');
			echo $this->view->render('unlinked-program/get.tpl');	
			die;			
		}		
		
		$start = microtime(true);	
		foreach($asigns as $i){
			$start_2 = microtime(true);	
			$user 				= $this->getUserInfo($i['student_id']);
			$this->_debug[] = 'MID='.$i['student_id'].': '.round(microtime(true) - $start_2, 4).' сек.';	
			
			$start_2 = microtime(true);	
			$subject			= $this->getSubjectInfo($i['subject_id']);
			$this->_debug[] = 'CID='.$i['subject_id'].': '.round(microtime(true) - $start_2, 4).' сек.';	
			
			$user_programs		= $user['programs'];
			$subject_programs	= $subject['programs'];
			
			$isSameProgram = false; # программы совпадают. Если совпадают, то не выводим в отчет
			if(!empty($subject_programs)){
				foreach($subject_programs as $id => $name){
					if(isset($user_programs[$id])){
						$isSameProgram = true;
						break;
					}
				}
			}
			
			# программа совпадает. Не выводим в отчете
			if($isSameProgram){ continue; }
			
			$activity = $this->getStudentActivity($i['student_id'], $i['subject_id']);
			
			if($student_activity == HM_Report_ReportModel::ACTIVITY_YES){ # только если есть активность
				if(empty($activity)){ continue;}
				
			} elseif($student_activity == HM_Report_ReportModel::ACTIVITY_NO){ # только если нету активности
				if(!empty($activity)){ continue; }
			}
			
			$row = array(
				'student_id'			=> (int)$i['student_id'],
				'student_mid_external'	=> $user['mid_external'],
				'student_fio'			=> $user['fio'],
				'student_semester'		=> empty($user['semester']) ? '' : $user['semester'],
				'student_groups'		=> $user['groups'],
				'student_programs'	 	=> $user_programs,
				'student_activity'		=> $activity,
				
				'subject_id'			=> (int)$i['subject_id'],
				'subject_external_id'	=> $subject['external_id'],
				'subject_name'			=> $subject['name'],
				'subject_semester'		=> empty($subject['semester']) ? '' : $subject['semester'],
				'subject_programs'	 	=> $subject_programs,
				
			);			
			$content[] = $row;			
		}
		unset($asigns);
	
		$this->_users			= null;
		$this->_subjects		= null;
		$this->_lessons			= null;
		$this->_groups			= null;
		$this->_programs		= null;
		$this->_group_links		= null;
		
		$this->_serviceSubject		= null;
		$this->_serviceGroup		= null;
		$this->_serviceGroupCustom	= null;
		$this->_serviceLesson		= null;
		
		
		
		
		$this->_debug[] = 'Память Формирование данных: '.$this->getMemory();
		$this->_debug[] = 'Формирование данных: '.round(microtime(true) - $start, 4).' сек.';		
		
		
		$this->view->fields = array(
			'student_mid_external'	=> 'ID студента (1С)',
			'student_fio'			=> 'Студент',
			'student_semester'		=> 'Семестр студента',
			'student_groups'		=> 'Группы студента',
			'student_programs'		=> 'Программы студента',
			'student_activity'		=> 'Активность студента',
			
			'subject_external_id'	=> 'ID сессии (1С)',			
			'subject_name'			=> 'Сессия',			
			'subject_semester'		=> 'Семестр сессии',
			'subject_programs'		=> 'Программы сессии',
		);		
		
		$this->view->content 	= $content;
		$this->view->debug		= $this->_debug;
		echo $this->view->render('unlinked-program/get.tpl');	
		$this->_debug[] = 'Память конец: '.$this->getMemory();
		die;		
	}
	
	
	protected function getUserInfo($user_id){
		$user_id = (int)$user_id;
		if(empty($user_id)){ return false; }
		
		if(isset($this->_users[$user_id])){ return $this->_users[$user_id];}		
		
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		$select = $this->_serviceSubject->getSelect();
		$select->from('People', array(
			'mid_external',
			'LastName',
			'FirstName',
			'Patronymic',			
		));
		$select->where($this->_serviceSubject->quoteInto('MID = ?', $user_id));
		$user = $select->query()->fetchObject();
		
		if(empty($user)){
			$this->_users[$user_id] = false;
			return $this->_users[$user_id];
		}
		
		$data = array(
			'mid_external'	=> $user->mid_external,
			'fio' 			=> $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,	
			'semester'		=> $this->getUserSemestr($user->mid_external),
			'groups'		=> $this->getUserGroups($user_id),			
		);		
		$data['programs'] = $this->getGroupPrograms(array_keys($data['groups']));
		
		$this->_users[$user_id] = $data;
		return $this->_users[$user_id];
	}
	
	private function getUserSemestr($mid_external){	
		if(empty($mid_external)){ return false; }
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		$select = $this->_serviceSubject->getSelect();
		$select->from('student_recordbooks', array('semester'));
		$select->where($this->_serviceSubject->quoteInto('mid_external = ?', $mid_external));
		$row = $select->query()->fetchObject();
		return (int)$row->semester;
	}
	
	
	#### Группы ####
	private function getUserGroups($user_id){
		$user_id = (int)$user_id;
		if(empty($user_id)){ return false; }
		if(!$this->_serviceGroupCustom) { $this->_serviceGroupCustom = $this->getService('StudyGroupCustom'); }
		$group_ids = $this->_serviceGroupCustom->getByUserId($user_id);
		if(empty($group_ids)){ return false; }
		$data = array();
		foreach($group_ids as $group_id){
			$data[$group_id] = $this->getGroupName($group_id);			
		}
		return $data;				
	}
	
	
	public function getGroupName($group_id){
		if(empty($this->_groups)){
			if(!$this->_serviceGroup) { $this->_serviceGroup = $this->getService('StudyGroup'); }
			$this->_groups = $this->_serviceGroup->getGroupList();
		}
		return $this->_groups[$group_id];
	}
	
	
	#### Программы ####
	# получаем программы указанных групп.
	private function getGroupPrograms($group_ids){		
		if(empty($group_ids)){ return false; }
		
		if(!$this->_serviceGroupProgramm) { $this->_serviceGroupProgramm = $this->getService('StudyGroupProgramm'); }
		
		$data = array();
		foreach($group_ids as $group_id){
			
			if(isset($this->_group_links[$group_id])){
				$programms	= $this->_group_links[$group_id];
			} else {
				$programms = $this->_serviceGroupProgramm->getGroupProgrammsIds($group_id);	
				$this->_group_links[$group_id] = $programms;
			}
			
			if(empty($programms)){ continue;}
			foreach($programms as $program_id){
				$data[$program_id] = $this->getProgramName($program_id);				
			}			
		}
		return $data;				
	}
	
	
	// программа сессии
	private function getSubjectPrograms($subject_id){
		$subject_id = (int)$subject_id;
		if(empty($subject_id)){ return false; }
		
		if(!$this->_serviceProgrammEvent) { $this->_serviceProgrammEvent = $this->getService('ProgrammEvent'); }
		
		$programms = $this->_serviceProgrammEvent->getSubjectProgramms($subject_id);
		
		if(empty($programms)){ return false; }
		
		$data = array();
		foreach($programms as $program_id){
			$data[$program_id] = $this->getProgramName($program_id);				
		}	
		
		return $data;
	}
	
	public function getProgramName($program_id){
		if(empty($this->_programs)){
			if(!$this->_serviceProgram) { $this->_serviceProgram = $this->getService('Programm'); }
			$this->_programs = $this->_serviceProgram->getProgrammList();
		}
		return $this->_programs[$program_id];
	}
	
	
	# сессия
	public function getSubjectInfo($subject_id){
		$subject_id = (int)$subject_id;
		if(empty($subject_id)){ return false; }
		
		if(isset($this->_subjects[$subject_id])){ return $this->_subjects[$subject_id];}
		
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		$select = $this->_serviceSubject->getSelect();
		$select->from('subjects', array(
			'external_id',
			'name',
			'semester',
			'faculty',			
		));
		$select->where($this->_serviceSubject->quoteInto('subid = ?', $subject_id));
		$subject = $select->query()->fetchObject();
		
		if(empty($subject)){
			$this->_subjects[$subject_id] = false;
			return $this->_subjects[$subject_id];
		}

		
		$data = array(
			'external_id'	=> $subject->external_id,
			'name' 			=> $subject->name,
			'semester'		=> $subject->semester,
			'programs'		=> $this->getSubjectPrograms($subject_id), # массив. По на самом деле у нас на 1 сессию 1 программа.
		);		
		
		$this->_subjects[$subject_id] = $data;
		return $this->_subjects[$subject_id];		
	}
	
	
	# занятия
	public function getLessons($subject_id){
		$subject_id = (int)$subject_id;
		if(empty($subject_id)){ return false; }
		if(isset($this->_lessons[$subject_id])){
			return $this->_lessons[$subject_id];
		}
		
		if(!$this->_serviceLesson) { $this->_serviceLesson = $this->getService('Lesson'); }
		$lessons = $this->_serviceLesson->getActiveLessonsOnSubjectIdCollection($subject_id)->getList('SHEID');
		if(empty($lessons)){
			$this->_lessons[$subject_id] = false;
			return false;		
		}
		$this->_lessons[$subject_id] = $lessons;
		return $this->_lessons[$subject_id];
	}
		
	/**
	 * находим активность студента в сессии
	 * @reutrn array of reasons
	    1. Есть оценка за сессию - указать
		2. Есть оценка в занятии - указать балл и урок
		3. Есть диалог с тьютором - указать где
	*/
	private function getStudentActivity($student_id, $subject_id){
		$student_id = (int)$student_id; 
		$subject_id = (int)$subject_id;
		
		if(empty($student_id) || empty($subject_id)){ return false; }
		
		$data = array();
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		$select = $this->_serviceSubject->getSelect();
		$select->from('courses_marks', array(
			'mark',
			'mark_current',
			'mark_landmark',				
		));
		$select->where($this->_serviceSubject->quoteInto('mid = ?', $student_id));
		$select->where($this->_serviceSubject->quoteInto('cid = ?', $subject_id));
		$row	 = $select->query()->fetchObject();
		if(!empty($row)){
			$separate = (empty($row->mark_current) && empty($row->mark_current)) ? '' : ' ('.$row->mark_current.'/'.$row->mark_current.')';
			$data[] = _('Выставлен итоговый балл').': '.$row->mark.$separate;
			return $data;
		}
		
		
		# Если нет обязатлеьных занятий, то и проверят дальше нет смысла
		$lesson_ids = $this->getLessons($subject_id);
		if(empty($lesson_ids)){ return false; }
		
		$select = $this->_serviceSubject->getSelect();
		$select->from('scheduleID', array(
			'SHEID',
			'V_STATUS',						
		));
		$select->where($this->_serviceSubject->quoteInto('MID = ?', $student_id));
		$select->where($this->_serviceSubject->quoteInto('SHEID IN (?)', $lesson_ids));
		$res	 = $select->query()->fetchAll();
		
		if(empty($res)){ return false; }
		
		foreach($res as $i){
			if($i['V_STATUS'] > 0){
				$data[] = 'Набрано '.$i['V_STATUS'] .' баллов в занятии #'.$i['SHEID'];
			}
		}	
		if(!empty($data)){ return $data; }
		
		
		$select = $this->_serviceSubject->getSelect();
		$select->from('interview', array(
			'lesson_id',
			'type'
		));
		$select->where($this->_serviceSubject->quoteInto('user_id = ?', $student_id));
		$select->where($this->_serviceSubject->quoteInto('lesson_id IN (?)', $lesson_ids));
		$res	 = $select->query()->fetchAll();		
		if(empty($res)){ return false; }
		
		$types = HM_Interview_InterviewModel::getTypes();
		foreach($res as $i){			
			$data[] = $types[$i['type']].' в занятии <a href="/interview/index/index/lesson_id/'.$i['lesson_id'].'/subject_id/'.$subject_id.'/user_id/'.$student_id.'" tagret="_blank">#'.$i['lesson_id'].'</a>';			
		}	
		return $data;
	}
	
	
	function getMemory(){
		
		$size = memory_get_usage(true);
		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
	
	public function removeAssignsAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
		
		if(!$request->isPost() && !$request->isGet()){
			die;
		}
		$result = array();
		
		$rows 	= $request->getParam('rows');
		if(empty($rows)){
			$result['error'] 	= 1;
			$result['message']	= _('Не выбраны строки для удаления');
			echo json_encode($result);
			die;
		}
		
		if(!is_array($rows)){
			$rows = array($rows);
		}
		
		$data = array();
		foreach($rows as $i){
			$tmp 		= explode('_', $i);			
			$user_id	= (int)$tmp[0];
			$subject_id = (int)$tmp[1];
			if(empty($user_id) || empty($subject_id)){ continue; }
			
			$data[$i] = array(
				'user_id' 		=> $user_id,
				'subject_id'	=> $subject_id,
			);			
		}
		
		if(empty($data)){
			$result['error'] 	= 1;
			$result['message']	= _('Нет данных для удаления назначений');
			echo json_encode($result);
			die;			
		}
		
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		
		foreach($data as $row_id => $i){
			$user_id 	= (int)$i['user_id'];
			$subject_id = (int)$i['subject_id'];
			if(empty($user_id) || empty($subject_id)){ continue; }
			
			$this->_serviceSubject->unassignStudent($subject_id, $user_id);
			$result['removed'][$row_id] = $row_id;			
		}
		
		if(empty($result['removed'])){
			$result['error'] 	= 1;
			$result['message']	= _('Ни одного назначения не удалено');
			echo json_encode($result);
			die;			
		}
		
		
		$result['message']	= _('Назначения успешно удалены');
		echo json_encode($result);		
		die;
	}
	
	
	public function assignGraduatedAction()
	{
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
		
		if(!$request->isPost() && !$request->isGet()){
			die;
		}
		$result = array();
		
		$rows 	= $request->getParam('rows');
		if(empty($rows)){
			$result['error'] 	= 1;
			$result['message']	= _('Не выбраны строки для перевода в завершенные');
			echo json_encode($result);
			die;
		}
		
		if(!is_array($rows)){
			$rows = array($rows);
		}
		
		$data = array();
		foreach($rows as $i){
			$tmp 		= explode('_', $i);			
			$user_id	= (int)$tmp[0];
			$subject_id = (int)$tmp[1];
			if(empty($user_id) || empty($subject_id)){ continue; }
			
			$data[$i] = array(
				'user_id' 		=> $user_id,
				'subject_id'	=> $subject_id,
			);			
		}
		
		if(empty($data)){
			$result['error'] 	= 1;
			$result['message']	= _('Нет данных для перевода в завершенные');
			echo json_encode($result);
			die;			
		}
		
		if(!$this->_serviceSubject) { $this->_serviceSubject = $this->getService('Subject'); }
		
		foreach($data as $row_id => $i){
			$user_id 	= (int)$i['user_id'];
			$subject_id = (int)$i['subject_id'];
			if(empty($user_id) || empty($subject_id)){ continue; }
			
			$this->_serviceSubject->assignGraduated($subject_id, $user_id);
			$result['removed'][$row_id] = $row_id;			
		}
		
		if(empty($result['removed'])){
			$result['error'] 	= 1;
			$result['message']	= _('Ни одного назначения не переведено в завершенные');
			echo json_encode($result);
			die;			
		}
		
		
		$result['message']	= _('Назначения успешно переведены в завершенные');
		echo json_encode($result);		
		die;
		
	}
	
	
	
}