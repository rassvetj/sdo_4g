<?php
class Marksheet_IndexController extends HM_Controller_Action
{
    const CACHE_NAME = 'Marksheet_IndexController';
	
	protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;
	
	
	
	protected $_scores		= NULL;
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'scores'			=> $this->_scores,
                'cacheCreateted'	=> $this->_cacheCreateted,
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
            $this->_cacheCreateted		= $actions['cacheCreateted'];            
            $this->_scores				= $actions['scores'];            
            $this->_restoredFromCache 	= true;
            return true;
        }

        return false;
    }

    public function indexAction()
    {

		
		$serviceMFile = $this->getService('FilesMarksheet');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
		
		
		$fromDate = $this->_getParam('from', '');
        $toDate   = $this->_getParam('to', '');
        $group    = $this->_getParam('groupname', null);
		
		$is_manual = (int)$this->_getParam('manual', 0);

        $this->_setParam('to', null);
        $this->_setParam('from', null);
        $this->_setParam('groupname', null);

        $fromDate = str_replace('_', '.', $fromDate);
        $toDate = str_replace('_', '.', $toDate);


       $courseId = $this->id = (int) $this->_getParam('subject_id', 0);

	   $this->view->maxBallTotalRating  = $this->getService('Lesson')->getMaxBallTotalRating($courseId);	   
	   $this->view->maxBallMediumRating = $this->getService('Lesson')->getMaxBallMediumRating($courseId);

		if($this->view->maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
			$this->view->maxBallTotalRating  = HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT;
			$this->view->maxBallMediumRating = 100 - HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT;
		}

		# список ранее созданных ведомостей, доступных для скачивания. Привязка к курсу и автору.
		$this->view->files_marksheet = $serviceMFile->getSubjectMarksheets($courseId); 
		
	   
	   
       if ( $this->_request->isPost() || $is_manual == 1 ) {
           if (($fromDate == '' || $toDate == '')) {
               $fromDate = null;
               $toDate   = null;
           }

           if (!$fromDate && !$toDate && !$group) {
               Zend_Registry::get('session_namespace_default')->marksheetFilter = null;
           } else {
               Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId] = array( 'to'    => $toDate,
                                                                                                    'from'  => $fromDate,
                                                                                                    'group' => $group);
           }

       } else {
           $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
           $fromDate = $dates['from'];
           $toDate   = $dates['to'];
           $group    = $dates['group'];
       }

		if(!empty($group)){
			$this->view->isSelectedGroup = true;
		   
		}
	   
       /*if(($fromDate == '' || $toDate == '') && $this->_request->isPost()){
            $fromDate = null;
            $toDate = null;
            Zend_Registry::get('session_namespace_default')->marksheetFilter = null;
        }elseif($fromDate == '' || $toDate == ''){
            $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
            $fromDate = $dates['from'];
            $toDate = $dates['to'];
        }else{
            Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId] = array('to' => $toDate, 'from' => $fromDate);
            $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
        }*/
        $this->view->dates = $dates;

        $subject                       = $this->getOne($this->getService('Subject')->find($courseId));
		
		/*
        $groups                        = $this->getService('Group')->fetchAll(array('cid = ?' => $courseId));
		
		
        
      //  $this->view->groupname         = array(0=>_('-Все-')) + ((count($groups))? $groups->getList('gid','name') : array());

        $studygroups                   = $this->getService('StudyGroupCourse')->getCourseGroups($courseId);
		
		$studygroups_custom            = $this->getService('StudyGroupCustom')->getCourseGroups($courseId);
		
		
		$allGroupsForFiltered = array(); # форматированный формат всех доступных групп для фильтрации.
		if (count($studygroups)) {
			foreach ($studygroups as $studygroup) {
				$allGroupsForFiltered[$studygroup->group_id] = $studygroup->name;
			}
		}
		if (count($studygroups_custom)) {
			foreach ($studygroups_custom as $studygroup) {
				$allGroupsForFiltered[$studygroup->group_id] = $studygroup->name;
			}
		}		
		$availableGroups = $this->getService('Subject')->filterGroupsByAssignStudents($courseId, $this->getService('User')->getCurrentUserId(), $allGroupsForFiltered); # группы, доступные тьютору с учетом назначения на конкретную группу.
		
	
        $this->view->groupname         = array();

        if (count($studygroups)) {
            $this->view->groupname[] =  _('-Группы-');
            foreach ($studygroups as $studygroup) {
				if(!isset($availableGroups[$studygroup->group_id])) { continue; }
                $this->view->groupname['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }
		
		if (count($studygroups_custom)) {
            if(!count($this->view->groupname)){
				$this->view->groupname[] =  _('-Группы-');	
			}			
            foreach ($studygroups_custom as $studygroup) {
				if(!isset($availableGroups[$studygroup->group_id])) { continue; }
                $this->view->groupname['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }
		
				
        if (count($groups)) {			
            $this->view->groupname[] =  _('-Подгруппы-');
            foreach ($groups as $item) {
                $this->view->groupname['s_'.$item->gid] = $item->name;
            }
        }
		*/
		$this->view->groupname = $this->getService('Lesson')->createFilterGroupList($courseId, $this->getService('User')->getCurrentUserId());

        $this->view->current_groupname = $group;
        $this->view->dates = array('from' => $fromDate, 'to' => $toDate);

        $this->view->setExtended(
            array(
                'subjectName' => $this->service,
                'subjectId' => $this->id,
                'subjectIdParamName' => $this->idParamName,
                'subjectIdFieldName' => $this->idFieldName,
                'subject' => $subject
            )
        );
        
		$this->view->isAllowBringTrained = true;
		$user = $this->getService('User')->getCurrentUser();		
		if($this->getService('Acl')->inheritsRole($user->role, HM_Role_RoleModelAbstract::ROLE_TUTOR)){ //--Если это тьютор, то для него генерируем другую таблицу. Без возможности переводить в прошедшее обучение.
			$this->view->isAllowBringTrained = false;
			$this->view->page = (empty($subject->module_code)) ? 'page' : 'page-module-slave';
			
		}
		
		
		
		$score 			= $this->getScore($courseId, $fromDate, $toDate, $group);
		$serviceSubject = $this->getService('Subject');
		
		
		
		/*	
        $score = $this->getService('Lesson')->getUsersScore($courseId, $fromDate, $toDate, $group);
		
		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $courseId, true);			
		}
		*/
		
		
		/*if($marks[$subject->subid] > 0){
					$view->reasonFail 	= $subjService->getFailPassMessage($userId, $subject->subid);
				}
				*/
		$midWithHalfAccess = $this->getService('UserInfo')->getMidWithHalfAccess();
		if(!empty($midWithHalfAccess)){
			foreach($score[0] as $mid => $i){
				if(in_array($mid, $midWithHalfAccess)){
					unset($score[0][$mid]);
				}
			}
		}

		$this->view->additional                 = array();
        $this->view->score 						= $score;
        $this->view->subjectId 					= $courseId;
		if($this->getService('Subject')->isMainModule($courseId)){
			
			# получаем список студентов и только по ним находим данные по доп. модулю.
			$user_IDs 		  = array_keys($score[0]);
			$this->view->page = 'page-module-main';	
			$this->view->additional	= array(
				'moduleData' => $this->getService('Subject')->getModuleData($courseId, $user_IDs),
			);
		}
		
		
		$this->view->additional['filter_user_ids'] = array_keys($score[0]);
		
		if($serviceSubject->isDOT($courseId)){
			$this->view->page = 'page-dot';			
		}
		
		# кнопку Завершение приема ПЗ и РК показываем только для неДО
			
		# Если нет новый действий со стороны студентов и у всех есть балл за сессию, пусть даже и 0, то показываем кнопку "Завершить сессию"
		# ДОступно семинаристу и/или преподавателю лабораторных работ или если роль не задана, то просто преподавателю, главное, чтобы он не был лектором
		# Лектор также может быть и семинаристом, которому доступен курс
		$isAllowBtnBlockedTask	= true;
		$isLector				= $this->getService('LessonAssignTutor')->isLector($this->getService('User')->getCurrentUserId(), $courseId);
		if($isLector){
			$isAllowBtnBlockedTask	= false;
				
			if(!$isAllowBtnBlockedTask){
				$isSeminarian   		= $this->getService('LessonAssignTutor')->isSeminarian($this->getService('User')->getCurrentUserId(), $courseId);
				if($isSeminarian){ $isAllowBtnBlockedTask	= true; }
			}
				
			if(!$isAllowBtnBlockedTask){
				$isLaborant   = $this->getService('LessonAssignTutor')->isLaborant($this->getService('User')->getCurrentUserId(), $courseId);
				if($isLaborant){ $isAllowBtnBlockedTask	= true; }
			}
		}
		
		
		if(!$isAllowBtnBlockedTask){
			$this->view->isShowBtnBlockedTask = 0;
		} else {
			$this->view->isShowBtnBlockedTask = 1;
			$this->view->blockedTaskGroups	  = $this->getService('Lesson')->getBlockedTaskGroups($courseId, $this->getService('User')->getCurrentUserId());
			#pr($groups);
			# получаем доступных студентов
			# получаем их группы.
			
			# получаем список групп, где нужно выставить оценку неуд.
			# Если последнее сообдени не выставлена оценка и не решение на проверку, то выставляем оценку.
			
			# оставляем группы, в которых есть студенты для явыставления неуда.
			
		}
		
		
		# Информация по ведомости из 1С
		$this->view->marksheets = $this->getMarksheetsInfo($subject);
		$this->view->has_marksheet	= false; # Есть данные по ведемости на выбранную группу. Если данных нет - запретить формировать ведомость.
		$group_external_id 			= false;
		
		if(!empty($group)){
			$tmp 				= explode('_', $group);
			$group_id			= (int)$tmp[1];
		}
		
		if(!empty($group_id)){
			$group_external_id	= $this->getService('StudyGroup')->getById($group_id)->id_external;
		}
		
		if(!empty($this->view->marksheets)){
			foreach($this->view->marksheets as $i){
				if($i->group_external_id == $group_external_id){
					$this->view->has_marksheet = true;
					break;
				}
			}
		}
		
		$current_user_id = $this->getService('User')->getCurrentUserId();
		
		$this->view->is_manager = false;
		if(in_array($current_user_id, HM_Marksheet_MarksheetModel::getManagers())){
			$this->view->is_manager = true;
		}
		
		
		
		#if($_GET['dev'] == 76){
		#	echo '<pre>';
		#	var_dump($this->view->score[3]);
		#	echo '</pre>';
		#}
		
		if(!$this->view->is_manager){
			$default	= new Zend_Session_Namespace('default');
			$user_base	= $default->userRestore;
			if(isset($user_base)){
				if(in_array($user_base->MID, HM_Marksheet_MarksheetModel::getManagers())){
					$this->view->is_manager = true;
				}
			}
		}
    }
	
	
	public function getScore($courseId, $fromDate, $toDate, $group){
		#$key = $courseId.'~'.$fromDate.'~'.$toDate.'~'.$group;
		
		# кэширование не нужно
		#if(empty($this->_scores[$key])){ $this->restoreFromCache(); }
		
		#if($this->_cacheCreateted <= time() ){ # очищаем кэш, старше 1 минут
		#	$this->clearCache(); 
		#	$this->_cacheCreateted = time() + (60*1);
		#}
		
		#if(!empty($this->_scores[$key])){ return $this->_scores[$key]; }
		
		
		$score = $this->getService('Lesson')->getUsersScore($courseId, $fromDate, $toDate, $group);
		
		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $courseId, true);			
		}
		#$this->_scores[$key] = $score;
		#$this->saveToCache();		
		return $score;
	}
	

    public function setScoreAction()
    {
        $scores = $this->_getParam('score');
        $courseId = $this->_getParam('subject_id', 0);

        $isTutor = $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            HM_Role_RoleModelAbstract::ROLE_TUTOR
        );
        

        if ($scores && !empty($scores) && is_array($scores))
        {
            foreach ($scores as $id => $score)
            {
                list($user_id, $lesson_id) = explode("_", $id);
                $allowTutors = array();
                if(is_numeric($lesson_id)){
                    $allowTutors = $this->getService('Lesson')->fetchAll(
                        $this->getService('Lesson')->quoteInto(
                            'allowTutors = 1 AND SHEID = ?',
                            array($lesson_id)
                        )
                    )->current();
                    $allowTutors = $allowTutors->allowTutors;
                }
                if(!$isTutor || ($isTutor && $allowTutors)){
                    if (null === $score || '' === $score)
                        $score = -1;
                    $this->_setScore($id, $score, $courseId);
                }
            }
        }
        else
        {
            $id = $this->_getParam('id');
            $score = $this->_getParam('score', -1);
            $this->_setScore($id, $score, $courseId);
        }

        echo count($scores);
        exit;
    }

    private function _setScore($id, $score, $courseId)
    {
        $score = iconv('UTF-8', Zend_Registry::get('config')->charset, $score);
        list($pkey, $skey) = explode("_", $id);

        $this->getService('LessonAssign')->setUserScore($pkey, $skey, $score, $courseId);
    }
    
    public function setTotalScoreAction() {
        $persons = $this->_getParam('person', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        
        $lessonAssignService = $this->getService('LessonAssign');
        $userService = $this->getService('User');
        
        
        $notMarked = array();
        foreach($persons as $userId => $value){
            if(!$lessonAssignService->onLessonScoreChanged($subjectId, $userId)){
                $notMarked[] = $userId;
            }
        }
        
        $this->_flashMessenger->addMessage(_('Оценки выставлены успешно!'));
        
        if(count($notMarked)){
            $users = $userService->fetchAll(array('MID IN (?)' => $notMarked));
            if (count($users)) {
                foreach($users as $user) {
                    $userName = $user->getName();
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _("Оценки не были выставлены: ") . $userName
                    ));
                }
            }
        }
        
    }
    
    public function setCommentAction(){
        $comment = $this->_getParam('comment');
        $score = $this->_getParam('score');
        $subjectId = $this->_getParam('subject_id',0);
        $comment = iconv('UTF-8', Zend_Registry::get('config')->charset, $comment);
        foreach($score as $key => $value){
            list($pkey, $skey) = explode("_", $key);
            $this->getService('LessonAssign')->setUserComments($pkey, $skey, $comment, $subjectId);
        }
        echo 'Ok';
        exit;
    }
    
    public function graduateStudentsAction(){
        $person = $this->_getParam('person', 0);
        $courseId = $this->_getParam('subject_id', 0);
        foreach($person as $key => $value) {
            if (!$this->getService('Subject')->assignGraduated($courseId, $key)) {
                echo 'Fail';
                exit;
            }
            /*$student = $this->getOne(
                $this->getService('Student')->fetchAll(
                    array(
                    	'CID = ?' => $courseId, 
                    	'MID = ?' => $key
                    )
                )
            );
            if($student){
                $this->getService('Graduated')->insert(array('MID' => $key, 'CID' => $courseId, 'begin' => $student->time_registered));
                $this->getService('Student')->deleteBy(array('CID = ?'=> $courseId, 'MID = ?' => $key));
            }else{
               echo 'Fail';
               exit;
            } */
       }
       echo 'Ok';
       exit;
    }

    public function clearScheduleAction(){
        $schedule = $this->_getParam('schedule', 0);
        foreach($schedule as $key => $value){
            $this->getService('LessonAssign')->updateWhere(array('V_STATUS' => -1), array('SHEID = ?' => $key));
        }
        echo 'Ok';
        exit;
    }
    
    public function printAction(){
    	
		/*
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score = $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']); 

		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $subjectId, true);			
		}		
		
		$exam_types = HM_Subject_SubjectModel::getExamTypes();
		$info 		= $this->getService('Marksheet')->getInfo($subjectId);
		
		$additional = array(
			'recordBookNumbers' => $this->getService('RecordCard')->getRecordbookNumbers(),
			'dataRatingMedium' 	=> $score[3], # Итоговый текущий рейтинг # если это модуль, то берем интегральную оценку.
			'dataRatingTotal' 	=> $score[4],  # Рубежный рейтинг
			'subjectName'		=> $subject->name,			
			'exam_type'			=> $subject->exam_type,
			'maxBallTotalRating'=> $this->getService('Lesson')->getMaxBallTotalRating($subjectId),
			'isDO'				=> $subject->isDO,			
			'exam_type_name'	=> $exam_types[$subject->exam_type],
            
			'marksheet_external_id' => !empty($info->external_id) 	? $info->external_id 	: '',
			'faculty' 				=> !empty($info->faculty) 		? $info->faculty 		: '', #$subject->faculty,			
			'semester' 				=> !empty($info->semester) 		? $info->semester 		: '', #$subject->semester,
			'course' 				=> !empty($info->course) 		? $info->course 		: '',
			'years' 				=> !empty($info->year) 			? $info->year 			: '', #$subject->year_of_publishing,
			'dean' 					=> !empty($info->dean) 			? $info->dean 			: '',
			'tutor'					=> !empty($info->tutor) 		? $info->tutor 			: '',
			'date_issue' 			=> !empty($info->date_issue) 	? date('d.m.Y', strtotime($info->date_issue)) : '',			
		);
				
        $this->view->score      = $score;
        $this->view->subjectId  = $subjectId;
		$this->view->additional = $additional;
        
		echo $this->view->render('index/export/vedomost-print.tpl');		
		die;
		*/
		
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$courseId = $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($courseId));
        
        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
        $score = $this->getService('Lesson')->getUsersScore($courseId,$dates['from'],$dates['to'],$dates['group']);
        
		
		
		
		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $courseId, true);			
		}
		
		
		$additional = array(			
			'dataRatingMedium' 	=> $score[3], # Итоговый текущий рейтинг
			'dataRatingTotal' 	=> $score[4],  # Рубежный рейтинг
			'exam_type'			=> $subject->exam_type,
			'isDO'				=> $subject->isDO,
			'maxBallTotalRating'	=> $this->getService('Lesson')->getMaxBallTotalRating($courseId),	   
			'maxBallMediumRating'	=> $this->getService('Lesson')->getMaxBallMediumRating($courseId),			
		);
		$this->view->additional = $additional;
		
        $this->view->score = $score;
        $this->view->subjectId = $courseId;
		
    	
    }
    
    public function wordAction(){

		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));
        
        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score = $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']);
        
        $this->view->score = $score;
        $this->view->subjectId = $subjectId;
        $data =  $this->view->render('index/export-word.tpl');
        
        $doc = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
        fwrite($doc, $data);
        fclose($doc);
        
		$this->sendFile($subjectId, 'doc', $subject->name);
    	
    }
    
    public function vedomostAction(){
    	
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

		# программа сессии
		$subject_programm_ids = $this->getService('ProgrammEvent')->getSubjectProgramms($subjectId);
		if(!empty($subject_programm_ids)){
			$subject_programms = $this->getService('Programm')->fetchAll($this->getService('Programm')->quoteInto(
				"programm_id IN (?) AND (name LIKE 'КЛН-%' OR name LIKE 'МИН-%' OR name LIKE 'ОШ-%' OR name LIKE 'ПАВ-%')", $subject_programm_ids)
			);
		}
		
		$this->view->isProgrammFilial = false;
		if(count($subject_programms) > 0){
			$this->view->isProgrammFilial = true;
		}
		
        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score = $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']);
		$midWithHalfAccess = $this->getService('UserInfo')->getMidWithHalfAccess();
        if(!empty($midWithHalfAccess)){
			foreach($score[0] as $mid => $i){
				if(in_array($mid, $midWithHalfAccess)){
					unset($score[0][$mid]);
				}
			}
        }

		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $subjectId, true);			
		}		
		
		
		
		
		$tmp 				= explode('_', $dates['group']);
		$group_id			= (int)$tmp[1];
		$group_id_external 	= false;
		if(!empty($group_id)){			
			$res_group 			= $this->getService('StudyGroup')->getOne($this->getService('StudyGroup')->find($group_id));
			$group_id_external	= $res_group->id_external;			
		}
		$info = $this->getService('Marksheet')->getInfo($subjectId, false, $group_id_external);
		
		
		$exam_types = HM_Subject_SubjectModel::getExamTypes();
		$additional = array(
			'marksheet_external_id' => !empty($info->external_id) 	? $info->external_id 	: '',
			'faculty' 				=> !empty($info->faculty) 		? $info->faculty 		: '', #$subject->faculty,
			#'study_form' 			=> !empty($info->study_form) 	? $info->study_form 	: '',
			'semester' 				=> !empty($info->semester) 		? $info->semester 		: '', #$subject->semester,
			'course' 				=> !empty($info->course) 		? $info->course 		: '',
			'years' 				=> !empty($info->year) 			? $info->year 			: '', #$subject->year_of_publishing,
			'dean' 					=> !empty($info->dean) 			? $info->dean 			: '',
			'tutor'					=> !empty($info->tutor) 		? $info->tutor 			: '',
			'form_study'			=> !empty($info->form_study) 	? $info->form_study 	: '',
			'date_issue' 			=> !empty($info->date_issue) 	? date('d.m.Y', strtotime($info->date_issue)) : '',
			'attempt'				=> !empty($info->attempt) 		? $info->attempt 		: '',
			
			
			
			'recordBookNumbers' => $this->getService('RecordCard')->getRecordbookNumbers(),
			'dataRatingMedium' 	=> $score[3], # Итоговый текущий рейтинг # если это модуль, то берем интегральную оценку.
			'dataRatingTotal' 	=> $score[4],  # Рубежный рейтинг
			'tutors'			=> $this->getService('Subject')->getAssignedTutors($subjectId),
			'subjectName'		=> $subject->name,
			
			'exam_type'			=> $subject->exam_type,
			'maxBallTotalRating'=> $this->getService('Lesson')->getMaxBallTotalRating($subjectId),
			'isDO'				=> $subject->isDO,
			'is_practice'		=> $subject->is_practice,
			
			'exam_type_name'	=> $exam_types[$subject->exam_type],
			'discipline'		=> !empty($info->discipline) 	? $info->discipline 	: $subject->name,
		);

		if($additional['maxBallTotalRating'] > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
			$additional['maxBallTotalRating']  = HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT;
		}
		
		if($this->getService('Subject')->isMainModule($subjectId)){			
			# получаем список студентов и только по ним находим данные по доп. модулю.
			$user_IDs 		  = array_keys($score[0]);				
			$additional['moduleData'] = $this->getService('Subject')->getModuleData($subjectId, $user_IDs);			
		}
		
		
        $this->view->score = $score;
        $this->view->subjectId = $subjectId;
		$this->view->additional = $additional;
		
		
        
		# xls
		/*
		$data =  $this->view->render('index/export-vedomost.tpl');
        $xls = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
        fwrite($xls, $data);
        fclose($xls);
		$this->sendFile($subjectId, 'xls', $subject->name);
		*/
		
		# PDF dompdf
