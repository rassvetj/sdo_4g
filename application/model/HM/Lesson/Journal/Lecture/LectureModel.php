<?php
class HM_Lesson_Journal_Lecture_LectureModel extends HM_Lesson_Journal_JournalModel
{   
	public function getType()
    {
        return HM_Event_EventModel::TYPE_JOURNAL_LECTURE;
    }

	public function getExecuteUrl()
    {
    	$url = array(
            'module' 		=> 'journal',
            'controller' 	=> 'lecture',
            'action' 		=> 'index',
            'lesson_id' 	=> $this->SHEID,
        );
    	
    	if ($this->getServiceContainer()->getService('Acl')->inheritsRole(
                $this->getServiceContainer()->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_RoleModelAbstract::ROLE_TEACHER, 
                    HM_Role_RoleModelAbstract::ROLE_TUTOR, 
                )
        )) {    		
            $url = array(
                'module' 		=> 'journal',
                'controller' 	=> 'lecture',
                'action' 		=> 'extended',
                'lesson_id' 	=> $this->SHEID,
                'subject_id' 	=> $this->CID
            );
    	}
    	
        return Zend_Registry::get('view')->url($url);
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'journal',
                        'controller' => 'lecture',
                        'action'     => 'result',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }
}