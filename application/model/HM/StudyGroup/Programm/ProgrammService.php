<?php
class HM_StudyGroup_Programm_ProgrammService extends HM_Service_Abstract
{

    public function addProgrammOnGroup($programmId, $groupId)
    {
        if (!in_array($programmId, $this->getGroupProgrammsIds($groupId))) {
            $this->insert(array('group_id' => $groupId,'programm_id' => $programmId));
        }
        $usersIds = $this->getService('StudyGroup')->getUsers($groupId);
        $users = $this->getService('User')->getUsersByIds($usersIds);
        /* Зачисление на юзера на программу, без зачисление на евент, так как группа зачисляться будет автоматически */
        foreach($users as $user) {
            $this->getService('Programm')->assignToUser( $user->MID, $programmId, false);
        }

        /* Прописываем группу на курсы */
        $courses = $this->getService('Programm')->getSubjects($programmId);
        if ($courses) {
            foreach ($courses as $course) {
                $this->getService('StudyGroupCourse')->addCourseOnGroup($course->item_id, $groupId, $course->isElective);
            }
        }

        return true;
    }

    public function removeGroupFromProgramm($groupId, $programmId)
    {
        if (!in_array($programmId, $this->getGroupProgrammsIds($groupId))) {
            return true;
        }

        /* Удаляем связь группа - программа*/
        $this->deleteBy(array('group_id = ?' => $groupId, 'programm_id = ?' => $programmId));

        $usersIds = $this->getService('StudyGroup')->getUsers($groupId);
        $users = $this->getService('User')->getUsersByIds($usersIds);
        /* Удаление связи пользователь программа */
        foreach($users as $user) {
            $this->getService('ProgrammUser')->unassign($user->MID, $programmId);
        }

        /* Отчисление группы с программы */
        $courses = $this->getService('Programm')->getSubjects($programmId);
        if ($courses) {
            foreach ($courses as $course) {
                $this->getService('StudyGroupCourse')->removeGroupFromCourse($groupId, $course->item_id);
            }
        }

        return true;
    }

    public function removeGroupProgramms($groupId)
    {
        /* Список пользователей группы */
        $groupUsers = $this->getService('StudyGroup')->getUsers($groupId);
        foreach ($this->getGroupProgrammsIds($groupId) as $programmId) {
            /* Отмена зачисление на курс */
            foreach($groupUsers as $user) {
                if (!count($this->isUserProgrammInOtherGroup($groupId, $programmId, $user ))) {
                    $this->getService('ProgrammUser')->unassign($programmId, $user);
                }
            }
            /* Удаление привязки группа - программа */
            $this->deleteBy(array('group_id = ?' => $groupId, 'programm_id = ?' => $programmId));
        }

    }

    public function isUserProgrammInOtherGroup($groupId, $programmId, $userId )
    {
        /* проверка или пользователь записан на этот же курс в другой группе */
        $select = $this->getSelect();
        $select->from(
            array('sgc' => 'study_groups_programms'),
            array('sgc.*')
            )
            ->joinLeft(
                array('g' => 'study_groups_users'),
                'g.group_id = sgc.group_id',
                array()
            )
            ->where('g.user_id = ?', $userId)
            ->where('sgc.group_id != ?', $groupId)
            ->where('sgc.programm_id = ?', $programmId);

        return $select->query()->fetchAll();
    }


    public function getGroupProgrammsIds($groupId)
    {
        $output = array();
        foreach($this->fetchAll($this->quoteInto('group_id = ?',$groupId)) as $id) {
            $output []= $id->programm_id;
        }
        return $output;
    }

    public function getProgrammGroups($programmId)
    {
        $courses = $this->fetchAll($this->quoteInto('programm_id = ?', $programmId));
        if (count($courses)) return $this->getService('StudyGroup')->fetchAll(array('group_id IN (?)' => $courses->getList('group_id') ));
        else false;

    }


    public function assignUser($groupId, $userId)
    {
        $courses = array();
        foreach($this->getGroupProgrammsIds($groupId) as $programmId) {
            $courses = array_merge($courses, $this->getService('Programm')->assignToUser($userId, $programmId));
        }

        return $courses;
    }

    public function unassignUser($groupId, $userId)
    {
        foreach($this->getGroupProgrammsIds($groupId) as $programmId) {
            if (!count($this->isUserProgrammInOtherGroup($groupId, $programmId, $userId ))) {
                $this->getService('ProgrammUser')->unassign($userId, $programmId);
            }

        }
    }
	
	/**
	 * список программ по id групп.
	*/
	public function getProgrammsByGroups($groupIDs) {
		if(empty($groupIDs)){ return array(); }
        
		$select = $this->getSelect();
		$select->from(array('sg' => 'study_groups_programms'), array(
			'programm_id' 	=> 'p.programm_id',
			'programm_name' => 'p.name',
		));
		$select->join(array('p' => 'programm'), 'p.programm_id = sg.programm_id', array() );
		$select->where($this->quoteInto('sg.group_id IN (?)', $groupIDs));	
		$res = $select->query()->fetchAll();
		if(empty($res)){ return array(); }
		
		$list = array();
		foreach($res as $g){
			$list[$g['programm_id']] = $g['programm_name'];
		}
		return $list;		
		return $list;		
    }
	
	public function getProgrammIdByUser($user_id)
	{
		$select = $this->getSelect();
		$select->from(array('sgp' => 'study_groups_programms'), array(
			'programm_id' 	=> 'sgp.programm_id',
		));
		$select->join(array('sgc' => 'study_groups_custom'), 'sgc.group_id = sgp.group_id', array() );
		$select->where($this->quoteInto('sgc.user_id = ?', intval($user_id)));
		$res = $select->query()->fetchObject();
		return (int)$res->programm_id;
		
	}
    
}