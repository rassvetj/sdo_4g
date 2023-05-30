<?php
class Webinar_Library_Service extends Library_Service 
{
    protected static $_instance;

    /**
     * @return Webinar_Library_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function get($pointId)
    {
    	if ($pointId) {
    		$select = $this->_table->select()
                               ->where('pointId = ?', $pointId);
            $rows = $this->_table->fetchAll($select);
    	    if ($rows->count()) {    	    	
                return $rows->current();
            }
    	}
    }
    
    public function insertIfNotExists($data)
    {
    	if ($data['pointId']) {
    	    if (!$this->get($data['pointId'])) {
    	    	$task = Task_Service::getInstance()->get($data['pointId']);
    	    	if ($task) {
    	    		$data['title'] = $task->title;
    	    		$item = $this->_table->createRow($data);
    	    		$item->save();
    	    		return $item->bid;    	    	
    	    	}
    	    }
    	}
    }    
}