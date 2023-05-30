<?php
/*
 * Информационный ресурс
 */

class HM_Lesson_Resource_ResourceModel extends HM_Lesson_LessonModel
{
    public function getType()
    {
        return HM_Event_EventModel::TYPE_RESOURCE;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}resource.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        parse_str(str_replace(';','&',$this->params),  $module);

        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url(array(
        																			'module' => 'resource',// 'file',
        																			'controller' => 'index',// 'get',
        																			'action' => 'index',// 'resource',
        																			'resource_id' => array(
        																				'resource_id' => $module['module_id']
        																			)
       )));

/*
        return Zend_Registry::get('config')->url->base.sprintf(
            'webinar/index/index/pointId/',
            $this->getModuleId(),
            $this->SHEID
        );*/
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
        return false;
    }
    
    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'lesson', 
                	'controller' => 'execute', 
                	'action' => 'index', 
                	'lesson_id' => $this->SHEID
                );
    }
    
    public function getFreeModeAllUrlParam()
    {
        return array(
                	'module' => 'resource', 
                	'controller' => 'list', 
                	'action' => 'index'
                );
    }
    
    
    public function isFreeModeEnabled()
    {
        return true;
    }
    
    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_BINARY;
    }

    public function onStart()
    {
        $scale = false;
        $subject = $this->getService()->getSubjectByLesson($this->SHEID);
        $scale = $this->getScale($subject->mark_type);
        switch ($scale) {
            case HM_Scale_ScaleModel::TYPE_BINARY:
                $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                break;
            case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                $range = HM_Scale_ScaleModel::getRange(HM_Scale_ScaleModel::TYPE_CONTINUOUS);
                $score = $range[1];
                break;
        }

        return $score;
    }
}