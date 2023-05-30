<?php
//--Тестирование времени выполнения скрипта.
class Bookinna_TestTimed496568dced1810858e7d6d2a257d80dController extends HM_Controller_Action {
		
	public function init(){	
		if(time() > strtotime('2016-03-06 23:59')){ //--Дата, до которой будет работать скрипт
			$this->_redirector = $this->_helper->getHelper('Redirector');			
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}			
		$this->_helper->layout()->disableLayout(); 
		$this->_helper->viewRenderer->setNoRender(true);				
	}
	
	public function t0Action()
    {			
		$start = microtime(true);		
		$time = microtime(true) - $start;
		printf('%.4F с.', $time); 
		exit();
	}
	
	public function t15Action()
    {			
		$start = microtime(true);		
		sleep(15);
		$time = microtime(true) - $start;
		printf('%.4F с.', $time); 
		exit();
	}	
	
	public function t20Action()
    {			
		$start = microtime(true);		
		sleep(20);
		$time = microtime(true) - $start;
		printf('%.4F с.', $time); 
		exit();
	}	
		
	public function t60Action()
    {			
		$start = microtime(true);		
		sleep(60);
		$time = microtime(true) - $start;
		printf('%.4F с.', $time); 
		exit();
	}	
	
	
	
}