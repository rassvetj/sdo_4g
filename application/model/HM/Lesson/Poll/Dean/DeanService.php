<?php
class HM_Lesson_Poll_Dean_DeanService extends HM_Lesson_LessonService
{

    /**
     * don't use outside of service layer !!!important!!!
     * @param  $lessonId
     * @param  $studentId
     * @return bool
     */
    public function assignStudent($lessonId, $studentId)
    {
        $return = parent::assignStudent($lessonId, $studentId);
        if ($return) {
            $lesson = $this->getOne($this->find($lessonId));
            if ($lesson) {
                $this->getService('PollFeedback')->assign($studentId, $lesson->CID, $lessonId);
            }
        }
        return $return;
    }

    protected function _sendAssignStudentsMessage($lesson, $students, $templateId, $slaves = null)
    {
        if (!count($students)) return false;

        $messenger = $this->getService('Messenger');
        $messenger->setTemplate($templateId);

        $messenger->assign(
            array(
                'subject_id' => $lesson->CID,
                'url2' => Zend_Registry::get('view')->serverUrl(
                    Zend_Registry::get('view')->url(array(
                        'module' => 'lesson',
                        'controller' => 'execute',
                        'action' => 'index',
                        'lesson_id' => $lesson->SHEID
                    ), null, true)
                ),
                'title' => $lesson->title,
                'begin' => $lesson->getBeginDatetime($lesson->created),
                'end' => $lesson->getEndDatetime($lesson->created),
                'slaves' => ''
            )
        );

        if ($lesson->isTimeFree()) {
            $messenger->assignValue('begin', _('неограничено'));
            $messenger->assignValue('end', _('неограничено'));
        }

        foreach($students as $studentId) {
            if ( $studentId === null ) {continue;}
            $messenger->assignValue('slaves', '');
            if ((null !== $slaves) && ($templateId == HM_Messenger::TEMPLATE_POLL_LEADERS)) {
                if (isset($slaves[$studentId])) {
                    $collection = $this->getService('User')->fetchAll(
                        $this->quoteInto('MID IN (?)', $slaves[$studentId])
                    );

                    $studentSlaves = array();
                    if (count($collection)) {
                        foreach($collection as $user) {
                            $studentSlaves[] = $user->getName();
                        }
                    }

                    if (count($studentSlaves)) {
                        $messenger->assignValue('slaves', join(', ', $studentSlaves));
                    }
                }
            }
            $messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentId);
        }
        return true;
    }

    protected function _isExecutableForDefault($lesson)
    {

        $registered = null;
        if (in_array($lesson->typeID, HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT)
            && !$this->getService('Subject')->isGraduated($lesson->CID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не являетесь прошедшим обучения на курсе'));
        }

        if (!$this->isUserAssigned($lesson->SHEID, $this->getService('User')->getCurrentUserId())) {
            throw new HM_Exception(_('Вы не назначены на занятие'));
        }

        if ($lesson->timetype == HM_Lesson_LessonModel::TIMETYPE_RELATIVE) { // Относительное занятие
            $assign = $this->getOne(
                $this->getService('LessonAssign')->fetchAll(
                    $this->quoteInto(array('SHEID = ?', ' AND MID = ?'), array($lesson->SHEID, $this->getService('User')->getCurrentUserId()))
                )
            );
            if ($assign) {
                $registered = $assign->created;
            }
        }

        // Проверка дат
        if (!$lesson->isExecutable($registered)) {
            throw new HM_Exception(_('Занятие назначено на другое время'));
        }

        return true;
    }

    protected function _isExecutableForRole($lesson)
    {

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)) {
            $this->_isExecutableForDean($lesson);
        } else {
            $this->_isExecutableForDefault($lesson);
        }

/*        switch($this->getService('User')->getCurrentUserRole()) {
            case HM_Role_RoleModelAbstract::ROLE_DEAN:
                $this->_isExecutableForDean($lesson);
                break;
            default:
                $this->_isExecutableForDefault($lesson);
                break;
        }*/
    }

    /*
    public function assignStudents($lessonId, $students, $unassign = true)
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        if ($lesson) {
            switch($lesson->typeID) {
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                        $students = $this->getService('Subject')->getAssignedTeachers($lesson->CID)->getList('MID', 'MID');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                        $collection = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', $students));
                        $students = $leaders = array();
                        if (count($collection)) {
                            foreach($collection as $user) {
                                if ($user->head_mid > 0) {
                                    $students[$user->head_mid] = $user->head_mid;
                                    $leaders[$user->head_mid][$user->MID] = $user->MID;
                                }
                            }

                            if (count($leaders)) {
                                foreach($leaders as $leaderId => $slaves) {
                                    $this->getService('LessonDeanPollAssign')->assignStudents($lessonId, $slaves, $leaderId);
                                }
                            }
                        }
                    break;
            }

            // Чтобы обновился created у опросов руководителю и преподавателям
            if (in_array($lesson->typeID, array(HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER, HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER))) {
                if (count($students)) {
                    $this->getService('LessonAssign')->deleteBy(
                        $this->quoteInto(
                            array('SHEID = ?', ' AND MID IN (?)'),
                            array($lessonId, $students)
                        )
                    );
                }
            }

            $return = parent::assignStudents($lessonId, $students, $unassign);

            if ($return) {
                if (count($students)) {
                    $messenger = $this->getService('Messenger');
                    switch($lesson->typeID) {
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                            $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_TEACHERS);
                            break;
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                            $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_LEADERS);
                            break;
                        default:
                            $messenger->setTemplate(HM_Messenger::TEMPLATE_POLL_STUDENTS);

                    }

                    $messenger->assign(
                        array(
                            'subject_id' => $lesson->CID,
                            'url2' => Zend_Registry::get('view')->serverUrl(
                                Zend_Registry::get('view')->url(array(
                                    'module' => 'lesson',
                                    'controller' => 'execute',
                                    'action' => 'index',
                                    'lesson_id' => $lessonId
                                ))
                            )
                        )
                    );
                    
                    foreach($students as $studentId) {
                        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $studentId);
                    }
                }
            }
            
            return $return;

        }
        return false;
    }
    */

    public function unassignStudent($lessonId, $studentId)
    {
        $this->getService('LessonDeanPollAssign')->deleteBy(
            $this->quoteInto(
                array('lesson_id = ?', ' AND head_mid = ?'),
                array($lessonId, $studentId)
            )
        );

        $lesson = $this->getOne($this->find($lessonId));
        if ($lesson) {
            $this->getService('PollFeedback')->cancel($studentId, $lesson->CID, $lessonId);
        }

        return parent::unassignStudent($lessonId, $studentId);
    }

    private function _prepareWhere($where = null)
    {
        if (null === $where) {
            $where = '';
        }

        if (strlen($where)) {
            $where .= ' AND ';
        }

        $where .= $this->quoteInto('typeID IN (?)', array_keys(HM_Event_EventModel::getDeanPollTypes()));
        return $where;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return parent::fetchAll($this->_prepareWhere($where), $order, $count, $offset);
    }

    public function countAll($where = null)
    {
        return parent::countAll($this->_prepareWhere($where));
    }

    public function fetchAllDependence($dependence = null, $where = null, $order = null, $count = null, $offset = null)
    {
        return parent::fetchAllDependence($dependence, $this->_prepareWhere($where), $order, $count, $offset);
    }
    

    
}