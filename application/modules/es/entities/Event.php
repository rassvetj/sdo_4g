<?php
/**
 * Description of Event
 *
 * @author slava
 */
class Es_Entity_Event extends Es_Entity_AbstractEvent implements Es_Entity_HasGroupProperty {

    /**
     *
     * @var \Es_Entity_AbstractGroup
     */
    protected $group = null;
    
    /**
     *
     * @var \Es_Entity_AbstractGroupType
     */
    protected $groupType = null;
    
    /**
     * 
     * @return \Es_Entity_AbstractGroupType
     */
    public function getGroupType() {
        return $this->groupType;
    }

    /**
     * 
     * @param \Es_Entity_AbstractGroupType $groupType
     */
    public function setGroupType(\Es_Entity_AbstractGroupType $groupType) {
        $this->groupType = $groupType;
    }

    
    /**
     * Get group.
     *
     * @return \Es_Entity_AbstractGroup
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Set group.
     *
     * @param $group \Es_Entity_AbstractGroup the value to set.
     */
    public function setGroup(\Es_Entity_AbstractGroup $group)
    {
        $this->group = $group;
    }
}

?>
