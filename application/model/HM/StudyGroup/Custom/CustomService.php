<?php
class HM_StudyGroup_Custom_CustomService extends HM_Service_Abstract
{


    public function isGroupUser($groupId, $userId)
    {
        return $this->getOne($this->fetchAll(array(
                    'user_id = ?' => $userId,
                    'group_id = ?' => $groupId
               )));

    }

    public function addUser($groupId, $userId)
    {
        $this->getService('StudyGroupCustom')->insert(array(
            'group_id' => $groupId,
            'user_id' => $userId
        ));

        /* Зачисление пользователя на курсы группы */
        $this->getService('StudyGroupCourse')->assignUser($groupId, $userId);
    }

    public function removeUser($groupId, $userId)
    {
        $this->deleteBy(array(
            'user_id = ?' => $userId,
            'group_id = ?' => $groupId
        ));

        /* Отчисление пользователя с курсов группы */
        $this->getService('StudyGroupCourse')->unassignUser($groupId, $userId);
    }
	
	
	public function getCourseGroups($courseId)
    {   
		if(!$courseId){ return; }
		$select = $this->getSelect();
		$select->from(array('p' => 'People'), array('sgc.group_id') );
		$select->join(array('s' => 'Students'), 's.MID = p.MID', array() );
		$select->join(array('sgc' => 'study_groups_custom'), 'sgc.user_id = s.MID', array() );		
		$select->where('s.CID = ?', $courseId);
		$select->where('p.blocked = ?', 0);
		$select->group(array('sgc.group_id'));
		$res = $select->query()->fetchAll();
		
		if(!$res){ return; }
		
		$groupIDs = array();
		$iter = new ArrayIterator($res);
		foreach($iter as $value) {
			$groupIDs[] = $value['group_id'];
		}		
		
        if (count($groupIDs)) {
            return $this->getService('StudyGroup')->fetchAll(array('group_id IN (?)' => $groupIDs));			
        }		
    }
	
	
	public function getByUserId($user_id){
		return $this->fetchAll(array('user_id = ?' => $user_id))->getList('group_id'); 		
	}
	
	public function getUserGroups($user_id)
	{
		$user_id = (int)$user_id;
		$groups  = new HM_Collection(array(), 'HM_StudyGroup_StudyGroupModel');
		if(empty($user_id)){ return $groups; }
		
		$select = $this->getSelect();
		$select->from(array('g' => 'study_groups'), array('g.*') );
		$select->join(array('sgc' => 'study_groups_custom'), 'sgc.group_id = g.group_id', array() );
		$select->where('sgc.user_id = ?', $user_id);
		$res = $select->query()->fetchAll();
		if(empty($res)){ return $groups; }
		foreach($res as $item){
			$groups->offsetSet($groups->count(), $item);
		}
		return $groups;
	}
	
	

}