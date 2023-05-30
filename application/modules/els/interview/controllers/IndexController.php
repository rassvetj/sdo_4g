<?php
class Interview_IndexController extends HM_Controller_Action_Subject
{
    public function indexAction()
    {
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	
        
		$lessonId = $this->_getParam('lesson_id', 0);
        $currentId = $this->getService('User')->getCurrentUserId();
        $userId = $this->_getParam('user_id', $currentId);
        $subjectId = $this->_getParam('subject_id', 0);
		$showGraduated = (int)$this->_getParam('graduated', 0);

        $subjectService = $this->getService('Subject');
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR ))){
			$studentIDs = $subjectService->getAvailableStudents($currentId, $subjectId);
			if($studentIDs && !in_array($userId, $studentIDs)){				
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Вы не имеете права просматривать эту страницу'))
				);
				$this->_redirect('/');
			}			
		}
		
		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER))) {
			#$fg = $this->_getParam('fg', 0);
			#if($fg == 78){				
			#} else
			if($userId != $currentId){
				$this->_redirect('/');
			}
		}
		
        
		$isShowBaseForm = $this->getService('Interview')->isShowBaseForm($userId, $lessonId, $subjectId); //--доп. условие отображения формы в уроке.
		$this->isShowBaseForm = $isShowBaseForm; # для formProccess()
				
        $condition = array();
        if ($lessonId) {
            $condition['lesson_id = ?'] = $lessonId;
        } elseif ($taskId = $this->_getParam('task_id')) {
            $task = $this->getService('Task')->getOne($this->getService('Task')->find($taskId));
            if ($task && !empty($task->questions)) {
                if (count($questions = explode(HM_Question_QuestionModel::SEPARATOR, $task->data))) {
                    $condition["question_id IN ('?')"] = new Zend_Db_Expr(implode("','", $questions));
                }
            }
        }
        
        if($this->_getParam('task-preview') && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER))) {
        	$this->view->taskPreview = true;
        	$condition['type = ?'] = HM_Interview_InterviewModel::MESSAGE_TYPE_TASK;
        
        } else {
        	
        	$user = $this->getService('User')->getOne($this->getService('User')->fetchAll(array('MID = ?' => $userId)));
			if(!$user){
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Пользователь не найден'))
				);
				$this->_redirect('/');
			}
			
        	$this->getService('Unmanaged')->setSubHeader($user->getName());

            $interviewForm = new HM_Form_Interview();
            if ($this->isAjaxRequest()) {
                $req = $this->getRequest();
                $interviewForm->setAction(
                    $this->view->url(
                        array(
                            'module' => $req->getModuleName(),
                            'controller' => $req->getControllerName(),
                            'action' => $req->getActionName(),
                            'referer_redirect' => 1
                        )
                    )
                );
            }
        	$this->view->form = $interviewForm;
        	$this->formProccess();

            if ($currentId == $userId) {
                /**
                 * исключаем попадание в выборку своих ответов из других заданий
                 * оставляем только ответы самому себе
                 */
                $condition[] = '(user_id = ' . $userId .' OR to_whom = ' . $userId . ') AND (user_id != ' . $currentId . ' OR user_id = to_whom OR to_whom = 0)';
            } else {
                $condition[] = '(user_id = ' . $userId .' OR to_whom = ' . $userId . ')';
            }
        }
       
	   // --> if($lng == 'eng')
	   //	$messages = $this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File', $condition, array('interview_id'));
	   
        $messages = $this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File', $condition, array('interview_id'));
		
		// echo '<pre>'; exit(var_dump($messages));

        if ($lesson = $this->getOne($this->getService('Lesson')->find($lessonId))) {
    		$mark = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array("MID = ?"=>$userId, "SHEID = ?"=>$lesson->SHEID)));
    
    		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER))) {
    			$mark = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array('MID = ?' => $currentId, "SHEID = ?"=>$lesson->SHEID)));
    		}
    		
    		if ($mark->V_STATUS != HM_Scale_Value_ValueModel::VALUE_NA) {
    			$this->view->mark = $mark->V_STATUS;
    		} else {
    			$this->view->mark = "";
    		}
    		
    		if ($lesson->teacher) {
                $this->view->teacher = $this->getOne($this->getService('User')->find($lesson->teacher));
            }
            
            $this->view->lesson = $lesson;
        }
        		
		foreach($messages as $message) {
            $keyshowform[] = $message->type;			
        }
		        
		$subject = $subjectService->find($subjectId)->current();
		$isSubjectExpired = $subject->isExpired();			
		
		$isShowAttemptButton = false; //--выводить кнопку добавления попыти на прикрепление решения (выставления оценки)
		
		if(count($keyshowform)){
            if(in_array(HM_Interview_InterviewModel::MESSAGE_TYPE_BALL, $keyshowform)){
                if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
                    HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR 
                ))){					
					if($isShowBaseForm){						

					} else {
						$this->view->form 				= new HM_Form_Mark();							
						$this->view->isChangeMarkForm 	= true;							
						$isShowAttemptButton 			= true;
					}										
                } else {					
					if($isShowBaseForm){ //--если продлена и можно еще прикреплять ответы или выствалять оценки.
						
					} else {
						$this->view->form = '';							
					}					
                }
            }
        }        		
		$this->view->isShowForm = false;		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
			HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR 
		))){
			$this->view->isShowForm 		= true;
			
			$this->view->isCanSetMark  	= $this->getService('Interview')->isCanSetMark($user->MID, $lessonId);
			
			
				# Если студент не прислал решение в рошедшей сессии и тьютор не может выставить оценку, то разешаем выставить оценку, но только неуд (наявка.)
				
				if(!$this->view->isCanSetMark){
					
					if(!$subjectService->isAvailableSubject($subjectId, $userId)){
						#echo 'Нет доступа у студента';
						$this->view->isCanSetMarkLight	= true; # Показать упрощенную форму выставления балла: только с действием "Выставить оценку" и одним диапазоном - 2					
										
						if($isShowAttemptButton === true){
							$this->view->form_light = '';
							$isShowAttemptButton 	= false; # нет смысла давать попытку, если оценка не может быть больше 0.
						} else {
							$this->view->form_light = new HM_Form_Interviewlight();								
						}
					} else {
						#echo 'Доступна для студента';						
					}
				}
			
			
			$this->view->isTutor 		= true;
		} else {
			if($isShowBaseForm){
				$this->view->isShowForm = true;
			}	
		}
				
		$this->view->isShowAttemptButton = $isShowAttemptButton;
        $this->view->messages = $messages;			
        $this->view->readOlny = $showGraduated ? true : false;
	
    }
       
    protected function formProccess()
    {
        $lessonId   = $this->_getParam('lesson_id', 0);
        $userId     = (int)$this->_getParam('user_id', 0);
        $subject_id = (int)$this->_getParam('subject_id', 0);

        $request = $this->getRequest();
        $currentId = $this->getService('User')->getCurrentUserId();				
        if ($request->isPost())
        {
            if( ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER ))) && ($this->isShowBaseForm === false)){
				$this->_flashMessenger->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
						  'message' => _('Вы не можете отправлять сообщения, т.к. преподаватель Вам выставил оценку.'))
				);

				if ($this->_getParam('referer_redirect')) {
					$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
				}
				
				$this->_redirector->gotoSimple('index', 'index', 'interview',
					array(
						'lesson_id' => $lessonId,
						'subject_id' => $this->_getParam('subject_id', 0),						
					)
				);  				
			}
		
			$form	= $this->view->form;  			
			try {			
				if (is_object($form->files)) {
				
				if(!$form->files->isUploaded() && !$form->files->receive() && !$form->files->isReceived()){				
					$form->removeElement('files');				
				}	
				
				}
			} catch (Exception $e) {}
			
			if ($form->isValid($request->getParams()))
			{
				$date = new HM_Date();
				$form = $this->view->form;         
				$fileIds = array();
				if($userId > 0){
					$lessonUserId = $userId;
				}else{
					$lessonUserId = $currentId;
				}
				
				if($this->getService('Acl')->inheritsRole(	$this->getService('User')->getCurrentUserRole(),
															array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR)
				)){					
					$this->getService('Workload')->setLessonViolation($this->getService('User')->getCurrentUserId(), $userId, $lessonId, $this->_getParam('subject_id', 0));
				}
				
				$message = $this->getService('Interview')->getOne($this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File',array('lesson_id = ?' => $lessonId, '( user_id = ' . $lessonUserId .' OR to_whom = ' . $lessonUserId . ')'), array('interview_id')));
				$interview = $this->getService('Interview')->insert(
					array(
						'user_id' => $this->getService('User')->getCurrentUserId(),
						'to_whom' => $userId,
						'lesson_id' => $lessonId,
						'title' => '',
						'question_id' => '',
						'type' => $this->view->form->getValue('type'),
						'message' => $this->view->form->getValue('message'),
						'message_translation' => $this->view->form->getValue('message_translation'),
						'date' => $date->toString(),
						'interview_hash' => $message->interview_hash,
						//'ball' => $this->view->form->getValue('ball')
						//'mark' => $this->view->form->getValue('ball'),
					)
				);
				
				if($form->files){
					if (is_object($form->files)) {
						if($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()){
							$files = $form->files->getFileName();
							if(count($files) > 1){
								foreach($files as $file){
									
									$fileInfo = pathinfo($file);
									$file = $this->getService('Files')->addFile($file, $fileInfo['basename']);
									$this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
								}
							}else{
								$fileInfo = pathinfo($files);
								$file = $this->getService('Files')->addFile($files, $fileInfo['basename']);
								$this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
							}
						}	
					}
					if (is_object($form->files_en)) {
						if($form->files_en->isUploaded() && $form->files_en->receive() && $form->files_en->isReceived()){
							$files_en = $form->files_en->getFileName();
							if(count($files_en) > 1){
								foreach($files_en as $file){
									
									$fileInfo = pathinfo($file);
									$file = $this->getService('Files')->addFile($file, $fileInfo['basename']);
									$this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_en_id' => $file->file_id));
								}
							}else{
								$fileInfo = pathinfo($files);
								$file = $this->getService('Files')->addFile($files, $fileInfo['basename']);
								$this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_en_id' => $file->file_id));
							}
						}						
					}
					
					
				}
				
				$this->_flashMessenger->addMessage(_('Сообщение успешно добавлено.'));    
				if($this->view->form->getValue('type') == HM_Interview_InterviewModel::MESSAGE_TYPE_BALL && $this->view->form->getValue('ball') != "") {
							
					//--сохраняем весь прогресс выставленных оценок в сообщениях
					//--оценку за урок обновляем в том случае, если она больше предыдущей за урок.					
					$lessonOld = $this->getOne($this->getService('LessonAssign')->fetchAll(
						array(
							'SHEID = ?' => $lessonId,
							'MID = ?' => $userId
						)
					));
					if($lessonOld){
						$ballOld = $lessonOld->V_STATUS;
					} else {
						$ballOld = false;
					}
					
					
					$ballNew = $this->view->form->getValue('ball');
					$collection = $this->getService('LessonAssign')->fetchAllDependence('Lesson', array('MID = ?' => $userId, 'SHEID = ?' => $lessonId));
					if (count($collection)) {
						$lessonAssign = $collection->current();
						$lesson = $lessonAssign->lessons->current();
						
						
						$subject = $this->getService('Lesson')->getSubjectByLesson($lessonId);
						$markType = $subject->mark_type;						
						if($markType == HM_Mark_StrategyFactory::MARK_BRS){
							if (isset($lesson->max_ball)) {
								switch($lesson->getType()) {				
									default:
									$ballNew = round($ballNew * $lesson->max_ball / 100, 2);
								}
							}
						} elseif($markType == HM_Mark_StrategyFactory::MARK_WEIGHT){
							$ballNew = round($ballNew, 2);
						}						
					}
					
					if($ballNew > $ballOld){
						$this->getService('LessonAssign')->setUserScore($userId, $lessonId, $this->view->form->getValue('ball'));
					}			
					
					$this->getService('Interview')->updateWhere(
						array('ball' => $ballNew),
						array('interview_id = ?' => $interview->interview_id)
					);
					
					## Обновляем оценку за сессию.
					$this->getService('Subject')->setScore($subject_id, $userId);
					
				}
				if ($this->_getParam('referer_redirect'))
					$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
				$this->_redirector->gotoSimple('index', 'index', 'interview',
					array(
						'lesson_id' => $lessonId,
						'subject_id' => $this->_getParam('subject_id', 0),
						'user_id' => ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR))) ? $userId : null,
					)
				);    
			}else{    
			}					
        }else{ 
        }    
    }
    
    public function changeMarkAction() {
        $userId 	= (int)$this->_getParam('user_id', 0);
        $lessonId 	= (int)$this->_getParam('lesson_id', 0);
        $subject_id = (int)$this->_getParam('subject_id', 0);
        $request 	= $this->getRequest();
        
        $form = new HM_Form_Mark();
        
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                if ($form->getValue('ball') != "") {
                    $this->getService('LessonAssign')->setUserScore($userId, $lessonId, $form->getValue('ball'));
					$this->setUserScoreMessage($userId, $lessonId, $form->getValue('ball'));
					
					## Обновляем оценку за сессию.
					$this->getService('Subject')->setScore($subject_id, $userId);
					
                }
                $this->_flashMessenger->addMessage(_('Оценка успешно изменена'));    
            } else {
                $this->_flashMessenger->addMessage(_('Введите значение от 0 до 100'));
            }
        }
        $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
        
    }
	
	/**
	 * Добавляет еще одны попытку на прикрепление задания на проверку или выставления оценки приподаавтелем.
	**/
	public function addAttemptAction() {		
        $userId 	= (int)$this->_getParam('user_id', 0);
        $lessonId 	= (int)$this->_getParam('lesson_id', 0);		
        $request 	= $this->getRequest();
        
        if ($request->isPost()) {						
			
			if($this->getService('Lesson')->addAttemptToUser($userId, $lessonId)){
				$this->_flashMessenger->addMessage(_('Попытка успешно добавлена'));    	
			} else {
				$this->_flashMessenger->addMessage(_('Не удалось добавить попытку'));    	
			}
        }
        $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);        
    }
	
	/**
	 * меняем оценку в последнем сообщении тьютора с типом "Оценка выставлена"
	*/
	public function setUserScoreMessage($userId, $lessonId, $ball){		
		try {
			$max_select = $this->getService('Workload')->getSelect();
			$max_select->from(
				array('m' => 'interview'),
				array(
					'max_date' => 'MAX(m.date)',
				)
			);
			$max_select->where('m.type = ?',HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);			
			//$max_select->where('m.user_id = ?',$this->getService('User')->getCurrentUserId()); //--изменять оценку может и другой тьютор. Поэтому это условие лишнее.
			$max_select->where('m.to_whom = ?',$userId);
			$max_select->where('m.lesson_id = ?',$lessonId);						
			$max_res = $max_select->query()->fetchObject();		
			 
			$select = $this->getService('Workload')->getSelect();
			$select->from(
				array('m' => 'interview'),
				array('interview_id' => 'm.interview_id',)
			);
			
			$select->where('m.type = ?',HM_Interview_InterviewModel::MESSAGE_TYPE_BALL);			
			$select->where('m.to_whom = ?',$userId);
			$select->where('m.lesson_id = ?',$lessonId);		
			$select->where('m.date = ?',$max_res->max_date);		
			$interview = $select->query()->fetchObject();
			
			if($interview && $interview->interview_id){
				$ballNew = $ball;
				$collection = $this->getService('LessonAssign')->fetchAllDependence('Lesson', array('MID = ?' => $userId, 'SHEID = ?' => $lessonId));
				if (count($collection)) {
					$lessonAssign = $collection->current();
					$lesson = $lessonAssign->lessons->current();
					
					
					$subject = $this->getService('Lesson')->getSubjectByLesson($lessonId);
					$markType = $subject->mark_type;						
					if($markType == HM_Mark_StrategyFactory::MARK_BRS){
						if (isset($lesson->max_ball)) {
							switch($lesson->getType()) {				
								default:
								$ballNew = round($ballNew * $lesson->max_ball / 100, 2);
							}
						}
					} elseif($markType == HM_Mark_StrategyFactory::MARK_WEIGHT){
						$ballNew = round($ballNew, 2);
					}						
				}			
				$this->getService('Interview')->updateWhere(
					array('ball' => $ballNew),
					array('interview_id = ?' => $interview->interview_id)
				);
			}		
		} catch (Exception $e) {
			
		}		
	}
	
	/**
	 * тоже самое, что и insexAction, только выводит все занятия студента в сессии
	*/
	public function allAction()
    {
        
		$lessonId = $this->_getParam('lesson_id', 0);
        $currentId = $this->getService('User')->getCurrentUserId();
        $userId = $this->_getParam('user_id', $currentId);
        $subjectId = $this->_getParam('subject_id', 0);

        $subjectService = $this->getService('Subject');
        $lessonService = $this->getService('Lesson');
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR ))){
			$studentIDs = $subjectService->getAvailableStudents($currentId, $subjectId);
			if($studentIDs && !in_array($userId, $studentIDs)){				
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Вы не имеете права просматривать эту страницу'))
				);
				$this->_redirect('/');
			}			
		}
		
		$allowLessons = false;
		if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TUTOR) {
			$collection = $this->getService('LessonAssignTutor')->getAssignSubject($this->getService('User')->getCurrentUserId(), $subjectId);
			if(count($collection)){				
				$allowLessons = $collection->getList('LID');				
			}			
		}
		
		$lessons = $lessonService->getActiveLessonsOnSubjectIdCollection($subjectId);
		if(empty($lessons)){
			echo _('Нет данных для отображения');
			die;
		}
		$tasks = array();
		foreach($lessons as $l){
			if($allowLessons && !isset($allowLessons[$l->SHEID])){ continue; }
			if(!$l->allowTutors){ continue; }
			if($l->typeID != HM_Event_EventModel::TYPE_TASK){ continue; }
			$tasks[$l->SHEID] = $l->SHEID;
		}
		if(empty($tasks)){
			echo _('Нет занятий с типом "Задание" для отображения');
			die;
		}
		
		
		$user = $this->getService('User')->getById($userId);
		if(!$user) { 
			echo _('Нет такого пользователя');
			die;
		}
		$this->view->user = $user;
		
		
		$lessonsContent = array();
		foreach($tasks as $lesson_id){			
			$lessonsContent[$lesson_id] = $this->getHtmlContent($subjectId, $lesson_id, $userId);
		}
		$this->view->lessonsContent = array_filter($lessonsContent);
        	
    }
	
	/**
	 * @return html/bool
	 * переписка тьютора со студентов в занятии
	*/
	public function getHtmlContent($subject_id, $lesson_id, $user_id){
		if(!$subject_id || !$lesson_id || !$user_id){ return false; }
		
		$currentUserId = $this->getService('User')->getCurrentUserId();
		$lesson = $this->getService('Lesson')->getLesson($lesson_id);
		if(!$lesson) { return false; }
		
		$lessonAssign = $this->getService('LessonAssign')->getRow($lesson_id, $user_id);
		
		$showGraduated = (int)$this->_getParam('graduated', 0);

		
		if(!$lessonAssign) { return false; } # не назначен студент
		
		
				
		$condition = array();
        if ($lesson_id) {
            $condition['lesson_id = ?'] = $lesson_id;
        }
		
		if ($currentUserId == $user_id) {
			/**
			 * исключаем попадание в выборку своих ответов из других заданий
			 * оставляем только ответы самому себе
			 */
			$condition[] = '(user_id = ' . $user_id .' OR to_whom = ' . $user_id . ') AND (user_id != ' . $currentUserId . ' OR user_id = to_whom OR to_whom = 0)';
		} else {
			$condition[] = '(user_id = ' . $user_id .' OR to_whom = ' . $user_id . ')';
		}
		
		$messages = $this->getService('Interview')->fetchAllHybrid('User', 'Files', 'File', $condition, array('interview_id'));
		$lastMessage = array();
		$issetTask 	 = false;	
		$types		 = HM_Interview_InterviewModel::getTypes();
		foreach($messages as $message) {
			if($message->type == HM_Interview_InterviewModel::MESSAGE_TYPE_TASK) { $issetTask = true; }
			if($message->type != HM_Interview_InterviewModel::MESSAGE_TYPE_TASK && $lastMessage['interview_id'] < $message->interview_id){
				$lastMessage = array(
					'interview_id'	=> $message->interview_id,
					'type' 			=> $message->type,
					'type_name'		=> $types[$message->type],
				);
			}
			$keyshowform[] = $message->type;
		}
		
		$isShowAttemptButton = false;		
		$form = new HM_Form_Interview();
		if(count($keyshowform)){
            if(in_array(HM_Interview_InterviewModel::MESSAGE_TYPE_BALL, $keyshowform)){                				
				if($this->getService('Interview')->isShowBaseForm($user_id, $lesson_id, $subject_id) !== true){
					$isShowAttemptButton = true;	
					$form 	 			 = new HM_Form_Mark();
					$this->view->isChangeMarkForm 	= true;						
				} 
				
            }
        }
		if(!$isShowAttemptButton){
			$form->setAction(
				$this->view->url(
					array(
						'module' 			=> 'interview',
						'controller' 		=> 'index',
						'action' 			=> 'send-form',
						'referer_redirect'  => 1,				
					)
				)
			);
		}
		$form->setAttrib('id', 'lesson_'.$lesson_id);
		$form->addElement('hidden', 'lesson_id', array(
            'value' => $lesson_id,
            'Validators' => array('Int'),
            'Filters' => array('Int'),            
        ));
		#
		$submit = $form->getElement('button');		
		if($submit){
			$submit->setAttrib('id', 'submit_'.$lesson_id);
			$submit->setAttrib('class', 'submit_btn');
		}
		
		$type = $form->getElement('type');		
		if($type){
			$type->setAttrib('id', 'type_'.$lesson_id);
			$type->setAttrib('class', 'type');
		}
		
		$is_change_mark = $form->getElement('is_change_mark');		
		if($is_change_mark){ $is_change_mark->setAttrib('id', 'is_change_mark_'.$lesson_id); }
		
		$message = $form->getElement('message');		
		if($message){ $message->setAttrib('id', 'message_'.$lesson_id); }
		
		$ball = $form->getElement('ball');		
		if($ball){
			$ball->setAttrib('id', 'ball_'.$lesson_id);
			$ball->setAttrib('class', 'ball');
		}
		
		$range_mark = $form->getElement('range_mark');		
		if($range_mark){
			$range_mark->setAttrib('data_id', 'range_mark_'.$lesson_id);			
		}
		
		$this->view->lesson 			 = $lesson;
		$this->view->lesson_score 		 = $lessonAssign->V_STATUS;
		$this->view->issetTask 		 	 = $issetTask;
		$this->view->lastMessage 		 = $lastMessage;
		$this->view->taskNameType 		 = $types[HM_Interview_InterviewModel::MESSAGE_TYPE_TASK];				
		$this->view->isCanSetMark  		 = $this->getService('Interview')->isCanSetMark($user_id, $lesson_id);
		$this->view->isTutor 			 = true;
		$this->view->form 				 = $form;
		$this->view->isShowAttemptButton = $isShowAttemptButton;
		$this->view->messages  			 = $messages;
		$this->view->lesson_id 			 = $lesson_id;
		$this->view->user_id   			 = $user_id;
		$this->view->readOlny            = $showGraduated ? true : false;
		
		# реализовать вывод в этом шаблоне.
		return $this->view->render('/index/_part.tpl'); 
	}
	
	public function sendFormAction(){
		$interviewForm = new HM_Form_Interview();
		$interviewForm->setAction(
			$this->view->url(
				array(
					'module' 			=> 'interview',
					'controller' 		=> 'index',
					'action' 			=> 'send-form',
					'referer_redirect'  => 1,				
				)
			)
		);
		$this->view->form = $interviewForm;
		$this->formProccess();	
	}
	
    
    
}