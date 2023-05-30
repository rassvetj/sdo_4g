<?php

class Bookinna_PromoController extends HM_Controller_Action {
		
	public function indexAction()
    {	
		$this->view->setHeader(_(' '));
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/bookinna.css');		
	}
}