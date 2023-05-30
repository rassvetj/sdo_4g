<?php
class HM_StudyGroup_StudyGroupService extends HM_Service_Abstract
{
	protected $_subjectGroupList 	= array(); //--ключ - subject_id, значение - список названий групп. использцется в разделе "Сессии" и "Учебные курсы".
	protected $_groupList 			= array();
	const CACHE_NAME = 'HM_StudyGroup_StudyGroupService';


    public function create($name, $type)
    {
        $group = $this->insert(array(
            'name' => $name,
            'type' => $type
        ));

        return $group;
    }

    public function delete($groupId)
    {
        $group = $this->getOne($this->getService('StudyGroup')->find($groupId));

        /* Отменяем курсы которые были назначенны группе */
        $this->getService('StudyGroupCourse')->removeGroupCourses($groupId);
        /* Отменяем занятия которые были назначенны группе */
        $this->getService('StudyGroupCourse')->removeGroupLessons($groupId);
        /* Отменяем программы которые были назначенны группе */
        $this->getService('StudyGroupProgramm')->removeGroupProgramms($groupId);


        if ($group->type == HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM) {
            $this->getService('StudyGroupCustom')->deleteBy($this->quoteInto('group_id = ?',$groupId));
        } else {
            $this->getService('StudyGroupAuto')->deleteBy($this->quoteInto('group_id = ?',$groupId));
        }

        return parent::delete($groupId);
    }

    public function getDepartments($groupId)
    {
        $departments = array();
        $refs = $this->getService('StudyGroupAuto')->fetchAll(array(
            'group_id = ?' => $groupId
        ));
        foreach($refs as $ref) {
            $departments []= $ref->department_id;
        }
        return $departments;
    }

    public function getPositions($groupId)
    {
        $positions = array();
        $refs = $this->getService('StudyGroupAuto')->fetchAll(array(
            'group_id = ?' => $groupId
        ));
        foreach($refs as $ref) {
            $positions []= $ref->position_code;
        }
        return $positions;
    }

