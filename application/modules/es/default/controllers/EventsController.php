<?php
/**
 * Description of EventsController
 *
 * @author slava
 */
class EventsController extends Es_Controller_ESRestController {

    public function listAction() {
        /*@var $userService HM_User_UserService */
        $userService = $this->getService('User');
        $userRole = $userService->getCurrentUserRole();
        //        if ($userRole)
    }

    public function getAction() {
        /** @var Zend_Acl $aclService */
        $aclService = $this->getService('Acl');

        /** @var HM_User_UserModel $user */
        $user = $this->getService('User')->getCurrentUser();
        $types = $this->getRequest()->getParam('types');
        /* @var $filter Es_Entity_Filter */
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$user->MID);
        $filter->setTypes($types);
        $filter->setIsGroupResultRequire((bool)$this->getRequest()->getParam('group', false));
        
        /* limit result */
        $limit = $this->getRequest()->getParam('limit', null);
        if ($limit !== null) {
            $filter->setLimit((int)$limit);
        }
        
        /* showAll switcher */
        $showAll = $this->getRequest()->getParam('showAll', null);
        if ($showAll !== null) {
            $filter->setOnlyNotShowed(!(bool)(int)$showAll);
        }
        
        /* fromTime limit */
        $fromTime = $this->getRequest()->getParam('fromTime', null);
        if ($fromTime !== null) {
            $filter->setFromTime((float)$fromTime);
        }
        
        /* toTime limit */
        $toTime = $this->getRequest()->getParam('toTime', null);
        if ($toTime !== null) {
            $filter->setToTime((float)$toTime);
        }
        /* singleSubject */
        $singleSubject = $this->getRequest()->getParam('singleSubject', null);
        if ($singleSubject !== null) {
            $filter->setSingleSubject((int)$singleSubject);
        }

        $userRole = $user->role;
        
		if (
			$aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TEACHER)
			||
			$aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_TUTOR)	
		) {

            // для преподавателя
            $filter->setExcludeEventTypes(array(
                'courseAddMaterial',        // Добавление материала в курс
                'courseAttachLesson',       // Назначение занятия студенту
                'courseScoreTriggered',     // Выставление оценки за курс
                'commentAdd',               // Добавление комментария к чему-либо на уровне портала
                'commentInternalAdd',       // Добавление комментария к чему-либо на уровне курса
                'courseTaskScoreTriggered', // Выставление оценки за занятие
				'motivationMessage'         // мотивированное заключение. Запись юолжна быть в БД
				#'courseAddMessage'          // написал сообщение в сессии. Запись юолжна быть в БД !
            ));

        } elseif ($aclService->inheritsRole($userRole, HM_Role_RoleModelAbstract::ROLE_STUDENT)) {
            // для студента
            $filter->setExcludeEventTypes(array(
                'courseTaskAction',          // Выполнение задания студентом
				
            ));
        } else {

            // для всех остальных вообще всё сносим
            $filter->setExcludeEventTypes(array(
                'courseAddMaterial',        // Добавление материала в курс
                'courseAttachLesson',       // Назначение занятия студенту
                'courseScoreTriggered',     // Выставление оценки за курс
                'commentAdd',               // Добавление комментария к чему-либо на уровне портала
                'commentInternalAdd',       // Добавление комментария к чему-либо на уровне курса
                'courseTaskScoreTriggered', // Выставление оценки за занятие
                'courseTaskAction',         // Выполнение задания студентом
                'motivationMessage'         // мотивированное заключение
            ));
        }

        /*@var $event sfEvent */
        $event = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_PULL,
            $this,
            array('filter' => $filter)
        );
        $eventCollection = $event->getReturnValue();
        if ($filter->getIsGroupResultRequire()) {
            $result = array();
            $groupTypes = array();
            $data = array();
            /*@var $eventItem Es_Entity_AbstractEvent */
            foreach ($eventCollection as $eventItem) {
                $group = $eventItem->getGroup();
                if (!array_key_exists($group->getName(), $data)) {
                    $data[$group->getName()] = array(
                        'group_description' => $group->getData(),
                        'group_type' => $group->getType(),
                        'events' => array()
                    );
                }
                $groupType = $eventItem->getGroupType();
                if (!in_array($groupType->getId(), $groupTypes)) {
                    $groupTypes[] = $groupType->getId();
                }
                $arrayDecorator = new Es_Service_Decorator_ArrayDecorator($eventItem);
                $data[$group->getName()]['events'][] = $arrayDecorator->out();
            }
            $result['data'] = $data;
            $groupTypes = array_unique($groupTypes);
            $filter->setForceStats((bool)(int)$this->getRequest()->getParam('forceStats', false));
            if (
                    (!$filter->getForceStats() && (sizeof($groupTypes) > 0)) ||
                    ($filter->getForceStats())
            ) {
                /* go for stats */
                $filter->setGroupTypeId($groupTypes);
                $statsEvent = $this->getService('EventServerDispatcher')->trigger(
                    Es_Service_Dispatcher::EVENT_PULL_STATS,
                    $this,
                    array('filter' => $filter)    
                );
                $groupTypesWithStats = $statsEvent->getReturnValue();
                if ($groupTypesWithStats->count() > 0) {
                    $result['stats'] = array();
                    foreach ($groupTypesWithStats as $groupType) {
                        $result['stats'][] = array(
                            'group_type' => $groupType->getName(),
                            'showed_event' => $groupType->getStat()->getShowed(),
                            'not_showed_event' => $groupType->getStat()->getNotShowed()
                        );
                    }
                }
            }
            return $this->_helper->json(
                    $result
            ); 
        } else {
            $arrayDecorator = new Es_Service_Decorator_CollectionArrayDecorator($eventCollection);
            return $this->_helper->json($arrayDecorator->out());        
        }
    }

    public function markasviewedAction() {
        $user = $this->getService('User')->getCurrentUser();
        $eventId = $this->getRequest()->getParam('eventId');
        if (is_array($eventId)) {
            array_walk($eventId, function ($val, $key) use(&$eventId) {
                $eventId[$key] = intval($val);
            });
        }
        /*@var $filter Es_Entity_AbstractFilter */
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setEventId($eventId);
        $filter->setUserId((int)$user->MID);
        $event = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $filter)
        );
        $result = $event->getReturnValue();
        $response = array('success' => $result);
        return $this->_helper->json($response);
    }

    public function typelistAction() {
        $event = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_GET_TYPES_LIST,
            $this,
            array()
        );
        /*@var $result Es_Entity_AbstractEventTypeList */
        $result = $event->getReturnValue();
        $response = array();
        if ($result->count() > 0) {
            foreach ($result as $index => $eventType) {
                $response[] = array(
                    'id' => $eventType->getId(),
                    'name' => $eventType->getName()
                );
            }
        }
        return $this->_helper->json($response);
    }

    public function postAction() {
        return $this->_helper->json($this->getRequest()->getPost());
    }

    public function putAction() {
        $params = $this->_helper()->params();
        return $this->_helper->json($params);
    }

}

?>
