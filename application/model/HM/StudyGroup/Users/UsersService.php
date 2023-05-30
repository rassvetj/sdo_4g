<?php
class HM_StudyGroup_Users_UsersService extends HM_Service_Abstract
{


    public function getUserGroups($userId)
    {
        $select = $this->getSelect();
        $select->from(
            array('sg' => 'study_groups'),
            array('sg.*')
        )
            ->joinLeft(
                array('g' => 'study_groups_users'),
                'g.group_id = sg.group_id',
                array()
            )
            ->where('g.user_id = ?', $userId);

        return $select->query()->fetchAll();
    }

    public function getUsersOnCourse($groupId,$courseId)
    {

        $select = $this->getSelect();
        $select->from(
            array('sgu' => 'study_groups_users'),
            array('sgu.*')
        )
            ->joinLeft(
                array('sgc' => 'study_groups_courses'),
                'sgc.group_id = sgu.group_id',
                array()
            )
            ->where('sgc.course_id = ?', $courseId);

        if ($groupId > 0) {
            $select->where('sgc.group_id = ?', $groupId);
        }

        return $select->query()->fetchAll();

    }
	
	
	public function getUsersOnCourseCustom($groupId,$courseId)
    {
        
		$select = $this->getSelect();
		$select->from(array('s' => 'Students'), array('sgc.user_id') );		
		$select->join(array('sgc' => 'study_groups_custom'), 'sgc.user_id = s.MID', array() );		
		$select->where('s.CID = ?', $courseId);		
		if ($groupId > 0) {
            $select->where('sgc.group_id = ?', $groupId);
        }
		return $select->query()->fetchAll();		
    }
	
	/**
	 * получаем группы по студентам
	 * @param array()
	*/
	public function getUsersGroups($userIDs)
    {
        $userIDs = (array) $userIDs;
		if(!count($userIDs)){ return false; }
		
		$select = $this->getSelect();
        $select->from(
            array('sg' => 'study_groups'),
            array('sg.*')
        )
            ->join(
                array('g' => 'study_groups_users'),
                'g.group_id = sg.group_id',
                array()
            )
            ->where($this->quoteInto('g.user_id IN  (?)', $userIDs));

        return $select->query()->fetchAll();
    }
	
	/**
	 * Получаем первую группу студента. Имя
	 *
	*/
	public function getUserGroupName($user_id)
	{
		$user_id = (int)$user_id;
		if(empty($user_id)){ return false; }
		
		$groups = $this->getUserGroups($user_id);
		if(empty($groups)){ return false; }
		
		$group_first = reset($groups);
		return $group_first['name'];
	}
	
	/**
	 * Получаем первую группу студента
	 *
	*/
	public function getUserGroup($user_id)
	{
		$user_id = (int)$user_id;
		if(empty($user_id)){ return false; }
		
		$groups = $this->getUserGroups($user_id);
		if(empty($groups)){ return false; }
		
		$group_first = reset($groups);
		return new HM_StudyGroup_StudyGroupModel($group_first);
	}
	
}