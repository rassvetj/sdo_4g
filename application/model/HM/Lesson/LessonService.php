<?php
class HM_Lesson_LessonService extends HM_Service_Abstract
{
	protected $_lessonTypes  = array(); //--array([subject_id] => [lesson_id] = HM_Subject_Mark_MarkModel::BALL_CURRENT || HM_Subject_Mark_MarkModel::BALL_EXAM)
	protected $_maxBallMediumRating  = array(); # key - subject id, value - max ball
	
	
	const CACHE_NAME = 'HM_Lesson_LessonService';

    public function insert($data)
    {
        $data = $this->_processGroupDate($data);
        $data = $this->_processCondition($data);

        $data['max_mark'] = $data['vedomost'] ? $this->getMaxMark($data['typeID']) : 0;

        $result = parent::insert($data);

        if ($result) {
            $this->getService('LessonAssign')->insert(array('SHEID' => $result->SHEID, 'MID' => 0));
			
			$this->getService('Subject')->updateLandmark($data['CID']);
        }
        return $result;
    }

    protected function getMaxMark($typeID)
    {
        if (in_array($typeID,array(HM_Event_EventModel::TYPE_RESOURCE, HM_Event_EventModel::TYPE_LECTURE, HM_Event_EventModel::TYPE_COURSE))) {
            return 1;
        } else {
            return 100;
        }
    }

    protected function _processCondition($data)
    {
        if (isset($data['Condition'])) {
            switch($data['Condition']) {
            case HM_Lesson_LessonModel::CONDITION_NONE:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_progress'] = '0';
                $data['cond_avgbal'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_PROGRESS:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_avgbal'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_AVGBAL:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_progress'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_SUMBAL:
                $data['cond_sheid'] = '';
                $data['cond_mark'] = '';
                $data['cond_progress'] = '0';
                $data['cond_avgbal'] = '0';
                break;
            case HM_Lesson_LessonModel::CONDITION_LESSON:
                $data['cond_progress'] = '0';
                $data['cond_avgbal'] = '0';
                $data['cond_sumbal'] = '0';
                break;
            }
            unset($data['Condition']);
        }
        unset($data['Condition']);
        return $data;
    }

    protected function _processGroupDate($data)
    {
        if (isset($data['GroupDate'])) {
            $data['startday'] = '';
            $data['stopday'] = '';
            switch($data['GroupDate']) {
            case HM_Lesson_LessonModel::TIMETYPE_FREE:
                $data['begin'] = $this->getDateTime();
                $data['end'] = $data['begin'];
                $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_FREE;
                break;
            case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                try {
                    $begin = new HM_Date($data['currentDate'].' '.$data['beginTime']);
                } catch(Zend_Date_Exception $e) {
                    $begin = new HM_Date();
                }
                try {
                    $end = new HM_Date($data['currentDate'].' '.$data['endTime']);
                } catch (Zend_Date_Exception $e) {
                    $end = new HM_Date();
                }
                $data['begin'] = $begin->toString('yyyy-MM-dd HH:mm');
                $data['end'] = $end->toString('yyyy-MM-dd HH:mm');
                $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_DATES;
                break;
            case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                $data['begin'] = $this->getDateTime();
                $data['end'] = $this->getDateTime();
                $data['startday'] = $data['beginRelative']*24*60*60;
                $data['stopday'] = $data['endRelative']*24*60*60;
                $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_RELATIVE;
                break;
            default:
                //if (!strlen($data['beginDate']))
                try {
                    $begin = new HM_Date($data['beginDate']);
                } catch (Zend_Date_Exception $e) {
                    $begin = new HM_Date();
                }
                $begin->set('00:00', Zend_Date::TIMES);

                try {
                    $end = new HM_Date($data['endDate']);
                } catch (Zend_Date_Exception $e) {
                    $end = new HM_Date();
                }
                $end->set('23:59', Zend_Date::TIMES);
                $data['begin'] = $begin->toString('yyyy-MM-dd HH:mm');
                $data['end'] = $end->toString('yyyy-MM-dd HH:mm');
                $data['timetype'] = HM_Lesson_LessonModel::TIMETYPE_DATES;
            }

            unset($data['GroupDate']);
            unset($data['beginDate']);
            unset($data['endDate']);
            unset($data['currentDate']);
            unset($data['beginTime']);
            unset($data['endTime']);
            unset($data['beginRelative']);
            unset($data['endRelative']);
        }
        return $data;
    }

    public function update($data)
    {
        $data = $this->_processCondition($data);
        $data = $this->_processGroupDate($data);
        $data['max_mark'] = $data['vedomost'] ? $this->getMaxMark($data['typeID']) : 0;
        $lesson = parent::update($data);
		
		$this->getService('Subject')->updateLandmark($data['CID']);
		
        return $lesson;
    }

    public function deleteFromConditions($lessonId)
    {
        $this->updateWhere(array('cond_sheid' => ''), $this->quoteInto('cond_sheid = ?', $lessonId));

        $collection = $this->fetchAll(
            $this->quoteInto(
                array('cond_sheid LIKE ?', ' OR cond_sheid LIKE ?', ' OR cond_sheid LIKE ?'),
                array("$lessonId#%", "%#$lessonId#%", "%#$lessonId")
            )
        );

        if (count($collection)) {
            foreach($collection as $lesson) {
                $necessary = $lesson->getNecessaryLessonsId();
                if (is_array($necessary) && count($necessary)) {
                    for($i=0; $i < count($necessary); $i++) {
                        if ($necessary[$i] == $lessonId) {
                            unset($necessary[$i]);
                        }
                    }
                    $this->update(array('SHEID' => $lesson->SHEID, 'cond_sheid' => join('#', $necessary)));
                }
            }
        }

    }

    public function delete($lessonId)
    {
        $lesson = $this->find($lessonId)->current();
        $params = $lesson->getParams();
        $typeId = $lesson->typeID;
        $moduleId = $params['module_id'];

        if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
            $typeId = HM_Event_EventModel::TYPE_COURSE; // открываем весь модуль
            $moduleId = $params['course_id'];
            $siblings = $this->fetchAll($this->quoteInto(
                array('params LIKE ?'),
                array('%course_id='.$moduleId.';%')
            ));
            if (count($siblings) > 1) $moduleId = false;
        }
        $this->setLessonFreeMode($moduleId, $typeId, $lesson->CID, HM_Lesson_LessonModel::MODE_FREE);

        // Удаление назначений
        $this->getService('LessonAssign')->deleteBy($this->quoteInto('SHEID = ?', $lessonId));

        // Удаление тестов
        $this->getService('Test')->deleteBy($this->quoteInto('lesson_id = ?', $lessonId));

        // Удаление из условий
        $this->deleteFromConditions($lessonId);

        return parent::delete($lessonId);
    }

    public function assignStudents($lessonId, $students, $unassign = true, $taskUserVars = array())
    {
        $lesson = $this->getOne($this->find($lessonId));
        $studentForUpdates = array();
        if (is_array($students) && count($students)) {
            #$assigns = $this->getService('LessonAssign')->fetchAll($this->quoteInto('SHEID = ? AND MID > 0', $lessonId));
			$assigns = $this->getService('Student')->fetchAll($this->quoteInto('CID = ? AND MID > 0', $lesson->CID));

            if (count($assigns)) {
                foreach($assigns as $assign) {
                    if (in_array($assign->MID, $students)) {
                        $key = array_search($assign->MID, $students);
                        if (false !== $key && !in_array($students[$key], $studentForUpdates)) {
                            $studentForUpdates[] = $students[$key];
                            unset($students[$key]);
                        }
                    } else {
                        if ($unassign) {
                            $this->unassignStudent($lessonId, $assign->MID);
                        }
                    }
                }
            }

            foreach($students as $studentId) {
                $this->assignStudent($lessonId, $studentId, (isset($taskUserVars[$studentId]))? $taskUserVars[$studentId] : null );
            }
            $triggerService = $this->getService('LessonAssignESTrigger');
            if ((int)$lesson->isfree == HM_Lesson_LessonModel::MODE_PLAN) {
                $this->getService('EventDispatcher')->notify(
                    new sfEvent(
                        $triggerService,
                        get_class($triggerService).'::esPushTrigger',
                        array(
                            'lesson' => $lesson,
                            'students' => $students
                        )
                    )
                );
            }

//[che 3.06.2014 #16963] //Отсутствовал вызов обновления - в $this->assignStudent->CreateTask он было, но кривое. Правда и в updateTasks - тоже не работало. Короче, была реализована тройная защита от обновления :)
            $this->getService('Question')->updateTasks($lesson, $studentForUpdates, $taskUserVars);
            $this->updateDates($studentForUpdates, $lessonId);
//
        }
    }
    public function updateDates($students, $lessonId){
        $lesson = $this->getOne($this->find($lessonId));

        foreach($students as $studentId){
            if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_RELATIVE) {
                switch($lesson->typeID) {
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                    $assign = $this->getOne(
                        $this->getService('LessonAssign')->fetchAll(
                            $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson->SHEID, $studentId))
                        )
                    );
                    if ($assign) {
                        $base = $assign->created;
                    }
                    break;
                default:
                    $student = $this->getOne(
                        $this->getService('Student')->fetchAll(
                            $this->quoteInto(array('CID = ?', ' AND MID = ?'), array($lesson->CID, $studentId))
                        )
                    );
                    if ($student) {
                        $base = (max($lesson->startday,$lesson->stopday) > 0) ? $student->time_registered : $student->time_ended_planned; // если кол-во дней отрицательное, то отсчитывать от конца курса
                    } else {
                        return false;
                    }
                }

