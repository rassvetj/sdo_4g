<?php
//--Тестовая для проверки curl index контролера данной модели
class Bookinna_Test2Controller extends HM_Controller_Action {
		
	public function init(){	
		$this->_helper->layout()->disableLayout(); 
		$this->_helper->viewRenderer->setNoRender(true);		
		parent::init();
	}
	
	public function indexAction()
    {	
		$request = $this->getRequest();
		
		//if($this->getService('User')->getCurrentUserId() == '5829') : //--Убрать эту проверку позже
			
			
			$serverName =$_SERVER['SERVER_NAME'];
			$postfields = array('ulogin'=>'TestAcc2', 'upassword'=> '285613');
			
			
			

			$ch = curl_init();			
			curl_setopt($ch, CURLOPT_URL, 'http://'.$serverName.'/bookinna/');				
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_POST, 1);			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only! //--не проверять ssl сертификат
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); // не проверять Host SSL сертификата
			curl_setopt($ch, CURLOPT_HEADER, false);
			
			$result = curl_exec($ch);
			
			//echo curl_getinfo($ch) . '<br/>';
			//echo curl_errno($ch) . '<br/>';
			//echo curl_error($ch) . '<br/>';
			
			curl_close($ch);
			echo '<pre>';
			var_dump(Zend_Json::decode($result));
			echo '</pre>';
			//var_dump($result);
			
			
		//endif; 
 
		exit();
	}	
}