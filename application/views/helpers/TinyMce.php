<?php
class HM_View_Helper_TinyMce extends HM_View_Helper_Abstract
{
 
    public $view;
 
    public function __construct()
    {
 
        $registry = Zend_Registry::getInstance();
        if (!isset($registry[__CLASS__])) {
            $container = new HM_View_Helper_TinyMce_Container();
            $registry[__CLASS__] = $container;
        }
        $this->_container = $registry[__CLASS__];
    }
 
    public function TinyMce($location = '/js/tinymce/')
    {
        require_once 'Zend/View/Exception.php';
        throw new Zend_View_Exception('This TinyMce view helper is deprecated');
        
        $this->_container->setLocalPath($location);
        return $this->_container;
    }
 
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        $this->_container->setView($view);
    }
 
    public function __call($method, $args)
    {
        if (!method_exists($this->_container, $method)) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception(sprintf('Invalid method "%s" called on TinyMce view helper', $method));
        }
 
        return call_user_func_array(array($this->_container, $method), $args);
    }
 
}