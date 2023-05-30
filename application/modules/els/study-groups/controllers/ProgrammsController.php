<?php
class StudyGroups_ProgrammsController extends HM_Controller_Action
{

    public function assignProgrammAction()
    {
        $ids = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
        }
        $programmIds = $this->_getParam('programmId', 0);

        if (!count($ids)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ));
            $this->_redirectToIndex();
        }

        if ($programmIds === 0) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите один или несколько курсов')
            ));
           $this->_redirectToIndex();
        }

        foreach ($programmIds as $programmId) {
            /* Проверяем или существует курс */
            $programm = $this->getService('Programm')->getById($programmId);
            if (!$programm) {
                if ($programmId != 0) {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Курс не найден id=['.$programmId.']')
                    ));
                }
                continue;
            }

            /* Перебираем группы */
            foreach($ids as $id) {
                $group = $this->getService('StudyGroup')->getById($id);
                if ($group) {
                    /* Записываем группу на курс */
                    if ($this->getService('StudyGroupProgramm')->addProgrammOnGroup($programm->programm_id, $group->group_id)) {
                        $this->_flashMessenger->addMessage(_('Программа обучения: "'.$programm->name.'" успешно назначена группе: "'.$group->name.'"'));
                    } else {
                        $this->_flashMessenger->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                            'message' => _('Программа обучения: "'.$programm->name.'" не назначена группе: "'.$group->name.'"')
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

    public function unassignProgrammAction()
    {
        $ids = array();
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
        }
        $programmIds = $this->_getParam('programmId', 0);

        if (!count($ids)) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите одну или несколько учебных групп')
            ));
            $this->_redirectToIndex();
        }

        if ($programmIds === 0) {
            $this->_flashMessenger->addMessage(array(
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Укажите один или несколько курсов')
            ));
            $this->_redirectToIndex();
        }

        foreach ($programmIds as $programmId) {
            /* Проверяем или существует курс */
            $programm = $this->getService('Programm')->getById($programmId);
            if (!$programm) {
                if ($programmId != 0) {
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _('Курс не найден id=['.$programmId.']')
                    ));
                }
                continue;
            }

            /* Перебираем группы */
            foreach($ids as $id) {
                $group = $this->getService('StudyGroup')->getById($id);
                if ($group) {
                    /* Отписываем группу с курса */
                    if ($this->getService('StudyGroupProgramm')->removeGroupFromProgramm($group->group_id, $programm->programm_id)) {
                        $this->_flashMessenger->addMessage(_('Программа обучения: "'.$programm->name.'" успешно отменена группе: "'.$group->name.'"'));
                    } else {
                        $this->_flashMessenger->addMessage(array(
                            'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                            'message' => _('Программа обучения: "'.$programm->name.'" не отменена группе: "'.$group->name.'"')
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