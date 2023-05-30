<?php
class HM_Learningsubjects_LearningsubjectsService extends HM_Service_Abstract
{
    const CACHE_NAME = 'HM_Learningsubjects_LearningsubjectsService';
	
	const CACHE_LIFETIME = 3600; # время жизни кэша  секунд
	
	private $_control_list 	= array();
	private $_expired 		= 0;
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 '_control_list'	=> $this->_control_list,
                 '_expired'  		=> $this->_expired,
				 
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
            $this->_control_list	= $actions['_control_list'];            
            $this->_expired   		= $actions['_expired'];            
            return true;
        }

        return false;
    }
	
	
	public function getIndexSelect() {
       

	   $select = $this->getSelect();
        $select->from(
            array(
                'ls' => 'learning_subjects'
            ),
            array(
                'ls.learning_subject_id',                
                'ls.name',
				'ls.module_code',
				'ls.id_external',
                'direction' => new Zend_Db_Expr("REPLACE(ls.direction, '~', ' ')"),				
                'ls.specialisation',
                'ls.hours',
                'ls.control',
                'ls.year',
				'ls.semester',
                'subject_name' => 's.name',				
                'ls.name_plan',
                'ls.date_update',
				'sessions' => new Zend_Db_Expr("MAX(session.subid)"),
				'ls.isDO',
				'ls.comment',				
            )
        );        
        $select->joinLeft(
            array('s' => 'Subjects'),
            ('s.subid = ls.subject_id'),
            array()
        );
		
		$select->joinLeft(
            array('session' => 'subjects'),
            'session.learning_subject_id_external = ls.id_external',
            array()
        );
		
		
		$select->group(array('ls.learning_subject_id', 'ls.name', 'ls.module_code', 'ls.direction', 'ls.specialisation', 'ls.hours', 'ls.control',  'ls.year', 'ls.semester', 's.name', 'ls.name_plan', 'ls.date_update', 'ls.id_external', 'ls.comment', 'ls.isDO'));	
		
        return $select;
    } 
	
	/**
	 * Определяет прикрепленный базовый курс к предмету	 	 
	 */
	public function getBaseSubjectId($learning_subject_id_external, $module_code = NULL) {		
        $learningsubject = $this->fetchAll(array('id_external = ?' => $learning_subject_id_external))->current();
		return $learningsubject->subject_id;		
	}
	
	
	/**
	 * получаем учебный предмет по коду из 1С
	 */
	public function getByCode($learning_subject_id_external){
        return $this->fetchAll(array('id_external = ?' => $learning_subject_id_external))->current();		
	}
	
	
	
	public function getControlList()
	{
		$this->restoreFromCache();
		if($this->_expired < time()){
			$this->clearCache();
		}
		
		if(!empty($this->_control_list)){ return $this->_control_list;  }
		
		$select = $this->getSelect();
		$select->from('learning_subjects', array('control'));
		$select->group('control');
		$res = $select->query()->fetchAll();
		$data = array();
		
		foreach($res as $i){
			if(empty($i['control'])){ continue; }
			$data[$i['control']] = $i['control'];
		}
		ksort($data);
		
		$this->_control_list 	= $data;
		$this->_expired 		= time() + self::CACHE_LIFETIME;		
		$this->saveToCache();
		
		return $data;
	}
	
	
	
	
}