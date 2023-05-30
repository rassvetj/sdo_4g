<?php
class Report_UncheckedWorksController extends HM_Controller_Action_Crud
{
    protected $_faculties  = array(); # факультеты mid => fio
    protected $_departments  = array(); # кафедры mid => fio
	
	const CACHE_NAME = 'Report_UncheckedWorksController';
	protected $_reportBallTutors 	= array(); # отчет по выставленным оценкам тьютора.
	protected $_reportTutorID 		= NULL; # выбранный тьютор
	protected $_statusTime 			= NULL; # условие отбора
	protected $_statusDebt 			= NULL; # условие отбора
	
	protected $_userList 			= array(); # накопительный кэш списка пользователей. mid => fio
	protected $_blockedUsers 		= array(); # список всех заблокированных пользователей, в т.ч. и студентов mid => mid
	protected $_tutorAssigns 		= array(); # список всех назначений тьюторов на сессию subject_id => array of tutor id
	
	protected $_serviceUser 		= null;
	protected $_serviceTutor 		= null; 
	
	
	protected $_countStudents 		= NULL; 
	protected $_currentLang	= 'rus'; 
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'reportBallTutors' 	=> $this->_reportBallTutors,                                  
                'reportTutorID' 	=> $this->_reportTutorID, 
                'statusTime' 		=> $this->_statusTime, 
                'statusDebt' 		=> $this->_statusDebt, 
                'countStudents' 	=> $this->_countStudents,                                  
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
            $this->_reportBallTutors  	= $actions['reportBallTutors'];
            $this->_reportTutorID  		= $actions['reportTutorID'];   
            $this->_statusTime  		= $actions['statusTime'];   
            $this->_statusDebt  		= $actions['statusDebt'];   
            $this->_countStudents  		= $actions['countStudents'];
            $this->_restoredFromCache 	= true;
            return true;
        }
        return false;
    }
	
	
	public function init()
    {
        parent::init();       
		
        $this->getService('Unmanaged')->setHeader(_('Кол-во непроверенных работ (ДО)'));
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
    }
	
	
    public function indexAction()
    {        
		$this->clearCache();	
		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		
		$this->view->form = new HM_Form_Unchecked();
		$tutors = $this->getService('Report')->getTutorList();
		if($this->_currentLang == 'eng') {
			foreach($tutors as $key => $value) 
				$tutors[$key] = $this->translit($value);
			
			$tutors[''] = 'All';		
		}		
		$this->view->tutors = $tutors;	
    }
	
	public function getAction()
    {	
	/*	    
    Для занятия с типом «Задание» подсчитать кол-во непроверенных работ. Статус занятия - Решение на проверку или вопрос преподавателю. 
    Примечание
		Выводить только НЕ нулевые значения кол-ва непроверенных работ. Если нарушений нет, выводить ничего не надо 
		Для текущей сессии отсчет кол-ва начинается через три дня после типа сообщения студента «Вопрос преподавателю или решение на проверку».
	*/
		
		#$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
			try {
			##########
				$tutor_id 		= intval($request->getParam('tutor_id', false));				
				$status_time 	= intval($request->getParam('status_time', false));
				$status_debt 	= intval($request->getParam('status_debt', false));
				
				
				$serviceUser 		 		= $this->getService('User');		
				$serviceSubject 	 		= $this->getService('Subject');		
				$serviceGroup   	 		= $this->getService('StudyGroup');	
				$serviceSGProgramm   		= $this->getService('StudyGroupProgramm');	
				$this->_serviceOrgstructure = $this->getService('Orgstructure');				
				$curDT						= new DateTime;				
				
				$this->restoreFromCache();				
				if($this->_reportTutorID === $tutor_id && $this->_statusTime === $status_time && $this->_statusDebt === $status_debt){						
						$totalData 		= $this->_reportBallTutors;
						$countStudents 	= $this->_countStudents;
				} else {
						# Сессии, которые будут исключены из основного запроса
						/*
						$subSelect = $serviceUser->getSelect();
						$subSelect->from(array('subj'	=> 'subjects'), array('subj' => 'subj.subid'));
						$subSelect->join(array('l' 		=> 'schedule'), 'l.CID = subj.subid', array());						
						$subSelect->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()));            
						$subSelect->where('l.typeID NOT IN (?)', array(HM_Event_EventModel::TYPE_TASK, HM_Event_EventModel::TYPE_JOURNAL)); # исключаем уроки с типом "задание", "журнал"						
						$subSelect->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
						$subSelect->group('subj.subid');
						*/
						####
						
						$select = $serviceUser->getSelect();
						
						if($tutor_id > 0){ $select->where($serviceUser->quoteInto('t.MID = ?', $tutor_id)); }
						
						if($status_time == HM_Report_ReportModel::STATUS_TIME_CURRENT){							
							$select->where($serviceUser->quoteInto(
								array('(subj.end >= ?', ' OR subj.time_ended_debt >= ? ', ' OR subj.time_ended_debt_2 >= ? )'),
								array($curDT->format('Y-m-d 00:00:00') , $curDT->format('Y-m-d 00:00:00') , $curDT->format('Y-m-d 00:00:00') )								
							));
						} elseif($status_time == HM_Report_ReportModel::STATUS_TIME_PAST){							
							$select->where($serviceUser->quoteInto(
								array(
									'(	   (subj.end < ? AND subj.time_ended_debt IS NULL AND subj.time_ended_debt_2 IS NULL) ', # нет продления
									'   OR (subj.end < ? AND subj.time_ended_debt < ? AND subj.time_ended_debt_2 IS NULL )', # первое продление прошло
									'   OR (subj.end < ? AND subj.time_ended_debt_2 < ? )	)' # второе продление прошло. Неважно, что за дата у первого продления
								
								),
								array(
									$curDT->format('Y-m-d 00:00:00'),
									$curDT->format('Y-m-d 00:00:00'), $curDT->format('Y-m-d 00:00:00'),
									$curDT->format('Y-m-d 00:00:00'), $curDT->format('Y-m-d 00:00:00'),
								)								 
							));
						}
						
						if($status_debt == HM_Report_ReportModel::STATUS_DEBT_YES){
							$select->where('( subj.time_ended_debt IS NOT NULL OR subj.time_ended_debt_2 IS NOT NULL )'); # Продлена
						} elseif($status_debt == HM_Report_ReportModel::STATUS_DEBT_NO){												
							$select->where($serviceUser->quoteInto(" (subj.time_ended_debt IS NULL OR subj.time_ended_debt = ?) ", ''));
							$select->where($serviceUser->quoteInto(" (subj.time_ended_debt_2 IS NULL OR subj.time_ended_debt_2 = ?) ", '')); # не продлена
						}
									
						$select->from(array('subj' => 'subjects'), array(
							'tutor_id'			=> 'p.MID',
							'fio'				=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
							'subject_id'		=> 'subj.subid',
							'subject_name'		=> 'subj.name',
							'lesson_id'			=> 'l.SHEID',
							'lesson_name'		=> 'l.title',
							'lesson_type'		=> 'l.typeID',
							'zet'				=> 'subj.zet',
							'semester'			=> 'subj.semester',							
							'isCurrent'			=> new Zend_Db_Expr(" CASE WHEN subj.end >= '".$curDT->format('Y-m-d 00:00:00')."' THEN 1 
																	  ELSE 
																		  
																		CASE WHEN subj.time_ended_debt >= '".$curDT->format('Y-m-d 00:00:00')."' THEN 1 
																		ELSE 
																		
																			CASE WHEN subj.time_ended_debt_2 >= '".$curDT->format('Y-m-d 00:00:00')."' THEN 1 
																			ELSE 0 END
																		
																		END
																		
																	  END "),
							'begin'				=> 'subj.begin',
							'end'				=> 'subj.end',
							'time_ended_debt'	=> new Zend_Db_Expr('CASE WHEN subj.time_ended_debt_2 IS NOT NULL THEN subj.time_ended_debt_2 ELSE subj.time_ended_debt END'),
							'subject_external_id'=> 'subj.external_id',
						));
						$select->joinLeft(array('t' 	=> 'Tutors'), 'subj.subid = t.CID', array());
						$select->joinLeft(array('p' 	=> 'People'), 't.MID = p.MID AND (p.blocked IS NULL OR p.blocked = '.HM_User_UserModel::STATUS_ACTIVE.')', array());
						
						$select->join(array('l' 	=> 'schedule'), 'l.CID = subj.subid', array());
						
						$select->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()));  
						$select->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
						#$select->where($serviceUser->quoteInto('subj.subid NOT IN (?)', $subSelect));
						$select->where('subj.base = ?', 2); 
												
						$select->where('subj.isDO = ?', HM_Subject_SubjectModel::FACULTY_DO);
						#$select->where('(p.blocked IS NULL OR p.blocked = ?)', HM_User_UserModel::STATUS_ACTIVE);
						
						#if($this->getService('User')->getCurrentUserId() == 5829){
							#$select->where('subj.subid = ?', 12101);
							#echo $select->assemble();
						#die;
						#}
						
						
						#echo $select->assemble();
						#die;
						
						
						try {
							$res = $select->query()->fetchAll();
						} catch (Exception $e) {							
							echo '<br>1. Код ошибки: ',  $e->getCode(), "\n";
							die;
						}
						
						
						#$res = $select->query()->fetchAll();						
						unset($select);
						#unset($subSelect);
						
						if(empty($res)){
							echo 'Нет данных';
							die;
						}
						
						$tutorsIDs 			= array();
						$lessonIDs 			= array();
						$subjectIDs 		= array();
						$totalData 			= array(); # конечный результат отчета
						$currentLessonIDs	= array(); # занятия текущих сессий
						$availableSubjectList = array(); # список признаков доступности тьютора к сессии. Недоступен м.б. из-за продления.
						
						$lessonOnSubject = array(); # все занятия с группировкой оп сессиям
						
						foreach($res as $i){							
							$tutorsIDs[$i['tutor_id']] 		= $i['tutor_id'];
							$lessonIDs[$i['lesson_id']] 	= $i['lesson_id'];
							$subjectIDs[$i['subject_id']] 	= $i['subject_id'];
							
							$lessonOnSubject[$i['subject_id']][$i['lesson_id']] = $i['lesson_id'];
							
							$availableStudents = empty($i['tutor_id'])	?	false	:	$serviceSubject->getAvailableStudents($i['tutor_id'], $i['subject_id']);
							
							# Тьютор есть и проверка на доступность еще не делалась.
							if(!empty($i['tutor_id']) && !isset($availableSubjectList[$i['tutor_id']][$i['subject_id']])){
								$availableTutors 										= $this->getService('Subject')->getAvailableDebtorTutors($i['subject_id']);
								$availableSubjectList[$i['tutor_id']][$i['subject_id']]	= isset($availableTutors[$i['tutor_id']])	?	'Да'	:	'Нет';
							}
							
							
							$totalData[$i['tutor_id'].'~'.$i['subject_id']] = array(
								'tutor_id' 			=> $i['tutor_id'],
								'tutor_name' 		=> $i['fio'],
								'subject_id'		=> $i['subject_id'],
								'subject_external_id'=> $i['subject_external_id'],								
								'subject_name'		=> $i['subject_name'],
								'zet'				=> $i['zet'],						
								'availableStudents' => $availableStudents,
								'faculty'			=> $this->getFaculty($i['tutor_id']),
								'department'		=> $this->getDepartment($i['tutor_id']),
								'lessonIDs'			=> $lessonOnSubject[$i['subject_id']], #$i['lesson_id'],
								'semester'			=> ( $i['semester'] % 2 ) ? ('не четный') : ('четный'),
								'isCurrent'			=> ($i['isCurrent'] > 0) ? ('Текущая') : ('Прошедшая'),
								'begin'				=> date('d.m.Y', strtotime($i['begin'])),
								'end'				=> date('d.m.Y', strtotime($i['end'])),
								'time_ended_debt'	=> (strtotime($i['time_ended_debt'])) ? (date('d.m.Y', strtotime($i['time_ended_debt']))) : ('нет'),
								'isAvailableSubject'=> $availableSubjectList[$i['tutor_id']][$i['subject_id']],
							);
							
							# Если тьютора нет, находим всех заблокированных этого курса.							
							if(empty($i['tutor_id'])){
								if(empty($totalData[$i['tutor_id'].'~'.$i['subject_id']]['blocked_tutor_name'])){
									$totalData[$i['tutor_id'].'~'.$i['subject_id']]['blocked_tutor_name'] = $this->getBlockedTutors($i['subject_id']);
								}
							}
							
							if($i['isCurrent'] > 0){
								$currentLessonIDs[$i['lesson_id']] = $i['lesson_id'];	
							}
						}
						
						if(empty($lessonIDs) || empty($subjectIDs)){
							echo 'Нет данных';
							die;
						}
						
						#### Находим слушателей + завершивших обучение на сессии				
						$subUsers = $serviceUser->getSelect();
						$subUsers->from(array('Students'),	array('MID','CID'));
						
						$subGrad = $serviceUser->getSelect();
						$subGrad->from(array('graduated'),	array('MID','CID'));
						
						$subUSelect = $serviceUser->getSelect();
						$subUSelect->union(array($subUsers, $subGrad));
						
						$studentSelect = $serviceUser->getSelect();			
						$studentSelect->from(array('s' => $subUSelect), array('subject_id' => 's.CID', 'student_id' => 's.MID'));				
						$studentSelect->join(array('p' => 'People'), 'p.MID = s.MID', array());
						$studentSelect->where('s.CID > ?', 0);
						$studentSelect->where('s.MID > ?', 0);
						$studentSelect->where('p.blocked != 1'); # не учитываем заблокированных
						$studentSelect->where($serviceUser->quoteInto('s.CID IN (?)', $subjectIDs));
						$studentSelect->group(array('s.CID', 's.MID'));
						#$resStudent = $studentSelect->query()->fetchAll();
						
						try {
							$resStudent = $studentSelect->query()->fetchAll();
						} catch (Exception $e) {							
							echo '<br>2. Код ошибки: ',  $e->getCode(), "\n";
							die;
						}
						
						$assignStudents = array();
						if(!empty($resStudent)){
							foreach($resStudent as $i){
								$assignStudents[$i['subject_id']][] = $i['student_id'];
							}
						}
						unset($resStudent);								
						unset($studentSelect);								
						unset($subUSelect);								
						unset($subUsers);								
						unset($subGrad);								
						####
						
						
					
						# отбираем данные по последнему сообщению: решение на проверку.							
						$subProveSelect = $serviceUser->getSelect();
						$subProveSelect->from('interview', array('interview_id'=> new Zend_Db_Expr('MAX(interview_id)')));
						$subProveSelect->group(array('lesson_id', 'interview_hash'));
						
						$proveSelect = $serviceUser->getSelect();
						$proveSelect->from(array('m' => 'interview'), array(
							'm.lesson_id',
							'm.user_id',
						));
						$proveSelect->join(array('m2' => $subProveSelect), 'm2.interview_id = m.interview_id', array());
						#$proveSelect->join(array('p' => 'People'), 'm.user_id = p.MID', array());
						
						#$proveSelect->join(array('l' => 'schedule'), 'l.SHEID = m.lesson_id', array());                        
						#$proveSelect->join(array('st' => 'Students'), 'st.MID = m.user_id AND st.CID=l.CID', array());


						$proveSelect->where('type IN (?)', array(HM_Interview_InterviewModel::MESSAGE_TYPE_TEST, HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION));
						#$proveSelect->where('p.blocked != 1');
						
						# для занятий текущий сессии только те сообщения, дата создания которых более 3 дней, для прошедших - ограничения нет
						$curDT->setTime(23, 59, 59);
						$curDT->sub(new DateInterval('P3D'));
						
						if(!empty($currentLessonIDs)){
							$proveSelect->where($serviceUser->quoteInto(
								array('((m.date < ? ', ' AND m.lesson_id IN (?)) ', ' OR m.lesson_id NOT IN (?))'),
								array($curDT->format('Y-m-d 00:00:00'), $currentLessonIDs, $currentLessonIDs)
							));
						}
					
						$proveSelect->group(array('m.lesson_id', 'm.user_id'));
						#echo $proveSelect->assemble();
						#$resProve =  $proveSelect->query()->fetchAll();
						
						try {
							$resProve =  $proveSelect->query()->fetchAll();
						} catch (Exception $e) {							
							echo '<br>3. Код ошибки: ',  $e->getCode(), "\n";
							#echo $proveSelect->assemble();
							die;
						}
						
						# Запрос слишком толстый. Поэтому не будет брать таблицы студентов, пользователей, занятий.
						# Отдельно получим активных пользователей и в цикле исключим из запроса по сообщениям заблокированных
						# Активные пользователи
						$activeSelect = $serviceUser->getSelect();
						$activeSelect->from(array('p' => 'People'), array(
							'p.MID',
							'l.SHEID'
						));
						$activeSelect->join(array('st' => 'Students'), 'st.MID = p.MID', array());
						$activeSelect->join(array('l' => 'schedule'), 'l.CID = st.CID', array());
						
						$activeSelect->where('(p.blocked IS NULL OR p.blocked = ?)', HM_User_UserModel::STATUS_ACTIVE);
						$resActive =  $activeSelect->query()->fetchAll();
						
						$activeUsers = array(); # активные пользователи
						if(!empty($resActive)){
							foreach($resActive as $p){
								$activeUsers[$p['MID']][$p['SHEID']] = $p['SHEID'];
							}
						}
						####
						
						
						
						
						
						$proveData = array(); # набор студентов, которые последние написали в уроке.
						if(!empty($resProve)){
							foreach($resProve as $p){
								if(!isset($activeUsers[$p['user_id']][$p['lesson_id']])){ continue; } # отсеиваем заблокированных
								$proveData[$p['lesson_id']][] = $p['user_id'];
							}
						}
						#pr($proveData[2679]);
						unset($resProve);
						unset($proveSelect);
						unset($subProveSelect);														
						
						
						
						#### вычисляем кол-во студентов как пересечение между доступных тьютору и всего назначено на сессию
						foreach($totalData as $key => $i){
							
							#pr($i);
							
							if($i['availableStudents'] === false){ # доступны все студенты. 
								$tmpStudentIDs = $assignStudents[$i['subject_id']];
							} else {
								$tmpStudentIDs = (!empty($assignStudents[$i['subject_id']]) && !empty($i['availableStudents']))?(array_intersect($assignStudents[$i['subject_id']], $i['availableStudents'])):(array());
							}
							
							$totalData[$key]['countStudents'] 	= count($tmpStudentIDs);
							$totalData[$key]['groups'] 			= (empty($tmpStudentIDs))?(false):($serviceGroup->getGroupListOnUserIDs($tmpStudentIDs));
							$totalData[$key]['programms']		= (empty($totalData[$key]['groups']))?(false):($serviceSGProgramm->getProgrammsByGroups(array_keys($totalData[$key]['groups'])));
							$totalData[$key]['totalNeedBallStudentd'] =  ($totalData[$key]['countStudents']) * 3 * intval($totalData[$key]['zet']);	
							
							$totalData[$key]['isLastTutor']		= $this->isLastTutor($i['subject_id'], $i['tutor_id']) ? 'Да' : 'Нет';
							
							$totalData[$key]['proveCount'] = 0;
							if(count($i['lessonIDs'])){								
								foreach($i['lessonIDs'] as $lesson_id){	
								
									if(isset($proveData[$lesson_id])){
										if($i['availableStudents'] === false){ # доступны все студенты.
											$totalData[$key]['proveCount'] = $totalData[$key]['proveCount'] + count($proveData[$lesson_id]);											
										} else {																					
											$totalData[$key]['proveCount'] = $totalData[$key]['proveCount'] + count(array_intersect($proveData[$lesson_id], $tmpStudentIDs));
										}
									} else {										
										$totalData[$key]['proveCount'] = $totalData[$key]['proveCount'] + 0;
									}
								}
							}
							
							if($totalData[$key]['proveCount'] < 1){								
								unset($totalData[$key]);
								continue;
							}
							
							unset($totalData[$key]['availableStudents']);
						}
						
						
						#### Берем только послеюнюю выставленную оценку.
						$subMessageSelect = $serviceUser->getSelect();
						$subMessageSelect->from('interview', array('interview_id'=> new Zend_Db_Expr('MAX(interview_id)')));
						$subMessageSelect->where('type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
						$subMessageSelect->group(array('lesson_id', 'user_id', 'to_whom'));
						####
						
						#### Отбор кол-во выставленных оценок в уроке.
						$messageSelect = $serviceUser->getSelect();
						$messageSelect->from(array('m' => 'interview'), array(
							'lesson_id'		=> 'm.lesson_id',					
							'tutor_id'		=> 'm.user_id',									
							'count_students'=> new Zend_Db_Expr('COUNT(m.to_whom)'),					
						));
						$messageSelect->join(array('m2' => $subMessageSelect), 'm2.interview_id = m.interview_id', array());
						$messageSelect->where('m.type = ?', HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);
						$messageSelect->where($serviceUser->quoteInto('m.user_id IN (?)', $tutorsIDs));
						$messageSelect->where($serviceUser->quoteInto('m.lesson_id IN (?)', $lessonIDs));
						$messageSelect->group(array('m.lesson_id', 'm.user_id'));
						####
						
						#$resMes = $messageSelect->query()->fetchAll();
						
						
						try {
							$resMes = $messageSelect->query()->fetchAll();
						} catch (Exception $e) {							
							echo '<br>4. Код ошибки: ',  $e->getCode(), "\n";
							die;
						}
						
						
						unset($messageSelect);
						unset($subMessageSelect);						
						unset($subjectIDs);
						unset($tutorsIDs);
						$mesData = array();
						if(!empty($resMes)){
							foreach($resMes as $m){
								$mesData[$m['tutor_id']][$m['lesson_id']] = $m['count_students'];
							}
						}
						
						
						#берем кол-во выставленных оценок в уроке для журнала
						$jSelect = $serviceUser->getSelect();
						$jSelect->from(array('lu' => 'scheduleID'), array(
							'lesson_id'		=> 'lu.SHEID',												
							'count_students'=> new Zend_Db_Expr('COUNT(lu.MID)'),					
						));
						$jSelect->join(array('l' => 'schedule'), 'l.SHEID = lu.SHEID', array());
						$jSelect->where('l.typeID = ?', HM_Event_EventModel::TYPE_JOURNAL);
						$jSelect->where('lu.V_STATUS > ?', 0);
						
						$jSelect->where($serviceUser->quoteInto('l.SHEID IN (?)', $lessonIDs));
						$jSelect->group(array('lu.SHEID'));
						#$resJournal = $jSelect->query()->fetchAll();
						
						try {
							$resJournal = $jSelect->query()->fetchAll();
						} catch (Exception $e) {							
							echo '<br>5. Код ошибки: ',  $e->getCode(), "\n";
							die;
						}
						
						
						$jData = array();
						if(!empty($resJournal)){
							foreach($resJournal as $m){
								$jData[$m['lesson_id']] = $m['count_students'];
							}
						}
						######
						unset($lessonIDs);
						
						
						
						
						$data = array();						
						foreach($res as $i){
							if(!isset($totalData[$i['tutor_id'].'~'.$i['subject_id']])){ continue; }
							
							if($i['lesson_type'] == HM_Event_EventModel::TYPE_JOURNAL){
								$ballCount = (isset($jData[$i['lesson_id']])) ? ($jData[$i['lesson_id']]) : (0);					
							} else {
								$ballCount = (isset($mesData[$i['tutor_id']][$i['lesson_id']])) ? ($mesData[$i['tutor_id']][$i['lesson_id']]) : (0);													
							}
							#$ballCount = (isset($mesData[$i['tutor_id']][$i['lesson_id']])) ? ($mesData[$i['tutor_id']][$i['lesson_id']]) : (0);					
							$type = false;
							if(stristr($i['lesson_name'], 'Академическая активность') !== FALSE) { # тип 1
								$type = 1;
							} elseif(stristr($i['lesson_name'], 'Задание к разделу') !== FALSE) { # тип 2
								$type = 2;
							} elseif(stristr($i['lesson_name'], 'Рубежный контроль') !== FALSE) { # тип 3
								$type = 3;
							} elseif(stristr($i['lesson_name'], 'Итоговый контроль') !== FALSE) { # тип 4
								$type = 4;
							}	
							
							if($type){								
								$totalData[$i['tutor_id'].'~'.$i['subject_id']]['ballCount'][$type] = $totalData[$i['tutor_id'].'~'.$i['subject_id']]['ballCount'][$type] + $ballCount;												
								$totalData[$i['tutor_id'].'~'.$i['subject_id']]['ballCountDetail'][$type][$i['lesson_id']] = $ballCount;									
							}
						}
						unset($res);				
						unset($mesData);
						
						$this->_reportBallTutors = $totalData;
						$this->_countStudents 	 = $countStudents;	
						$this->_reportTutorID	 = $tutor_id;												
						$this->_statusTime	 	 = $status_time;												
						$this->_statusDebt	 	 = $status_debt;												
						$this->saveToCache();
				}
				
				$this->view->countStudents 	= $countStudents;
				$this->view->data 			= $totalData;				
			##########
			} catch (Exception $e) {
				//echo 'Ошибка: ',  $e->getMessage(), "\n";
				echo '<br>Код ошибки: ',  $e->getCode(), "\n";
				#echo $e->getMessage();
			}									
		}		
	}
	
	
	public function getFaculty($user_id){
		if(empty($user_id)){ return ''; }
		
		if(isset($this->_faculties[$user_id])){ return $this->_faculties[$user_id]; }
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$this->_faculties[$user_id] = $this->_serviceOrgstructure->getFaculty($user_id);
		return $this->_faculties[$user_id];	
	}
	
	public function getDepartment($user_id){
		if(empty($user_id)){ return ''; }
		
		if(isset($this->_departments[$user_id])){ return $this->_departments[$user_id]; }
		if(!$this->_serviceOrgstructure){ $this->_serviceOrgstructure = $this->getService('Orgstructure');	}
		$this->_departments[$user_id] = $this->_serviceOrgstructure->getDepartmentName($user_id);
		return $this->_departments[$user_id];	
	}
	
	public function getCsvAction(){		
		$this->_helper->getHelper('layout')->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		$filename = 'Отчет по оценкам тьюторов_от_'.date('d.m.Y H.i');
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		
		$this->restoreFromCache();						
		#$this->_reportBallTutors;
		#$this->_countStudents;
		
		$fields = array(
			_('Текущая/прошедшая'),
			_('Дата начала'),
			_('Дата окончания'),
			_('Дата продления'),
			_('ФИО тьютора'),
			_('Сессия доступна тьютору'),
			_('Последний тьютор'),
			_('Факультет'),
			_('Кафедры'),
			_('Сессия'),
			_('ID сессии (1C)'),			
			_('ID сессии'),			
			_('ЗЕТ'),			
			_('Нет реакции преподавателя'),
			_('Группы студентов'),
			_('Программы обучения'),
			_('Ссылка на сессию'),
			_('Тьютор с заблокированной записью'),
		);		
		//добавялем BOM
		echo "\xEF\xBB\xBF";
		echo implode(';', $fields);
		echo "\r\n";
		$delim = ';';
		foreach($this->_reportBallTutors as $i){ 
			echo $i['isCurrent'].$delim;
			echo $i['begin'].$delim;
			echo $i['end'].$delim;
			echo $i['time_ended_debt'].$delim;		
		
			echo $i['tutor_name'].$delim;			
			echo $i['isAvailableSubject'].$delim;
			echo $i['isLastTutor'].$delim;
			echo $i['faculty'].$delim;
			echo $i['department'].$delim;			
			echo $i['subject_name'].$delim;
			echo $i['subject_external_id'].'.'.$delim;
			echo $i['subject_id'].$delim;			
			echo $i['zet'].$delim;			
			echo $i['proveCount'].$delim;
			
			$groups = (!empty($i['groups']))?(implode(', ', $i['groups'])):('Нет');
			echo $groups.$delim;
			
			$programms = (!empty($i['programms']))?(implode(', ', $i['programms'])):('Нет');
			echo $programms.$delim;
			
			echo 'http://'.$_SERVER['SERVER_NAME'].$this->view->baseUrl($this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id']))).$delim;
			
			echo $i['blocked_tutor_name'].$delim;
			
			echo "\r\n";
		}				
	}
	
	/**
	 * Является ли указанный тьютор - последним назначенным
	*/
	public function isLastTutor($subject_id, $tutor_id){
		$last_tutor = $this->getLastTutor($subject_id);
		if($last_tutor == $tutor_id){ return true; }
		return false;
	}
	
	/**
	 * Последний назначенный тьютор - id
	*/
	public function getLastTutor($subject_id){
		if(empty($subject_id)){ return false; }
		
		$serviceUser = $this->getService('User');
		$select   = $serviceUser->getSelect();
		$select->from('Tutors', array('MID'));
		$select->where('CID = ?', $subject_id);
		$select->where('MID > ?', 0);
		$select->order(array('date_assign DESC', 'TID DESC'));
		$row = $select->query()->fetchObject();
		if($row->MID){ return $row->MID; }
		return false;		
	}
	
	public function getBlockedTutors($subject_id){			
		if(empty($subject_id)){ return; }
		
		if(!$this->_tutorAssigns[$subject_id]){
			if(!$this->_serviceTutor){ $this->_serviceTutor = $this->getService('Tutor');  }
			$assigns = $this->_serviceTutor->getAssigns($subject_id);
			if(empty($assigns)){ 
				$this->_tutorAssigns[$subject_id] = array();				
				return;
			}
			$this->_tutorAssigns[$subject_id] = $assigns->getList('MID');			
		}
		
		if(empty($this->_tutorAssigns[$subject_id])){ return; }
		
		$blocked_tutors = array();
		foreach($this->_tutorAssigns[$subject_id] as $tutor_id){			
			if(!$this->isBlocked($tutor_id)){ continue; }
			$blocked_tutors[$tutor_id] = $this->getUserName($tutor_id);
		}
		if(empty($blocked_tutors)){ return; }
		return implode(', ', $blocked_tutors);
	}
	
	/**
	 * Список всех заблокированных в СДО
	*/
	public function isBlocked($tutor_id){
		if(empty($this->_blockedUsers)){
			if(!$this->_serviceUser) { $this->_serviceUser = $this->getService('User');  }
			$this->_blockedUsers = $this->_serviceUser->fetchAll(array('blocked = ?' => HM_User_UserModel::STATUS_BLOCKED))->getList('MID');
		}
		if(isset($this->_blockedUsers[$tutor_id])){ return true; }
		return false; 
	}
	
	
	public function getUserName($user_id){
		if(!isset($this->_userList[$user_id])){
			if(!$this->_serviceUser) { $this->_serviceUser = $this->getService('User');  }
			$user = $this->_serviceUser->getById($user_id);
			$this->_userList[$user_id] = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		}
		return $this->_userList[$user_id];
	}
	
	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	}	

}