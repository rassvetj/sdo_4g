<?php

require_once 'Zend/View/Helper/Abstract.php';

abstract class HM_View_Infoblock_Abstract extends ZendX_JQuery_View_Helper_UiWidget
{
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;        
        $config = Zend_Registry::get('config');
        $this->view->addScriptPath($config->path->infoblocks->default . 'views/');
        parent::setView($view);
        return $this;
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    /**
     * @param  HM_Collection $collection
     * @return HM_Model_Abstract
     */
    public function getOne($collection)
    {
        if (count($collection)) {
            return $collection->current();
        }
        return false;
    }
}