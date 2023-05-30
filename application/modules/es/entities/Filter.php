<?php
/**
 * Description of Filter
 *
 * @author slava
 */
class Es_Entity_Filter extends Es_Entity_AbstractFilter implements Es_Entity_HasGroupProperty {

    protected $isGroupResultRequire = false;
    protected $limit = null; 
    protected $fromTime = null;
    protected $toTime = null;
    protected $groupId = null;
    protected $groupTypeId = null;
    protected $group = null;
    protected $eventTriggerId = null;
    protected $onlyNotShowed = true;
    protected $forceStats = false;
    protected $singleSubject = null;
    protected $excludeEventTypes = array();
    
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
     * @return Es_Entity_AbstractNotifyType
     */
    public function getNotifyType() {
        return $this->notifyType;
    }

    /**
     * 
     * @param Es_Entity_AbstractNotifyType $notifyType
     */
    public function setNotifyType(Es_Entity_AbstractNotifyType $notifyType) {
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
     * @param Es_Entity_AbstractEventType $eventType
     */
    public function setEventType(Es_Entity_AbstractEventType $eventType) {
        $this->eventType = $eventType;
    }
        
    public function getOnlyNotShowed() {
        return $this->onlyNotShowed;
    }

    public function setOnlyNotShowed($onlyNotShowed) {
        $this->onlyNotShowed = $onlyNotShowed;
    }
    
    public function getEventTriggerId() {
        return $this->eventTriggerId;
    }

    public function setEventTriggerId($eventTriggerId) {
        $this->eventTriggerId = $eventTriggerId;
    }
    
    public function getGroup() {
        return $this->group;
    }

    public function setGroup(\Es_Entity_AbstractGroup $group) {
        $this->group = $group;
    }
    
    public function getGroupTypeId() {
        return $this->groupTypeId;
    }

    public function setGroupTypeId($groupTypeId) {
        $this->groupTypeId = $groupTypeId;
    }

    /**
     * Get isGroupResultRequire.
     *
     * @return isGroupResultRequire.
     */
    public function getIsGroupResultRequire()
    {
        return $this->isGroupResultRequire;
    }
    
    /**
     * Set isGroupResultRequire.
     *
     * @param isGroupResultRequire the value to set.
     */
    public function setIsGroupResultRequire($isGroupResultRequire)
    {
        if (!is_bool($isGroupResultRequire)) {
            throw new Es_Exception_InvalidArgument('Group result switcher parameter must be a boolean');
        }
        $this->isGroupResultRequire = $isGroupResultRequire;
    }
    
    /**
     * Get limit.
     *
     * @return limit.
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Set limit.
     *
     * @param limit the value to set.
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
    
    /**
     * Get fromTime.
     *
     * @return fromTime.
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }
    
    /**
     * Set fromTime.
     *
     * @param fromTime the value to set.
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;
    }
    
    /**
     * Get toTime.
     *
     * @return toTime.
     */
    public function getToTime()
    {
        return $this->toTime;
    }
    
    /**
     * Set toTime.
     *
     * @param toTime the value to set.
     */
    public function setToTime($toTime)
    {
        $this->toTime = $toTime;
    }
    
    /**
     * Get groupId.
     *
     * @return groupId.
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
    
    /**
     * Set groupId.
     *
     * @param groupId the value to set.
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }
    
    public function getForceStats() {
        return $this->forceStats;
    }

    public function setForceStats($forceStats) {
        $this->forceStats = $forceStats;
    }

    public function getSingleSubject()
    {
        return $this->singleSubject;
    }

    public function setSingleSubject($singleSubject)
    {
        $this->singleSubject = $singleSubject;
    }

    /**
     * Исключение из выдачи списка событий
     *
     * @param $eventTypes
     */
    public function setExcludeEventTypes($eventTypes)
    {
        $exclude = &$this->excludeEventTypes;

        foreach ($eventTypes as $eventType) {
            $exclude[$eventType] = $eventType;
        }
    }

    public function getExcludeEventTypes()
    {
        return array_values($this->excludeEventTypes);
    }

}

?>
