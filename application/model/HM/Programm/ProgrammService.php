<?php
class HM_Programm_ProgrammService extends HM_Service_Abstract
{

	protected $_subjectProgrammList = array(); //--ключ - subject_id, значение - список программ обучени. использцется в разделе "Сессии" и "Учебные курсы".
	const CACHE_NAME = 'HM_Programm_ProgrammService';

    /**
     * Назначаем программу пользователю
     *
     * @param $userId
     * @param $programmId
     * @return bool
     */
    public function assignToUser($userId, $programmId, $whithAssignEvent = true)
    {
        if($this->isAssigned($userId, $programmId)){
            return false;
        }

        return $this->getService('ProgrammUser')->assign($userId, $programmId, $whithAssignEvent);
    }

    public function delete($programmId)
    {
        /* Удаляем связь Программа - Учебная группа */
        $groups =  $this->getService('StudyGroupProgramm')->getProgrammGroups($programmId);
        if (count($groups)) {
            foreach ($groups as $group) {
                $this->getService('StudyGroupProgramm')->removeGroupFromProgramm($group->group_id, $programmId);
            }
        }

        /* Отписываем слушателей программы */
        $users = $this->getProgrammUsers($programmId);
        if (count($users)) {
            foreach ($users as $user) {
                $this->getService('ProgrammUser')->unassign($user->user_id, $programmId);
            }
        }

        /* Удаляем привязку программа - евенты */
        $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(
                array('programm_id = ?'),
                array($programmId)
            )
        );

