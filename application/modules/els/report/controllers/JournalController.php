<?php

class Report_JournalController extends HM_Controller_Action_Crud
{
	
	const CACHE_NAME = 'Report_JournalController';
	
	private $_users    = NULL;
	private	$_subjects = NULL;
	private $_lessons  = NULL;
	private $_groups   = NULL;
	
	public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет по журналу'));
    }
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                '_users'    => $this->_users,
				'_subjects' => $this->_subjects,
				'_lessons'  => $this->_lessons,
				'_groups'   => $this->_groups,
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
			$this->_users    = $actions['_users'];
			$this->_subjects = $actions['_subjects'];
			$this->_lessons  = $actions['_lessons'];
			$this->_groups   = $actions['_groups'];
            return true;
        }
        return false;
    }
	
	
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/content-modules/marksheet.css');		
        $this->view->headLink()->appendStylesheet($config->url->base.'/themes/rgsu/css/theme.css');		
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/content-modules/score.css');		
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');		
		$this->view->headLink()->appendStylesheet('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css');		
		$this->view->headScript()->appendFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js');
		$this->view->headScript()->appendFile($config->url->base.'/js/rgsu.js');
				
		$subjectList  = array();
		$journalTypes = array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_PRACTICE, HM_Event_EventModel::TYPE_JOURNAL_LAB);
		
		$this->clearCache();
		
		$form = new HM_Form_Journal();
		
		$this->view->form = $form;
	}
	
	# ФССЭиСТ-ТОР-Б-0-Д-2011-А
	# ИДО-СРБ-Б-19-З-2012-А
	# ЦРЭО-ФИН-М-2-З-2014-ВОБ
	# Тестовая программа
	# 16906 - занятие
	# ИН-ЯЗ
	public function getAction()
    {		
		$request    = $this->getRequest();        	
		$subjectId  = (int)$request->getParam('subjectId', 0);
		$groupId    = (int)$request->getParam('groupId', 0);
		$programmId = (int)$request->getParam('programmId', 0);
		
		if(empty($subjectId) && empty($groupId) && empty($programmId)){
			echo 'Выберите сессиию, группу или программу обучение';
			die;
		}
		
		$subjectIds = $this->getSubjectIds($subjectId, $groupId, $programmId);
		if(empty($subjectIds)){
			echo 'Не определены сессии';
			die;
		}
		
		
		$this->clearCache();
		
		try {
			$this->loadData($subjectIds);
		} catch (Exception $e) {
			echo $e->getMessage();
			die;
		}
		
		$this->saveToCache();
		
		$this->view->users          = $this->_users;
		$this->view->subjects       = $this->_subjects;
		$this->view->lessons        = $this->_lessons;		
		$this->view->groups         = $this->_groups;		
		$this->view->lessonTypeList = HM_Event_EventModel::getTypes();
		$this->view->subjectId      = $subjectId;
		$this->view->groupId        = $groupId;
		$this->view->programmId     = $programmId;
		
		try {
			echo $this->view->render('journal/get.tpl');
		} catch (Exception $e) {
			echo '<pre>';
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
			echo '</pre>';		
		}
		die;
	}
	
	private function getSubjectIds($subjectId, $groupId = false, $programmId = false)
	{
		if(!empty($subjectId)){
			return array($subjectId);
		}
		
		if(!empty($groupId)){
			$groupProgrammIds = $this->getService('StudyGroupProgramm')->fetchAll(
				$this->getService('StudyGroupProgramm')->quoteInto('group_id=?', $groupId)
			)->getList('programm_id');
			if(empty($groupProgrammIds)){
				return array();
			}
		}
		
		$programmIds = !empty($groupProgrammIds) ? $groupProgrammIds : array($programmId);
		
		return  $this->getService('ProgrammEvent')->fetchAll(
					$this->getService('ProgrammEvent')->quoteInto(array(' programm_id IN (?) ', ' AND type=? '), array($programmIds, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT))
				)->getList('item_id');
	}
	
	public function exportLessonAction()
	{
		$request   = $this->getRequest();        	
		$lessonId  = (int)$request->getParam('lesson_id', 0);
		$subjectId = (int)$request->getParam('subject_id', 0);
		
		if(empty($lessonId)){
			echo 'Не определено занятие';
			die;
		}
		
		if(empty($subjectId)){
			echo 'Не определена сессиия';
			die;
		}
		
		$this->restoreFromCache();
		
		if(empty($this->_lessons) || !$this->_lessons->exists('SHEID', $lessonId)){
			$this->clearCache();
			try {
				$this->loadData(array($subjectId));
				$this->saveToCache();
			} catch (Exception $e) {
				echo $e->getMessage();
				die;
			}	
		}
		
		$lesson = $this->_lessons->exists('SHEID', $lessonId);
		
		if(!$lesson){
			echo 'Не найдены данные по занятию №' . $lessonId;
			echo '<br>Сформируйте отчет заново';
			die;
		}
		
		$subject = $this->_subjects->exists('subid', $lesson->CID);
		
		$this->view->lesson   = $lesson;
		$this->view->users    = $this->_users;
		$this->view->subject  = $subject;
		$this->view->groups   = $this->_groups;
		$this->view->lesson   = $lesson;
		$content = $this->view->render('journal/export/excel.tpl');
		
		echo $this->getExportExcel($content, 'Отчет_по_журналу_' . $lesson->title);
		die;
	}
	
	private function sortUsers($a, $b)
	{
        return strcmp($a['studentName'], $b['studentName']);
	}
	
	private function sortDays($a, $b)
	{
        return $a['journalCreate'] > $b['journalCreate'];
	}
	
	public function getExportExcel($content, $name = 'report'){
		
		$file_name = $name.'_'.date('Y.m.d_H-i',time());
		
		set_time_limit( 0 );
		
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=UTF-8" )        
            ->setRawHeader( "Content-Disposition: attachment; filename=".$file_name.".xls" )
            ->setRawHeader( "Content-Transfer-Encoding: binary" )
            ->setRawHeader( "Content-Encoding: UTF-8" )
            ->setRawHeader( "Expires: 0" )
            ->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
            ->setRawHeader( "Pragma: public" )        
            ->sendResponse();
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $content;
		exit();
	}
	
	public function loadData($subjectIds)
	{
		if(empty($subjectIds)){
			throw new Exception('Сессия не определена');
			return false;
		}
		$userService       = $this->getService('User');
		$journalTypes      = array(HM_Event_EventModel::TYPE_JOURNAL_LECTURE, HM_Event_EventModel::TYPE_JOURNAL_PRACTICE, HM_Event_EventModel::TYPE_JOURNAL_LAB);
		$users             = new HM_Collection(array(), 'HM_User_UserModel');
		$subjects          = $this->getService('Subject')->fetchAll($userService->quoteInto('subid IN (?)', $subjectIds));
		$journalDaysCounts = array();
		
		if(!count($subjects)){
			throw new Exception('Сессия не найдена');
			return false;
		}
		
		foreach($subjects as $subject){
			$subject->practicMaxBall = $this->getService('LessonJournalResult')->getPracticMaxBall($subject->subid);
			$subject->lessons = new HM_Collection(array(), 'HM_Lesson_LessonModel');
		}
		
		$lessons = $this->getService('Lesson')->fetchAll($userService->quoteInto(array('CID IN (?)', ' AND typeID IN (?)'), array($subjects->getList('subid'), $journalTypes)), array('title'));		
		
		if(!count($lessons)){
			throw new Exception('Нет занятий с типом "Журнал"');
			return false;
		}
		
		foreach($lessons as $lesson){
			$subject = $subjects->exists('subid', $lesson->CID);
			if(!$subject){ continue; }
			
			$lesson->journalDaysCount = 0;
			
			$subject->lessons->offsetSet($subject->lessons->count(), $lesson);
		}
		
		# Если нужно удалить 2 элемента, идущих друг за другом, то один из них пропускается, т.к. происходит сдвиг индексов. Поэтому выполняется второй проход
		foreach($subjects as $offset => $subject){ if(!count($subject->lessons)){ $subjects->offsetUnset($offset); } }		
		foreach($subjects as $offset => $subject){ if(!count($subject->lessons)){ $subjects->offsetUnset($offset); } }
		
		if(!count($subjects)){
			throw new Exception('Нет занятий с типом "Журнал"');
			return false;
		}
		
		# уйдет в представление
		#$lesson->typeName         = $allTypes[$lesson->typeID];
		
		$days = $this->getService('LessonJournal')->fetchAll($userService->quoteInto(" lesson_id IN (?) AND (is_hidden=0 OR is_hidden IS NULL OR is_hidden='')", $lessons->getList('SHEID')), array('date_create'));
		if(!count($lessons)){
			throw new Exception('Нет данных в журнале');
			return false;
		}
		
		foreach($days as $day){
			$journalDaysCounts[$day->lesson_id] = $journalDaysCounts[$day->lesson_id] ? 1 + $journalDaysCounts[$day->lesson_id] : 1;
		}
		
		#$subjectProgrammItems = $this->getService('ProgrammEvent')->fetchAll(
		#	$this->getService('ProgrammEvent')->quoteInto(array(' item_id IN (?) ', ' AND type=? '), array($subjects->getList('subid'), HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT))
		#);
		
		foreach($subjects as $subject){
			#$subjectProgrammItem = $subjectProgrammItems->exists('item_id', $subject->subid);
			#$subject->programmId = $subjectProgrammItem ? $subjectProgrammItem->programm_id : false;
			
			foreach($subject->lessons as $lesson){
				$lesson->journalDaysCount = array_key_exists($lesson->SHEID, $journalDaysCounts) ? intval($journalDaysCounts[$lesson->SHEID])                 : 0;
				$lesson->dayWeightBall    = !empty($lesson->journalDaysCount)                    ? $subject->practicMaxBall/($lesson->journalDaysCount * 100) : 0;
				$lesson->dayMaxBall       = round($lesson->dayWeightBall*100, 2);
				$lesson->days             = new HM_Collection(array(), $days->getModelClass());
			}
		}
		
		#foreach($days as $day){
			#$lesson = $lessons->exists('SHEID', $day->lesson_id);			
			#$day->name       = strtotime($day->date_lesson)>0 ? date('d.m.Y', strtotime($day->date_lesson)) : $day->date_lesson; # перевести во view
			#$day->weightBall = ($lesson && !empty($lesson->journalDaysCount)) ? $subject->practicMaxBall/($lesson->journalDaysCount * 100) : 0;			
			#$day->maxBall    = round($day->weightBall*100, 2);			
			#$day->items      = new HM_Collection(array(), 'HM_Lesson_Journal_Result_ResultModel');
		#}
		
		$journalResults   = $this->getService('LessonJournalResult')->fetchAll($userService->quoteInto('journal_id IN (?)', $days->getList('journal_id')));
		$students         = $this->getService('Student')->fetchAll($userService->quoteInto('CID IN (?)', $subjects->getList('subid')));
		$graduated        = $this->getService('Graduated')->fetchAll($userService->quoteInto('CID IN (?)', $subjects->getList('subid')));
		$users            = $userService->fetchAll($userService->quoteInto('MID IN (?)', $students->getList('MID') + $graduated->getList('MID')), array('LastName','FirstName','Patronymic'));
		$lessonAssigns    = $this->getService('LessonAssign')->fetchAll($userService->quoteInto('SHEID IN (?)', $lessons->getList('SHEID')));
		$userGroupAssigns = $this->getService('StudyGroupUsers')->fetchAll($userService->quoteInto('user_id IN (?)', $students->getList('MID') + $graduated->getList('MID')));
		$groups           = $this->getService('StudyGroup')->fetchAll($userService->quoteInto('group_id IN (?)', $userGroupAssigns->getList('group_id')));
		
		foreach($users as $user){
			$userGroupAssign = $userGroupAssigns->exists('user_id', $user->MID);
			
			$user->isGraduated = $graduated->exists('MID', $user->MID) ? true                       : false;
			$user->groupId     = $userGroupAssign                      ? $userGroupAssign->group_id : false;
		}
		
		foreach($journalResults as $journalResult){
			$foundedLessonAssign = false;
			$day          = $days->exists('journal_id', $journalResult->journal_id);
			$day->journalResults = $day->journalResults ? $day->journalResults : new HM_Collection(array(), $journalResults->getModelClass());
			
			foreach($lessonAssigns as $lessonAssign){
				if($lessonAssign->MID == $journalResult->MID && $lessonAssign->SHEID == $journalResult->lesson_id){
					$foundedLessonAssign = $lessonAssign;
					break;
				}
			}
			
			$journalResult->ballAcademic = $foundedLessonAssign ? $foundedLessonAssign->ball_academic : 0;
			$journalResult->ballPractic  = $foundedLessonAssign ? $foundedLessonAssign->ball_practic  : 0;
			$journalResult->V_STATUS     = $foundedLessonAssign ? $foundedLessonAssign->V_STATUS      : 0;	
			
			$day->journalResults->offsetSet($day->journalResults->count(), $journalResult);
		}
		
		foreach($days as $day){
			foreach($subjects as $subject){
				$lesson = $subject->lessons->exists('SHEID', $day->lesson_id);
				if($lesson){ break; }
			}
			if(!$lesson){ continue; }			
			$lesson->days->offsetSet($lesson->days->count(), $day);
		}
		
		# для сокращения размера кэша, удаляем из моделей неиспользуемые поля. Если поля понудобятся во view, то нужно их вернуть в этом методе.
		$subjects = $this->clearData($subjects);
		
		$this->_users    = $users;
		$this->_subjects = $subjects;
		$this->_lessons  = $lessons;
		$this->_groups   = $groups;
		return true;
		
		
		#foreach($journalResults as $journalResult){
			#$day                = $days->exists('journal_id', $journalResult->journal_id);			
			#$lessonAssign = false;
			#foreach($lessonAssigns as $assign){
				#if($assign->MID == $journalResult->MID && $assign->SHEID == $journalResult->lesson_id){
				#	$lessonAssign = $assign;				
				#}
			#}			
			#$journalResult->isBe         = HM_Lesson_Journal_Result_ResultModel::getIsBeName($journalResult->isBe);
			#$journalResult->format_attendance = HM_Lesson_Journal_Result_ResultModel::getFormatAttendanceName($journalResult->format_attendance);
			#$journalResult->mark         = $journalResult->mark < 0 ? '' : $journalResult->mark;
			#$journalResult->ball         = $day          ? round($day->weightBall * $journalResult->mark, 2) : '';			
			#$journalResult->ballAcademic = $lessonAssign ? $lessonAssign->ball_academic             : 0;
			#$journalResult->ballPractic  = $lessonAssign ? $lessonAssign->ball_practic              : 0;
			#$journalResult->V_STATUS     = $lessonAssign ? $lessonAssign->V_STATUS                  : 0;
		#}
		
		#$this->_users   = $users;
		#$this->_subject = $subject;
		#$this->_lessons = $lessons;		
		#return true;
	}
	
	# Удаление неиспользуемых полей для уменьшения размера кэша этого объекта.
	private function clearData($subjects)
	{
		$unNecessaryFieldList = array(
			'HM_Subject_SubjectModel' => array(
				'code shortname', 'supplier_id', 'description', 'type reg_type', 'begin_planned', 'end_planned', 'longtime', 'price', 'price_currency', 'plan_users', 'services period',
				'period_restriction_type', 'last_updated', 'access_mode', 'access_elements', 'mode_free_limit', 'auto_done', 'base', 'base_id', 'base_color', 'claimant_process_id', 'state', 
				'default_uri', 'scale_id', 'auto_mark auto_graduate', 'formula_id', 'threshold', 'mark_type', 'chair', 'exam_type', 'learn', 'hours_total', 'classroom', 'self_study',
				'lection', 'lab', 'practice', 'exam', 'year_of_publishing', 'zet', 'learning_subject_id_external', 'time_ended_debt', 'isSheetPassed', 'isDO', 'begin_learning', 
				'language_code', 'module_code', 'semester', 'time_ended_debt_2', 'faculty', 'date_created', 'elective_mode', 'name_translation', 'shortname_translation', 'description_translation', 
				'isCommission', 'is_practice', 'module_name', 'practice_begin', 'practice_end', 'mark_current', 'mark_landmark', 'practicMaxBall',
			),
			'HM_Lesson_LessonModel' => array(			
				'url', 'descript', 'begin', 'end', 'createID', 'createDate', 'vedomost', 'CHID', 'startday', 'stopday', 'timetype', 'isgroup', 'cond_sheid', 'cond_mark', 'cond_progress', 'cond_avgbal', 'cond_sumbal',
				'cond_operation', 'period', 'rid', 'gid', 'teacher', 'moderator', 'pub', 'sharepointId', 'connectId', 'recommend', 'notice', 'notice_days', 'all', 'perm', 'params', 'activities', 'order', 'tool', 
				'isfree', 'section_id', 'session_id', 'formula_penalty_id', 'required', 'allowTutors', 'isCanMarkAlways', 'parent_lesson_id', 'isSection', 'title_translation', 'descript_translation', 'isCanSetMark',
			),
			'HM_Lesson_Journal_JournalModel' => array(
				'is_hidden',
			),
			'HM_Lesson_Journal_Result_ResultModel'  => array(				
				'date_create', 'date_update',
			),
		);
		
		$unNecessarySubjectFields = $unNecessaryFieldList[$subjects->getModelClass()];		
		foreach($subjects as $subject){
			foreach($unNecessarySubjectFields as $fieldName){
				unset($subject->{$fieldName});
			}
			$lessons = $subject->lessons;
			if($lessons){
				$unNecessaryLessonFields = $unNecessaryFieldList[$lessons->getModelClass()];
				foreach($lessons as $lesson){
					foreach($unNecessaryLessonFields as $fieldName){
						unset($lesson->{$fieldName});
					}
					$days = $lesson->days;
					if($days){
						$unNecessaryDayFields = $unNecessaryFieldList[$days->getModelClass()];
						foreach($days as $day){
							foreach($unNecessaryDayFields as $fieldName){
								unset($day->{$fieldName});
							}
							$journalResults = $day->journalResults;
							if($journalResults){
								$unNecessaryJournalResultFields = $unNecessaryFieldList[$journalResults->getModelClass()];
								foreach($journalResults as $journalResult){
									foreach($unNecessaryJournalResultFields as $fieldName){
										unset($journalResult->{$fieldName});
									}									
								}								
							}
						}
					}					
				}
			}
		}
		return $subjects;
	}
	
	public function exportToArchiveAction()
	{
		$zipName    = 'Отчет по новостям.';
		$request    = $this->getRequest();        	
		$lessonId   = (int)$request->getParam('lesson_id', 0);
		$subjectId  = (int)$request->getParam('subject_id', 0);
		$groupId    = (int)$request->getParam('group_id', 0);
		$programmId = (int)$request->getParam('programm_id', 0);
		
		if(empty($subjectId) && empty($groupId) && empty($programmId)){
			echo 'Не заданы параметры: "занятие", "сессия", "группа" или "программа обучения"';
			die;
		}
		
		$subjectIds = $this->getSubjectIds($subjectId, $groupId, $programmId);
		if(empty($subjectIds)){
			echo 'Не определены сессии';
			die;
		}
		
		if(!empty($subjectId)){
			$subject  = $this->getService('Subject')->getById($subjectId);
			$zipName .= ' Сессия ' . $subject->name;
			
		} elseif(!empty($groupId)){
			$group = $this->getService('StudyGroup')->getById($groupId);
			$zipName .= ' Группа ' . $group->name;
			
		} elseif(!empty($programmId)){
			$programm = $this->getService('Programm')->getById($programmId);
			$zipName .= ' Программа ' . $programm->name;
		}
		
		$zipName .= '. От ' . date('d.m.Y H:i');
		
		$this->restoreFromCache();
		
		$cacheSubjectIds  = $this->_subjects ? $this->_subjects->getList('subid') : array();
		$commonSubjectIds = array_intersect($cacheSubjectIds, $subjectIds);
		
		# Если в кэше есть хотябы одна требуемая сессия, значит это искомые данные, просто все остлаьные были отброшены, например, по причине отсутствия в них занятий "Журнал".
		# Иначе формируем данные в кэш заново.
		if(empty($commonSubjectIds)){
			$this->clearCache();
			try {
				$this->loadData($subjectIds);
				$this->saveToCache();
			} catch (Exception $e) {
				echo $e->getMessage();
				die;
			}
		}
		
		if(!count($this->_subjects)){
			echo 'Не сессий для выгрузки';
			die;
		}
		
		$serviceZip = $this->getService('FilesZip');
		if(!$serviceZip->createZip()){
			echo _('Не удалось создать архив');
			die;
		}
		
		$this->view->users  = $this->_users;
		$this->view->groups = $this->_groups;
		
		$isEmptyArchive = true;
		foreach($this->_subjects as $sKey => $subject){
			if(!$subject->lessons){ continue; }
			
			$this->view->subject = $subject;
			
			foreach($subject->lessons as $lKey => $lesson){
				$fileName      = ($sKey+1) . '. ' . $subject->name . '/' . ($lKey+1) . '. ' . $lesson->title . '.xls';
				
				$this->view->lesson  = $lesson;
				$lessonContent = $this->view->render('journal/export/excel/_lesson.tpl');
				
				if(!$serviceZip->addFileFromContentToZip($fileName, $lessonContent)){
					continue;
				}
				$isEmptyArchive = false;							
			}
		}
		
		if($isEmptyArchive){
			echo 'Архив пуст';
			die;
		}
		
		$zipPath = $serviceZip->getZipPath();
		$serviceZip->close();
		
		$serviceZip->sendZip($zipPath, $zipName);
		die;
	}
	
}