<?php
class HM_Controller_Action_Extended extends HM_Controller_Action
{
    // имя сервиса - сущности
    protected $service = 'Course';

    // $id виртуального кабинета
    protected $id = 0;

    // имя параметра, передаваемого в адресе
    protected $idParamName = 'course_id';
    
    // поле в базе
    protected $idFieldName = 'CID';

    /**
     * Инициализируем контроллер.
     * получаем данные о сущности и в зависимости от 
     * значения services определяем нужные табы.
     */
    public function init()
    {
        parent::init();
        if (!$this->isAjaxRequest()) {
            $this->id = (int) $this->_getParam($this->idParamName, 0);
            $this->_userId = (int) $this->_getParam('user_id', 0);
            $subject = $this->getOne($this->getService($this->service)->find($this->id));
            $this->_subject = $subject;
            $this->view->setExtended(
                array(
                    'subjectName' => $this->service,
                    'subjectId' => $this->id,
                    'subjectIdParamName' => $this->idParamName,
                    'subjectIdFieldName' => $this->idFieldName,
                    'subject' => $subject,
                    'userId'          => $this->_userId
                )
            );
        }

        $this->view->withoutContextMenu = $this->_getParam('withoutContextMenu', false);
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    public function initLessonTabs()
    {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        if ($lessonId) {
            // Добавляем табы
            $this->view->clearTabLinks();
            $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
            if ($lesson && strlen($lesson->activities)) {
                $activities = unserialize($lesson->activities);
                if (is_array($activities) && count($activities)) {
                    $activityNames = HM_Activity_ActivityModel::getTabActivities();
                    $activityUrls = HM_Activity_ActivityModel::getTabUrls();
                    foreach($activities as $activityId) {
                        $url = $activityUrls[$activityId];
                        if (is_array($url)) {
                            $url['subject'] = 'subject';
                            $url['subject_id'] = $subjectId;
                            $url['lesson_id'] = $lessonId;
                        } elseif(is_string($url)) {
                            $url .= "?subject=subject&subject_id=".$subjectId."&lesson_id=".$lessonId;
                        }
                        $this->view->addTabLink($activityNames[$activityId], $url);
                    }
                }
            }
        }
    }

    /**
     * Возвращаем названия сущностей.
     * 
     * @return multitype:NULL 
     */
    public function getTabNames()
    {

        return HM_Activity_ActivityModel::getTabActivities();
    
    }
    
    /**
     * Экшн для редактирования количества сервисов - табов.
     */
    public function editServicesAction()
    {

        $form = new HM_Form_Services();
        $request = $this->_request;
        if ($request->isPost())
        {
            if ($form->isValid($request->getParams()))
            {
                $res = 0;
                foreach($form->getValue('activity') as $val){
                    $res = $res | (int) $val;
                }
                
                //$data = $this->getService($this->service)->find($this->id);
                
                //$data[0]->services = $value;
                
                $array = array(
                    $this->idFieldName => $this->id,
                    'services' => $res);
                //pr($array);
                
                $this->getService($this->service)->update($array);
                
                $this->_flashMessenger->addMessage(_('Набор сервисов взаимодействия успешно изменен.'));
                $this->_redirector->gotoSimple('edit-services', 'index', strtolower($this->service), array(
                    $this->idParamName => array(
                        $this->idParamName => $this->id)));
            
            } else
            {
            
            }
        } else
        {
            
            $data = $this->getService($this->service)->find($this->id);
            
            $service = ( int ) $data[0]->services;
            
            
            $services = HM_Activity_ActivityModel::getTabActivities();

            $res= array();
            foreach($services as $key => $val){
                if($service & $key){
                    $res[] = $key;
                }
            }

            $form->populate(array('activity' => $res)
            );
        
        }
        
        $this->view->form = $form;
    
    }
    
}