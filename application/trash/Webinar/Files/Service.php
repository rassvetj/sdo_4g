<?php
class Webinar_Files_Service extends Object_Service {
    protected static $_instance;

    public function __construct() {
        $this->_table = new Webinar_Files_Table();
    }

    /**
     * @return Webinar_Files_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getItemList($pointId) {
        $list = array();
        
        /*$select = $this->_table->select()
                               ->joinInner(array('webinar_files'), 'webinar_files.file_id = files.file_id', array())
                               ->where('webinar_files.webinar_id = ?', $pointId)
                               ->order('file_id');*/
        $select = $this->_table->select()
                ->from(array('t1' => 'files'), 't1.*')
                ->joinInner(array('t2' => 'webinar_history'), 't2.item = t1.file_id', array())
                ->where('t2.pointId = ?', $pointId)
                ->where('t2.action = ?', 'set')
                ->order('t1.file_id');
        foreach($this->_table->fetchAll($select) as $item) {
            $list[$item->file_id] = $item;
        }
        return $list;
    }
    
}
