<?php
class HM_Programm_User_UserService extends HM_Service_Abstract
{
    public function assign($userId, $programmId, $whithAssignEvent = true)
    {
        //$date = HM_Date::now();
        //$this->insert(array('user_id' => $userId, 'programm_id' => $programmId, 'assign_date' => $this->getDateTime($date->getTimestamp())));
        $this->insert(array('user_id' => $userId, 'programm_id' => $programmId, 'assign_date' => $this->getDateTime()));

        if ($whithAssignEvent) {
            $courses = array();
            $events = $this->getService('ProgrammEvent')->fetchAll($this->quoteInto('programm_id = ?', $programmId));
            if (count($events)) {
                foreach($events as $event) {
                    if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                        $courses[] =  $event->item_id;
                        /* Подписываем только на обязательные курсы */
                        if ($event->isElective == 0) {
                            $this->getService('Subject')->assignStudent($event->item_id, $userId);
                        }
                    }
                }
            }
            /* Возвращаем список курсов которые входят в программу, нужно для правильного добавления слушателя в группу */
            return $courses;
        }
    }

    public function unassign($userId, $programmId)
    {

        $events = $this->getService('ProgrammEvent')->fetchAll($this->quoteInto('programm_id = ?  AND isElective = 0', $programmId));
        if (count($events)) {
            foreach($events as $event) {
                if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                    $this->getService('Subject')->unassignStudent($event->item_id, $userId);
                    $this->getService('Claimant')->deleteBy(
                        $this->quoteInto(array('MID = ?', ' AND CID = ?'), array($userId, $event->item_id))
                    );
                }
            }
        }

        $this->deleteBy(
            $this->quoteInto(
                array('user_id = ?', ' AND programm_id = ?'),
                array($userId, $programmId)
            )
        );
    }
	
	/**
	 * Удаление всех программ студента. Использяется при смене группы с сохранением старых назначений на сессии.
	*/
	public function unassignAllProgramms($userId){
		$this->deleteBy(
            $this->quoteInto('user_id = ?', $userId)            
        );		 
	}
	
	public function getProgramms($userId)
	{
		$assigns   = $this->fetchAllDependenceJoinInner('Programm', $this->quoteInto(array('self.user_id = ?'), array($userId)));
		$programms = new HM_Collection();
		$programms->setModelClass('HM_Programm_ProgrammModel');
		
		foreach($assigns as $assign){
			$programms->offsetSet($programms->count(), $assign->programms->current());			
		}		
		return $programms;
	}



}