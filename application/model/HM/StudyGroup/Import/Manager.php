<?php
class HM_StudyGroup_Import_Manager
{
    protected $_existingStudyGroups = array();
    protected $_existingStudyGroupsIds = array();

    protected $_insertsStudyGroups = array();
    protected $_updatesStudyGroups = array();
    protected $_deletesStudyGroups = array();
    protected $_linkedProgrammsStudyGroups = array();
    protected $_conflictedProgrammsStudyGroups = array();
    
    const CACHE_NAME = 'HM_StudyGroups_Import_Manager';
    
    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $studyGroups = $this->getService('StudyGroup')->fetchAll();

        if (count($studyGroups)) {
            foreach($studyGroups as $studyGroup) {
                $this->_existingStudyGroups[$studyGroup->group_id] = $studyGroup;
                if (strlen($studyGroup->id_external)) {
                    $studyGroup->id_external = trim($studyGroup->id_external);
                    $this->_existingStudyGroupsIds[$studyGroup->id_external] = $studyGroup->group_id;
                }
            }
        }
    }

    protected function _needStudyGroupUpdate($studyGroup)
    {
        $existingStudyGroup = $this->_existingStudyGroups[$this->_existingStudyGroupsIds[$studyGroup->id_external]];
		
		//--fix for cron
		if(method_exists($studyGroup, 'getValues')) {						
			$values = $studyGroup->getValues(null, array('id_external'));
		} else {					
			$values = $this->getValues($studyGroup, null, array('id_external'));			
		}

        if (count($values)) {
            foreach($values as $key => $value) {
                if ($existingStudyGroup->{$key} != $value) {
                    return true;
                }
            }
        }

        return false;
    }
	
	
	protected function _needStudyGroupLinkProgramm($studyGroup, $existingStudyGroup){		
		if(empty($studyGroup->programm_id_external)){ return false; }
			
		$programmService = $this->getService('Programm');		
			
		$newProgramm = $programmService->fetchAll( $programmService->quoteInto('id_external = ?', $studyGroup->programm_id_external) )->current(); //--находим id программы, на которую хотим назначить
		
		if ($newProgramm->programm_id) { 			
			
			$studyProgrammService 	= $this->getService('StudyGroupProgramm');
			$curGroupProgramms = $studyProgrammService->fetchAll($studyProgrammService->quoteInto('group_id = ?', $existingStudyGroup->group_id)); # берем все программы, назначенные на группу.
			$curGroupProgrammIDs 	= array();
			$curGroupProgrammNames  = array(); # названия назначенных программ
			$curGroupProgrammExtIDs = array(); # внешние id назначенных программ
			if(count($curGroupProgramms)){						
				foreach($curGroupProgramms as $i){
					$currentProgramm = $programmService->getById($i->programm_id); //--программа, на которую назначена группа.
					$curGroupProgrammIDs[$i->programm_id] = $i->programm_id;
					$curGroupProgrammNames[$i->programm_id] = $currentProgramm->name;
					$curGroupProgrammExtIDs[$i->programm_id] = $currentProgramm->id_external;
				}
			}
			if(count($curGroupProgrammIDs)){
				if(in_array($newProgramm->programm_id, $curGroupProgrammIDs)){ # группа уже назначена на одну из этих программу.
					return false;
				} else { # группа назначена на другую программу обучения. Вывести в конфликты группу и программу.	
					
					$additional = array(
						'current_programm_names'=> array_filter($curGroupProgrammNames),
						'current_programm_ids' 	=> array_filter($curGroupProgrammExtIDs),					
						'new_programm_name'		=> $newProgramm->name,
						'new_programm_id' 		=> $newProgramm->id_external,
					);	
					$this->_conflictedProgrammsStudyGroups[$studyGroup->id_external] = array('source' => $existingStudyGroup, 'destination' => $studyGroup, 'additional' => $additional );
					return false;
				}
			} else {				
				return true;
			}			
			
		}
		return false;
	}

    public function getInsertsCount()
    {
        return count($this->_insertsStudyGroups);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesStudyGroups);
    }

    public function getDeletesCount()
    {
        return count($this->_deletesStudyGroups);
    }
    
    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_insertsStudyGroups;
    }

    public function getUpdates()
    {
        return $this->_updatesStudyGroups;
    }

    public function getDeletes()
    {
        return $this->_deletesStudyGroups;
    }
	
	public function getConflictedProgrammsStudyGroups()
    {
        return $this->_conflictedProgrammsStudyGroups;
    }
	
	public function getCountConflictedProgrammsStudyGroups()
    {
        return count($this->_conflictedProgrammsStudyGroups);
    }
	
	
	public function getLinkedProgrammsStudyGroups()
    {
		return $this->_linkedProgrammsStudyGroups;
    }
	
	public function getCountLinkedProgrammsStudyGroups()
    {
        return count($this->_linkedProgrammsStudyGroups);
    }
	
	
	

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' 			=> $this->_insertsStudyGroups,
                 'updates' 			=> $this->_updatesStudyGroups,
                 'deletes' 			=> $this->_deletesStudyGroups,
                 'linkedProgramm' 	=> $this->_linkedProgrammsStudyGroups,
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
            $this->_insertsStudyGroups 			= $actions['inserts'];
            $this->_updatesStudyGroups 			= $actions['updates'];
            $this->_deletesStudyGroups 			= $actions['deletes'];
            $this->_linkedProgrammsStudyGroups 	= $actions['linkedProgramm'];
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
			$programmService = $this->getService('Programm');
            foreach($items as $item) {

                if (!isset($this->_existingStudyGroupsIds[$item->id_external])) {
                    $this->_insertsStudyGroups[$item->id_external] = $item;
                } else {
                    if (isset($this->_updatesStudyGroups[$item->id_external])) continue;
                    if (!isset($this->_existingStudyGroups[$this->_existingStudyGroupsIds[$item->id_external]])) continue;

                    $existingStudyGroup = $this->_existingStudyGroups[$this->_existingStudyGroupsIds[$item->id_external]];
                    
                    if ($this->_needStudyGroupUpdate($item)) {
                        $item->group_id = $existingStudyGroup->group_id;
                        $this->_updatesStudyGroups[$item->id_external] = array('source' => $existingStudyGroup, 'destination' => $item);
                    }
					
					if ($this->_needStudyGroupLinkProgramm($item, $existingStudyGroup)) {
                        $item->group_id = $existingStudyGroup->group_id;
						
						$newProgramm = $programmService->fetchAll( $programmService->quoteInto('id_external = ?', $item->programm_id_external) )->current();						
						$this->_linkedProgrammsStudyGroups[$item->id_external] = array('destination' => $item, 'additional' => array('programm_id' => $newProgramm->programm_id));
                    }
					
                    unset($this->_existingStudyGroups[$existingStudyGroup->group_id]);
                }
            }
        }
        if (count($this->_existingStudyGroups)) {
//            $this->_deletesStudyGroups = $this->_existingStudyGroups;
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
				'allInsert' => count($this->_insertsStudyGroups), //--Всего для вставки			
				'insert' => 0, //--по факту вставлено
				'allUpdate' => count($this->_updatesStudyGroups), //--Всего кол-во обновленных
				'update' => 0, //--по факту кол-во обновленных			
			);
			

			$studyGroupService = $this->getService('StudyGroup');
			if (count($this->_insertsStudyGroups)) {

				foreach($this->_insertsStudyGroups as $id => $insert) {
					
					//--fix for cron
					if(method_exists($insert, 'getValues')) {								
						$values = $insert->getValues(null, array());
					} else {					
						$values = $this->getValues($insert, null, array());			
					}
					
					$studyGroup = $studyGroupService->insert($values);				
					if ($studyGroup) {
						$this->_existingStudyGroupsIds[$studyGroup->id_external] = $studyGroup->group_id;
						$this->linkProgramm($studyGroup);
						
						$result['insert']++;						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_SUCCESS'], $values['id_external'], $values['name']), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_ERROR'], $values['id_external'], $values['name']), $log::ERR); }
					}
				}
			}

			if (count($this->_updatesStudyGroups)) {
				foreach($this->_updatesStudyGroups as $id => $update) {
					//--fix for cron
					if(method_exists($update['destination'], 'getValues')) {													
						$values = $update['destination']->getValues(null, array());
					} else {					
						$values = $this->getValues($update['destination'], null, array());			
					}				
					$isUpdate = $studyGroupService->update($values);                
					if($isUpdate){	
						$result['update']++;						
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_SUCCESS'], $values['id_external'], $values['name']), 9); }
					} else {						
						if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_ERROR'], $values['id_external'], $values['name']), $log::ERR); }
					}	
				}
			}
			
			if (count($this->_linkedProgrammsStudyGroups)) {
				$serviceProgramm 	= $this->getService('Programm');
				$serviceStudyGroup 	= $this->getService('StudyGroup');
				foreach($this->_linkedProgrammsStudyGroups as $id => $link) {
					$this->linkProgramm($link['destination']);					
					$users = $serviceStudyGroup->getUsers($link['destination']->group_id);
					if(count($users)){
						foreach($users as $user_id){										
							$serviceProgramm->assignToUser($user_id, $link['additional']['programm_id']);	/* Зачисление пользователя на курсы программы */						
						}
					}					
					
				}
			}
			
			if (count($this->_deletesStudyGroups)) {
				foreach($this->_deletesStudyGroups as $id => $delete) {
					if (strlen($delete->id_external)) {
	//                    $studyGroupService->delete($delete->group_id);
					}
				}
			}
		
		} catch (Exception $e) {	
			//echo $e->getMessage(), "\n";								
			$result['error'] = true;	
			if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), $log::ERR); }
		}
		return $result;
    }
    
    private function linkProgramm($studyGroup) {
        $groupId           = $studyGroup->group_id;
        $programIdExternal = $studyGroup->programm_id_external;
        
        if ($programIdExternal && $groupId) {
            $programmService      = $this->getService('Programm');
            $studyProgrammService = $this->getService('StudyGroupProgramm');
            
            $where = $programmService->quoteInto(
                    array('id_external = ?'), array($programIdExternal)
            );
            $programm = $programmService->fetchAll($where)->current();
            if ($programm->programm_id) {
                if (!in_array($programm->programm_id, $studyProgrammService->getGroupProgrammsIds($groupId))) {
                    $studyProgrammService->insert(array(
                        'group_id' => $groupId,
                        'programm_id' => $programm->programm_id
                    ));
                }
            }
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