    public function getUsers($groupId)
    {
        $users = array();
        $refs = $this->getService('StudyGroupUsers')->fetchAll(array(
            'group_id = ?' => $groupId
        ));
        foreach($refs as $ref) {
            $users []= $ref->user_id;
        }
        return $users;
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('группа plural_1', '%s группа', $count), $count);
    }

    public function getById($id)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('group_id = ?', $id)));
    }

    public function getByName($name)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('name = ?', $name)));
    }
	
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'subjectGroupList' => $this->_subjectGroupList,                                                  
                 'groupList' 		=> $this->_groupList,                                                  
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
            $this->_subjectGroupList 	= $actions['subjectGroupList'];                                  
            $this->_groupList 			= $actions['groupList'];                                  
            return true;
        }
        return false;
    }
	
	/**
	 * список всех групп назначенных студентов на сессии
	 * @return array(CID => groups name (string))
	*/
	public function getSubjectGroupList(){		
		if(count($this->_subjectGroupList)){						
			return $this->_subjectGroupList;
		}
		$this->restoreFromCache();
		if(count($this->_subjectGroupList)){			
			return $this->_subjectGroupList;
		}
		
		$select = $this->getSelect();        
		$select->from(array('st' => 'Students'),
				array(
					'CID' => 'st.CID',
					'name' => 'sg.name',
				)
		); 
		$select->join(
            array('sgu' => 'study_groups_users'),
            'sgu.user_id = st.MID',
            array()
        );
		$select->join(
            array('sg' => 'study_groups'),
            'sg.group_id = sgu.group_id',
            array()
        );		
		$select->where('st.CID > 0');
		$select->where('st.MID > 0');
		$select->group(array('st.CID', 'sg.name'));
		$res = $select->query()->fetchAll();
		$groups = array();
		foreach($res as $g){
			if(isset($groups[$g['CID']])){
				$groups[$g['CID']] .= ', '.$g['name'];	
			} else {
				$groups[$g['CID']] = $g['name'];	
			}
			
		}		
		$this->_subjectGroupList = $groups;		
		$this->saveToCache();		
		return $this->_subjectGroupList;
	}
	
	/**
	 * формирует список групп по указанным пользователям
	 * @return array key = group_id, value = 'group_name'
	 */
	public function getGroupListOnUserIDs($userIDs = false){
		# неверно работает. переделать.
		/*
		if(empty($this->_groupList)){
			$this->restoreFromCache();
		}		
		if(!empty($this->_groupList)){
			return $this->_groupList;
		}
		*/
		
		
		//-если есть кэш, вернуть, иначе:
		$select = $this->getSelect();		
		if($userIDs){ 
			$userIDs = (array) $userIDs;			
			$select->where($this->quoteInto('sgu.user_id IN (?)', $userIDs));	
		}		
		
		$select->from(array('sg' => 'study_groups'),
			array(				
				'group_id'		=> 'sg.group_id',				
				'group_name'	=> 'sg.name',
			)
		);
		$select->join(array('sgu' => 'study_groups_users'), 'sg.group_id = sgu.group_id', array() );
		$select->order('sg.name ASC');
		$res = $select->query()->fetchAll();
		if(!count($res)) { return false; }
		$usersGroups = array();		
		foreach($res as $r){
			$usersGroups[$r['group_id']] = $r['group_name'];
		}
		
		$this->_groupList = $usersGroups;
		$this->saveToCache();
		
		return $usersGroups;		
	}
	
	
	/**
	 * получение списка групп через область ответственности пользователя
	**/
	public function getGroupsByResponsibility($supervisor_id){
		$responsibilityType = $this->getService('SupervisorResponsibility')->getResponsibilityType($supervisor_id);
		
		if(!$responsibilityType) { return false; }
		 
		$responsibilities = $this->getService('SupervisorResponsibility')->fetchAll($this->quoteInto(array('user_id = ?'), array($supervisor_id) ))->getList('responsibility_id', 'responsibility_id');
		if(!$responsibilities) { return false; }
		
        $groups = array();
		if( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::SUBJECT_RESPONSIBILITY_TYPE ){
			
			$select = $this->getService('Subject')->getSelect();			
			$select->from(array('s' => 'subjects'), array( 
                    'group_id' => 'sg.group_id',
                    'name' => 'sg.name',
                )
            );
			$select->join(array('st' => 'Students'), 'st.CID = s.subid', array());
			$select->join(array('sgc' => 'study_groups_custom'), 'sgc.user_id = st.MID', array());
			$select->join(array('sg' => 'study_groups'), 'sg.group_id = sgc.group_id', array());
			$select->where($this->quoteInto('s.subid IN (?)', $responsibilities));
			$select->where('st.MID > 0');
			$select->group(array('sg.group_id', 'sg.name'));			
			$res = $select->query()->fetchAll();
			if(!$res) { return false; }
			foreach($res as $g){
				$groups[$g['group_id']] = $g['name'];
			}
		} elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::GROUP_RESPONSIBILITY_TYPE) {
			$res = $this->fetchAll($this->quoteInto('group_id IN (?)', $responsibilities));
			if(!$res){ return false; }
			foreach($res as $g){
				$groups[$g->group_id] = $g->name;
			}
		} elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::PROGRAMM_RESPONSIBILITY_TYPE) {
			$courses = $this->getService('StudyGroupProgramm')->fetchAll($this->quoteInto('programm_id IN (?)', $responsibilities));
			$res = $this->fetchAll($this->quoteInto('group_id IN (?)', $courses->getList('group_id') ));
			if(!$res){ return false; } 
			foreach($res as $g){
				$groups[$g->group_id] = $g->name;
			}
		} elseif( $responsibilityType == HM_Role_Supervisor_Responsibility_ResponsibilityModel::STUDENT_RESPONSIBILITY_TYPE) {			
			$res = $this->getService('StudyGroupUsers')->getUsersGroups($responsibilities);
			if(!$res){ return false; }
			foreach($res as $g){
				$groups[$g['group_id']] = $g['name'];
			}
		}	
		asort($groups);
		return $groups;            
	}

	/**
	 * группа выпускная
	 * @return bool
	*/
	public function isGraduate($user_id){
		$select = $this->getSelect();        
		$select->from(array('g' => 'study_groups'), array('group_id' => 'g.group_id')); 
		$select->join(array('sgu' => 'study_groups_users'), 'sgu.group_id = g.group_id', array());
		$select->where('sgu.user_id = ?', intval($user_id));
		$select->where('g.year_graduated = ?', date('Y'));
		$res = $select->query()->fetchAll();
		if($res){ return true; }
		return false;		
	}
	
	/**
	 * Список всех групп всех студентов
	*/
	public function getUsersGroupList(){		
		$select = $this->getSelect();		
		
		$select->from(array('sg' => 'study_groups'),
			array(				
				'user_id'		=> 'sgu.user_id',				
				'group_name'	=> new Zend_Db_Expr('GROUP_CONCAT(DISTINCT sg.name)'),
			)
		);
		$select->join(array('sgu' => 'study_groups_users'), 'sg.group_id = sgu.group_id', array());	
		$select->where('sgu.user_id > 0');
		$select->group(array('sgu.user_id'));
		$res = $select->query()->fetchAll();
		if(!count($res)) { return false; }
		$data = array();		
		foreach($res as $r){
			$data[$r['user_id']] = $r['group_name'];
		}
		return $data;
	}
	
	public function getGroupIDsByName($name){
		if(empty($name)){ return false; }
		
		$select = $this->getSelect();
		$select->from('study_groups', array('group_id'));		 
		$select->where($this->quoteInto("name LIKE ?", '%'.$name.'%'));
		$res = $select->query()->fetchAll();
		if(empty($res)){ return false; }
		$data = array();
		foreach($res as $i){
			$data[$i['group_id']] = $i['group_id'];
		}
		return $data;
	}
	
	
	public function getGroupList(){		
		return $this->fetchAll(array(), array('name ASC'))->getList('group_id', 'name');		
	}
	
	
	public function getByCodes($codes = array())
	{
		if(empty($codes)){ return false; }
		if(!is_array($codes)){
			$codes = array($codes);
		}
		return $this->fetchAll($this->quoteInto('id_external IN (?)',  $codes));
	}
	
	public function getByCode($code)
	{
		if(empty($code)){ return false; }
		return $this->getOne($this->getByCodes($code));
	}
	
	public function getNameByCode($code)
	{
		if(empty($code)){ return false; }
		$group = $this->getByCode($code);
		if(!$group){ return false; }
		return $group->name;
	}
	
	
	public function getBySubject($subjectId = false)
	{
		if(empty($subjectId)){ return false; }
		
		$select = $this->getSelect();
		$select->from(array('sg'  => 'study_groups'), array('sg.*'));
		$select->join(array('sgp' => 'study_groups_programms'), 'sgp.group_id   = sg.group_id',     array());
		$select->join(array('pe'  => 'programm_events'),        'pe.programm_id = sgp.programm_id', array());
		$select->where($this->quoteInto('pe.item_id = ?', $subjectId));
		$select->where($this->quoteInto('pe.type    = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
		$res = $select->query()->fetchAll();
		
		$collection = new HM_Collection(array(), 'HM_StudyGroup_StudyGroupModel');
		foreach($res as $item){
			$collection[count($collection)] = new HM_StudyGroup_StudyGroupModel($item);	
		}
		
		return $collection;
	}
	
}
