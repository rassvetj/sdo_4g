<?php

/**
 * Description of AbstractNotifyType
 *
 * @author slava
 */
abstract class Es_Entity_AbstractNotifyType {
    
    protected $id = null;
    protected $name = null;
    
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
    
}

?>
