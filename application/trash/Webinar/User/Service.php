<?php
class Webinar_User_Service extends Object_Service {

    protected static $_instance;

    public function __construct() {
        $this->_table = new Webinar_User_Table();
    }

    /**
     * 
     * @return Webinar_User_Service
     */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function insert(Webinar_User $user) {
        return $this->_table->insert($user->getAsArray());
    }

    public function update(Webinar_User $user) {
        $where = $this->_table->getAdapter()->quoteInto('pointId = ?', $user->pointId).' AND '
                .$this->_table->getAdapter()->quoteInto('userId = ?', $user->userId);
        unset($user->pointId);
        unset($user->userId);
        return $this->_table->update($user->getAsArray(), $where);
    }   
    
    public function pingUser($pointId, $userId) {
    	$users = $this->_table->find($pointId, $userId);
    	if ($users->count()) {
    		$user = $users->current();
    		$user->last = date('Y-m-d H:i:s');
    		$user->save();
    	} else {
    		$user = $this->_table->createRow();    		
    		$user->pointId = $pointId;
            $user->userId = $userId;
    		$user->last = date('Y-m-d H:i:s');
    		$user->save();
    	}
    }
    
    public function getLeader($pointId) {
        $webinarPerson = false;
        $users = new Users();
        $select = $users->select()
                        ->from('People')
                        ->join('schedule', 'schedule.teacher = People.MID')
                        ->where('schedule.SHEID = ?', $pointId)
                        ->setIntegrityCheck(false);
        foreach ($users->fetchAll($select) as $user) {
            /*$webinarPerson = new Webinar_User();
            $webinarPerson->id         = $user->MID;
            $webinarPerson->lastName   = $user->LastName;
            $webinarPerson->firstName  = $user->FirstName;
            $webinarPerson->middleName = $user->Patronymic;*/
        	return $user;        	
        }
        //return $webinarPerson;
    }

    
    public function getUserList($pointId) {
        $list = array();
        
        $leader = $this->getLeader($pointId);
        if ($leader) {
        	$list[$leader->MID] = $leader;
        }
        
        $users = new Users();
        $select = $users->select()
                        ->from('People')
                        ->join('scheduleID', 'scheduleID.MID = People.MID')
                        ->where('scheduleID.SHEID = ?', $pointId)
                        ->setIntegrityCheck(false);
        foreach ($users->fetchAll($select) as $user) {
/*            $webinarPerson = new Webinar_User();
            $webinarPerson->id         = $user->MID;
            $webinarPerson->lastName   = $user->LastName;
            $webinarPerson->firstName  = $user->FirstName;
            $webinarPerson->middleName = $user->Patronymic;
*/
            $list[$user->MID] = $user;
        }
        return $list;
    }

    /**
     * Возвращает список юзеров онлайн для вебинара $pointId
     * @param int $pointId
     * @return array
     */
    public function getUserListOnline($pointId) {
        $list = array();
        $users = new Users();
        $where = $this->_table->getAdapter()->quoteInto('last < ?', date('Y-m-d H:i:s', time() - 60*5)).' AND '
                .$this->_table->getAdapter()->quoteInto('pointId = ?', $pointId);
        $this->_table->delete($where);

        $select = $users->select()
                        ->from('People')
                        ->join('webinar_users', 'webinar_users.userId = People.MID')
                        ->where('webinar_users.pointId = ?', $pointId)
                        ->setIntegrityCheck(false);
        foreach ($users->fetchAll($select) as $user) {
/*            $webinarPerson = new Webinar_User();
            $webinarPerson->id         = $user->MID;
            $webinarPerson->lastName   = $user->LastName;
            $webinarPerson->firstName  = $user->FirstName;
            $webinarPerson->middleName = $user->Patronymic;
*/
            $list[$user->MID] = $user;            
        } 
        return $list;
    }
   
}
