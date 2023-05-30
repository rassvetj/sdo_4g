<?php

class Workload_ReportController extends HM_Controller_Action
{		
	private $_getClosedSubjects 	= false;
	private $_tutorFacultyList 		= array(); //-- [tutor_id] => [faculty name]
	private $_groupList 			= array(); //-- все группы.
	private $_tutorDepartmentList 	= array();
	private $_subjectGroupList 		= array();
	
	private $_periodAutumn = array('begin' => '01.09', 'end' => '31.01');	#осенний
	private $_periodSpring = array('begin' => '01.02', 'end' => '30.06');	#весенний
	
	###########################################
	/**
	 * общий экшен текущей нагрузки пользователя
	 * доступен орг. обучения и тютору.	 
	*/
	public function workloadAction(){		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$this->getService('Unmanaged')->setHeader(_('Выполнение педагогической нагрузки'));
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		
		$form = $this->getDefaultForm();			
		$this->view->form = $form;
	}
	
	/**
	 * Отчет "Просрочка" общий. Доступен тьютору, орг обучения, наблюдателю
	*/	
	public function violationsAction(){	
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$this->getService('Unmanaged')->setHeader(_('Нарушение сроков реагирования'));
		
		$form = $this->getDefaultForm();		
		$this->view->form = $form;
	}
	
	/**
	 * Отчет "Нагрузка" для организаторов обучения. Окончательный
	*/	
	public function workloadEndAction(){		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}		
		$this->getService('Unmanaged')->setHeader(_('Выполнение педагогической нагрузки. Окончательный отчет.'));
		
