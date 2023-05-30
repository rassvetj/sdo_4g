<?php
class HM_Programm_Import_Manager
{
    protected $_existingProgramm = array();
    protected $_existingProgrammIds = array();

    protected $_insertsProgramm = array();
    protected $_updatesProgramm = array();
    protected $_deletesProgramm = array();
    
    const CACHE_NAME = 'HM_Programm_Import_Manager';
    
    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $where = $this->getService('Programm')->quoteInto(array('id_external <> \'\''), array());
        $programms = $this->getService('Programm')->fetchAll($where);

        if (count($programms)) {
            foreach($programms as $programm) {
                $this->_existingProgramm[$programm->programm_id] = $programm;
                if (strlen($programm->id_external)) {
                    $programm->id_external = trim($programm->id_external);
                    $this->_existingProgrammIds[$programm->id_external] = $programm->programm_id;
                }
            }
        }
    }

    protected function _needProgrammUpdate($programm)
    {
        $existingProgramm = $this->_existingProgramm[$this->_existingProgrammIds[$programm->id_external]];

		//--fix for cron
		if(method_exists($programm, 'getValues')) {			
			$values = $programm->getValues(null, array('id_external'));
		} else {					
			$values = $this->getValues($programm, null, array('id_external'));			
		}
		

        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingProgramm->{$key} != $value) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getInsertsCount()
    {
        return count($this->_insertsProgramm);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesProgramm);
    }

    public function getDeletesCount()
    {
        return count($this->_deletesProgramm);
    }
    
    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_insertsProgramm;
    }

    public function getUpdates()
    {
        return $this->_updatesProgramm;
    }

    public function getDeletes()
    {
        return $this->_deletesProgramm;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_insertsProgramm,
                 'updates' => $this->_updatesProgramm,
                 'deletes' => $this->_deletesProgramm,
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
            $this->_insertsProgramm = $actions['inserts'];
            $this->_updatesProgramm = $actions['updates'];
            $this->_deletesProgramm = $actions['deletes'];
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

                if (!isset($this->_existingProgrammIds[$item->id_external])) {
                    $this->_insertsProgramm[$item->id_external] = $item;
                } else {
                    if (isset($this->_updatesProgramm[$item->id_external])) continue;
                    if (!isset($this->_existingProgramm[$this->_existingProgrammIds[$item->id_external]])) continue;

                    $existingProgramm = $this->_existingProgramm[$this->_existingProgrammIds[$item->id_external]];
                    
                    if ($this->_needProgrammUpdate($item)) {
                        $item->programm_id = $existingProgramm->programm_id;
                        $this->_updatesProgramm[$item->id_external] = array('source' => $existingProgramm, 'destination' => $item);
                    }
                    unset($this->_existingProgramm[$existingProgramm->programm_id]);
                }
            }
        }

        if (count($this->_existingProgramm)) {
//            $this->_deletesProgramm = $this->_existingProgramm;
        }

        $this->saveToCache();
    }
    
	/**
	 * @param - Zend Log
	*/
    public function import($log = false)
    {
        $result = array(			
			'error' => false,
			'allInsert' => count($this->_insertsProgramm), //--Всего для вставки			
			'insert' => 0, //--по факту вставлено
			'allUpdate' => count($this->_updatesProgramm), //--Всего кол-во обновленных
			'update' => 0, //--по факту кол-во обновленных			
		);
		
		try { 
		
			$programmsService = $this->getService('Programm');
			if (count($this->_insertsProgramm)) {

				foreach($this->_insertsProgramm as $id => $insert) {

					//--fix for cron
					if(method_exists($insert, 'getValues')) {								
						$values = $insert->getValues(null, array());
					} else {					
						$values = $this->getValues($insert, null, array());			
					}
					
					//$programm = $programmsService->insert($insert->getValues(null, array()));
					
					$programm = $programmsService->insert($values);
					if ($programm) {
						$this->_existingProgrammIds[$programm->id_external] = $programm->programm_id;
						$result['insert']++;						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_SUCCESS'], $values['id_external'], $values['name']), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_ERROR'], $values['id_external'], $values['name']), $log::ERR); }
					}
				}
			}

			if (count($this->_updatesProgramm)) {
				foreach($this->_updatesProgramm as $id => $update) {
					
					//--fix for cron
					if(method_exists($update['destination'], 'getValues')) {								
						$values = $update['destination']->getValues(null, array());
					} else {					
						$values = $this->getValues($update['destination'], null, array());			
					}
					
					$update = $programmsService->update($values);
					
					if($update){
						$result['update']++;						
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_SUCCESS'], $values['id_external'], $values['name']), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_ERROR'], $values['id_external'], $values['name']), $log::ERR); }
					}
				}
			}
			
			if (count($this->_deletesProgramm)) {
				foreach($this->_deletesProgramm as $id => $delete) {
					if (strlen($delete->id_external)) {
	//                    $programmsService->delete($delete->programm_id);
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