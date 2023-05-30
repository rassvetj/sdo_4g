<?php
class HM_View_Helper_SubjectPreview extends HM_View_Helper_Abstract
{
    public function subjectPreview($subject, $marks,  $graduatedList, $studentCourseData, $data = array('isElective' => false, 'switcher' => 'list'), $lng = 'rus')
    { 		
		static $counter = 0;
		
		$additional = array();
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	

        $services = Zend_Registry::get('serviceContainer');

        $userService      = $services->getService('User');
        $aclService       = $services->getService('Acl');
        $subjService      = $services->getService('Subject');

        $subjectId     = $subject->subid;
		
		
		if($subjService->isMainModule($subjectId)){
			$subject->name = $subject->name.' (Главный модуль)';
		}
		
		$this->view->date_end                  = $subject->getEstimatedDateEnd();
		$this->view->date_end_landmark_control = $subject->getEstimatedDateLandmarkControl();
		$this->view->isDO                      = $subject->isDOT();
		
		# метод isDOT переделать на кэш и возможную передачу кроме id моедль с данными. А то слишком много будет лишних обращений к БД 
		# isDO
		/*
		if(!empty($subject->isDO)){
			$this->view->isDO = true;
			$end_timestamp = strtotime($subject->end);
			if($end_timestamp > 0){			
				$dt = new DateTime();
				$dt->setTimestamp($end_timestamp);
				$dt->sub(new DateInterval('P5D'));			
				# Дата окончания обучения, не позднее
				$this->view->date_end = $dt->format('d.m.Y');
							
				# Дата приема рубежного контроля. Если это воскресенье, то сдвигаем на субботу.
				$dt->setTimestamp($end_timestamp);			
				# воскресенье -1 день
				if($dt->format('w') == 0){ $dt->sub(new DateInterval('P1D')); }			
				$this->view->date_end_landmark_control = $dt->format('d.m.Y');
			}
		} else {
			$this->view->date_end 					= date('d.m.Y', strtotime($subject->end));
			$this->view->date_end_landmark_control 	= $this->view->date_end;
		}
		*/
		
			
		
        $descriptionId = 'hm-subject-list-item-description-container-'.(++$counter);

        $userId    = $userService->getCurrentUserId();
        $userRole  = $userService->getCurrentUserRole();

        $isEndUser = $aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_ENDUSER);
        #$isTutor   = $aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TUTOR);
		
		

        $tutors = $userService->fetchAllJoinInner('Tutor', $userService->quoteInto(array('Tutor.CID = ?'), array($subjectId)));
		
		
		
		#if($isEndUser || $isTutor) {			
		if($isEndUser) {
            $tutors = $subjService->filterAvailableTutors($tutors, $userId, $subjectId);
        }
		$subject->tutors = $tutors;
        
        $view = $this->view;
		
		$params = array(
            'currentUserId'     => $userId,
            'subject'           => $subject,
            'subjectId'         => $subjectId,
            'studentCourseData' => $studentCourseData,
            'isElectiv'         => $data['isElective'],
            'switcher'          => $data['switcher'],
            'showScore'         => $isEndUser,
            'isTeacher'         => !$isEndUser,
            'disperseName'      => '',
            'disperse'          => false, // "Завершить обучение"
            'graduated'         => false, // "Курс завершен"
            'action'            => $isEndUser ? 'my' : 'index',
            'descriptionId'     => $descriptionId, // id дива с описанием (табы) в правой части
			'isStudent'			=> $isEndUser,
        );
		
		if(!$isEndUser){
			$params['groups'] = $services->getService('StudyGroup')->getBySubject($subjectId);			
			$params['groups'] = $this->filteredGroups($params['groups'], $studentCourseData['users_groups']);
		}
		
		

        $view->assign($params);
		
		
		$view->isDebt 				= (empty($studentCourseData['time_ended_debtor'])) ? (false) : (true); //--это продленая сессия		
		$view->isActiveDebt         =  (strtotime($studentCourseData['time_ended_debtor']) >= strtotime(date('d.m.Y 00:00:00')) ) ? (true) : (false);  //--продление еще активно
		
		# Сессия в текущих, а ее уже продлили => долг не выводим
		if(strtotime($subject->end) > time()){
			$view->isDebt       = false;
			$view->isActiveDebt = false;	
		}
		

