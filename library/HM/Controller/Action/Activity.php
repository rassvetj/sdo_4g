<?php
class HM_Controller_Action_Activity extends HM_Controller_Action
{
    const ERR_NORIGHTS = 'Не хватает прав. Вы не можете использовать данный вид сервиса взаимодействия.';
    
    const PARAM_SUBJECT    = 'subject';
    const PARAM_SUBJECT_ID = 'subject_id';
    const PARAM_LESSON_ID  = 'lesson_id';
    
    protected $_activitySubjectName;
    protected $_activitySubjectId;
    protected $_activityResourceId;
    protected $_showInFrame;
    
    /**
     * @var HM_Subject_SubjectModel
     */
    private $_activitySubject;

    /**
     * @var HM_Lesson_LessonModel
     */
    private $_activityLesson;
    
    public function init()
    {
        parent::init();

        $this->_activitySubjectName = (string) $this->_getParam('subject', '');
        $this->_activitySubjectId   = (int) $this->_getParam('subject_id');
        $this->_activityResourceId   = (int) $this->_getParam('activity_resource_id');
        $this->_showInFrame   = (int) $this->_getParam('frame', 0);

        if(!$this->_activitySubjectName || !$this->_activitySubjectId) return;

        $id = 0;
        switch(strtolower($this->_activitySubjectName)) {
            case 'subject':
                $service = 'Subject';
                $idParamName  = 'subject_id';
                $idFieldName = 'subid';
                $id = $this->_getParam('subject_id', 0);
                break;
            case 'course':
                $service = 'Course';
                $idParamName  =  'course_id';  //'subject_id';
                $idFieldName = 'CID';
                $id = $this->_getParam('subject_id', 0);
                break;
            case 'resource':
                $service = 'Resource';
                $idParamName  = 'subject_id';
                $idFieldName = 'resource_id';
                $id = $this->_getParam('subject_id', 0);
                break;
        }

        $this->_activitySubject = $this->getOne($this->getService($service)->find($this->_activitySubjectId));

        if(!$this->isAjaxRequest() && !$this->_activityResourceId && !$this->_showInFrame) {
            $this->view->setExtended(
                array(
                    'subjectName'        => $service,
                    'subjectId'          => $this->_activitySubjectId,
                    'subjectIdParamName' => $idParamName,
                    'subjectIdFieldName' => $idFieldName,
                    'subject'            => $this->_activitySubject
                )
            );
        } else {
            Zend_Registry::get('serviceContainer')->getService('Unmanaged')->getController()->setView('DocumentBlank', 'document-blank-nobg');        
        }
    }

    /**
     * @return HM_Subject_SubjectModel
     */
    protected function getActivitySubject()
    {
        return $this->_activitySubject;
    }
    
    /**
     * @return HM_Lesson_LessonModel
     */
    protected function getActivityLesson()
    {
        if($this->_activityLesson === null){
            $lessonId = (int) $this->_getParam('lesson_id');
            if(!$lessonId) return;
            
            $this->_activityLesson = $this->getService('Lesson')->getLesson($lessonId);
            
        }
        
        return $this->_activityLesson;
    }

    public function preDispatch()
    {
        $activitySubjectName = $this->_getParam('subject', '');
        $activitySubjectId = $this->_getParam('subject_id', 0);

        if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_USER
            && !$activitySubjectName
            && !$activitySubjectId) {
            return true;
        }
        
        if ($this->_activityResourceId) {
            return true; // доступ к сервису через базу знаний - всем разрешаем на чтение; на изменение должно быть запрещено средствами acl (в каждом сервисе)
        }

        if (!$this->getService('Activity')->isActivityUser($this->getService('User')->getCurrentUserId(), $this->getService('User')->getCurrentUserRole(), $activitySubjectName, $activitySubjectId)) {
            throw new HM_Permission_Exception(_('Не хватает прав. Вы не можете использовать данный вид сервиса взаимодействия.'));
        }
    }
}