<?php
class HM_Subject_Import_Manager
{
    protected $_existingSessions    = array();
    protected $_existingSessionsIds = array();

    protected $_insertsSessions  = array();
    protected $_updatesSessions  = array();
    protected $_notFoundSessions = array();
    protected $_notFoundProgrammSessions = array();
    protected $_notFoundTutorSessions = array(); //--сессии, у которых указан тьютор, но его нет в БД
    protected $_tutorLinkGroups 	  = array(); //--записи для назначения тьютора на сессию и группы
    protected $_notFoundGroups 	  	= array(); //--записи сессий, в которых указана группа, но ее нет в системе.
    protected $_updateAssign 	  	= array(); //--существующие сессии, в которых нужно будет создать новую связь: назначение тьютора на сессию или группу.
	
	# assign tutor on lesson
	protected $_tutorLecture 	= array();
	protected $_tutorPractice 	= array();
	protected $_tutorLab 		= array();
	protected $_matchingSubjectIDs = array(); # subject external_id to primary key 
	protected $_matchingUserGUIDs = array();  # people external_id  to primary key 
	protected $_notHours			= array();  # неДО Записи, в котрых не указаны часы
	protected $_notTaskLessons		= array();  # неДО Записи, в базовом курсе которых нет нет ни одного занятия с фразой «Задание»
	
	protected $_notFoundLearningsubjects = array(); # не найден учебный предмет
	protected $_notLinkLearningsubjects  = array(); # учебный предмет не связан с базовым курсом
	
    const CACHE_NAME = 'HM_Sessions_Import_Manager';
    
    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init($items = array())
    {
				
		$this->_subjService = $this->getService('Subject');
		
		$select = $this->_subjService->getSelect();
		$select->from(
			array(
				'subj' => 'subjects'
			),
			array(				
				'subid' => 'subj.subid',
				'external_id' => 'subj.external_id',
				'Teacher_MID' => 'pe.MID',
				'Teacher_mid_external' => 'pe.mid_external',
				
				'code' => 'subj.code',
				'name' => 'subj.name',
				'shortname' => 'subj.shortname',
				'supplier_id' => 'subj.supplier_id',
				'description' => 'subj.description',
				'type' => 'subj.type',
				'reg_type' => 'subj.reg_type',
				'begin_planned' => 'subj.begin_planned',
				'end_planned' => 'subj.end_planned',
				'begin' => 'subj.begin',
				'end' => 'subj.end',
				'longtime' => 'subj.longtime',
				'price' => 'subj.price',
				'price_currency' => 'subj.price_currency',
				'plan_users' => 'subj.plan_users',
				'services' => 'subj.services',
				'period' => 'subj.period',
				'period_restriction_type' => 'subj.period_restriction_type',
				'last_updated' => 'subj.last_updated',
				'access_mode' => 'subj.access_mode',
				'access_elements' => 'subj.access_elements',
				'mode_free_limit' => 'subj.mode_free_limit',
				'auto_done' => 'subj.auto_done',
				'base' => 'subj.base',
				'base_id' => 'subj.base_id',
				'base_color' => 'subj.base_color',
				'claimant_process_id' => 'subj.claimant_process_id',
				'state' => 'subj.state',
				'default_uri' => 'subj.default_uri',
				'scale_id' => 'subj.scale_id',
				'auto_mark' => 'subj.auto_mark',
				'auto_graduate' => 'subj.auto_graduate',
				'formula_id' => 'subj.formula_id',
				'threshold' => 'subj.threshold',
				'mark_type' => 'subj.mark_type',
				'chair' => 'subj.chair',
				'exam_type' => 'subj.exam_type',
				'learn' => 'subj.learn',
				'hours_total' => 'subj.hours_total',
				'classroom' => 'subj.classroom',
				'self_study' => 'subj.self_study',
				'lection' => 'subj.lection',
				'lab' => 'subj.lab',
				'practice' => 'subj.practice',
				'exam' => 'subj.exam',
				'year_of_publishing' => 'subj.year_of_publishing',
				'zet' => 'subj.zet',
				'learning_subject_id_external' => 'subj.learning_subject_id_external',
				'isDO' 				=> 'subj.isDO',
				//'time_ended_debt' => 'subj.time_ended_debt',	
				'begin_learning' 	=> 'subj.begin_learning',
				'language_code' 	=> 'subj.language_code',
				'module_code' 		=> 'subj.module_code',				
				'semester' 			=> 'subj.semester',				
				'faculty' 			=> 'subj.faculty',				
				'is_practice' 		=> 'subj.is_practice',				
				'module_name' 		=> 'subj.module_name',				
				'practice_begin' 	=> 'subj.practice_begin',				
				'practice_end' 		=> 'subj.practice_end',				
			)				
		);
		
		$select->joinLeft(
			//array('th' => 'Teachers'),
			array('th' => 'Tutors'),
			'th.CID=subj.subid',
			array()
		);
		
		$select->joinLeft(
			array('pe' => 'People'),
			'pe.MID=th.MID',
			array()
		);			
		
		$select->where(
			$this->_subjService->quoteInto(
				"subj.base = ?",
				HM_Subject_SubjectModel::BASETYPE_SESSION
			)
		);
		$select->where('subj.external_id IS NOT NULL');
		//$select->where('pe.mid_external IS NOT NULL');
		
		
		
		$newExternalIds = array();
		if(!empty($items)){
			foreach($items as $item){
				$externalId = trim($item->external_id);
				if(empty($externalId)){ continue; }
				$newExternalIds[$externalId] = $externalId;
			}
		}
		if(empty($newExternalIds)){				
			$select->where('1=0');
		} else {
			$select->where($this->_subjService->quoteInto('subj.external_id IN (?)', $newExternalIds));
		}		
		
		$sessions = $select->query()->fetchAll();
		
		/*
		$sessions = $this->getService('Subject')->fetchAll(array(
            'base = ?' => HM_Subject_SubjectModel::BASETYPE_SESSION,
            ' external_id IS NOT NULL'
        ));
		*/	
		
		
		
		
		
		
		
		if (count($sessions)) {
            foreach($sessions as $session) { 
				//echo '<pre>';
				//var_dump(isset($this->_existingSessions[$session['subid']]));
				//echo '</pre>';
                if(isset($this->_existingSessions[$session['subid']])){ //--сессия повторилась
					if(!empty($session['Teacher_mid_external'])){ //--преподов без внешнего id не учитываем.
						$this->_existingSessions[$session['subid']]['Teachers'][$session['Teacher_MID']] = $session['Teacher_mid_external'];
					}					
				} else {
					$session['Teachers'][$session['Teacher_MID']] = $session['Teacher_mid_external'];
					$this->_existingSessions[$session['subid']] = $session;
				}
                if (strlen($session['external_id'])) {
                    $session['external_id'] = trim($session['external_id']);
                    $this->_existingSessionsIds[$session['external_id']] = $session['subid'];
                }				
            }
        }
				
		
		/*
        if (count($sessions)) {
            foreach($sessions as $session) { 				
                $this->_existingSessions[$session->subid] = $session;
                if (strlen($session->external_id)) {
                    $session->external_id = trim($session->external_id);
                    $this->_existingSessionsIds[$session->external_id] = $session->subid;
                }
            }
        }
		*/
    }

