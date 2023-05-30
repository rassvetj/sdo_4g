<?php
/**
 * Description of AbstractGroupType
 *
 * @author slava
 */
abstract class Es_Entity_AbstractGroupType {
    
    const GROUP_TYPE_PERSONAL_MESSAGE = 1;
    const GROUP_TYPE_DISCUSSION = 2;
    const GROUP_TYPE_NOTIFICATION = 3;
    
    protected $id = null;
    protected $name = null;
    
    /**
     *
     * @var Es_Entity_AbstractGroupTypeStat 
     */
    protected $stat = null;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * 
     * @return Es_Entity_AbstractGroupTypeStat
     */
    public function getStat() {
        return $this->stat;
    }

    /**
     * 
     * @param Es_Entity_AbstractGroupTypeStat $stat
     */
    public function setStat(Es_Entity_AbstractGroupTypeStat $stat) {
        $this->stat = $stat;
    }

    
}

?>
