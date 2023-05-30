<?php

class Infoblock_ProgressController extends HM_Controller_Action
{

	public function init()
	{
		parent::init();
        $this->_helper->layout()->setLayout('ajax.tpl');
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
	}

	
	public function getCvsAction(){
	    
	    $this->getResponse()->setHeader('Content-Type', 'text/csv', true);
	    $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="Progress.csv"', true);
	    
	    
	    $result = $this->view->progressBlock('','',array('format' => 'array'));
	    $this->view->data = $result;
	    
	}
	
	
	
}