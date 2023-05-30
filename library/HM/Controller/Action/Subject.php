<?php
class HM_Controller_Action_Subject extends HM_Controller_Action_Extended
{

    protected $service = 'Subject';
    protected $idParamName  = 'subject_id';
    protected $idFieldName = 'subid';

    public function init()
    {
        parent::init();

        $isPollExecute = false;
        if (sprintf('%s:%s', $this->_request->getModuleName(), $this->_request->getControllerName()) == 'lesson:execute') {
            $lessonId = (int) $this->_getParam('lesson_id', 0);
            if ($lessonId) {
                $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
                if ($lesson) {
                    if (in_array($lesson->typeID, array(
                        HM_Event_EventModel::TYPE_POLL,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT,
                        HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER
                    ))) {
                        $isPollExecute = true;
                    }
                }
            }
        }

/*        if(empty($this->_subject)){
            $id = (int) $this->_getParam($this->idParamName, 0);
            $this->_subject = $this->getOne($this->getService($this->service)->find($id));
        }*/

     if (!$this->isAjaxRequest() && !$isPollExecute) {

            // автосоздание секций для обратной совместимости с 4.2
            if ($this->_subject) {
                $this->getService('Section')->getDefaultSection($this->_subject->subid);
            }
            
            if ($this->_subject && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
                
				$isActiveDebt = $this->getService('Subject')->isActiveDebt($this->_subject->subid);				
				
                if ($this->_subject->period == HM_Subject_SubjectModel::PERIOD_DATES // не совсем верно, есть еще вариант с ограниченной длительностью и он никак не обрабатывается; рассчитываем на то, что скоро появится перевод в прош.обучение по крону и эта проверка не понадобится
                    && strtotime($this->_subject->end) < time()
                    && $this->_subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT
					&& !$isActiveDebt
                ){
                   
                    
					# показываем страницу с перепиской в задании
					# только для прошедших сессий со строгим ограничением по времени
					if ($lesson->typeID == HM_Event_EventModel::TYPE_TASK){
						 $this->_redirector->gotoSimple('index', 'past', 'lesson', array('subject_id' => $this->_subject->subid, 'lesson_id' => $lesson->SHEID));						
					}

					$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_NOTICE, 'message' => _('Время обучения на курсе закончилось')));					
					$this->_redirector->gotoSimple('index', 'list', 'subject');
                }
                
				//--Фанфары. Временно их отключаем.
				/*
                if (sprintf('%s:%s', $this->_request->getControllerName(), $this->_request->getActionName()) != 'index:happy-end') {                    
					if ($this->getService('SubjectMark')->isConfirmationNeeded($this->_subject->subid, $this->getService('User')->getCurrentUserId())) {
                        //$this->_redirector->gotoSimple('happy-end', 'index', 'subject', array('subject_id' => $this->_subject->subid));
						if(strtotime($this->_subject->end) < time()){ //--Если дата окончания курса меньше текущей даты, т.е. курс прошел.
							$this->_redirector->gotoSimple('happy-end', 'index', 'subject', array('subject_id' => $this->_subject->subid));
						}
                    }                
                }                
				*/                
            }
        }

        /*if($this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE && $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
            $this->view->deleteContextMenu('subject');
            $this->view->addInfoBlock('freeAccessToSubjectBlock', array('title' => $this->_subject->shortname ? $this->_subject->shortname : _('Содержание'), 'subject' => $this->_subject));
        }*/
    }



}