#try {	


		require_once("dompdf/dompdf_config.inc.php");
		$data =  $this->view->render('index/export/vedomost-pdf.tpl');		
		
		
		
		
		$data = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$data.'</html>';				
		$dompdf = new DOMPDF();
		$dompdf->set_paper('letter', 'landscape');
		

		
		$dompdf->load_html($data);
		
		
		try {
			
			$dompdf->render();		
		
		} catch (Exception $e) {
			
			$customPaper = array(0,0,580,790);		
			$dompdf->set_paper($customPaper,'landscape');
			#echo  $e->getMessage();
			#die;
		}
			
		#if($subjectId == 18265){
			
			
			
		#	echo 1;
		#	die;
		#}
		
		$output = $dompdf->output();
		
		/*
		$this->getService('FilesMarksheet')->addFileFromBinary(
			$output,
			date('d.m.Y').'.pdf', #Имя файла, выводимое для тьютора
			$subjectId,
			array(
				'author_id' => $this->getService('User')->getCurrentUserId(),
				'ext'		=> 'pdf',
			)
		);
		*/
		
		
		$pdf = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
		fwrite($pdf, $output);
		fclose($pdf);
		$this->sendFile($subjectId, 'pdf', $subject->name);
		

 
#} catch (Exception $e) {
    #echo  $e->getMessage();
