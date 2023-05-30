<?php
class Confirm_UserInfoController extends HM_Controller_Action
{
    public function init()
    {
		parent::init();
    }
    
    
    public function indexAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$user_id = $this->getService('User')->getCurrentUserId();
		
		if(empty($user_id)){
			echo json_encode(array('error' => 'Авторизуйтесь'));
			die;
		}
		
		$type				= HM_StudentNotification_Agreement_AgreementModel::TYPE_DIST_LEARNING;
		$request			= $this->getRequest();
		$params				= $request->getParams();
		$param_name			= false;
		$param_value		= false;
		$param_desctition	= '';
		$hide_popup 		= 0;
		
		if(array_key_exists('skype', $params)){
			$param_name			= 'skype';
			$param_value		= $params['skype'];
			$param_desctition	= 'skype';
			
		} elseif(array_key_exists('phone', $params)){
			$param_name			= 'phone';
			$param_value		= $params['phone'];
			$param_desctition	= 'телефон';
			
		} elseif(array_key_exists('email', $params)){
			$param_name			= 'email';
			$param_value		= $params['email'];
			$param_desctition	= 'email';
		}
		
		$param_value = trim($param_value);
		
		if(empty($param_value)){
			echo json_encode(array('error' => 'Заполните поле '.$param_desctition));
			die;
		}
		
		if($param_name == 'email'){
			if (!filter_var($param_value, FILTER_VALIDATE_EMAIL)) {
				echo json_encode(array('error' => 'Неверно указан '.$param_desctition));
				die;
			}
		}
		
		
		
		$data = array(
			$param_name => $param_value,
		);
		
		$isSave = $this->getService('UserInfoConfirm')->save($data, $user_id);
		
		if($isSave){
			# все зполненно, можно больше не показывать окно для подтверждения. 
			if(	!empty($isSave->email) && !empty($isSave->phone) && !empty($isSave->skype) ){
				$hide_popup = 1;
				$serviceAgreement = $this->getService('StudentNotificationAgreement');
				if( !$serviceAgreement->hasAgreement($user_id, $type) ){
					$serviceAgreement->add($user_id, $type);
				}
			}
			echo json_encode(array('message' => $param_desctition.' успешно подтвержден', 'hide_popup' => $hide_popup ));
			die;
		}
		
