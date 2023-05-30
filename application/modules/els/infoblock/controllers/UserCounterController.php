<?php

class Infoblock_UserCounterController extends HM_Controller_Action
{

	public function init()
	{
		parent::init();
        header("Pragma: cache");

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
	}

	
	
	public function getStatsAction(){
	    
	    $params['from'] = $this->_getParam('from', 0);
	    $params['to'] = $this->_getParam('to', 0);
	    
	    $translate = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => array(
                        'temp' => array(
                            'temp',
                            'temp'
                        ),
                        'temp' => ''
                    ),
                'locale'  => 'ru'
            )
        );	
	    $stats = $this->getService('Session')->getUsersStats($params['from'], $params['to']);
	    echo Zend_Json::encode($stats);
	    exit;
	}
	
	
	public function getSubjectStatsAction(){
	    
	    $subjectId = $this->_getParam('subject_id', 0);
	    $params['from'] = $this->_getParam('from', 0);
	    $params['to'] = $this->_getParam('to', 0);
	    
	    $translate = new Zend_Translate(
            array(
                'adapter' => 'array',
                'content' => array(
                        'temp' => array(
                            'temp',
                            'temp'
                        ),
                        'temp' => ''
                    ),
                'locale'  => 'ru'
            )
        );
        
	    $res = $this->getService('Lesson')->getUsersStats($params['from'], $params['to'], $subjectId);
	    $res['time'] = round($res['time']/3600) . ' ' . iconv(Zend_Registry::get('config')->charset, 'UTF-8', $translate->translate(array('час', 'часа', 'часов', round($res['time']/3600), 'ru_RU')));
	    $res['count'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $translate->translate(array('посетил', 'посетило', 'посетили', $this->counter['count'], 'ru_RU'))) . ' ' . $res['count'] . ' ' . iconv(Zend_Registry::get('config')->charset, 'UTF-8', $translate->translate(array('человек', 'человека', 'человек', $res['count'], 'ru_RU')));
	    
	    echo Zend_Json::encode($res);
	    exit;
	}
	
	
}