<?php
class Webinar_Plan_Service extends Object_Service {
    protected static $_instance;

    public function __construct() {
        $this->_table = new Webinar_Plan_Table();
    }

    /**
     * @return Webinar_Plan_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function insert(Webinar_Plan_Item $item) {
        return $this->_table->insert($item->getAsArray());
    }
    
    public function getCurrentItem($pointId) {
        return Webinar_Plan_CurrentItem_Service::getInstance()->getCurrentItem($pointId);    
    }
    
    public function setCurrentItem($pointId, $itemId) {
        return Webinar_Plan_CurrentItem_Service::getInstance()->setCurrentItem($pointId, $itemId);
    }
    
    public function getItemList($pointId) {
        $list = array();
        $select = $this->_table->select()
                               ->where('pointId = ?', $pointId)
                               ->order('id');
        foreach($this->_table->fetchAll($select) as $item) {
            $list[$item->id] = $item;
        }
        return $list;
    }
}