		echo json_encode(array('error' => 'Не удалось подтвердить '.$param_desctition));
		die;
		
		
	}
	
	public function additionalAction()
    {
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$user    = $this->getService('User')->getCurrentUser();
		$user_id = $user->MID;
		
		if(empty($user_id)){
			echo json_encode(array('error' => 'Авторизуйтесь'));
			die;
		}
		
		$type				= HM_StudentNotification_Agreement_AgreementModel::TYPE_SNILS_INN;
		$request			= $this->getRequest();
		$params				= $request->getParams();
		$param_name			= false;
		$param_value		= false;
		$param_desctition	= '';
		$hide_popup 		= 0;
		$data               = array();
		
		
		if(array_key_exists('inn', $params)){
			$param_name			= 'inn';
			$param_value		= trim($params['inn']);
			$param_desctition	= 'ИНН';
			
			if(empty($param_value)){
				echo json_encode(array('error' => 'Заполните поле '.$param_desctition));
				die;
			}
			
			if(!$this->isInn($param_value, false)){
				echo json_encode(array('error' => 'Не корректный номер ' . $param_desctition));
				die;
			}
			$data[$param_name] = $param_value;
		} 
		
		if(array_key_exists('snils', $params)){
			$param_name			= 'snils';
			$param_value		= trim($params['snils']);
			$param_desctition	= 'СНИЛС';
			
			if(empty($param_value)){
				echo json_encode(array('error' => 'Заполните поле '.$param_desctition));
				die;
			}
			
			if(!$this->isSnils($param_value, false)){
				echo json_encode(array('error' => 'Не корректный номер ' . $param_desctition));
				die;
			}			
			$data[$param_name] = $param_value;
		}
		
		$upload = new Zend_File_Transfer();
		$files  = $upload->getFileInfo();
		
		$innFileInfo   = false;
		$snilsFileInfo = false;
		
		if(array_key_exists('inn_file', $files) || array_key_exists('inn_file_raw', $files)){		
			if($upload->isUploaded('inn_file')){
				$innFileInfo = $files['inn_file'];					
			} elseif($upload->isUploaded('inn_file_raw')){
				$innFileInfo = $files['inn_file_raw'];
			}
			
			if(!$innFileInfo){
				echo json_encode(array('error' => 'Загрузите файл ИНН'));
				die;
			}
		}
		
		
		
		if(array_key_exists('snils_file', $files) || array_key_exists('snils_file_raw', $files)){
			if($upload->isUploaded('snils_file')){
				$snilsFileInfo = $files['snils_file'];		
			} elseif($upload->isUploaded('snils_file_raw')){
				$snilsFileInfo = $files['snils_file_raw'];
			}
			if(!$snilsFileInfo){
				echo json_encode(array('error' => 'Загрузите файл СНИЛС'));
				die;
			}
		}
		
		if($innFileInfo){
			$inn_file_id   = $this->saveFileInn($user->mid_external,   $innFileInfo);
			if($inn_file_id){
				$data['inn_ftp_file_id']   = (int)$inn_file_id;
			}
		}
		
		if($snilsFileInfo){
			$snils_file_id = $this->saveFileSnils($user->mid_external, $snilsFileInfo);
			if($snils_file_id){
				$data['snils_ftp_file_id']   = (int)$snils_file_id;
			}
		}
		
		if(empty($data)){
			$isSave = true;
		} else {
			$isSave = $this->getService('UserInfoConfirm')->save($data, $user_id);	
		}
		
		if(!$isSave){
			echo json_encode(array('error' => 'Не удалось сохранить данные'));
			die;
		}
		
		# все зполненно, можно больше не показывать окно для подтверждения. 
		if($isSave){
			$hide_popup = 1;
			$serviceAgreement = $this->getService('StudentNotificationAgreement');
			if( !$serviceAgreement->hasAgreement($user_id, $type) ){
				$serviceAgreement->add($user_id, $type);
			}
		}
		echo json_encode(array('message' => 'Информация успешно отправлена', 'hide_popup' => $hide_popup ));
		die;		
	}
	
	# для физ лиц. Для юр. лиц другой
	public function isInn($inn, $checkSum = false)
	{
		if(!preg_match('#([\d]{12})#', $inn, $m)) {
			return false;
		}
		$inn = $m[0];
		if(!$checkSum){ return true; }
		
		$code11 = (($inn[0] * 7 + $inn[1] * 2 + $inn[2] * 4 + $inn[3] *10 + 
                    $inn[4] * 3 + $inn[5] * 5 + $inn[6] * 9 + $inn[7] * 4 + 
                    $inn[8] * 6 + $inn[9] * 8) % 11 ) % 10;
        $code12 = (($inn[0] * 3 + $inn[1] * 7 + $inn[2] * 2 + $inn[3] * 4 + 
                    $inn[4] *10 + $inn[5] * 3 + $inn[6] * 5 + $inn[7] * 9 + 
                    $inn[8] * 4 + $inn[9] * 6 + $inn[10]* 8) % 11 ) % 10;
                        
        if ($code11 == $inn[10] && $code12 == $inn[11]){ return true; }
		return false;
	}
	
	# Ex: 152-675-138 70
	public function isSnils($snils, $checkSum = false)
	{
		if(!preg_match('#([\d]{3})(-)([\d]{3})(-)([\d]{3})( )([\d]{2})#', $snils, $m)) {
			return false;
		}
		$snils = $m[0];
		if(!$checkSum){ return true; }
		
		$snils = str_replace('-', '', $snils);
		$res = substr($snils, -2, 2);
        $sum = 0;
        for($i = 0; $i < 9; $i++){
			$sum += (int)$snils[$i] * (9 - $i);
		}
        if(!(($res == $sum % 101) || ($res == '00' && $sum == 100))){
			return false;
		}
        return true;
	}
	
	public function saveFileInn($user_mid_external, $fileInfo)
	{
		$config             = Zend_Registry::get('config')->ftp;
		$tmpFilePath        = $fileInfo['tmp_name'];
		$folder             = $config->dir->inn;
		$user_mid_external 	= ereg_replace("[^-a-zA-Zа-яА-ЯёЁ0-9]", "_", $user_mid_external);
		$serviceFilesFtp	= $this->getService('FilesFtp');
		$fileExt            = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);		
		$ftpPath 			= '/' . $user_mid_external . '/' . basename($fileInfo['name']);
		$fileData 			= $serviceFilesFtp->addFile($tmpFilePath, $ftpPath, $folder);
		if(!$fileData){ return false; }
		
		if(!$serviceFilesFtp->setConnected($config->host, $config->login, $config->password)){ return false; }
		if(!$serviceFilesFtp->createDir('/' . $folder . '' . pathinfo($ftpPath, PATHINFO_DIRNAME ))){ return false; }
		if(!$serviceFilesFtp->uploadRemoteFtp($tmpFilePath, $fileData->file_id, $fileExt)){ return false; }
		return $fileData->file_id;
	}
	
	public function saveFileSnils($user_mid_external, $fileInfo)
	{
		$config             = Zend_Registry::get('config')->ftp;
		$tmpFilePath        = $fileInfo['tmp_name'];
		$folder             = $config->dir->snils;
		$user_mid_external 	= ereg_replace("[^-a-zA-Zа-яА-ЯёЁ0-9]", "_", $user_mid_external);
		$serviceFilesFtp	= $this->getService('FilesFtp');
		$fileExt            = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);		
		$ftpPath 			= '/' . $user_mid_external . '/' . basename($fileInfo['name']);
		$fileData 			= $serviceFilesFtp->addFile($tmpFilePath, $ftpPath, $folder);
		if(!$fileData){ return false; }
		
		if(!$serviceFilesFtp->setConnected($config->host, $config->login, $config->password)){ return false; }
		if(!$serviceFilesFtp->createDir('/' . $folder . '' . pathinfo($ftpPath, PATHINFO_DIRNAME ))){ return false; }
		if(!$serviceFilesFtp->uploadRemoteFtp($tmpFilePath, $fileData->file_id, $fileExt)){ return false; }
		return $fileData->file_id;
	}
	
	
	
}




