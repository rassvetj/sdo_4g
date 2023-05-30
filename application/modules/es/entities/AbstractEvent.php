<?php
/**
 * Description of AbstractEvent
 *
 * @author slava
 */
abstract class Es_Entity_AbstractEvent extends HM_Collection_Primitive implements Es_Service_Decorator_DecoratorBehavior {
    
    const EVENT_TYPE_FORUM_ADD_MESSAGE = 1;
    const EVENT_TYPE_BLOG_ADD_MESSAGE = 2;
    const EVENT_TYPE_WIKI_ADD_PAGE = 3;
    const EVENT_TYPE_WIKI_MODIFY_PAGE = 4;
    
    const EVENT_TYPE_FORUM_INTERNAL_ADD_MESSAGE = 5;
    const EVENT_TYPE_BLOG_INTERNAL_ADD_MESSAGE = 6;
    const EVENT_TYPE_WIKI_INTERNAL_ADD_PAGE = 7;
    const EVENT_TYPE_WIKI_INTERNAL_MODIFY_PAGE = 8;

    const EVENT_TYPE_COURSE_ADD_MATERIAL = 9;
    const EVENT_TYPE_COURSE_ATTACH_LESSON = 10;
    const EVENT_TYPE_COURSE_SCORE_TRIGGERED = 11;
    const EVENT_TYPE_COURSE_TASK_ACTION = 12;

    const EVENT_TYPE_COMMENT_ADD = 13;
    const EVENT_TYPE_COMMENT_INTERNAL_ADD = 14;

    const EVENT_TYPE_LESSON_SCORE_TRIGGERED = 15;

    const EVENT_TYPE_PERSONALL_MESSAGE_SEND = 16;	
	
    const EVENT_TYPE_MOTIVATION_MESSAGE = 1001; //-- id типа уведомления из БД должен совпадать с этим значениеmм.
    const EVENT_TYPE_COURSE_ADD_MESSAGE = 1002; //-- id типа уведомления из БД должен совпадать с этим значениеmм.
	

    /**
     *
     * @var Es_Entity_Trigger
     */
    private $userListGetter = null;
    
    /**
     *
     * @var int
     */
    private $eventType = null;
    
    /**
     *
     * @var string
     */
    private $eventTypeStr = null;
    
    /**
     * Event identity
     * @var int 
     */
    private $id = null;
    
    /**
     *
     * @var int
     */
    private $createTime = null;
    
    /**
     * 
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * 
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     *
     * @var array
     */
    private $relatedUserList = null;
    
    public function setParam($name, $value) {
        return $this->add($value, $name);
    }
    
    public function removeParam($name) {
        return $this->offsetUnset($name);
    }
    
    public function getParam($name) {
        return $this->offsetGet($name);
    }
    
    public function getParams() {
        return $this->_raw;
    }
    
    public function subjectId($subjectId = null) {
        if (null !== $subjectId) {
            $this->setParam('subjectId', $subjectId);
            return;
        }
        return $this->getParam('subjectId');
    }
    
    /**
     * 
     * @return Es_Entity_Trigger
     */
    public function getUserListGetter() {
        return $this->userListGetter;
    }

    /**
     * 
     * @param Es_Entity_Trigger $userListGetter
     */
    public function setUserListGetter(Es_Entity_Trigger $userListGetter) {
        $this->userListGetter = $userListGetter;
    }
    
    /**
     * Return related users list which attached to this event
     * @return array
     */
    public function getRelatedUserList() {
        if (null === $this->relatedUserList) {
            $this->relatedUserList = $this->getUserListGetter()->getRelatedUserList($this->subjectId());
        }
        return $this->relatedUserList;
    }
    
    public function setParams(array $params) {
        if (sizeof($params) > 0) {
            $this->_count = 0;
            foreach ($params as $key => $value) {
                if (!is_string($key)) {
                    throw new Es_Exception_InvalidArgument('Event parameters array is invalid, all keys mut be a string');
                }
                $this->setParam($key, $value);
            }
            return;
        }
        $this->_raw = $params;
        return;
    }
    
    /**
     * 
     * @return int
     */
    public function getEventType() {
        return $this->eventType;
    }

    /**
     * 
     * @param int $eventType
     */
    public function setEventType($eventType) {
        $this->eventType = $eventType;
    }
    
    /**
     * 
     * @return string
     */
    public function getEventTypeStr() {
        return $this->eventTypeStr;
    }

    /**
     * 
     * @param string $eventTypeStr
     */
    public function setEventTypeStr($eventTypeStr) {
        $this->eventTypeStr = $eventTypeStr;
    }
    
    /**
     * 
     * @return int
     */
    public function getCreateTime() {
        return $this->createTime;
    }

    /**
     * 
     * @param int $createTime
     */
    public function setCreateTime($createTime) {
        $this->createTime = $createTime;
    }
    
    public function out() {
        return $this;
    }
    
}

?>
