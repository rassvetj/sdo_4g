<?php
class HM_Lesson_Language_LanguageModel extends HM_Lesson_LessonModel
{
	public function getType()
    {
        return HM_Event_EventModel::TYPE_LANGUAGE;
    }
	
    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}course.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }
	
	public function getServiceContainer() {
        return Zend_Registry::get('serviceContainer');
    }

    public function getExecuteUrl()
    {
        $url = array(
            'module' 		=> 'languages',
            'controller' 	=> 'index',
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
                'module' 		=> 'lesson',
                'controller' 	=> 'result',
                'action' 		=> 'extended',
                'lesson_id' 	=> $this->SHEID,
                'subject_id' 	=> $this->CID
            );
    	}
    	
        return Zend_Registry::get('view')->url($url);
    }

    public function getResultsUrl($options = array())
    {
		return false;
    }

    public function isResultInTable()
    {
        return true;
    }

    public function isFreeModeEnabled()
    {
        return true;
    }

    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_CONTINUOUS;
    }
    
}