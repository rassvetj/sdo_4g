<?php
class StudyGroups_CoursesController extends HM_Controller_Action
{

    public function assignCourseAction()
    {
        $ids = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
        }
        $courseIds = $this->_getParam('courseId', 0);

        if (!count($ids)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ));
            $this->_redirectToIndex();
        }

        if ($courseIds === 0) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите один или несколько курсов')
            ));
           $this->_redirectToIndex();
        }

        foreach ($courseIds as $courseId) {
            /* Проверяем или существует курс */
            $course = $this->getService('Subject')->getById($courseId);
            if (!$course) {
                if ($courseId != 0) {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Курс не найден id=['.$courseId.']')
                    ));
                }
                continue;
            }

            /* Перебираем группы */
            foreach($ids as $id) {
                $group = $this->getService('StudyGroup')->getById($id);
                if ($group) {
                    /* Записываем группу на курс */
                    if ($this->getService('StudyGroupCourse')->addCourseOnGroup($course->subid, $group->group_id)) {
                        $this->_flashMessenger->addMessage(_('Курс: "'.$course->name.'" успешно назначен группе: "'.$group->name.'"'));
                    } else {
                        $this->_flashMessenger->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                            'message' => _('Курс: "'.$course->name.'" не назначен группе: "'.$group->name.'"')
                        ));
                    }
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Группа не найдена id=['.$id.']')
                    ));
                }
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignCourseAction()
    {
        $ids = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
        }
        $courseIds = $this->_getParam('courseId', 0);

        if (!count($ids)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ));
            $this->_redirectToIndex();
        }

        if ($courseIds === 0) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите один или несколько курсов')
            ));
            $this->_redirectToIndex();
        }

        foreach ($courseIds as $courseId) {
            /* Проверяем или существует курс */
            $course = $this->getService('Subject')->getById($courseId);
            if (!$course) {
                if ($courseId != 0) {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Курс не найден id=['.$courseId.']')
                    ));
                }
                continue;
            }

            /* Перебираем группы */
            foreach($ids as $id) {
                $group = $this->getService('StudyGroup')->getById($id);
                if ($group) {
                    /* Отписываем группу с курса */
                    if ($this->getService('StudyGroupCourse')->removeGroupFromCourse($group->group_id, $course->subid)) {
                        $this->_flashMessenger->addMessage(_('Курс: "'.$course->name.'" успешно отменен группе: "'.$group->name.'"'));
                    } else {
                        $this->_flashMessenger->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                            'message' => _('Ошибка отмены назначенного курса: "'.$course->name.'", не был назначен группе: "'.$group->name.'"')
                        ));
                    }
                } else {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Группа не найдена id=['.$id.']')
                    ));
                }
            }
        }
        $this->_redirectToIndex();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoRoute(array(
            'module' => 'study-groups',
            'controller' => 'list',
            'action' => 'index'
        ), null, true);
    }
}