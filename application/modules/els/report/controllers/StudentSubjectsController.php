<?php
class Report_StudentSubjectsController extends HM_Controller_Action_Crud
{
	/**
	 * Отчет выводит информацию по сессиям студента.
	 * Доступна/не доступна, прошедшая, текущая, дата продления и т.п.
	*/
	
	
	const CACHE_NAME = 'Report_StudentSubjectsController';
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
				'report_data' 	=> $this->report_data,
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->report_data  	= $actions['report_data'];
            #$this->_restoredFromCache 	= true;
            return true;
        }
        return false;
    }
	
	
	public function init()
    {
        parent::init();       
        $this->getService('Unmanaged')->setHeader(_('Сессии студентов'));
    }
	
	
    public function indexAction()
    {        
		$this->clearCache();		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form 			= new HM_Form_StudentSubjects();			
    }
	
	public function getAction()
    {
		$request = $this->getRequest();
        	
		$group_ids		= (array)$request->getParam('group_id', array());
		$student_ids	= (array)$request->getParam('student_id', array());
		
		$group_ids 		= array_map('intval', $group_ids);
		$student_ids	= array_map('intval', $student_ids);
		
		$group_ids		= array_filter($group_ids);
		$student_ids	= array_filter($student_ids);
		
		
		if(empty($group_ids) && empty($student_ids)){
			echo 'Выберите группу или студента из списка';
			die;			
		}
		
		
		$serviceUser = $this->getService('User');
		
		$select = $serviceUser->getSelect();
		$select->from(array('subj' => 'subjects'), array(							
			'subject_id' 				=> 'subj.subid',
			'subject_external_id'		=> 'subj.external_id',
			'subject_name'				=> 'subj.name',
			'subject_begin'				=> 'subj.begin',
			'subject_end'				=> 'subj.end',
			'subject_semester'			=> 'subj.semester',
			'subject_time_ended_debt'	=> 'subj.time_ended_debt',
			'subject_time_ended_debt_2'	=> 'subj.time_ended_debt_2',
			'subject_begin_learning'	=> 'subj.begin_learning',
			'subject_period'			=> 'subj.period',
			
			'student_MID'				=> 'p.MID',
			'student_LastName'			=> 'p.LastName',
			'student_FirstName'			=> 'p.FirstName',
			'student_Patronymic'		=> 'p.Patronymic',
			'student_mid_external'		=> 'p.mid_external',
			
			'is_student' 					=> 'student.is_student',			
			'student_time_registered'		=> 'student.time_registered',
			'student_time_graduated'		=> 'student.time_graduated',
			'student_time_ended_debtor'		=> 'student.time_ended_debtor',
			'student_time_ended_debtor_2'	=> 'student.time_ended_debtor_2',
			
			'student_group_name'			=> 'sg.name',
			'student_group_id'				=> 'sg.group_id',
      
      	
		));
		
		### объеденяем текущих студентов и завершивших обучение
		$select_user = $serviceUser->getSelect();
		$select_user->from(array('Students'),	array('MID','CID', 
														'is_student' => new Zend_Db_Expr('1'), 
														'time_registered', 
														'time_ended_debtor', 
														'time_ended_debtor_2',
														'time_graduated' => new Zend_Db_Expr('NULL'), 
												));						
		
		$select_graduated = $serviceUser->getSelect();
		$select_graduated->from(array('graduated'),	array('MID','CID', 
															'is_student' 		  => new Zend_Db_Expr('0'), 
															'time_registered'	  => 'begin', 															
															'time_ended_debtor'   => new Zend_Db_Expr('NULL'), 
															'time_ended_debtor_2' => new Zend_Db_Expr('NULL'), 
															'time_graduated'	  => 'created', 
													));		
						
		$select_union = $serviceUser->getSelect();
		$select_union->union(array($select_user, $select_graduated));
		
		$select->join(array('student' => $select_union), 'student.CID = subj.subid', array());
		$select->join(array('p' => 'People'), 'p.MID = student.MID', array());
		$select->joinLeft(array('sgc' => 'study_groups_custom'), 'sgc.user_id = student.MID', array());
		$select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgc.group_id', array());
		
		$select->where('p.role_1c = ?', HM_User_UserModel::ROLE_1C_STUDENT);
		$select->where('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED);		
		
		# Применяем ограничения
		if(!empty($group_ids)){
			$select->where($serviceUser->quoteInto('sgc.group_id IN (?)', $group_ids));
		}
		
		if(!empty($student_ids)){
			$select->where($serviceUser->quoteInto('student.MID IN (?)',$student_ids));
		}
		
		$select->order(array('p.LastName', 'p.FirstName', 'p.Patronymic'));
		
		#echo $select->assemble();
		#die;
		
		
		try {
			$raw = $select->query()->fetchAll();
		} catch (Exception $e) {
			echo 'Ошибка основного запроса: ',  $e->getMessage(), "\n";
			die;
		}
		
		
		$this->view->data = array();
		$subject_ids 	  = array(); 
		$student_ids 	  = array(); 
		foreach($raw as $i){
			$subject_ids[$i['subject_id']] = $i['subject_id'];
			$student_ids[$i['student_MID']] = $i['student_MID'];
			$subject_begin = $this->dateFormatted();
			
			$item = array(
				'subject_id' 				=> (int)$i['subject_id'],
				'subject_external_id'		=> $i['subject_external_id'],
				'subject_name'				=> $i['subject_name'],				
				'subject_begin'				=> $this->dateFormatted($i['subject_begin']),
				'subject_end'				=> $this->dateFormatted($i['subject_end']),
				'subject_semester'			=> (int)$i['subject_semester'],
				'subject_time_ended_debt'	=> $this->dateFormatted($i['subject_time_ended_debt']),
				'subject_time_ended_debt_2'	=> $this->dateFormatted($i['subject_time_ended_debt_2']),
				'subject_begin_learning'	=> $this->dateFormatted($i['subject_begin_learning']),
				'subject_period'			=> $i['subject_period'],
				
				'student_id'					=> (int)$i['student_MID'],
				'student_fio'					=> $i['student_LastName'].' '.$i['student_FirstName'].' '.$i['student_Patronymic'],
				'student_mid_external'			=> $i['student_mid_external'],
				'student_time_registered'		=> $this->dateFormatted($i['student_time_registered']),
				'student_time_graduated'		=> $this->dateFormatted($i['student_time_graduated']),
				'student_time_ended_debtor'		=> $this->dateFormatted($i['student_time_ended_debtor']),
				'student_time_ended_debtor_2'	=> $this->dateFormatted($i['student_time_ended_debtor_2']),
				'student_group_name'			=> $i['student_group_name'],
				'student_group_id'				=> $i['student_group_id'],
			);
			
			
			if($i['subject_period'] == HM_Subject_SubjectModel::PERIOD_FREE)
			{
				$item['subject_end'] .= '<br />(не ограничено)';
			}
			
			
			$item['subject_status_for_student'] = _('В прошедших');
			$item['color'] = '#ffc9c9';
			
			
			if(empty($i['is_student'])){
				$item['subject_status_for_student'] = _('В завершенных');
				$item['color'] = '#d4d4d4';
				
			} elseif(strtotime($i['subject_begin']) > time()){
				$item['subject_status_for_student'] = _('В будущих');
				$item['color'] = '#c4dce8;';
				
			} elseif(
				strtotime($i['subject_end']) >= strtotime(date('Y-m-d 00:00:00'))
				||				
				strtotime($i['student_time_ended_debtor']) >= strtotime(date('Y-m-d 00:00:00'))
				||
				strtotime($i['student_time_ended_debtor_2']) >= strtotime(date('Y-m-d 00:00:00'))
				||
				$i['subject_period'] == HM_Subject_SubjectModel::PERIOD_FREE
			){
				$item['subject_status_for_student'] = _('В текущих');
				$item['color'] = 'white';
			}
			
			$this->view->data[] = $item;
		}
		
		$tutor_data     = $this->getTutorInfo($subject_ids);
		$mark_data	    = $this->getMarkInfo($subject_ids, $student_ids);
		$lesson_data    = $this->getLessonInfo($subject_ids, $student_ids);
		$interview_data = $this->getInterviewInfo($subject_ids, $student_ids);
		
		
		# добавляем данные по тьютору в данные отчета
		$this->view->data = $this->joinTutorInfo($this->view->data, $tutor_data);
		$this->view->data = $this->joinMarkInfo($this->view->data, $mark_data);
		$this->view->data = $this->joinLessonInfo($this->view->data, $lesson_data);
		$this->view->data = $this->joinInterviewInfo($this->view->data, $interview_data);
		
		$this->report_data = $this->view->data;
		$this->saveToCache();
		
		# сохраняем в кэш для экспорта в файл
		try {
			echo $this->view->render('student-subjects/get.tpl');
		} catch (Exception $e) {
			echo 'Ошибка метода render: ',  $e->getMessage(), "\n";		
		}		
		die;
	}
	
	private function joinTutorInfo($data, $tutor_data)
	{
		foreach($data as $key => $i){
			$tutors = $tutor_data[$i['subject_id']];
			if(empty($tutors)){ continue; }
			$data[$key]['tutors'] = $this->filterTutors($i, $tutors);			
		}
		return $data;
	}
	
	private function joinMarkInfo($data, $mark_data)
	{
		foreach($data as $key => $i){
			$mark_key = $i['student_id'].'~'.$i['subject_id'];
			$data[$key]['mark'] = $mark_data[$mark_key];			
		}
		return $data;
	}
	
	private function joinLessonInfo($data, $lesson_data)
	{
		foreach($data as $key => $i){
			$mark_key = $i['student_id'].'~'.$i['subject_id'];
			$data[$key]['lesson'] = $lesson_data[$mark_key];			
		}
		return $data;
	}
	
	private function joinInterviewInfo($data, $interview_data)
	{
		foreach($data as $key => $i){
			$interview_key = $i['student_id'] . '~' . $i['subject_id'];
			$interview     = $interview_data[$interview_key];
			
			if(empty($interview)){ continue; }
			foreach($interview as $item){
				if(
					!array_key_exists('hasSolutionToCheck', $data[$key])
					&&
					$item['type'] == HM_Interview_InterviewModel::MESSAGE_TYPE_TEST
				){
					$data[$key]['hasSolutionToCheck'] = true;
				}
				
				if(
					!array_key_exists('hasTeacherAnswer', $data[$key])
					&&
					$item['type'] == HM_Interview_InterviewModel::MESSAGE_TYPE_ANSWER
				){
					$data[$key]['hasTeacherAnswer'] = true;
				}
				
				
			}			
		}
		return $data;
	}
	
	
	
	
	
	# отсеиваем тьюторов, которые недоступны текущему стдуенту
	private function filterTutors($item, $tutors)
	{
		$debt_1_subject   = $item['subject_time_ended_debt'];
		$debt_2_subject   = $item['subject_time_ended_debt_2'];
		$student_group_id = $item['student_group_id'];
		
		if(empty($tutors)){ return false; }
		
		foreach($tutors as $key => $tutor){
			$tutors[$key]['available'] = true;
			
			if(!empty($tutor['groups'])){
				# назначение на группу.
				if(!in_array($student_group_id, $tutor['groups'])){
					$tutors[$key]['available'] = false;
					continue;
				}
			}
			
			# сессия не продлена. Доступны все тьюторы
			if(empty($debt_1_subject) && empty($debt_2_subject)){
				continue;
			}
			
			# второе продление
			if(!empty($debt_2_subject) && empty($tutor['date_debt_2'])){
				$tutors[$key]['available'] = false;
				continue;
			}
			
			# первое продление
			if(!empty($debt_1_subject) && empty($tutor['date_debt']) && empty($tutor['date_debt_2']) ){
				$tutors[$key]['available'] = false;
				continue;
			}
		}
		return $tutors;
	}
	
	# получаем данные по тьюторам в т.ч. дата продления и назначение на группы
	private function getTutorInfo($subject_ids)
	{
		if(empty($subject_ids)){ return false; }
		
		$serviceUser = $this->getService('User');
		
		$select = $serviceUser->getSelect();
		$select->from(array('t' => 'Tutors'), array(							
			'subject_id' 		=> 't.CID',
			'MID'				=> 'p.MID',
			'LastName'			=> 'p.LastName',
			'FirstName'			=> 'p.FirstName',
			'Patronymic'		=> 'p.Patronymic',
			'mid_external'		=> 'p.mid_external',
			'date_debt'			=> 't.date_debt',
			'date_debt_2'		=> 't.date_debt_2',			
			'groups'			=> new Zend_Db_Expr('GROUP_CONCAT(tg.GID)'),
		));		
		$select->join(array('p' => 'People'), 'p.MID = t.MID', array());		
		$select->joinLeft(array('tg' => 'Tutors_groups'), 'tg.CID = t.CID AND tg.MID=p.MID AND tg.GID > 0', array());		
		$select->where($serviceUser->quoteInto('t.CID IN (?)', $subject_ids));
		$select->where('p.blocked != ?', HM_User_UserModel::STATUS_BLOCKED);
		$select->group(array('t.CID', 'p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'p.mid_external', 't.date_debt', 't.date_debt_2'));
		
		try {
			$raw = $select->query()->fetchAll();
		} catch (Exception $e) {
			echo 'Ошибка метода getTutorInfo: ',  $e->getMessage(), "\n";
			die;
		}
		if(empty($raw)){ return false; }
		$data = array();
		foreach($raw as $i){
			$groups = empty($i['groups']) ? false : explode(',', $i['groups']);
			$data[$i['subject_id']][$i['MID']] = array(
				'subject_id' 		=> $i['subject_id'],
				'MID'				=> $i['MID'],
				'fio'				=> $i['LastName'].' '.$i['FirstName'].' '.$i['Patronymic'],
				'date_debt'			=> $this->dateFormatted($i['date_debt']),
				'date_debt_2'		=> $this->dateFormatted($i['date_debt_2']),
				'groups'			=> $groups,
			);
		}
		return $data;
	}
	
	# получаем оценки студентов. Не динамически из занятий, а те, что зафиксированы в БД как итоговый балл
	private function getMarkInfo($subject_ids, $student_ids)
	{
		if(empty($subject_ids) || empty($student_ids)){ return false; }
		
		$serviceUser = $this->getService('User');
		
		$select = $serviceUser->getSelect();
		$select->from('courses_marks', array('cid', 'mid', 'mark', 'mark_current', 'mark_landmark'));
		$select->where($serviceUser->quoteInto('cid IN (?)', $subject_ids));
		$select->where($serviceUser->quoteInto('mid IN (?)', $student_ids));
		try {
			$raw = $select->query()->fetchAll();
		} catch (Exception $e) {
			echo 'Ошибка метода getMarkInfo: ',  $e->getMessage(), "\n";
			die;
		}
		
		if(empty($raw)){ return false; }
		$data = array();
		foreach($raw as $i){
			$mark_key = $i['mid'].'~'.$i['cid'];
			$data[$mark_key] = array(
				'mark' 			=> $i['mark'],
				'mark_current' 	=> empty($i['mark_current']) ? 0 : $i['mark_current'],
				'mark_landmark' => empty($i['mark_landmark']) ? 0 : $i['mark_landmark'],				
			);
		}
		return $data;
	}
	
	private function getLessonInfo($subject_ids, $student_ids)
	{
		if(empty($subject_ids) || empty($student_ids)){ return false; }
		
		$serviceUser = $this->getService('User');
		
		$select = $serviceUser->getSelect();
		$select->from(array('l' => 'schedule'), array(
			'cid'             => 'l.CID',
			'mid'             => 'la.MID',
			'min_date_assign' => new Zend_Db_Expr('MIN(la.created)'),
		));
		$select->join(array('la' => 'scheduleID'), 'la.SHEID = l.SHEID', array());
		$select->where($serviceUser->quoteInto('l.CID  IN (?)', $subject_ids));
		$select->where($serviceUser->quoteInto('la.MID IN (?)', $student_ids));
		$select->group(array('l.CID', 'la.MID'));
		
		try {
			$raw = $select->query()->fetchAll();
		} catch (Exception $e) {
			echo 'Ошибка метода getLessonInfo: ',  $e->getMessage(), "\n";
			die;
		}
		
		if(empty($raw)){ return false; }
		$data = array();
		foreach($raw as $i){
			$mark_key = $i['mid'].'~'.$i['cid'];
			$data[$mark_key] = array(
				'min_date_assign' => date('d.m.Y', strtotime($i['min_date_assign'])),
			);
		}
		return $data;
	}
	
	private function getInterviewInfo($subject_ids, $student_ids)
	{
		$data            = array();
		$lessonRelations = array();
		
		if(empty($subject_ids) || empty($student_ids)){ return false; }
		
		$serviceUser = $this->getService('User');
		$select = $serviceUser->getSelect();
		$select->from(array('l' => 'schedule'), array(
			'lesson_id'  => 'l.SHEID',
			'subject_id' => 'l.CID',
		));		
		$select->where($serviceUser->quoteInto('l.CID  IN (?)', $subject_ids));
		$select->where($serviceUser->quoteInto('l.typeID = ?', HM_Event_EventModel::TYPE_TASK));
		
		try {
			$raw = $select->query()->fetchAll();
		} catch (Exception $e) {
			echo 'Ошибка метода getInterviewInfo при поиске занятий: ',  $e->getMessage(), "\n";
			die;
		}
		if(empty($raw)){ return false; }
		foreach($raw as $lesson){
			$lessonRelations[$lesson['lesson_id']] = $lesson['subject_id'];
		}
		if(empty($lessonRelations)){ return false; }
		
		$selectSub = $serviceUser->getSelect();
		$selectSub->from(array('interview'), array(
			'lesson_id'      => 'lesson_id',
			'interview_hash' => 'interview_hash',
			'last_date'      => new Zend_Db_Expr("MAX(date)"),			
		));
		$selectSub->where($serviceUser->quoteInto('lesson_id IN (?)', array_keys($lessonRelations)));
		$selectSub->where($serviceUser->quoteInto(array('( user_id IN (?) ', ' OR to_whom IN (?) )' ), array($student_ids, $student_ids)));
		$selectSub->group(array('lesson_id', 'interview_hash'));
	 	
		$select    = $serviceUser->getSelect();
		$select->from(array('i' => 'interview'), array(
			'lesson_id' => 'i.lesson_id',
			'user_id'   => 'i.user_id',
			'to_whom'   => 'i.to_whom',
			'type'      => 'i.type',
		));
		$select->join(array('i2' => $selectSub), 'i.interview_hash = i2.interview_hash AND i.date = i2.last_date', array());
		$select->where($serviceUser->quoteInto('i.lesson_id IN (?)', array_keys($lessonRelations)));
		$select->where($serviceUser->quoteInto(array('( i.user_id IN (?) ', ' OR i.to_whom IN (?) )' ), array($student_ids, $student_ids)));
		
		try {
			$raw = $select->query()->fetchAll();
		} catch (Exception $e) {
			echo 'Ошибка метода getInterviewInfo: ',  $e->getMessage(), "\n";
			die;
		}		
		if(empty($raw)){ return false; }
		
		foreach($raw as $i){
			$student_id = !empty($i['to_whom']) ? $i['to_whom'] : $i['user_id'];
			$subject_id = $lessonRelations[$i['lesson_id']];
			$key        = $student_id . '~' . $subject_id;
						
			$data[$key][$i['lesson_id']] = array(
				'student_id' => $student_id,
				'type'       => $i['type'],
				'lesson_id'  => $i['lesson_id'],
				'subject_id' => $subject_id,
			);
		}
		return $data;
	}
	
	
	public function dateFormatted($date = false)
	{
		$timestamp = strtotime($date);
		if($timestamp <= 0){ return ''; }
		return date('d.m.Y', $timestamp);
	}
	
	
	
	public function getCsvAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		$filename = 'Отчет по сессиям студента от '.date('d.m.Y H.i');
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		
		$this->restoreFromCache();						
		
		
		$fields = array(
			_('Студент'),
			_('Код 1С'),
			_('Группа'),
			_('Дата назначения курса (не позднее)'),
			_('Студент: назначен'),
			_('Студент: завершен'),
			_('Студент: продление 1'),
			_('Студент: продление 2'),
			_('Сессия'),
			_('Cессия: код'),
			_('Cессия: семестр'),
			_('Cессия: начало'),
			_('Cессия: окончание'),
			_('Cессия: продление 1'),
			_('Cессия: продление 2'),
			_('Cессия для студента'),
			_('Cессия: ссылка'),
			_('Тьюторы. Доступны'),
			_('Тьюторы. Не доступны'),
			_('Балл'),			
			_('Решение на проверку'),
		);		
		//добавялем BOM
		echo "\xEF\xBB\xBF";
		echo implode(';', $fields);
		echo "\r\n";
		$delim = ';';
		foreach($this->report_data as $i){
			
			$lastMessage = _('Нет');
			if($i['hasSolutionToCheck']){
				$lastMessage = _('Да');				
			} elseif($i['hasTeacherAnswer']){
				$lastMessage = _('Ответ преподавателя');
			}
			
			echo $i['student_fio'].$delim;
			echo $i['student_mid_external'].$delim;
			echo $i['student_group_name'].$delim;
			echo $i['lesson']['min_date_assign'] . $delim;			
			echo $i['student_time_registered'].$delim;
			echo $i['student_time_graduated'].$delim;
			echo $i['student_time_ended_debtor'].$delim;
			echo $i['student_time_ended_debtor_2'].$delim;
			echo $i['subject_name'].$delim;
			echo empty($i['subject_external_id']) ? $delim : '.'.$i['subject_external_id'].$delim;
			echo $i['subject_semester'].$delim;
			echo $i['subject_begin'].$delim;
			echo $i['subject_end'].$delim;
			echo $i['subject_time_ended_debt'].$delim;
			echo $i['subject_time_ended_debt_2'].$delim;
			echo $i['subject_status_for_student'].$delim;
			echo 'http://'.$_SERVER['SERVER_NAME'].$this->view->baseUrl($this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id']))).$delim;			
			
			$tutors_str_available = '';
			$tutors_str_not_available = '';
			if(!empty($i['tutors'])){				
				foreach($i['tutors'] as $tutor){
					if($tutor['available']) { $tutors_str_available .= ', '.$tutor['fio']; 		} 
					else 					{ $tutors_str_not_available .= ', '.$tutor['fio'];	}					
				}				
			}
			echo trim($tutors_str_available, ',').$delim;
			echo trim($tutors_str_not_available, ',').$delim;
			
			$mark = '';
			if(!empty($i['mark'])){						
				$mark = $i['mark']['mark'].' ('.$i['mark']['mark_current'].'/'.$i['mark']['mark_landmark'].')';
			}
			echo $mark.$delim;
			echo $lastMessage . $delim;
			
			echo "\r\n";
		}				
	}
	

	

}