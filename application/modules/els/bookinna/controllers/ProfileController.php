<?php
/* Тестовая для проверки curl index контролера данной модели */
/* Позже будет возвращать обратно BookInna ответ */
/* Список возможных кодов возвращяемых данных */
/*
	'ERR_PARAMS' => 'Некорректные входные данные.',
	'ERR_EMAIL' => 'Некорректный email.',
	'ERR_USER' => 'Пользователь не найден.',
	'ERR_NOTHING_CHANGE' => 'У пользователя уже есть валидный email. Изменять нечего.'
	'ERR_SAVE' => 'Ошибка сохранения email-а.'
	'SUCCESS' => 'Операция завершена успешно.'

*/
class Bookinna_ProfileController extends HM_Controller_Action {
		
	public function init(){	
		$this->_helper->layout()->disableLayout(); 
		$this->_helper->viewRenderer->setNoRender(true);		
		
		$allowedIPs = array( //--добавить сюда ip скрипта BookInna. 
			'192.168.132.220',
			'127.0.0.1',					
			'185.3.141.213', //--bookinna old ip
			'185.3.141.242', //--bookinna			
		);
		
		if(!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs) && !in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)){						
			exit();
		}
		
		parent::init();
	}
	
	public function updateAction()
    {	
		$request = $this->getRequest();
		
		if ($request->isPost()) {	
			$ulogin = ($request->getParam('ulogin') ) ? ($request->getParam('ulogin')) : (false);
			$uemail = ($request->getParam('uemail') ) ? ($request->getParam('uemail')) : (false);
			
			if(!$ulogin || !$uemail){
				$data = array(
					'error' => 'Incorrect params',
					'code' 	=> 'ERR_PARAMS',
				);
			} else {
				if(!$this->isValidEmail($uemail)){
					$data = array(
						'error' => 'Incorrect email.',
						'code' 	=> 'ERR_EMAIL',
					);
				} else {
					$userSDO = $this->getUserSDO($ulogin);
					if(!$userSDO){
						$data = array(
							'error' => 'User not found',
							'code' 	=> 'ERR_USER',
						);
					} else {						
						if(!$this->isNeedUpdateProfile($userSDO, array('EMail' => $uemail))){
							$data = array(
								'error' => 'User already has email.',
								'code' 	=> 'ERR_NOTHING_CHANGE',
							);
						} else {
							//--Обновляем email
							if(!$this->updateExternalEmail($userSDO['mid_external'], $uemail)){
								$data = array(
									'error' => 'Email is not updated.',
									'code' 	=> 'ERR_SAVE',
								);
							} else {
								$data = array(
									'success'	=> 'Email is changed.',
									'code' 		=> 'SUCCESS'									
								);								
							}							
						}
					}								
				}	
			}
			echo Zend_Json::encode($data);			
		}		
		exit();
	}	
	
	
	
	
	public function isValidEmail($email){
		if(!$email){
			return false;
		}
		$validator = new Zend_Validate_EmailAddress();
		if ( $validator->isValid($email) ) {
			return true;
		}		
		return false;
	}
	
	
	/**
	 * получаем информацию о студенте из БД СДО.
	*/
	protected function getUserSDO($ulogin){
		if(!$ulogin){
			return false;
		}
				
		$select = $this->getService('User')->getSelect();
		$select->from(array('p'=>'People'), 
			array(
				'MID',
				'mid_external' 		=> 'p.mid_external',
				'EMail'				=> 'p.EMail',				
				'email_external' 	=> 'exte.email',													
			)
		);				
		$select->joinLeft(array('exte' => 'student_ext_emails'),
			'exte.mid_external = p.mid_external',
			array()
		);		
		$select->where($this->quoteInto('Login = ?', $ulogin)); 										
		
		$info = $select->query()->fetch();
		
		if(!$info){
			return false;	
		}		
		return $info;
	}
	
	
	/**
	 * Надо ли обновлять профиль студента
	 * param1 - профиль стдуента. array()
	 * param2 - поля с новыми данными array()
	*/
	protected function isNeedUpdateProfile($profile = array(), $newData = array()){
		
		if(!$profile || !$newData || !is_array($profile) || !is_array($newData)){
			return false;
		}
		
		foreach($profile as $k => $v){			
			if(isset($newData[$k]) && !empty($newData[$k])){				
				if($k == 'EMail'){
					if(!$this->isValidEmail($v)){
						if(isset($profile['email_external'])){
							if(!$this->isValidEmail($profile['email_external'])){
								return true;
							}	
						} else {
							return true;
						}						
					}
				} else {					
					if($newData[$k] != $v){
						return true;
					}
				}
			}			
		}
		return false;		
	}
	
	
	/**
	* Изменяет внешнюю почту студента.
	*/
	protected function updateExternalEmail($mid_external, $email){
		
		
		if(!$mid_external || empty($mid_external) || !$email){
			return false;
		}		
		
		try {
			$dLog = Zend_Db_Table::getDefaultAdapter();		
			$where = $dLog->quoteInto(
				'mid_external = (?)',											
				$mid_external
			);
			$dLog->delete('student_ext_emails', $where); //--удаляем втарые записи студента.
			
			
			$data = array(
				'mid_external' => $mid_external,
				'email' => $email,
			);
			$isInsert = $dLog->insert('student_ext_emails', $data);
			if($isInsert){
				return true;
			}
			return false;		
		} catch (Exception $e) {
			return false;
		}		
	}
}