<?php
/*
 * ВНИМАНИЕ!!! 
 * Прим мерже с боковыми ветками может потеряться совместимость с адаптерами
 * Если интеграция в проекте настроена, лучше вообще не мержить этот файл 
*
*/

class HM_Orgstructure_Import_Manager
{

    const CACHE_NAME = 'HM_Orgstructure_Import_Manager';

    const POSITION_SOID_EXTERNAL = 'exported';
    
    protected $_existingItems = array();
    protected $_existingIds = array();
    protected $_existingPeople = array();
    protected $_existingPeopleIds = array();

    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();
    
    protected $_positions = array();
    
    private $_restoredFromCache = false;

    
    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'inserts'   => $this->_inserts,
                'updates'   => $this->_updates,
                'deletes'   => $this->_deletes,
                'positions' => $this->_positions,
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
            $this->_inserts = $actions['inserts'];
            $this->_updates = $actions['updates'];
            $this->_deletes = $actions['deletes'];
            $this->_positions = $actions['positions'];
			
			//-------------------------------
			//return $actions;
			
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getInserts()
    {
        return $this->_inserts;
    }
  
    public function getUpdates()
    {
        return $this->_updates;
    }

    public function getDeletes()
    {
        return $this->_deletes;
    }
    
    public function getPositions()
    {
        return $this->_positions;
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getUpdatesCount()
    {
        return count($this->_updates);
    }

    public function getDeletesCount()
    {
        return count($this->_deletes);
    }
    
    public function getPositionsCount()
    {
        return count($this->_positions);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount() + $this->getDeletesCount() + $this->getPositionsCount();
    }

    protected function _init()
    {
        $items = $this->getService('Orgstructure')->fetchAll(array(
            'blocked = ?' => 0,
            'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT
        ));

        if (count($items)) {
            foreach($items as $item) {
                $this->_existingItems[$item->soid] = $item;
                if (strlen($item->soid_external)) {
                    $item->soid_external = trim($item->soid_external);
                    $this->_existingIds[$item->soid_external] = $item->soid;
                }
            }
        }

        $persons = $this->getService('User')->fetchAll();

        if (count($persons)) {
            foreach($persons as $person) {
                $this->_existingPeople[$person->MID] = $person;
                if (strlen($person->mid_external)) {
                    $person->mid_external = trim($person->mid_external);
                    $this->_existingPeopleIds[$person->mid_external] = $person->MID;
                }
            }
        }

    }

    protected function _isItemExists($itemExternalId)
    {
        return isset($this->_existingIds[$itemExternalId]);
    }

    protected function _isPersonExists($personExternalId)
    {
        if (isset($this->_existingPeopleIds[$personExternalId])) {
            return $this->_existingPeopleIds[$personExternalId];
        }

        return false;
    }
    
    protected function _needItemUpdate($item)
    {        
		$existingItem = $this->_existingItems[$this->_existingIds[$item->soid_external]];
		
		if(!$existingItem){
			return false;
		}

        
		//--fix for cron
		if(method_exists($item, 'getValues')) {								
			$values = $item->getValues(null, array('soid', 'soid_external', 'mid_external', 'owner_soid_external'));
		} else {					
			$values = $this->getValues($item, null, array('soid', 'soid_external', 'mid_external', 'owner_soid_external'));			
		}
		
		
        if (count($values)) {
            foreach($values as $key => $value) {
                if (trim($existingItem->{$key}) != trim($value)) {
                    return true;
                }
            }
        }

        $item->owner_soid = trim($item->owner_soid);

        if (strlen($item->owner_soid)) {
            if (!$this->_isItemExists($item->owner_soid)) {
                return true;
            }

            if ($existingItem->owner_soid != $this->_existingIds[$item->owner_soid]) {
                return true;
            }
        }

        $item->mid = trim($item->mid);

        if (strlen($item->mid)) {
            if (!$this->_isPersonExists($item->mid)) return true;
            if ($existingItem->mid != $this->_existingPeopleIds[$item->mid]) {
                return true;
            }
        }

        return false;
    }

    public function init($items)
    {

        if ($this->_restoredFromCache) {
            $this->_init();
            return true;
        }

        if (count($items)) {

            $this->_init();

            foreach($items as $item) {
			

                $item->soid_external = trim($item->soid_external);

                if (strlen($item->soid_external)){

                    if (!$this->_isItemExists($item->soid_external)) {
                        // insert new item
                        $this->_inserts[$item->soid_external] = $item;
                    } else {

                        $existingItem = $this->_existingItems[$this->_existingIds[$item->soid_external]];

                        if ($this->_needItemUpdate($item)) {
                            $item->soid = $existingItem->soid;
                            $this->_updates[$item->soid_external] = array('source' => $existingItem, 'destination' => $item);
                        }

                        //unset($this->_existingIds[$existingItem->soid_external]);
                        unset($this->_existingItems[$existingItem->soid]);

                    }
                } else {                    	
					//--fix Для возможности добавить элемент с одинаковым id в разные разделы. Ex, преподаватель принадлежит к 2 орг. единицам.
					$this->_positions[$item->mid_external.'~'.$item->owner_soid_external] = $item;					
                }
            }

            if (count($this->_existingItems)) {
//                $this->_deletes = $this->_existingItems;
            }

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
				'allInsert' => count($this->_inserts), //--Всего для вставки			
				'allUpdate' => count($this->_updates), //--Всего кол-во обновленных
				'update' => 0, //--по факту кол-во обновленных
				'allUsers' => count($this->_positions), //--Всего  людей
				'users' => 0, //--по факту кол-во обновленных людей			
			);
        
			if (count($this->_inserts)) {
				
				$insertArr = array();
				foreach($this->_inserts as $ins){
					$insertArr[$ins->soid_external] = array('insert' => $ins, 'childs' => array(), 'parent' => 0);
				}

				// ВНИМАНИЕ!! может ооочень долго работать!
				foreach($insertArr as $key => &$value){
					foreach($insertArr as $key2 => &$value2){
						if($key == $value2['insert']->owner_soid_external){
							$value['childs'][] = &$value2;
							$value2['parent'] = $key;
						}
					}
				}


				foreach($insertArr as $insert){
					if($insert['parent'] === 0){
						$this->insertNode($insert);
					}
				}
			}

			if (count($this->_updates)) {
				foreach($this->_updates as $id => $update)
				{
					$update['destination']->owner_soid = $update['destination']->owner_soid_external; //--без этой строки все обновленные ноды становились первого уровня.
					$update = $update['destination'];

					if (strlen($update->owner_soid) && isset($this->_existingIds[$update->owner_soid])) {
						$update->owner_soid = $this->_existingIds[$update->owner_soid];
					} else {
						$update->owner_soid = 0;
					}

					
					//--fix for cron
					if(method_exists($update, 'getValues')) {								
						$values = $update->getValues(null, array('owner_soid_external', 'mid_external'));
					} else {					
						$values = $this->getValues($update, null, array('owner_soid_external', 'mid_external'));			
					}
					
					
					$update = $this->getService('Orgstructure')->updateNode($values, $update->soid, $update->owner_soid);
					if($update){
						$result['update']++;						 
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_SUCCESS'], $values['soid_external'], $values['name']), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_ERROR'], $values['soid_external'], $values['name']), $log::ERR); }
					}
				}
			}
			
			if (count($this->_positions)){
				$where = $this->getService('Orgstructure')->quoteInto(
					array(
						'type = ?',
						' AND soid_external = ?'
					),
					array(
						HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
						self::POSITION_SOID_EXTERNAL
						
					)
				);
				$this->getService('Orgstructure')->deleteBy($where);
				
				foreach($this->_positions as $mid_external => $position){
					
					//fix $mid_external - это 2 поля, разделенные ~. Берем только первую часть.
					$mid_external = strstr($mid_external,'~',true);
					
					if($this->_existingPeopleIds[$mid_external] && $this->_existingIds[$position->owner_soid_external]){
						$position->soid_external = self::POSITION_SOID_EXTERNAL;
						$position->owner_soid = $this->_existingIds[$position->owner_soid_external];
						$position->mid = $this->_existingPeopleIds[$mid_external];
						$position->type = HM_Orgstructure_OrgstructureModel::TYPE_POSITION;
						
						//--fix for cron
						if(method_exists($position, 'getValues')) {								
							$values = $position->getValues(null, array('owner_soid_external', 'mid_external'));
						} else {					
							$values = $this->getValues($position, null, array('owner_soid_external', 'mid_external'));			
						}					
						
						$isInsertUser = $this->getService('Orgstructure')->insert(
							$values,
							$position->owner_soid
						);
						
						if($isInsertUser){
							$result['users']++;							
							if($log){ $log->log(sprintf($log->msg['ITEM_POSITION_CREATE_SUCCESS'], $values['mid']), 9); }
						} else {							
							if($log){ $log->log(sprintf($log->msg['ITEM_POSITION_CREATE_ERROR'], $values['mid']), $log::ERR); }
						}
					}
				}
			}
		} catch (Exception $e) {						
			$result['error'] = true;
			if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), $log::ERR); }			
		}
		return $result;
    }


    protected function insertNode($node)
    {
        
		$insert = $node['insert'];

        if (false === $this->_setParent($insert)) return true;
        
        
		//--fix for cron
		if(method_exists($insert, 'getValues')) {								
			$values = $insert->getValues(null, array('soid', 'owner_soid_external', 'mid_external')); // skip these attrs
		} else {					
			$values = $this->getValues($insert, null, array('soid', 'owner_soid_external', 'mid_external'));			
		}
        
		$item = $this->getService('Orgstructure')->insert(
            $values,
            $values['owner_soid']
        );

        if ($item) {
            $this->_existingIds[$insert->soid_external] = $item->soid;
            
            foreach($node['childs'] as $child){
                $this->insertNode($child);
            }            
        }
    }
    
    protected function _setParent(&$insert)
    {
    	if (empty($insert->owner_soid_external)) {
    		$insert->owner_soid = 0; // real 1st level
    	} elseif (isset($this->_existingIds[$insert->owner_soid_external])) {
    		$insert->owner_soid = $this->_existingIds[$insert->owner_soid_external];
    	} else {
    		// оторванные ветки - целиком пропускаем
    		return false;
    	}
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