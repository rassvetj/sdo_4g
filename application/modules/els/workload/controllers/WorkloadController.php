<?php

class Workload_WorkloadController extends HM_Controller_Action
{	
	private $_groupList 			= array(); //-- все группы.
	private $_tutorDepartmentList 	= array();
	private $_subjectGroupList 		= array();
	
	/**
	 * Отчет "О нагрузке" Доступен тьютору и орг. оюучения
	*/	
	public function getWorkloadReportAction(){	
		
		//--добавить проверку на доступность сессии и тьютора в параметрах запроса
		$config = Zend_Registry::get('config');
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');		
		$this->getHelper('viewRenderer')->setNoRender();		
		
		try { 
			$this->_serviceUser 		= $this->getService('User');			
			$this->_serviceWorkload 	= $this->getService('Workload');
			$this->_serviceWorkloadSheet= $this->getService('WorkloadSheet');
			$this->_serviceOrgstructure = $this->getService('Orgstructure');
			
			$userRole 		= $this->_serviceUser->getCurrentUserRole();
			$currentUserId 	= $this->_serviceUser->getCurrentUserId();
			$isTutor = (in_array($userRole, array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) ? (true) : (false);
			
			$user_id 		= $this->_request->getParam('user_id', false);		
			$subject_id 	= $this->_request->getParam('subject_id', false);			
			$report_type_id = $this->_request->getParam('report_type_id', HM_Workload_WorkloadModel::REPORT_TYPE_CURRENT);			
			
			$ignorePeriods  = $this->_request->getParam('ignorePeriods', false);
			
			$year = date('Y',time());
			$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.
			
			$date_begin = $this->_request->getParam('date_begin', false); 
			$date_end 	= $this->_request->getParam('date_end', false);
			$isEnd 		= $this->_request->getParam('isEnd', false); //--для окончательного отчета. Значит, что берем только сессии, по которым ведомость передана
			
			$dateEndIsEmpty = (empty($date_end)) ? (true) : (false); //-- флаг. Значит, что дата окончания явно не указана.
			
			$validator 	= new Zend_Validate_Date(array('format' => 'dd.MM.yyyy',));
			$validator2 = new Zend_Validate_Date(array('format' => 'yyyy-MM-dd',));
			if( $validator->isValid($date_begin) || $validator2->isValid($date_begin) )	{ $date_begin = date('Y-m-d',strtotime($date_begin));	}
			else 																		{ $date_begin = $year.'-09-01';							} //--дата отсчета с 01 сентября каждого года.
						
			if( $validator->isValid($date_end) || $validator2->isValid($date_end) )	{ $date_end = date('Y-m-d',strtotime($date_end));	}
			else 																	{ $date_end = date('Y-m-d', time());				}
			
			
			if ($isTutor) {
				if($currentUserId != $user_id || $isEnd){
					$this->_flashMessenger->addMessage(array(
						'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
						'message' => _('У вас нет доступа к этому разделу')
					));
					$this->_redirect('/');
				}				
			}
			
			if($user_id) 						{ $urlParams['user_id'] 		= $user_id; 		}
			if($subject_id) 					{ $urlParams['subject_id'] 		= $subject_id;		}
			if($date_begin) 					{ $urlParams['date_begin'] 		= $date_begin;		}
			if($date_end && !$dateEndIsEmpty) 	{ $urlParams['date_end'] 		= $date_end; 		}
			if($isEnd) 							{ $urlParams['isEnd'] 			= $isEnd; 			}
			if($report_type_id) 				{ $urlParams['report_type_id'] 	= $report_type_id;	}
			if($ignorePeriods) 					{ $urlParams['ignorePeriods']	= $ignorePeriods;	}
			$this->view->urlParams = $urlParams;
			$this->view->caption = _('Выполнение педагогической нагрузки для ФДО');	
			
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
			
			$select = $this->_serviceWorkload->getSelect();
			
			
			if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED){ # отчет по продленным сессиям
				$select->where('subj.time_ended_debt IS NOT NULL OR subj.time_ended_debt_2 IS NOT NULL');
				$select->where($this->_serviceWorkload->quoteInto(
					array('subj.begin <= ? ', ' AND (subj.time_ended_debt >= ?', ' OR subj.time_ended_debt_2 >= ?)'),
					array($date_end, $date_begin, $date_begin)
				));				
			} elseif($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL) { # продленные + текущие				
				$select->where($this->_serviceWorkload->quoteInto(
					#array('(subj.time_ended_debt IS NOT NULL AND subj.begin <= ? ', ' AND subj.time_ended_debt >= ?) ', ' OR (subj.time_ended_debt IS NULL AND subj.begin <= ? ', ' AND subj.end >= ?)'),
					#array($date_end, $date_begin, $date_end, $date_begin)
					array('(   (subj.time_ended_debt IS NOT NULL OR subj.time_ended_debt_2 IS NOT NULL) AND subj.begin <= ? ', ' AND (subj.time_ended_debt >= ? ', ' OR subj.time_ended_debt_2 >= ? ))', ' OR (subj.time_ended_debt IS NULL AND subj.time_ended_debt_2 IS NULL AND subj.begin <= ? ', ' AND subj.end >= ?)'),
					array($date_end, $date_begin, $date_begin, $date_end, $date_begin)
				));				
			} else {
				$select->where('subj.time_ended_debt IS NULL AND subj.time_ended_debt_2 IS NULL');
				$select->where($this->_serviceWorkload->quoteInto(					
					array('subj.begin <= ? ', ' AND subj.end >= ?'),
					array($date_end, $date_begin)
				));	
			}
			
			
			
			if($isEnd)	{	$select->where('subj.isSheetPassed IS NOT NULL');	}
			else 		{	$select->where('subj.isSheetPassed IS NULL');		}
			
			if($user_id > 0){ //--выбран тьютор											
				if($subjectIDs){ //--выбрана сессия										
					$select->where($this->_serviceWorkload->quoteInto('subj.subid IN (?)', $subjectIDs));
				} 	
			}
			
			$select->where($this->_serviceWorkload->quoteInto('t.MID IN (?)', $tutorIDs));
			$select->where('subj.base = ?',HM_Subject_SubjectModel::BASETYPE_SESSION);
			$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO); //--ФДО или ФДО_Б
			
			# группировка студентов и прошедшх обучение.
			$subUsers = $this->_serviceWorkload->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
			
			$subGrad = $this->_serviceWorkload->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
			
			$subUSelect = $this->_serviceWorkload->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
			
			
			$fields = array(
				'tutor_id' 			=> 'p.MID',					
				'name' 				=> 'subj.name',					
				'faculty' 			=> 'p.MID',															
				'subid' 			=> 'subj.subid',
				'begin' 			=> 'subj.begin',
				'end' 				=> 'subj.end',
				'time_ended_debt' 	=> new Zend_Db_Expr("CASE WHEN (subj.time_ended_debt_2 IS  NULL OR subj.time_ended_debt_2 = '') THEN subj.time_ended_debt ELSE subj.time_ended_debt_2 END "),
			);
			
			if ($isTutor) {				
				$select->group(array('subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end', 'subj.time_ended_debt', 'subj.time_ended_debt_2'));
			} else {
				$fields['fio'] = new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)");
				$select->group(array('p.LastName', 'p.FirstName', 'p.Patronymic', 'subj.begin', 'subj.name', 'p.MID','subj.subid', 'subj.end', 'subj.time_ended_debt', 'subj.time_ended_debt_2'));
			}
			