                if ($lesson->startday) {
                    $begin = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->startday/86400);
                    $lessonData['beginRelative'] = $begin->get('Y-M-d');
                } else {
                    $lessonData['beginRelative'] = null;
                }
                if ($lesson->stopday) {
                    $end = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->stopday/86400);
                    $lessonData['endRelative'] = $end->get('Y-M-d') . ' 23:59:59';
                } else {
                    $lessonData['endRelative'] = null;
                }


                $this->getService('LessonAssign')->updateWhere(array('beginRelative' => $lessonData['beginRelative'], 'endRelative' => $lessonData['endRelative']), array('SHEID = ?' => $lessonId, 'MID = ?' => $studentId));

            }
        }
    }


    /**
     * @param $lessonId
     * @param HM_Form_Element_Html5File|HM_Form_Element_ServerFile $photo Элемент формы
     * @param $destination Путь к папке с иконками
     * @return bool
     */
    public static function updateIcon($lessonId, $photo, $destination = null)
    {
        $destination = Zend_Registry::get('config')->path->upload->lesson;
        return HM_Subject_SubjectService::updateIcon($lessonId, $photo, $destination);
    }

    public function assignStudent($lessonId, $studentId, $taskVariant = null)
    {
        if ($studentId === null || intval($studentId) == 0) return false;
        $lesson = $this->getOne($this->find($lessonId));

        //if ( $lesson->teacher != $studentId ) {
        $this->getService('Question')->createTask($lessonId, $studentId, $taskVariant);
        //}

        // если занятие с типом форум, то пользователя еще и подписываем на уведомления
        if ($lesson->typeID == HM_Activity_ActivityModel::ACTIVITY_FORUM) {
            $this->getService('Subscription')->subscribeUserToChannelByLessonId($studentId,$lessonId);
        }
		
		$existingLessonAssign = $this->getOne(
									$this->getService('LessonAssign')->fetchAll(
										$this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lessonId, $studentId))
									)
								);
		
		if(!empty($existingLessonAssign)){
			return $existingLessonAssign;
		}
		

        $lessonData = array(
            'SHEID'      => (int) $lessonId,
            'MID'        => (int) $studentId,
            'isgroup'    => 0,
        );
        // вычисляем относительные даты и записываем в базу
        if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_RELATIVE) {

            switch($lesson->typeID) {
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                $assign = $this->getOne(
                    $this->getService('LessonAssign')->fetchAll(
                        $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson->SHEID, $studentId))
                    )
                );
                if ($assign) {
                    $base = $assign->created;
                } else {
                    $base = $this->getDateTime();
                }
                break;
            default:
                $student = $this->getOne(
                    $this->getService('Student')->fetchAll(
                        $this->quoteInto(array('CID = ?', ' AND MID = ?'), array($lesson->CID, $studentId))
                    )
                );
                if ($student) {
                    $base = (max($lesson->startday,$lesson->stopday) > 0) ? $student->time_registered : $student->time_ended_planned; // если кол-во дней отрицательное, то отсчитывать от конца курса
                } else {
                    return false;
                }
            }

            if ($lesson->startday && $base) {
                $begin = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->startday/86400);
                $lessonData['beginRelative'] = $begin->get('Y-M-d');
            } else {
                $lessonData['beginRelative'] = null;
            }
            if ($lesson->stopday && $base) {
                $end = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->stopday/86400);
                $lessonData['endRelative'] = $end->get('Y-M-d') . ' 23:59:59';
            } else {
                $lessonData['endRelative'] = null;
            }
                /*$begin = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->startday/86400);
                $end = HM_Date::getRelativeDate(new Zend_Date(strtotime($base)), $lesson->stopday/86400);

                if ($lesson->startday) $lessonData['beginRelative'] = $begin->get('Y-M-d');
                if ($lesson->stopday) $lessonData['endRelative'] = $end->get('Y-M-d') . ' 23:59:59';*/

        }

        return $this->getService('LessonAssign')->insert($lessonData);
    }

    public function unassignStudent($lessonId, $studentId)
    {
        $this->getService('Interview')->deleteBy(array($this->getService('Interview')->quoteInto(array('to_whom = ? ',' AND lesson_id = ? '),array($studentId,$lessonId))));
        return $this->getService('LessonAssign')->deleteBy(sprintf("SHEID = '%d' AND MID = '%d'", $lessonId, $studentId));
	}

    public function unassignAllStudents($lessonId)
    {
        $this->getService('Interview')->deleteBy($this->getService('Interview')->quoteInto('lesson_id = ? ',$lessonId));
        return $this->getService('LessonAssign')->deleteBy($this->getService('LessonAssign')->quoteInto('SHEID = ? AND MID > 0', $lessonId));
    }

    public function isUserAssigned($lessonId, $userId)
    {
        $collection = $this->getService('LessonAssign')->fetchAll($this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lessonId, $userId)));
        return count($collection);
    }

    public function isLaunchConditionSatisfied($lessonId, $lesson = null, $checkRole = true)
    {
        if ($checkRole
            && !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
            //&& !in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_STUDENT))
        ) return true;

        if (null == $lesson) {
            $lesson = $this->getOne($this->find($lessonId));
        }

        $conditionLesson = $conditionProgress = $conditionAvg = $conditionSum = null;

        if ($lesson) {
            if ($lesson->cond_sheid && $lesson->cond_mark) {
                $sheids = explode('#', $lesson->cond_sheid);
                $marks  = explode('#', $lesson->cond_mark);
                if (is_array($sheids) && count($sheids) && is_array($marks) && count($marks) && (count($sheids) == count($marks))) {
                    $conditions = array();
                    foreach($sheids as $index => $sheid) {
                        $conditions[] = sprintf('(%s)', $this->quoteInto(array('SHEID = ?', ' AND V_STATUS >= ?'), array($sheid, (float) $marks[$index])));
                    }
                    if (count($conditions)) {
                        $collection = $this->getService('LessonAssign')->fetchAll(
                            $this->quoteInto('MID = ?', $this->getService('User')->getCurrentUserId())
                            .' AND ('.join(' OR ', $conditions).')'
                        );

                        $conditionLesson = (count($collection) == count($sheids));
                    }
                }
            }

            if ($lesson->cond_progress || $lesson->cond_avgbal || $lesson->cond_sumbal) {
                $collection = $this->getService('LessonAssign')->fetchAllDependenceJoinInner(
                    'Lesson',
                    $this->quoteInto(
                        array(
                            'self.MID = ?',
                            ' AND Lesson.CID = ? AND Lesson.vedomost = 1',
                            ' AND typeID NOT IN (?)',
                            ' AND isfree = ?'
                        ),
                        array(
                            $this->getService('User')->getCurrentUserId(),
                            $lesson->CID,
                            array_keys(HM_Event_EventModel::getExcludedTypes()),
                            HM_Lesson_LessonModel::MODE_PLAN
                        )
                    )
                );
                if (count($collection)) {
                    $lessons = array();
                    $lessonsCompleted = $lessonsTotal = $lessonsSumBal = $lessonsProgress = $lessonsAvgBal = 0;
                    foreach($collection as $item) {
                        if ($item->V_STATUS > 0) {
                            $lessonsCompleted++;
                            $lessonsSumBal += $item->V_STATUS;
                        }
                        $lessons[$item->SHEID] = 1;
                        //$lessonsTotal++;
                    }

                    $lessonsTotal = count($lessons);

                    if ($lessonsTotal)
                        $lessonsProgress = floor(doubleval(($lessonsCompleted/$lessonsTotal)*100));
                    if ($lessonsCompleted)
                        $lessonsAvgBal = $lessonsSumBal/$lessonsCompleted;
                }

                if ($lesson->cond_progress) {
                    $conditionProgress = $lesson->checkInterval($lessonsProgress, $lesson->cond_progress);
                }

                if ($lesson->cond_avgbal) {
                    $conditionAvg = $lesson->checkInterval($lessonsAvgBal, $lesson->cond_avgbal);
                }

                if ($lesson->cond_sumbal) {
                    $conditionSum = $lesson->checkInterval($lessonsSumBal, $lesson->cond_sumbal);
                }
            }
        }

        $return = !(integer)$lesson->cond_operation;
        foreach (array($conditionLesson, $conditionProgress, $conditionAvg, $conditionSum) as $argument) {
            if (null !== $argument) {
                $return = $lesson->cond_operation ? $return || $argument : $return && $argument;
            }
        }

        return $return;

    }

    protected function _isExecutableForDean($lesson)
    {
        return true;
    }

    protected function _isExecutableForTeacher($lesson)
    {
        if (!$this->getService('Subject')->isTeacher($lesson->CID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не являетесь преподавателем на курсе'));
        }

        return true;
    }
    
    protected function _isExecutableForTutor($lesson)
    {
        if (!$this->getService('Subject')->isTutor($lesson->CID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не являетесь тьютером на курсе'));
        }

        return true;
    }

    protected function _isExecutableForStudent($lesson)
    {
        $registered = null;
        $isGraduated = $this->getService('Subject')->isGraduated($lesson->CID, $this->getService('User')->getCurrentUserId());
        $isStudent = $this->getService('Subject')->isStudent($lesson->CID, $this->getService('User')->getCurrentUserId());
        if (in_array($lesson->typeID, array_keys(HM_Event_EventModel::getDeanPollTypes()))) {
            if (!$isGraduated) {
                throw new HM_Exception(_('Вы не являетесь прошедшим обучения на курсе'));
            }
        } else {
            if (!$isStudent && !$isGraduated) {
                throw new HM_Exception(_('Вы не являетесь слушателем на курсе'));
            } elseif (!$isStudent && $lesson->vedomost) {
                throw new HM_Exception(_('Вы переведены в прошедшие обучение на этом курсе; запуск занятий на оценку не разрешен.'));
            }
        }

        if (!$this->isUserAssigned($lesson->SHEID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не назначены на занятие'));
        }

        // Проверка дат (только для студентов)
        if (!$lesson->isExecutable()) {
            throw new HM_Exception(_('Занятие назначено на другое время'));
        }

        // Проверка условий запуска
        if (!$this->isLaunchConditionSatisfied($lesson->SHEID, $lesson)) {
            throw new HM_Exception(_('Условия запуска занятия не выполнены'));
        }

        return true;

    }

    protected function _isExecutableForDefault($lesson)
    {
        throw new HM_Exception(_('Нет прав для запуска данного занятия'));
    }

    protected function _isExecutableForRole($lesson)
    {

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
            $this->_isExecutableForStudent($lesson);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
            $this->_isExecutableForTeacher($lesson);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_SUPERVISOR))) {
            $this->_isExecutableForDean($lesson);
        } elseif ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
            $this->_isExecutableForTutor($lesson);
        } else {
            $this->_isExecutableForDefault($lesson);
        }

