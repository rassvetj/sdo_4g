<?php
class HM_Debtors_Import_Manager
{
    protected $_existingDebt = array();
    protected $_existingDebtIds = array();

    protected $_insertsLearningsubjects = array();
    protected $_updatesLearningsubjects = array();
    protected $_deletesLearningsubjects = array();
    
    protected $_existingUserIDs 		= array();    //--id из 1C
    protected $_existingSubjectIDs 		= array(); //--id из 1C
    
    protected $_attemptUserIDs 			= array();
    protected $_graduatedDebtors 		= array(); # должники, которые по какой-то причине завершили обучение. Переводим в обычное состояние и продляем сессию.
    protected $_graduatedDebtorsEnded 	= array(); # должники, которые завершили обучение, но они есть в должниках, но по баллам по урокам у них более 65 баллов, т.е. не должны быть должниками.
	
	
	protected $_subjectMarkService 		= null;	
	
	
	protected $tutorsToDebt 			= array();	# данные по назначению тьюторов на первое и второе продление: subject_id => tutor_id => date_debt, date_debt_2
	
	
	
	
	protected $_notFoundDebtors 		= array();
	protected $_notAssign		 		= array();
	protected $_groups					= array(); # список групп пользователей user_id => array of group name
	protected $_groupsSubject 			= array(); # список групп сессии, доступной через программу обучения вида subject_id => array of group name
	
	protected $_serviceGroup			= NULL;
	protected $_serviceMarkBrs 			= null;	
	protected $_serviceSubject 			= null;	
	protected $_serviceJResult 			= null;		
	protected $_serviceLesson 			= null;		
	protected $_serviceDebtors 			= null;	
	protected $_serviceSubjectMark		= null;	
	protected $_serviceUser				= null;	
	protected $_serviceTutor			= null;	
	protected $_serviceTutorGroup		= null;	
	
	protected $_incorrectData	 		= array(); # некорректные данные
	protected $_conflictData	 		= array(); # конфликтные ситуации
	protected $_alredyExtended 			= array(); # Уже продлены, продлять на надо.
	protected $_updateData	 			= array(); # данные для продления.
	protected $_userSubjects			= array(); # Сессии, на которые назначен студент. Вида MID => array of subjects
	protected $_tutorList				= array(); # Накопитльный массив тьюторов вида:  mid_external => array of other params (fio, mid and etc )
	protected $_tutorsAssignSubjects	= array(); # Тьюторы, которые будут назначены на сессию
	protected $_tutorsUpdateSubjects	= array(); # Тьюторы, которые уже назначены, но нужно обновить дату продления.
	protected $_tutorsAssignGroups		= array(); # Тьюторы, которые будут назначены на группу студента
	protected $_tutorNotFound			= array(); # Тьюторы, которых нет в БД
	protected $_removeRoleDebtFirst		= array(); # данные для удаления ролей первого продления. Ключ - сессия, значение - тьютор, которого надо оставить, остальных - понизить до обычного тьютора
	protected $_removeRoleDebtSecond	= array(); # данные для удаления ролей второго продления. Ключ - сессия, значение - тьютор, которого надо оставить, остальных - понизить до обычного тьютора
	protected $_removeLessonAssign		= array(); # Список тьюторов и сессий, в которых надо удалить назначение на занятия. Т.е. делаем доступным все занятия тьютору. tutor_id => subject_ids 

	# накопительный кэш
	#	оценок
	#	пользователей
	#	групп
	#	классификатора
		
		
	
	
    