			$select->from(array('p' => 'People'), $fields);
			$select->join(array('t' => 'Tutors'), 'p.MID = t.MID', array());		
			$select->join(array('subj' => 'subjects'), 't.CID = subj.subid', array());	
			$select->joinLeft(
				array('s' => $subUSelect),
				's.CID = subj.subid',				
				array('students' => 'COUNT(DISTINCT s.MID)')
			);
				
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
				foreach($res as $i){
                    $date_begin_mod = $date_begin;
					$date_end_mod = $date_end;
					
					
					if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED){ # для продленных сессий берем только данные после даты окончания сессии. Данные после даты продления также учитываем.
						if(strtotime($date_begin_mod) < strtotime($i['end'])){
							$date_begin_mod = $i['end'];
						}
					} elseif($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL){ # вне зависимости от продления, берем с даты начала сессии
						
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
					
					$tutor_id 	= ($isTutor) ? ($currentUserId) : ($i['tutor_id']);
					$subject_id = $i['subid'];
					
					$subjectGroups = $this->_subjectGroupList[$subject_id];						
					$subjectGroups = $this->getService('Subject')->filterGroupsByAssignStudents($subject_id, $tutor_id, $subjectGroups);
					
					$groups = '';
					if($subjectGroups && count($subjectGroups)){
						$groups = implode($subjectGroups, ',');
					}
					
					
					$availableStudents 	= $this->getService('Subject')->getAvailableStudents($tutor_id, $subject_id);
					$students 			= ($availableStudents !== false) ? (count($availableStudents)) : ($i['students']); 
					
					if(!$isTutor){
						if(isset($this->_tutorDepartmentList[$tutor_id])){
							$departmentName = $this->_tutorDepartmentList[$tutor_id];
						} else {
							$departmentName = $this->_serviceOrgstructure->getDepartmentName($tutor_id);								
							$this->_tutorDepartmentList[$tutor_id] = $departmentName;
						}						
					}
					
					if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED){ # отчет по продленным сессиям
						$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($tutor_id, $subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-приветственное письмо		
						$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($tutor_id, $subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-проверка заданий		
						$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($tutor_id, $subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-форум
						$_F4 = $this->_serviceWorkload->getWorkload_F4($subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-ведомость передана. 
						$workloadTotal = $_F0 * $_F4 * ( $_F1 + $_F2);	
					} else {					
						# без разделения на периоду. Берем все, что попадает в заданный промежуток
						if($ignorePeriods){ 
							$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($tutor_id, $subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-приветственное письмо		
							$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($tutor_id, $subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-проверка заданий		
							$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($tutor_id, $subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-форум
							$_F4 = $this->_serviceWorkload->getWorkload_F4($subject_id, date('Y-m-d', strtotime($date_end_mod)), date('Y-m-d', strtotime($date_begin_mod))); //-ведомость передана. 
							if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL){ # для всех - нужны не часы, а кол-во выставленных работ
								$workloadTotal = $_F0 * $_F4 * ( ($_F1/0.35) + ($_F2/0.05) + $_F3);		
							} else {
								$workloadTotal = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
							}
							
						} else {
							$workloadSpring = 0;
							$workloadAutumn = 0;
							$periods = $this->getPeriods(strtotime($date_begin_mod), strtotime($date_end_mod));
							
							if($periods){														
								$_F3 = 0; //-вебираны не влияют на дату.
								if(isset($periods['spring'])){								
									$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($tutor_id, $subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-приветственное письмо		
									$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($tutor_id, $subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-проверка заданий		
									$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($tutor_id, $subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-форум										
									$_F4 = $this->_serviceWorkload->getWorkload_F4($subject_id, date('Y-m-d', $periods['spring']['end']), date('Y-m-d', $periods['spring']['begin'])); //-ведомость передана. 
									if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL){ # для всех - нужны не часы, а кол-во выставленных работ
										$workloadSpring = $_F0 * $_F4 * ( ($_F1/0.35) + ($_F2/0.05) + $_F3);		
									} else {
										$workloadSpring = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
									}
								}							
								if(isset($periods['autumn'])){								
									$_F0 = $this->_serviceWorkload->getWorkloadFix_F0($tutor_id, $subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-приветственное письмо		
									$_F1 = $this->_serviceWorkload->getWorkloadFix_F1($tutor_id, $subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-проверка заданий		
									$_F2 = $this->_serviceWorkload->getWorkloadFix_F2($tutor_id, $subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-форум										
									$_F4 = $this->_serviceWorkload->getWorkload_F4($subject_id, date('Y-m-d', $periods['autumn']['end']), date('Y-m-d', $periods['autumn']['begin'])); //-ведомость передана. 
									if($report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL){ # для всех - нужны не часы, а кол-во выставленных работ
										$workloadAutumn = $_F0 * $_F4 * ( ($_F1/0.35) + ($_F2/0.05) + $_F3);		
									} else {
										$workloadAutumn = $_F0 * $_F4 * ( $_F1 + $_F2 + $_F3);	
									}	
								}														
							}
						}
					}
					
					if($isTutor){
						$content[] = array(
							'subject_id'	=> $subject_id,
							'name' 			=> $i['name'],
							'groups' 		=> $groups,
							'students' 		=> $students,
							'f0' 			=> $_F0,
							'f1' 			=> $_F1,
							'f2' 			=> $_F2,
							'f4' 			=> $_F4,						
							'workloadAutumn'=> $workloadAutumn,
							'workloadSpring'=> $workloadSpring,
							'workloadTotal' => $workloadTotal,	
							'subject_begin' => (!empty($i['begin'])) ? (date('d.m.Y', strtotime($i['begin']))) : (''),
							'subject_end' 	=> (!empty($i['end'])) ? (date('d.m.Y', strtotime($i['end']))) : (''),
							'subject_debt' 	=> (!empty($i['time_ended_debt'])) ? (date('d.m.Y', strtotime($i['time_ended_debt']))) : (''),							
						);
					} else {
						$content[] = array(
							'subject_id'	=> $subject_id,
							'fio' 			=> $i['fio'],
							'department' 	=> $departmentName,
							'faculty' 		=> $this->updateFaculty($i['faculty']),
							'name' 			=> $i['name'],
							'groups' 		=> $groups,
							'students' 		=> $students,							
							'workloadAutumn'=> $workloadAutumn,
							'workloadSpring'=> $workloadSpring,
							'workloadTotal' => $workloadTotal,
							'subject_begin' => (!empty($i['begin'])) ? (date('d.m.Y', strtotime($i['begin']))) : (''),
							'subject_end' 	=> (!empty($i['end'])) ? (date('d.m.Y', strtotime($i['end']))) : (''),
							'subject_debt' 	=> (!empty($i['time_ended_debt'])) ? (date('d.m.Y', strtotime($i['time_ended_debt']))) : (''),						
						);
					}
				}
				$this->view->report_type_id = $report_type_id;
				$subTemplate = $ignorePeriods	? '_light' : '';
				
				$template = ($isTutor) ? ('_tutor'.$subTemplate.'.tpl') : ('_dean'.$subTemplate.'.tpl');
				
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
					#var_dump($template);
					#die;
					$tt = $this->view->render('report/parts/excel/workload/'.$template);
					$this->getExportExcel($tt, 'workload');						
					exit();
				}	
				$this->view->getParams 		= $this->_request->getParams();
				$this->view->content 		= $content;				
			}			
			$content = $this->view->render('report/parts/_workload_report'.$template);			
			echo $content;			
		} catch (Exception $e) {
			#echo $e->getMessage();			
			echo _('Произошла ошибка');
		}
	}
	
	
	/**
	 * по просрочке такая же ф-ция. вынести к сервис subject
	*/
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
	 * по просрочке такая же ф-ция. вынести к сервис subject
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
	 * по просрочке такая же ф-ция. вынести к сервис subject 	 
	*/
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