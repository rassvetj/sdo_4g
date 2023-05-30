<?php
class HM_Learningsubjects_Import_Manager
{
    protected $_existingLearningsubjects = array();
    protected $_existingLearningsubjectsIds = array();

    protected $_insertsLearningsubjects = array();
    protected $_updatesLearningsubjects = array();
    protected $_deletesLearningsubjects = array();
    
    const CACHE_NAME = 'HM_Learningsubjects_Import_Manager';
    
    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $learningsubjects = $this->getService('Learningsubjects')->fetchAll();

        if (count($learningsubjects)) {
            foreach($learningsubjects as $learningsubject) {
                $this->_existingLearningsubjects[$learningsubject->learning_subject_id] = $learningsubject;
                if (strlen($learningsubject->id_external)) {
                    $learningsubject->id_external = trim($learningsubject->id_external);
                    $this->_existingLearningsubjectsIds[$learningsubject->id_external] = $learningsubject->learning_subject_id;
                }
            }
        }
    }

    protected function _needLearningsubjectUpdate($learningsubject)
    {
        $existingLearningsubject = $this->_existingLearningsubjects[$this->_existingLearningsubjectsIds[$learningsubject->id_external]];
		
        
		//--fix for cron
		if(method_exists($learningsubject, 'getValues')) {											
			$values = $learningsubject->getValues(null, array('id_external'));
		} else {					
			$values = $this->getValues($learningsubject, null, array('id_external'));			
		}
		
        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingLearningsubject->{$key} != $value) {
                    return true;
                }
            }
        }

        return false;
    }
	
	/**
	 * для автоматической загружке по заданию
	*/
	/*
	protected function _needLearningsubjectUpdateAuto($learningsubject)
    {
        $existingLearningsubject = $this->_existingLearningsubjects[$this->_existingLearningsubjectsIds[$learningsubject->id_external]];
		
		$values = array();
		foreach((array)$learningsubject as $k => $v){
			if($k != 'id_external'){
				$values[$k] = $v;
			}
		}		
		
        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingLearningsubject->{$key} != $value) {
                    return true;
                }
            }
        }

        return false;
    }
	*/

    public function getInsertsCount()
    {
        return count($this->_insertsLearningsubjects);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesLearningsubjects);
    }

    public function getDeletesCount()
    {
        return count($this->_deletesLearningsubjects);
    }
    
    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_insertsLearningsubjects;
    }

    public function getUpdates()
    {
        return $this->_updatesLearningsubjects;
    }

    public function getDeletes()
    {
        return $this->_deletesLearningsubjects;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_insertsLearningsubjects,
                 'updates' => $this->_updatesLearningsubjects,
                 'deletes' => $this->_deletesLearningsubjects,
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
            $this->_insertsLearningsubjects = $actions['inserts'];
            $this->_updatesLearningsubjects = $actions['updates'];
            $this->_deletesLearningsubjects = $actions['deletes'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {        
		$this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                if (!isset($this->_existingLearningsubjectsIds[$item->id_external])) {
                    $this->_insertsLearningsubjects[$item->id_external] = $item;
                } else {
                    
					if (isset($this->_updatesLearningsubjects[$item->id_external])) continue;
                    if (!isset($this->_existingLearningsubjects[$this->_existingLearningsubjectsIds[$item->id_external]])) continue;
					
                    $existingLearningsubject = $this->_existingLearningsubjects[$this->_existingLearningsubjectsIds[$item->id_external]];
                    
                    if ($this->_needLearningsubjectUpdate($item)) {
                        $item->learning_subject_id = $existingLearningsubject->learning_subject_id;
                        $this->_updatesLearningsubjects[$item->id_external] = array('source' => $existingLearningsubject, 'destination' => $item);
                    }
                    unset($this->_existingLearningsubjects[$existingLearningsubject->learning_subject_id]);
                }
            }
        }

        if (count($this->_existingLearningsubjects)) {
//            $this->_deletesLearningsubjects = $this->_existingLearningsubjects;
        }
		
        $this->saveToCache();
    }
    
	/**
	 * @param - Zend Log
	*/
    public function import($log = false)
    {   
		try {
			$result = array(				
				'error' => false,
				'allInsert' => count($this->_insertsLearningsubjects), //--Всего для вставки
				'insert' => 0, //--по факту кол-во созданных
				'allUpdate' => count($this->_updatesLearningsubjects), //--Всего кол-во обновленных
				'update' => 0, //--по факту кол-во обновленных
			);	
			$learningsubjectsService = $this->getService('Learningsubjects');
			
			if (count($this->_insertsLearningsubjects)) {

				foreach($this->_insertsLearningsubjects as $id => $insert) {	
					//--fix for cron
					if(method_exists($insert, 'getValues')) {								
						$values = $insert->getValues(null, array());
					} else {					
						$values = $this->getValues($insert, null, array());			
					}
				
					$learningsubject = $learningsubjectsService->insert($values);
					if ($learningsubject) {
						$this->_existingLearningsubjectsIds[$learningsubject->id_external] = $learningsubject->learning_subject_id;
						$result['insert']++;						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_SUCCESS'], $learningsubject->id_external, $learningsubject->name), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_ERROR'], $insert['id_external'], $insert['name']), $log::ERR); }
					}
				}
			}
			
			if (count($this->_updatesLearningsubjects)) {
				foreach($this->_updatesLearningsubjects as $id => $update) {   
					//--fix for cron
					if(method_exists($update['destination'], 'getValues')) {								
						$values = $update['destination']->getValues(null, array());
					} else {					
						$values = $this->getValues($update['destination'], null, array());			
					}			
					$idUpdate = $learningsubjectsService->update($values);
					if($idUpdate){
						$result['update']++;						
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_SUCCESS'], $values['id_external'], $values['name']), 9); }
					} else {												
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_ERROR'], $values['id_external'], $values['name']), $log::ERR); }
					}
				}
			}
			
			if (count($this->_deletesLearningsubjects)) {
				foreach($this->_deletesLearningsubjects as $id => $delete) {
					if (strlen($delete->id_external)) {
	//                    $learningsubjectsService->delete($delete->learning_subject_id);
					}
				}
			}
		} catch (Exception $e) {								
			$result['error'] = true;	
			if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), $log::ERR); }			
		}		
		return $result;		
    }
	
	
	
	/**
	 * возвращает значения определенных полей. mod HM_Model_Abstract
	*/
	public function getValues($data = array(), $keys = null, $excludes = null)
	{
		$data = (array)$data;
		$values = array();
		if (is_array($data) && count($data)) {
			foreach($data as $key => $value) {
				if ((!is_object($value) && !is_array($value)) || $value instanceof Zend_Db_Expr) {
					if (is_array($keys) && !in_array($key, $keys)) continue;
					if (is_array($excludes) && in_array($key, $excludes)) continue;
					$values[$key] = $value;
				}
			}
		}
		return $values;
	}
}