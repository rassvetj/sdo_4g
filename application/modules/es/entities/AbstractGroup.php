<?php

class Es_Entity_AbstractGroup
{

    protected $id = null;
    protected $triggerInstanceId = null;
    protected $data = null;
    protected $type = null;


    /**
     *   Get type
     *
     * @return {string} type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param {string} the value to set.
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    
    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set id.
     *
     * @param id the value to set.
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Get data.
     *
     * @return data.
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set data.
     *
     * @param data the value to set.
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    
    /**
     * Get triggerInstanceId.
     *
     * @return triggerInstanceId.
     */
    public function getTriggerInstanceId()
    {
        return $this->triggerInstanceId;
    }
    
    /**
     * Set triggerInstanceId.
     *
     * @param triggerInstanceId the value to set.
     */
    public function setTriggerInstanceId($triggerInstanceId)
    {
        $this->triggerInstanceId = $triggerInstanceId;
    }

    public function getName()
    {
        if ($this->getTriggerInstanceId() === null || $this->getType() === null) {
            throw new Es_Exception_Runtime('Event group unique name parts were not defined correctly');
        }
        return $this->getType().'_'.$this->getTriggerInstanceId();
    }

}

?>
