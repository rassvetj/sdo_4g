<?php
/*
 * Журнал
 */
class HM_Lesson_Journal_JournalModel extends HM_Lesson_LessonModel
{

    const STATUS_HIDDEN_YES = 1;
	
	const TASK_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    const ASSIGN_TYPE_RANDOM = 0;
    const ASSIGN_TYPE_MANUAL = 1;
	
	#const MAX_BALL_ACADEMIC_ACTIVITY = 10; # на основании приказа №146 31.01.2018, вместо старых 15
	const MAX_BALL_ACADEMIC_ACTIVITY = 40; # Новый журнал: на основании ТЗ от 12.2022 для очки, вечерки и классич заочки
	const MAX_BALL_PRACTICE_AND_LAB  = 40; # на основании ТЗ от 12.2022  для очки, вечерки и классич заочки
	const MAX_BALL_PRACTICAL_TASK    = 20; # Если есть занятие ИПЗ

	const MAX_BALL_AA_WITHOUT_PZ_AND_LAB = 80; # Новый журнал: Максимальный балл за журналы лекций, если нет практических занятий и лабораторных работ.

	const MAX_BALL_PRACTICAL_TASK_WITHOUT_IPZ	= 40; # Если нет занятия ИПЗ на основании приказа №146 31.01.2018
    
    protected $_primaryName = 'journal_id';

    public function getType()
    {
        return HM_Event_EventModel::TYPE_JOURNAL;
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
            'module' => 'journal',
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
                'module' => 'journal',
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
        $params = array('module'     => 'journal',
                        'controller' => 'result',
                        'action'     => 'extended',
                        'lesson_id'  => $this->SHEID,
                        'subject_id' => $this->CID);
        $params = (count($options))? array_merge($params,$options) : $params;
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }

    public function isResultInTable()
    {
        return false;
		/*
		return Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
                Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),
                array(
                    HM_Role_RoleModelAbstract::ROLE_TEACHER,
                    HM_Role_RoleModelAbstract::ROLE_TUTOR
                )
        );
		*/
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