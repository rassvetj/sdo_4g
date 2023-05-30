<?php
/**
 * Description of AbstractFilter
 *
 * @author slava
 */
abstract class Es_Entity_AbstractFilter {
    
    protected $userId = null;
    
    /**
     *
     * @var int|array
     */
    protected $eventId = null;
    
    /**
     *
     * @var array
     */
    protected $types = null;
    
    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }
    
    /**
     * 
     * @return array
     */
    public function getTypes() {
        return $this->types;
    }

    /**
     * 
     * @param array $types
     */
    public function setTypes(array $types) {
        $this->types = $types;
    }
    
    /**
     * 
     * @return int|array
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * 
     * @param int|array $eventId
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;
    }
    
}

?>