    protected function _needSessionUpdate($session)
    {
		$existingSession = $this->_existingSessions[$this->_existingSessionsIds[$session->external_id]];
		
		//--fix for cron
		if(method_exists($session, 'getValues')) {								
			$values = $session->getValues(null, array(
				'external_id', 'programm_id_external',
				'teacher_id_external',
				'group_external_id',
				//'learning_subject_id_external'
			));		
		} else {					
			$values = $this->getValues($session, null, array('external_id', 'programm_id_external', 'teacher_id_external', 'group_external_id'));
		}
		
        if (count($values)) {
			$userService = $this->getService('User');
            foreach($values as $key => $value) {
                if(in_array($key, array('begin', 'end', 'begin_learning'))){
					$date = strtotime($existingSession[$key]);
                    $existingSession[$key] = date('Y-m-d', $date);
					
					if($existingSession[$key] == '1970-01-01' && empty($value)){
						return false;
					}
                }
				
				if(in_array($key, array('practice_begin', 'practice_end'))){
					$date 					= strtotime($existingSession[$key]);
					if($date <= 0 && empty($value)){ continue; }
					
					$existingSession[$key]	= date('Y-m-d', $date);
					$value					= date('Y-m-d', strtotime($value));
                }
				
				/*
				elseif($key == 'teacher_id_external'){
					if( //--Если в csv указан внешний id препода, и его нет на этой сессии, то обновляем сессию
						isset($existingSession['Teachers']) &&
						is_array($existingSession['Teachers']) &&
						count($existingSession['Teachers']) > 0 &&
						!empty($value) &&
						!in_array($value, $existingSession['Teachers'])
					){ //--Надо обновить препода						
						$user = $userService->fetchAll(array('mid_external = ?' => $value))->current();//--Ищем в БД пользователя с таким id.
						if($user->MID){
							return true;	
						} 
						return false;							
					}					
				}
				*/
				                
				if(isset($existingSession[$key])){									
					if ($existingSession[$key] != $value) {
						return true;
					}
				}				
            }
        }

        return false;
    }
	
	/**
	 * нужно ли создавать новое назначение тьютора при обновлении сессии
	 * @return bool
	*/
	protected function _needAssignTutor($subject_id, $tutor_id, $group_id = false){
		if(!$subject_id || !$tutor_id){ return false; }
		
		# назначение на сессию целиком
		$tutorService = $this->getService('Tutor');
		$isAssign = $tutorService->getOne($tutorService->fetchAll($tutorService->quoteInto(array('CID = ?', ' AND MID = ?'), array($subject_id, $tutor_id))));
		if(!$isAssign){ return true; } # если не назначен на сессию, то не важно, назначен ли на группу.		
		
		if(!$group_id){
			if($isAssign){ return false; }
			return true;			
		}
		
		# назначен на указанную группу.
		$tutorGroupService = $this->getService('SubjectGroup');
		$isAssignGroup = $tutorGroupService->getOne($tutorGroupService->fetchAll($tutorGroupService->quoteInto(array('CID = ?', ' AND MID = ?', ' AND GID = ? '), array($subject_id, $tutor_id, $group_id))));
		
		if($isAssignGroup){
			return false;
		}
		return true;		
	}