		$view->endDebtDate 			= ($studentCourseData['time_ended_debtor']) ? ($studentCourseData['time_ended_debtor']) : (false); //--Дата окончания продления.
		$view->exam_type 			= $studentCourseData['exam_type'];
		#$view->users_groups 		= $studentCourseData['users_groups'];		
		$view->isNewActionStudent 	= $studentCourseData['isNewActionStudent'];

        if ($isEndUser) {

            $courseIsGraduated = $graduatedList ? $graduatedList->exists('CID', $subject->subid) : false;

            $view->graduated = $courseIsGraduated;

            if (!$courseIsGraduated) {
                if (($subject->reg_type == HM_Subject_SubjectModel::REGTYPE_FREE || $subject->reg_type == HM_Subject_SubjectModel::REGTYPE_MODER) && $subject->isAccessible()){
                    $view->disperse = true;
                }
				
				
            }
			/*
			$view->reasonFail = array();
			if($marks[$subject->subid] > 0){
				$view->reasonFail 	= $subjService->getFailPassMessage($userId, $subject->subid);					
			}
			*/
			
			$view->mark         = $marks->exists('cid', $subject->subid);
			$view->markFailInfo = $view->mark ? $view->mark->getHumanInfo() : false;
			
			$failLessons = array();
			if($view->markFailInfo){
				foreach($view->markFailInfo as $info){
					if(is_array($info['lessons'])){
						foreach($info['lessons'] as $lessonId){
							$failLessons[$lessonId] = $lessonId;
						}
					}
				}
			}
			$additional['failLessons'] = $failLessons;
        }		
        $this->createDescriptionTabs($descriptionId, $subject, $additional);
		
		$view->isEndUser = $isEndUser;
		
