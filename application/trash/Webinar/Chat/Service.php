<?php
class Webinar_Chat_Service extends Task_Service {
    protected static $_instance;

    public function __construct() {
        $this->_table = new Webinar_Chat_Table();
    }
    
    /**
     * @return Webinar_Chat_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function insert($data) {
    	$message = $this->_table->createRow($data);
    	$message->save();
    	return $message->id;
        //return $this->_table->insert($message->getAsArray());
    }

    public function getListByInterval($pointId, $start, $stop) {
        $list = array();

        $select = $this->_table->select()
                               ->where('pointId = ?', $pointId)
                               ->where('datetime >= ?', $start)
                               ->where('datetime <= ?', $stop)
                               ->order('id');
        return $this->_table->fetchAll($select);
/*        foreach($this->_table->fetchAll($select) as $item) {
            $message = new Webinar_Chat_Message();
            $message->id = $item->id;
            $message->pointId = $pointId;
            $message->userId = $item->userId;
            $message->message = $item->message;
            $message->datetime = $item->datetime;
            if ($item->datetime instanceof DateTime) {
                $message->datetime = $item->datetime->format('Y-m-d H:i:s');
            }
            
            $list[] = $message;
        }

        return $list;
*/    }
    
    public function getList($pointId) {
        $list = array();

        $select = $this->_table->select()
                               ->where('pointId = ?', $pointId)
                               ->order('id');
        return $this->_table->fetchAll($select);
/*        foreach($this->_table->fetchAll($select) as $item) {
            $message = new Webinar_Chat_Message();
            $message->id = $item->id;
            $message->pointId = $pointId;
            $message->userId = $item->userId;
            $message->message = $item->message;
            $message->datetime = $item->datetime;
            if ($item->datetime instanceof DateTime) {
                $message->datetime = $item->datetime->format('Y-m-d H:i:s');
            }
            
            $list[] = $message;
        }

        return $list;*/
    }
}