#}
        
		
		# PDF tcpdf
		/*
		require_once APPLICATION_PATH . '/../library/tcpdf/tcpdf.php';
		$data =  $this->view->render('index/export/vedomost-pdf.tpl');		
		
		$data = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$data.'</html>';				
		
		
		
		// create new PDF document
		$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Nicola Asuni');
		$pdf->SetTitle('TCPDF Example 028');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(10, PDF_MARGIN_TOP, 10);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set font
		$pdf->SetFont('times', 'B', 20);
	
		$pdf->AddPage(); 		
		$pdf->writeHTML($data);		
		$pdfFilePath = Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId;
		$pdf->Output($pdfFilePath,'F');

		$this->sendFile($subjectId, 'pdf', $subject->name);
		*/
    }
	
	
	
	public function excelAction(){
		/*
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score = $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']); 

		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $subjectId, true);			
		}		
		
		
		
		
		$info = $this->getService('Marksheet')->getInfo($subjectId);
		
		
		$exam_types = HM_Subject_SubjectModel::getExamTypes();
		$additional = array(
			'marksheet_external_id' => !empty($info->external_id) 	? $info->external_id 	: '',
			'faculty' 				=> !empty($info->faculty) 		? $info->faculty 		: '', #$subject->faculty,			
			'semester' 				=> !empty($info->semester) 		? $info->semester 		: '', #$subject->semester,
			'course' 				=> !empty($info->course) 		? $info->course 		: '',
			'years' 				=> !empty($info->year) 			? $info->year 			: '', #$subject->year_of_publishing,
			'dean' 					=> !empty($info->dean) 			? $info->dean 			: '',
			'tutor' 				=> !empty($info->tutor) 		? $info->tutor 			: '',
			'date_issue' 			=> !empty($info->date_issue) 	? date('d.m.Y', strtotime($info->date_issue)) : '',
		
			'recordBookNumbers' => $this->getService('RecordCard')->getRecordbookNumbers(),
			'dataRatingMedium' 	=> $score[3], # Итоговый текущий рейтинг # если это модуль, то берем интегральную оценку.
			'dataRatingTotal' 	=> $score[4],  # Рубежный рейтинг
			#'tutors'			=> $this->getService('Subject')->getAssignedTutors($subjectId),
			'subjectName'		=> $subject->name,			
			'exam_type'			=> $subject->exam_type,
			'maxBallTotalRating'=> $this->getService('Lesson')->getMaxBallTotalRating($subjectId),
			'isDO'				=> $subject->isDO,						
			'exam_type_name'	=> $exam_types[$subject->exam_type],
		);
		#######		
		
        $this->view->score = $score;
        $this->view->subjectId = $subjectId;
		$this->view->additional = $additional;
        
		$data =  $this->view->render('index/export/vedomost-excel.tpl');		
		$output = $data;
		
		$pdf = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
		fwrite($pdf, $output);
		fclose($pdf);
		$this->sendFile($subjectId, 'xls', $subject->name);  
		*/
		
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId 	= $this->id = (int) $this->_getParam('subject_id', 0);
        $subject 	= $this->getOne($this->getService('Subject')->find($subjectId));
        $dates 		= Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score 		= $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']);
        
		$serviceSubject = $this->getService('Subject');
		foreach($score[0] as $user_id => $user_data){			
				$score[2][$user_id.'_total']['fail_message'] = $serviceSubject->getFailPassMessage($user_id, $subjectId, true);			
		}
		
		
		$additional = array(			
			'dataRatingMedium' 	=> $score[3], # Итоговый текущий рейтинг
			'dataRatingTotal' 	=> $score[4],  # Рубежный рейтинг
			'exam_type'			=> $subject->exam_type,
			'isDO'				=> $subject->isDO,
			'maxBallTotalRating'	=> $this->getService('Lesson')->getMaxBallTotalRating($subjectId),	   
			'maxBallMediumRating'	=> $this->getService('Lesson')->getMaxBallMediumRating($subjectId),			
		);
		
        $this->view->score 		= $score;
        $this->view->subjectId 	= $subjectId;
		$this->view->additional = $additional;
        $data =  $this->view->render('index/export-excel.tpl');
        
		$file_name = $subject->name;
		
		if(!empty($dates['group'])){
			$tmp 		= explode('_', $dates['group']);
			$group_id 	= (int)$tmp[1];			
			$group 		= $this->getService('StudyGroup')->getById($group_id);
			$file_name .= '_'.$group->name;
		}
		$file_name .= '_'.date('d.m.Y_H-i');
		
        $xls = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
        fwrite($xls, $data);
        fclose($xls);
        
		$this->sendFile($subjectId, 'xls', $file_name);
		/**/
    	
    }
    
    public function sendFile($subjectId, $ext = 'doc', $name = null){
    	
        if ($subjectId) {
        	$name = $name ? $name : $subjectId;
            $options = array('filename' => $name.'.'.$ext);
            
            switch(true){
            	case $ext == 'doc':
            		$contentType = 'application/word';
            		break;
            	case $ext == 'xls':
            		$contentType = 'application/excel';
            		break;
            	case strpos($this->getRequest()->getHeader('user_agent'), 'opera'):
            		$contentType = 'application/x-download';
            		break;
				case $ext == 'pdf':
            		$contentType = 'application/pdf';
            		break;
            	default:
            		$contentType = 'application/unknown';
            }
            
            $this->_helper->SendFile(
				Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId,
				$contentType,
				$options
	        );
            die();
        }
        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    	
    }
	
	
	private function getMarksheetsInfo($subject)
	{
		$res = $this->getService('Marksheet')->getBySubjectCode($subject->external_id);
		if(!count($res)){ return false; }
		
		$data 					= array();
		$group_external_ids		= array();
		$student_mid_externals	= '';
		
		foreach($res as $i){
			$data[$i->marksheet_id] = $i;
			$group_external_ids[$i->group_external_id] = $i->group_external_id;
			$student_mid_externals .= ','.$i->students;			
		}
		$group_external_ids        = array_filter($group_external_ids);
		$student_mid_external_list = explode(',', $student_mid_externals);
		$student_mid_external_list = array_filter($student_mid_external_list);
		
		$group_list = array();
		if(!empty($group_external_ids)){
			$group_collection = $this->getService('StudyGroup')->getByCodes($group_external_ids);
			if(!empty($group_collection)){
				$group_list = $group_collection->getList('id_external', 'name');
			}			
		}
		
		$student_list = array();
		if(!empty($student_mid_external_list)){		
			$user_collection = $this->getService('User')->getByIdExternal($student_mid_external_list);
			if(!empty($user_collection)){
				foreach($user_collection as $i){
					$student_list[$i->mid_external] = $i->FirstName.' '.$i->LastName.' '.$i->Patronymic;				}
			}
		}
		
		foreach($data as $marksheet_id => $i){
			$i->group	= $group_list[$i->group_external_id];
			
			$ids 		= explode(',', $i->students);
			$ids 		= array_filter($ids);
			$students 	= array();
			foreach($ids as $mid_external){ $students[$mid_external] = $student_list[$mid_external]; }			
			$i->students = $students;
		}	
		return $data;
	}
	
    
}