		$form = $this->getDefaultForm();
		$form->addElement('hidden', 'isEnd', array(
			'Required' => true,
			'value' => 1,				
		));			
		$this->view->form = $form;
	}
	###########################################
	
	
	
	
	public function modifyAction(){
		/*
		if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
		*/	
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		
		$form = new HM_Form_Report();
		$this->modifyForm($form);
		
		echo $form;
	}
	
	public function endModifyAction(){	
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		
		$this->_getClosedSubjects = true;		
		$form = new HM_Form_Report();
		
		$form->addElement('hidden', 'isEnd', array(
			'Required' => true,
			'value' => 1,				
		));	
		
		$this->modifyForm($form);
		echo $form;
	}
	
	
	/**
	 * Задает форму по умолчанию. Стартовую
	 * @return Zend_Form
	*/
	public function getDefaultForm(){
		$form = new HM_Form_Report();
		
		$userService = $this->getService('User');
		if (in_array($userService->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {													
			$list = array();
			$list = $this->subjectToList($userService->getCurrentUserId(), $studentIDs);
			$form->addElement('select', 'subject_id', array(
				'Label' => _('Курсы'),
				'Required' => false,
				'multiOptions' => $list,
				'Validators' => array('Int'),
				'Filters' => array('Int'),				
			));
		}
		
		return $form;		
	}

	
	/**
	 * изменяет поля формы в зависимости от переданных параметров
	*/
	public function modifyForm(Zend_Form $form){		
		
		$user_id = $this->_request->getParam('user_id', false);		
		$subject_id = $this->_request->getParam('subject_id', false);		
		$date_begin = $this->_request->getParam('date_begin', false);		
		$date_end = $this->_request->getParam('date_end', false);	
		$date_fix = $this->_request->getParam('date_fix', false); //--до какой даты расчитывать. Если пусто, то tine()	
		$type_do	= $this->_request->getParam('type_do', false); 
		//$actionURL = $this->_request->getHeader('referer');
		
		
		$el_date_begin = $form->getElement('date_begin');
		if($el_date_begin){
			$el_date_begin->setValue($date_begin);
		}
		
		$el_date_end = $form->getElement('date_end');
		if($el_date_end){
			$el_date_end->setValue($date_end);
		}
		
		$el_date_fix = $form->getElement('date_fix');
		if($el_date_fix){
			$el_date_fix->setValue($date_fix);
		}
		
		$el_type_do = $form->getElement('type_do');
		if($el_type_do){
			$el_type_do->setValue($type_do);
		}
		
		
		$userService = $this->getService('User');		
		$list = array();		
		
		if (in_array($userService->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {											
			$list = $this->subjectToList($userService->getCurrentUserId());
		} else {					
			$el_user_id = $form->getElement('user_id');
			$el_user_id->setValue($user_id);
			
			$list = $this->subjectToList($user_id);			 
		}
		
		if($user_id && $user_id > 0){		
			$form->addElement('select', 'subject_id', array(
				'Label' => _('Курсы'),
				'Required' => false,
				'multiOptions' => $list,
				'Validators' => array('Int'),
				'Filters' => array('Int'),				
			));
			
			if($subject_id){
				$el_subject_id = $form->getElement('subject_id');
				$el_subject_id->setValue($subject_id);
			}
		}
		
		//$form->setAction($actionURL);
		return $form;		
	}
	
	/**
	 * формируем массив для селекта
	*/
	public function subjectToList($user_id){				
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}		
		
		if($this->_getClosedSubjects === true){								
			$subjects = $this->getService('WorkloadSheet')->getCloseSubjectList($user_id);			
		} else {					
			$subjects = $this->getService('WorkloadSheet')->getOpenSubjectList($user_id);				
		}		
		$list = array('' => 'Нет');		
		if($subjects){
			$list = array('-1' => 'Все');
			foreach($subjects as $s){
				$list[$s['subid']] = $s['name'];				
			}
		}		
		return $list;
	}
	

	
	
	
	#################### 0000000009999
	/**
	 * Отчет "Просрочка" Общий. Доступен  для тьютора, наблюдателя и орг обучения
	*/	
	/*	
	public function getViolationsReportAction(){
		ini_set('memory_limit', '2548M'); # выборка из БД занимает чуть юольше 1Гб, что превышает ограничение, указанные в php.ini. Неоходимо оптимизировать запрос для сокращения расходуемой памяти. После можно убрать эту строку.
		//--добавить проверку на доступность сессии и тьютора в параметрах запроса
		$config = Zend_Registry::get('config');
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$this->getHelper('viewRenderer')->setNoRender();		
		
		try { 
			if(!$serviceUser){ $serviceUser = $this->getService('User'); }			
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			if(!$this->_serviceWorkloadSheet){ $this->_serviceWorkloadSheet = $this->getService('WorkloadSheet');	}
			if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
			
			$user_id = $this->_request->getParam('user_id', false);					
			$subject_id = $this->_request->getParam('subject_id', false);

			
			$year = date('Y',time());
			$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.
			
			$date_begin = $this->_request->getParam('date_begin', $year.'-09-01'); //--дата отсчета с 01 сентября каждого года.
			$date_end = $this->_request->getParam('date_end', date('Y-m-d', time()));
			$isEnd = $this->_request->getParam('isEnd', false); //--для окончательного отчета. Значит, что берем только сессии, по которым ведомость передана
			
			
			$dateEndIsEmpty = false; //-- флаг. Значит, что дата окончания явно не указана.			
			$t = $this->_request->getParam('date_end', false);
			if(empty($t)){ 
				$dateEndIsEmpty = true;
			}
			
			$validator = new Zend_Validate_Date(array('format' => 'dd.MM.yyyy',));
			$validator2 = new Zend_Validate_Date(array('format' => 'yyyy-MM-dd',));
			if( $validator->isValid($date_begin) || $validator2->isValid($date_begin) ){
				$date_begin =  date('Y-m-d',strtotime($date_begin));
			}  else {
				$date_begin = $year.'-09-01';	
			}
			
			if( $validator->isValid($date_end) || $validator2->isValid($date_end) ){
				$date_end =  date('Y-m-d',strtotime($date_end));
			}  else {
				$date_end = date('Y-m-d', time());
			}
			
			if (in_array($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
				if($serviceUser->getCurrentUserId() != $user_id){
					$user_id = $serviceUser->getCurrentUserId();
					$subject_id = false;
				}
			}
			
			
			//$date_end = ($validator->isValid($date_end) ) ? (date('Y-m-d',strtotime($date_end))) : (date('Y-m-d', time()));
			
			if($user_id) 						{ $urlParams['user_id'] = $user_id; 		}
			if($subject_id) 					{ $urlParams['subject_id'] = $subject_id; 	}
			if($date_begin) 					{ $urlParams['date_begin'] = $date_begin; 	}
			if($date_end && !$dateEndIsEmpty) 	{ $urlParams['date_end'] = $date_end; 		}
			if($isEnd) 							{ $urlParams['isEnd'] = $isEnd; 			}
			$this->view->urlParams = $urlParams;
			$this->view->caption = _('Нарушение сроков реагирования для ФДО');			
			
			if($user_id > 0){ //--выбран тьютор	
				$tutorIDs = array($user_id);
			} else { 				
				$tutors = $this->_serviceWorkload->getListOrgstructurePersons($serviceUser->getCurrentUserId(), $isEnd);	
				$tutorIDs = array_filter(array_keys ($tutors), 'is_int');				
			}
			
			if(!count($tutorIDs)){
				echo _('Не выбран пользователь');
				exit;
			}
			
			if($subject_id > 0){	$subjectIDs = array($subject_id);	}
			else {					$subjectIDs = false;				}
			
			
			//--список групп.
			$select_groups = $this->_serviceWorkload->getSelect();
			$select_groups->from('study_groups', array('group_id', 'name'));
			$res_groups = $select_groups->query()->fetchAll();			
			unset($select_groups);
			if(count($res_groups)){
				foreach($res_groups as $g){
					$this->_groupList[$g['group_id']] = $g['name'];
				}
			}
			unset($res_groups);
			
									
			$select = $this->_serviceWorkload->getSelect();
			$ball_select = $this->_serviceWorkload->getSelect();
			if($isEnd){
				$select->where('subj.isSheetPassed IS NOT NULL');
			} else {
				$select->where('subj.isSheetPassed IS NULL');
			}
			
			if($user_id > 0){ //--выбран тьютор											
				if($subjectIDs){ //--выбрана сессия										
					$select->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subjectIDs));
					$ball_select->where($this->_serviceWorkload->quoteInto('l.CID IN (?)', $subjectIDs));
				} 	
			}
			
			$select->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));
			$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
			$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			
			
			
			# получаем список сессий - уроков по указанным тьюторам.############################
			$selectLessons = $this->_serviceWorkload->getSelect();
			$selectLessons->from(array('l' => 'schedule'),
				array(				
					'lasson_id'		=> 'l.SHEID',
					'session_id'	=> 'l.CID',
				)
			);
			$selectLessons->join(
				array('session' => 'subjects'),
				'session.subid = l.CID',
				array()
			);		
			$selectLessons->join(
				array('t' => 'Tutors'),
				'session.subid = t.CID',
				array()
			);
			$selectLessons->where('session.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); 
			$selectLessons->where('session.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			$selectLessons->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));	
			$selectLessons->group(array('l.SHEID', 'l.CID'));
			$resLesson = $selectLessons->query()->fetchAll();
			unset($selectLessons);
			$sLessons = array(); # array('session_id' => array('leddon_id'))
			foreach($resLesson as $l){
				$sLessons[$l['session_id']][] = $l['lasson_id'];
			}
			unset($resLesson);
			############################	
			
			
			
			
			
			//--подзапрос на кол-во непроверенных записей.  //-- студенты без оценки			
			$ball_select->from(
				array('l' => 'schedule'),
				array(										
					'subid' => 'l.CID',
					'lesson_id' => 'l.SHEID',
					'notBall' => 'COUNT(DISTINCT sh.SSID)'					
				)
			);				
			$ball_select->joinLeft(
				array('sh' => 'scheduleID'),
				'sh.SHEID = l.SHEID AND sh.V_STATUS < 0 AND sh.MID > 0',																										
				array()		
			);
			$ball_select->where('l.typeID = ?',HM_Event_EventModel::TYPE_TASK);
						
			$ball_select->group(array('l.CID', 'l.SHEID'));
			$ball_res = $ball_select->query()->fetchAll();
			unset($ball_select);
			$notBalls = array();
			if($ball_res){
				foreach($ball_res as $b){ 
					if(!isset($notBalls[$b['subid']])){
						$notBalls[$b['subid']] = $b['notBall'];	
					} else { //--суммируем непроверенных студентов из всех уроков этой сессии.
						$notBalls[$b['subid']] += $b['notBall'];
					}										
				}
			}	
			unset($ball_res);

			# объединение студентов и завершивших обучение.
			$subUsers = $this->_serviceWorkload->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
				
			$subGrad = $this->_serviceWorkload->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
				
			$subUSelect = $this->_serviceWorkload->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));			
			
			if (in_array($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {	
			######## тьютор
				
				$select->from(
					array('p' => 'People'),
					array(				
						'tutor_id' 		=> 'p.MID',					
						'name' 			=> 'subj.name',																								
						'subid' 		=> 'subj.subid',															
						'end' 			=> 'subj.end',											
						'date_assign'	=> 't.date_assign',											
					)
				);
				$select->join(
					array('t' => 'Tutors'),
					'p.MID = t.MID',
					array()
				);		
				$select->join(
					array('subj' => 'subjects'),
					't.CID = subj.subid',
					array()
				);
				
				$select->joinLeft(
					array('s' => $subUSelect),
					's.CID = subj.subid',				
					array('students' => 'COUNT(DISTINCT s.MID)')
				);	

				
				$select->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии
				
				$select->where($this->_serviceWorkload->quoteInto('subj.end <= ?', $date_end)); # сессии, которые на дату окончания периода завершились. Открытые не берем.
					
				$select->group(array('subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end', 't.date_assign'));				
				
				
				$res_0 = $select->query()->fetchAll();
				unset($select);
				unset($subUSelect);
				unset($subUsers);
				unset($subGrad);
				
				$res = new ArrayIterator($res_0);
				$selectedSubjectIDs = array(); //--id отобранных сессий.
				foreach($res as $r){
					$selectedSubjectIDs[$r['subid']] = $r['subid'];
				}				
				$this->setSubjectGroupList($selectedSubjectIDs); //-_получаем массив сессия - группы этой сессии.
				
				
				$content = false;
				if($res){														
					$content = 	array();					
					foreach($res as $i){						
						$date_end_mod = $date_end;
						$date_begin_mod = $date_begin;
						
						//--ограничение на период окончания забора данных для расчета нагрузки и просрочки.
						if($dateEndIsEmpty && $i['end']){							
							if(strtotime($i['end']) > 0 && strtotime($i['end']) < strtotime($date_end)){																
								$date_end_mod = date('Y-m-d', strtotime($i['end']));		//--исключаем данные, которые созданы после завершения сессии 			
							}
						}
						
						//--если дата назначения позже даты начала периода, то берем дату назначения на сессию как начала периода.
						
						if(strtotime($i['date_assign']) > 0){
							if(strtotime($i['date_assign']) > strtotime($date_begin)){
								$date_begin_mod = date('Y-m-d', strtotime($i['date_assign']));
							}
						}
												
						$subjectGroups = $this->_subjectGroupList[$i['subid']];						
						$groups = '';
						if($subjectGroups && count($subjectGroups)){
							$groups = implode($subjectGroups, ',');
						}
						
						
						
						############################ кол-во записей, где студент задал вопрос, но на него не ответил препод.				
						$newWorks = 0;
						if(isset($sLessons[$i['subid']]) && count($sLessons[$i['subid']])){													
							$newWorks = $this->getNewWorks($i['subid'], $sLessons[$i['subid']], $date_begin_mod, $date_end_mod);
						} else {
							# нет уроков в сессии => нет сообщиней. Пропускаем?
						}
						############################	
						
						
						
						$T_message = $this->_serviceWorkloadSheet->getAvgT_Message($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod); //--просрочка по письму - берем дату из таблицы просрочек, 					
						$vi_MessageCount = ($T_message > 0) ? (1) : (0);
						
						$T_subject = $this->_serviceWorkloadSheet->getAvgT_Subject($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod, true); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
						$T_subject_links = $T_subject['links'];
						$T_subject_avg = $T_subject['avg'];
						$urls = array();
						if(count($T_subject_links)){
							foreach($T_subject_links as $link){
								$urls[] = $this->createViolationLink($link, 'lesson');								
							}
						}
						
						
						$T_subject_ActiveStudents = $T_subject['activeStudents'];
						$vi_SubjectCount = ($T_subject['count_violations'] > 0) ? ($T_subject['count_violations']) : (0);
						
						$T_forum = 	 $this->_serviceWorkloadSheet->getAvgT_Forum($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod, true); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
						$T_forum_links = $T_forum['links'];						
						if(count($T_forum_links)){
							foreach($T_forum_links as $link){
								$urls[] = $this->createViolationLink($link, 'forum');								
							}
						}
						
						
						$T_forum_avg = $T_forum['avg'];
						$T_forum_ActiveStudents = $T_forum['activeStudents'];
						$vi_ForumCount = ($T_forum['count_violations'] > 0) ? ($T_forum['count_violations']) : (0);
						
						$T_vedomost = $this->_serviceWorkloadSheet->getAvgT_Vedomost($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod); //--просрочка по предоставлению ведомости
						
						
						$vi_VedomostCount = ($T_vedomost > 0) ? (1) : (0);
						
						$count_violations = $vi_MessageCount + $vi_SubjectCount + $vi_ForumCount + $vi_VedomostCount;						
						
						$content[] = array(
							'name' 				=> $i['name'],												
							'groups' 			=> $groups,									
							'students' 			=> $i['students'],

							'notBall' 			=> $notBalls[$i['subid']],							
							
							'vi_MessageCount' 	=> $vi_MessageCount,
							'T_message' 		=> $this->getDays($T_message),
							
							'vi_SubjectCount' 	=> $vi_SubjectCount,
							'T_subject_avg' 	=> $this->getDays($T_subject_avg),
							
							'vi_ForumCount' 	=> $vi_ForumCount,
							'T_forum_avg' 		=> $this->getDays($T_forum_avg),							
							
							'vi_VedomostCount' 	=> $vi_VedomostCount,
							'T_vedomost' 		=> $this->getDays($T_vedomost),
							
							'count_violations' 	=> $count_violations,
							'urls' 				=> $urls,
							'newWorks'			=> $newWorks,
						);						
					}
					
					###### Экспорт в Excel
					$isExportExcel = $this->_request->getParam('export', false);
					if($isExportExcel == 'excel'){
						$period = false;
						$dBegin = $this->_request->getParam('date_begin', false);
						$dEnd = $this->_request->getParam('date_end', false);
						if($dBegin) { $period = 'c '.$dBegin; 		}
						if($dEnd) 	{ $period .= ' по '.$dEnd; 		}												
						$this->view->period = $period;
						$this->view->content = $content;
						$tt = $this->view->render('report/parts/excel/violations/_tutor.tpl');						
						$this->getExportExcel($tt, 'violations');						
						exit();						
					}			
					$this->view->getParams = $this->_request->getParams();
					$this->view->content = $content;									
				} 				
				$content = $this->view->render('report/parts/_violations_report_tutor.tpl');
				echo $content;
			################# наблюдатель
			} elseif (in_array($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))) {	 
			#################

				$select->from(
					array('p' => 'People'),
					array(				
						'tutor_id' => 'p.MID',	
						'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),						
						'name' => 'subj.name',																									
						'subid' => 'subj.subid',					
						'name_plan' => 'ls.name_plan',
						'end' => 'subj.end',
						'date_assign'	=> 't.date_assign',						
					)
				);
				$select->join(
					array('t' => 'Tutors'),
					'p.MID = t.MID',
					array()
				);		
				$select->join(
					array('subj' => 'subjects'),
					't.CID = subj.subid',
					array()
				);
				$select->joinLeft(
					array('ls' => 'learning_subjects'),
					'ls.id_external = subj.learning_subject_id_external',
					array()
				);				
				$select->join(
					array('s' => $subUSelect),
					's.CID = subj.subid',
					array()
				);				
				
				$select->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии
				$select->where($this->_serviceWorkload->quoteInto('subj.end <= ?', $date_end)); # сессии, которые на дату окончания периода завершились. Открытые не берем.
					
					
				$select->group(array('p.LastName', 'p.FirstName', 'p.Patronymic', 'subj.begin', 'subj.name', 'p.MID','subj.subid','ls.name_plan', 'subj.end', 't.date_assign'));
				
							
				$res_0 = $select->query()->fetchAll();
				$res = new ArrayIterator($res_0);
				$selectedSubjectIDs = array(); //--id отобранных сессий.
				foreach($res as $r){
					$selectedSubjectIDs[$r['subid']] = $r['subid'];
				}				
				$this->setSubjectGroupList($selectedSubjectIDs); //-_получаем массив сессия - группы этой сессии.
				
				$content = false;
				
				if($res){									
					$content = array();									
					foreach($res as $i){
						$date_end_mod 	= $date_end;
						$date_begin_mod = $date_begin;
						//--ограничение на период окончания забора данных для расчета нагрузки и просрочки.
						if($dateEndIsEmpty && $i['end']){							
							if(strtotime($i['end']) > 0 && strtotime($i['end']) < strtotime($date_end)){																
								$date_end_mod = date('Y-m-d', strtotime($i['end']));		//--исключаем данные, которые созданы после завершения сессии 			
							}
						}
						
						//--если дата назначения позже даты начала периода, то берем дату назначения на сессию как начала периода.
						if(strtotime($i['date_assign']) > 0){
							if(strtotime($i['date_assign']) > strtotime($date_begin)){
								$date_begin_mod = date('Y-m-d', strtotime($i['date_assign']));
							}
						}
						
						
						$subjectGroups = $this->_subjectGroupList[$i['subid']];
						$groups = '';
						if($subjectGroups && count($subjectGroups)){
							$groups = implode($subjectGroups, ',');
						}
						
						
						############################ кол-во записей, где студент задал вопрос, но на него не ответил препод.				
						$newWorks = 0;
						if(isset($sLessons[$i['subid']]) && count($sLessons[$i['subid']])){							
							$newWorks = $this->getNewWorks($i['subid'], $sLessons[$i['subid']], $date_begin_mod, $date_end_mod);
						} else {
							# нет уроков в сессии => нет сообщиней. Пропускаем?
						}
						############################
						
						
							
						$count_violations = 0;
						
						$T_message = $this->_serviceWorkloadSheet->getAvgT_Message($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod); //--просрочка по письму - берем дату из таблицы просрочек, 					
						if($T_message > 0) { $count_violations++; }
						
						$T_subject = $this->_serviceWorkloadSheet->getAvgT_Subject($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod, true); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
						$T_subject_links = $T_subject['links'];
						$urls = array();
						if(count($T_subject_links)){
							foreach($T_subject_links as $link){
								$urls[] = $this->createViolationLink($link, 'lesson');								
							}
						}
						
						$T_subject_avg = $T_subject['avg'];
						$T_subject_ActiveStudents = $T_subject['activeStudents'];
						$count_violations = $count_violations + $T_subject['count_violations'];
						
						$T_forum = 	 $this->_serviceWorkloadSheet->getAvgT_Forum($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod, true); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
						$T_forum_links = $T_forum['links'];
						if(count($T_forum_links)){
							foreach($T_forum_links as $link){
								$urls[] = $this->createViolationLink($link, 'forum');								
							}
						}
						
						
						$T_forum_avg = $T_forum['avg'];
						$T_forum_ActiveStudents = $T_forum['activeStudents'];
						$count_violations = $count_violations + $T_forum['count_violations'];
						
						$T_vedomost =$this->_serviceWorkloadSheet->getAvgT_Vedomost($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod); //--просрочка по предоставлению ведомости
						if($T_vedomost > 0) { $count_violations++; }
						
						$avg = ( ($T_message + $T_subject_avg + $T_forum_avg + $T_vedomost) / 4 );
						
						
						if(isset($this->_tutorFacultyList[$i['tutor_id']])){
							$facultyName = $this->_tutorFacultyList[$i['tutor_id']];
						} else {
							$facultyName = $this->updateFaculty($i['tutor_id']);
							$this->_tutorFacultyList[$i['tutor_id']] = $facultyName;
						}
						
						if(isset($this->_tutorDepartmentList[$i['tutor_id']])){
							$departmentName = $this->_tutorDepartmentList[$i['tutor_id']];
						} else {
							$departmentName = $this->_serviceOrgstructure->getDepartmentName($i['tutor_id']);								
							$this->_tutorDepartmentList[$i['tutor_id']] = $departmentName;
						}
						
						$content[] = array(											
							'fio' 				=> $i['fio'],
							'department' 		=> $departmentName,
							'faculty' 			=> $facultyName,
							'name' 				=> $i['name'],
							'name_plan'			=> $i['name_plan'],
							'groups' 			=> $groups,
							'count_violations' 	=> $count_violations,
							'avg' 				=> $this->getDays($avg),																													
							'notBall' 			=> $notBalls[$i['subid']],											
							'urls' 				=> $urls,	
							'newWorks'			=> $newWorks,							
						);
					}
					
					###### Экспорт в Excel					
					$isExportExcel = $this->_request->getParam('export', false);
					if($isExportExcel == 'excel'){
						$period = false;
						$dBegin = $this->_request->getParam('date_begin', false);
						$dEnd = $this->_request->getParam('date_end', false);
						if($dBegin) { $period = 'c '.$dBegin; 		}
						if($dEnd) 	{ $period .= ' по '.$dEnd; 		}												
						$this->view->period = $period;
						$this->view->content = $content;
						$tt = $this->view->render('report/parts/excel/violations/_supervisor.tpl');						
						$this->getExportExcel($tt, 'violations');						
						exit();
					}						
					$this->view->getParams = $this->_request->getParams();
					$this->view->content = $content;
				}
				$content = $this->view->render('report/parts/_violations_report_supervisor.tpl');
				echo $content;
			#################
			} else {
			################# орг обучения				
				$select->from(
					array('p' => 'People'),
					array(				
						'tutor_id' => 'p.MID',	
						'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),						
						'name' => 'subj.name',																									
						'subid' => 'subj.subid',
						'end' => 'subj.end',
						'date_assign'	=> 't.date_assign',							
					)
				);
				$select->join(
					array('t' => 'Tutors'),
					'p.MID = t.MID',
					array()
				);		
				$select->join(
					array('subj' => 'subjects'),
					't.CID = subj.subid',
					array()
				);
				$select->join(
					array('s' => $subUSelect),
					's.CID = subj.subid',
					array()
				);				
				
				
				$select->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии
				$select->where($this->_serviceWorkload->quoteInto('subj.end <= ?', $date_end)); # сессии, которые на дату окончания периода завершились. Открытые не берем.
				
					
				$select->group(array('p.LastName', 'p.FirstName', 'p.Patronymic', 'subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end', 't.date_assign'));
				
				$fio_column = array('title' => _('ФИО'));			
				if (in_array($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {	
					$fio_column = array('hidden' => true);				
				}			
				
				$res = $select->query()->fetchAll();	
				$content = false;	
				if($res){
					$content = array();	
					$data = array();					
					foreach($res as $i){						
						$date_end_mod 	= $date_end;
						$date_begin_mod = $date_begin;
						//--ограничение на период окончания забора данных для расчета нагрузки и просрочки.
						if($dateEndIsEmpty && $i['end']){							
							if(strtotime($i['end']) > 0 && strtotime($i['end']) < strtotime($date_end)){																
								$date_end_mod = date('Y-m-d', strtotime($i['end']));		//--исключаем данные, которые созданы после завершения сессии 			
							}
						}
						
						//--если дата назначения позже даты начала периода, то берем дату назначения на сессию как начала периода.
						if(strtotime($i['date_assign']) > 0){
							if(strtotime($i['date_assign']) > strtotime($date_begin)){
								$date_begin_mod = date('Y-m-d', strtotime($i['date_assign']));
							}
						}
						
						if(!isset($data[$i['tutor_id']]['fio'])){
							$data[$i['tutor_id']]['fio'] = $i['fio'];	
						}
						
						
						############################ кол-во записей, где студент задал вопрос, но на него не ответил препод.				
						
						if(!isset($data[$i['tutor_id']]['newWorks'])){ $data[$i['tutor_id']]['newWorks'] = 0; }
						
						if(isset($sLessons[$i['subid']]) && count($sLessons[$i['subid']])){							
							$data[$i['tutor_id']]['newWorks'] = $data[$i['tutor_id']]['newWorks'] + $this->getNewWorks($i['subid'], $sLessons[$i['subid']], $date_begin_mod, $date_end_mod);
						} else {
							# нет уроков в сессии => нет сообщиней. Пропускаем?
						}
						############################
						
						
							$T_message = $this->_serviceWorkloadSheet->getAvgT_Message($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod); //--просрочка по письму - берем дату из таблицы просрочек, 					
							if($T_message > 0){ $data[$i['tutor_id']]['count_violations']++; } //--кол-во нарушений.
							
						
						
							$T_subject = $this->_serviceWorkloadSheet->getAvgT_Subject($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod, true); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
							$T_subject_links = $T_subject['links'];
							if(count($T_subject_links)){
								foreach($T_subject_links as $link){
									$data[$i['tutor_id']]['urls'][] = $this->createViolationLink($link, 'lesson');								
								}
							}
						
							
							$T_subject_avg = $T_subject['avg'];						
							$data[$i['tutor_id']]['count_violations'] = $data[$i['tutor_id']]['count_violations'] + $T_subject['count_violations'];
							
							$T_forum = 	 $this->_serviceWorkloadSheet->getAvgT_Forum($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod, true); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
							$T_forum_links = $T_forum['links'];
							if(count($T_forum_links)){
								foreach($T_forum_links as $link){
									$data[$i['tutor_id']]['urls'][] = $this->createViolationLink($link, 'forum');								
								}
							}
							
							$T_forum_avg = $T_forum['avg'];						
							$data[$i['tutor_id']]['count_violations'] = $data[$i['tutor_id']]['count_violations'] + $T_forum['count_violations'];
							
							$T_vedomost =$this->_serviceWorkloadSheet->getAvgT_Vedomost($i['tutor_id'], $i['subid'], $date_end_mod, $date_begin_mod); //--просрочка по предоставлению ведомости
							if($T_vedomost > 0) { $data[$i['tutor_id']]['count_violations']++; }
							
							$data[$i['tutor_id']]['total'] = $data[$i['tutor_id']]['total'] + ( ($T_message + $T_subject_avg + $T_forum_avg + $T_vedomost) / 4 );						
							$data[$i['tutor_id']]['countSubjects']++;						
							
							$data[$i['tutor_id']]['notBall'] += $notBalls[$i['subid']];
						
						
					}														
					
					foreach($data as $tutor_id => $i){														
						$avg = 0;
						if($i['countSubjects'] > 0){
							$avg = 	$i['total'] /  $i['countSubjects'];
						}

						if(isset($this->_tutorFacultyList[$tutor_id])){
							$facultyName = $this->_tutorFacultyList[$tutor_id];
						} else {
							$facultyName = $this->updateFaculty($tutor_id);
							$this->_tutorFacultyList[$tutor_id] = $facultyName;
						}
						
						if(isset($this->_tutorDepartmentList[$tutor_id])){
							$departmentName = $this->_tutorDepartmentList[$tutor_id];
						} else {
							$departmentName = $this->_serviceOrgstructure->getDepartmentName($tutor_id);								
							$this->_tutorDepartmentList[$tutor_id] = $departmentName;
						}
						
						$content[] = array(
							'fio' 				=> $i['fio'],				
							'department' 		=> $departmentName,
							'faculty' 			=> $facultyName,
							'count_violations' 	=> $i['count_violations'],
							'avg' 				=> $this->getDays($avg),	
							'notBall' 			=> $i['notBall'],	
							'urls' 				=> $i['urls'],							
							'newWorks' 			=> $i['newWorks'],							
						);						
					}										
					###### Экспорт в Excel					
					$isExportExcel = $this->_request->getParam('export', false);
					if($isExportExcel == 'excel'){
						$period = false;
						$dBegin = $this->_request->getParam('date_begin', false);
						$dEnd = $this->_request->getParam('date_end', false);
						if($dBegin) { $period = 'c '.$dBegin; 		}
						if($dEnd) 	{ $period .= ' по '.$dEnd; 		}												
						$this->view->period = $period;
						$this->view->content = $content;
						$tt = $this->view->render('report/parts/excel/violations/_dean.tpl');						
						$this->getExportExcel($tt, 'violations');						
						exit();						
					}	
					$this->view->getParams = $this->_request->getParams();
					$this->view->content = $content;					
				} 
				$content = $this->view->render('report/parts/_violations_report_dean.tpl');
				echo $content;
			#################
			}
		} catch (Exception $e) {
			echo $e->getMessage();			
			echo _('Произошла ошибка');
		}
	}
	*/
	
	
	
	#################### 0000000009999
	/**
	 * ушел в отдельный экшен
	 * Отчет "О нагрузке" Текущий. Доступен тьютору и орг. оюучения
	*/
	/*	
	public function getWorkloadReportAction(){		
		//--добавить проверку на доступность сессии и тьютора в параметрах запроса
		$config = Zend_Registry::get('config');
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$this->getHelper('viewRenderer')->setNoRender();		
		try { 
			if(!$serviceUser){ $serviceUser = $this->getService('User'); }			
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			if(!$this->_serviceWorkloadSheet){ $this->_serviceWorkloadSheet = $this->getService('WorkloadSheet');	}
			if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}			
			
			$user_id = $this->_request->getParam('user_id', false);		
			$subject_id = $this->_request->getParam('subject_id', false);			
			
			$year = date('Y',time());
			$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.
			
			$date_begin = $this->_request->getParam('date_begin', $year.'-09-01'); //--дата отсчета с 01 сентября каждого года.
			$date_end = $this->_request->getParam('date_end', date('Y-m-d', time()));
			$isEnd = $this->_request->getParam('isEnd', false); //--для окончательного отчета. Значит, что берем только сессии, по которым ведомость передана
			
			
			$dateEndIsEmpty = false; //-- флаг. Значит, что дата окончания явно не указана.			
			$t = $this->_request->getParam('date_end', false);
			if(empty($t)){ 
				$dateEndIsEmpty = true;
			}
			
			
			$validator = new Zend_Validate_Date(array('format' => 'dd.MM.yyyy',));
			$validator2 = new Zend_Validate_Date(array('format' => 'yyyy-MM-dd',));
			if( $validator->isValid($date_begin) || $validator2->isValid($date_begin) ){
				$date_begin =  date('Y-m-d',strtotime($date_begin));
			}  else {
				$date_begin = $year.'-09-01';	
			}
			
			if( $validator->isValid($date_end) || $validator2->isValid($date_end) ){
				$date_end =  date('Y-m-d',strtotime($date_end));
			}  else {
				$date_end = date('Y-m-d', time());
			}
			
			if (in_array($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
				if($serviceUser->getCurrentUserId() != $user_id || $isEnd){
					$this->_flashMessenger->addMessage(array(
						'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('У вас нет доступа к этому разделу')
					));
					$this->_redirect('/');
				}				
			}
			
			if($user_id) 						{ $urlParams['user_id'] = $user_id; 		}
			if($subject_id) 					{ $urlParams['subject_id'] = $subject_id; 	}
			if($date_begin) 					{ $urlParams['date_begin'] = $date_begin; 	}
			if($date_end && !$dateEndIsEmpty) 	{ $urlParams['date_end'] = $date_end; 		}
			if($isEnd) 							{ $urlParams['isEnd'] = $isEnd; 			}
			$this->view->urlParams = $urlParams;
			$this->view->caption = _('Выполнение педагогической нагрузки для ФДО');	
			
			if($user_id > 0){ //--выбран тьютор	
				$tutorIDs = array($user_id);
			} else { 				
				$tutors = $this->_serviceWorkload->getListOrgstructurePersons($serviceUser->getCurrentUserId(), $isEnd);	
				$tutorIDs = array_filter(array_keys ($tutors), 'is_int');				
			}
			
			if(!count($tutorIDs)){
				echo _('Не выбран пользователь');
				exit;
			}
			
			if($subject_id > 0){	$subjectIDs = array($subject_id);	}
			else {					$subjectIDs = false;				}
			
			//--список групп.
			$select_groups = $this->_serviceWorkload->getSelect();
			$select_groups->from('study_groups', array('group_id', 'name'));
			$res_groups = $select_groups->query()->fetchAll();
			unset($select_groups);
			if(count($res_groups)){
				foreach($res_groups as $g){
					$this->_groupList[$g['group_id']] = $g['name'];
				}
			}
			unset($res_groups);
			
			$select = $this->_serviceWorkload->getSelect();
			
			if($isEnd){
				$select->where('subj.isSheetPassed IS NOT NULL');
			} else {
				$select->where('subj.isSheetPassed IS NULL');
			}
			
			if($user_id > 0){ //--выбран тьютор											
				if($subjectIDs){ //--выбрана сессия										
					$select->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subjectIDs));
				} 	
			}
			
			$select->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));
			$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
			
			# шруппировка студентов и прошедшх обучение.
			$subUsers = $this->_serviceWorkload->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
			
			$subGrad = $this->_serviceWorkload->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
			
			$subUSelect = $this->_serviceWorkload->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
			
			//--тьютор
			if (in_array($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
				#######
				
				$select->from(
					array('p' => 'People'),
					array(				
						'tutor_id' => 'p.MID',					
						'name' => 'subj.name',					
						'faculty' => 'p.MID',															
						'subid' => 'subj.subid',
						'end' => 'subj.end',												
					)
				);
				$select->join(
					array('t' => 'Tutors'),
					'p.MID = t.MID',
					array()
				);		
				$select->join(
					array('subj' => 'subjects'),
					't.CID = subj.subid',
					array()
				);
				
				$select->joinLeft(
					array('s' => $subUSelect),
					's.CID = subj.subid',				
					array('students' => 'COUNT(DISTINCT s.MID)')
				);			
				
				$select->where($this->_serviceWorkload->quoteInto(array('(subj.begin >= ? AND subj.time_ended_debt IS NOT NULL) OR (subj.time_ended_debt IS NULL) '), array($year.'-09-01')));
				#$select->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии	
				
				$select->group(array('subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end'));
								
				$res_0 = $select->query()->fetchAll();
				unset($select);
				unset($subUSelect);
				unset($subUsers);
				unset($subGrad);
				
				$res = new ArrayIterator($res_0);
				$selectedSubjectIDs = array(); //--id отобранных сессий.
				foreach($res as $r){
					$selectedSubjectIDs[$r['subid']] = $r['subid'];
				}				
				$this->setSubjectGroupList($selectedSubjectIDs); //-_получаем массив сессия - группы этой сессии.				
								
				$content = false;
				if($res){
					$content = array();					
					if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}										
					foreach($res as $i){						
						$date_end_mod = $date_end;
						//--ограничение на период окончания забора данных для расчета нагрузки и просрочки.
						if($dateEndIsEmpty && $i['end']){							
							if(strtotime($i['end']) > 0 && strtotime($i['end']) < strtotime($date_end)){																
								$date_end_mod = date('Y-m-d', strtotime($i['end']));		//--исключаем данные, которые созданы после завершения сессии 			
							}
						}						
						
						
						$subjectGroups = $this->_subjectGroupList[$i['subid']];						
						$subjectGroups = $this->getService('Subject')->filterGroupsByAssignStudents($i['subid'], $serviceUser->getCurrentUserId(), $subjectGroups);
						
						$groups = '';
						if($subjectGroups && count($subjectGroups)){
							$groups = implode($subjectGroups, ',');
						}
						
						$tutor_id = $i['tutor_id'];
						$subject_id = $i['subid'];
						
						$periods = $this->getPeriods(strtotime($date_begin), strtotime($date_end_mod));
						$workloadSpring = 0;
						$workloadAutumn = 0;						
						if($periods){							
							//$_F3 = $this->_serviceWorkload->getWorkload_F3($subject_id); //-вебираны не влияют на дату.
							$_F3 = 0; //-вебираны не влияют на дату.
							if(isset($periods['spring'])){								
								$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($tutor_id, $subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-приветственное письмо		
								$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($tutor_id, $subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-проверка заданий		
								$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($tutor_id, $subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-форум										
								$_F4 = $this->_serviceWorkload->getWorkload_F4($subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-ведомость передана. 
								$workloadSpring = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
							}							
							if(isset($periods['autumn'])){								
								$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($tutor_id, $subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-приветственное письмо		
								$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($tutor_id, $subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-проверка заданий		
								$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($tutor_id, $subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-форум										
								$_F4 = $this->_serviceWorkload->getWorkload_F4($subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-ведомость передана. 
								$workloadAutumn = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
							}														
						}
						
									
						$content[] = array(
							'name' => $i['name'],
							'groups' => $groups,
							'students' => $i['students'],
							'f0' => $_F0,
							'f1' => $_F1,
							'f2' => $_F2,
							'f4' => $_F4,						
							'workloadAutumn' => $workloadAutumn,
							'workloadSpring' => $workloadSpring,
							//'totalPeriod' => $date_begin.'-'.$date_end_mod,
							//'workloadAutumnPeriod' => date('Y-m-d', $periods['autumn']['begin']).'-'.date('Y-m-d', $periods['autumn']['end']),
							//'workloadSpringPeriod' => date('Y-m-d', $periods['spring']['begin']).'-'.date('Y-m-d', $periods['spring']['end']),
						);						
					}					
					###### Экспорт в Excel
					$isExportExcel = $this->_request->getParam('export', false);
					if($isExportExcel == 'excel'){
						$period = false;
						$dBegin = $this->_request->getParam('date_begin', false);
						$dEnd = $this->_request->getParam('date_end', false);
						if($dBegin) { $period = 'c '.$dBegin; 		}
						if($dEnd) 	{ $period .= ' по '.$dEnd; 		}												
						$this->view->period = $period;
						$this->view->content = $content;
						$tt = $this->view->render('report/parts/excel/workload/_tutor.tpl');						
						$this->getExportExcel($tt, 'workload');						
						exit();	
					}	
					$this->view->getParams = $this->_request->getParams();
					$this->view->content = $content;
				}				
				$content = $this->view->render('report/parts/_workload_report_tutor.tpl');
				echo $content;
			} else { //--орг обучения
				########
				
				$select->from(
					array('p' => 'People'),
					array(				
						'tutor_id' => 'p.MID',
						'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),									
						'name' => 'subj.name',					
						'faculty' => 'p.MID',															
						'subid' => 'subj.subid',
						'end' => 'subj.end',												
					)
				);
				$select->join(
					array('t' => 'Tutors'),
					'p.MID = t.MID',
					array()
				);		
				$select->join(
					array('subj' => 'subjects'),
					't.CID = subj.subid',
					array()
				);
				$select->joinLeft(
					array('s' => $subUSelect),
					's.CID = subj.subid',				
					array('students' => 'COUNT(DISTINCT s.MID)')
				);				
				
				#$select->where('subj.time_ended_debt IS NULL');//--Исключаем продленные сессии
				$select->where($this->_serviceWorkload->quoteInto(array('(subj.begin >= ? AND subj.time_ended_debt IS NOT NULL) OR (subj.time_ended_debt IS NULL) '), array($year.'-09-01')));
				
				$select->group(array('p.LastName', 'p.FirstName', 'p.Patronymic', 'subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end'));
				
				
				$res_0 = $select->query()->fetchAll();
				unset($select);
				
				$res = new ArrayIterator($res_0);
				$selectedSubjectIDs = array(); //--id отобранных сессий.
				foreach($res as $r){
					$selectedSubjectIDs[$r['subid']] = $r['subid'];
				}				
				$this->setSubjectGroupList($selectedSubjectIDs); //-_получаем массив сессия - группы этой сессии.
				
				$content = false;
				if($res){
					if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}					
					$content = array();										
					foreach($res as $i){
						$date_end_mod = $date_end;
						//--ограничение на период окончания забора данных для расчета нагрузки и просрочки.
						if($dateEndIsEmpty && $i['end']){							
							if(strtotime($i['end']) > 0 && strtotime($i['end']) < strtotime($date_end)){																
								$date_end_mod = date('Y-m-d', strtotime($i['end']));		//--исключаем данные, которые созданы после завершения сессии 			
							}
						}
						
						$subjectGroups = $this->_subjectGroupList[$i['subid']];
						$subjectGroups = $this->getService('Subject')->filterGroupsByAssignStudents($i['subid'], $i['tutor_id'], $subjectGroups);
						
						$groups = '';
						if($subjectGroups && count($subjectGroups)){
							$groups = implode($subjectGroups, ',');
						}
						
						if(isset($this->_tutorDepartmentList[$i['tutor_id']])){
							$departmentName = $this->_tutorDepartmentList[$i['tutor_id']];
						} else {
							$departmentName = $this->_serviceOrgstructure->getDepartmentName($i['tutor_id']);								
							$this->_tutorDepartmentList[$i['tutor_id']] = $departmentName;
						}
						
						
						$periods = $this->getPeriods(strtotime($date_begin), strtotime($date_end_mod));
						$workloadSpring = 0;
						$workloadAutumn = 0;						
						if($periods){							
							//$_F3 = $this->_serviceWorkload->getWorkload_F3($i['subid']); //-вебираны не влияют на дату.
							$_F3 = 0;
							if(isset($periods['spring'])){								
								$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($i['tutor_id'], $i['subid'], date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-приветственное письмо		
								$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($i['tutor_id'], $i['subid'], date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-проверка заданий		
								$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($i['tutor_id'], $i['subid'], date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-форум										
								$_F4 = $this->_serviceWorkload->getWorkload_F4($i['subid'], date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-ведомость передана. 
								$workloadSpring = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
							}							
							if(isset($periods['autumn'])){								
								$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($i['tutor_id'], $i['subid'], date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-приветственное письмо		
								$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($i['tutor_id'], $i['subid'], date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-проверка заданий		
								$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($i['tutor_id'], $i['subid'], date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-форум										
								$_F4 = $this->_serviceWorkload->getWorkload_F4($i['subid'], date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-ведомость передана. 
								$workloadAutumn = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
							}														
						}
						
						
						$content[] = array(
							'fio' => $i['fio'],
							'department' => $departmentName,
							'faculty' => $this->updateFaculty($i['faculty']),
							'name' => $i['name'],
							'groups' => $groups,
							'students' => $i['students'],
							//'workload' => $this->updateEndWorkloadFix($i['subid'], $i['tutor_id'], $date_end_mod, $date_begin),
							'workloadAutumn' => $workloadAutumn,
							'workloadSpring' => $workloadSpring,
							//'totalPeriod' => $date_begin.'-'.$date_end_mod,
							//'workloadAutumnPeriod' => date('Y-m-d', $periods['autumn']['begin']).'-'.date('Y-m-d', $periods['autumn']['end']),
							//'workloadSpringPeriod' => date('Y-m-d', $periods['spring']['begin']).'-'.date('Y-m-d', $periods['spring']['end']),
						);
					}					
					###### Экспорт в Excel
					$isExportExcel = $this->_request->getParam('export', false);
					if($isExportExcel == 'excel'){
						$period = false;
						$dBegin = $this->_request->getParam('date_begin', false);
						$dEnd = $this->_request->getParam('date_end', false);
						if($dBegin) { $period = 'c '.$dBegin; 		}
						if($dEnd) 	{ $period .= ' по '.$dEnd; 		}												
						$this->view->period = $period;
						$this->view->content = $content;
						$tt = $this->view->render('report/parts/excel/workload/_dean.tpl');						
						$this->getExportExcel($tt, 'workload');						
						exit();
					}	
					$this->view->getParams = $this->_request->getParams();
					$this->view->content = $content;
				} 
				$content = $this->view->render('report/parts/_workload_report_dean.tpl');
				echo $content;
			}			
		} catch (Exception $e) {
			//echo $e->getMessage();			
			echo _('Произошла ошибка');
		}
	}
	*/
	
	
	#################
	/**
	 * Отчет "Просрочка" Общий. Выгрузка в Excel
	 * $criteria - условия отбора. Выводятся над шапкой таблицы.
	*/		
	public function getExportExcel($content, $name = 'report'){
		
		$file_name = $name.'_'.date('Y.m.d_H-i',time());
		
		set_time_limit( 0 );
		
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=UTF-8" )
            ->setRawHeader( "Content-Disposition: attachment; filename=".$file_name.".xls" )
            ->setRawHeader( "Content-Transfer-Encoding: binary" )
            ->setRawHeader( "Expires: 0" )
            ->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
            ->setRawHeader( "Pragma: public" )        
            ->sendResponse();        		
		echo $content;
		exit();
	}
	
	
	public function updateStatus($id_violations, $seconds, $tutor_id, $subject_id){
		$status = '';		
		if($id_violations < 1){ //--просрочка НЕ зафиксирована в БД			
			$type = HM_Workload_WorkloadModel::TYPE_WELCOME_MESSAGE;			
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			
			$isDateFirstMsg = $this->_serviceWorkload->getTimeFirstMessage($tutor_id, $subject_id); //--есть приветственное сообщение
			$seconds = $this->_serviceWorkload->setCurrentWelcomeViolation($tutor_id, $subject_id, $type); //--фиксируем, если надо и получаем просрочку в секундах			
			if($isDateFirstMsg)	{	$status = _('Отправил');	}
			else {					$status = _('Не отправил');	}
		} else { $status = _('Отправил');	}
		if($seconds < 1){	$status .= _(' (не просрочил)');	}
		else {				$status .= _(' (просрочил)');		}				
		return $status;			
	}
	
	public function updateDate($date){
		if(!$date){ return _('Нет'); }		
		return date('d.m.Y', strtotime($date));		
	}
	
	
	/**
	 * По id сессии получаем кол-во назначенных студентов
	*/
	public function updateCountStudents($subject_id){
		$students = (isset($this->subject_students[$subject_id])) ? $this->subject_students[$subject_id] : (0);
		return $students;		
	}
	
	/**
	 * По id сессии получаем кол-во разделов с типом "задание на проверку"
	*/
	public function updateCountParts($subject_id){
		$parts = (isset($this->countParts[$subject_id])) ? $this->countParts[$subject_id] : (0);
		return $parts;
	}
	
	/**
	 * По id сессии и id тьютора получаем среднее время просрочки по сессии в секундах. Надо переводить в часы, минуты, дни, недели?
	*/
	public function updateAvgTime($subject_id, $tutor_id){
		$time = (isset($this->avgTime[$tutor_id][$subject_id]) && $this->avgTime[$tutor_id][$subject_id] >= 0) ? $this->avgTime[$tutor_id][$subject_id] : (false);				
		if($time){
			return $this->getDays($time);			
		} elseif($time === false){
			return _('Нет активных студентов');			
			//return 0;			
		}
		return 0;
	}
	
	
	/**
	 * По id сессии и id тьютора получаем среднее время просрочки по всем типам просрочки
	*/
	public function updateAvgTimeAll($subject_id, $tutor_id, $passedSeconds = 0){		
		$avgFromDB = false; //--переделать на проверку, если ест среднее время в БД для отчета, то брать там, иначе расчитывать.
		if($avgFromDB){
			$totalTime = $avgFromDB;
		} else {
			if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
			
			$countSubjectStudents = $this->_serviceWorkload->getSubjectStudentCount($subject_id);		
			$avgTimeWelcome = 0;
			$avgPassed = 0;
			if($countSubjectStudents[$subject_id] > 0){
				$timeWelcome = $this->_serviceWorkload->getTimeWelcomeMessage($tutor_id, $subject_id); 
				$avgTimeWelcome = $timeWelcome / $countSubjectStudents[$subject_id];
				$avgPassed = $passedSeconds / $countSubjectStudents[$subject_id];
			} 
			$subjectTime = (isset($this->avgTime[$tutor_id][$subject_id]) && $this->avgTime[$tutor_id][$subject_id] >= 0) ? $this->avgTime[$tutor_id][$subject_id] : (false);				
			$forumTime = (isset($this->avgForumTime[$tutor_id][$subject_id]) && $this->avgForumTime[$tutor_id][$subject_id] >= 0) ? $this->avgForumTime[$tutor_id][$subject_id] : (false);				
			
			$totalTime = $avgPassed + $avgTimeWelcome + $subjectTime + $forumTime;
		}
		if($totalTime){
			return $this->getDays($totalTime);			
		} elseif($totalTime === false){
			return _('Нет активных студентов');			
			//return 0;			
		}
		return 0;
	}
	
	public function getDays($seconds = 0){
		$days = floor($seconds/86400);
		return $days;
		/*
		$dStart = new DateTime(date('d.m.Y H:i:s', time() - $seconds));
		$dEnd  = new DateTime();		
		$dDiff = $dStart->diff($dEnd);		
		$format_time = 0;
		$d = $dDiff->format('%a'); 		
		//$h = $dDiff->format('%h'); 		
		
		if($d > 0){
			$format_time = $d.' ';			
			$last_number = $d % 10;		
			//if($last_number == 1)							{ $format_time .= _('день');	}
			//elseif($last_number >= 2 && $last_number <= 4)	{ $format_time .= _('дня'); 	}
			//else											{ $format_time .= _('дней');	}
		}		
		*/
		/*
		if($h > 0){
			if($format_time !== 0){ $format_time .= ', '; }
			$format_time .= $h.' ';			
			$last_number = $h % 10;		
			if($last_number == 1)							{ $format_time .= _('час');		}
			elseif($last_number >= 2 && $last_number <= 4)	{ $format_time .= _('часа'); 	}
			else											{ $format_time .= _('часов');	}
		}		
		*/
		//return $format_time;		
	}
	
	public function updateIntervals($subject_id){
		$parts = (isset($this->intervals[$subject_id])) ? $this->intervals[$subject_id] : (0);
		return $parts;
	}
	
	public function updateFaculty($tutor_id){						
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$faculty = $this->_serviceOrgstructure->getFaculty($tutor_id);
		if(!$faculty){
			return _('Нет');
		}
		return $faculty;
	}
	
	/**
	 * расчитываем нагрузку для сессий, по которым ведомость не передана.
	*/
	public function updateWorkload($subject_id, $tutor_id){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$H = $this->_serviceWorkload->getWorkload($tutor_id, $subject_id);
		if($H === false){
			return _('Нет');
		}
		return $H;
	}
	
	/**
	 * расчитываем нагрузку для сессий, по которым ведомость уже ПЕРЕДАНА
	*/
	public function updateEndWorkload($subject_id, $tutor_id){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$H = $this->_serviceWorkload->getEndWorkload($tutor_id, $subject_id);
		if($H === false){
			return _('Нет');
		}
		return $H;
	}
		
	public function updateAvgWelcome($subject_id, $tutor_id){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$countSubjectStudents = $this->_serviceWorkload->getSubjectStudentCount($subject_id);		
		$avgTimeWelcome = 0;		
		if($countSubjectStudents[$subject_id] > 0){
			$timeWelcome = $this->_serviceWorkload->getTimeWelcomeMessage($tutor_id, $subject_id); 
			$avgTimeWelcome = $timeWelcome / $countSubjectStudents[$subject_id];			
		} 
		if($avgTimeWelcome){
			return $this->getDays($avgTimeWelcome);			
		} elseif($avgTimeWelcome === false){
			return _('Нет активных студентов');			
		}
		return _('0 дней');
	}
	
	public function updateAvgSubject($subject_id, $tutor_id){
		$avgSubject = (isset($this->avgTime[$tutor_id][$subject_id]) && $this->avgTime[$tutor_id][$subject_id] >= 0) ? $this->avgTime[$tutor_id][$subject_id] : (false);				
		if($avgSubject){
			return $this->getDays($avgSubject);			
		} elseif($avgSubject === false){
			return _('Нет активных студентов');			
		}
		return _('0 дней');
	}
	
	public function updateAvgForum($subject_id, $tutor_id){
		$avgForum = (isset($this->avgForumTime[$tutor_id][$subject_id]) && $this->avgForumTime[$tutor_id][$subject_id] >= 0) ? $this->avgForumTime[$tutor_id][$subject_id] : (false);				
		if($avgForum){
			return $this->getDays($avgForum);			
		} elseif($avgForum === false){
			return _('Нет активных студентов');						
		}
		return _('0 дней');
	}
	
	
	/**
	 * Среднее время просрочки.
	*/
	public function updateAvgPassed($subject_id, $tutor_id){		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject');	}
		
		$secondsInDb = $this->_serviceWorkload->getViolations($tutor_id, HM_Workload_WorkloadModel::TYPE_SHEET_PASSED, $subject_id);
		if($secondsInDb){//--если в БД нет времени придоставления ведомости, то берем текущую дату - дата назначения на сессию или начала сессии.		
			$passedSeconds = $secondsInDb->current()->violation_time;			
		} else {
			$begin = 0;
			$subj = $this->_serviceSubject->getById($subject_id);//-- дата окончания сессии.
			if($subj){
				if(strtotime($subj->end) > 0){
					$begin = strtotime($subj->end);
				} elseif(strtotime($subj->end_planned) > 0){
					$begin = strtotime($subj->end_planned);
				} else {
					return _('Нет даты окончания сессии');
				}
			}			
			$passedSeconds = $this->_serviceWorkload->getViolationSeconds($begin, time());						
		}
		$countSubjectStudents = $this->_serviceWorkload->getSubjectStudentCount($subject_id);				
		$avgPassed = 0;
		if($countSubjectStudents[$subject_id] > 0){						
			$avgPassed = $passedSeconds / $countSubjectStudents[$subject_id];
		} 			
		if($avgPassed){
			return $this->getDays($avgPassed);			
		} elseif($avgPassed === false){
			return _('Нет активных студентов');			
		}
		return _('0 дней');
	}
	
	
	/**
	 * расчитывает просрочку на указанную дату.	 
	*/
	public function updateAvgTimeFixAll($subject_id, $tutor_id, $date_fix){
		if(!$this->_serviceWorkloadSheet){ $this->_serviceWorkloadSheet = $this->getService('WorkloadSheet');	}
		$avg = $this->_serviceWorkloadSheet->getT($tutor_id, $subject_id, $date_fix);
		if($avg){
			return $this->getDays($avg);			
		} elseif($avg === false){
			return _('Нет активных студентов');						
		}
		return _('0 дней');
	}
	
	/**
	 * расчитывает нагрузку на указанную дату.	 
	*/
	public function updateEndWorkloadFix($subject_id, $tutor_id, $date_end, $date_begin = false){
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		$H = $this->_serviceWorkload->getWorkloadFix($tutor_id, $subject_id, $date_end, $date_begin);
		if($H === false){
			return _('Нет');
		}
		return $H;		
	}

	
	/**
	 * определение названий групп по их id
	 * $groupIDs - string. разделитель запятая.
	 * return array();
	*/
	public function getGroupListName($groupIDs){		
		if(!in_array($groupIDs)){
			$groups = explode(',', $groupIDs);
			$groups = array_unique($groups);
			$groups = array_filter($groups);
			if(count($groups) < 1){
				return false;
			}
			foreach($groups as $i){
				if(isset($this->_groupList[$i])){
					$list[] = $this->_groupList[$i];
				}
			}
			return $list;
		}
	}
	
	
	public function setSubjectGroupList($subjectIDs){	
		if(!$subjectIDs){ return; }
		
		$subjectIDs = (array)$subjectIDs;
		
		if(!count($subjectIDs)){ return; }
		
		$select = $this->_serviceWorkload->getSelect();				
		$select->from(
			array('pe' => 'programm_events'),
			array(						
				'subject_id' 	=> 'pe.item_id',
				'group_name' 	=> 'sg.name',												
				'group_id' 		=> 'sg.group_id',												
			)
		);
		$select->join(
			array('sgp' => 'study_groups_programms'),
			'sgp.programm_id = pe.programm_id',
			array()
		);
		$select->join(
			array('sg' => 'study_groups'),
			'sg.group_id = sgp.group_id',
			array()
		);	
		$select->group(array('pe.item_id','sg.name', 'sg.group_id'));
		$select->where('pe.item_id IN (?)', $subjectIDs);				
		$res = $select->query()->fetchAll();
		$iterator = new ArrayIterator($res);
		$subjectGroupList = array();
		foreach($iterator as $ig){
			$subjectGroupList[$ig['subject_id']][$ig['group_id']] = $ig['group_name'];
		}
		$this->_subjectGroupList = $subjectGroupList;
	}
	
	
	public function createViolationLink($data, $type){		
		if(empty($data)) { return false; }
		if(!in_array($type, array('lesson', 'forum'))) { return false; }
		
		if(!$this->serviseUser){ $this->serviseUser = $this->getService('User'); }
		if($type == 'lesson'){			
			$url = $this->view->url(array('module' => 'interview', 'controller' => 'index', 'action' => 'index', 'lesson_id' => $data['lesson_id'], 'subject_id' => $data['subject_id'], 'user_id' => $data['student_id']));			
		} elseif($type == 'forum'){
			$url = $this->view->url(array('module' => 'forum', 'controller' => 'subject', 'action' => 'subject', $data['subject_id'] => $data['section_id'])).'#msg-'.$data['message_id'];
		}
		$user = $this->serviseUser->getById($data['author_id']);
		if($user->MID){
			$name = $user->LastName;
			$name = ($user->FirstName) ? ($name.' '.mb_substr($user->FirstName,0,1).'.') : ($name);
			$name = ($user->Patronymic) ? ($name.' '.mb_substr($user->Patronymic,0,1).'.') : ($name);			
		} else {
			$name = _('нет');
		}
		
		return array(
			'url'	=> $url,
			'name'	=> $name,
		);
	}
	
	/**
	 * разбивает период на осенний и весенний периоды
	 * return array('spring' => array('begin', 'end'), 'autumn' => array('begin', 'end'))
	 * params timestamp
	*/
	public function getPeriods($begin, $end){
		if(!$begin || !$end){ return false; }
		$bYear = date('Y',$begin); # год начала интервала
		$eYear = date('Y',$end);
		
		
		if($begin > $end){
			//echo 'Нагрузку не будет считать<br>';
			return false;
		}
		#список возможных годов
		$years = array();						
		for ($i = $bYear-1; $i <= $eYear; $i++) {
			$years[] = $i;
		}
		
		# промежуток, который не попадает ни в осенний, ни в весенний период.
		if(strtotime($bYear.'-07-30') < $begin && $begin < strtotime($bYear.'-09-01')){
			$begin = strtotime($bYear.'-07-30');
		}
		if(strtotime($eYear.'-07-30') < $end && $end < strtotime($eYear.'-09-01')){
			$end = strtotime($eYear.'-07-30');
		}
		
		
		$periods = array(); # все возможные периоды, среди которых будет происходить сопоставление дат
		foreach($years as $y){
			$periods['autumn'][$y] = array(
				'begin' => strtotime($y.'-09-01'),
				'end' 	=> strtotime(($y+1).'-01-31'),
			);
			$periods['spring'][$y] = array(
				'begin' => strtotime($y.'-02-01'),
				'end' 	=> strtotime($y.'-07-30'),
			);
		}
		
		$beginAutumnPeriod = false;
		$endAutumnPeriod = false;
				
		$beginType = false;
		$endType = false;		
		$beginYear = false;
		$endYear = false;
		
		$result = array();
		
		foreach($periods['autumn'] as $year => $i){
			if( $i['begin'] <= $begin && $begin <= $i['end'] ){
				$beginAutumnPeriod = $i;
				$beginType = 'autumn';
				$beginYear = $year;
			}							
			if( $i['begin'] <= $end && $end <= $i['end'] ){
				$endAutumnPeriod = $i;	
				$endType = 'autumn';
				$endYear = $year;								
			}
		}
		
		$beginSpringPeriod = false;
		$endSpringPeriod = false;
		$endPeriodCorrect = false;
		
		foreach($periods['spring'] as $year => $i){
			if( $i['begin'] <= $begin && $begin <= $i['end'] ){
				$beginSpringPeriod = $i;
				$beginType = 'spring';
				$beginYear = $year;								
			}							
			if( $i['begin'] <= $end && $end <= $i['end'] ){
				$endSpringPeriod = $i;
				$endType = 'spring';
				$endYear = $year;									
			}
		}
		
		if($beginType == $endType && $beginYear != $endYear){
			if($beginType == 'spring'){
				$endPeriodCorrect = $periods['autumn'][$beginYear];
			} else {
				$endPeriodCorrect = $periods['spring'][$beginYear+1];
			}							
		} elseif($beginType == $endType && $beginYear == $endYear) { # один и тот же период.
			//echo 'Период совпадает';
			
			$result[$beginType] = array(
				'begin' => $begin,
				'end' 	=> $end,
			);			
			return $result;
		}
		
		# если период того же типа периода, но другого года, то берем этого года, но другой тип периода.
		# например, если начало - осенний период 2015 года, а окончание - осенний, но 2016 года, то пропустили весенний период 2015 года. Берем его.
		
		
		if($beginAutumnPeriod)	{ 
			//echo 'Период осенний начало';
			$result[$beginType] = array(
				'begin' => $begin,
				'end' 	=> $beginAutumnPeriod['end'],
			);
		} else {
			//echo 'Период весенний начало';			
			$result[$beginType] = array(
				'begin' => $begin,
				'end' 	=> $beginSpringPeriod['end'],
			);
		}	
		
		if($endPeriodCorrect){
			//echo 'Корректировка даты конца';
			$indexName = (isset($result['spring'])) ? ('autumn') : ('spring');
			
			$result[$indexName] = array(
				'begin' => $endPeriodCorrect['begin'],
				'end' 	=> $endPeriodCorrect['end'],
			);			
		}  else {
			if($endAutumnPeriod)	{ 
				//echo 'Период осенний конец';
				$result[$endType] = array(
					'begin' => $endAutumnPeriod['begin'],
					'end' 	=> $end,
				);
			} else {
				//echo 'Период весенний конец';
				$result[$endType] = array(
					'begin' => $endSpringPeriod['begin'],
					'end' 	=> $end,
				);			
			}
		}
				
		return $result;
					
						
	}
	
	/**
	 * кол-во непроверенных работ
	 * непроверенная работа - если последнее сообщение от студента.
	 * param array of lesson id
	 * return int
	*/
	public function getNewWorks($session_id, $lessonsIDs, $date_begin, $date_end) {
		$lessonsIDs = (array)$lessonsIDs;
		$newWorks = 0;		
		$studentIDs = array(); # только активные студенты
		
		if(!count($lessonsIDs)) { return $newWorks; }
		if(!$session_id)	 	{ return $newWorks; }
		
		if(!$this->_serviceWorkload){ $this->_serviceWorkload = $this->getService('Workload');	}
		
		$selectStudents = $this->_serviceWorkload->getSelect();
		$selectStudents->from('Students', array('MID') );
		$selectStudents->where($this->_serviceWorkload->quoteInto('CID = ?', $session_id));	
		$res = $selectStudents->query()->fetchAll();
		if(count($res)){
			foreach($res as $i){
				$studentIDs[$i['MID']] = $i['MID'];
			}
		}
		unset($res);
		unset($selectStudents);
		
		if(!count($studentIDs)){ return $newWorks; }
		
		$subMessage = $this->_serviceWorkload->getSelect();
		$subMessage->from(array('m' => 'interview'),
			array(				
				'interview_hash'	=> 'm.interview_hash',									
				'max_interview_id'  => new Zend_Db_Expr('MAX(m.interview_id)'),
			)
		);
		$subMessage->group(array('m.interview_hash'));
		$subMessage->where($this->_serviceWorkload->quoteInto(array('m.date >= ?', ' AND m.date <= ?'), array($date_begin, $date_end)));	
		$subMessage->where($this->_serviceWorkload->quoteInto('m.lesson_id IN (?)', $lessonsIDs));	
		
		$selectMessage = $this->_serviceWorkload->getSelect();
		$selectMessage->from(array('m' => 'interview'),
			array(													
				'lesson_id' => 'm.lesson_id',
				'user_id' => 'm.user_id',
				'to_whom' => 'm.to_whom',
				'type' => 'm.type',
			)
		);
		$selectMessage->join(
			array('sub_m' => $subMessage),								
			'sub_m.interview_hash = m.interview_hash AND sub_m.max_interview_id = m.interview_id',
			array()
		);
		$selectMessage->where($this->_serviceWorkload->quoteInto(array('m.date >= ?', ' AND m.date <= ?'), array($date_begin, $date_end)));	
		$selectMessage->where($this->_serviceWorkload->quoteInto('m.lesson_id IN (?)', $lessonsIDs));
		$selectMessage->where($this->_serviceWorkload->quoteInto('m.type IN (?)', array(HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION, HM_Interview_InterviewModel::MESSAGE_TYPE_TEST)));
		
		$selectMessage->where($this->_serviceWorkload->quoteInto('m.user_id IN (?)', $studentIDs));
		
		$lastMessages = $selectMessage->query()->fetchAll();		
		if($lastMessages){
			foreach($lastMessages as $lm){
				if(in_array($lm['type'], array(HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION, HM_Interview_InterviewModel::MESSAGE_TYPE_TEST)))
				$newWorks++;
			}
		}
		unset($selectMessage);
		unset($lastMessages);
		unset($subMessage);
		
		return $newWorks;				
	}
	
}