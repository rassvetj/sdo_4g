<?php
class HM_User_Import_Manager
{
    protected $_existingPeople = array();
    protected $_existingPeopleIds = array();
    protected $_existingUserTags = array();

    protected $_insertsPeople = array();
    protected $_updatesPeople = array();
    protected $_deletesPeople = array();
    protected $_userTags = array();
    
	protected $_doubleRows = array(); # задвоенные записи
	
	protected $_needUnassignGroupUserIDs = array(); //--список id студентов (НЕ external id), для которых надо открепить от группы.
	protected $_needAssignNewGroupUserIDs = array(); 

    protected $_notProcessed = array();

    const CACHE_NAME = 'HM_User_Import_Manager';

    private $_loginCount = 0;
    private $_restoredFromCache = false;

    const DEFAULT_PASSWORD = 'pass';

    public function getService($name)
    {
        if (Zend_Registry::isRegistered('serviceContainer')) {
            $sc = Zend_Registry::get('serviceContainer');
            return $sc->getService($name);
        }else{
            return null;
        }
    }

    private function _init($role1c)
    {
        /*
        $srv = $this->getService('User');

        $persons = $srv->fetchAll(array('role_1c = ?' => $role1c));

        if (count($persons)) {
            foreach($persons as $person) {
                $this->_existingPeople[$person->MID] = $person;
                if (strlen($person->mid_external)) {
                    $person->mid_external = trim($person->mid_external);
                    $this->_existingPeopleIds[$person->mid_external] = $person->MID;
                }
            }
        }
        */
        //$this->_existingUserTags = $this->getService('Tag')->getTagsCache(array_values($this->_existingPeopleIds), $this->getService('TagRef')->getUserType());; //временно, до переделывания алгоритма. Т.к. запрос на метки слишком большой и SQL его не тянет
    }

