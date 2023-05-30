<?php
class Webinar_History_Service extends Object_Service {
    protected static $_instance;

    public function __construct() {
        $this->_table = new Webinar_History_Table();
    }

    /**
     * 
     * @return Webinar_History_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function insert(Webinar_History_Item $item) {
    	$item->save();    	
        //return $this->_table->insert($item->getAsArray());
    }

    public function insertCurrentItem($pointId, $itemId) {
        $userId = Library::getUserId();
        if ($userId > 0) {
        	$webinarHistoryItem = $this->_table->createRow(
        	    array(
                    'userId' => $userId,
                    'pointId' => $pointId,
                    'action' => 'set',
                    'item' => $itemId,
                    'datetime' => date('Y-m-d H:i:s')
                )
            );
            return $this->insert($webinarHistoryItem);
        }
    }

    public function insertRecordStart($pointId, $filename = '') {
        $userId = Library::getUserId();
        if ($userId > 0) {
            $webinarHistoryItem = $this->_table->createRow(
                array(
                    'userId' => $userId,
                    'pointId' => $pointId,
                    'action' => 'record start',
                    'item' => $filename,
                    'datetime' => date('Y-m-d H:i:s')
                )
            );
            return $this->insert($webinarHistoryItem);
        }
    }

    public function insertRecordStop($pointId) {
        $userId = Library::getUserId();
        if ($userId > 0) {
            $webinarHistoryItem = $this->_table->createRow(
                array(
                    'userId' => $userId,
                    'pointId' => $pointId,
                    'action' => 'record stop',
                    'datetime' => date('Y-m-d H:i:s')
                )
            );
            return $this->insert($webinarHistoryItem);
        }
    }

    public function getList($pointId) {
        $list = array();

        $select = $this->_table->select()
                               ->where('pointId = ?', $pointId)
                               ->order('id');
        return $this->_table->fetchAll($select);
        //foreach($this->_table->fetchAll($select) as $item) {
            /*$historyItem = new Webinar_History_Item();
            $historyItem->id = $item->id;
            $historyItem->pointId = $pointId;
            $historyItem->userId = $item->userId;
            $historyItem->action = $item->action;
            $historyItem->item = $item->item;
            $historyItem->datetime = $item->datetime;
            if ($item->datetime instanceof DateTime) {
                $historyItem->datetime = $item->datetime->format('Y-m-d H:i:s');
            }*/
            
            //$list[] = $historyItem;
        //}

        //return $list;
    }
    
    public function getFiles($pointId) 
    {
    	$files = array();
    	
    	if ($pointId) {
    		$select = $this->_table->select()
    		                      ->where('pointId = ?', $pointId)
    		                      ->where('action = ?', 'record start')
    		                      ->where('item <> ?', '');
    	    $historyItems = $this->_table->fetchAll($select);
    	    if ($historyItems) {
    	    	foreach($historyItems as $item) {
    	    		if (strlen($item->item)) {
    	    			$list = Webinar_Service::getInstance()->getFiles($item->item);
    	    			if (is_array($list) && count($list)) {
    	    				foreach($list as $file) {
    	    					$files[$file] = $file;
    	    				}
    	    			}
    	    		}
    	    	}
    	    }
    	}
    	
    	return $files;
    }

}