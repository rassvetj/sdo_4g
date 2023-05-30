<?php
class Task_Service extends Object_Service {       

    public function __construct() {
        $this->_table = new Task_Table();
    }

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function get($taskId) {
        if ($tasks = $this->_table->find($taskId)) {
            if ($tasks->count()) {
                return $tasks->current();
            }
        }
    }
    
    public function isPublic($taskId) {
        if ($task = $this->get($taskId)) {
            return $task->pub;
        }
    }
    
    public function isUserAllowed($taskId, $userId) {
        $select = $this->_table->getAdapter()->select();
        $select->from('scheduleID')->where('SHEID = ?', (int) $taskId)->where('MID = ?', (int) $userId);
        $result = $this->_table->getAdapter()->fetchRow($select);
        return count($result);
    }
    
    public function isTeacherAllowed($taskId, $teacherId)
    {
        $select = $this->_table->getAdapter()->select();
        $select->from('schedule')->where('SHEID = ?', (int) $taskId)->where('teacher = ?', (int) $teacherId);
        $result = $this->_table->getAdapter()->fetchRow($select);
        return count($result);
    	
    }

    public function getUserList($taskId) {
        $list = array();
        $users = new Users();
        $select = $users->select()
                        ->from('People')
                        ->join('scheduleID', 'scheduleID.MID=People.MID')
                        ->where('scheduleID.SHEID = ?', (int) $taskId)
                        ->setIntegrityCheck(false);
        foreach ($users->fetchAll($select) as $user) {
            $list[] = $user;
        }
        return $list;
    }

    public function getTeacherId($taskId) {
        if ($task = $this->get($taskId)) {
            return $task->teacher;
        }
    }
    
    public function isRunnable($taskId) {
    	
    }

}