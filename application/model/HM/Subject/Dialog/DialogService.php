<?php

class HM_Subject_Dialog_DialogService extends HM_Service_Abstract implements Es_Entity_Trigger
{
	protected $userId = null;
	protected $interview_id = null;

    public function setUserId($id) {
        $this->userId = $id;
    }
	
	public function setInterviewId($id) {
        $this->interview_id = $id;
    }
	
	public function createEvent(HM_model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, array(
            'date', 'message'
        ), $this);
        return $event; 
    }	
	
	public function triggerPushCallback() {
        return function($ev) {
            $params 		= $ev->getParameters();
			$interMessage 	= $params['message'];
            $service 		= $ev->getSubject();
			$event 			= $service->createEvent($interMessage);
			$user 			= $service->getService('User')->getById(intval($interMessage->user_id));
			$subject 		= $service->getService('Subject')->getById($interMessage->CID);
			
			$service->setInterviewId($interMessage->interview_id);			
            $event->setParam('course_name', $subject->name);
            $event->setParam('user_name', $user->getName());
            $event->setParam('user_id', $user->getPrimaryKey());
			
			$userAvatar = '/'.ltrim($user->getPhoto(), '/');
            $event->setParam('user_avatar', $userAvatar);
            
            $event->setParam('course_id', $subject->getPrimaryKey());
            $event->setParam('subjectId', $subject->getPrimaryKey());
			
			$event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ADD_MESSAGE);
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, $subject->getPrimaryKey()
            );
			$eventGroup->setData(json_encode(
                array(
                    'course_name'	=> $event->getParam('course_name'),
                    'course_id'		=> $event->getParam('course_id')
                )
            ));
			$event->setGroup($eventGroup);
			$esService = $service->getService('EventServerDispatcher');
            
			
			$esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );		
        }; 
    }
	
	public function getRelatedUserList($id) {
        $select= Zend_Db_Table_Abstract::getDefaultAdapter()->select()            
            ->from(array('t' => 'Tutors'), array('tid' => 't.MID','to_whom'=>'i.to_whom', 'user_id' => 'i.user_id'))
            ->join(array('s' => 'subjects'), 's.subid = t.CID')            
            ->join(array('i' => 'subjects_interview'), 'i.CID = s.subid')
            ->where('i.interview_id = ?', intval($this->interview_id), 'INTEGER');
        $stmt = $select->query();
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $result = array();
		$isUser = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER);
		$serviceSubject = $this->getService('Subject');
        # шлем автору и всем доступных тьюторам.
		foreach ($rows as $item) {			            
			if (!in_array($item['to_whom'], $result) && $item['to_whom'] ) {
                $result[] = $item['to_whom'];
            }	
			$studentIDs = $serviceSubject->getAvailableStudents(intval($item['tid']), $item['subid']);
			$studentID = ($isUser)?($item['user_id']):($item['to_whom']); # отправитель или получаетль - студент
			if(!$studentIDs || in_array($studentID, $studentIDs)){ # нет ограничений в назначении или назначение есть и текущий студент доступен тьютору
				$result[] = intval($item['tid']);
			}							
		}		
        return $result;		
    }
}