    protected function _needPersonUpdate($person)
    {
        $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$person->mid_external]];
		#pr($this->_existingPeople[47854]);
        
		//--fix for cron
		if(method_exists($person, 'getValues')) {						
			$values = $person->getValues(null, array('mid_external', 'role', 'isAD', 'tags', 'role_1c', 'status_1c', 'group_id_external', 'isDO'));
		} else {					
			$values = $this->getValues($person, null, array('mid_external', 'role', 'isAD', 'tags', 'role_1c', 'status_1c', 'group_id_external', 'isDO'));			
		}
					
		#pr($values);
		#pr($existingPerson);
        if (count($values)) {
            foreach($values as $key => $value) {
                if($key == 'BirthDate'){					
					if(!empty($existingPerson->{$key})){											
						$date = strtotime($existingPerson->{$key});						
						$existingPerson->{$key} = date('Y', $date);			
						if(strlen($value) < 4){
							$value = date('Y', $date);	
						}						
					}					
                } 
				
				
				if($key == 'begin_learning'){
					$date		= strtotime($existingPerson->{$key});					
					if($date <= 0){
						$existingPerson->{$key} = '';
					} else {
						$old_date				= date('Y-m-d', $date);	
						$existingPerson->{$key} = $old_date;
					}					
				} 		
				
				if ($existingPerson->{$key} != $value) { 					
					return true;
				}
				
				
            }
        }

        return false;
    }
	
	/**
	 * - return boolean
	 * - Проверка на обновление группы студента при импорте.
	*/
	protected function _needStudentUpdateGroup($MID, $group_id_external){
		$isMemberGroup = $this->getService('StudyGroupUsers')->getUserGroups($MID);
		
		$isNeedUpdate = true;
		if(count($isMemberGroup) > 0){ 						
			//return false; //--пока будем привязывать к группе тех, кто не состоит в группе.
			foreach($isMemberGroup as $i){						
				if(!empty($i['id_external'])){ //--Если группа загруженная из CSV.
					$isNeedUpdate = false;
					break;
				}
				/*
				if($i['id_external'] == $group_id_external){
					$isNeedUpdate = false;
					break;
				}
				*/
			}
		} 
		
		if($isNeedUpdate){ 			
			$isIssetGroup = $this->getService('StudyGroup')->fetchAll(array('id_external = ?' => $group_id_external));	
			if(empty($isIssetGroup) || count($isIssetGroup) < 1) { //--группы нет				
				return false;
			}			
			return true;
		}		
				
		return false;
	}

    protected function _needTagsUpdate($tags, $userId)
    {
        $tags = explode(',', $tags);
        if ((empty($this->_existingUserTags[$userId]) && count($tags)) ||
            count(array_diff($tags, $this->_existingUserTags[$userId]))
        ) {
            return true;
        }
        return false;
    }

    public function getInsertsCount()
    {
        return count($this->_insertsPeople);
    }

    public function getUpdatesCount()
    {
        return count($this->_updatesPeople);
    }

    public function getDeletesCount()
    {
        return count($this->_deletesPeople);
    }

    public function getUserTagsCount()
    {
        return count($this->_userTags);
    }

    public function getNotProcessedCount()
    {
        return count($this->_notProcessed);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount()
            + $this->getUserTagsCount();
    }

    public function getInserts()
    {
        return $this->_insertsPeople;
    }

    public function getUpdates()
    {
        return $this->_updatesPeople;
    }

    public function getDeletes()
    {
        return $this->_deletesPeople;
    }

    public function getUserTags()
    {
        return $this->_userTags;
    }

    public function getNotProcessed()
    {
        return $this->_notProcessed;
    }
	
	public function getDoubleRows()
    {
        return $this->_doubleRows;
    }
	
	public function getDoubleRowsCount()
    {
        return count($this->_doubleRows);
    }
	
	

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_insertsPeople,
                 'updates' => $this->_updatesPeople,
                 'deletes' => $this->_deletesPeople,
                 'userTags' => $this->_userTags,
                 'notProcessed' => $this->_notProcessed,
                 'needUnassignGroupUserIDs' => $this->_needUnassignGroupUserIDs,
                 'needAssignNewGroupUserIDs' => $this->_needAssignNewGroupUserIDs,
                 'doubleRows' => $this->_doubleRows,
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
            $this->_insertsPeople = $actions['inserts'];
            $this->_updatesPeople = $actions['updates'];
            $this->_deletesPeople = $actions['deletes'];
            $this->_userTags = $actions['userTags'];
            $this->_notProcessed = $actions['notProcessed'];
            $this->_needUnassignGroupUserIDs = $actions['needUnassignGroupUserIDs'];
            $this->_needAssignNewGroupUserIDs = $actions['needAssignNewGroupUserIDs'];
            $this->_doubleRows = $actions['doubleRows'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items, $role1c)
    {

		try {

            $this->_init($role1c);

            if ($this->_restoredFromCache) {
                return true;
            }

            if (count($items)) {
//ll('pre Loop');
                $srvUser = $this->getService('User');
                $insertedCount = 0;
                foreach ($items as $item) {
//ll($item->mid_external.' '.$item->LastName.' '.$item->FirstName);
                    if (empty($item->mid_external)) {
                        $this->_notProcessed[] = $item;
                        continue;
                    }

                    $persons = $srvUser->fetchAll(array('mid_external = ?' => $item->mid_external));
                    //if (!isset($this->_existingPeopleIds[$item->mid_external])) {
                    if (!count($persons)) {
//ll('not found');
                        if (isset($this->_insertsPeople[$item->mid_external])) {
                            $this->_doubleRows[$item->mid_external] = $item;
                        }
                        $this->_insertsPeople[$item->mid_external] = $item;
//ll($item->mid_external.' '.$item->LastName.' '.$item->FirstName);
//--------------------------------------------------------------------------------------------
                        $insert = $item;
                        if (!isset($insert->Login)) {
                            $insert->Login = $this->_generateLogin();
                        }

                        if (empty($insert->Password)) {
                            $insert->Password = self::DEFAULT_PASSWORD;
                        }
                        $insert->Password = new Zend_Db_Expr("PASSWORD('".$insert->Password."')");

                        // Если не заполнены поля e-mail, lastname, firstname, то просить заполнить при первом логине
                        if (!strlen($insert->EMail) || (!strlen($insert->LastName) && !strlen($insert->FirstName))) {
                            $insert->need_edit = HM_User_UserModel::NEED_EDIT_AFTER_FIRST_LOGIN;
                        }

                        if($insert->BirthDate != '' && $insert->BirthDate > 1900){ //диапазон периода дат в mssql идет с 1900 года. все, что меньше - выбросит исключение с ошибкой БД
                            $insert->BirthDate .= '-01-01';
                        } else {
                            unset($insert->BirthDate);
                        }


                        //--fix for cron
                        if(method_exists($insert, 'getValues')) {
                            $values = $insert->getValues(null,array('isTutor', 'tags', 'group_id_external', 'isDO'));
                        } else {
                            $values = $this->getValues($insert, null, array('isTutor', 'tags', 'group_id_external', 'isDO'));
                        }

                        try {
                            $user = $srvUser->insert($values);
                            $insertedCount++;

                            if ($user) {
                                if (!empty($insert->isTutor)) {
                                    $srvUser->assignRole($user->MID, HM_Role_RoleModelAbstract::ROLE_TUTOR);
                                }
                                if (!empty($insert->tags) && $this->_needTagsUpdate($insert->tags, $user->MID)) {
                                    $this->_userTags[$user->MID] = explode(',', $insert->tags);
                                }
                                if (!empty($insert->group_id_external)) {
                                    $this->_assignStudyGroups($insert->group_id_external, $user->MID);
                                }
                            }
                        }catch (Exception $e) {
                            $str = $e->getMessage();
                            die ($str);
                        }
//--------------------------------------------------------------------------------------------
                        /*
if ($insertedCount >= 100){
    break;
}*/
                    } else {
//ll('found');


                        if (isset($this->_updatesPeople[$item->mid_external])) {
                            $this->_doubleRows[$item->mid_external] = $item;
                            continue;
                        }

                        //if (!isset($this->_existingPeople[$this->_existingPeopleIds[$item->mid_external]])) continue;

                        //$existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$item->mid_external]];
                        $existingPerson = $persons[0];

                        if (!empty($item->isTutor)) {
                            $srvUser->assignRole($existingPerson->MID, HM_Role_RoleModelAbstract::ROLE_TUTOR);
                        }

                        unset($item->isTutor);
                        unset($item->Password);

                        $isUpdateGroup = false;


                        if (!empty($item->group_id_external) && !empty($existingPerson->MID)) { //--Если это студент

                            if ($this->_needUnassignGroup($existingPerson->MID, $item->group_id_external)) {

                                $isUpdateGroup = true;
                                $this->_needUnassignGroupUserIDs[] = $existingPerson->MID;

                            } elseif ($this->_needAssignNewGroup($existingPerson->MID, $item->group_id_external)) { //--Если группа с CSV есть, но студент не прикреплен ни к одной из групп в СДО.

                                $this->_needAssignNewGroupUserIDs[] = $existingPerson->MID;

                                $isUpdateGroup = true;
                            }
                        }

                        if ($this->_needPersonUpdate($item) || $isUpdateGroup) {
                            $item->MID = $existingPerson->MID;
                            $this->_updatesPeople[$item->mid_external] = array('source' => $existingPerson, 'destination' => $item);
                        }

                        if (!empty($item->tags) && $this->_needTagsUpdate($item->tags, $existingPerson->MID)) {
                            $this->_userTags[$existingPerson->MID] = explode(',', $item->tags);
                        }

                        //unset($this->_existingPeople[$existingPerson->MID]);
                    }
                }
            }
ll('end pre Loop');
            if (count($this->_existingPeople)) {
                //	$this->_deletesPeople = $this->_existingPeople;
            }

            //var_dump(count($this->_updatesPeople));
            $this->saveToCache();

        } catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
    }

    protected function _generateLogin()
    {
        if ($this->_loginCount == 0) {
    		$user = $this->getService('User')->getOne($this->getService('User')->fetchAll($this->getService('User')->quoteInto("Login LIKE '?%'", new Zend_Db_Expr(HM_User_UserService::NEW_LOGIN_PREFIX)), 'MID DESC', 1));
    		if ($user) {
    			$this->_loginCount = (int) substr($user->Login, strlen(HM_User_UserService::NEW_LOGIN_PREFIX));
    		}
        }
        while(true) {
            $login = HM_User_UserService::NEW_LOGIN_PREFIX.str_pad((string) $this->_loginCount, 4, "0", STR_PAD_LEFT);
            $collection = $this->getService('User')->fetchAll($this->getService('User')->quoteInto('Login = ?', $login));
            if (count($collection)) {
                $this->_loginCount++;
                continue;
            } else {
                $this->_loginCount++;
                return $login;
            }
        }
    }

    public function insert_item($insert, $log = false, $logDO = false, $result){
/*
        if (!isset($insert->Login)) {
            $insert->Login = $this->_generateLogin();
        }

        if (empty($insert->Password)) {
            $insert->Password = self::DEFAULT_PASSWORD;
        }
        $insert->Password = new Zend_Db_Expr("PASSWORD('".$insert->Password."')");

        // Если не заполнены поля e-mail, lastname, firstname, то просить заполнить при первом логине
        if (!strlen($insert->EMail) || (!strlen($insert->LastName) && !strlen($insert->FirstName))) {
            $insert->need_edit = HM_User_UserModel::NEED_EDIT_AFTER_FIRST_LOGIN;
        }

        if($insert->BirthDate != '' && $insert->BirthDate > 1900){ //диапазон периода дат в mssql идет с 1900 года. все, что меньше - выбросит исключение с ошибкой БД
            $insert->BirthDate .= '-01-01';
        } else {
            unset($insert->BirthDate);
        }


        //--fix for cron
        if(method_exists($insert, 'getValues')) {
            $values = $insert->getValues(null,array('isTutor', 'tags', 'group_id_external', 'isDO'));
            $doValues = $insert->getValues(array('isDO'), null);
        } else {
            $values = $this->getValues($insert, null, array('isTutor', 'tags', 'group_id_external', 'isDO'));
            $doValues = $this->getValues($insert, array('isDO'), null);
        }

        if($doValues['isDO']){
            $result['allInsertDO']++;
        } else {
            $result['allInsert']++;
        }

        try {
            $user = $this->getService('User')->insert($values);
        }catch (Exception $e) {
            ll('-------------- Exception ------------------');
            lv($values);
            $str = $e->getMessage();
            ll($str);
            die ($str);
        }
*/
/*
        if ($user) {
            if (!empty($insert->isTutor)) {
                $teachers[] = $user->MID;
            }
            if (!empty($insert->tags) && $this->_needTagsUpdate($insert->tags, $user->MID)) {
                $this->_userTags[$user->MID] = explode(',', $insert->tags);
            }
            if (!empty($insert->group_id_external)) {
                $this->_assignStudyGroups($insert->group_id_external, $user->MID);
            }


            if($doValues['isDO']){
                $result['insertDO']++;
                if($logDO) { $logDO->log(sprintf($logDO->msg['ITEM_CREATE_SUCCESS'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), 9);	}
            } else {
                $result['insert']++;
                if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_SUCCESS'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), 9); }
            }

            $this->_existingPeopleIds[$user->mid_external] = $user->MID;
        } else {
            if($doValues['isDO'] && $logDO){
                $logDO->log(sprintf($logDO->msg['ITEM_CREATE_ERROR'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), $logDO::ERR);
            } else {
                if($log){ $log->log(sprintf($log->msg['ITEM_CREATE_ERROR'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), $log::ERR); }
            }

        }
*/
    }
	/**
	 * @param 1 - Zend Log
	 * @param 2 - Zend Log - для стуеднтов ДО
	*/
    public function import($log = false, $logDO = false)
    {
        
		try {
			
			
			if($this->getDoubleRowsCount()){
				foreach($this->_doubleRows as $d){					
					if($d->isAD){					 	
						if($logDO){ $logDO->log(sprintf($logDO->msg['DOUBLE_MID_ROW'], $d->mid_external, $d->LastName.' '.$d->FirstName.' '.$d->Patronymic), 6);	}
					} else {						
						if($log)  { $log->log(sprintf($logDO->msg['DOUBLE_MID_ROW'], $d->mid_external, $d->LastName.' '.$d->FirstName.' '.$d->Patronymic), 6); 		}	
					}																			
				}
			}
			
			
			$result = array(				
				'error' => false,
				
				'allInsert' => 0, //--Всего для вставки			
				'insert' => 0, //--по факту вставлено
				
				'allInsertDO' => 0, //--Всего для вставки с ФДО				
				'insertDO' => 0, //--по факту вставлено с ФДО
				
				'allUpdate' => 0, //--Всего кол-во обновленных
				'update' => 0, //--по факту кол-во обновленных			
				
				'allUpdateDO' => 0, //--Всего кол-во обновленных с ФДО				
				'updateDO' => 0, //--по факту кол-во обновленных с ФДО

				'doubleRows' => $this->getDoubleRowsCount(),
			);
		
			$teachers = array();
			if (count($this->_insertsPeople)) {
/*
				foreach($this->_insertsPeople as $id => $insert) {

                    $this->insert_item($insert, $log, $logDO, $result);

				}
*/
			}

			if (count($this->_updatesPeople)) {
				foreach($this->_updatesPeople as $id => $update) {
					
					if (!empty($update['destination']->group_id_external) && !empty($update['destination']->MID)) {						
						if(
							$this->_unassignGroupWithoutProgress($update['destination']->MID) ||
							in_array($update['destination']->MID, $this->_needAssignNewGroupUserIDs)
							
						){ //--Удаляем студента из группы, но прогресс обучения оставляем.
							$this->_assignStudyGroups($update['destination']->group_id_external, $update['destination']->MID); //--прикрепляем к новой группе.
						} 
					}
					
					if($update['destination']->BirthDate != '' && $update['destination']->BirthDate > 1900){ //диапазон периода дат в mssql идет с 1900 года. все, что меньше - выбросит исключение с ошибкой БД
						$update['destination']->BirthDate = $update['destination']->BirthDate.'-01-01';
					} else {						
						unset($update['destination']->BirthDate);						
					}
					
					
					//--fix for cron
					if(method_exists($update['destination'], 'getValues')) {						
						$values = $update['destination']->getValues(null, array('Password', 'isTutor', 'tags', 'group_id_external', 'isDO'));
						$doValues = $update['destination']->getValues(array('isDO'), null);
					} else {					
						$values = $this->getValues($update['destination'], null, array('Password', 'isTutor', 'tags', 'group_id_external', 'isDO'));			
						$doValues = $this->getValues($update['destination'], array('isDO'), null);									
					}					
					
					if($doValues['isDO']){
						$result['allUpdateDO']++;	
					} else {
						$result['allUpdate']++;
					}
					
					
					$isUpdate = $this->getService('User')->update($values);
					if($isUpdate){
						if($doValues['isDO']){
							$result['updateDO']++;
							if($logDO) { $logDO->log(sprintf($logDO->msg['ITEM_UPDATE_SUCCESS'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), 9); }
						} else {
							$result['update']++;
							if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_SUCCESS'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), 9); }	
						}							
					} else {						
						if($doValues['isDO'] && $logDO){														
							$logDO->log(sprintf($logDO->msg['ITEM_UPDATE_ERROR'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), $logDO::ERR);
						} else {							
							if($log){ $log->log(sprintf($log->msg['ITEM_UPDATE_ERROR'], $values['mid_external'], $values['LastName'].' '.$values['FirstName'].' '.$values['Patronymic']), $log::ERR); }	
						}						
					}
				}
			}
			
			//if (count($this->_userTags)) { //временно, до переделывания алгоритма. Т.к. запрос на метки слишком большой и SQL его не тянет
				//$this->_assignTags();
			//}
			if (count($teachers)) {
				foreach($teachers as $userId ) {
					$this->getService('User')->assignRole($userId, HM_Role_RoleModelAbstract::ROLE_TUTOR);
				}
			}

			if (count($this->_deletesPeople)) {
				foreach($this->_deletesPeople as $id => $delete) {
					if (strlen($delete->mid_external)) {
	//                    $this->getService('User')->update(array('MID' => $delete->MID, 'blocked' => 1));
					}
				}
			}
		
		
		
		
		} catch (Exception $e) {						
			if($logDO){ $logDO->log(sprintf($logDO->msg['EXCEPTION'], $e->getMessage()), $logDO::ERR); }
			if($log){ $log->log(sprintf($log->msg['EXCEPTION'], $e->getMessage()), $log::ERR); }
			
			$result['error'] = true;	
		}
		return $result;
		
    }

    protected function _assignTags()
    {
        foreach ($this->_userTags as $userId => $tags) {
            $tagsCache = (isset($this->_existingUserTags[$userId])) ? $this->_existingUserTags[$userId] : array();
            $this->getService('Tag')->update(array_merge($tagsCache, $tags), $userId,
                $this->getService('TagRef')->getUserType());
        }
    }
    
    protected function _assignStudyGroups($groupIdExternal, $user_id){
        $studyGroupService       = $this->getService('StudyGroup');
        $studyGroupCustomService = $this->getService('StudyGroupCustom');
//        $programmService         = $this->getService('Programm');
        
        $groups = $studyGroupService->fetchAll()->getList('id_external', 'group_id');

        if($groups[$groupIdExternal]){
            if(!$studyGroupCustomService->isGroupUser($groups[$groupIdExternal], $user_id)) {
                $studyGroupCustomService->addUser($groups[$groupIdExternal], $user_id);
            }
        }
    }
	
	/**
	 * открепляем студента от всех его групп, при этом прогрес обучения в этой группе оставляем.
	*/
	protected function _unassignGroupWithoutProgress($user_id){
		
		if(in_array($user_id, $this->_needUnassignGroupUserIDs)){					
			try {
				$dLog = Zend_Db_Table::getDefaultAdapter();		
				$where = $dLog->quoteInto(
					'user_id = ?',
					trim($user_id)
				);
				$dLog->delete('study_groups_custom', $where); //--удаляем запись в study_groups_custom;
				
				#удаляем студента со всех программ.
				$this->getService('ProgrammUser')->unassignAllProgramms($user_id);
				
				return true;				
			} catch (Exception $e) {
				return false;
			}
		}
		return false;
	}
	
	/**
	 * проверяем, надо ли студента откреплять от группы с сохранинием прогресса в этой группе.
	 * return boolean
 	*/
	protected function _needUnassignGroup($user_id, $groupIdExternal){
		$studyGroupService       = $this->getService('StudyGroup');
        $studyGroupCustomService = $this->getService('StudyGroupCustom');
        
        $groups = $studyGroupService->fetchAll()->getList('id_external', 'group_id');
		
        if($groups[$groupIdExternal]){
		
			if(!$studyGroupCustomService->isGroupUser($groups[$groupIdExternal], $user_id)) { //--если студент  не в группе, что указана в CSV файле.                
				return true;
            }
		
        }				
		
		return false;
		
	}
	
	
	/**
	 * проверяет на наличие привязки группы к студенту и наличие группы из CSV в БД
	*/
	protected function _needAssignNewGroup($user_id, $groupIdExternal){
		$studyGroupService       = $this->getService('StudyGroup');
        $studyGroupCustomService = $this->getService('StudyGroupCustom');
        
        $groups = $studyGroupService->fetchAll()->getList('id_external', 'group_id');
		
		if($groups[$groupIdExternal]){
		
			//$isInGroups = $studyGroupCustomService->getOne($studyGroupCustomService->fetchAll(array(
				//'user_id = ?' => $userId				
			//)));		
			$select = $studyGroupCustomService->getSelect();
			$select->from('study_groups_custom');
			$select->where('user_id = ?', $user_id);
			$select->limit(1);
			$res = $select->query()->fetchAll();
			if(count($res) < 1){ //--Если не в группе и группа из CSV есть в БД, то привязываем.
				return true;
			}			
			//$isInGroups = $studyGroupCustomService->fetchAll(array('user_id = ?', $userId));			
			//if(!$isInGroups){ //--Если не в группе и группа из CSV есть в БД, то привязываем.
				//return true;
			//}			
		}	
		return false;		
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