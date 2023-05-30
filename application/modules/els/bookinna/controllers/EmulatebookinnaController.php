<?php
/* Имитирует ответы от BookInna */
class Bookinna_EmulatebookinnaController extends HM_Controller_Action {
		
	public function init(){	
		$this->_helper->layout()->disableLayout(); 
		$this->_helper->viewRenderer->setNoRender(true);
		
		$allowedIPs = array( //--Список разрешенных ip.
			'192.168.132.220',
			'127.0.0.1',
			'10.11.106.50',
		);
		
		if(!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs) && !in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)){						
			exit();
		}
		
		parent::init();
	}
	
	
	/* 
	 * Эмулируем передачу данных от BookInna в СДО
	*/
	public function getemailAction()
    {	
		ini_set('display_errors', 'Off');
		error_reporting(0);

		$params = array(
			'ulogin' => 'TestAcc1',
			'uemail' => 'mail@bookinna.ru',
		);
		
		
		$host = $_SERVER['HTTP_HOST'];
		$urlSdoToUpdateEmail = 'http://'.$host.'/bookinna/profile/update/'; //-путь до скрипта СДО для обновления email
				
		$ch = curl_init();						
		curl_setopt($ch, CURLOPT_URL, $urlSdoToUpdateEmail);			
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, 1);			
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only! //--не проверять ssl сертификат
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); // не проверять Host SSL сертификата
		curl_setopt($ch, CURLOPT_HEADER, false);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		echo '<pre>';
		echo $result;
		//var_dump(Zend_Json::decode($result));
		echo '</pre>';
		
	
		exit();
	}	
}