/*        switch($this->getService('User')->getCurrentUserRole()) {
            case HM_Role_RoleModelAbstract::ROLE_TEACHER:
                $this->_isExecutableForTeacher($lesson);
                break;
            case HM_Role_RoleModelAbstract::ROLE_STUDENT:
                $this->_isExecutableForStudent($lesson);
                break;
            case HM_Role_RoleModelAbstract::ROLE_DEAN:
                $this->_isExecutableForDean($lesson);
                break;
            default:
                if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)) {
                    $this->_isExecutableForStudent($lesson);
                } else {
                    $this->_isExecutableForDefault($lesson);
                }
                break;
}*/
    }

    public function isExecutable($lessonId)
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', $lessonId));
        if ($lesson) {
            $this->_isExecutableForRole($lesson);
            return true;
        } else {
            throw new HM_Exception(_('Занятие не найдено'));
        }
    }

    /**
     * Возвращает subject_id
     *
     * @author Artem Smirnov
     * @date 19.02.2013
     * @param $lessonID
     * @return string
     */
    public function getSubjectByLesson($lessonID)
    {
        /** @var $lessonService HM_Lesson_LessonService */
        $lesson = $this->findDependence('Subject',$lessonID)->current();
        return $lesson->subject->current();
    }
	
	
	/** 	 
	*/
	# дла ДО разделение на "Итоговый текущий рейтинг" и "Рубежный рейтинг" идет не по занятиям, а по алгоритму , как в "Экспорт результатов обучения" - в % от максимального балла.
    public function getUsersScore($courseId, $fromDate = '', $toDate = '', $group = null, $forGraduated = false ){

		if($courseId == 0){
            return false;
        }
		$subject = $this->getService('Subject')->getById($courseId);
        $groupUsers = array();
		#$subject = $this->getService('Subject')->getById($courseId);
		
		$issetIPZ = $this->issetIPZ($courseId);

        if ($group !== null ) {
            $group = explode ('_',$group);

            /* Параметр Учебная группа */
            if ($group[0] == 'sg') {
                $users = $this->getService('StudyGroupUsers')->getUsersOnCourse((int)$group[1],$courseId);
                $users_custom = $this->getService('StudyGroupUsers')->getUsersOnCourseCustom((int)$group[1],$courseId);					
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
                $users = $this->getService('GroupAssign')->fetchAll(array('gid=?' => (int)$group[1], 'cid=?' => $courseId));
                if (count($users)) {
                    $groupUsers = array_keys($users->getList('mid','gid'));
                }
            }

            /* Если лдевый параметр удаляем переменную */
            if ($group[0] != 's' && $group[0] != 'sg') {
                unset($group);
            }
        }

        if (!$forGraduated) {
            $students = $this->getService('User')->fetchAllDependenceJoinInner('Student', $this->quoteInto('Student.CID = ?', $courseId));
        } else {
            $students = $this->getService('User')->fetchAllDependenceJoinInner('Graduated', $this->quoteInto('Graduated.CID = ?', $courseId));
        }

        $total = $this->getOne($this->getService('Subject')->fetchAllHybrid('Mark', 'User', 'Student', $this->quoteInto('subid = ?', $courseId)));
	
        //$students = $tt->users;

        $collection = $this->getService('Lesson')->fetchAllDependenceJoinInner(
            'Assign',
            $this->quoteInto(array('self.CID  = ?', ' AND self.vedomost = ?', ' AND isfree = ?'), array($courseId, 1, HM_Lesson_LessonModel::MODE_PLAN)),
            'self.order'
        );

        // $schedule->typeID == -($event->event_id)
        $events = $eventIdsNegative = $eventIds = array();
        $eventIdsNegative = $collection->getList('SHEID', 'typeID');
        if (count($eventIdsNegative)) {
            foreach ($eventIdsNegative as $eventId) {
                if ($eventId < 0) {
                    $eventIds[-$eventId] = true;
                }
            }
        }
        if (count($eventIds)) {
            $eventsCollection = $this->getService('Event')->fetchAll(array('event_id IN (?)' => array_keys($eventIds)));
            foreach ($eventsCollection as $event) {
                $events[$event->event_id] = $event;
            }
        }

        $persons 			= array();
        $schedules 			= array();
		$dataRatingTotal 	= array();
		$taskRating 		= array(); # баллы за задания + журнал-практическая часть.
		$academRating 		= array(); # баллы за академическую активность только в журнале
		$dataRatingMedium 	= array();
        $scores = array();
		$total_ball			= array(); # сумма баллов по всем занятиям
		$taskMax            = 0;
		
		
        if (count($collection)) {
            foreach($collection as $item) {				
				$isTotalRating = ( (stristr($item->title, 'Итоговый тест') !== FALSE) || (stristr($item->title, 'Итоговый контроль') !== FALSE) ) ? (true) : (false);
				
                $schedules[$item->SHEID] = $item;				
                $assigns = $item->getAssigns();				
                if ($item->typeID && isset($events[-$item->typeID])) {
                    $item->setEvent($events[-$item->typeID]);
                }
				
				if($item->typeID == HM_Event_EventModel::TYPE_TASK && (stristr($item->title, 'задание') !== FALSE)){
					$taskMax += $item->max_ball;
				}

                if ($assigns) {
                    $inPeriod = false;

                    foreach($assigns as $assign) {						
					
                        if ($assign->MID > 0) {
                            if ($student = $students->exists('MID', $assign->MID)) {                                
								$persons[$student->MID] = $student;
                            }

                            if($fromDate != '' && $toDate != '' && $inPeriod == false){
                                $fromDate = new Zend_Date($fromDate);
                                $toDate = new Zend_Date($toDate);

                                switch($item->timetype){
                                case HM_Lesson_LessonModel::TIMETYPE_FREE:
                                    $inPeriod = true;
                                    break;
                                case HM_Lesson_LessonModel::TIMETYPE_DATES:
                                case HM_Lesson_LessonModel::TIMETYPE_TIMES:
                                    $begin = new Zend_Date($item->begin);
                                    $end = new Zend_Date($item->end);
                                    if( ($begin->getTimestamp() >= $fromDate->getTimestamp() && $begin->getTimestamp() <= $toDate->getTimestamp())
                                        || ($end->getTimestamp() >= $fromDate->getTimestamp() && $end->getTimestamp() <= $toDate->getTimestamp())
                                        || ($end->getTimestamp() >= $toDate->getTimestamp() && $begin->getTimestamp() <= $fromDate->getTimestamp())
                                        || ($end->getTimestamp() <= $toDate->getTimestamp() && $begin->getTimestamp() >= $fromDate->getTimestamp())){
                                            $inPeriod = true;
                                        }
                                    break;
                                case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                                    if($student != false){
                                        $begin = new Zend_Date($assign->beginRelative);
                                        $end = new Zend_Date($assign->endRelative);

                                        if( ($begin->getTimestamp() >= $fromDate->getTimestamp() && $begin->getTimestamp() <= $toDate->getTimestamp())
                                            || ($end->getTimestamp() >= $fromDate->getTimestamp() && $end->getTimestamp() <= $toDate->getTimestamp())
                                            || ($end->getTimestamp() >= $toDate->getTimestamp() && $begin->getTimestamp() <= $fromDate->getTimestamp())
                                            || ($end->getTimestamp() <= $toDate->getTimestamp() && $begin->getTimestamp() >= $fromDate->getTimestamp())){
                                                $inPeriod = true;
                                            }
                                    }
                                    break;
                                default:
                                    break;
                                }
                            }
							
							$score 				= $assign->V_STATUS;					
							$score 				= (stripos($item->title, 'итоговый контроль') !== false) ? round($score) : $score;
							$assign->V_STATUS 	= $score;


                            //                            switch($assign->V_STATUS) {
                            //                                case -2:
                            //                                    $assign->V_STATUS = _('Б');
                            //                                    break;
                            //                                case -3:
                            //                                    $assign->V_STATUS = _('Н');
                            //                                    break;
                            //                            }
							$isTotalPractic = $this->isTotalPractic($item);
							
							if(	   ($isTotalPractic && $assign->V_STATUS <= 0)
								|| (stristr($item->title, 'рубежный контроль') !== false)
								#|| (stristr($item->title, 'итоговый контроль') !== false)							
							){								
								$assign->isFail = !$this->isPassLesson($item, $assign->V_STATUS);	
							}
							
							if(($isTotalPractic && $assign->V_STATUS <= 0) || $assign->isFail){ # есть ИПЗ но его не сдавал еще. Если = 0, то значит сдавал, но получил неуд.								
								$scores[$assign->MID.'_failTotalPractic'] = 1;	
								
								
							}
							
                            $scores[$assign->MID.'_'.$assign->SHEID] = $assign;
/*                            if($total->marks instanceof HM_Collection){
                                    if($temp = $total->marks->exists('mid', $assign->MID)){
                                        $scores[$assign->MID.'_total'] = $temp;
                                    }
}*/

							


							if($assign->V_STATUS > 0) {
								if($item->typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE || $item->typeID == HM_Event_EventModel::TYPE_JOURNAL_LAB){
									if(isset($academRating[$assign->MID]))	{ $academRating[$assign->MID] = $academRating[$assign->MID] + $assign->ball_academic; }
									else 									{ $academRating[$assign->MID] = $assign->ball_academic; }
								
								} elseif($item->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){ # журнал - практическое занятие
									
									if(isset($taskRating[$assign->MID])){ $taskRating[$assign->MID] = $taskRating[$assign->MID] + $assign->ball_practic; }
									else 								{ $taskRating[$assign->MID] = $assign->ball_practic; }
									
									#  Итоговый текущий рейтинг																	
									if(isset($academRating[$assign->MID]))	{ $academRating[$assign->MID] = $academRating[$assign->MID] + $assign->ball_academic; }
									else 									{ $academRating[$assign->MID] = $assign->ball_academic; }

								# Рубежный рейтинг								
								} elseif($isTotalRating){ 
									if(isset($dataRatingTotal[$assign->MID]))	{ $dataRatingTotal[$assign->MID] = $dataRatingTotal[$assign->MID] + $assign->V_STATUS; }
									else 										{ $dataRatingTotal[$assign->MID] = $assign->V_STATUS; }	

								# на поощрения не должно накладываться ограниение в максимальный балл.
								} elseif($item->typeID == HM_Event_EventModel::TYPE_TASK && (stristr($item->title, 'поощрени') !== FALSE)){
                                    if(isset($dataRatingMedium[$assign->MID]))	{ $dataRatingMedium[$assign->MID] = $dataRatingMedium[$assign->MID] + $assign->V_STATUS; }
                                    else                                  		{ $dataRatingMedium[$assign->MID] = $assign->V_STATUS; }   
									
								} elseif($item->typeID == HM_Event_EventModel::TYPE_TASK && (stristr($item->title, 'задание') !== FALSE)){
									if(isset($taskRating[$assign->MID])){ $taskRating[$assign->MID] = $taskRating[$assign->MID] + $assign->V_STATUS; }
									else 								{ $taskRating[$assign->MID] = $assign->V_STATUS; }
									
								#  Итоговый текущий рейтинг
								} else { 
									if(isset($dataRatingMedium[$assign->MID]))	{ $dataRatingMedium[$assign->MID] = $dataRatingMedium[$assign->MID] + $assign->V_STATUS; }
									else 										{ $dataRatingMedium[$assign->MID] = $assign->V_STATUS; }
									
								}
							}
                        }
                    }
					
                    if($inPeriod == false && $fromDate != '' && $toDate != ''){
                        unset($schedules[$item->SHEID]);
                    }
                }
            }
			
			
			if(!empty($taskRating)){									
				foreach($taskRating as $user_id => $ball) {
					$normalize_ball = ($subject->isDO > 0) ? ($ball) : ($this->normalizeTask($ball, $issetIPZ, $taskMax));
					if(isset($dataRatingMedium[$user_id])){ $dataRatingMedium[$user_id] += $normalize_ball; }
					else 								  { $dataRatingMedium[$user_id]  = $normalize_ball; }					
				}
			}
			
			if(!empty($academRating)){									
				foreach($academRating as $user_id => $ball) {
					$normalize_ball = ($subject->isDO > 0) ? ($ball) : ($this->normalizeAcadem($ball));					
					if(isset($dataRatingMedium[$user_id])){ $dataRatingMedium[$user_id] += $normalize_ball; }
					else 								  { $dataRatingMedium[$user_id]  = $normalize_ball; }					
				}
			}
			
			if($subject->isDO < 1){ # не ДОТ
				$serviceLesson = $this->getService('Lesson');	
				foreach($dataRatingMedium as &$i){
					$i = $serviceLesson->normalizeTotalCurrentRating($i);				
				}
			}
			
			if(is_object($subject)){
				if($subject->isPractice()){					
					$maxBallTotalRating = $this->getMaxBallTotalRating($courseId);
					
					# Если за итоговый контроль (Рубежный рейтинг) больше 20 баллов, то это старый вариант практик, для которых оценки определяется по формуле ДО
					if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){				
						foreach($collection as $item) {
							$assigns = $item->getAssigns();
							if (!$assigns){ continue; }
							foreach($assigns as $assign){
								if($assign->V_STATUS < 0){ continue; }
								$total_ball[$assign->MID] += $assign->V_STATUS;
							}
						}
						$serviceSubject = $this->getService('Subject');
						foreach($total_ball as $student_id => $ball){
							$dataRatingMedium[$student_id] 	= $serviceSubject->getPracticeMarkCurrent($ball);
							$dataRatingTotal[$student_id]	= $serviceSubject->getPracticeMarksLandmark($ball);
						}
					}
				} else {
					
					$maxBallTotalRating = $this->getMaxBallTotalRating($courseId);
					
					if($_GET['dev'] == 76){
						var_dump($maxBallTotalRating);
					}
					
                    # Если балл более 20, то нужно нормализовать набранные баллы в соответствии с 80 на 20
                    if($maxBallTotalRating > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){                        
                        foreach($dataRatingMedium as $student_id => $mark_current){
							$old_mark_landmark = $dataRatingTotal[$student_id];
							$dataRatingMedium[$student_id] = HM_Subject_SubjectModel::normalizMarkCurrent($mark_current, $maxBallTotalRating, $old_mark_landmark);
                        }

                        foreach($dataRatingTotal as $student_id => $mark_landmark){
                            $dataRatingTotal[$student_id] = HM_Subject_SubjectModel::normalizMarkLandmark($mark_landmark, $maxBallTotalRating);
                        }
                    }

					foreach($dataRatingMedium as $student_id => $mark_current){
						if($mark_current > 80){
							$dataRatingMedium[$student_id] = 80;
						}
					}
					

						
                }
			}
        }
		
		
		if(
			$this->getService('Subject')->isDOT($courseId)
			||
			$subject->isWithoutHours()
		){
			$divide_balls = $this->reDivideDotBalls($dataRatingMedium, $dataRatingTotal);
			$dataRatingMedium = $divide_balls['marks_current'];
			$dataRatingTotal = $divide_balls['marks_landmark'];
		}
		
		
		 
		
		
		$moduleData 	= array();
		$isMainModule = ($this->getService('Subject')->isMainModule($courseId)) ? true : false;
		

        // сортировка юзеров по ФИО
        $persons = array();		
        if (count($students)) {
			
			# ограничение для тьюторов по назначенным на них студентов
			$subjectService = $this->getService('Subject');	
			$userService = $this->getService('User');		
			if ($this->getService('Acl')->inheritsRole($userService->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) {
				$studentIDs = $subjectService->getAvailableStudents($userService->getCurrentUserId(), $courseId);				
			}
			$subject = $this->getService('Subject')->getById($courseId);
			$isDO	 = $this->getService('Subject')->isDOT($courseId);
            foreach($students as $student) {
				if($student->blocked == HM_User_UserModel::STATUS_BLOCKED){ continue; }
				
				if($studentIDs !== false && is_array($studentIDs)){	
					if(!in_array($student->MID, $studentIDs)){ continue; }					
				}				
                if ( !in_array($student->MID,$groupUsers) && $group ) continue;
                $student->studyGroups = $this->getService('StudyGroupUsers')->getUserGroups($student->MID);
                $persons[$student->MID] = $student;

                if ($total && $total->marks) {
                    if ($temp = $total->marks->exists('mid', $student->MID)) {
						if($isMainModule){							
							$integrateMediumRating 	= $this->getService('Subject')->getIntegrateMediumRating($subject->module_code, $student->MID, $subject->semester);
							$sumMark 				= round($integrateMediumRating) + round($dataRatingTotal[$student->MID]);							
						} else {
							$sumMark = ($dataRatingTotal[$student->MID] > 0) ? (	round($dataRatingTotal[$student->MID]) + round($dataRatingMedium[$student->MID])	) : (false);
						}
						
						if($isDO){
							#$sumMark = round($temp->mark);
						}
						
						$sumMark = $this->normalizeTotalBall($sumMark);
						
                        $scores[$student->MID.'_total'] = array(
																'mark'		=> $sumMark,
																'comment'	=> $temp->comments,
																'mark_5' 	=> $this->getFiveScaleMark($sumMark),
						);
						if($isMainModule){
							$scores[$student->MID.'_total']['integrateMediumRating'] = $integrateMediumRating;
						}
                        continue;
                    }
                }

                // если итоговая оценка не выставлена
                $scores[$student->MID.'_total'] = array('mark'=>HM_Scale_Value_ValueModel::VALUE_NA,'comment'=>'');
            }
        }
        @uasort($persons, array($this,'userCompare'));

		$this->sortLessons(&$schedules);

        return array($persons, $schedules, $scores, $dataRatingMedium, $dataRatingTotal);
    }

    /**
     * Для сортировки пользователей по ФИО
     */
    public function userCompare ($a,$b)
    {
        return strcmp($a->getName(), $b->getName());
    }

    public function isTeacher($lessonId, $userId)
    {
        $lesson = $this->getOne($this->find($lessonId));
        if ($lesson) {
            return ($lesson->teacher == $userId);
        }
        return false;
    }

    public function getUsersStats($from, $to, $subjectId)
    {

        $from = date('Y-m-d', strtotime($from));
        $to = date('Y-m-d', strtotime($to));

        $select = $this->getSelect();

        $select->from(
            array('sc' => 'scorm_tracklog'),
            array('mid', 'start', 'stop')
        )
        ->joinInner(array('subjects_courses'), 'subjects_courses.course_id = sc.CID', array())
        ->where('subjects_courses.subject_id  = ?', $subjectId)
        ->where('sc.start >= ?',  $from . ' 00:00')
        ->where('sc.stop <= ?',  $to . ' 23:59:59');

        $query = $select->query();

        $fetch = $query->fetchAll();

        $users = array();
        $time = 0;
        $count = 0;
        foreach($fetch as $val){
            if(!isset($users[$val['mid']]))
            {
                $count++;
                $users[$val['mid']] = true;
            }

            $time = $time + (strtotime($val['stop']) - strtotime($val['start']));
        }
        return array('time' => $time, 'count' => $count);

    }

    public function getAssignedForLeader($lessonId)
    {
        $leaders = $this->getService('LessonAssign')->fetchAll(array('SHEID = ?' => $lessonId));
        $leaders = $leaders->getList('MID', 'SSID');
        $student = $this->getService('LessonDeanPollAssign')->fetchAll(array('lesson_id = ?' => $lessonId, 'head_mid IN (?)' => array_keys($leaders)));

        $studentList = $student->getList('student_mid', 'lesson_id');

        $students = $this->getService('User')->fetchAll(array('MID IN (?)' => array_keys($studentList)));

        return $students;
    }

    public function getAvailableStudents($subjectId)
    {
        return $this->getService('Subject')->getAssignedUsers($subjectId)->getList('MID', 'MID');
    }


    public function getTotalCoursePercent($lessonId, $userId, $courseId)
    {

        $items = $this->getService('CourseItem')->fetchAll(
            array('cid = ?' => $courseId, 'module <> ?' => 0)
        );

        $count = count($items);

        $total = 0;

        foreach($items as $item){
            $track = $this->getService('ScormTrack')->getLastUserTrack(
                $userId,
                $courseId,
                $item->oid,
                $item->module,
                $lessonId
            );
            if($track){
                if (empty($track->scoremax)) {
                    $track->scoremax = 100;
                }
                if(
                    $track->status == HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED
                    || $track->status == HM_Scorm_Track_Data_DataModel::STATUS_PASSED
                ) {
                    $total += 100;
                }elseif($track->score != 0 && $track->scoremax != 0){
                    $total += ($track->score / $track->scoremax) * 100;
                }
            }

        }
        if ($count) {
            if (100 >= $percent = ceil($total/$count)) {
                return $percent;
            }
        }
        return 0;
    }

    /**
     *  Получаем массив результатов занятия пользователей типа 'userId' => V_STATUS для занятия по его ID
     *  выбираются данные с V_STATUS > 0
     *  @param int $lessonId ID занятия
     *  @return array
     */
    public function getMarkedUsersId($lessonId)
    {
        $results             = array();
        $lessonAssignService = $this->getService('LessonAssign');
        $collection          = $lessonAssignService->fetchAll($lessonAssignService->quoteInto(array('V_STATUS > ?', ' AND SHEID = ?'),array(0,intval($lessonId))));

        if ( count($collection) ) {
            $results = $collection->getList('MID','V_STATUS');
        }

        return $results;
    }

    public function getLesson($lessonId)
	{
		#$cache      = Zend_Registry::get('cache');
		#$cache_name = self::CACHE_NAME . '__' . __FUNCTION__;
		#$lifetime   = 60; # сек. - время жизни
		
		#$items   = $cache->load($cache_name);
		
		#$item    = $items[$lessonId];
		#$lesson  = $item['items'];
		#$expired = $item['expired'];
		
		#if((int)$expired < time()){
		#	$lesson = false;
		#}
		
		#if(!$lesson){
			$lesson                      = $this->fetchRow(array('SHEID = ?' => (int) $lessonId));
		#	$items[$lessonId]['items']   = $lesson;
		#	$items[$lessonId]['expired'] = time() + $lifetime;
						
		#	$cache->save($items, $cache_name);			
		#}
		return $lesson;
    }
    public function setLessonFreeMode($moduleId, $typeId, $subjectId, $newMode = HM_Lesson_LessonModel::MODE_FREE)
    {
        if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
            $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
        }

        if ($freeLesson = $this->getOne(
            $this->fetchAll(array(
                "params LIKE '%module_id=" . $moduleId . ";'",
                'CID = ?' => $subjectId,
                'typeID = ?' => $typeId,
                'isfree = ?' => $newMode == HM_Lesson_LessonModel::MODE_FREE ? HM_Lesson_LessonModel::MODE_FREE_BLOCKED : HM_Lesson_LessonModel::MODE_FREE,
            )))) {

                $data = $freeLesson->getValues();
                $data['isfree'] = $newMode;
                $this->update($data);
            }
    }

    /**
     * Function returns count of finished lessons which in statment(vedomost)
     * in percents
     * @param HM_Collection $lessons which should be used in calculation
     * @return int
     */
    public function countPercents(HM_Collection $lessons) {
        if (!count($lessons)) {
            return 0;
        }
        $totalCount = 0;
        $lessonsPercent = 0;
        foreach ($lessons as $lesson) {
            if ($lesson->vedomost) {
                $totalCount++;
            }
            if (HM_Event_EventModel::TYPE_COURSE != $lesson->typeID) {
                $other[] = $lesson->SHEID;
                continue;
            }
            $lessonsPercent += (int) $this->getTotalCoursePercent(
                $lesson->SHEID,
                $this->getService('User')->getCurrentUserId(),
                $lesson->CID
            );
        }

        $lessonsPercent += 100 * $this->countAllDependenceJoinInner(
            'Assign',
            $this->quoteInto(
                array(
                    'self.SHEID IN(?) AND self.vedomost = 1 ',
                    'AND Assign.V_STATUS > -1 AND self.isfree = ? ',
                    'AND Assign.MID = ?'
                ),
                array(
                    $other,
                    HM_Lesson_LessonModel::MODE_PLAN,
                    $this->getService('User')->getCurrentUserId()
                )
            )
        );
        return $totalCount ? intval($lessonsPercent/$totalCount) : 0;
    }

	
	/**
	 * Увеличивает кол-во попыток на прикрепление задания в уроке. Оно же: кол-во раз, которое преподаватель может выставить оценку студенту (type = 5).
	 * @return boolean
	*/
	public function addAttemptToUser($userId, $lessonId){
		
		if(!$userId || !$lessonId){
			return false;
		}		
		$assign = $this->getOne(
			$this->getService('LessonAssign')->fetchAll(
				$this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lessonId, $userId))
			)
		);		
		if(!$assign){
			return false;
		}
		
		$curAttempts = (int)$assign->attempts;
		
		$curAttempts++;	
		
		if($this->getService('LessonAssign')->updateWhere(
			array('attempts' => $curAttempts),
			array('SHEID = ?' => $lessonId, 'MID = ?' => $userId)
		)){
			return true;
		}		
		return false;
	}
	
	
	/**
	 * разпределяем уроки по 2 типам: "Текущий балл" и "зачету/экзамену"
	*/
	/*
	public function getLessonBRSType($subject_id){
		if(!$subject_id) { return false; }
		
		if(empty($this->_lessonTypes)){
			$this->restoreFromCache();
		}
		if(isset($this->_lessonTypes[$subject_id])){
			return $this->_lessonTypes[$subject_id];
		}
		
		$lessons = 	$this->fetchAll(
						$this->quoteInto(
							array('CID = ?', ' AND typeID NOT IN (?)', ' AND isfree = ?'),
							array($subject_id, array_keys(HM_Event_EventModel::getExcludedTypes()), HM_Lesson_LessonModel::MODE_PLAN)
						),
						array('order ASC')						
					);
		if(!$lessons && !count($lessons)){ return false; }
		
		$issetFinalTest = false; //-- храним id урока с итоговым тестом
		foreach($lessons as $lesson){
			if(stripos($lesson->title, 'итоговый') !== false){
				$issetFinalTest = $lesson->SHEID;
				break;
			}			
		}
		
		
		$data = array();			
		foreach($lessons as $lesson){			
			if(HM_Event_EventModel::TYPE_RESOURCE == $lesson->typeID || HM_Event_EventModel::TYPE_COURSE == $lesson->typeID){ // Аннотация и Курсы по БРС
				$data[$lesson->SHEID] = HM_Subject_Mark_MarkModel::BALL_CURRENT;
			} elseif(HM_Event_EventModel::TYPE_TEST == $lesson->typeID) { // тест
				if($issetFinalTest && $issetFinalTest == $lesson->SHEID){ // итоговый тест
					$data[$lesson->SHEID] = HM_Subject_Mark_MarkModel::BALL_EXAM;
				} else {
					$data[$lesson->SHEID] = HM_Subject_Mark_MarkModel::BALL_CURRENT;
				}
			} elseif(HM_Event_EventModel::TYPE_TASK == $lesson->typeID){ // задание
				if($issetFinalTest){ //--есть итоговый тест, то все задания как текущие.
					$data[$lesson->SHEID] = HM_Subject_Mark_MarkModel::BALL_CURRENT;
				} else {
					if(stripos($lesson->title, 'зачет') !== false || stripos($lesson->title, 'экзамен') !== false){
						$data[$lesson->SHEID] = HM_Subject_Mark_MarkModel::BALL_EXAM;
					} else {
						$data[$lesson->SHEID] = HM_Subject_Mark_MarkModel::BALL_CURRENT;
					}
				}
			}			
		}
		$this->_lessonTypes[$subject_id] = $data;
		$this->saveToCache();		
		if(!count($data)) { return false; }
		return $data;
	}
	*/
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'lessonTypes'	=> $this->_lessonTypes,                 
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
            $this->_lessonTypes	= $actions['lessonTypes'];                        
            return true;
        }
        return false;
    }
	
	/**
	 * Получаем список уроков в указанной сессии.
	 * Условия отбора аналогичны отбору вразделе "План занятий" курса.
	*/
	public function getActiveLessonsOnSubjectId($subject_id) {		
		$lessons = $this->fetchAll($this->quoteInto(
			array('CID = ?', ' AND typeID NOT IN (?)', ' AND isfree = ?'),
			array($subject_id, array_keys(HM_Event_EventModel::getExcludedTypes()), HM_Lesson_LessonModel::MODE_PLAN)
		))->getList('SHEID');
		return $lessons;		
	}
	
	/**
	 * Получаем коллекцимю  уроков в указанной сессии.
	 * Условия отбора аналогичны отбору вразделе "План занятий" курса.
	*/
	public function getActiveLessonsOnSubjectIdCollection($subject_id) {
		$lessons = $this->fetchAll($this->quoteInto(
			array('CID = ?', ' AND typeID NOT IN (?)', ' AND isfree = ?'),
			array($subject_id, array_keys(HM_Event_EventModel::getExcludedTypes()), HM_Lesson_LessonModel::MODE_PLAN)
		));
		return $lessons;		
	}

	/**
	 *  преобразует из 100 бальной в 5 бальную щкалу
	 * @ param int
	 * @ return int/boolean
	 */
	function getFiveScaleMark($mark){				
		$mark = intval($mark);
		if($mark > 84){
			return 5;
		} elseif( 84 >= $mark && $mark >= 75 ){
			return 4;
		} elseif( 74 >= $mark && $mark >= 65 ){
			return 3;
		} elseif( 65 > $mark && $mark > 0 ){
			return 2;
		} 
		return false;
	}

	/**
	 * @return bool
	 * Проверяет доступность занятия
	 * Нет ни одного назначения на занятие - доступны все занятия сессии.
	 * Есть хотя бы одно назначение на занятие - доступны только назначенные занятия.
	*/
	public function isAvailable($tutor_id, $lesson_id, $subject_id){
		if(!$tutor_id || !$lesson_id || !$subject_id){ return false; }
		$assigns = $this->getService('LessonAssignTutor')->getAssignSubject($tutor_id, $subject_id);	
		if(!count($assigns)){ return true; }
		$toList = $assigns->getList('LID', 'LID');				
		return (bool) (isset($toList[$lesson_id]));
	}
	
	public function getTaskSumMaxBall($subject_id){
		if(!$subject_id){ return 0; }		
        $balls = $this->fetchAll($this->quoteInto(array('CID = ?', ' AND typeID = ?', ' AND title LIKE ?'), array($subject_id, HM_Event_EventModel::TYPE_TASK, '%задание%')))->getList('SHEID', 'max_ball');                				
		return array_sum($balls);
	}
	

	/**
	 * ограничивает сумму максимально возможных баллов за занятия + журнал-практика.
	 * issetIPZ - если есть ИПЗ, то максимум 20, иначе 40
	*/
	public function normalizeTask($ball, $issetIPZ = false, $ballMax = false)
	{
		if(empty($ballMax)){
			$ballMax = ($issetIPZ === true) ? 20 : 40;
		} 
		if($ball < $ballMax){ return $ball; }
		return $ballMax;
	}
	
	/**
	 * ограничивает сумму максимально возможных баллов за академическую активность в журнале
	*/
	public function normalizeAcadem($ball){
		if($ball < HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY){ return $ball; }
		return HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY;
	}


	/**
	 * ограничение на максимальный "итоговый текущий рейтинг".
	**/
	public function normalizeTotalCurrentRating($ball){
		if($ball < HM_Lesson_LessonModel::MAX_BALL_TOTAL_CURRENT_RATING){ return $ball; }
		return HM_Lesson_LessonModel::MAX_BALL_TOTAL_CURRENT_RATING;
	}

	public function issetJournalPractic($subject_id){
		 return (bool) count($this->fetchAll($this->quoteInto(array('CID = ?', ' AND typeID = ?'), array($subject_id, HM_Event_EventModel::TYPE_JOURNAL_PRACTICE)))->getList('SHEID'));  
	}
	
	public function getTextFiveScaleMark($mark, $exam_type){
		if($exam_type == HM_Subject_SubjectModel::EXAM_TYPE_TEST){
			if(in_array($mark, array(5,4,3)))	{ return 'зачтено'; }
			elseif($mark == 2)					{ return 'не зачтено'; }
			return 'неявка';
		}
		
		switch ($mark) {
			case 5:
				return 'отлично';				
			case 4:
				return 'хорошо';				
			case 3:
				return 'удовлетворительно';				
			case 2:				
				return 'неуд.';
		}
		return 'неявка';
	}
	
	/**
	 * param HM Lesson Model
	 * @return bool
	 * Определяет, относится ли занятие к "Итоговый текущий рейтинг" или нет. Если нет, то это "Рубежный рейтинг".
	 * Для типа "журнал-практика" нужно отдельно делать провекру.
	 * Неверное описание. Должно быть "Рубежный рейтинг"
	*/
	public function isMediumRating($lesson){			
		if($lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE || $lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_LAB){
			return true;
			
		} elseif($lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){
			return true; # Для типа "журнал-практика" нужно отдельно делать провекру. Но балл в занятии - это "Итоговый текущий рейтинг"
			
		} elseif( (stristr($lesson->title, 'Итоговый тест') !== FALSE) || (stristr($lesson->title, 'Итоговый контроль') !== FALSE) ){ 
			return false;
			
		} elseif($lesson->typeID == HM_Event_EventModel::TYPE_TASK && (stristr($lesson->title, 'поощрени') !== FALSE)){
			return true;
			
		} elseif($lesson->typeID == HM_Event_EventModel::TYPE_TASK && (stristr($lesson->title, 'задание') !== FALSE)){
			return true;
			
		} else { 
			return true;
		}					
	}
	
	/**
	 * @return bool 
	 * Прошел ли положительный порог по сдаче "Рубежного рейтинга" в 65%
	 * Для ДОТ сессий накопленный балл нужно показывать всегда
	 * Неверное описание. Должно быть "Итоговый текущий рейтинг"
	*/
	public function isPassTotalRating($max_ball, $current_ball, $is_DOT = false, $is_practice = false)
	{
		if($is_practice && $max_ball > HM_Subject_SubjectModel::MAX_MARK_LANDMARK_DEFAULT){ return true; }		
		if($is_DOT){ return true; }				 
		return (bool)(	floatval(str_replace(',', '.', $current_ball))	>=	floatval(str_replace(',', '.', ($max_ball * HM_Lesson_LessonModel::PASS_TOTAL_RATING_PERCENT)))	);
	}
	
	/**
	 * @return bool 
	 * Прошел ли положительный порог по сдаче "Итогового текущего рейтинга" в 65%
	 * Для ДОТ сессий накопленный балл нужно показывать всегда
	 * Неверное описание. Должно быть "Рубежный рейтинг"
	*/
	public function isPassMediumRating($max_ball, $current_ball, $is_DOT = false){		
		if($is_DOT){ return true; }		
		
		
		# огругляем только для больших величин, т.к. при окгурглении малых баллов возникают проблемы с пороговыми значыениями.
		#if($max_ball > 10 ){
		#	$current_ball = round($current_ball);	
		#}
		
		
		return (bool)(	floatval(str_replace(',', '.', $current_ball))	>=	floatval(str_replace(',', '.', ($max_ball * HM_Lesson_LessonModel::PASS_MEDIUM_RATING_PERCENT)))	);
	}
	

	/**
	 * Максимальный балл для графы "Рубежный рейтинг"
	 * TODO переписать, используя isMediumRating
	*/
	public function getMaxBallTotalRating($subject_id)
	{
		###########################
		$subject = $this->getService('Subject')->getByid($subject_id);
		if($subject){
			$typeModel       = $subject->getTypeModel();
			$maxMarkLandmark = $typeModel->getMaxMarkLandmark();
			if(!empty($maxMarkLandmark)){
				return $maxMarkLandmark;
			}
		}
		
		$collection = $this->getActiveLessonsOnSubjectIdCollection($subject_id);
		if(!$collection){ return false; }
		$max_ball = 0;
		foreach($collection as $item){
			if($item->required != 1){ continue; }

			if($this->isTotalRating($item)){
				$max_ball += $item->max_ball;	
			}		
		}
		if($max_ball > 80){ return 80; } # почему 80? Ведь это экзамен, и должен быть не более 20. Это "Рубежный рейтинг"
		return $max_ball;		
	}
	

	/**
	 * @return bool
	 * Проверяет есть ли в базовом курсе занятия с вхождением фразы «Задание» для формирования журналов при создании сессии.
	*/
	public function issetTaskLessons($subject_id){
		 return (bool) count($this->fetchAll($this->quoteInto(array('CID = ?', ' AND typeID = ?', ' AND title LIKE ?'), array($subject_id, HM_Event_EventModel::TYPE_TASK, '%задание%')))->getList('SHEID')); 
	}

	
	/**
	  * param HM Lesson Model
	  * @return bool
	  * Неверное описание. Должно быть "Итоговый текущий рейтинг"
	  * Это "Итоговый контроль" - занятия, в которых есть "Итоговый контроль" или "Итоговый тест"
	*/
	public function isTotalRating($lesson){
		return !$this->isMediumRating($lesson);
	}
	
	public function correctMediumBall($ball){
		if($ball > HM_Lesson_LessonModel::MODULE_MAX_MEDIUM_BALL){ return HM_Lesson_LessonModel::MODULE_MAX_MEDIUM_BALL; }
		return $ball;
	}

	
	/**
	 * Максимальный балл для графы "Итоговый текущий рейтинг"
	*/
	public function getMaxBallMediumRating($subject_id, $lessons = NULL)
	{
		
		if(isset($this->_maxBallMediumRating[$subject_id])){ return $this->_maxBallMediumRating[$subject_id]; }
	
		$subject = $this->getService('Subject')->getByid($subject_id);
		if($subject){
			$typeModel      = $subject->getTypeModel();
			$maxMarkCurrent = $typeModel->getMaxMarkCurrent();
			if(!empty($maxMarkCurrent)){
				return $maxMarkCurrent;
			}
		}
		
		
		
		if(!$lessons) { $lessons = $this->getActiveLessonsOnSubjectIdCollection($subject_id); }		
		if(!$lessons){ return false; }
		$max_ball = 0;
		foreach($lessons as $item){			
			if($item->required != 1)		{ continue; }
			if($this->isMediumRating($item)){
				$max_ball += $item->max_ball;
				if($item->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE && !empty($item->max_ball_practice_or_lab))	{ $max_ball += $item->max_ball_practice_or_lab; }
				}					
		}
		
		$this->_maxBallMediumRating[$subject_id] = 80;
		if($max_ball > 80){ return 80; }
		
		$this->_maxBallMediumRating[$subject_id] = $max_ball;
		
		return $max_ball;		
	}

	/**
	 * Так уж понадобилось все занятия со словом поощрение выводить в конце всех занятий перед "итоговый контроль".
	 * @param HM Lesson Model collection
	*/
	public function sortLessons($lessons){
		$promotionLessons = array(); # "Поощрение"
		$totalControl 	  = array(); # "итоговый контроль"
		foreach($lessons as $key => $item){
			if(stripos($item->title, 'поощрени') !== false){
				$promotionLessons[$key] = $item;
				unset($lessons[$key]);
			} elseif(stripos($item->title, 'итоговый контроль') !== false){
				$totalControl[$key] = $item;
				unset($lessons[$key]);
			}
		}
		$lessons = $lessons + $promotionLessons;		
		$lessons = $lessons + $totalControl;
	}
	

	/*
	 * сбрасывает попытки прохождения тестов
	 * $param_1 int
	 * $param_2 array of int
	**/
	public function resetTestCount($subject_id, $users){
		
		$lessonMaxBallList = $this->getActiveLessonsOnSubjectIdCollection($subject_id)->getList('SHEID', 'max_ball');
		
		$collection = $this->getService('LessonAssign')->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID IN (?)'), array(array_keys($lessonMaxBallList), $users)));
		if(!count($collection)){ return false; }
		$needResetUsers = array();
		foreach($collection as $i){
			if( $i->V_STATUS  >= ($lessonMaxBallList[$i->SHEID] * HM_Lesson_LessonModel::PASS_LESSON_PERCENT) ){ continue; }
			$needResetUsers[$i->SHEID][$i->MID] = $i->MID;
		}
		
		if(empty($needResetUsers)){ return false; }
		
		$serviseLogUser 	= $this->getService('StatisticTest');
		$serviseTestAttempt = $this->getService('TestAttempt');
		
		foreach($needResetUsers as $lesson_id => $students){
			if(empty($students)){ continue; }
			$criteriaDelete = $this->quoteInto(array('cid = ?', ' AND sheid = ? ', 		' AND mid IN (?)'), array($subject_id, $lesson_id, $students));
			$criteriaUpdate = $this->quoteInto(array('cid = ?', ' AND lesson_id = ? ',  ' AND mid IN (?)'), array($subject_id, $lesson_id, $students));
			
			$serviseLogUser->deleteBy($criteriaDelete);
			$serviseTestAttempt->updateWhere(array('qty' => '0'), $criteriaUpdate);
		}
	}
	
	/**
 	 * Сброс попыток проходления теста у конкретного студента
	*/
	public function resetTestCountOnUser($subject_id, $user_id){
		$lessonMaxBallList 	= $this->getActiveLessonsOnSubjectIdCollection($subject_id)->getList('SHEID', 'max_ball');
		$assigns 			= $this->getService('LessonAssign')->fetchAll($this->quoteInto(array('SHEID IN (?)', ' AND MID = ?'), array(array_keys($lessonMaxBallList), $user_id)));
		
		$serviseLogUser 	= $this->getService('StatisticTest');
		$serviseTestAttempt = $this->getService('TestAttempt');
		
		foreach($assigns as $i){			
			if( $i->V_STATUS  >= ($lessonMaxBallList[$i->SHEID] * HM_Lesson_LessonModel::PASS_LESSON_PERCENT) ){ continue; }
			
			$criteriaDelete = $this->quoteInto(array('cid = ?', ' AND sheid = ? ', 		' AND mid = ? '), array($subject_id, $i->SHEID, $user_id));
			$criteriaUpdate = $this->quoteInto(array('cid = ?', ' AND lesson_id = ? ',  ' AND mid = ? '), array($subject_id, $i->SHEID, $user_id));
			
			$serviseLogUser->deleteBy($criteriaDelete);
			$serviseTestAttempt->updateWhere(array('qty' => '0'), $criteriaUpdate);
		}
		
	}
	
	/**
	 * список групп для фильтра по группам в занятии
	 * @return 
	**/
	public function createFilterGroupList($subject_id, $tutor_id){
		$groups                        = $this->getService('Group')->fetchAll(array('cid = ?' => $subject_id));
		$studygroups                   = $this->getService('StudyGroupCourse')->getCourseGroups($subject_id);		
		$studygroups_custom            = $this->getService('StudyGroupCustom')->getCourseGroups($subject_id);
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
		$availableGroups = $this->getService('Subject')->filterGroupsByAssignStudents($subject_id, $tutor_id, $allGroupsForFiltered); # группы, доступные тьютору с учетом назначения на конкретную группу.
		
        $groupname         = array();

        if (count($studygroups)) {
            $groupname[] =  _('-Группы-');
            foreach ($studygroups as $studygroup) {
				if(!isset($availableGroups[$studygroup->group_id])) { continue; }
                $groupname['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }
		
		if (count($studygroups_custom)) {
            if(!count($groupname)){
				$groupname[] =  _('-Группы-');	
			}			
            foreach ($studygroups_custom as $studygroup) {
				if(!isset($availableGroups[$studygroup->group_id])) { continue; }
                $groupname['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }
		
				
        if (count($groups)) {			
            $groupname[] =  _('-Подгруппы-');
            foreach ($groups as $item) {
                $groupname['s_'.$item->gid] = $item->name;
            }
        }
		return $groupname;
	}

	/**
	 * Итоговое практическое задание (ИПЗ)
	*/
	public function isTotalPractic($lesson){		
		$title = (is_string($lesson)) ? $lesson : $lesson->title;
		if(stristr($title, 'ИПЗ') !== false){ return true; }
		return false;
	}
	
	public function isPassLesson($lesson, $user_ball){
		return (bool)(	floatval(str_replace(',', '.', $user_ball))	>=	floatval(str_replace(',', '.', ($lesson->max_ball * HM_Lesson_LessonModel::PASS_LESSON_PERCENT)))	);
		#if($user_ball >= ($lesson->max_ball * HM_Lesson_LessonModel::PASS_LESSON_PERCENT) ){ return true; }
		#return false;		
	}
	
	/**
	 * Является ли занятие рубежным контролем.
	*/
	public function isBoundaryControl($lesson_name){
		return (stristr($lesson_name, 'Рубежный контроль') !== FALSE) ? true : false;		
	}
	
	/**
	 * Есть ли среди занятий сессии ИПЗ	 
	*/
	public function issetIPZ($subject_id){
		return (bool) count($this->fetchAll($this->quoteInto(array('CID = ?', ' AND title LIKE ?'), array($subject_id, '%ИПЗ%')))->getList('SHEID')); 
	}
	
	
	/**
	 * занятия с типом задание
	*/
	public function getTaskLessons($subject_id){
		$lessons = $this->fetchAll($this->quoteInto(
			array('CID = ?', ' AND typeID = ?', ' AND isfree = ?'),
			array($subject_id, HM_Event_EventModel::TYPE_TASK, HM_Lesson_LessonModel::MODE_PLAN)
		))->getList('SHEID');
		return $lessons;	
	}
	
	/**
	 * Студенты, доступные тьютору, у которых последнее сообщение не решение на проверку и не выставлена оценка.
	*/
	public function getBlockedTaskGroups($subject_id, $tutor_id){
		
		$students_total = $this->getAvailableNoReactionLessonStudents($subject_id, $tutor_id);
		
		$groups = $this->getService('StudyGroupUsers')->getUsersGroups($students_total);
		$data = array();
		foreach($groups as $i){
			$data[$i['group_id']] = $i['name'];
		}
		return $data;		
	}
	
	
	/**
	 * @return array(student_id => lesson_ids)
	*/
	public function getBlockedTaskUsers($subject_id, $tutor_id, $group_id){
		$noReactionLessonStudents = $this->getAvailableNoReactionLessonStudents($subject_id, $tutor_id);
		
		
		$students_subject	= $this->getService('Student')->getUsersIds($subject_id); # студенты, обучающиеся в данный момент на указанной сессии
		$students_available	= $this->getService('Subject')->getAvailableStudents($tutor_id, $subject_id); # доступные студенты тьютору
		
		$students_total  	= $students_subject;
		
		# false - значит доступны все студенты
		if($students_available !== false){			
			$students_total = array_intersect($students_total, $students_available);
		}
		
		$students_group	= $this->getService('StudyGroup')->getUsers($group_id); # студенты группы
		$students_total = array_intersect($students_group, $students_total);
		
		# Получить студенто-занятия, в которых нужно выставить оценку.
		$noReactionLessonStudents = $this->getService('Interview')->getWithoutReactionLessons($subject_id, $students_total);
		
		return $noReactionLessonStudents;		
	}
	
	
	
	/**
	 * Доступные тьютору студенты, которым нужно выставить неуд, т.к. никакой активности там нет (нет решения на проверку и нет оценки от тьютора)
	**/
	public function getAvailableNoReactionLessonStudents($subject_id, $tutor_id){
		$students_subject	= $this->getService('Student')->getUsersIds($subject_id); # студенты, обучающиеся в данный момент на указанной сессии
		$students_available	= $this->getService('Subject')->getAvailableStudents($tutor_id, $subject_id); # доступные студенты тьютору
		
		$students_total  	= $students_subject;
		
		# false - значит доступны все студенты
		if($students_available !== false){			
			$students_total = array_intersect($students_total, $students_available);
		}
		
		# Получить студенто-занятия, в которых нужно выставить оценку.
		$noReactionLessonStudents = $this->getService('Interview')->getWithoutReactionLessons($subject_id, $students_total);
		
		if(empty($noReactionLessonStudents)){ return false; }
		
		$students_total = array_intersect($students_total, array_keys($noReactionLessonStudents));
		
		return $students_total;		
	}
	
	/**
	 * Перераспределяет итоговый балл на "Итоговый текущий рейтинг" и "Рубежный рейтинг" по правилам ДО, как в Экспорте результатов обучения
	 *
	*/
	public function reDivideDotBalls($marks_current, $marks_landmark)
	{
		$marks_total = array();
		
		foreach($marks_current as $user_id => $ball){			
			if(!isset($marks_landmark[$user_id]) || $marks_landmark[$user_id] <= 0)	{ $landmark = 0; }
			else																	{ $landmark = $marks_landmark[$user_id]; }
			
			if($ball <= 0){ $ball = 0; }			
			$marks_total[$user_id] = $ball + $landmark;
		}
		
		foreach($marks_landmark as $user_id => $ball){			
			$mark_current  = $marks_current[$user_id]<=0 ? 0 : $marks_current[$user_id];
			$mark_landmark = $ball<=0                    ? 0 : $ball;
			
			$marks_total[$user_id] = $mark_current + $mark_landmark;
		}
		
		$data = array(
			'marks_current'  => array(),
			'marks_landmark' => array(),
		);
		
		foreach($marks_total as $user_id => $total_ball){
			$ball_exam 		= $this->getService('Subject')->getExamBall($total_ball);
			$ball_current 	= $this->getService('Subject')->getCurrentBall($total_ball, $ball_exam);						
			$ball_current 	= round($ball_current); 
			$ball_exam 		= round($ball_exam);

			$data['marks_current'][$user_id] = $ball_current; # Итоговый текущий рейтинг
			$data['marks_landmark'][$user_id] = $ball_exam;   # Рубежный рейтинг
		}
		return  $data;
	}
	
	
	
	
	/**
	 * Увеличивает кол-во попыток на прикрепление задания в уроке. Оно же: кол-во раз, которое преподаватель может выставить оценку студенту (type = 5).
	 * справедливо для несколькоих студентов 
	 * С учетом проверки - нужно ли добавить попытку или нет
	 * @return users | false
	*/
	public function addAttemptToUsers($user_ids, $lesson_id){
		
		$user_ids = (array)$user_ids;
		
		if(empty($user_ids) || !$lesson_id){ return false; }
		
		
		$assigns =	$this->getService('LessonAssign')->fetchAll(
						$this->quoteInto(array('SHEID = ?', ' AND MID IN(?) '), array($lesson_id, $user_ids))
					);
		
		
		if(!$assigns){ return false; }
		
		
		
		# кол-во выставленных оценок студенту. Не имеет значения, кто выставил
		$res_count_marks =	$this->getService('Interview')->fetchAll(
								$this->quoteInto(array('lesson_id = ?', ' AND type = ? ' , ' AND to_whom IN(?) '), array($lesson_id, HM_Interview_InterviewModel::MESSAGE_TYPE_BALL,  $user_ids))
							);
		
		
		$count_marks = array();
		foreach($res_count_marks as $i){ $count_marks[$i->to_whom] = (int)$count_marks[$i->to_whom] + 1; }
		
		
		
		$new_attempts = array();
		foreach($assigns as $i){ $new_attempts[$i->MID] = (int)$count_marks[$i->MID]; }
		
		if(empty($new_attempts)){ return false; }
		
		
		foreach($new_attempts as $user_id => $attempts){
			$this->getService('LessonAssign')->updateWhere(array('attempts' => $attempts), array('SHEID = ?' => $lesson_id, 'MID = ?' => $user_id));
		}
		
		return array_keys($new_attempts);
	}
	
	
	public function normalizeTotalBall($ball)
	{
		if($ball > 100){ return 100; }
		return $ball;
	}
	
	public function getBySubject($subjectId)
	{
		#$cache      = Zend_Registry::get('cache');
		#$cache_name = self::CACHE_NAME . '__' . __FUNCTION__;
		#$lifetime   = 60; # сек. - время жизни
		
		#$items    = $cache->load($cache_name);
		
		#$item     = $items[$subjectId];
		#$lessons  = $item['items'];
		#$expired  = $item['expired'];
		
		#if((int)$expired < time()){
		#	$lessons = false;
		#}
		
		#if(!$lessons){
			$lessons = $this->fetchAll(array(
											'CID = ?' 			=> $subjectId,
											'vedomost = ?' 		=> 1,
											'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
											'isfree = ?'	 	=> HM_Lesson_LessonModel::MODE_PLAN,
										));
		#	$items[$subjectId]['items']   = $lessons;
		#	$items[$subjectId]['expired'] = time() + $lifetime;
						
		#	$cache->save($items, $cache_name);			
		#}
		return $lessons;
	}
	
	public function getLandmarkCount($subjectId)
	{
		$lessons  = $this->getBySubject($subjectId);
		$count    = 0;
		foreach($lessons as $lesson){
			if( $lesson->isBoundaryControl() ){ 
				$count++;
			}
		}
		return $count;
	}
	
	public function getSubjectId($lessonId)
	{
		$lesson = $this->getLesson($lessonId);
		return (int)$lesson->CID;
	}
	
}
