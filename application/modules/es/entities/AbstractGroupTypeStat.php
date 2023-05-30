<?php
/**
 * Description of AbstractGroupTypeStat
 *
 * @author slava
 */
abstract class Es_Entity_AbstractGroupTypeStat {
    
    protected $showed = null;
    protected $notShowed = null;
    
    public function getShowed() {
        return $this->showed;
    }

    public function setShowed($showed) {
        $this->showed = $showed;
    }
    
    public function getNotShowed() {
        return $this->notShowed;
    }

    public function setNotShowed($notShowed) {
        $this->notShowed = $notShowed;
    }
    
}

?>
