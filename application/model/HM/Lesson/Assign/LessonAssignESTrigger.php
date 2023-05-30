<?php

/**
 * Description of LessonAssignESTrigger
 *
 * @author slava
 */
class HM_Lesson_Assign_LessonAssignESTrigger extends HM_Service_Primitive implements Es_Entity_Trigger {
    
    protected $studentIds = array();
    
    public function getStudentIds() {
        return $this->studentIds;
    }

    public function setStudentIds(array $studentIds) {
        if (!is_array($studentIds)) {
            throw new Es_Exception_InvalidArgument('Invalid students list param');
        }
        $this->studentIds = $studentIds;
    }
    
    public function createEvent(\HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, array(
            'title', 'descript'
        ), $this);
        $user = $this->getService('User')->getCurrentUser();
        $event->setParam('author', $user->getName());
        $event->setParam('author_id', $user->getPrimaryKey());

        $userAvatar = '/'.ltrim($user->getPhoto(),'/');
        $event->setParam('author_avatar', $userAvatar);
        return $event;
    }

    public function getRelatedUserList($event) {
        $ids = array();
        array_walk($this->getStudentIds(), function($val, $key) use(&$ids) {
            if ($val !== null) {
                $ids[] = $val;
            }
        });
        return $ids;
    }

    public function triggerPushCallback() {
        return function($ev) {
            $params = $ev->getParameters();
            /*@var $service HM_Lesson_Assign_LessonAssignESTrigger */
            $service = $ev->getSubject();
            $service->setStudentIds($params['students']);
            $task = $params['lesson'];
            $event = $service->createEvent($task);
            $subject = $service->getService('Subject')->find((int)$task->CID)->current();
            $event->setParam('course_name', $subject->name);
            $event->setParam('course_id', $subject->getPrimaryKey());
            $event->setParam('subjectId', $subject->getPrimaryKey());
            $event->setParam('lesson_id', $task->SHEID);
            $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ATTACH_LESSON);
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$subject->getPrimaryKey()
            );
            $eventGroup->setData(json_encode(array(
                'course_name' => $subject->name,
                'course_id' => $subject->getPrimaryKey(),
            )));
            $event->setGroup($eventGroup);
            $service->getService('EventServerDispatcher')->trigger(
                    Es_Service_Dispatcher::EVENT_PUSH,
                    $service,
                    array('event' => $event)
            );
        };
    }
    
}

?>