        return parent::delete($programmId);
    }

    public function getEvents($programmId)
    {
        $events = $this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programmId));
        return $events;
    }

    public function getProgrammUsers($programmId)
    {
        return $this->getService('ProgrammUser')->fetchAll(array('programm_id = ?' => $programmId));
    }

    public function updateCoursesForUsers($programmId, $addCourses = array(), $removeCourses = array())
    {

        $users = $this->getProgrammUsers($programmId);
        if ($users) {
            if ($addCourses) {
               foreach ($addCourses as $subjectId) {
                   $subject = $this->getSubject($programmId, $subjectId);
                   if ($subject && !$subject->isElective) {
                       foreach ($users as $user) {
                           $this->getService('Subject')->assignStudent($subjectId, $user->user_id);
                       }
                   }
               }
            }
            if ($removeCourses) {
                foreach ($addCourses as $subjectId) {
                    $subject = $this->getSubject($programmId, $subjectId);
                    /* Удаление с курса, и все равно элективный или нет */
                    //if ($subject && !$subject->isElective) {
                        foreach ($users as $user) {
                            $this->getService('Subject')->unassignStudent($subjectId, $user->user_id);
                        }
                    //}
                }
            }
        }
    }

    public function updateCoursesForGroups($programmId, $newIds = array(), $oldIds = array())
    {
        $oldCourses = array_merge($oldIds['Elektive'], $oldIds['noElektive']);
        $newCourses = array_merge($newIds['Elektive'], $newIds['noElektive']);

        $addCourses = array_diff($newCourses, $oldCourses);
        $removeCourses = array_diff($oldCourses, $newCourses);

        $addNoElectiveCourses = array_diff($newIds['noElektive'], $oldIds['noElektive']);

        $groups = $this->getService('StudyGroupProgramm')->getProgrammGroups($programmId);
        if ($groups) {
            if ($addCourses) {
                foreach ($addCourses as $courseId) {
                    $course = $this->getSubject($programmId, $courseId);
                    if ($course) {
                        foreach ($groups as $group) {
                            $this->getService('StudyGroupCourse')->addCourseOnGroup($course->item_id, $group->group_id, $course->isElective);
                        }
                    }
                }
            }
            if ($removeCourses) {
                foreach ($removeCourses as $courseId) {
                    $course = $this->getSubject($programmId, $courseId);
                    if ($course) {
                        foreach ($groups as $group) {
                            $this->getService('StudyGroupCourse')->removeGroupFromCourse($group->group_id, $course->item_id, false); //$course->isElective); Удаляем все курсы со слушателей, группа уже не подписана на программу
                        }
                    }
                }
            }
            /* Изменение статуса на неэлективный ToDo покачто не трогаем когда неэлективный стал элективным*/
            if (!$addCourses && !$removeCourses && $addNoElectiveCourses) {
                foreach ($addNoElectiveCourses as $courseId) {
                    $course = $this->getSubject($programmId, $courseId);
                    if ($course) {
                        foreach ($groups as $group) {
                            $this->getService('StudyGroupCourse')->addCourseOnGroup($course->item_id, $group->group_id, $course->isElective);
                        }
                    }
                }
            }

        }

    }

    public function getProgrammsBySubjectId($subjectId, $currentUserId)
    {

        $programms = $this->fetchAllDependenceJoinInner(
            'ProgrammEvents',
            $this->quoteInto(array(
                    'item_id = ?',
                    'AND type = ?'
                ),
                array(
                    $subjectId,
                    HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT
                ))
        );
        $output = array();

        if (count($programms)) {
            foreach ($programms as $programm) {
                if ($this->isAssigned($currentUserId, $programm->programm_id)) {
                    $output[] = $programm;
                }
            }

        }
        return $output;
    }

    public function getSubjects($programmId)
    {
        $subjects = $this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programmId, 'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT));
        return $subjects;
    }

    public function getSubject($programmId, $subjectId)
    {
        $subjects = $this->getOne($this->getService('ProgrammEvent')->fetchAll(array('programm_id = ?' => $programmId, 'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, 'item_id = ?' => $subjectId)));
        return $subjects;
    }

    /**
     * @param $userId
     * @param $programmId
     * @param null $cycleId Пока пустой. Потом нужна проверка на существование этой программы в этом цикле
     */
    public function isAssigned($userId, $programmId, $cycleId = null)
    {
        $fetch = $this->getService('ProgrammUser')->fetchAll(array('user_id = ?' => $userId, 'programm_id = ?' => $programmId));

        if(count($fetch) > 0){
            return true;
        }
        return false;
    }

    public function getUserProgramms($userId)
    {

        $select = $this->getSelect();
        $select->from(
            array('p' => 'programm'),
            array('p.*')
        )
            ->joinInner(
                array('pu' => 'programm_users'),
                'p.programm_id = pu.programm_id',
                array()
            )
            ->where('pu.user_id = ?', $userId);

        return $select->query()->fetchAll();
    }

    public function getUserProgress($programmId,$userID)
    {
        $events = $this->getEvents($programmId);

        $isEnded = 0;
        $count = 0;
        $graduated = $this->getService('Graduated')->fetchAll(array('MID = ?' => $userID));

        foreach ($events as $event) {
            if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                $count++;
                if (count($graduated) && $graduated->exists('CID', $event->item_id)) {
                    $isEnded ++;
                }
            }
        }

        return sprintf(_('пройдено %s из %s'), $isEnded, $count);
    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('программа plural', '%s программа', $count), $count);
    }

    public function assignSubject($programmId, $subjectId, $isElective = 0)
    {
        $collection = $this->getService('ProgrammEvent')->fetchAll(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?', ' AND item_id = ?'),
                array($programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $subjectId)
            )
        );

        if (!count($collection)) {
            return $this->getService('ProgrammEvent')->insert(
                array(
                    'programm_id' => $programmId,
                    'type' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
                    'item_id' => $subjectId,
                    'isElective' => $isElective,
                    'name' => ''
                )
            );
        } else {
            return $this->getService('ProgrammEvent')->updateWhere(
                array(
                    'isElective' => $isElective
                ),
                array(
                    'programm_id = ?' => $programmId,
                    'type = ?' => HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
                    'item_id = ?' => $subjectId
                )
            );
        }

    }

    public function unassignSubject($programmId, $subjectId)
    {
        return $this->getService('ProgrammEvent')->deleteBy(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?', ' AND item_id = ?'),
                array($programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $subjectId)
            )
        );
    }

    public function getById($id)
    {
        return $this->getOne($this->fetchAll($this->quoteInto('programm_id = ?', $id)));
    }
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'subjectProgrammList' => $this->_subjectProgrammList,                                                  
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
            $this->_subjectProgrammList = $actions['subjectProgrammList'];                                  
            return true;
        }
        return false;
    }
	
	/**
	 * список всех программ, связанных с сессиями
	 * @return array(CID => programm name (string))
	*/
	public function getSubjectProgrammList(){		
		if(count($this->_subjectProgrammList)){									
			return $this->_subjectProgrammList;
		}
		$this->restoreFromCache();
		if(count($this->_subjectProgrammList)){					
			return $this->_subjectProgrammList;
		}
		
		$select = $this->getSelect();        
		$select->from(array('p' => 'programm'),
				array(
					'CID' => 'pe.item_id',
					'name' => 'p.name',
				)
		); 
		$select->join(
            array('pe' => 'programm_events'),
            'pe.programm_id = p.programm_id',
            array()
        );		
		$select->where('pe.type = ?', HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT);		
		$select->group(array('pe.item_id', 'p.name'));
		$res = $select->query()->fetchAll();
		$programms = array();
		foreach($res as $g){
			if(isset($programms[$g['CID']])){
				$programms[$g['CID']] .= ', '.$g['name'];	
			} else {
				$programms[$g['CID']] = $g['name'];	
			}			
		}		
		$this->_subjectProgrammList = $programms;		
		$this->saveToCache();		
		return $this->_subjectProgrammList;
	}
	
	
	public function getProgrammList(){		
		return $this->fetchAll(array(), array('name ASC'))->getList('programm_id', 'name');		
	}
	
	
	public function getByUser($userId)
	{
		return $this->fetchAllDependenceJoinInner('ProgrammUser', $this->quoteInto(array('user_id = ?'), array($userId)));
	}

}