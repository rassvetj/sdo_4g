<?php
class Marksheet_GraduateController extends HM_Controller_Action
{
    /**
	 * TODO: для модульных должна браться интегральная, а не обычный Итоговый текущий рейтинг.
	*/
	public function indexAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
				
		$person 				= $this->_getParam('person', 0);
        $subject_id 			= $this->_getParam('subject_id', 0);
		
		$serviceSubject 		= $this->getService('Subject');
		$serviceUser			= $this->getService('User');
		$current_Tutor_id		= $serviceUser->getCurrentUserId();
		
		
		
		if(!$serviceSubject->isTutor($subject_id, $current_Tutor_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Вы не являетесь тьютором на курсе.'))
			);	
			$this->_redirect('/');	
		}
		
		
		# А также нет непроверенных работ: 
		if($serviceSubject->isNewActionStudent($subject_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('У Вас есть неотвеченные сообщения студентов в занятии.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		
		
		$isMainModule 			= $serviceSubject->isMainModule($subject_id);
		$subject				= $serviceSubject->getById($subject_id);
		
		# берем оценку студента и имя урока и разделяем на 2 оценки.
		# или выставляем признак для забора данных в 1С и выставляем полученные оценки.
		try {
		
			$serviceMark		 = $this->getService('SubjectMark');			
			$lessonAssignService = $this->getService('LessonAssign');
			
			
			/*
			$collection = $this->getService('Lesson')->fetchAllDependenceJoinInner(
				'Assign',
				$this->getService('Lesson')->quoteInto(array('self.CID  = ?', ' AND self.vedomost = ?', ' AND isfree = ?'), array($courseId, 1, HM_Lesson_LessonModel::MODE_PLAN)),
				'self.order'
			);
			
			$dataRatingMedium = array();
			$dataRatingTotal = array();
			if (count($collection)) {
				foreach($collection as $item) {
					if($item->typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE || $item->typeID == HM_Event_EventModel::TYPE_JOURNAL_LAB){
						$isTotalRating = false;					
					} else {
						$isTotalRating = ( (stristr($item->title, 'Итоговый тест') !== FALSE) || (stristr($item->title, 'Итоговый контроль') !== FALSE) ) ? (true) : (false);
					}
					$assigns = $item->getAssigns();
					if ($assigns) {
						foreach($assigns as $assign) {
							if($assign->V_STATUS > 0) {
								if($item->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){ # журнал - практическое занятие
									if(isset($dataRatingTotal[$assign->MID])){
										$dataRatingTotal[$assign->MID] = $dataRatingTotal[$assign->MID] + $assign->ball_practic;
									} else {
										$dataRatingTotal[$assign->MID] = $assign->ball_practic;
									}	
									if(isset($dataRatingMedium[$assign->MID])){
										$dataRatingMedium[$assign->MID] = $dataRatingMedium[$assign->MID] + $assign->ball_academic + $assign->ball_promotion;
									} else {
										$dataRatingMedium[$assign->MID] = $assign->ball_academic + $assign->ball_promotion;
									}									
								} elseif($isTotalRating){ 	# Рубежный рейтинг							
									if(isset($dataRatingTotal[$assign->MID])){
										$dataRatingTotal[$assign->MID] = $dataRatingTotal[$assign->MID] + $assign->V_STATUS;
									} else {
										$dataRatingTotal[$assign->MID] = $assign->V_STATUS;
									}								
								} else { # Итоговый текущий рейтинг
									if(isset($dataRatingMedium[$assign->MID])){
										$dataRatingMedium[$assign->MID] = $dataRatingMedium[$assign->MID] + $assign->V_STATUS;
									} else {
										$dataRatingMedium[$assign->MID] = $assign->V_STATUS;
									}
								}
							}
						}
					}					
				}
			}
			*/
			
			
			$serviceJResult = $this->getService('LessonJournalResult');
			# перевод в прощедшее + расчет оценки.
			foreach($person as $user_id => $value) {
				$marks = $serviceJResult->getRatingSeparated($subject_id, $user_id);				
				#$serviceSubject->assignGraduated($subject_id, $user_id);
				
				$mark_current 			= round($marks['medium']);
				$mark_landmark			= round($marks['total']);
				
				$failPassMessage = $serviceSubject->getFailPassMessage($user_id, $subject_id, true);
				if(!empty($failPassMessage)){
					$mark_landmark = 0;
				}
				
				# Для модульной сессии берем не Итоговый текущий рейтинг, а Интегральный текущий рейтинг
				if($isMainModule){			
					$integrateMediumRating	= $serviceSubject->getIntegrateMediumRating($subject->module_code, $user_id, $subject->semester);
					$mark_current 			= round($integrateMediumRating);
				}	
				
				
				$data = array(
					'need_to_1c'	=> 1,
					'mark_current'	=> $mark_current,
					'mark_landmark' => $mark_landmark,					
				);
				$where = array(
					'cid = ?' => $subject_id,
					'mid = ?' => $user_id
				);
				
				$res = $serviceMark->fetchAll($serviceMark->quoteInto(array('cid = ?', ' AND mid = ?'), array($subject_id, $user_id)));
				if($res){		
					$lessonAssignService->onLessonScoreChanged($subject_id, $user_id);					
				} 			
				$serviceMark->updateWhere($data, $where); 
			}
   
		} catch (Exception $e) {
			#$this->_flashMessenger->addMessage(_('Выброшено исключение: '.$e->getMessage()));	
		}
		
		$this->_flashMessenger->addMessage(_('Выбранные студенты завершли обучение'));		
		die;
	}
	
	
	/**
	 * Индивидуальная ведомость на одного студента с переводом в прошедшее
	 * Учитывает модульную димциплину. В этом случае расчитывает интегральную оценку.	 
	*/
	public function individualVedomostAction(){
		
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
				
		$user_id 	= (int)$this->_getParam('user_id', 0);
        $subject_id = (int)$this->_getParam('subject_id', 0);
		
		$serviceSubject 		= $this->getService('Subject');
		$serviceUser			= $this->getService('User');
		$current_Tutor_id		= $serviceUser->getCurrentUserId();
				
		if($serviceSubject->isDOT($subject_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Нельзя завершать курс для данного типа сессии.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		
		
		if(!$serviceSubject->isStudent($subject_id, $user_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Студент не назначен на курс или его завершил.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		
		if(!$serviceSubject->isTutor($subject_id, $current_Tutor_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Вы не являетесь тьютором на курсе.'))
			);	
			$this->_redirect('/');	
		}
		
		$subject				= $serviceSubject->getById($subject_id);
		if(empty($subject->time_ended_debt) && empty($subject->time_ended_debt_2)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Индивидуальное направление можно формировать только для продленных сессий.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		
		
		# проверить, что студент учится и что он доступен данному тьютору.
		$students 				=  $serviceSubject->getAvailableStudents($current_Tutor_id, $subject_id);		
		if( is_array($students) && !in_array($user_id, $students)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Этот студент недоступен Вам.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}		
		
		
		# А также нет непроверенных работ: 
		if($serviceSubject->isNewActionStudent($subject_id, array($user_id))){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Ответье не сообщение студента в занятии.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		
		
		$serviceJResult 		= $this->getService('LessonJournalResult');
		
		$serviceMark			= $this->getService('SubjectMark');
		$serviceLessonAssign 	= $this->getService('LessonAssign');
		$serviceLesson			= $this->getService('Lesson');
		$serviceSGUsers			= $this->getService('StudyGroupUsers');
		
		$exam_types 			= HM_Subject_SubjectModel::getExamTypes();
		$student 				= $serviceUser->getById($user_id);
		$recordBookNumbers 		= $this->getService('RecordCard')->getRecordbookNumbers($user_id);
		
		
		
		$isMainModule 			= $serviceSubject->isMainModule($subject_id);
		
		
		$marks 					= $serviceJResult->getRatingSeparated($subject_id, $user_id);		
		$serviceSubject->assignGraduated($subject_id, $user_id);
		$mark_current 			= round($marks['medium']);
		$mark_landmark			= round($marks['total']);
		
		$failPassMessage = $serviceSubject->getFailPassMessage($user_id, $subject_id, true);
		if(!empty($failPassMessage)){
			$mark_landmark = 0;
		}
		
		# Для модульной сессии берем не Итоговый текущий рейтинг, а Интегральный текущий рейтинг
		if($isMainModule){			
			$integrateMediumRating	= $serviceSubject->getIntegrateMediumRating($subject->module_code, $user_id, $subject->semester);
			$mark_current 			= round($integrateMediumRating);
		}		
				
		$data = array(
			'need_to_1c'	=> 1,
			'mark_current'	=> $mark_current,
			'mark_landmark' => $mark_landmark,
			'sync_1c'		=> 0, # для случая, когда данные ушли в 1С, флаг выставился, сессия продлилась и студент снова назначился на сессию. И чтобы снова данные ушли в 1С, нужно флаг сбросить			
		);
		$where = array(
			'cid = ?' => $subject_id,
			'mid = ?' => $user_id
		);
		
		$res = $serviceMark->fetchAll($serviceMark->quoteInto(array('cid = ?', ' AND mid = ?'), array($subject_id, $user_id)));
		if($res){		
			$serviceLessonAssign->onLessonScoreChanged($subject_id, $user_id);					
		} 			
		$serviceMark->updateWhere($data, $where);

		# Получеем все занития
		$lessons = $serviceLesson->fetchAll(            
            $serviceLesson->quoteInto(array('CID  = ?', ' AND vedomost = ?', ' AND isfree = ?'), array($subject_id, 1, HM_Lesson_LessonModel::MODE_PLAN))  
        );
		
		$marks = $serviceLessonAssign->fetchAll(            
            $serviceLessonAssign->quoteInto(array('MID = ?', ' AND SHEID IN (?)'), array($user_id, $lessons->getList('SHEID')))
        )->getList('SHEID', 'V_STATUS');
		
		
		$isHasFailInModule = false;
		if($isMainModule){
			$user_IDs = array($user_id);
			
			# получаем список студентов и только по ним находим данные по доп. модулю.						
			$moduleData = $this->getService('Subject')->getModuleData($subject_id, $user_IDs);	
			
			foreach($moduleData['subjects'] as $module_subject_id => $subject_name) {
				if($isHasFailInModule === false && $moduleData['additional'][$module_subject_id]['is_fail_module'][$user_id]){
					$isHasFailInModule = true;
				}
			}
		}
		
		
		
		
		if(empty($mark_landmark)){
			$mark_5 = 0; # неявка
		
		# не преодолен порог в 65% по Рубежному рейтингу
		}elseif(	!$serviceLesson->isPassTotalRating($serviceLesson->getMaxBallTotalRating($subject_id), $mark_landmark, $subject->isDO, $subject->is_practice) || $isHasFailInModule	){
			$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
		}else{
			$mark_5 = $serviceLesson->getFiveScaleMark($mark_current + $mark_landmark);
		}
		$this->view->mark_5 			= $mark_5;		
		$this->view->mark_5_text 		= $serviceLesson->getTextFiveScaleMark($mark_5, $subject->exam_type);
		$this->view->student			= $student;
		
		$this->view->recordBookNumber 	= $recordBookNumbers[$user_id];
		$this->view->exam_type_name 	= $exam_types[$subject->exam_type];
		$this->view->subject			= $subject;
		$this->view->isModuleSubject	= empty($subject->module_code) ? false : true;
		
		$this->view->lessons			= $lessons;
		$this->view->lesson_marks		= $marks;
		$this->view->mark_current		= $mark_current;
		$this->view->mark_landmark		= $mark_landmark;
		$this->view->mark_total			= $mark_current + $mark_landmark;
		
		
		$info = $this->getService('Marksheet')->getInfo($subject_id, $user_id);
		
		$this->view->marksheet_external_id	= !empty($info->external_id) 	? $info->external_id 	: '';
		$this->view->faculty				= !empty($info->faculty) 		? $info->faculty 		: ''; #$subject->faculty;		
		#$this->view->study_form 			= !empty($info->study_form) 	? $info->study_form 	: '';
		$this->view->semester 				= !empty($info->semester) 		? $info->semester 		: ''; #$subject->semester;
		$this->view->course 				= !empty($info->course) 		? $info->course 		: '';
		$this->view->years 					= !empty($info->year) 			? $info->year 			: ''; #$subject->year_of_publishing;
		$this->view->dean 					= !empty($info->dean) 			? $info->dean 			: '';
		$this->view->attempt 				= !empty($info->attempt) 		? $info->attempt 		: '';
		$this->view->study_base 			= !empty($info->study_base)		? $info->study_base 	: '';
		$this->view->rating 				= !empty($info->rating)			? $info->rating 		: '';
		$this->view->form_control 			= !empty($info->form_control)	? $info->form_control 	: '';
		$this->view->date_issue 			= !empty($info->date_issue)		? date('d.m.Y', strtotime($info->date_issue)) 	: '';
		$this->view->theme_type 			= !empty($info->theme_type)		? $info->theme_type		: '';
		$this->view->theme		 			= !empty($info->theme)			? $info->theme			: '';
		$this->view->tutor		 			= !empty($info->tutor)			? $info->tutor			: '';
		$this->view->form_study 			= !empty($info->form_study)		? $info->form_study 	: '';
		
		
		
		#Имя файла, выводимое для тьютора
		$doc_name						= date('d.m.Y').'-'.$subject->name.'. ИН ('.$student->LastName.' '.$student->FirstName.' '.$student->Patronymic.').pdf';
		
		
		$res	= $serviceSGUsers->getUserGroups($user_id);
		$groups = array();
		foreach($res as $row){
			$groups[$row['group_id']] = $row['name'];			
		}		
		$this->view->groups = empty($groups) ? '' : implode(', ', $groups);		
		
		/*
		# Для ИН берем всех тьюторов? Или текущего?		
		$res 	= $serviceSubject->getAssignedTutors($subject_id);
		$tutors = array();
		foreach($res as $row){
			$fio = $row->LastName.' '.$row->FirstName.' '.$row->Patronymic;
			#if($current_Tutor_id == $row->MID){
				#$tutors = array($fio);
				#break;
			#}
			$tutors[] = $fio;
		}
		*/
		
		#$this->view->tutors = empty($tutors) ? '' : implode(', ', $tutors);
		
		$content 						=  $this->view->render('graduate/export/individual-vedomost-pdf.tpl');
		
		require_once("dompdf/dompdf_config.inc.php");
		$content = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$content.'</html>';				
		$dompdf = new DOMPDF();
		#$dompdf->set_paper('letter', 'landscape');
		$dompdf->load_html($content);
		
		
		try {			
			$dompdf->render();		
		
		} catch (Exception $e) {
			
			$customPaper = array(0,0,580,790);		
			$dompdf->set_paper($customPaper,'landscape');
		}
		
		
		
		$output = $dompdf->output();
		
		$inserted = $this->getService('FilesMarksheet')->addFileFromBinary(
			$output,
			$doc_name, 
			$subject_id,
			array(
				'author_id' 			=> $current_Tutor_id,
				'ext'					=> 'pdf',
				'student_id'			=> $user_id,
				'marksheet_external_id'	=> $info->external_id,
			)
		);
		
		if(empty($inserted->file_id)){
			# редирект назад в ведомость 
			# вывести сообщение об ошибке
		}
		
		$this->_helper->redirector->gotoSimple('index', 'get', 'marksheet', array('file_id' => $inserted->file_id));		
	}
	
	/**
	 * Завершить курс. Формирует ведомость и переводит студентов, которые отображаются на странице в прошедшее.
	 * Реализовать через кэш? А если открыть две разные вкладки одной сессии с разными отборами? Тогда сделать кэшь с ключем условия отбора по группе.
	*/
	public function vedomostAction(){
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$serviceSubject 		= $this->getService('Subject');
		$serviceUser			= $this->getService('User');
		$serviceLesson			= $this->getService('Lesson');
		$serviceSGUsers			= $this->getService('StudyGroupUsers');
		$serviceJResult 		= $this->getService('LessonJournalResult');
		$serviceMark			= $this->getService('SubjectMark');
		$serviceLessonAssign 	= $this->getService('LessonAssign');
		$serviceRecordCard		= $this->getService('RecordCard');
		
    	$subject_id 			= (int) $this->_getParam('subject_id', 0);        
		$current_Tutor_id		= $serviceUser->getCurrentUserId();
		
		/*
		if($serviceSubject->isDOT($subject_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Нельзя завершать курс для данного типа сессии.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		*/
		
		
		if(!$serviceSubject->isTutor($subject_id, $current_Tutor_id)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Вы не являетесь тьютором на курсе.'))
			);	
			$this->_redirect('/');	
		}
		
		
		
		$subject				= $serviceSubject->getById($subject_id);		
		/*
		# теперь более нет понятия индивидуальных направлений. Для продленных сессий формируем полноценную ведомость, как для обычных сессий
		if(!empty($subject->time_ended_debt) || !empty($subject->time_ended_debt_2)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Нельзя завершать продленный курс.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		*/
		
		
		
		# ограничение для тьюторов по назначенным на них студентов
		if ($this->getService('Acl')->inheritsRole($serviceUser->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
			$studentIDs = $serviceSubject->getAvailableStudents($serviceUser->getCurrentUserId(), $subject_id);				
		}
		
		
		
		
		$isMainModule	= $serviceSubject->isMainModule($subject_id);
		#$subject		= $serviceSubject->getById($subject_id);
        $dates 			= Zend_Registry::get('session_namespace_default')->marksheetFilter[$subject_id];
		$group			= $dates['group'];
		$groupUsers 	= array();
		
		$all_groups 	= array();
		
		$maxBallTotalRating = $serviceLesson->getMaxBallTotalRating($subject_id);
		$recordBookNumbers 	= $serviceRecordCard->getRecordbookNumbers();
		$exam_types 		= HM_Subject_SubjectModel::getExamTypes();

		if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){
			$maxBallTotalRating = HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT;
		}
		
		$group_id_external 	= false;
		# отбор по группе
		if ($group !== null ) {
			$group = explode ('_',$group);
			
			$group_id			= (int)$group[1];			
			if(!empty($group_id)){			
				$res_group			= $this->getService('StudyGroup')->getOne($this->getService('StudyGroup')->find($group_id));
				$group_id_external	= $res_group->id_external;			
			}

			/* Параметр Учебная группа */
			if ($group[0] == 'sg') {
				$users = $serviceSGUsers->getUsersOnCourse((int)$group[1],$subject_id);
				$users_custom = $serviceSGUsers->getUsersOnCourseCustom((int)$group[1],$subject_id);					
				if (count($users)) {
					foreach ($users as $user) {
						$groupUsers[] = $user['user_id'];
					}
				}
				if (count($users_custom)) {
					foreach ($users_custom as $user) {
						$groupUsers[] = $user['user_id'];
					}
				}
			}
			/* Параметр Подгруппа */
			if ($group[0] == 's') {
				$users = $this->getService('GroupAssign')->fetchAll(array('gid=?' => (int)$group[1], 'cid=?' => $subject_id));
				if (count($users)) {
					$groupUsers = array_keys($users->getList('mid','gid'));
				}
			}

			/* Если лдевый параметр удаляем переменную */
			if ($group[0] != 's' && $group[0] != 'sg') {
				unset($group);
			}
		}
		
		if($studentIDs === false){ # доступны все
			$filter_user_ids = $groupUsers;
		} else {
			$filter_user_ids = array_intersect($studentIDs, $groupUsers);
		}
		
		
		# А также нет непроверенных работ: 
		if($serviceSubject->isNewActionStudent($subject_id, $filter_user_ids)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('У Вас есть неотвеченные сообщения студентов в занятии.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));
		}
		
		# студенты
		$students 	= $serviceUser->fetchAllDependenceJoinInner('Student', $serviceUser->quoteInto('Student.CID = ?', $subject_id));		
		
		$persons 	= array();		
		
		/*
		# ограничение для тьюторов по назначенным на них студентов
		if ($this->getService('Acl')->inheritsRole($serviceUser->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
			$studentIDs = $serviceSubject->getAvailableStudents($serviceUser->getCurrentUserId(), $subject_id);				
		}
		*/
		
		if(!count($students) || empty($students)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('В курсе нет назначенных студентов или все завершили обучение.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));			
		}
		
		if($isMainModule){
			$user_IDs = array();
			foreach($students as $student){
				$user_IDs[$student->MID] = $student->MID;
			}
		
			# получаем список студентов и только по ним находим данные по доп. модулю.						
			$moduleData = $this->getService('Subject')->getModuleData($subject_id, $user_IDs);	
		}
		
		
		# программа сессии
		$subject_programm_ids = $this->getService('ProgrammEvent')->getSubjectProgramms($subject_id);
		if(!empty($subject_programm_ids)){
			$subject_programms = $this->getService('Programm')->fetchAll($this->getService('Programm')->quoteInto(
				"programm_id IN (?) AND (name LIKE 'КЛН-%' OR name LIKE 'МИН-%' OR name LIKE 'ОШ-%' OR name LIKE 'ПАВ-%')", $subject_programm_ids)
			);
		}
		
		$this->view->isProgrammFilial = false;
		if(count($subject_programms) > 0){
			$this->view->isProgrammFilial = true;
		}
		
		$midWithHalfAccess         = $this->getService('UserInfo')->getMidWithHalfAccess();
		$assignGraduatedStudentIds = array();
		foreach($students as $student){
			if($student->blocked == HM_User_UserModel::STATUS_BLOCKED){ continue; }
			
			if($studentIDs !== false && is_array($studentIDs)){	
				if(!in_array($student->MID, $studentIDs)){ continue; }					
			}				
			if ( !in_array($student->MID,$groupUsers) && $group ) continue;


			if(!empty($midWithHalfAccess) && in_array($student->MID, $midWithHalfAccess)){
				continue;
			}

			$assignGraduatedStudentIds[$student->MID] = $student->MID; # фактически искомые студенты, которых нужно перевести в завершенные.
			
			# расчет оценок с разделением на Итоговый текущий рейтинг  и Рубежный рейтинг
			$marks 					= $serviceJResult->getRatingSeparated($subject_id, $student->MID);
			$serviceSubject->assignGraduated($subject_id, $student->MID);
			$mark_current 			= round($marks['medium']);
			$mark_landmark			= round($marks['total']);
			
			$failPassMessage = $serviceSubject->getFailPassMessage($student->MID, $subject_id, true);
			if(!empty($failPassMessage)){
				$mark_landmark = 0;
			}
		
			# Для модульной сессии берем не Итоговый текущий рейтинг, а Интегральный текущий рейтинг
			if($isMainModule){			
				$integrateMediumRating	= $serviceSubject->getIntegrateMediumRating($subject->module_code, $student->MID, $subject->semester);
				$mark_current 			= round($integrateMediumRating);
			}		
				
			$data = array(
				'need_to_1c'	=> 1,
				'mark_current'	=> $mark_current,
				'mark_landmark' => $mark_landmark,
				'sync_1c'		=> 0, # для случая, когда данные ушли в 1С, флаг выставился, сессия продлилась и студент снова назначился на сессию. И чтобы снова данные ушли в 1С, нужно флаг сбросить
			);
			$where = array(
				'cid = ?' => $subject_id,
				'mid = ?' => $student->MID,
			);
			
			$res = $serviceMark->fetchAll($serviceMark->quoteInto(array('cid = ?', ' AND mid = ?'), array($subject_id, $student->MID)));
			if($res){		
				# Зачем тут это?
				$serviceLessonAssign->onLessonScoreChanged($subject_id, $student->MID);					
			} 			
			$serviceMark->updateWhere($data, $where);
			
			
			
			$isHasFailInModule = false; # есть ли причины недопуска.
				
			if($isMainModule){	
				foreach($moduleData['subjects'] as $module_subject_id => $subject_name) {
					if($isHasFailInModule === false && $moduleData['additional'][$module_subject_id]['is_fail_module'][$student->MID]){
						$isHasFailInModule = true;
					}
				}
			}
			
			
			
			if(empty($mark_landmark)){
				$mark_5 = 0; # неявка
				
			# не преодолен порог в 65% по Рубежному рейтингу
			}elseif(	!$serviceLesson->isPassTotalRating($maxBallTotalRating, $mark_landmark, $subject->isDO, $subject->is_practice) || $isHasFailInModule	){
				$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
				
			}else{
				$mark_5 = $serviceLesson->getFiveScaleMark($mark_current + $mark_landmark);
			}	
			
			
			$student->mark_5_text 		= $serviceLesson->getTextFiveScaleMark($mark_5, $subject->exam_type);
			$student->mark_current 		= $mark_current;
			$student->mark_landmark 	= $mark_landmark;
			$student->mark_total		= $mark_current + $mark_landmark;
			
			$student->studyGroups 		= $serviceSGUsers->getUserGroups($student->MID);			
			$student->recordBookNumber 	= $recordBookNumbers[$student->MID];
			
			
			foreach($student->studyGroups as $row){
				$all_groups[$row['group_id']] = $row['name'];			
			}			
			
			
			
			$persons[$student->MID] = $student;			
		}
		
		
		# для модульных сессий оценка не вычисляется. Только завершаем сессию. Вычисление сделано выше только для главного модуля.
		if($isMainModule){
			$moduleSubjects = $serviceSubject->getModuleSubjects($subject->module_code, $subject->semester);
			foreach($moduleSubjects as $moduleSubject){
				if($subject_id == $moduleSubject->subid){ continue; }
				
				foreach($assignGraduatedStudentIds as $studentId){
					$serviceSubject->assignGraduated($moduleSubject->subid, $studentId);
				}
			}
		}
		
		
		if(empty($persons)){
			$this->_flashMessenger->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('В курсе нет назначенных студентов или все завершили обучение.'))
			);	
			$this->_helper->redirector->gotoSimple('index', 'index', 'marksheet', array('subject_id' => $subject_id));			
		}
		
		@uasort($persons, array($serviceLesson,'userCompare'));
		
	
		
		
		
		# Получеем все занития
		$lessons = $serviceLesson->fetchAll(            
            $serviceLesson->quoteInto(array('CID  = ?', ' AND vedomost = ?', ' AND isfree = ?'), array($subject_id, 1, HM_Lesson_LessonModel::MODE_PLAN))  
        );
		
		/*
		$res 	= $serviceSubject->getAssignedTutors($subject_id);
		$tutors = array();
		foreach($res as $row){
			$tutors[] = $row->LastName.' '.$row->FirstName.' '.$row->Patronymic;
		}
		*/
		
		#$this->view->tutors 			= empty($tutors) ? '' : implode(', ', $tutors);		
		$this->view->persons			= $persons;
		
		$this->view->groups 			= empty($all_groups) ? '' : implode(', ', $all_groups);	
		$this->view->exam_type_name 	= $exam_types[$subject->exam_type];
		$this->view->subject			= $subject;
		$this->view->isModuleSubject	= empty($subject->module_code) ? false : true;		
		$this->view->lessons			= $lessons;
		
		
		
		
		$info = $this->getService('Marksheet')->getInfo($subject_id, false, $group_id_external);
		
		#Имя файла, выводимое для тьютора
		$doc_name = $info->external_id . '-' . date('d.m.Y') . '-' . $subject->name.'.pdf';
		
		$this->view->marksheet_external_id	= !empty($info->external_id) 	? $info->external_id 	: '';
		$this->view->faculty				= !empty($info->faculty) 		? $info->faculty 		: ''; #$subject->faculty;		
		#$this->view->study_form 			= !empty($info->study_form) 	? $info->study_form 	: '';
		$this->view->semester 				= !empty($info->semester) 		? $info->semester 		: ''; #$subject->semester;
		$this->view->course 				= !empty($info->course) 		? $info->course 		: '';
		$this->view->years 					= !empty($info->year) 			? $info->year 			: ''; #$subject->year_of_publishing;
		$this->view->dean 					= !empty($info->dean) 			? $info->dean 			: '';
		$this->view->tutor 					= !empty($info->tutor) 			? $info->tutor 			: '';
		$this->view->form_study 			= !empty($info->form_study)		? $info->form_study 	: '';
		$this->view->date_issue 			= !empty($info->date_issue)		? date('d.m.Y', strtotime($info->date_issue)) 	: '';
		$this->view->attempt 				= !empty($info->attempt)		? $info->attempt 		: '';
		
		$content 							=  $this->view->render('graduate/export/vedomost-pdf.tpl');
		
		try {		
			require_once("dompdf/dompdf_config.inc.php");
			$content = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$content.'</html>';				
			$dompdf = new DOMPDF();
			
			$customPaper = array(0,0,580,790);		
			$dompdf->set_paper($customPaper,'landscape');
			
			# со стандартным размером "letter" => array(0,0,612.00,792.00) возникает баг в библиотеке, из-за кторого она падает. Зацикливается перенос ячейки
			#$dompdf->set_paper('letter', 'landscape');
			
			
			$dompdf->load_html($content);
			
			
			try {			
				$dompdf->render();		
				
			} catch (Exception $e) {
			
				$customPaper = array(0,0,580,790);		
				$dompdf->set_paper($customPaper,'landscape');
			}
			
			
			
			
			$output = $dompdf->output();			
		} catch (Exception $e) {
			echo 'Не удалось сформировать файл. ',  $e->getMessage(), "\n";
			die;
		}
		
		

		
		$inserted = $this->getService('FilesMarksheet')->addFileFromBinary(
			$output,
			$doc_name, 
			$subject_id,
			array(
				'author_id' 			=> $serviceUser->getCurrentUserId(),
				'ext'					=> 'pdf',				
				'group_id'				=> $group_id,
				'marksheet_external_id'	=> $info->external_id,
			)
		);
			
		if(empty($inserted->file_id)){
			# редирект назад в ведомость 
			# вывести сообщение об ошибке
		}
		
		$this->_helper->redirector->gotoSimple('index', 'get', 'marksheet', array('file_id' => $inserted->file_id));
		die;
	}
	

}

