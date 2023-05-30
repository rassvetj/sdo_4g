<?php
class HM_Extension_Remover_WebinarRemover extends HM_Extension_Remover_Abstract implements HM_Service_Extension_Remover_Interface
{
    const DISABLE_WEBINAR = 1;

    protected $_itemsToHide = array(
        'contextMenus' => array(
            'cm:subject:webinars_page1', // webinars
        ),
    ); 
    
    public function init()
    {

    }
    
    public function registerEventsCallbacks()
    {
    	$this->getService('EventDispatcher')->connect(
    			HM_Extension_ExtensionService::EVENT_FILTER_CONTEXT_BLOCK,
    			array($this, 'callFilterContextBlock')
    	);
    	$this->getService('EventDispatcher')->connect(
    			HM_Extension_ExtensionService::EVENT_FILTER_LESSON_TYPES,
    			array($this, 'callFilterLessonTypes')
    	);
    }
        
	// todo: рефакторить
    public function callFilterContextBlock($event)
    {
    	return self::DISABLE_WEBINAR;
    }

    public function callFilterLessonTypes($event, $lessonTypes)
    {
        unset($lessonTypes[HM_Event_EventModel::TYPE_WEBINAR]);
    	return $lessonTypes;
    }
}