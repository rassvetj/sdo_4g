<?php
class HM_Programm_Event_EventService extends HM_Service_Abstract
{
    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('мероприятия во множественном числе', '%s мероприятий', $count), $count);
    }


    public function assignToUser($userId, $eventId)
    {

        $event = $this->find($eventId)->current();
        if($event){
            $this->getService('ProgrammEventUser')->assign($userId, $event);
            $serviceName = $event->getServiceName();
            if($this->getService($serviceName) instanceof HM_Programm_Event_Interface){
                $this->getService($serviceName)->assignToUser($userId, $event->item_id);
            }

        }
    }
	
	
	/**
	 * Программы сессии
	*/
	public function getSubjectProgramms($subject_id){
		return	$this->fetchAll(array($this->quoteInto(
						array('item_id = ?', ' AND type = ?'),
						array($subject_id,     HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
				)))->getList('programm_id');		
	}

}
