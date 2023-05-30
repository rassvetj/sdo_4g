<?php
/*
 * Custom Event Lesson
 */
class HM_Lesson_Custom_CustomModel extends HM_Lesson_LessonModel
{
    private $_lesson = null;
    private $_event = null;

    public function __construct($data)
    {
        parent::__construct($data);

        $data['typeID'] = $data['tool'];
        $this->_lesson = HM_Lesson_LessonModel::factory($data);
    }
    
    public function setEvent(HM_Event_EventModel $event)
    {
        $this->_event = $event;
    }

    public function getType()
    {
        return $this->_lesson->getType();
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $icon = Zend_Registry::get('config')->path->upload->event . (-$this->typeID).".jpg";
        if (file_exists($icon)) {
            return Zend_Registry::get('config')->url->base . 'upload/events/'.(-$this->typeID).'.jpg';
        }
        return $this->_lesson->getIcon($size);
    }

    public function isExternalExecuting()
    {
        return $this->_lesson->isExternalExecuting();
    }

    public function getExecuteUrl()
    {
        return $this->_lesson->getExecuteUrl();
    }

    public function getResultsUrl($options = array())
    {
        return $this->_lesson->getResultsUrl($options);
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, array('getIcon'))) {
            return call_user_func_array(array($this, $name), $arguments);
        }
        pr($name);die();
        if (method_exists($this->_lesson, $name)) {
            return call_user_func_array(array($this->_lesson, $name), $arguments);
        }

        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $arguments);
        }

        throw new HM_Exception(sprintf('Method %s does not exists', $name));
    }
    
    public function getScale()
    {
        if (!isset($this->_event) && $this->typeID) {
            if ($collection = Zend_Registry::get('serviceContainer')->getService('Event')->find(-$this->typeID)) {
                $this->_event = $collection->current();
            }
        }
        if ($this->_event) {
            return $this->_event->scale_id;
        }
        return HM_Scale_ScaleModel::TYPE_CONTINUOUS; // default
    }
    
    
    public function onFinish($result) {
        return $this->_lesson->onFinish($result);
    }
}