		return $view->render('subject-preview.tpl');
    }

    protected function createDescriptionTabs($descriptionId, $subject, $additional = false) {
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	
		
        $services = Zend_Registry::get('serviceContainer');

        $lessonService      = $services->getService('Lesson');
        $lessonAssignService= $services->getService('LessonAssign');
        $userService        = $services->getService('User');
        $interviewService   = $services->getService('Interview');
        $subjectService    	= $services->getService('Subject');
        $serviceAcl    		= $services->getService('Acl');
        $serviceLJResult	= $services->getService('LessonJournalResult');		
		
		$currentUserRole	= $userService->getCurrentUserRole();

        $view = $this->view;

		if($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_STUDENT))){
			$where = $lessonService->quoteInto(
				array(
					'self.MID = ?',
					' AND Lesson.CID = ?',
					' AND Lesson.isfree = ?'
				),
				array(
					(int) $userService->getCurrentUserId(),
					$subject->subid,
					HM_Lesson_LessonModel::MODE_PLAN,
				)
			);
			$assigns = $lessonAssignService->fetchAllDependenceJoinInner('Lesson', $where);
		} else {
			$where = $lessonService->quoteInto(array('schedule.CID = ?', ' AND schedule.isfree = ?'), array($subject->subid, 	HM_Lesson_LessonModel::MODE_PLAN));			
			$assigns = $lessonService->fetchAll($where);			
		}

        $lessonsArray = array();
        $now = time();

        /**
         * @var $lesson HM_Lesson_LessonModel
         */	
		if($serviceAcl->inheritsRole($currentUserRole,array(HM_Role_RoleModelAbstract::ROLE_TUTOR,HM_Role_RoleModelAbstract::ROLE_TEACHER))){			
			$assignedUsers = $subjectService->getAssignedUsers($subject->subid);
			$studentIDs	= false;
			$countStudents = count($assignedUsers);
			
			if($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_TUTOR))){
				$studentIDs = $subjectService->getAvailableStudents($userService->getCurrentUserId(), $subject->subid);
				if($studentIDs !== false){					
					$countStudents = 0;
					foreach($assignedUsers as $user){
						if(isset($studentIDs[$user->MID])){ $countStudents++; }
					}				
				}
			} 		
			$newActionsStudents = $subjectService->getNewActionStudent($subject->subid, $studentIDs);			
		}

		foreach ($assigns as $assign) {									
			if(!$assign){ continue; }
			if(
				$serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_TUTOR))
				&& 
				!$lessonService->isAvailable($userService->getCurrentUserId(), $assign->SHEID, $subject->subid)
			){				
				continue;
			}
            
			if($services->getService('Acl')->inheritsRole($services->getService('User')->getCurrentUserRole(),array(HM_Role_RoleModelAbstract::ROLE_STUDENT))){
				$lesson = $lessonService->getOne($assign->lessons);	
			} else {
				$lesson = $assign;	
			}
			if(!$lesson){ continue; }

			$status = '';
            if($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_STUDENT))){ //--выводим детализацию по урокам в табе.				
				$current_ball 	= ($assign->V_STATUS == -1) ? (0) : ($assign->V_STATUS);		 
				$max_ball 		= $lesson->max_ball;
	            $practic_html   = '';
				if($lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){
					#$max_ball    += $serviceLJResult->getPracticMaxBall($lesson->CID);					
					$current_ball = $assign->ball_academic;
					$practic_html = ' / '.$assign->ball_practic._(' из ').$serviceLJResult->getPracticMaxBall($lesson->CID);
				}

				$status = $current_ball._(' из ').$max_ball.$practic_html;
				if($lesson->typeID == HM_Event_EventModel::TYPE_TASK){ //--для задания находим статус последнего сообщения
					$lastMessage = $interviewService->getLastMessage($lesson->SHEID, $userService->getCurrentUserId());
					if($lastMessage){
						$types = HM_Interview_InterviewModel::getTypes();
						if($types[$lastMessage['type']]){
							$status = '('.$types[$lastMessage['type']].') '.$status;					
						}
					}					
				}
			}
			
			if($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_TUTOR,HM_Role_RoleModelAbstract::ROLE_TEACHER))){ //--выводим детализацию по урокам в табе.
				if($lesson->typeID == HM_Event_EventModel::TYPE_TASK){
					$status = ($newActionsStudents[$lesson->SHEID]) ? ($newActionsStudents[$lesson->SHEID]) : (0);
					$status .= _(' из ').$countStudents;
				}
			}
			
            if ($lesson->isRelative()) {
                $lesson->setAssigns(array($assign));
                $lesson->end = $lesson->getEndDateRelative();
                $lesson->begin = $lesson->getBeginDateRelative();
            }

            $begin = strtotime($lesson->begin);
            $end   = strtotime($lesson->end);
            $isFree = $lesson->isTimeFree();

            $datetimeFormat = (($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_DATES) && (date('H:i', $begin) != '00:00')) ? 'd.m H:i' : 'd.m';

            $lessonUrlParams = array(
                'action'     => 'index',
                'controller' => 'execute',
                'module'     => 'lesson',
                'lesson_id'  => $lesson->SHEID,
                'subject_id' => $lesson->CID
            );

			if($lng == 'eng' && $lesson->title_translation != '')
				$lesson->title = $lesson->title_translation;			
			
			$failLesson = false;
			if($additional['failLessons'] && in_array($lesson->SHEID, $additional['failLessons'])){
				$failLesson = true;
			}
			
            $lessonsArray[] = array(
                'CID'         => $lesson->CID,
                'description' => $lesson->descript,
                'title'       => $lesson->title,
                'isFree'      => $isFree,
                'begin'       => date($datetimeFormat, $begin),
                'end'         => date($datetimeFormat, $end),
                'isExpired'   => (!$isFree) && ($end < $now),
                'url'         => $view->url($lessonUrlParams, false, true),
				'status'	  => $status,
				'order'	  	  => $lesson->order,
				'failLesson'  => $failLesson,
            );
        }
		
		# Сортировка занятий.
		$tmp = array();
		foreach($lessonsArray as $i){
			$key = $i['order'];			
			if(isset($tmp[$key])){ $key = count($lessonsArray) + count($tmp); } # Если 2 одинаковых значения поля сортировки, то второй элемент отправляем на комчатку. И неважно, какой у него был вес порядка.
			$tmp[$key] = $i;				
		}
		ksort($tmp);
		$lessonsArray = $tmp;

        $HM = $view->HM();

        // создаём описание курса с табами в правой части
        $HM->create('hm.module.course.ui.list.CourseDescriptionTabs', array(
            'renderTo' => '#'.$descriptionId,
            'lessons'  => $lessonsArray,
            'course_id' => $subject->subid
        ));
    }
	
	protected function filteredGroups($groups, $user_groups)
	{
		$collection = new HM_Collection();
		$collection->setModelClass($groups->getModelClass());
		
		if(empty($user_groups)){ return $collection; }
		
		foreach($user_groups as $name){
			$group = $groups->exists('name', $name);
			if(!$group){ continue; }
			$collection[count($collection)] = $group;
		}
		return $collection;
	}	
}