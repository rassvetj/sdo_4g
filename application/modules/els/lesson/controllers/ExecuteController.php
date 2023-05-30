<?php
class Lesson_ExecuteController extends HM_Controller_Action_Subject
{
    public function indexAction()
    {
        if(
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)
			&&
			!$this->getService('User')->isLoginAs()
		){
			$userInfo = $this->getService('UserInfo')->getCurrentUserInfo();
			if($userInfo && !$userInfo->isAvailableSubjects()){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array(
					'type'    => HM_Notification_NotificationModel::TYPE_ERROR, 
					'message' => _('У Вас нет доступа к занятиям. ' . $userInfo->getHumanizedStatus())
				));			
				$this->_helper->redirector->gotoSimple('index', 'index', 'index');
				die;
			}
		}
		
		
		//subject/index/index/subject_id/92/course_id/221

        $returnUrl = $_SERVER['HTTP_REFERER'];
        $lessonId = $this->view->lessonId = (int) $this->_getParam('lesson_id');
        $subjectId = $this->view->subjectId = (int) $this->_getParam('subject_id', 0);

        if ($lessonId) {
			
			
			if(
				$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
				&&
				!$this->getService('Lesson')->isAvailable($this->getService('User')->getCurrentUserId(), $lessonId, $subjectId)
			){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('У Вас нет доступа к этому занятию.')));			
				$this->_helper->redirector->gotoSimple('card', 'index', 'subject', array('subject_id' 	=> $subjectId));			
			} 
				
			
			

            try{
                $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
                if ($lesson) {

                    if ($lesson->getService()->isExecutable($lessonId)) {

                        if ( $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_STUDENT) ) {

							$isActiveDebt = $this->getService('Subject')->isActiveDebt($this->_subject->subid); //--открываем доступ к курсу, если продлен долг.
						
                            // проверка даты начала/окончания курса и занятия
                            $currentDate = new HM_Date();

                            // фиксированная дата курса
                            if ($this->_subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT
							&& 
							!$isActiveDebt //--Если не долг, или время долга прошло.
							) {
                                $subjectBegin = new HM_Date($this->_subject->begin);
                                $subjectEnd   = new HM_Date($this->_subject->end);
                                if ($subjectBegin->getTimestamp() > $currentDate->getTimestamp() || $subjectEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($subjectBegin->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала курса не наступила') : _('Курс завершен')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }
                            // фиксированная дата занятия
                            if (!$lesson->recommend && ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_DATES || $lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_TIMES)) {
                                $lessonBegin = new HM_Date($lesson->begin);
                                $lessonEnd   = new HM_Date($lesson->end);
                                if ($lessonBegin->getTimestamp() > $currentDate->getTimestamp() || $lessonEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($lessonEnd->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала занятия не наступила') : _('Занятие завершено')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }
                            // относительная дата занятия
                            if (!$lesson->recommend && $lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_RELATIVE) {
                                $lessonAssign = $this->getService('LessonAssign')->getOne($this->getService('LessonAssign')->fetchAll(array('SHEID = ?'  => $lessonId, 'MID = ?'    => $this->getService('User')->getCurrentUserId())));
                                $lessonBegin = new HM_Date($lessonAssign->beginRelative);
                                $lessonEnd   = new HM_Date($lessonAssign->endRelative);
                                if ($lessonBegin->getTimestamp() > $currentDate->getTimestamp() || $lessonEnd->getTimestamp() < $currentDate->getTimestamp()) {
                                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => ($lessonEnd->getTimestamp() > $currentDate->getTimestamp())? _('Дата начала занятия не наступила') : _('Занятие завершено')));
                                    $this->_redirector->gotoUrl($returnUrl);
                                }
                            }

                            // эта логика ушла в onLessonStart
//                             $this->getService('LessonAssign')->updateWhere(array(
//                                     'V_DONE' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_INPROCESS,
//                                     'launched' => date('Y-m-d H:i:s'),
//                                ),
//                                array(
//                                    'SHEID = ?'  => $lessonId,
//                                    'MID = ?'    => $this->getService('User')->getCurrentUserId(),
//                                    'V_DONE = ?' => HM_Lesson_Assign_AssignModel::PROGRESS_STATUS_NOSTART
//                                )
//                             );
                        }

                        /* Логирование захода пользователя в занятие */
                        $this->getService('Session')->toLog(array('course_id' => $subjectId));
                        $this->getService('Session')->toLog(array('lesson_id' => $lesson->SHEID, 'course_id' => $subjectId, 'lesson_type' => $lesson->typeID));

                        $this->getService('LessonAssign')->onLessonStart($lesson);

                        if ($lesson->isExternalExecuting()) {
                            Zend_Registry::get('session_namespace_default')->lesson['execute']['returnUrl'] = $_SERVER['HTTP_REFERER'];
                            $this->_redirector->gotoUrl($lesson->getExecuteUrl());
                            //header('Location: '.$lesson->getExecuteUrl());
                            //exit();
                        } else {
                            $url = '';
                            switch($lesson->getType()) {
                                case HM_Event_EventModel::TYPE_LECTURE:
                                    /* $item = $this->getService('CourseItem')->getOne(
                                        $this->getService('CourseItem')->findDependence('Module', $lesson->getModuleId())
                                    );
                                    if ($item && ($module = $item->getModule())) {
                                        $url = $module->getUrl();
                                    }*/

                                    // TODO: Начало хардкода. времени вообще нет, а нужно чтобы было вчера
                                    $course = $this->getService('CourseItem')->getOne($this->getService('CourseItem')->fetchAll(array('oid = ?' => $lesson->getModuleId())));

                                    $courseId = $course->cid;
                                    $course = $this->getOne($this->getService('Course')->find($courseId));
                                    //$this->view->addInfoBlock('CourseStatusBlock', array('course' => $course, 'title' => _('Статус курса')));
                                    $opened = $this->getService('CourseItem')->getOpenedBranch($courseId);

                                    if ( $lesson->getModuleId() ) {
                                        $tree = $this->getService('CourseItem')->getBranchContent($courseId, $lesson->getModuleId(), FALSE, TRUE);
                                        $isDegeneratedTree = $this->getService('CourseItem')->isDegeneratedBranch($courseId, $lesson->getModuleId());
                                    } else {
                                        $tree = $this->getService('CourseItem')->getTreeContent($courseId, $opened);
                                        $isDegeneratedTree = $this->getService('CourseItem')->isDegeneratedTree($courseId);
                                    }

                                    //$userId = $this->getService('User')->getCurrentUserId();
                                    //$this->view->current = $this->getService('CourseItemCurrent')->getCurrent($userId, 0, $courseId);

                                    $this->view->current = $lesson->getModuleId();
                                    if ($this->view->current) {
                                        $this->view->itemCurrent = $this->getService('CourseItem')->getOne(
                                            $this->getService('CourseItem')->find($this->view->current)
                                        );
                                    }
                                    $this->view->courseContent = true;
                                    $this->view->courseObject = $course;
                                    $this->view->tree = $tree;
                                    $this->view->allowEmptyTree = true;
                                    $this->view->isDegeneratedTree = $isDegeneratedTree;

                                    // Конец хардкода

                                    return;
                                    break;
                            }

                            if (strlen($url)) {
                                $this->view->url = $url;
                                return true; // render index.tpl
                            }

                            $this->_flashMessenger->addMessage(array('message' => _('Данное занятие невозможно запустить'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                        }
                    }
                } else {
                    $this->_flashMessenger->addMessage(array('message' => _('Занятие не найдено'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                }

            } catch(HM_Exception $exception) {
                $this->_flashMessenger->addMessage(array('message' => $exception->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            }

        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Не указан идентификатор занятия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
        }

        //$this->_redirector->gotoSimple('index', 'index', 'default');
        $this->_redirector->gotoUrl($returnUrl);

    }
}