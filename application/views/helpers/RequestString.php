<?php

class Zend_View_Helper_RequestString extends Zend_View_Helper_Abstract
{
	//private $_request = null;

	public function __construct()
	{
		$this->_request = new Zend_Session_Namespace('RequestData');
	}


	public function requestString()
	{
		$fc = Zend_Controller_Front::getInstance()->getRequest();
		
		$data = ($fc->getModuleName() == 'default' && $fc->getControllerName() == 'error') ? $this->view->request->getParams() : $fc->getParams();
		$data['hModule'] = $data['module'];
		$data['hController'] = $data['controller'];
		$data['hAction'] = $data['action'];
		unset($data['module']);
		unset($data['controller']);
		unset($data['action']);		
		
		$result = '';
		foreach($data as $key => $value){
			if(!preg_match("/^[_a-zA-Z0-9]+$/", $key) || !preg_match("/^[_a-zA-Z0-9]+$/", $value)) continue;
			$result .= '/'.$key.'/'.$value;
		}
		
		return $result;
	}

}