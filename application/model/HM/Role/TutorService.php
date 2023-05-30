<?php
class HM_Role_TutorService extends HM_Service_Abstract
{
    
	const CACHE_NAME 		= 'HM_Role_TutorService';
	const CACHE_LIFETIME	= 900; #900; # время жизни кэша. 15 минут
	
	protected $_subject_ids	= array(); # кэш id сессий, на которые назначен тьютор
	
	
	private function saveToCacheByName($name)
    {
        return Zend_Registry::get('cache')->save(
            array(
                $name.'_lifetime'	=> $this->{$name.'_lifetime'},
                $name				=> $this->{$name},
            ),
            self::CACHE_NAME.'_'.$name
        );
    }
	
	private function clearCacheByName($name)
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME.'_'.$name);
    }
	
	private function restoreFromCacheByName($name)
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME.'_'.$name)) {
            $this->{$name}				= $actions[$name];
            $this->{$name.'_lifetime'}	= $actions[$name.'_lifetime'];
            return true;
        }
        return false;
    }
	
	
	public function isUserExists($subjectId, $userId)
    {
        $collection = $this->fetchAll( array('CID = ?' => $subjectId, 'MID = ?' => $userId)
        //$this->quoteInto(array('CID = ?', 'MID = ?'), array($subjectId, $userId))
        );
        return count($collection);
    }
	
	
	/**
	 * тьютор первого продления
	*/
	public function isDebtTutorFirst($tutor_id, $subject_id){
		$item = $this->getOne($this->fetchAll( array('CID = ?' => $subject_id, 'MID = ?' => $tutor_id)));
		if(!empty($item->date_debt)){ return true; }
		return false; 
	}
	
	
	/**
	 * тьютор второго продления
	*/
	public function isDebtTutorSecond($tutor_id, $subject_id){
		$item = $this->getOne($this->fetchAll( array('CID = ?' => $subject_id, 'MID = ?' => $tutor_id)));
		if(!empty($item->date_debt_2)){ return true; }
		return false; 
	}
	
	
	public function getAssigns($subject_id){
		return $this->fetchAll(array('CID = ?' => $subject_id));        
	}
	
	public function getAssign($subjectId, $tutorId){
		return $this->getOne($this->fetchAll($this->quoteInto(array('CID = ? AND ', 'MID = ?'), array($subjectId, $tutorId))));
	}
	
	
	public function getSubjectIds($tutor_id){
		$cache_field 			= '_subject_ids';
		$cache_field_lifetime	= $cache_field.'_lifetime';
		$key					= $tutor_id;
		
		if(empty($this->{$cache_field})){ 
			$this->restoreFromCacheByName($cache_field);			
		}
		
		if($this->{$cache_field_lifetime} <= time() ){ # очищаем кэш, старше CACHE_LIFETIME
			$this->clearCacheByName($cache_field);  	
			$this->{$cache_field_lifetime} = time() + self::CACHE_LIFETIME;			
		}
		
		if(isset($this->{$cache_field}[$key])){			
			return $this->{$cache_field}[$key];			
		} 
		
		$this->{$cache_field}[$key] = $this->fetchAll(array('MID = ?' => $tutor_id))->getList('CID');
		
		$this->saveToCacheByName($cache_field);		
		return $this->{$cache_field}[$key];     
	}
	
	public function update($data){
		
		return parent::update($data);	
	}
	
	
}