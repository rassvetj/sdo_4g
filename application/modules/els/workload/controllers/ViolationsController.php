<?php

class Workload_ViolationsController extends HM_Controller_Action
{		
	
	private $_tutorFacultyList 		= array(); //-- [tutor_id] => [faculty name]
	private $_groupList 			= array(); //-- все группы.
	private $_tutorDepartmentList 	= array();
	private $_subjectGroupList 		= array();
	
	#private $_periodAutumn = array('begin' => '01.09', 'end' => '31.01');	#осенний
	#private $_periodSpring = array('begin' => '01.02', 'end' => '30.06');	#весенний
	
	
	/**
	 * Отчет "Просрочка" Общий. Доступен  для тьютора, наблюдателя и орг обучения
	*/		
	public function getViolationsReportAction(){
		
		ini_set('memory_limit', '2548M'); 
		//--добавить проверку на доступность сессии и тьютора в параметрах запроса
		$config = Zend_Registry::get('config');
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$this->getHelper('viewRenderer')->setNoRender();		
		
		try { 
			$this->_serviceUser 			= $this->getService('User');
			$this->_serviceWorkload 		= $this->getService('Workload');
			$this->_serviceWorkloadSheet 	= $this->getService('WorkloadSheet');
			$this->_serviceOrgstructure 	= $this->getService('Orgstructure');
			
			$userRole 		= $this->_serviceUser->getCurrentUserRole();
			$currentUserId 	= $this->_serviceUser->getCurrentUserId();
			$isTutor 		= (in_array($userRole, array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) ? (true) : (false);
			$isSupervisor 	= (in_array($userRole, array(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))) ? (true) : (false); 
			
			if($isTutor)			{ $template = '_tutor.tpl';		 }
			elseif($isSupervisor)	{ $template = '_supervisor.tpl'; }
			else 					{ $template = '_dean.tpl'; 		 }
			
			$user_id 			= $this->_request->getParam('user_id', false);					
			$subject_id 		= $this->_request->getParam('subject_id', false);
			$report_type_id 	= $this->_request->getParam('report_type_id', HM_Workload_WorkloadModel::REPORT_TYPE_CURRENT);
			$isEnd 				= $this->_request->getParam('isEnd', false); //--для окончательного отчета. Значит, что берем только сессии, по которым ведомость передана
			$date_begin 		= $this->_request->getParam('date_begin', false);
			$date_end 			= $this->_request->getParam('date_end', false);
			$type_do 			= $this->_request->getParam('type_do', false);
						
			$year = date('Y',time());
			$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.
			
			$dateEndIsEmpty = (empty($date_end)) ? (true) : (false); //-- флаг. Значит, что дата окончания явно не указана.
			
			$validator 	= new Zend_Validate_Date(array('format' => 'dd.MM.yyyy',));
			$validator2 = new Zend_Validate_Date(array('format' => 'yyyy-MM-dd',));
			
			if( $validator->isValid($date_begin) || $validator2->isValid($date_begin) )	{ $date_begin = date('Y-m-d',strtotime($date_begin));	}
			else 																		{ $date_begin = $year.'-09-01';							} //--дата отсчета с 01 сентября каждого года.
						
			if( $validator->isValid($date_end) || $validator2->isValid($date_end) )	{ $date_end = date('Y-m-d',strtotime($date_end));	}
			else 																	{ $date_end = date('Y-m-d', time());				}
			
			
			if ($isTutor && $currentUserId != $user_id) {				
				$user_id = $currentUserId;
				$subject_id = false;				
			}
			
			if($user_id) 						{ $urlParams['user_id'] 		= $user_id; 		}
			if($subject_id) 					{ $urlParams['subject_id'] 		= $subject_id; 		}
			if($date_begin) 					{ $urlParams['date_begin'] 		= $date_begin; 		}
			if($date_end && !$dateEndIsEmpty) 	{ $urlParams['date_end'] 		= $date_end; 		}
			if($isEnd) 							{ $urlParams['isEnd'] 			= $isEnd; 			}
			if($report_type_id) 				{ $urlParams['report_type_id'] 	= $report_type_id;	}
			if($type_do) 						{ $urlParams['type_do'] 		= $type_do;			}
			$this->view->urlParams = $urlParams;
			$this->view->caption = _('Нарушение сроков реагирования для ФДО');			
			
			if($user_id > 0){ //--выбран тьютор	
				$tutorIDs = array($user_id);
			} else { 				
				$tutors = $this->_serviceWorkload->getListOrgstructurePersons($currentUserId, $isEnd);	
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
			
									
			$select 		= $this->_serviceWorkload->getSelect();
			$ball_select 	= $this->_serviceWorkload->getSelect();
			if($isEnd)	{	$select->where('subj.isSheetPassed IS NOT NULL');	}
			else		{ 	$select->where('subj.isSheetPassed IS NULL');		}
			
			if($user_id > 0 && $subjectIDs){ //--выбран тьютор	//--выбрана сессия																							
				$select->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subjectIDs));
				$ball_select->where($this->_serviceWorkload->quoteInto('l.CID IN (?)', $subjectIDs));				 	
			}
			
			
			if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED){ # отчет по продленным сессиям
				$select->where('subj.time_ended_debt IS NOT NULL');
				$select->where($this->_serviceWorkload->quoteInto(
					array('subj.begin <= ? ', ' AND subj.time_ended_debt >= ?'),
					array($date_end, $date_begin)
				));				
			} else {
				$select->where('subj.time_ended_debt IS NULL');
				$select->where($this->_serviceWorkload->quoteInto(					
					array('subj.begin <= ? ', ' AND subj.end >= ?'),
					array($date_end, $date_begin)
				));	
			}
			
			$select->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));
			$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			
			#$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
			if($type_do != HM_Report_ReportModel::DO_ALL){
				$select->where($this->_serviceWorkload->quoteInto('subj.isDO = ?', $type_do));
			}
			
			
			
			# получаем список сессий - уроков по указанным тьюторам.############################
			$selectLessons = $this->_serviceWorkload->getSelect();
			$selectLessons->from(array('l' => 'schedule'),
				array(				
					'lasson_id'		=> 'l.SHEID',
					'session_id'	=> 'l.CID',
				)
			);
			$selectLessons->join(
				array('subj' => 'subjects'),
				'subj.subid = l.CID',
				array()
			);		
			$selectLessons->join(
				array('t' => 'Tutors'),
				'subj.subid = t.CID',
				array()
			);
			$selectLessons->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); 
			$selectLessons->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			$selectLessons->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));

			if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED){ # отчет по продленным сессиям
				$selectLessons->where('subj.time_ended_debt IS NOT NULL');
				$selectLessons->where($this->_serviceWorkload->quoteInto(
					array('subj.begin <= ? ', ' AND subj.time_ended_debt >= ?'),
					array($date_end, $date_begin)
				));				
			} else {
				$selectLessons->where('subj.time_ended_debt IS NULL');
				$selectLessons->where($this->_serviceWorkload->quoteInto(					
					array('subj.begin <= ? ', ' AND subj.end >= ?'),
					array($date_end, $date_begin)
				));	
			}

			
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
					if(!isset($notBalls[$b['subid']]))	{ $notBalls[$b['subid']] = $b['notBall'];	}
					else								{ $notBalls[$b['subid']] += $b['notBall'];	}//--суммируем непроверенных студентов из всех уроков этой сессии.					
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


			$fields = array(
				'tutor_id' 			=> 'p.MID',					
				'name' 				=> 'subj.name',																								
				'subid' 			=> 'subj.subid',															
				'end' 				=> 'subj.end',											
				'date_assign'		=> 't.date_assign',	
				'time_ended_debt' 	=> 'subj.time_ended_debt',				
			);
			
			if ($isTutor) {	
			} elseif ($isSupervisor) { 
				$fields['name_plan'] 	= 'ls.name_plan';
				$fields['fio'] 			= new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)");
			} else { 					
				$fields['fio'] = new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)");
			}
			$select->from(array('p' => 'People'), $fields);
			
			
			$select->join(array('t' 	=> 'Tutors'), 'p.MID = t.MID', array());		
			$select->join(array('subj' 	=> 'subjects'), 't.CID = subj.subid', array());
			
			$groupFields = array('subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end', 't.date_assign', 'subj.time_ended_debt');
			
			if ($isTutor) {	
				$select->joinLeft(	array('s' => $subUSelect), 's.CID = subj.subid', array('students' => 'COUNT(DISTINCT s.MID)') );								
			} else {				
				$select->join(		array('s' => $subUSelect), 's.CID = subj.subid', array() );				
				$groupFields[] = 'p.LastName';
				$groupFields[] = 'p.FirstName';
				$groupFields[] = 'p.Patronymic';
				
				if ($isSupervisor) {				
					$select->joinLeft(array('ls' => 'learning_subjects'), 'ls.id_external = subj.learning_subject_id_external', array() );	
					$groupFields[] = 'ls.name_plan';					
				} 				
			}
			$select->group($groupFields);

			$res_0 = $select->query()->fetchAll();
			unset($select);
			unset($subUSelect);
			unset($subUsers);
			unset($subGrad);
			
			$res = new ArrayIterator($res_0);
			
			if($isTutor || $isSupervisor){
				$selectedSubjectIDs = array(); //--id отобранных сессий.
				foreach($res as $r){ $selectedSubjectIDs[$r['subid']] = $r['subid']; }				
				$this->setSubjectGroupList($selectedSubjectIDs); //-_получаем массив сессия - группы этой сессии.
			}
			
			$content = array();
			$data 	 = array();
			if($res){
				foreach($res as $i){
					$date_end_mod 	= $date_end;
					$date_begin_mod = $date_begin;
					
					if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED){ # для продленных сессий берем только данные после даты окончания сессии. Данные после даты продления также учитываем.
						$date_begin = $i['end'];
					} else {
						//--ограничение на период окончания забора данных для расчета нагрузки и просрочки.
						/*
						if($dateEndIsEmpty && $i['end']){							
							if(strtotime($i['end']) > 0 && strtotime($i['end']) < strtotime($date_end)){																
								$date_end_mod = date('Y-m-d', strtotime($i['end']));		//--исключаем данные, которые созданы после завершения сессии 			
							}
						}
						*/						
					}
					
					//--если дата назначения позже даты начала периода, то берем дату назначения на сессию как начала периода.					
					if(strtotime($i['date_assign']) > strtotime($date_begin)){						
						$date_begin_mod = date('Y-m-d', strtotime($i['date_assign']));						
					}
					
					if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED && strtotime($i['end']) > strtotime($date_begin_mod)){ # Для продленных сессий берем только данные с даты окончания сессии
						$date_begin_mod = $i['end']; 						
					}

					$tutor_id 	= ($isTutor) ? ($currentUserId) : ($i['tutor_id']);
					$subject_id = $i['subid'];					
					
					if($isTutor || $isSupervisor){
						$subjectGroups = $this->_subjectGroupList[$subject_id];						
						$subjectGroups = $this->getService('Subject')->filterGroupsByAssignStudents($subject_id, $tutor_id, $subjectGroups);
						$groups = '';
						if($subjectGroups && count($subjectGroups)){
							$groups = implode($subjectGroups, ',');
						}
					}
					
						
					if($isTutor || $isSupervisor){
						$newWorks = 0; # кол-во записей, где студент задал вопрос, но на него не ответил препод.				
						if(isset($sLessons[$subject_id]) && count($sLessons[$subject_id])){													
							$newWorks = $this->getNewWorks($subject_id, $sLessons[$subject_id], $date_begin_mod, $date_end_mod);
						} else {
							# нет уроков в сессии => нет сообщиней. Пропускаем?
						}
					
						if($isTutor ){
							$T_message = $this->_serviceWorkloadSheet->getAvgT_Message($tutor_id, $subject_id, $date_end_mod, $date_begin_mod); //--просрочка по письму - берем дату из таблицы просрочек, 					
							$vi_MessageCount = ($T_message > 0) ? (1) : (0);
							
							$T_subject 			= $this->_serviceWorkloadSheet->getAvgT_Subject($tutor_id, $subject_id, $date_end_mod, $date_begin_mod, true); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
							$T_subject_links 	= $T_subject['links'];
							$T_subject_avg 		= $T_subject['avg'];
							$urls = array();
							if(count($T_subject_links)){
								foreach($T_subject_links as $link){ $urls[] = $this->createViolationLink($link, 'lesson'); }
							}
							
							
							$T_subject_ActiveStudents = $T_subject['activeStudents'];
							$vi_SubjectCount = ($T_subject['count_violations'] > 0) ? ($T_subject['count_violations']) : (0);
							
							$T_forum 		= $this->_serviceWorkloadSheet->getAvgT_Forum($tutor_id, $subject_id, $date_end_mod, $date_begin_mod, true); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
							$T_forum_links 	= $T_forum['links'];						
							if(count($T_forum_links)){
								foreach($T_forum_links as $link){ $urls[] = $this->createViolationLink($link, 'forum'); }
							}	
								
							$T_forum_avg 			= $T_forum['avg'];
							$T_forum_ActiveStudents = $T_forum['activeStudents'];
							$vi_ForumCount 			= ($T_forum['count_violations'] > 0) ? ($T_forum['count_violations']) : (0);					
							$T_vedomost 			= $this->_serviceWorkloadSheet->getAvgT_Vedomost($tutor_id, $subject_id, $date_end_mod, $date_begin_mod); //--просрочка по предоставлению ведомости
												
							$vi_VedomostCount = ($T_vedomost > 0) ? (1) : (0);
							
							$count_violations = $vi_MessageCount + $vi_SubjectCount + $vi_ForumCount + $vi_VedomostCount;
							
							$content[] = array(
								'subject_id'		=> $subject_id,
								'name' 				=> $i['name'],												
								'groups' 			=> $groups,									
								'students' 			=> $i['students'],

								'notBall' 			=> $notBalls[$subject_id],							
								
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
							
						} elseif($isSupervisor){
							
							$count_violations = 0;
							
							$T_message = $this->_serviceWorkloadSheet->getAvgT_Message($tutor_id, $subject_id, $date_end_mod, $date_begin_mod); //--просрочка по письму - берем дату из таблицы просрочек, 					
							if($T_message > 0) { $count_violations	++; }
							
							$T_subject = $this->_serviceWorkloadSheet->getAvgT_Subject($tutor_id, $subject_id, $date_end_mod, $date_begin_mod, true); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
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
							
							$T_forum = 	 $this->_serviceWorkloadSheet->getAvgT_Forum($tutor_id, $subject_id, $date_end_mod, $date_begin_mod, true); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
							$T_forum_links = $T_forum['links'];
							if(count($T_forum_links)){
								foreach($T_forum_links as $link){
									$urls[] = $this->createViolationLink($link, 'forum');								
								}
							}
							
							
							$T_forum_avg = $T_forum['avg'];
							$T_forum_ActiveStudents = $T_forum['activeStudents'];
							$count_violations = $count_violations + $T_forum['count_violations'];
							
							$T_vedomost =$this->_serviceWorkloadSheet->getAvgT_Vedomost($tutor_id, $subject_id, $date_end_mod, $date_begin_mod); //--просрочка по предоставлению ведомости
							if($T_vedomost > 0) { $count_violations++; }
							
							$avg = ( ($T_message + $T_subject_avg + $T_forum_avg + $T_vedomost) / 4 );
						
						
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
								'subject_id'	=> $subject_id,							
								'fio' 				=> $i['fio'],
								'department' 		=> $departmentName,
								'faculty' 			=> $facultyName,
								'name' 				=> $i['name'],
								'name_plan'			=> $i['name_plan'],
								'groups' 			=> $groups,
								'count_violations' 	=> $count_violations,
								'avg' 				=> $this->getDays($avg),																													
								'notBall' 			=> $notBalls[$subject_id],											
								'urls' 				=> $urls,	
								'newWorks'			=> $newWorks,							
							);							
						}

					} else {
						if(!isset($data[$tutor_id]['fio'])){
							$data[$tutor_id]['fio'] = $i['fio'];	
						}
						
						
						############################ кол-во записей, где студент задал вопрос, но на него не ответил препод.				
						
						if(!isset($data[$tutor_id]['newWorks'])){ $data[$tutor_id]['newWorks'] = 0; }
						
						if(isset($sLessons[$subject_id]) && count($sLessons[$subject_id])){							
							$data[$tutor_id]['newWorks'] = $data[$tutor_id]['newWorks'] + $this->getNewWorks($subject_id, $sLessons[$subject_id], $date_begin_mod, $date_end_mod);
						} else {
							# нет уроков в сессии => нет сообщиней. Пропускаем?
						}
						############################
						
						
						$T_message = $this->_serviceWorkloadSheet->getAvgT_Message($tutor_id, $subject_id, $date_end_mod, $date_begin_mod); //--просрочка по письму - берем дату из таблицы просрочек, 					
						if($T_message > 0){ $data[$tutor_id]['count_violations']++; } //--кол-во нарушений.
						
					
					
						$T_subject = $this->_serviceWorkloadSheet->getAvgT_Subject($tutor_id, $subject_id, $date_end_mod, $date_begin_mod, true); //--просрочка по проверке заданий - берем дату из таблицы просрочек, 
						$T_subject_links = $T_subject['links'];
						if(count($T_subject_links)){
							foreach($T_subject_links as $link){
								$data[$tutor_id]['urls'][] = $this->createViolationLink($link, 'lesson');								
							}
						}
					
						
						$T_subject_avg = $T_subject['avg'];						
						$data[$tutor_id]['count_violations'] = $data[$tutor_id]['count_violations'] + $T_subject['count_violations'];
						
						$T_forum = 	 $this->_serviceWorkloadSheet->getAvgT_Forum($tutor_id, $subject_id, $date_end_mod, $date_begin_mod, true); //--просрочка по ответу на форуме - берем дату из таблицы просрочек, 
						$T_forum_links = $T_forum['links'];
						if(count($T_forum_links)){
							foreach($T_forum_links as $link){
								$data[$tutor_id]['urls'][] = $this->createViolationLink($link, 'forum');								
							}
						}
						
						$T_forum_avg = $T_forum['avg'];						
						$data[$tutor_id]['count_violations'] = $data[$tutor_id]['count_violations'] + $T_forum['count_violations'];
						
						$T_vedomost =$this->_serviceWorkloadSheet->getAvgT_Vedomost($tutor_id, $subject_id, $date_end_mod, $date_begin_mod); //--просрочка по предоставлению ведомости
						if($T_vedomost > 0) { $data[$tutor_id]['count_violations']++; }
						
						$data[$tutor_id]['total'] = $data[$tutor_id]['total'] + ( ($T_message + $T_subject_avg + $T_forum_avg + $T_vedomost) / 4 );						
						$data[$tutor_id]['countSubjects']++;						
						
						$data[$tutor_id]['notBall'] += $notBalls[$subject_id];
						
					}					
				}
				
				if(!$isTutor && !$isSupervisor){
					foreach($data as $tutor_id => $i){														
						$avg = ($i['countSubjects'] > 0) ? ($i['total'] /  $i['countSubjects']) : (0);
						
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
				}
				
				
				
				###### Экспорт в Excel
				$isExportExcel = $this->_request->getParam('export', false);
				if($isExportExcel == 'excel'){
					$period = false;
					$dBegin = $this->_request->getParam('date_begin', false);
					$dEnd 	= $this->_request->getParam('date_end', false);
					if($dBegin) { $period = 'c '.$dBegin; 		}
					if($dEnd) 	{ $period .= ' по '.$dEnd; 		}												
					$this->view->period = $period;
					$this->view->content = $content;
					$tt = $this->view->render('report/parts/excel/violations/'.$template);						
					$this->getExportExcel($tt, 'violations');						
					exit();						
				}			
				$this->view->getParams = $this->_request->getParams();
				$this->view->content = $content;
			}
			
			$content = $this->view->render('report/parts/_violations_report'.$template);
			echo $content;
			
		} catch (Exception $e) {
			#echo $e->getMessage();			
			echo _('Произошла ошибка');
		}
	}
	/*
	 * по нагрузке Workload_WorkloadController такая же ф-ция. вынести в сервис subject 	
	**/
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
	
	
	public function getDays($seconds = 0){
		$days = floor($seconds/86400);
		return $days;			
	}
	
	
	/**
	 * формирует ссылку на просрочку
	**/
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
	
	
	public function updateFaculty($tutor_id){						
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$faculty = $this->_serviceOrgstructure->getFaculty($tutor_id);
		if(!$faculty){
			return _('Нет');
		}
		return $faculty;
	}
	
	/**
	 * по просрочке такая же ф-ция. вынести к сервис subject 
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
	
	
}