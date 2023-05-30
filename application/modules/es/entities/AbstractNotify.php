<?php
/**
 * Description of AbstractNotify
 *
 * @author slava
 */
abstract class Es_Entity_AbstractNotify {
    
    /**
     *
     * @var int 
     */
    protected $userId = null;
    
    /**
     *
     * @var boolean 
     */
    protected $isActive = null;
    
    /**
     *
     * @var Es_Entity_AbstractNotifyType
     */
    protected $notifyType = null;
    
    /**
     *
     * @var Es_Entity_AbstractEventType
     */
    protected $eventType = null;
    
    /**
     * 
     * @return int|null
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * 
     * @param int $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }

    /**
     * 
     * @return Es_Entity_AbstractNotifyType
     */
    public function getNotifyType() {
        return $this->notifyType;
    }

    /**
     * 
     * @param \Es_Entity_AbstractNotifyType $notifyType
     */
    public function setNotifyType(\Es_Entity_AbstractNotifyType $notifyType) {
        $this->notifyType = $notifyType;
    }

    /**
     * 
     * @return Es_Entity_AbstractEventType
     */
    public function getEventType() {
        return $this->eventType;
    }

    /**
     * 
     * @param \Es_Entity_AbstractEventType $eventType
     */
    public function setEventType(\Es_Entity_AbstractEventType $eventType) {
        $this->eventType = $eventType;
    }
    
    public function isActive() {
        return $this->isActive;
    }

    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }
    
}

?>
