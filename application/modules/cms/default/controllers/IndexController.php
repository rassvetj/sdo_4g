<?php
class IndexController extends HM_Controller_Action {
            
    public function indexAction()
    {
        $this->getResponse()->clearBody();
        $this->_redirect(Zend_Registry::get('baseUrl').'cms/index.php');        
        $this->view->content = '';
    }

}