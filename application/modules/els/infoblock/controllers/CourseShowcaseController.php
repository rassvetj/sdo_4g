<?php

class Infoblock_CourseShowcaseController extends HM_Controller_Action
{

    public function init()
    {
        parent::init();
        $this->_helper->layout()->setLayout('ajax');
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
    }


	public function indexAction()
	{
        $classifierId = $this->_getParam('classifier_id', 0);

        $categoryId = $this->_getParam('category_id', 0);

        echo $this->view->subjectsShowcaseBlock(null, array(), array('classifier_id' => $classifierId, 'category_id' => $categoryId));

	}

	
	
}