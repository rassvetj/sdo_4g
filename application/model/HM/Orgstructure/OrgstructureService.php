<?php
class HM_Orgstructure_OrgstructureService extends HM_Service_Nested
{
	protected $_userChairs			= array(); //--кафедры пользователей. array ('MID' => array(chair name))
	protected $_userFaculty 		= array(); //--факультеты пользователей. array ('MID' => array(faculty name))
	protected $_userDepartaments 	= array(); //--доступные подразделения, в которых идет поиск подчиненных студентов
	
	const CACHE_NAME = 'HM_Orgstructure_OrgstructureService';
	
    public function delete($id)
    {
        $collection = $this->fetchAll(
            $this->quoteInto('owner_soid = ?', $id)
        );

        if (count($collection)) {
            foreach($collection as $item) {
                $this->delete($item->soid);
            }
        }

        return parent::delete($id);
    }

    public function getTreeContent($parent = 0, $notEncode = true, $current = null, $type = null)
    {
        $tree = array();

        if($type == null){
            $type = array(HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT);
        }


        $collection = $this->fetchAll($this->quoteInto(array('owner_soid = ?', ' AND type IN (?)'), array($parent, $type)), false, null, array('type', 'name'));
        if (count($collection)) {
            foreach($collection as $unit) {
                $tree[] = array(
                    // 'title' => (($notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $unit->name) : $unit->name),
                    'title' => (($notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', _($unit->name) ) : _($unit->name) ),
                    'key' => (string) $unit->soid,
                    'isLazy' => ($parent == 0 ? false : true),
                    'isFolder' => true,
                );

                if ($current == $unit->soid) {
                    $tree[count($tree)-1]['active'] = true;
                }

                if ($parent == 0) {
                    $tree[count($tree)-1]['expand'] = true;
                    $tree[] = $this->getTreeContent($unit->soid, $notEncode);
                }
            }
        }

        return $tree;
    }

    public function getDescendants($parent){

        $descendants = array();
        $collection = $this->fetchAll($this->quoteInto('owner_soid = ?', $parent), 'name');
        if (count($collection)) {
            foreach($collection as $unit) {
                $descendants[] = (int)$unit->soid;
                $descendants = array_merge($descendants, $this->getDescendants($unit->soid));
            }
        }
        array_unique($descendants);
        return $descendants;
    }

    public function getDescendansForMultipleSoids($soids)
    {
        $descendants = array();
        if (count($soids)) {
            foreach ($soids as $soid) {
                // рекурсивно всем вложенным
                $descendants = array_merge($descendants, $this->getService('Orgstructure')->getDescendants($soid));
            }
            $checkedPositions = $this->fetchAll($this->quoteInto(
                array('soid IN (?) AND ', 'type = ?'),
                array($soids, HM_Orgstructure_OrgstructureModel::TYPE_POSITION)
            ));
            foreach ($checkedPositions as $position) {
                $descendants[] = (int)$position->soid;
            }
        }
        return $descendants;
    }

    public function getPositionsCodes()
    {
        $positions = array();
        $q = $this->getSelect()
            ->from(
                array('so' => 'structure_of_organ'),
                array(
                    'code' => 'DISTINCT(so.code)',
                    'so.name'
                )
            )
            ->where('so.code IS NOT NULL');
        //  print $q;exit;
        $res = $q->query()->fetchAll();
        foreach($res as $item) {
            $positions[$item['code']] = $item['name'];
        }
        return $positions;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('подразделение plural', '%s подразделение', $count), $count);
    }

    public function pluralFormPositionsCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('должность plural', '%s должность', $count), $count);
    }

     /**
     * Возвращает массив с ID всех дочерних элементов структуры для указанного родительского
     * @param int $soid ID родительского элемента
     * @return array
     */
    public function getChildIDs($soid)
    {
        $arChilds = array();
        $items = $this->fetchAll();
        $arWork = $items->getList('soid','owner_soid');

        foreach ( $arWork as $id=>$parentID) {

            if (is_array($parentID)) {
                $parentID = $parentID['id'];
            }

            if (!is_array($arWork[$id])) {
                $arWork[$id] = array( 'id'        => $id,
                                      'childrens' => array()
                                    );
            }

            if (!is_array($arWork[$parentID])) {
                $arWork[$parentID] = array( 'id'        => $parentID,
                                            'childrens' => array()
                                    );
            }

            $arWork[$parentID]['childrens'][] = &$arWork[$id];
        }

        $needElement = (isset($arWork[$soid]))? $arWork[$soid]['childrens'] : array();

        array_walk_recursive($needElement, array($this,'walkRecursiveFunction'), &$arChilds);

        return $arChilds;
    }

    public function walkRecursiveFunction($item,$key,$arChilds)
    {
       $arChilds[] = $item;
    }

    
    /**
     * Делает то же самое, что inserUser 
     * 
     */
    public function assignUser($userId, $ownerSoid, $positionName)
    {
        $owner = $this->getOne($this->find($ownerSoid));
        
        $positionPrev = $this->getOne(
            $this->fetchAll(
                $this->getService('Orgstructure')->quoteInto('mid = ?', $userId)
        ));
        
        if (empty($ownerSoid) || empty($owner)) $ownerSoid = 0; 
        if (empty($positionName)) $positionName = _('Сотрудник'); 
        
        // если ничего не изменилось - ничего не делаем
        if (($positionPrev->name != $positionName) || ($ownerSoid != $positionPrev->owner_soid)) {
        
            if ($positionPrev) {
                // если это перемещение - удаляем и воссоздаём в другом месте
                $this->getService('Orgstructure')->deleteBy(array('soid = ?' => $positionPrev->soid));
                $data = $positionPrev->getValues();
                unset($data['soid']);
            } else {
                // дефолтные параметры для нового сотрудника
                $data = array(
                    'name' => $positionName,
                    'mid' => $userId,
                    'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'owner_soid' => $ownerSoid
                );
            }
        
            $data['name'] = $positionName;
            $data['owner_soid'] = $ownerSoid;
            
            $this->insert($data, $ownerSoid);
        }
         
    }
    
    
    /**
     * DEPRECATED!
     *      */
    public function insertUser($userId, $positionId, $positionName = null)
    {

        /**
         * Добавил вариант что $positionId может быть не задано.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        $position = false;
        if($positionId == 0){
            $this->updateWhere(array('mid' => 0), $this->quoteInto('mid = ?', $userId));
            $position = $this->insert(
                array(
                    'name' => !$positionName ? _('Сотрудник') : $positionName,
                    'mid' => $userId,
                    'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'owner_soid' => !$positionId ? 0 : $positionId
                ));
            return $position;
        }
        $unit = $this->getOne($this->find($positionId));
        $this->updateWhere(array('mid' => 0), $this->quoteInto('mid = ?', $userId));
        if ($unit) {
            if ($unit->type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
                $position = $this->insert(
                    array(
                        'name' => !$positionName ? _('Сотрудник') : $positionName,
                        'mid' => $userId,
                        'type' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                        'owner_soid' => !$positionId ? 0 : $positionId
                    ),
                    $unit->soid
                );
            } else {
                $position = $this->updateNode(array('name' => !$positionName ? _('Сотрудник') : $positionName,'mid' => $userId), $positionId, $unit->owner_soid);
            }
        }

        return $position;
    }

    // DEPRECATED!!! 
    public function updateEmployees($soid)
    {
        $orgUnit = $this->getOne($this->find($soid));

        if($orgUnit && $orgUnit->is_manager == HM_Orgstructure_OrgstructureModel::SUPERVISOR){
            $employees = $this->fetchAll(array('owner_soid = ?' => $orgUnit->owner_soid, 'soid != ?' => $soid, 'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION));

            $list = $employees->getList('soid', 'mid');

            if(count($list)){
                $this->getService('User')->updateWhere(array('head_mid' => $orgUnit->mid), array('MID IN (?)' => array_values($list)));
                $this->getService('Orgstructure')->updateWhere(array('is_manager' => 0), array('soid IN (?)' => array_keys($list)));
            }
            $this->getService('Supervisor')->assign($orgUnit->mid);
        }else{
            if($orgUnit->mid > 0){
                $this->getService('Employee')->assign($orgUnit->mid);
            }
        }
    }




    public function update($data){
        $res = parent::update($data);
        //$this->updateEmployees($res->soid);
        return $res;
    }

    public function insert($data, $objectiveNodeId = 0, $position = HM_Db_Table_NestedSet::LAST_CHILD){
        $res = parent::insert($data, $objectiveNodeId, $position);
        //$this->updateEmployees($res->soid);
        return $res;
    }

    public function getDefaultParent()
    {
        // эта логика должна измениться, когда появится настройка области ответственности супервайзера
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)) {
            $position = $this->fetchAllDependence('Parent', array('mid = ?' => $this->getService('User')->getCurrentUserId()));
            if (count($position) && count($position->current()->parent) ) {
                return $position->current()->parent->current();
            }
        }

        $root = new stdClass();
        $headUnitTitle = $this->getService('Option')->getOption('headStructureUnitName');
        if (!strlen($headUnitTitle)) {
            $headUnitTitle = _(HM_Orgstructure_OrgstructureModel::DEFAULT_HEAD_STRUCTURE_ITEM_TITLE);
        }

        $root->soid = 0;
        $root->name = $headUnitTitle;

        return $root;
    }

    static public function getIconClass($type, $isManager)
    {
        $return = 'type-' . $type;
        if ($isManager) {
            $return .= '-manager';
        }
        return $return;
    }

    static public function getIconTitle($type, $isManager)
    {
        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            return _('Подразделение');
        } elseif ($isManager) {
            return _('Руководитель подразделения');
        }
        return _('Сотрудник');
    }
	
	
	/**
	* проверяем принадлежность пользователя к разделу оргструктуры по ее названию. 	
	* param - string - название раздела. Т.к. у них нет уникальных id
	*/
	public function isInThisPart($partName = false){
		if(!$partName){
			return false;
		}
		//--рекурсивно ищем нужный раздел идя от пользователя. Причем user может быть в нескольких разделах. НАдо все их проверить.
		$position = $this->fetchAllDependence('Parent', array('mid = ?' => $this->getService('User')->getCurrentUserId()));
		if (count($position) && count($position->current()->parent) ) {
			$curPart = $position->current()->parent->current();
		}
		
		if(strstr(trim($curPart->name), trim($partName)) !== false){ 
			return true;
		}
		
		return $this->getParent($curPart, $partName);			
	}
	
	
	/**
	* рекурсивно ищет подстроку в названиии радительского раздела
	* return boolean
	*/
	public function getParent($positionCur, $partName){		
		
		if(!$positionCur){
			return false;
		}		
		
		if(strstr(trim($positionCur->name), trim($partName)) !== false){ 
			return true;
		}
		
		$positionPrev = $this->getOne(
            $this->fetchAll(
                $this->getService('Orgstructure')->quoteInto('soid = ?', $positionCur->owner_soid)
        ));
		
		if(!$positionPrev){
			return false;
		}
		
		return $this->getParent($positionPrev, 'Преподаватели');
	}
	
	/**
	* Возвращает Название родителя, который идет после указанного.
	* $partName - имя родителя, до которого производится поиск
	* return string
	*/
	public function getChildOnParentName($positionCur, $partName, $curName = false){		
		
		if(!$positionCur){
			return false;
		}
		if(strstr(mb_strtolower(trim($positionCur->name)), mb_strtolower(trim($partName))) !== false){ 
			return $curName;
		}		
		$positionPrev = $this->getOne(
            $this->fetchAll(
                $this->getService('Orgstructure')->quoteInto('soid = ?', $positionCur->owner_soid)
        ));		
		if(!$positionPrev){
			return false;
		}		
		return $this->getChildOnParentName($positionPrev, 'Преподаватели', $positionCur->name);
	}
	
	/**
	 * возвращает факультет. Из расчета, что факультет - это первый предок разделов 'Преподаватели' или 'Студенты'
	 * @return array
	*/
	public function getFaculty($user_id){
		$curPart = false;
		$position = $this->fetchAllDependence('Parent', array('mid = ?' => $user_id));
		if (count($position) && count($position->current()->parent) ) {
			$curPart = $position->current()->parent->current();
		}		
		$partName = 'Преподаватели';
		$parentName = $this->getChildOnParentName($curPart, $partName);
		return $parentName;		
	}
	
	/**
	 * кафедра
	*/
	public function getDepartmentName($MID){
		if(!$MID) {
			return false;
		}
		$position = $this->fetchAllDependence('Parent', array('mid = ?' => $MID));
		if (count($position) && count($position->current()->parent) ) {
			$curPart = $position->current()->parent->current();
			return $curPart->name;
		}
		return false;
	}
	
	
	/**
	 * получаем всех студентов, учащихся на ФДО и ФДО_Б
	*/
	public function getStudentsDO(){
		$midIDExternal = array('ST_2139', 'ST_2132');
		$parents = $this->fetchAll($this->quoteInto('soid_external IN (?)', $midIDExternal));		
		$studentsDO = array(0); //--небоьшой фикс, если в оргструктуре не будет ни одного стуюента, то и ни одной сессии не выведется. А без 0 будут выводится все стуюенты без условия.
		if($parents){
			foreach($parents as $p){				
				if(!empty($p->soid)){
					$orgIDs = $this->getChildIDs($p->soid);
					$res = $this->fetchAll($this->quoteInto('soid IN (?) AND mid > 0', $orgIDs));						
					foreach($res as $i){
						$studentsDO[$i->mid] = $i->mid;
					}					
				}				
			}
		}				
		return $studentsDO;
	}
	
	
	/**
	 * возвращает всех подчиненных пользователей.
	 * return array
	*/
	public function getAllSubUnit(){
		$position = $this->fetchAllDependence('Parent', array('mid = ?' => $this->getService('User')->getCurrentUserId()));
		if (count($position) && count($position->current()->parent) ) {
			$curPart = $position->current()->parent->current();			
		} else {
			return false;
		}
		
		if(empty($curPart->soid_external)){
			return false;
		}
		
		$tt = explode('_', $curPart->soid_external);
		if(!isset($tt[1])){
			return false;
		}
		
		$userDepId = 'ST_'.$tt[1]; //--код департамента для студентов
		$u_collection = $this->getService('Orgstructure')->fetchAllDependence('Parent', array('soid_external = ?' => $userDepId));
		$u_position = $u_collection->current();
		$department = $u_position; //--переопределяем отдел для студентов.
	
		$select = $this->getSelect();
        $select->from(array('p' => 'People'),
			array(
				'p.MID',				
			)
		);
	
		$select->join(array('org' => 'structure_of_organ'),
            'org.mid = p.MID',
            array()
		);
		
		$select->where('org.lft > ?', $department->lft);
        $select->where('org.rgt < ?', $department->rgt);
        $select->where('org.mid != 0');
		
		$res = $select->query()->fetchAll();
		
		if(empty($res)){
			return false;
		}
		
		$list = array();
		foreach($res as $i){
			$list[$i['MID']] = $i['MID'];
		}
		
		return $list;		
	}
	
	/**
	 * подразделение студунтов за которым закреплен наблюдатель	 
	*/
	public function getUsersDepartaments($supervisor_id){
		if(!$supervisor_id) { return false; }		
		$departmentList = false;
		if (count($collection = $this->fetchAllDependence('Parent', array('mid = ?' => $supervisor_id)))) {						
			foreach($collection as $c){
				if(count($c->parent)){
					$departament = $c->parent->current();											
					if($departament->soid_external){
						$userDepId = str_replace(HM_Orgstructure_OrgstructureModel::PREFIX_TEACHER, HM_Orgstructure_OrgstructureModel::PREFIX_STUDENT, $departament->soid_external); //--код департамента для студентов						
						$u_collection = $this->fetchAllDependence('Parent', array('soid_external = ?' => $userDepId));						
						if(count($u_collection->current()) && $u_collection->current()->soid){							
							$departmentList[] = $u_collection->current();					
						}						
					}
				}				
			}				
		}		
		return $departmentList;
	}
	
	
	/**
	 * назначен ли наблюдатель в оргструктуре.
	*/
	public function isAssignedOnOrgstructure(){
		if (count($collection = $this->fetchAllDependence('Parent', array('mid = ?' => $this->getService('User')->getCurrentUserId())))) {
			$position = $collection->current(); //--департамент препода
			if (count($position->parent)) {
				$department = $position->parent->current(); 
			}
		}
		if(!$department){
			return false;
		}
		return true;	
	}
	
	/**
	 * список доступных студентов и их подразделение в оргструктуре.
	 * @return array: key = mid, value = department
	*/
	public function getUserDepartamentList($supervisor_id){
		if(!$supervisor_id){ return false; }
		
		if(empty($this->_userDepartaments)){
			$this->restoreFromCache();
		}
		
		$userDepartamentCache = $this->_userDepartaments[$supervisor_id];
		if(isset($userDepartamentCache)){ return $userDepartamentCache; }
		
		$departaments = $this->getUsersDepartaments($supervisor_id);
		if(!count($departaments)){ return false; }
		
		$select = $this->getSelect();
		$select->from(array('so' => 'structure_of_organ'),
			array(
				'user_id' 			=> 'so.mid',
				'user_position' 	=> 'so.name',
				'user_department' 	=> 'so2.name',
			)
		);
        $select->join(array('so2' => 'structure_of_organ'), 'so2.soid = so.owner_soid', array());	
		$select->join(array('p' => 'People'), 'p.MID = so.mid', array());			
		$select->where('so.mid IS NOT NULL');
		
		
		foreach($departaments as $c){				
			if(isset($c->lft) && isset($c->rgt) && !empty($c->lft) && !empty($c->rgt)){
				$criteria[] =	$this->quoteInto(
									array('(so.lft > ? ', ' AND so.rgt < ?)'),
									array($c->lft, $c->rgt)
								);	
			}
		}				
		$select->where($this->quoteInto(implode(' OR ', $criteria)));
		$select->where('so.mid > 0');
		$res = $select->query()->fetchAll();		
		if(!$res){ return false; }
		
		$userList = array();
		$iterator = new ArrayIterator($res);
		foreach($iterator as $user){
			$userList[$user['user_id']] = array( //--если студент принадлежит к несколькои подразделениям, то берем одно. Т.к. маловероятно, что будет более одного.
				'position' 		=> $user['user_position'], 
				'department' 	=> $user['user_department'], 
			);
		}
		$this->_userDepartaments[$supervisor_id] = $userList;
		$this->saveToCache();
		
		if(!count($userList)){ return false; }		
		return $userList;		
	}
	
	
	/**
	 * подразделения пользователей по их ID
	*/
	public function getUserDepartamentListByUserId($userIDs){
		if(!$userIDs){ return false; }
		$userIDs = (array) $userIDs;
		if(!count($userIDs)){ return false; }
		
		//--в кэш не сохраняем, т.к. постоянно будет менятся группа, а следовательно и набор данных ID
		//if(empty($this->_userDepartaments)){
			//$this->restoreFromCache();
		//}
		//$supervisor_id = $this->getService('User')->getCurrentUserId();
		//$userDepartamentCache = $this->_userDepartaments[$supervisor_id]; //--ф-ция getUserDepartamentList использует именно такой формат: [supervisor_id] => array(Users positions)
		//if(isset($userDepartamentCache)){ return $userDepartamentCache; }
		
		
		$select = $this->getSelect();
		$select->from(array('so' => 'structure_of_organ'),
			array(
				'user_id' 			=> 'so.mid',
				'user_position' 	=> 'so.name',
				'user_department' 	=> 'so2.name',
			)
		);
        $select->join(array('so2' => 'structure_of_organ'), 'so2.soid = so.owner_soid', array());	
		$select->join(array('p' => 'People'), 'p.MID = so.mid', array());			
		$select->where('so.mid IS NOT NULL');
		$select->where($this->quoteInto('p.MID IN (?)', $userIDs));
		$select->where('so.mid > 0');
		
		$res = $select->query()->fetchAll();		
		if(!$res){ return false; }
		
		$userList = array();
		$iterator = new ArrayIterator($res);
		foreach($iterator as $user){
			$userList[$user['user_id']] = array( //--если студент принадлежит к несколькои подразделениям, то берем одно. Т.к. маловероятно, что будет более одного.
				'position' 		=> $user['user_position'], 
				'department' 	=> $user['user_department'], 
			);
		}
		//$this->_userDepartaments[$supervisor_id] = $userList;
		//$this->saveToCache();
		
		if(!count($userList)){ return false; }		
		return $userList;	
	}
	
	/**
	 * получаем кафедры, на которые назначен пользователь.
	 * @return array: string
	*/
	public function getUserChair($user_id){
		if(!$user_id){ return false; }
		
		if(empty($this->_userChairs)){
			$this->restoreFromCache();
		}
		$cacheChair = $this->_userChairs[$user_id];
		
		if(isset($cacheChair)){ return $cacheChair; }
			
		$chairs = array();		
		if (count($collection = $this->fetchAllDependence('Parent', array('mid = ?' => $user_id)))) {
			foreach($collection as $d){
				if(isset($d->parent->current()->soid)){
					$chairs[$d->parent->current()->soid] = $d->parent->current()->name;				
				}
			}
		}	
		
		$this->_userChairs[$user_id] = $chairs;
		$this->saveToCache();
		
		if(!count($chairs)) { return false; }			
		return $chairs;
	}
	
	
	public function getUserFaculty($user_id){		
		if(!$user_id){ return false; }
		
		if(empty($this->_userFaculty)){
			$this->restoreFromCache();
		}
		$cacheFaculty = $this->_userFaculty[$user_id];
		if(isset($cacheFaculty)){ return $cacheFaculty; }

		$chairs = $this->getUserChair($user_id);
		
		if(!count($chairs)){ return false; }
		$chairIDs = array_keys($chairs);
		$faculties = array(); 
		if (count($collection = $this->fetchAllDependence('Parent', $this->quoteInto('soid IN (?)', $chairIDs)))) {
			foreach($collection as $c){
				if(is_object($c->parent)){
					if(is_object($c->parent->current())){
						if(isset($c->parent->current()->soid)){
							$faculties[$c->parent->current()->soid] = $c->parent->current()->name;
						}
					}
				}
			}			
		}		
		$this->_userFaculty[$user_id] = $faculties;
		$this->saveToCache();
		
		if(!count($faculties)){
			return false;
		}
		return $faculties;
	}
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'userChairs'  		=> $this->_userChairs,                 
                 'userFaculty'  	=> $this->_userFaculty,                 
                 'userDepartaments' => $this->_userDepartaments,                 
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
            $this->_userChairs   		= $actions['userChairs'];            
            $this->_userFaculty   		= $actions['userFaculty'];            
            $this->_userDepartaments   	= $actions['userDepartaments'];            
            return true;
        }
        return false;
    }
	
}