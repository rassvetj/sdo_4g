<?php
/*
 * Задание
 */
class HM_Lesson_Task_TaskModel extends HM_Lesson_LessonModel
{

    const TASK_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    const ASSIGN_TYPE_RANDOM = 0;
    const ASSIGN_TYPE_MANUAL = 1;
    
    protected $_primaryName = 'SHEID';

    public function getType()
    {
        return HM_Event_EventModel::TYPE_TASK;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}task.png";
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
            'module' => 'interview',
            'controller' => 'index',
            'action' => 'index',
            'lesson_id' => $this->SHEID,
        );
    	
    	if ($this->getServiceContainer()->getService('Acl')->inheritsRole(
                $this->getServiceContainer()->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_RoleModelAbstract::ROLE_TEACHER, 
                    HM_Role_RoleModelAbstract::ROLE_TUTOR, 
                )
        )) {
    		//$url['task-preview'] = 1;
            $url = array(
                'module' => 'lesson',
                'controller' => 'result',
                'action' => 'extended',
                'lesson_id' => $this->SHEID,
                'subject_id' => $this->CID
            );
    	}
    	
        return Zend_Registry::get('view')->url($url);
    }

    public function getResultsUrl($options = array())
    {
        $params = array('module'     => 'lesson',
                        'controller' => 'result',
                        'action'     => 'index',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }

    public function isResultInTable()
    {
        return Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
                Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_RoleModelAbstract::ROLE_TEACHER,
                    HM_Role_RoleModelAbstract::ROLE_TUTOR
                )
        );
    }

    /**
     * Возвращает массив "тип=>название" режимов назначения вариантов задания
     * @static
     * @return array
     */
    public static function getAssignTypes()
    {
        return array(
            self::ASSIGN_TYPE_RANDOM => _('Случайным образом'),
            self::ASSIGN_TYPE_MANUAL => _('Ручной режим')
        );
    }

    public function isFreeModeEnabled()
    {
        return false;
    }
}