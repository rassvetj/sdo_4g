<?php
class Infoblock_ScreencastController extends HM_Controller_Action_Chart
{
	public function init()
	{
		parent::init();
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
	}

	public function getScreencastAction()
	{
		$screencast = $this->_getParam('screencast', null);
		if (file_exists(APPLICATION_PATH . "/../public/swf/{$screencast}.swf")) {
		    $this->view->url = Zend_Registry::get('config')->url->base . "swf/{$screencast}.swf";
		}
	}
}