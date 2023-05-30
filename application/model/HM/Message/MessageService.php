<?php
class HM_Message_MessageService extends HM_Service_Abstract
{
    protected $_allowUsers	= array(); # id студентов, доступных для отправки сообщения
    protected $_lifeTime	= array(); # время жизни кэша
	const CACHE_NAME 		= 'HM_Message_MessageService';
	
	
	public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        return parent::insert($data);
    }
	
	
	/**
	 * разрешенные адресаты при отправке писем.
	 * Сейчас - для студентов только студенты его группы + все тьюторы текузих и прошедших сессий. Не завершенных
	*/
	public function getAllowMessageUsers($subject_id){
		
		$this->restoreFromCache();
		
		if($this->_lifeTime < time() ){
			$this->clearCache();
		}
		
		$user_id = $this->getService('User')->getCurrentUserId();
		$key 	 = $user_id.'~'.intval($subject_id);
		
		if(!empty($this->_allowUsers[$key])){			
			return $this->_allowUsers[$key];
		}
		
		
		$user_id	= $this->getService('User')->getCurrentUserId();
		$users_ids	= array();
		# студенты группы студента. array (key = value = mid)
		$group_ids	= $this->getService('StudyGroupUsers')->fetchAll($this->quoteInto('user_id = ?', $user_id ))->getList('group_id');
		
		if(!empty($group_ids)){ 
			$users_ids = $this->getService('StudyGroupUsers')->fetchAll($this->quoteInto('group_id IN (?)', $group_ids))->getList('user_id');
		}
		
		if(!empty($subject_id)){ 			
			# все тьюторы сессии array(value = mid);		
			$tutors    = array_keys($this->getService('Subject')->getTutotList($subject_id));
			$users_ids = $users_ids + $tutors;				
		}
			
		$this->_allowUsers[$key] = $users_ids;
		$this->_lifeTime		 = time() + (60*60*24); #  1 день кэш
		$this->saveToCache();		
		return $users_ids;		
	}
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'allowUsers'	=> $this->_allowUsers,                                                                                                                   
                 'lifeTime' 	=> $this->_lifeTime,                                                                                                                   
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_allowUsers 	= $actions['allowUsers'];                                              
            $this->_lifeTime 	= $actions['lifeTime'];                                              
            return true;
        }
        return false;
    }
	
}