    const CACHE_NAME = 'HM_Debtors_Import_Manager';
    
    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }
	
	/**
	 * переводит байты в читабельный вид
	*/
	public function convert($size)
	{
		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}

    private function _init()
    {				
		echo "Initial: ".$this->convert(memory_get_usage())." Mb \n<br>";
		$start = microtime(true);
		$select = $this->getService('Debtors')->getImportSelect();		
		$result = $select->query()->fetchAll();
		$time = microtime(true) - $start;
		printf('Скрипт выполнялся %.4F сек.', $time);
		echo "<br>1: ".$this->convert(memory_get_usage())." Mb \n<br>";

		
		$start = microtime(true);		
        if (count($result)) {
			$peopleList				= $this->getService('User')->getPeopleInfoList();
			echo "2: ".$this->convert(memory_get_usage())." Mb \n<br>";
			$subjectClassifiersList = $this->getService('ClassifierLink')->getSubjectClassifiersList();		
			echo "3: ".$this->convert(memory_get_usage())." Mb \n<br>";
			$userGroupList 			= $this->getService('StudyGroup')->getUsersGroupList();		
			echo "4: ".$this->convert(memory_get_usage())." Mb \n<br>";
			
            foreach($result as $i) {  
				$i['mid_external'] 				= $peopleList[$i['MID']]['mid_external'];
				$i['fio'] 						= $peopleList[$i['MID']]['fio'];
				$i['teacher_mid_external']		= $peopleList[$i['teacher_MID']]['mid_external'];
				$i['teacher_fio']				= $peopleList[$i['teacher_MID']]['fio'];
				$i['classifiers']				= $subjectClassifiersList[$i['CID']];
				$i['groups']					= $userGroupList[$i['MID']];
				$this->_existingDebt[$i['SID']] = $i;
                
				if (strlen($i['session_external_id'])  ) {					
                    $i['session_external_id'] 																= trim($i['session_external_id']);                    
                    $i['mid_external'] 																		= trim($i['mid_external']);                    
                    $this->_existingDebtIds[$i['mid_external'].'~'.$i['session_external_id']] 	= $i['SID'];
					$this->_existingUserIDs[$i['mid_external']] 											= $i['mid_external']; 
					$this->_existingSubjectIDs[$i['session_external_id']] 									= $i['session_external_id'];
                }				
            }
			echo "5: ".$this->convert(memory_get_usage())." Mb \n<br>";
			unset($result);
			unset($peopleList);
			unset($subjectClassifiersList);
			unset($userGroupList);				
			echo "6: ".$this->convert(memory_get_usage())." Mb \n<br>";
        }
		$time = microtime(true) - $start;
		printf('Скрипт выполнялся %.4F сек.', $time);
		die;		
    }

    protected function _needUpdate($debt)
    {        
		$key 			= $debt->mid_external.'~'.$debt->session_external_id;		
		$existingDebt 	= $this->_existingDebt[	$this->_existingDebtIds[$key]	];
		
		# Сессии с 65 и более не трогаем вовсе
		if($existingDebt['mark'] >= 65){
			$debt->reason_code 						= HM_Debtors_DebtorsModel::REASON_PASS_TO_MARK;
			$this->_conflictLearningsubjects[$key] 	= array('source' => $existingDebt, 'destination' => $debt);
			return false;
		}
		
		# даты продления отсутствуют или некорректные
		if(empty($debt->time_ended_debtor) && empty($debt->time_ended_debtor_2)){ 
			$debt->reason_code 						= HM_Debtors_DebtorsModel::REASON_NO_DATES;
			$this->_conflictLearningsubjects[$key] 	= array('source' => $existingDebt, 'destination' => $debt);
			return false;
		}
		
		$timestamp = false;
		if(empty($debt->time_ended_debtor_2)){ # первое продление
			$fieldName = 'time_ended_debtor';
		} else { # второе продление
			$fieldName = 'time_ended_debtor_2';
		}
		
		$timestamp = strtotime($debt->{$fieldName});
					
		if(!$timestamp || !$debt->{$fieldName}){
			$debt->reason_code 						= HM_Debtors_DebtorsModel::REASON_INCORRECT_DATE;			
			$this->_conflictLearningsubjects[$key] 	= array('source' => $existingDebt, 'destination' => $debt);
			return false;
		}
		
		if( $timestamp == strtotime($existingDebt[$fieldName]) ){ return false; }
		
		return true;
    }

    public function getInsertsCount()
    {
        return 0;
		//return count($this->_insertsLearningsubjects);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesLearningsubjects);
    }

    public function getDeletesCount()
    {
        return 0;		
    }
    
    public function getCount()
    {        
        return $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return array();	
    }

    public function getUpdates()
    {
        return $this->_updatesLearningsubjects;
    }

    public function getDeletes()
    {
        return $this->_deletesLearningsubjects;
    }
	
	public function getConflicts()
    {
        return $this->_conflictLearningsubjects;
    }
	
	public function getConflictCount()
    {
        return count($this->_conflictLearningsubjects);
    }
	
	public function getNotFound()
    {
        return $this->_notFoundDebtors;
    }
	
	public function getNotFoundCount()
    {
        return count($this->_notFoundDebtors);
    }
	
	/*
	public function getGraduatedDebtors()
    {
        return $this->_graduatedDebtors;
    }
	
	public function getGraduatedDebtorsCount()
    {
        return count($this->_graduatedDebtors);
    }
	*/
	/*
	public function getAlredyExtendedCount()
    {
        return count($this->_alredyExtended);
    }
	
	public function getAlredyExtended()
    {
        return $this->_alredyExtended;
    }
	*/
	/*
	public function getGraduatedDebtorsEndedCount()
    {
        return count($this->_graduatedDebtorsEnded);
    }
	
	public function getGraduatedDebtorsEnded()
    {
        return $this->_graduatedDebtorsEnded;
    }
	*/
	
	
	##
	public function getIncorrectData()			{ return $this->_incorrectData;  		}
	public function getConflictData()			{ return $this->_conflictData;  		}
	public function getNotFoundDebtors()		{ return $this->_notFoundDebtors; 		}
	public function getNotAssign()				{ return $this->_notAssign; 			}
	public function getAlredyExtended()			{ return $this->_alredyExtended; 		}
	public function getGraduatedDebtorsEnded()	{ return $this->_graduatedDebtorsEnded;	}
	public function getGraduatedDebtors()		{ return $this->_graduatedDebtors;		}
	public function getUpdateData()				{ return $this->_updateData;			}
	public function getTutorsAssignSubjects()	{ return $this->_tutorsAssignSubjects;	}
	public function getTutorsUpdateSubjects()	{ return $this->_tutorsUpdateSubjects;	}
	public function getTutorsAssignGroups()		{ return $this->_tutorsAssignGroups;	}
	public function getTutorNotFound()			{ return $this->_tutorNotFound;			}
	
	##
	
	
	
	

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' 					=> $this->_insertsLearningsubjects,
                 'updates' 					=> $this->_updatesLearningsubjects,
                 'deletes' 					=> $this->_deletesLearningsubjects,
                 'notfound' 				=> $this->_notFoundDebtors,
                 'graduatedDebtors' 		=> $this->_graduatedDebtors,
                 'attemptUserIDs' 			=> $this->_attemptUserIDs,
                 'conflictLearningsubjects' => $this->_conflictLearningsubjects,
                 'graduatedDebtorsEnded' 	=> $this->_graduatedDebtorsEnded,
                 'alredyExtended' 			=> $this->_alredyExtended,
                 'tutorsToDebt' 			=> $this->tutorsToDebt,                 			 		
                 'updateData' 				=> $this->_updateData,                 			 		
                 'tutorsAssignSubjects'		=> $this->_tutorsAssignSubjects,                 			 		
                 'tutorsUpdateSubjects'		=> $this->_tutorsUpdateSubjects,                 			 		
                 'tutorsAssignGroups'		=> $this->_tutorsAssignGroups,                 			 		
                 'conflictData'				=> $this->_conflictData,                 			 		
                 'incorrectData'			=> $this->_incorrectData,                 			 		
                 'notAssign'				=> $this->_notAssign,                 			 		
                 'tutorNotFound'			=> $this->_tutorNotFound,                 			 		
                 'removeRoleDebtFirst'		=> $this->_removeRoleDebtFirst,                 			 		
                 'removeRoleDebtSecond'		=> $this->_removeRoleDebtSecond,                 			 		
                 'removeLessonAssign'		=> $this->_removeLessonAssign,                 			 		
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
            $this->_insertsLearningsubjects  = $actions['inserts'];
            $this->_updatesLearningsubjects  = $actions['updates'];
            $this->_deletesLearningsubjects  = $actions['deletes'];
            $this->_notFoundDebtors 		 = $actions['notfound'];
            $this->_graduatedDebtors 		 = $actions['graduatedDebtors'];
            $this->_attemptUserIDs 			 = $actions['attemptUserIDs'];
            $this->_conflictLearningsubjects = $actions['conflictLearningsubjects'];
            $this->_graduatedDebtorsEnded 	 = $actions['graduatedDebtorsEnded'];
            $this->_alredyExtended 	 		 = $actions['alredyExtended'];
            $this->tutorsToDebt 	 	 	 = $actions['tutorsToDebt'];            			
            $this->_updateData 	 	 	 	 = $actions['updateData'];            			
            $this->_tutorsAssignSubjects 	 = $actions['tutorsAssignSubjects'];            			
            $this->_tutorsUpdateSubjects 	 = $actions['tutorsUpdateSubjects'];            			
            $this->_tutorsAssignGroups 	 	 = $actions['tutorsAssignGroups'];            			
            $this->_conflictData 	 	 	 = $actions['conflictData'];            			
            $this->_incorrectData 	 	 	 = $actions['incorrectData'];            			
            $this->_notAssign 	 	 	 	 = $actions['notAssign'];            			
            $this->_tutorNotFound 	 	 	 = $actions['tutorNotFound'];            			
            $this->_removeRoleDebtFirst 	 = $actions['removeRoleDebtFirst'];            			
            $this->_removeRoleDebtSecond 	 = $actions['removeRoleDebtSecond'];            			
            $this->_removeLessonAssign 	 	 = $actions['removeLessonAssign'];            			
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {   

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {			
			$serviceUser 		= $this->getService('User');
			$serviceSubject 	= $this->getService('Subject');
			$serviceGroup 		= $this->getService('StudyGroupUsers');
			$serviceGraduated 	= $this->getService('Graduated');
			$usersGroups 		= array();
			foreach($items as $key => $item) {
					
				# даты продления отсутствуют или некорректны
				if(empty($item->time_ended_debtor) && empty($item->time_ended_debtor_2)){ 
					$item->conflict_reason_code	= HM_Debtors_DebtorsModel::REASON_NO_DATES;
					$this->_incorrectData[$key] = $item;
					continue;
				}
				
				if(!empty($item->time_ended_debtor)){
					if(!strtotime($item->time_ended_debtor)){
						$item->conflict_reason_code	= HM_Debtors_DebtorsModel::REASON_INCORRECT_DATE;
						$this->joinTutors($item);
						$this->_conflictData[$key] = $item;
						continue;
					}
				}
				
				if(!empty($item->time_ended_debtor_2)){
					if(!strtotime($item->time_ended_debtor_2)){
						$item->conflict_reason_code	= HM_Debtors_DebtorsModel::REASON_INCORRECT_DATE;
						$this->joinTutors($item);
						$this->_conflictData[$key] = $item;
						continue;
					}
				}
				
				
				$isNotFound 		= false;
				$notFoundReasons 	= array();
				$user 				= $serviceUser->getOne($serviceUser->fetchAll($serviceUser->quoteInto('mid_external = ?', $item->mid_external)));
				if(!$user){
					$item->notFoundUser 			= true;
					$notFoundReasons[]	= HM_Debtors_DebtorsModel::REASON_NOT_FOUND_USER;
					$isNotFound = true;					
				}
				
				
				if($user){
					# группы пользователя
					$item->groups 		  = $this->getGroups($user->MID);	
					$item->student_id 	  = $user->MID;					
				}
				
				$subject = $serviceSubject->getOne($serviceSubject->fetchAll($serviceSubject->quoteInto('external_id = ?', $item->session_external_id)));				
				if(!$subject){
					$item->notFoundSubject	= true;										
					$notFoundReasons[]		= HM_Debtors_DebtorsModel::REASON_NOT_FOUND_SUBJECT;
					if($user){
						$item->supposedSubjects = $this->getSupposedSubjects($user->MID, $item->name);
					}					
					$isNotFound = true;
				}
				
				$item->exam_type_name 			 = HM_Subject_SubjectModel::getExamTypeName($subject->exam_type);
				
				if($isNotFound){
					$item->conflict_reason_code 	= $notFoundReasons;
					$this->_notFoundDebtors[$key] 	= $item;
					continue;
				}
				
				$item->subject_id 				 = $subject->subid;
				$item->subject_name 			 = $subject->name;
				$item->subject_external_id		 = $subject->external_id;
				$item->subject_time_ended_debt   = $subject->time_ended_debt;
				$item->subject_time_ended_debt_2 = $subject->time_ended_debt_2;
				$item->is_practice 				 = $subject->is_practice;
				
				$isGraduated = $serviceSubject->isGraduated($subject->subid, $user->MID);				
				$isStudent   = $serviceSubject->isStudent(  $subject->subid, $user->MID);
				
				# не назначен и не в прошедших
				if(!$isStudent && !$isGraduated ){
					$item->neverAssign				= true;
					$notFoundReasons[]				= HM_Debtors_DebtorsModel::REASON_NEVER_ASSIGN_USER;
					$isNotFound 					= true;
					$item->conflict_reason_code 	= $notFoundReasons;
					$this->_notFoundDebtors[$key] 	= $item;
					continue;					
					#$this->_notAssign[$key] = $item;
					#continue;
				}
				
				# студент может быть в завершенных, даже если дата окончания сессии еще не прошла
				if(strtotime($subject->end) > strtotime(date('d.m.Y')) && !$isGraduated){
					$item->conflict_reason_code	= HM_Debtors_DebtorsModel::REASON_SUBJECT_IS_CURRENT;
					$item->end 					= date('d.m.Y', strtotime($subject->end));
					$this->joinTutors($item);
					$this->setOldMarks(&$item);
					$this->_conflictData[$key]  = $item;
					continue;
				}
								
				# Полуцчаем список тьюторов и првоеряем, надо ли их назначить на сессию или на группу студента.
				$item->tutor 	= $this->getTutorList($item->tutor);
				$item->tutor_2 	= $this->getTutorList($item->tutor_2);
				
				# должна быть после  getTutorList
				$this->prepareTutors($item);
				
				# Прошлые даты продления
				if(!$this->_serviceDebtors) {	$this->_serviceDebtors = $this->getService('Debtors'); }				
				$assign 						= $this->_serviceDebtors->getOne($this->_serviceDebtors->fetchAll($this->_serviceDebtors->quoteInto(array('MID = ? AND', ' CID = ?'), array($user->MID, $subject->subid))));
				$item->old_time_ended_debtor	= $assign->time_ended_debtor;
				$item->old_time_ended_debtor_2	= $assign->time_ended_debtor_2;
				#$item->student_id 				= $user->MID;
				
				$this->setOldMarks(&$item);
				$this->setIsDO(&$item);
				
				# Даты продления не изменились. Продлять не надо.
				if(
					strtotime($item->time_ended_debtor) == strtotime($item->old_time_ended_debtor)
					&&
					strtotime($item->time_ended_debtor_2) == strtotime($item->old_time_ended_debtor_2)
				){
					$item->conflict_reason_code	= HM_Debtors_DebtorsModel::REASON_NOT_CHANGE;
					#$this->_conflictData[$key] = $item;
					$this->_alredyExtended[$key] = $item;
					continue;					
				}
				
				
				
				
				
				# уже продлены, проверяем, надо ли их продлять еще раз
				if(!empty($item->old_time_ended_debtor) || !empty($item->old_time_ended_debtor_2)){
					$isNeedUpdate = false;					
					
					if(!empty($item->time_ended_debtor)){						
						if(	strtotime($item->time_ended_debtor) != strtotime($item->old_time_ended_debtor)	){
							$isNeedUpdate = true;
						}
					}
					
					if(!empty($item->time_ended_debtor_2)){						
						if(	strtotime($item->time_ended_debtor_2) != strtotime($item->old_time_ended_debtor_2)	){
							$isNeedUpdate = true;
						}
					}
											
					# Если даты продления не изменились или не заданы, не продляем.
					if(!$isNeedUpdate) {												
						# а дойдет ли до сюда вообще???
						$this->_alredyExtended[$key] = $item;
						continue;						
					}
				}
				
				
				# добавляются соотв. свойства в $item в зависимости от исхода расчетов.
				# Прошел ли минимлаьный порог.
				if($this->isBallPass($user->MID, $subject, &$item)){
					# не продляем, а назначаем на сессию. Зачем?
					if($isGraduated){
						
						$subjService 	= $this->getService('Subject');
						$reasonFail		= $subjService->getFailPassMessage($item->student_id, $item->subject_id);
						if(empty($reasonFail)){							
							$this->_graduatedDebtorsEnded[$item->mid_external.'~'.$item->session_external_id] = $item;
						} else {
							$item->reasonFail = $reasonFail;
							$this->_graduatedDebtors[$item->mid_external.'~'.$item->session_external_id] = $item;
						}
						
					# Конфликтная ситуация. Причина задается в isBallPass
					} else {
						# проверить, есть ли причины недопуска. Если есть, то продлять долг.
						$subjService 	= $this->getService('Subject');
						$reasonFail		= $subjService->getFailPassMessage($item->student_id, $item->subject_id);
						if(empty($reasonFail)){						
							$this->joinTutors($item);
							$this->_conflictData[$key] = $item;	
						} else {	
							$item->reasonFail = $reasonFail;							
							$this->_updateData[$item->mid_external.'~'.$item->session_external_id] = $item;									
						}
					}
						
				} else {
						
					# продляем со всеми плюшками продления.
					if($isGraduated){							
						$this->_graduatedDebtors[$item->mid_external.'~'.$item->session_external_id] = $item;						
					
					# стандартное продление
					} else {
						$this->_updateData[$item->mid_external.'~'.$item->session_external_id] = $item;			
					}
				}	
            }
			
        }
		$this->saveToCache();
    }
    
    public function import()
    {
        $serviceDebtor = $this->getService('Debtors');
		$maxDateBebt 	= array();	#Максимальные даты продления сессии среди всех дат студентов
		try {
			if (count($this->_graduatedDebtors)) {
				$this->_subjectService = ($this->_subjectService)?($this->_subjectService):($this->getService('Subject'));
				foreach($this->_graduatedDebtors as $g){				
					$this->_subjectService->reAssignGraduatedStudent($g['graduate']->CID, $g['graduate']->MID);
					
					$logDataTest[$g['graduate']->CID][] = $g['graduate']->MID; //--данные для сброса кол-ва попыток тестов.
					
					$curDate = strtotime($g['destination']->time_ended_debtor);
					if($curDate && !isset($maxDateBebt[$g['graduate']->CID]['first'])){
						$maxDateBebt[$g['graduate']->CID]['first'] = $curDate;
					} elseif($curDate && ($maxDateBebt[$g['graduate']->CID]['first'] < $curDate)){
						$maxDateBebt[$g['graduate']->CID]['first'] = $curDate;
					}
					
					$curDate = strtotime($g['destination']->time_ended_debtor_2);
					if($curDate && !isset($maxDateBebt[$g['graduate']->CID]['second'])){
						$maxDateBebt[$g['graduate']->CID]['second'] = $curDate;
					} elseif($curDate && ($maxDateBebt[$g['graduate']->CID]['second'] < $curDate)){
						$maxDateBebt[$g['graduate']->CID]['second'] = $curDate;
					}
					
					$serviceDebtor->updateWhere(
						array(
							'time_ended_debtor' 	=> $g['destination']->time_ended_debtor,
							'time_ended_debtor_2' 	=> $g['destination']->time_ended_debtor_2,
						),
						array(
							'MID = ?' => $g['graduate']->MID,
							'CID = ?' => $g['graduate']->CID,						
						)
					);

					$this->_attemptUserIDs[$g['graduate']->MID.'~'.$g['graduate']->CID] = $g['graduate']->MID;		
				}
			}
			
			
			if (count($this->_graduatedDebtorsEnded)) {
				$this->_subjectService = ($this->_subjectService)?($this->_subjectService):($this->getService('Subject'));
				foreach($this->_graduatedDebtorsEnded as $ge){
					$this->_subjectService->reAssignGraduatedStudent($ge['graduate']->CID, $ge['graduate']->MID);										
				}
			}
		} catch (Exception $e) {
			echo $e->getMessage(), "\n";			
		}
		
        
        if (count($this->_updatesLearningsubjects)) {
           
			try {
				$logData = array();
				foreach($this->_updatesLearningsubjects as $id => $update) {				
					
					$logDataTest[$update['source']['CID']][] = $update['source']['MID']; //--данные для сброса кол-ва попыток тестов.
					
					$dataUpd = array('SID' => $update['source']['SID']);
					
					$curDate = strtotime($update['destination']->time_ended_debtor);
					if($curDate){ 
						$dataUpd['time_ended_debtor'] = $update['destination']->time_ended_debtor;
					}
					
					if($curDate && !isset($maxDateBebt[$update['source']['CID']]['first'])){
						$maxDateBebt[$update['source']['CID']]['first'] = $curDate;
					} elseif($curDate && ($maxDateBebt[$update['source']['CID']]['first'] < $curDate)){
						$maxDateBebt[$update['source']['CID']]['first'] = $curDate;
					}

					$curDate = strtotime($update['destination']->time_ended_debtor_2);
					
					if($curDate){ 
						$dataUpd['time_ended_debtor_2'] = $update['destination']->time_ended_debtor_2;
					}
					
					if($curDate && !isset($maxDateBebt[$update['source']['CID']]['second'])){
						$maxDateBebt[$update['source']['CID']]['second'] = $curDate;
					} elseif($curDate && ($maxDateBebt[$update['source']['CID']]['second'] < $curDate)){
						$maxDateBebt[$update['source']['CID']]['second'] = $curDate;
					}					
					
					$serviceDebtor->update($dataUpd);				
				}				
			} catch (Exception $e) {
				echo $e->getMessage(), "\n";
			}
		}	
		
		//--Сброс попыток.
		if(count($logDataTest) > 0){ # TODO если набран балл более 65%, то не надо добавлять		
			$serviceLesson = $this->getService('Lesson');	
			foreach($logDataTest as $sessionId => $usersId){
				$serviceLesson->resetTestCount($sessionId, $usersId);				
			}				
		}
				
		# обновление даты продления в сессии на максимальную								
		
		if(count($maxDateBebt) > 0){					
			$subjectService = $this->getService('Subject');								
			foreach($maxDateBebt as $sid => $mDate){												
				$subject = $subjectService->getById($sid);
				
				if(!$subject){ continue; }
				
				$data = array();
				if(strtotime($subject->time_ended_debt) < $mDate['first']){ 
					$data['time_ended_debt'] = date('Y-m-d', $mDate['first']);
				}
				if(strtotime($subject->time_ended_debt_2) < $mDate['second']){ 
					$data['time_ended_debt_2'] = date('Y-m-d', $mDate['second']);
				}
				if(empty($data)){ continue; }
				$data['subid'] = $sid;				
				$subjectService->update($data);//--в сессии добавляем максимальную дату продления этой сессии. Если NULL => сессия не продлена.	
			}					
		}
		
				
		//--добавляем по одной попытке на повторное прикрепление задания к уроку, если попыток уже нет
		
		if(count($this->_attemptUserIDs) > 0){
			$this->getService('Debtors')->incrementLessonAttempts($this->_attemptUserIDs);	# TODO если набран балл более 65%, то не надо добавлять									
		}	
				
		if (count($this->tutorsToDebt)) {
			$this->assignTutors();						
		}
    }    
	
	public function prepareTutors($item){
		
		if(!$this->_serviceSubject)	{	$this->_serviceSubject = $this->getService('Subject'); 	}
		if(!$this->_serviceUser)	{	$this->_serviceUser = $this->getService('User'); 		}
		if(!$this->_serviceTutor)	{	$this->_serviceTutor = $this->getService('Tutor'); 		}
		
		
		
		if(!empty($item->tutor)){		
			foreach($item->tutor as $mid_external => $name){
				if(isset($this->_tutorList[$mid_external])){
					$tutor	= $this->_tutorList[$mid_external];
				} else {
					$user = $this->_serviceUser->getOne($this->_serviceUser->fetchAll($this->_serviceUser->quoteInto('mid_external = ?', $mid_external)));	
					$tutor = array(
						'MID' => $user->MID,
						'fio' => $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,
						'blocked' => $user->blocked,
					);
					$this->_tutorList[$mid_external] = $tutor;				
				}
				
				if(!$tutor['MID']){
					$this->_tutorNotFound[$mid_external] = array(
																	'tutor_mid_external' 	=> $mid_external,																
																);
					continue;
					#222222222222222222222
				}
				
				if($tutor['blocked'] == HM_User_UserModel::STATUS_BLOCKED){
					$tutors = $item->tutor;
					unset($tutors[$mid_external]);
					$item->tutor = $tutors;
					continue;
				}
				
				#if($item->subject_external_id == '63579371602039'){
				#	var_dump($tutor);	
				#}
				
				$this->_removeLessonAssign[$tutor['MID']][$item->subject_id] = $item->subject_id;
				
				$key 	= $item->subject_id.'~'.$tutor['MID'];
				$assign	= $this->_serviceTutor->getOne($this->_serviceTutor->fetchAll($this->_serviceTutor->quoteInto(array('CID = ?', ' AND MID = ?'), array($item->subject_id, $tutor['MID']))));	
				
				# Данные для удаления роли - тьютор первого продления, кроме указанного
				$this->_removeRoleDebtFirst[$item->subject_id] = $tutor['MID'];
				#111111111111111111
				
				# нужно назначить на сессию как первое продление
				if(!$assign->TID){
					
					$this->_tutorsAssignSubjects[$key]['subject_id'] 			= $item->subject_id;
					$this->_tutorsAssignSubjects[$key]['subject_external_id'] 	= $item->subject_external_id;
					$this->_tutorsAssignSubjects[$key]['subject_name'] 			= $item->subject_name;
					$this->_tutorsAssignSubjects[$key]['tutor_id'] 				= $tutor['MID'];
					$this->_tutorsAssignSubjects[$key]['tutor_fio'] 			= $tutor['fio'];
					$this->_tutorsAssignSubjects[$key]['date_debt'] 			= $item->time_ended_debtor;
				} else {
					# Проверяем, нужно ли обновить дыт продления в назначении тьютора. Тут он становится тьютором первого или второго продления				
					#Проверяем на дату первого продления.
					if(strtotime($assign->date_debt) < strtotime($item->time_ended_debtor)){
						#  обновляем только на максимальную дату
						if(
							!isset($this->_tutorsUpdateSubjects[$key])
							||
							strtotime($this->_tutorsUpdateSubjects[$key]['date_debt']) < strtotime($item->time_ended_debtor)						
						){
							$this->_tutorsUpdateSubjects[$key]['subject_id'] 			= $item->subject_id;
							$this->_tutorsUpdateSubjects[$key]['subject_external_id'] 	= $item->subject_external_id;
							$this->_tutorsUpdateSubjects[$key]['subject_name'] 			= $item->subject_name;
							$this->_tutorsUpdateSubjects[$key]['tutor_id'] 				= $tutor['MID'];
							$this->_tutorsUpdateSubjects[$key]['tutor_mid_external']	= $mid_external;
							$this->_tutorsUpdateSubjects[$key]['tutor_fio'] 			= $tutor['fio'];
							$this->_tutorsUpdateSubjects[$key]['old_date_debt'] 		= $assign->date_debt;
							$this->_tutorsUpdateSubjects[$key]['TID']					= $assign->TID;
							$this->_tutorsUpdateSubjects[$key]['new_date_debt']			= $item->time_ended_debtor;						
						}
					}
					
					
					$availableStudents = $this->_serviceSubject->getAvailableStudents($tutor['MID'], $item->subject_id);		        
					# тьютору доступны не все студенты и этого студента нет среди доступных, необходимо назначить тьютора на группу студента
					if($availableStudents !== false && !isset($availableStudents[$item->student_id])){
						if(!empty($item->groups)){						
							foreach($item->groups as $id => $name){
								$this->_tutorsAssignGroups[$key.'~'.$id] = array(
									'subject_id' 			=> $item->subject_id,
									'subject_external_id' 	=> $item->subject_external_id,
									'subject_name' 			=> $item->subject_name,
									'tutor_id' 				=> $tutor['MID'],
									'tutor_fio' 			=> $tutor['fio'],
									'group_id' 				=> $id,
									'group_name' 			=> $name,
									'student_fio' 			=> $item->fio,								
								);
							}
						}					
					}
				}
			}
		}
		
		if(!empty($item->tutor_2)){
			foreach($item->tutor_2 as $mid_external => $name){			
				if(isset($this->_tutorList[$mid_external])){
					$tutor	= $this->_tutorList[$mid_external];
				} else {
					$user = $this->_serviceUser->getOne($this->_serviceUser->fetchAll($this->_serviceUser->quoteInto('mid_external = ?', $mid_external)));	
					$tutor = array(
						'MID' => $user->MID,
						'fio' => $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,
						'blocked' => $user->blocked,
					);
					$this->_tutorList[$mid_external] = $tutor;				
				}
				
				if(!$tutor['MID']){
					$this->_tutorNotFound[$mid_external] = array(
																	'tutor_mid_external' 	=> $mid_external,																
																);
					continue;
					#222222222222222222222
				}
				
				
				
				
				if($tutor['blocked'] == HM_User_UserModel::STATUS_BLOCKED){
					$tutors_2 = $item->tutor_2;
					unset($tutors_2[$mid_external]);
					$item->tutor_2 = $tutors_2;
					continue;
				}
				
				
				
				
				$this->_removeLessonAssign[$tutor['MID']][$item->subject_id] = $item->subject_id;
				
				# Данные для удаления роли - тьютор Второго продления, кроме указанного
				$this->_removeRoleDebtSecond[$item->subject_id] = $tutor['MID'];
				
				$assign	= $this->_serviceTutor->getOne($this->_serviceTutor->fetchAll($this->_serviceTutor->quoteInto(array('CID = ?', ' AND MID = ?'), array($item->subject_id, $tutor['MID']))));	
				$key 	= $item->subject_id.'~'.$tutor['MID'];
				
				# нужно назначить на сессию как второе продление
				# Если тьютор не назначен на сессю, значит ему априории доступны все студенты. А если он был назначен на группу, а потом удалили с сессии, а с группы нет? Исключить подобные моменты.			
				if(!$assign->TID){				
					$this->_tutorsAssignSubjects[$key]['subject_id'] 			= $item->subject_id;
					$this->_tutorsAssignSubjects[$key]['subject_external_id'] 	= $item->subject_external_id;
					$this->_tutorsAssignSubjects[$key]['subject_name'] 			= $item->subject_name;
					$this->_tutorsAssignSubjects[$key]['tutor_id'] 				= $tutor['MID'];
					$this->_tutorsAssignSubjects[$key]['tutor_fio'] 			= $tutor['fio'];
					$this->_tutorsAssignSubjects[$key]['date_debt_2'] 			= $item->time_ended_debtor_2;
				} else {
					# Проверяем, нужно ли обновить дыт продления в назначении тьютора. Тут он становится тьютором первого или второго продления
					# _tutorsUpdateSubjects
					#Проверяем на дату второго продления.
					if(strtotime($assign->date_debt_2) < strtotime($item->time_ended_debtor_2)){
						#  обновляем только на максимальную дату
						if(
							!isset($this->_tutorsUpdateSubjects[$key])
							||
							strtotime($this->_tutorsUpdateSubjects[$key]['date_debt_2']) < strtotime($item->time_ended_debtor_2)						
						){						
							$this->_tutorsUpdateSubjects[$key]['subject_id'] 			= $item->subject_id;
							$this->_tutorsUpdateSubjects[$key]['subject_external_id'] 	= $item->subject_external_id;
							$this->_tutorsUpdateSubjects[$key]['subject_name'] 			= $item->subject_name;
							$this->_tutorsUpdateSubjects[$key]['tutor_id'] 				= $tutor['MID'];
							$this->_tutorsUpdateSubjects[$key]['tutor_mid_external']	= $mid_external;
							$this->_tutorsUpdateSubjects[$key]['tutor_fio'] 			= $tutor['fio'];
							$this->_tutorsUpdateSubjects[$key]['old_date_debt_2'] 		= $assign->date_debt_2;
							$this->_tutorsUpdateSubjects[$key]['TID']					= $assign->TID;
							$this->_tutorsUpdateSubjects[$key]['new_date_debt_2'] 		= $item->time_ended_debtor_2;
						}
					}
					
					
					
					$availableStudents = $this->_serviceSubject->getAvailableStudents($tutor['MID'], $item->subject_id);		        
					# тьютору доступны не все студенты и этого студента нет среди доступных, необходимо назначить тьютора на группу студента
					if($availableStudents !== false && !isset($availableStudents[$item->student_id])){
						if(!empty($item->groups)){						
							foreach($item->groups as $id => $name){
								$this->_tutorsAssignGroups[$key.'~'.$id] = array(
									'subject_id' 			=> $item->subject_id,
									'subject_external_id' 	=> $item->subject_external_id,
									'subject_name' 			=> $item->subject_name,
									'tutor_id' 				=> $tutor['MID'],
									'tutor_fio' 			=> $tutor['fio'],
									'group_id' 				=> $id,
									'group_name' 			=> $name,								
									'student_fio' 			=> $item->fio,
								);
							}
						}					
					}
				}
			}
		}		
		#pr($this->_tutorsUpdateSubjects);		
	}

	
	
	
	
	
	public function getGroups($user_id){
		if(isset($this->_groups[$user_id])){ return $this->_groups[$user_id]; }
		if(!$this->_serviceGroup){	$this->_serviceGroup = $this->getService('StudyGroupUsers'); }
		$groups = $this->_serviceGroup->getUserGroups($user_id);
		$list = array();
		if(!empty($groups)){
			foreach($groups as $i){
				$list[$i['group_id']] = $i['name'];	
			}
		}
		$this->_groups[$user_id] = $list;
		return $this->_groups[$user_id];
	}
	
	
	public function isBallPass($user_id, $subject, $item){		
		
		if(!$this->_serviceMarkBrs){		$this->_serviceMarkBrs 		= $this->getService('MarkBrsStrategy'); }
		
		$maxBallTotalRating = $this->getService('Lesson')->getMaxBallTotalRating($subject->subid);
		
		# ДО или 
		# Если за итоговый контроль (Рубежный рейтинг) больше 20 баллов, то это старый вариант практик, для которых оценки определяется по формуле ДО
		if(
			$item->isDO 
			|| 
			(
				$item->is_practice 
				&& 
				$maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT
			)
		){
			
			$totalBall  = $this->_serviceMarkBrs->calcTotalValue($subject->subid, $user_id, true); # пересчитываем итоговую оценку, она может быть взята из 1С и не совпадать с реальным баллом по урокам.			
			$totalBall  = ($totalBall > 100) ? 100 : $totalBall ;
			
			$item->mark   		= $totalBall;
			$item->mark_needed	= 65;
			
			if($totalBall < 65){
				return false;
			}
			$item->conflict_reason_code = HM_Debtors_DebtorsModel::REASON_PASS_TO_MARK;			
		} else {
			
			
			# порог проходдения рубежного рейтинга в 65%
			if(!$this->_serviceJResult){	$this->_serviceJResult = $this->getService('LessonJournalResult'); }
			if(!$this->_serviceLesson) {	$this->_serviceLesson = $this->getService('Lesson'); }
		
			$maxBallTotalRating	= $this->_serviceLesson->getMaxBallTotalRating($subject->subid);

			# Если нет "рубежного рейтинга", то он и не сдан.
			if(empty($maxBallTotalRating)){
				return false;
			}
			
			$marks  			= $this->_serviceJResult->getRatingSeparated($subject->subid, $user_id);
			$isPassTotalRating  = $this->_serviceLesson->isPassTotalRating($maxBallTotalRating, $marks['total'], $subject->isDO, $subject->is_practice);			
			
			$item->mark_current 		= $marks['medium'];
			
			$item->mark_landmark 		= $marks['total'];
			$item->mark_landmark_max  	= $maxBallTotalRating;
			$item->mark_landmark_needed	= ($maxBallTotalRating * HM_Lesson_LessonModel::PASS_TOTAL_RATING_PERCENT);
			
			if(!$isPassTotalRating){
				return false;
			}	
			$item->conflict_reason_code = HM_Debtors_DebtorsModel::REASON_PASS_TO_TOTAL_RATING;			
		}		
		return true;
	}
	
	/**
	 * Находим сессии, на которые назначен студент, в названии которых есть имя сессии из csv файла.
	*/
	public function getSupposedSubjects($user_id, $subject_name){
		if(isset($this->_userSubjects[$user_id])){
			$user_subjects = $this->_userSubjects[$user_id];	
		} else {
			if(!$this->_serviceSubject){	$this->_serviceSubject = $this->getService('Subject'); }
			
			
			$subUsers = $this->_serviceSubject->getSelect();
			$subUsers->from(array('Students'),	array('MID','CID'));
			$subUsers->where('MID = ?', $user_id);
			
			$subGrad = $this->_serviceSubject->getSelect();
			$subGrad->from(array('graduated'),	array('MID','CID'));
			$subGrad->where('MID = ?', $user_id);
			
			$subUSelect = $this->_serviceSubject->getSelect();
			$subUSelect->union(array($subUsers, $subGrad));
			
			
			$select = $this->_serviceSubject->getSelect();
			$select->from(array('s' => 'subjects'), array(
														'id' 			=> 's.subid',
														'external_id'	=> 's.external_id',
														'name'			=> 's.name',
														'end'			=> 's.end',
													)
			);	
			$select->join(array('st' => $subUSelect), 's.subid = st.CID', array());
			$select->where('s.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
			$select->where('st.MID = ?', $user_id);
			$result = $select->query()->fetchAll();
			
			$user_subjects	= array();
			if($result){
				foreach($result as $i){
					$user_subjects[$i['id']] = $i;
				}
			} 
			$this->_userSubjects[$user_id] = $user_subjects;
		}
		
		$supposed_subjects = array();
		foreach($user_subjects as $subject_id => $i){
			if(stristr($i['name'], $subject_name) !== FALSE) {
				$i['groups'] = $this->getSubjectGroups($user_id, $subject_id);
				
				$supposed_subjects[$subject_id] = $i;
				
				
				
			}
		}		
		return $supposed_subjects;
	}
	
	
	public function getTutorList($tutors){
		
		$tutors = explode(',',$tutors);
		$tutors = array_filter($tutors);
		if(empty($tutors)){ return false; }
		$list = array();
		if(!$this->_serviceUser) {	$this->_serviceUser = $this->getService('User'); }
		foreach($tutors as $tutor_mid_external){
			if(isset($this->_tutorList[$tutor_mid_external])){
				$tutor	= $this->_tutorList[$tutor_mid_external];
			} else {
				$user = $this->_serviceUser->getOne($this->_serviceUser->fetchAll($this->_serviceUser->quoteInto('mid_external = ?', $tutor_mid_external)));	
				$tutor = array(
					'MID' => $user->MID,
					'fio' => $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,
					'blocked' => $user->blocked,
				);
				$this->_tutorList[$tutor_mid_external] = $tutor;				
			}
			
			$list[$tutor_mid_external] = $tutor['fio'];
		}
		return $list;
		
	}
	
	/**
	 * плучаем текущие оценки за сессию без пересчета по занятиям.
	*/
	public function setOldMarks($item){
		# берем данные из таблицы оценок.
		if(!$this->_serviceSubjectMark){	$this->_serviceSubjectMark 	= $this->getService('SubjectMark'); 	}		
		$old_marks = $this->_serviceSubjectMark->getOne($this->_serviceSubjectMark->fetchAll($this->_serviceSubjectMark->quoteInto(array('cid = ? ', ' AND mid = ? '), array($item->subject_id, $item->student_id))));
		$item->old_mark 			= $old_marks->mark 			? $old_marks->mark 			: 0;
		$item->old_mark_current 	= $old_marks->mark_current 	? $old_marks->mark_current 	: 0; # Итоговый текущий рейтинг
		$item->old_mark_landmark 	= $old_marks->mark_landmark ? $old_marks->mark_landmark : 0; # Рубежный рейтинг (экзамен)		
	}
	
	public function setIsDO($item){
		if(!$this->_serviceSubject){ $this->_serviceSubject = $this->getService('Subject'); }
		if($this->_serviceSubject->isDOT($item->subject_id)){
			$item->isDO 	  	= true;
		} else {
			$item->isDO 	  	= false;
		}
	}
	
	
	/**
	 * Получаем список групп сессии как пересечение м-у группами студента и сессии. Если общих групп нет, берем все группы студента.
	 * TODO
	*/
	public function getSubjectGroups($user_id, $subject_id){
		
		if(!isset($this->_groups[$user_id])){
			if(!$this->_serviceGroup){	$this->_serviceGroup = $this->getService('StudyGroupUsers'); }
			$groups = $this->_serviceGroup->getUserGroups($user_id);
			$list 	= array();
			if(!empty($groups)){
				foreach($groups as $i){
					$list[$i['group_id']] = $i['name'];	
				}
			}
			$this->_groups[$user_id] = $list;
		}
		$userGroups = $this->_groups[$user_id];
		
		
		if(!isset($this->_groupsSubject[$subject_id])){
			if(!$this->_serviceSubject){	$this->_serviceSubject = $this->getService('Subject'); }
			$select = $this->_serviceSubject->getSelect();
			
			$select->from(array('pe' => 'programm_events'), array(
				'group_id' 	=> 'sg.group_id',				
				'name' 		=> 'sg.name',				
			));			
			$select->where($this->_serviceSubject->quoteInto('pe.item_id = ? ', $subject_id));	
			
			$select->join(array('sgp' => 'study_groups_programms'), 'sgp.programm_id = pe.programm_id', array());
			$select->join(array('sg'  => 'study_groups'), 			'sg.group_id = sgp.group_id', 		array());
			$result 		= $select->query()->fetchAll();
			$list 	= array();
			if(!empty($result)){
				foreach($result as $i){
					$list[$i['group_id']] = $i['name'];					
				}				
			}
			$this->_groupsSubject[$subject_id] = $list;			
		}
		$subjectGroups 	= $this->_groupsSubject[$subject_id];		
		$group_common 	= array_intersect_key ($userGroups, $subjectGroups);
		if(empty($group_common)){
			$group_common = $userGroups;
		}
		return $group_common;
	}
	
	
	/**
	 * Базовое продление: обноление дат продления + сброс попыток в тесте + добавляется попытка на прикрепление решения в задании если попыток уже нет.
	*/
	public function importBase(){
		if(empty($this->_updateData)){
			return 0;
		}
		
		$totalImport = 0;
		
		# Максимальные даты продления в timestamp
		$this->max_time_ended_debt		= array();
		$this->max_time_ended_debt_2	= array();
		
		# Старые даты продления в timestamp
		$this->old_time_ended_debt		= array();
		$this->old_time_ended_debt_2   	= array();
		
		foreach($this->_updateData as $i){
			# базовое продление
			if($this->baseExtensionDebt($i)){ 
				$totalImport++;
			}
		}
		
		# обновление максимальной даты сессий
		$this->updateSubjectDates();
		
		return $totalImport;		
	}
	
	
	/**
	 * продление завершивших обучение: Назначение студента на сессию + Базовое продление
	*/
	public function importGraduated(){
		if(empty($this->_graduatedDebtors)){
			return 0;
		}
		
		$totalImport = 0;
		
		# Максимальные даты продления в timestamp
		$this->max_time_ended_debt		= array();
		$this->max_time_ended_debt_2	= array();
		
		# Старые даты продления в timestamp
		$this->old_time_ended_debt		= array();
		$this->old_time_ended_debt_2   	= array();
		
		if(!$this->_serviceSubject){	$this->_serviceSubject = $this->getService('Subject'); }
		
		foreach($this->_graduatedDebtors as $i){
			# назначаем на сессию. Нет контроля - назначился ли или нет.
			$this->_serviceSubject->reAssignGraduatedStudent($i->subject_id, $i->student_id);
			
			# не назначился по какой-то причине.
			if(!$this->_serviceSubject->isStudent($i->subject_id, $i->student_id)){
				continue;
			}
			
			
			# базовое продление
			if($this->baseExtensionDebt($i)){ 
				$totalImport++;
			}
		}
		
		# обновление максимальной даты сессий
		$this->updateSubjectDates();
		
		return $totalImport;	
	}
	
	/**
	 * Продление студента, который набрал проходной балл: только назначаем на сессию, не продляем даты, не сбрасываем попытки.
	*/
	public function importGraduatedPass(){
		
		if(empty($this->_graduatedDebtorsEnded)){
			return 0;
		}
		
		$totalImport = 0;
		if(!$this->_serviceSubject){	$this->_serviceSubject = $this->getService('Subject'); }
		
		foreach($this->_graduatedDebtorsEnded as $i){
			# назначаем на сессию. Нет контроля - назначился ли или нет.
			$this->_serviceSubject->reAssignGraduatedStudent($i->subject_id, $i->student_id);
			
			# не назначился по какой-то причине.
			if(!$this->_serviceSubject->isStudent($i->subject_id, $i->student_id)){
				continue;
			}			
			$totalImport++;			
		}
		return $totalImport;	
	}
	
	
	/**
	 * Базовое продление долга
	*/
	public function baseExtensionDebt($i){
		
		if(!$i->student_id){ return false; }
		if(!$i->subject_id){ return false; }
		
		if(!$this->_serviceDebtors) {	$this->_serviceDebtors = $this->getService('Debtors');  }
		if(!$this->_serviceLesson)  {	$this->_serviceLesson = $this->getService('Lesson'); 	}
		
		if(!isset($this->old_time_ended_debt[$i->subject_id]))		{	$this->old_time_ended_debt[$i->subject_id]   = strtotime($i->subject_time_ended_debt);		}
		if(!isset($this->old_time_ended_debt_2[$i->subject_id]))	{	$this->old_time_ended_debt_2[$i->subject_id] = strtotime($i->subject_time_ended_debt_2);	}
		
		if(!empty($i->time_ended_debtor)){				
			$data['time_ended_debtor'] = $i->time_ended_debtor;		
			if( $this->max_time_ended_debt[$i->subject_id] < strtotime($i->time_ended_debtor) ){
				$this->max_time_ended_debt[$i->subject_id] = strtotime($i->time_ended_debtor);
			}
		}
		if(!empty($i->time_ended_debtor_2)){	
			$data['time_ended_debtor_2']   = $i->time_ended_debtor_2;
			if( $this->max_time_ended_debt_2[$i->subject_id] < strtotime($i->time_ended_debtor_2) ){
				$this->max_time_ended_debt_2[$i->subject_id] = strtotime($i->time_ended_debtor_2);
			}
		}
		
		if(empty($data)){ return false; }
		
		$criteria = array(
							'MID = ?' => $i->student_id,
							'CID = ?' => $i->subject_id,						
						);							
		
		$isUpdate = $this->_serviceDebtors->updateWhere($data, $criteria);
		
		if(!$isUpdate){	
			return false;
		}
		
		# добавить попытку для прикрепления работы в каждом задании урока указанной сессии.
		$this->_serviceDebtors->addLessonAttemptUser($i->subject_id, $i->student_id); 		
		$this->_serviceLesson->resetTestCountOnUser($i->subject_id, $i->student_id);
		
		return true;		
	}
	
	
	/**
	 * Обновление дат продления в сессии на максимальную, сдери указанных в продлении студентов
	**/
	public function updateSubjectDates(){
		if(!$this->_serviceSubject){	$this->_serviceSubject = $this->getService('Subject'); }
		if(empty($this->old_time_ended_debt )){ return false; }
		
		foreach($this->old_time_ended_debt as $subject_id => $old_time_ended_debt_timestamp){
			if(isset($this->max_time_ended_debt[$subject_id]) && $old_time_ended_debt_timestamp < $this->max_time_ended_debt[$subject_id]){ 
				$subject_data['time_ended_debt'] = date('Y-m-d', $this->max_time_ended_debt[$subject_id]);
			}
			
			if(isset($this->max_time_ended_debt_2[$subject_id]) && $this->old_time_ended_debt_2[$subject_id] < $this->max_time_ended_debt_2[$subject_id]){ 
				$subject_data['time_ended_debt_2'] = date('Y-m-d', $this->max_time_ended_debt_2[$subject_id]);
			}			
			if(empty($subject_data)){ continue; }
			$subject_data['subid'] = $subject_id;			
			$this->_serviceSubject->update($subject_data);			
		}	
		return true;		
	}
	
	
	
	public function  importAssignTutorSubject(){
		if(empty($this->_tutorsAssignSubjects )){ return 0; }
		if(!$this->_serviceTutor){	$this->_serviceTutor = $this->getService('Tutor'); }
		
		$totalImport = 0;
		foreach($this->_tutorsAssignSubjects as $i){
			if($this->assignTutors($i['tutor_id'], $i['subject_id'], $i['date_debt'], $i['date_debt_2'])){
				$totalImport++;
			}
		}		
		return $totalImport;
	}
	
	/**
	 * Изменение назначений тьютора: обновление дат продления
	*/
	public function  importUpdateTutorSubject(){
		if(empty($this->_tutorsUpdateSubjects )){ return 0; }
		if(!$this->_serviceTutor){	$this->_serviceTutor = $this->getService('Tutor'); }
		
		$totalImport = 0;
		foreach($this->_tutorsUpdateSubjects as $i){
			$data = array();
			
			if(!empty($i['new_date_debt'])){
				$data['date_debt'] = $i['new_date_debt'];
			}
			
			if(!empty($i['new_date_debt_2'])){
				$data['date_debt_2'] = $i['new_date_debt_2'];
			}
			if(empty($data)){ continue; } 
			
			if(	$this->_serviceTutor->updateWhere( $data, array('TID = ? ' => $i['TID']) )	){
				$totalImport++;
			}			
		}		
		return $totalImport;
	}
	
	
	
	public function assignTutors($tutor_id, $subject_id, $date_debt, $date_debt_2){				
		$assign_id	= $this->_serviceTutor->getOne($this->_serviceTutor->fetchAll($this->_serviceTutor->quoteInto(array('CID = ?', ' AND MID = ?'), array($subject_id, $tutor_id))))->TID;	
		$data 		= array(
							'date_debt' 	=> $date_debt,
							'date_debt_2' 	=> $date_debt_2,		
						   );
		
		if($assign_id){
			return $this->_serviceTutor->updateWhere( $data, array('TID = ? ' => $assign_id) );						
		}
		
		$data['date_assign'] = new Zend_Db_Expr('GETDATE()');	
		$data['CID'] 		 = $subject_id;
		$data['MID'] 		 = $tutor_id;
		return $this->_serviceTutor->insert($data);
									
	}
	
	public function  importAssignTutorGroup(){		
		if(empty($this->_tutorsAssignGroups )){ return 0; }
		if(!$this->_serviceTutorGroup){	$this->_serviceTutorGroup = $this->getService('SubjectGroup'); }
		
		$totalImport = 0;
		foreach($this->_tutorsAssignGroups as $i){
			if($this->_serviceTutorGroup->assignTutorToGroup($i['tutor_id'], $i['subject_id'], $i['group_id'])){
				$totalImport++;
			}
		}
		return $totalImport;		
	}
	
	/**
	 * Преобразуем роль "тьютор первого продления" и "тьютор второго продления" в обычную роль "тьютор" путем удаления даты продления	 
	*/
	public function setBaseRoleTutors(){
		if(!$this->_serviceTutor)	{	$this->_serviceTutor = $this->getService('Tutor'); 		}
		if(!empty($this->_removeRoleDebtFirst)){
			$fields = array();
			$values = array();
			$count  = 0;
			foreach($this->_removeRoleDebtFirst as $subject_id => $tutor_id){
				$count++;
				$or 	  = ($count < count($this->_removeRoleDebtFirst)) ? ' OR ' : '';
				$fields[] = ' (CID = ?';
				$fields[] = ' AND MID != ?) '.$or;
				$values[] = $subject_id;
				$values[] = $tutor_id;
			}
			$criteria = $this->_serviceTutor->quoteInto($fields, $values);
			$isUpdate = $this->_serviceTutor->updateWhere(array('date_debt' => NULL), $criteria);				
		}
		
		if(!empty($this->_removeRoleDebtSecond)){
			$fields = array();
			$values = array();
			$count  = 0;
			foreach($this->_removeRoleDebtSecond as $subject_id => $tutor_id){
				$count++;
				$or 	  = ($count < count($this->_removeRoleDebtSecond)) ? ' OR ' : '';
				$fields[] = ' (CID = ?';
				$fields[] = ' AND MID != ?) '.$or;
				$values[] = $subject_id;
				$values[] = $tutor_id;
			}
			$criteria = $this->_serviceTutor->quoteInto($fields, $values);
			$isUpdate = $this->_serviceTutor->updateWhere(array('date_debt_2' => NULL), $criteria);
		}		
		return;
	}
	
	/**
	 * Удаляем все 
	*/
	public function removeLessonAssignTutors(){
		if(empty($this->_removeLessonAssign)){ return false; }
		if(!$this->_serviceTutorL)	{	$this->_serviceTutorL = $this->getService('LessonAssignTutor'); 		}
		$fields = array();
		$values = array();
		$count  = 0;
		foreach($this->_removeLessonAssign as $tutor_id => $subjects){
			$subject_ids = array_map('intval', $subjects);			
			$subject_ids = array_filter($subject_ids);
			if(empty($subject_ids)){ continue; }
			
			$count++;
			$or 	  = ($count < count($this->_removeLessonAssign)) ? ' OR ' : '';
			$fields[] = ' ( MID = ?';
			$fields[] = ' AND CID IN (?) ) '.$or;
			$values[] = $tutor_id;
			$values[] = $subject_ids;
		}
		$criteria 	= $this->_serviceTutorL->quoteInto($fields, $values);
		$isDel 		= $this->_serviceTutorL->deleteBy($criteria);
		if(!$isDel){ return false; }
		return true;
	}
	
	# Опасность! Мега костыль! Кто мешал сделать один метод на получение доступных тьюторов и студентов?
	public function joinTutors($item)
	{
		$subjectId = (int)$item->subject_id;
		if(empty($subjectId) && !empty($item->session_external_id)){
			$subject   = $this->getService('Subject')->getByCode($item->session_external_id);
			$subjectId = (int)$subject->subid;
		}
		if(empty($subjectId)){
			return false;
		}
		$tutors       = $this->getService('User')->fetchAllJoinInner('Tutor', $this->getService('User')->quoteInto(array('Tutor.CID = ?'), array($subjectId)));
		if(empty($tutors)){
			return false;
		}		
		$item->tutors = $this->getService('Subject')->filterAvailableTutors($tutors, $item->student_id, $subjectId);
	}
		
}