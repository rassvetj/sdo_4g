<?php
class HM_View_Helper_LessonPreview extends HM_View_Helper_Abstract
{

    public function lessonPreview($lesson, $titles = null, $template = 'lesson-preview', $forUser = NULL, $eventCollection = null)
    {
        
		
		$this->view->headScript()->appendFile($this->view->serverUrl('/js/application/marksheet/index/index/scoreList.js'));

/*        $this->view->allowEdit = $this->view->allowDelete = in_array(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
            array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_TEACHER)
        );*/

        $this->view->allowEdit = $this->view->allowDelete = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_TEACHER));

        $this->view->showScore = (
                (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER) ||
                        //Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT ||
                        (Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TUTOR && $forUser) ||
                (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER) && $forUser)
				
				
				
				) &&
                $lesson->vedomost
            );
//#17849
        $types = HM_Event_EventModel::getTypes();
        $extTypes = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll();
        $extTypes = $extTypes->getList('event_id', 'title');
        foreach($extTypes as $i=>$e)
            $types[-$i] = $e;
//
		if($lesson->timetype == 2) $datetime = _('Не ограничено');
		elseif($lesson->timetype == 1){
			
			// если возможно, показываем сразу абсолютные даты
			if ((Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER) ||
				Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TUTOR ||
                (Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() ==HM_Role_RoleModelAbstract::ROLE_TEACHER && $forUser))) {
				
                $begin = $lesson->getBeginDateRelative($forUser);
                $end = $lesson->getEndDateRelative($forUser);

				if(!$begin) {
					$datetime = sprintf(_("до %s "), $end);
				} elseif(!$end) {
					$datetime = sprintf(_("с %s "), $begin); 
				} elseif ($begin != $end) {
					$datetime = sprintf(_("с %s по %s"), $begin, $end);				
				} else {
					$datetime = $begin;
				}
				
			} else {
				$begin = $lesson->getBeginDay();
				$end = $lesson->getEndDay();
				$strtime = ( max($begin,$end) > 0) ? 'от начала обучения' : 'до окончания обучения';
				if ($begin == $end)	$datetime = sprintf(_('%s день %s по курсу'), abs($begin), $strtime);
				elseif(!$end)		$datetime = sprintf(_("с %s дня %s по курсу"), abs($begin), $strtime);
				else 				$datetime = sprintf(_("с %s по %s день %s по курсу"), abs($begin), abs($end), $strtime);
			}
		}
        else{
			$begin = $lesson->getBeginDate();
			$end = $lesson->getEndDate();
			if ($begin == $end)	$datetime = sprintf(_("%s, с %s по %s"), $begin, $lesson->getBeginTime(), $lesson->getEndTime());
			elseif(!$end)		$datetime = sprintf(_("с %s "), $begin);
			else $datetime = sprintf(_("с %s по %s"), $begin, $end);
        }

        $details = 1;
        if($lesson->getType() == HM_Event_EventModel::TYPE_TEST){
            $test = Zend_Registry::get('serviceContainer')->getService('Test')->getOne(Zend_Registry::get('serviceContainer')->getService('Test')->fetchAll(
                                                                                           Zend_Registry::get('serviceContainer')->getService('Test')->quoteInto(array('lesson_id = ?'), array($lesson->SHEID))
                                                                                       ));
            $details = $test->allow_view_log;
        } elseif ($lesson->getType() == HM_Event_EventModel::TYPE_RESOURCE) {
            $details = 0;
        }

        $this->view->titleUrl = $this->view->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson->SHEID, 'subject_id' => $lesson->CID), false, true);
        $this->view->targetUrl = '';

        if($lesson->getType() == HM_Event_EventModel::TYPE_COURSE){


            $courseId = $lesson->getModuleId();
            $course = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(Zend_Registry::get('serviceContainer')->getService('Course')->find($courseId));
            if($course->new_window == 1){
                $itemId = Zend_Registry::get('serviceContainer')->getService('CourseItemCurrent')->getCurrent(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), $lesson->CID, $courseId, $lesson->SHEID);
                if($itemId != false){
                    $this->view->titleUrl = $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId));
                    $this->view->targetUrl = '_blank';
                    //return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)). '" target = "_blank">'. $field.'</a>';
                }
            }

        }

        $this->view->currentUserId = ($forUser)? $forUser : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
        $this->view->isStudentPageForTeacher = ($forUser && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER))? true : false;
		$this->view->type = $types[$lesson->typeID];
    	$this->view->titles = $titles;
        $this->view->details = $details;
    	$this->view->datetime = $datetime;
        $this->view->lesson = $lesson;
        $this->view->eventCollection = $eventCollection;

        if($lesson->teacher[0]->MID > 0){
		    $this->view->teacher = array('user_id' => $lesson->teacher[0]->MID, 'fio' => trim($lesson->teacher[0]->LastName.' '.$lesson->teacher[0]->FirstName.' '.$lesson->teacher[0]->Patronymic));
        }else{
            $this->view->teacher = null;
        }
        return $this->view->render($template . '.tpl');
    }
}