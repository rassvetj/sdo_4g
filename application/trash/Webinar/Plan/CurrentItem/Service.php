<?php
class Webinar_Plan_CurrentItem_Service extends Object_Service {
    protected static $_instance;

    public function __construct() {
        $this->_table = new Webinar_Plan_CurrentItem_Table();
    }

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getCurrentItem($pointId) {
        $current = $this->_table->find($pointId);
        if (isset($current[0])) {
            return $current[0]['currentItem'];
        }
    }
        
    public function setCurrentItem($pointId, $itemId) {
        $this->_table->delete($this->_table->getAdapter()->quoteInto('pointId = ?', $pointId));
        return $this->_table->insert(array('pointId' => $pointId, 'currentItem' => $itemId));
    }
}