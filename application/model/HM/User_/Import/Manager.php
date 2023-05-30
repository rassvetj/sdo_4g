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

    protected $_notProcessed = array();

    const CACHE_NAME = 'HM_User_Import_Manager';

    private $_loginCount = 0;
    private $_restoredFromCache = false;

    const DEFAULT_PASSWORD = 'pass';

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init($role1c)
    {
        $persons = $this->getService('User')->fetchAll(array('role_1c = ?' => $role1c));

        if (count($persons)) {
            foreach($persons as $person) {
                $this->_existingPeople[$person->MID] = $person;
                if (strlen($person->mid_external)) {
                    $person->mid_external = trim($person->mid_external);
                    $this->_existingPeopleIds[$person->mid_external] = $person->MID;
                }
            }
        }
        $this->_existingUserTags = $this->getService('Tag')->getTagsCache(array_values($this->_existingPeopleIds), $this->getService('TagRef')->getUserType());;
    }

    protected function _needPersonUpdate($person)
    {
        $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$person->mid_external]];

        $values = $person->getValues(null, array('mid_external', 'role', 'isAD', 'tags', 'role_1c', 'status_1c', 'group_id_external'));

        if (count($values)) {
            foreach($values as $key => $value) {
                if($key == 'BirthDate'){
                    $date = strtotime($existingPerson->{$key});
                    $existingPerson->{$key} = date('Y', $date);
                }
                if ($existingPerson->{$key} != $value) {
                    return true;
                }
            }
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

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_insertsPeople,
                 'updates' => $this->_updatesPeople,
                 'deletes' => $this->_deletesPeople,
                 'userTags' => $this->_userTags,
                 'notProcessed' => $this->_notProcessed
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
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items, $role1c)
    {
        $this->_init($role1c);

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                if (empty($item->mid_external)) {
                    $this->_notProcessed[] = $item;
                    continue;
                }
                if (!isset($this->_existingPeopleIds[$item->mid_external])) {
                    $this->_insertsPeople[$item->mid_external] = $item;
                } else {
                    if (isset($this->_updatesPeople[$item->mid_external])) continue;
                    if (!isset($this->_existingPeople[$this->_existingPeopleIds[$item->mid_external]])) continue;

                    $existingPerson = $this->_existingPeople[$this->_existingPeopleIds[$item->mid_external]];

                    if (!empty($item->isTutor)) {
                        $this->getService('User')->assignRole($existingPerson->MID, HM_Role_RoleModelAbstract::ROLE_TUTOR);
                    }
                    unset($item->isTutor);
                    unset($item->Password);
                    if ($this->_needPersonUpdate($item)) {
                        $item->MID = $existingPerson->MID;
                        $this->_updatesPeople[$item->mid_external] = array('source' => $existingPerson, 'destination' => $item);
                    }
                    if (!empty($item->tags) && $this->_needTagsUpdate($item->tags, $existingPerson->MID)) {
                        $this->_userTags[$existingPerson->MID] = explode(',', $item->tags);
                    }

                    //unset($this->_existingPeopleIds[$existingPerson->mid_external]);
                    unset($this->_existingPeople[$existingPerson->MID]);
                }
            }
        }

        if (count($this->_existingPeople)) {
//            $this->_deletesPeople = $this->_existingPeople;
        }

        $this->saveToCache();
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

    public function import()
    {
        $teachers = array();

        if (count($this->_insertsPeople)) {

            foreach($this->_insertsPeople as $id => $insert) {

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
                if($insert->BirthDate != ''){
                    $insert->BirthDate .= '-01-01';
                }
                $user = $this->getService('User')->insert($insert->getValues(null,array('isTutor', 'tags', 'group_id_external')));
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
                    
                    $this->_existingPeopleIds[$user->mid_external] = $user->MID;
                }
            }
        }

        if (count($this->_updatesPeople)) {
            foreach($this->_updatesPeople as $id => $update) {
                $update['destination']->BirthDate = $update['destination']->BirthDate.'-01-01';
                $this->getService('User')->update($update['destination']->getValues(null, array('Password', 'isTutor', 'tags', 'group_id_external')));
            }
        }
        
        if (count($this->_userTags)) {
            $this->_assignTags();
        }
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

}