    public function getInsertsCount()
    {
        return count($this->_insertsSessions);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesSessions);
    }

    public function getNotFoundCount()
    {
        return count($this->_notFoundSessions);
    }
	
	public function getNotFoundProgrammCount()
    {
        return count($this->_notFoundProgrammSessions);
    }
    
    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount() + $this->getNotFoundCount() + $this->getNotFoundProgrammCount();
    }

    public function getInserts()
    {
        return $this->_insertsSessions;
    }

    public function getUpdates()
    {
        return $this->_updatesSessions;
    }
    
    public function getNotFound()
    {
        return $this->_notFoundSessions;
    }
	
	public function getNotFoundProgramm()
    {
        return $this->_notFoundProgrammSessions;
    }
	
	public function getNotFoundTutorSessions()
    {
        return $this->_notFoundTutorSessions;
    }
	
	public function getNotFoundTutorSessionsCount()
    {
        return count($this->_notFoundTutorSessions);
    }
	
	public function getNotFoundGroupSessions()
    {
        return $this->_notFoundGroups;
    }
	
	public function getNotFoundGroupSessionsCount()
    {
        return count($this->_notFoundGroups);
    }
	
	public function getUpdateAssignSessions()
    {
        return $this->_updateAssign;
    }
	
	public function getUpdateAssignSessionsCount()
    {
        return count($this->_updateAssign);
    }
	
	
	public function getTutorLectureCount(){		
		return count($this->_tutorLecture);
	}
	
	public function getTutorLabCount(){		
		return count($this->_tutorLab);
	}
	
	public function getTutorPracticeCount(){		
		return count($this->_tutorPractice);
	}
	
	public function getNotHoursCount(){		
		return count($this->_notHours);
	}
	
	public function getNotHours(){		
		return $this->_notHours;
	}
	
	public function getNotTaskLessonsCount(){		
		return count($this->_notTaskLessons);
	}
	
	public function getNotTaskLessons(){		
		return $this->_notTaskLessons;
	}
	
	public function getNotFoundLearningsubjects()		{ return $this->_notFoundLearningsubjects; }
	public function getNotFoundLearningsubjectsCount()	{ return count($this->_notFoundLearningsubjects); }
	
	public function getNotLinkLearningsubjects()		{ return $this->_notLinkLearningsubjects; }
	public function getNotLinkLearningsubjectsCount()	{ return count($this->_notLinkLearningsubjects); }
	

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts'  			=> $this->_insertsSessions,
                 'updates'  			=> $this->_updatesSessions,
                 'notFound' 			=> $this->_notFoundSessions,
                 'notFoundProgramm' 	=> $this->_notFoundProgrammSessions,
                 'notFoundTutorSessions'=> $this->_notFoundTutorSessions,
                 'tutorLinkGroups'	    => $this->_tutorLinkGroups,
                 'notFoundGroups'	    => $this->_notFoundGroups,
                 'updateAssign'	    	=> $this->_updateAssign,
                 
				 'tutorLecture'	    	=> $this->_tutorLecture,
				 'tutorPractice'	    => $this->_tutorPractice,
				 'tutorLab'	    		=> $this->_tutorLab,				 
				 'notHours'	    		=> $this->_notHours,				 
				 'notFoundLearningsubjects' => $this->_notFoundLearningsubjects,				 
				 'notLinkLearningsubjects' 	=> $this->_notLinkLearningsubjects,				 
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
            $this->_insertsSessions   			= $actions['inserts'];
            $this->_updatesSessions   			= $actions['updates'];
            $this->_notFoundSessions  			= $actions['notFound'];
            $this->_notFoundProgrammSessions  	= $actions['notFoundProgramm'];
            $this->_notFoundTutorSessions  		= $actions['notFoundTutorSessions'];
            $this->_tutorLinkGroups  			= $actions['tutorLinkGroups'];
            $this->_notFoundGroups  			= $actions['notFoundGroups'];
            $this->_updateAssign  				= $actions['updateAssign'];
            
			$this->_tutorLecture  				= $actions['tutorLecture'];
			$this->_tutorPractice  				= $actions['tutorPractice'];
			$this->_tutorLab  					= $actions['tutorLab'];
			$this->_notHours  					= $actions['notHours'];
			$this->_notFoundLearningsubjects  	= $actions['notFoundLearningsubjects'];
			$this->_notLinkLearningsubjects  	= $actions['notLinkLearningsubjects'];
            $this->_restoredFromCache 			= true;
            return true;
        }

        return false;
    }

    public function init($items)
    {
        $this->_init($items);
		
        if ($this->_restoredFromCache) {
            return true;
        }
		
		
        if (count($items)) {
			$serviceUser = $this->getService('User');
			$groupList = $this->getService('StudyGroup')->fetchAll('id_external IS NOT NULL')->getList('id_external', 'group_id');
			
            foreach($items as $item) {
				$learning_subject = $this->getService('Learningsubjects')->getByCode($item->learning_subject_id_external);
				
				# нет учебного предмета
				if(!$learning_subject->learning_subject_id){
					$item->notFoundLearningsubject 					= true;
					#$this->_notFoundSessions[$item->external_id] 	= $item;
					$this->_notFoundLearningsubjects[$item->external_id] 	= $item;
					continue;
				}
				
				# нет связи с базовым курсом.
				if(empty($learning_subject->subject_id)){
					$item->notAssignBaseSubject 					= true;
					$item->learning_subject_comment					= $learning_subject->comment;
					#$this->_notFoundSessions[$item->external_id] 	= $item;
					$this->_notLinkLearningsubjects[$item->external_id] 	= $item;
					continue;
				} else {
					$item->base 	= HM_Subject_SubjectModel::BASETYPE_SESSION;
					$item->base_id 	= $learning_subject->subject_id;
				}
				
				# не ДО, не практика, не элективный курс по физ.культуре
				if(	empty($item->isDO) ){
					if(	
						empty($item->is_practice) 
						&& 
						mb_stripos ($item->name, 'Элективные курсы по физической культуре и спорту') === false
					){
						if( empty($item->lection) && 	empty($item->lab) && 	empty($item->practice)	){					
							$this->_notHours[$item->external_id] = $item;
							#continue;
						}
					}
					
					if(!$this->setBaseId($item)){
						$this->_notFoundSessions[$item->external_id] = $item;
						continue;
					}
					
					/* ################ СУ00-2848 ################*/
					/*
					if($this->isNotFoundTaskLesson($item)){
						$this->_notTaskLessons[$item->external_id] = $item;
						continue;
					}
					*/
				}
				
				$this->prepareTutorLecture($item->tutor_lector, $item->external_id);
				$this->prepareTutorPractice($item->tutor_practic, $item->external_id);
				$this->prepareTutorLab($item->tutor_laboratory, $item->external_id);
		

				$teacher = false;
				if(!empty($item->teacher_id_external)){
					$teacher = $serviceUser->getOne($serviceUser->fetchAll($serviceUser->quoteInto('mid_external = ?', $item->teacher_id_external)));					
					if(!$teacher->MID){						
						$item->not_found_guid 		= $item->teacher_id_external;
						$item->not_found_guid_type 	= 'тьютор';
						$this->_notFoundTutorSessions[$item->external_id] = $item;						
						continue;
					}
					# проверить наличие группы. Если ее нет, то не назначаем
					if(empty($item->group_external_id)){
						$this->_tutorLinkGroups[$item->external_id][$teacher->MID] = false; # назначение на всю группу
					} elseif(isset($groupList[$item->group_external_id])){			
						$this->_tutorLinkGroups[$item->external_id][$teacher->MID][$groupList[$item->group_external_id]] = $groupList[$item->group_external_id];
					} else {												
						$fio = $teacher->LastName.' '.$teacher->FirstName.' '.$teacher->Patronymic;
						$this->_notFoundGroups[$item->external_id.'~'.$teacher->MID.'~'.$item->group_external_id] = array('source' => $item, 'additional' => array('fio' => $fio));
					}
				}
				
				
				#if($this->getService('User')->getCurrentUserId() == 5829){
					$teacher = false;
					if(!empty($item->tutor_lector)){
						$teacher = $serviceUser->getOne($serviceUser->fetchAll($serviceUser->quoteInto('mid_external = ?', $item->tutor_lector)));					
						if(!$teacher->MID){
							$item->not_found_guid      = $item->tutor_lector;
							$item->not_found_guid_type = 'лектор';
							$this->_notFoundTutorSessions[$item->external_id] = $item;						
							continue;
						}
						# проверить наличие группы. Если ее нет, то не назначаем
						if(empty($item->group_external_id)){
							$this->_tutorLinkGroups[$item->external_id][$teacher->MID] = false; # назначение на всю группу
						} elseif(isset($groupList[$item->group_external_id])){			
							$this->_tutorLinkGroups[$item->external_id][$teacher->MID][$groupList[$item->group_external_id]] = $groupList[$item->group_external_id];
						} else {												
							$fio = $teacher->LastName.' '.$teacher->FirstName.' '.$teacher->Patronymic;
							$this->_notFoundGroups[$item->external_id.'~'.$teacher->MID.'~'.$item->group_external_id] = array('source' => $item, 'additional' => array('fio' => $fio));
						}
					}	

					$teacher = false;
					if(!empty($item->tutor_practic)){
						$teacher = $serviceUser->getOne($serviceUser->fetchAll($serviceUser->quoteInto('mid_external = ?', $item->tutor_practic)));					
						if(!$teacher->MID){
							$item->not_found_guid 		= $item->tutor_practic;
							$item->not_found_guid_type 	= 'семинарист';
							$this->_notFoundTutorSessions[$item->external_id] = $item;						
							continue;
						}
						# проверить наличие группы. Если ее нет, то не назначаем
						if(empty($item->group_external_id)){
							$this->_tutorLinkGroups[$item->external_id][$teacher->MID] = false; # назначение на всю группу
						} elseif(isset($groupList[$item->group_external_id])){			
							$this->_tutorLinkGroups[$item->external_id][$teacher->MID][$groupList[$item->group_external_id]] = $groupList[$item->group_external_id];
						} else {												
							$fio = $teacher->LastName.' '.$teacher->FirstName.' '.$teacher->Patronymic;
							$this->_notFoundGroups[$item->external_id.'~'.$teacher->MID.'~'.$item->group_external_id] = array('source' => $item, 'additional' => array('fio' => $fio));
						}
					}	
					
					$teacher = false;
					if(!empty($item->tutor_laboratory)){
						$teacher = $serviceUser->getOne($serviceUser->fetchAll($serviceUser->quoteInto('mid_external = ?', $item->tutor_laboratory)));					
						if(!$teacher->MID){							
							$item->not_found_guid 		= $item->tutor_laboratory;
							$item->not_found_guid_type 	= 'лаборант';
							$this->_notFoundTutorSessions[$item->external_id] = $item;						
							continue;
						}
						# проверить наличие группы. Если ее нет, то не назначаем
						if(empty($item->group_external_id)){
							$this->_tutorLinkGroups[$item->external_id][$teacher->MID] = false; # назначение на всю группу
						} elseif(isset($groupList[$item->group_external_id])){			
							$this->_tutorLinkGroups[$item->external_id][$teacher->MID][$groupList[$item->group_external_id]] = $groupList[$item->group_external_id];
						} else {												
							$fio = $teacher->LastName.' '.$teacher->FirstName.' '.$teacher->Patronymic;
							$this->_notFoundGroups[$item->external_id.'~'.$teacher->MID.'~'.$item->group_external_id] = array('source' => $item, 'additional' => array('fio' => $fio));
						}
					}	
					
				#}
				
				
				
				if (!isset($this->_existingSessionsIds[$item->external_id])) {
					if($this->setBaseId(&$item)){ # есть предмет и связанный с ним курс.												
						if($this->isProgrammExist($item)){ //--программа							
							if(!isset($this->_insertsSessions[$item->external_id])){
								$this->_insertsSessions[$item->external_id] = $item;
							}
						} else {							
							$this->_notFoundProgrammSessions[$item->external_id] = $item;
						}	
                    } else {
                        $this->_notFoundSessions[$item->external_id] = $item;
                    }
                } else {
					
					$existingSession = $this->_existingSessions[$this->_existingSessionsIds[$item->external_id]];
					
					if($teacher->MID){
						$issetGroup = true;
						
						if(empty($item->group_external_id)){
							$group_id = false;
						} elseif(isset($groupList[$item->group_external_id])){
							$group_id = $groupList[$item->group_external_id];
						} else {
							$issetGroup = false;
						}				
						
						if($issetGroup){
							if ($this->_needAssignTutor($existingSession['subid'], $teacher->MID, $group_id)) {	
								$this->_updateAssign[$existingSession['subid'].'~'.$teacher->MID.'~'.$group_id] = array(
									'group_id'				=> $group_id,
									'subject_id' 			=> $existingSession['subid'],
									'tutor_id'				=> $teacher->MID,
									'tutor_fio'				=> $teacher->LastName.' '.$teacher->FirstName.' '.$teacher->Patronymic,
									'group_external_id'		=> $item->group_external_id,
									'subject_external_id'	=> $item->external_id,
									'subject_name'			=> $item->name,
									'tutor_id_external'		=> $item->teacher_id_external,
									
								);																
							}
						}
					}
					
					
					if (isset($this->_updatesSessions[$item->external_id])) continue;
                    if (!isset($this->_existingSessions[$this->_existingSessionsIds[$item->external_id]])) continue;

                    
					if(!$item->practice_begin){
						$item->practice_begin	= $existingSession['practice_begin'];
					}
					
					if(!$item->practice_end){
						$item->practice_end		= $existingSession['practice_end'];
					}
					
                    
					if ($this->_needSessionUpdate($item)) {
                        //$item->subid = $existingSession->subid;                        
						$item->subid = $existingSession['subid'];                        
						$this->_updatesSessions[$item->external_id] = array('source' => $existingSession, 'destination' => $item);                        
                    }
					
                    //unset($this->_existingSessions[$existingSession->subid]);
                    #unset($this->_existingSessions[$existingSession['subid']]);
                }
				
            }			
        }	


			#pr($this->_tutorLecture);
			#pr($this->_tutorPractice);
			#pr($this->_tutorLab);
		
        $this->saveToCache();
		
		
		
    }
	
	
	public function getSubjectId($external_subject_id){		
		if(isset($this->_matchingSubjectIDs[$external_subject_id])){
			return $this->_matchingSubjectIDs[$external_subject_id];
		} 		
		$subject = $this->getService('Subject')->getOne($this->getService('Subject')->fetchAll($this->getService('Subject')->quoteInto("external_id = ?", strval($external_subject_id))));		
		$this->_matchingSubjectIDs[$external_subject_id] = $subject->subid;
		
		if(!$subject->subid){ return false; }
		
		return $subject->subid;	
	}
	
	public function getMID($tutor_mid_external){
		if(isset($this->_matchingUserGUIDs[$tutor_mid_external])){
			return $this->_matchingUserGUIDs[$tutor_mid_external];
		} 
		$user = $this->getService('User')->getOne($this->getService('User')->fetchAll($this->getService('User')->quoteInto('mid_external = ?', $tutor_mid_external)));
		
		$this->_matchingUserGUIDs[$tutor_mid_external] = $user->MID;
		
		if(!$user->MID){ return false; }
		
		return $user->MID;	
	}
    
	/**
	 * @param - Zend Log
	*/
    public function import($log = false)
    {
		try { 
			$result = array(				
				'error' 	=> false,
				'allInsert' => count($this->_insertsSessions), //--Всего для вставки			
				'allUpdate' => count($this->_updatesSessions), //--Всего кол-во обновленных
				'update' 	=> 0, //--по факту кол-во обновленных	
				'allTutors' => 0, //--всего тьюторов для обновления в сессиях. По факту кол-во строк из CSV, где указан id тьютора.
				'tutors' 	=> 0, //--по факту обновлено тьюторов			
			);	
			
			$sessionsService = $this->getService('Subject');
			
			if (count($this->_insertsSessions)) {				
				$userService = $this->getService('User');				
				foreach($this->_insertsSessions as $id => $insert) {					
					if(!$this->updateSession($insert)){
						continue;
					}
					
					$subject = $sessionsService->getByCode($insert->external_id);
					# по какой-то пирчине такая сессия есть. Повторно ее не создаем.
					if(!empty($subject->subid)){
						continue;
					}					
					
					//--fix for cron
					if(method_exists($insert, 'getValues')) {								
						$values = $insert->getValues(null, array(
							'programm_id_external',
							'teacher_id_external',							
							'group_external_id',
							'tutor_lector',
							'tutor_practic',
							'tutor_laboratory',
							//'learning_subject_id_external',
						));
					} else {					
						$values = $this->getValues($insert, null, array('programm_id_external', 'teacher_id_external', 'group_external_id', 'tutor_lector', 'tutor_practic', 'tutor_laboratory'));			
					}
					if(method_exists($insert, 'getValues')) {								
						$teacher_id_external = $insert->getValues(array('teacher_id_external'), null);
					} else {					
						$teacher_id_external = $this->getValues($insert, array('teacher_id_external'), null);			
					}
									
					$session = $sessionsService->insert($values);						
					
					if ($session) {
						
						
						$sessionsService->copyElements($session->base_id, $session->subid);						
						$this->link($insert, $session->subid);						
						$this->_existingSessionsIds[$session->external_id] = $session->subid;						
						$result['insert']++;
						
						//--fix for cron
						if(method_exists($insert, 'getValues')) {								
							$teacher_id_external = $insert->getValues(array('teacher_id_external'), null);
						} else {					
							$teacher_id_external = $this->getValues($insert, array('teacher_id_external'), null);			
						}
						if(!empty($teacher_id_external['teacher_id_external'])){
							$user = $userService->getOne($userService->fetchAll($userService->quoteInto('mid_external = ?', $teacher_id_external['teacher_id_external'])));
							if($user){
								$sessionsService->sendTutorAssignMessage($user->MID, $session->subid); # переделать под назначение на группы							
							}
						}						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_SUCCESS'], $session->external_id, $session->name), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_ERROR'], $session->external_id, $session->name), $log::ERR); }
					}					
				}
			}
			
			if (count($this->_updatesSessions)) {
				$userService = $this->getService('User');			
				foreach($this->_updatesSessions as $id => $update) {								
					
					//--fix for cron
					if(method_exists($update['destination'], 'getValues')) {								
						$values = $update['destination']->getValues(null, array(
							'programm_id_external',
							'teacher_id_external',							
							'group_external_id',
							'tutor_lector',
							'tutor_practic',
							'tutor_laboratory',
							//'learning_subject_id_external',
						));
					} else {					
						$values = $this->getValues($update['destination'], null, array('programm_id_external', 'teacher_id_external', 'group_external_id', 'tutor_lector', 'tutor_practic', 'tutor_laboratory'));			
					}
					$isUpdSession = $sessionsService->update($values);					
				}
			}
			
			if(count($this->_updateAssign)){
				$serviceSubjectGroup = $this->getService('SubjectGroup');
				$needToSend = array();
				foreach($this->_updateAssign as  $row){					
					$isAssign = $serviceSubjectGroup->assignTutorToGroup($row['tutor_id'], $row['subject_id'], $row['group_id']);					
					if($isAssign){
						$needToSend[$row['tutor_id']][$row['subject_id']] = $row['subject_id'];
					}					
				}				
				if(count($needToSend)){
					foreach($needToSend as $tutor_id => $subjects){
						foreach($subjects as $subject_id){
							$sessionsService->sendTutorAssignMessage($tutor_id, $subject_id);	
						}
					}
				}				
			}
			
			
			if(count($this->_notFoundSessions)){
				foreach($this->_notFoundSessions as $i){									
					if($log){ $log->log(sprintf($log->msg['ITEM_SESSION_NOT_FOUND'], $i->external_id, $i->name), $log::ERR); }
				}
				
			}
			
			### Назначение тьюторов на занятия ###
			$serviceLAT   = $this->getService('LessonAssignTutor');
			$serviceTutor = $this->getService('Tutor');
			$updatingRoles = array();
			
			# данное действие должно выполняться ПОСЛЕ обновления и вставки сессий, иначе на новые сессии тьютор не назначится.		
			
			if(count($this->_tutorLecture)){
				foreach($this->_tutorLecture as $external_subject_id => $tutors){				
					$subject_id = $this->getSubjectId($external_subject_id);
					foreach($tutors as $tutor_mid_external){
						
						$tutor_id   = $this->getMID($tutor_mid_external);
						if(!$tutor_id){ continue; }
						$serviceLAT->assignLector($subject_id, $tutor_id);
						
						# м.б несколько ролей
						$updatingRoles[$tutor_id][$subject_id][HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR] = HM_Lesson_Assign_Tutor_TutorModel::ROLE_LECTOR;						
						$roles = array_sum($updatingRoles[$tutor_id][$subject_id]); 
						
						if(isset($updatingRoles[$tutor_id][$subject_id]) )
					# Это еще актуально?	
					$serviceTutor->updateWhere(
							array('roles' => $roles),
							array('MID = ? ' => $tutor_id, 'CID = ?' => $subject_id )
						);
						
					}				
				}			
			}
			
			if(count($this->_tutorPractice)){
				foreach($this->_tutorPractice as $external_subject_id => $tutors){				
					$subject_id = $this->getSubjectId($external_subject_id);
					foreach($tutors as $tutor_mid_external){
						$tutor_id   = $this->getMID($tutor_mid_external);
						if(!$tutor_id){ continue; }
						$serviceLAT->assignSeminarian($subject_id, $tutor_id);
						
						# м.б несколько ролей
						$updatingRoles[$tutor_id][$subject_id][HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE] = HM_Lesson_Assign_Tutor_TutorModel::ROLE_PRACTICE;						
						$roles = array_sum($updatingRoles[$tutor_id][$subject_id]); 
						
						$serviceTutor->updateWhere(
							array('roles' => $roles),
							array('MID = ? ' => $tutor_id, 'CID = ?' => $subject_id )
						);						
					}				
				}					
			}
			
			if(count($this->_tutorLab)){			  				
				foreach($this->_tutorLab as $external_subject_id => $tutors){				
					$subject_id = $this->getSubjectId($external_subject_id);					
					foreach($tutors as $tutor_mid_external){
						$tutor_id   = $this->getMID($tutor_mid_external);
						if(!$tutor_id){ continue; }
						$serviceLAT->assignLaborant($subject_id, $tutor_id);
						
						# м.б несколько ролей
						$updatingRoles[$tutor_id][$subject_id][HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB] = HM_Lesson_Assign_Tutor_TutorModel::ROLE_LAB;						
						$roles = array_sum($updatingRoles[$tutor_id][$subject_id]); 
						
						$serviceTutor->updateWhere(
							array('roles' => $roles),
							array('MID = ? ' => $tutor_id, 'CID = ?' => $subject_id )
						);												
					}				
				}
			}

			
			#if($this->getService('User')->getCurrentUserId() == 5829){	
				foreach($this->_tutorLinkGroups as $subject_external_id => $links){
					if(empty($links)){ continue; }
					$subject_id = $this->getSubjectId($subject_external_id);
					if(empty($subject_id)){ continue; }
					
					foreach($links as $tutor_id => $groups){				
						if(is_array($groups) && count($groups)){
							foreach($groups as $group_id){
								$this->getService('SubjectGroup')->assignTutorToGroup($tutor_id, $subject_id, $group_id);
								#echo 'Назначен тьютор '.$tutor_id.' на группу '.$group_id.' в сессии '.$subject_id.'<br />';
							}
						} else {
							$this->linkTutor($tutor_id, $subject_id);		
							#echo 'Назначен тьютор '.$tutor_id.' на всю сессию '.$subject_id.'<br />';
						}
					}
				}
			#}
			
		
		#pr($updatingRoles);
		#die;
			######
			
			
		} catch (Exception $e) {									
		echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), $log::ERR); }			
			$result['error'] = true;
			
			die;			
		}		
		return $result;
    }
    
    protected function link($insert, $sessionId) {       
		
		$programmService = $this->getService('Programm');
        $userService = $this->getService('User');

        if($insert->programm_id_external){
            $programm = $programmService->fetchAll(array('id_external = ?' => $insert->programm_id_external))->current();
        }

		/*
        if($insert->teacher_id_external){
            $user = $userService->fetchAll(array('mid_external = ?' => $insert->teacher_id_external))->current();
        }
		*/

        if($programm->programm_id){
            $this->linkProgramms($programm->programm_id, $sessionId);
        }
		
		# Переехал в основной метод импорта. Выполняется всегда.
		/*
		if(isset($this->_tutorLinkGroups[$insert->external_id])){
			foreach($this->_tutorLinkGroups[$insert->external_id] as $tutor_id => $groups){				
				if(is_array($groups) && count($groups)){
					foreach($groups as $group_id){
						$this->getService('SubjectGroup')->assignTutorToGroup($tutor_id, $sessionId, $group_id);						
					}
				} else {
					$this->linkTutor($tutor_id, $sessionId);					
				}
			}
		}
		*/
		
		/*
        if($user->MID){
            $this->linkTutor($user->MID, $sessionId);
        }
		*/

    }
    
    protected function linkProgramms($programmId, $sessionId) {
        
		$subjectService = $this->getService('Subject');
		
        $programmService = $this->getService('Programm');
		
        $programmService->assignSubject($programmId, $sessionId);
		
        
        $programmUsers = $programmService->getProgrammUsers($programmId);
		
        
        foreach ($programmUsers as $programmUser) {            			
			$subjectService->assignStudent($sessionId, $programmUser->user_id);			
        }		
    }
    
    protected function linkTutor($userId, $sessionId) {
        $tutorService = $this->getService('Tutor');
		#if(!empty($tutorService->isUserExists($sessionId, $userId))){ return; }
		
		$isAssign = $tutorService->getOne($tutorService->fetchAll( array('CID = ?' => $sessionId, 'MID = ?' => $userId)));
		if(!empty($isAssign->TID)){ return; }
		
		
		
        $tutorService->insert(array(
            'MID' => $userId,
            'CID' => $sessionId,
			'date_assign' => date('Y-m-d 23:59',time()),
        ));
    }
    
    protected function updateSession(&$insert) {
		if(empty($insert->base_id)){ return false; }
        
        $this->copySubject($insert);
        return true;        
    }
    
    protected function copySubject(&$insert) {
        
        $excludeFileds = array(
            'subid',
            'period',
            'exam_type',
        );
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->fetchAll(array('subid = ?' => $insert->base_id))->current();
        foreach ($subject->getValues() as $fieldName => $value) {
            if(($insert->$fieldName == '') && !in_array($fieldName, $excludeFileds)){
                $insert->$fieldName = $value;
            }
        }
    }

    /**
	 * 
	*/
    protected function setBaseId(&$insert) {
		if(!empty($insert->base_id) && !empty($insert->base)){ return true; }        
		$base_id = $this->getService('Learningsubjects')->getBaseSubjectId($insert->learning_subject_id_external);		
		
        if($base_id){            
            $insert->base 		= HM_Subject_SubjectModel::BASETYPE_SESSION;
            $insert->base_id 	= $base_id;
            return true;
        } else {
            return false;
        }
    }
	
	protected function setBaseName(&$insert) {        
		$subject = $this->getService('Subject')->getById($insert->base_id);
		if($subject){ $insert->base_name = $subject->name; }
		return;
    }
    
	/**
	 * DEPRECATED ?
	*/
    protected function isLearningsubjectExist($item) {		
		$base_id = $this->getService('Learningsubjects')->getBaseSubjectId($item->learning_subject_id_external);	
		
        if($base_id){
            return true;
        } else {
            return false;
        }
	}
    
	
	/**
	 * возвращает значения определенных полей. mod HM_Model_Abstract
	*/
	public function getValues($data = array(), $keys = null, $excludes = null)
	{
		$data = (array)$data;
		$values = array();
		if (is_array($data) && count($data)) {
			foreach($data as $key => $value) {
				if ((!is_object($value) && !is_array($value)) || $value instanceof Zend_Db_Expr) {
					if (is_array($keys) && !in_array($key, $keys)) continue;
					if (is_array($excludes) && in_array($key, $excludes)) continue;
					$values[$key] = $value;
				}
			}
		}
		return $values;
	}
	
	/**
	 * не создаем сессию, если для нее нету программы.
	*/	
	protected function isProgrammExist($item){		
		$programmIdExt = $item->programm_id_external;		
		if(empty($programmIdExt)){
			return false;
		}        
		
        $programmService = $this->getService('Programm');
        $programm = $programmService->fetchAll(array('id_external = ?' => $programmIdExt))->current();		        
		if($programm->programm_id){
            return true;
        } else {
            return false;
        }
	}
	
	
	protected function prepareTutorLecture($lector, $subject_external_id){
		if(!$lector || !$subject_external_id){ return; }
		$ids = explode(',', $lector);
		$ids = array_filter($ids);
		if(empty($ids)){ return; }
		$data = array();
		foreach($ids as $tutor_mid_external){
			$this->_tutorLecture[$subject_external_id][$tutor_mid_external] = $tutor_mid_external;
		}
		return;
	}
	
	protected function prepareTutorPractice($practic, $subject_external_id){
		if(!$practic || !$subject_external_id){ return; }
		$ids = explode(',', $practic);
		$ids = array_filter($ids);
		if(empty($ids)){ return; }
		$data = array();
		foreach($ids as $tutor_mid_external){
			$this->_tutorPractice[$subject_external_id][$tutor_mid_external] = $tutor_mid_external;
		}
		return;
	}
	
	protected function prepareTutorLab($laboratory, $subject_external_id){
		if(!$laboratory || !$subject_external_id){ return; }
		$ids = explode(',', $laboratory);
		$ids = array_filter($ids);
		if(empty($ids)){ return; }
		$data = array();
		foreach($ids as $tutor_mid_external){
			$this->_tutorLab[$subject_external_id][$tutor_mid_external] = $tutor_mid_external;
		}
		return;
	}
	
	protected function isNotFoundTaskLesson($item){		
		$this->setBaseId($item);
		if($this->getService('Lesson')->issetTaskLessons($item->base_id)){ return false; }
		$this->setBaseName($item);
		return true;
	}
	
				
}