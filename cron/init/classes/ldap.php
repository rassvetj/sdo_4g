<?php 
class Ldap_cCron extends HM_Ldap {
	
	/*
	protected $_server 	   		= null;
	protected $_port   	   		= null;
	protected $_login      		= null;
	protected $_password   		= null;
	protected $_authenticatedDn = null;
	
	public function setParams(){
		$config 	 	  		= Zend_Registry::get('config')->ldap->options->toArray();
		$this->_server 	  		= $config['server'];
		$this->_port   	  		= $config['port'];
		$this->_login  	  		= $config['username'];
		$this->_password  		= $config['password'];
		$this->_authenticatedDn = 'OU=Users,OU=Inactive Objects,DC=rgsu,DC=local';
	}
	*/
	
	protected $_blockedCodes = array(
					'514',
					'546',
					'66050',
					'66082',
					'262658',										
					'262690',										
					'328194',
					'328226',
	);

	private function setHost($host){
		$this->_options['host'] = $host;
	}
	
	private function setAccountDomainName($accountDomainName){
		$this->_options['accountDomainName'] = $accountDomainName;
	}
	
	private function setAccountDomainNameShort($accountDomainNameShort){
		$this->_options['accountDomainNameShort'] = $accountDomainNameShort;
	}
	
	private function setBaseDn($baseDn){
		$this->_options['baseDn'] = $baseDn;
	}
	
	private function setUsername($username){
		$this->_options['username'] = $username;
	}
	
	private function setPassword($password){
		$this->_options['password'] = $password;
	}
	
	public function isBlocked($login, $configSpecial){
		
		if(empty($login)){ return false; }
		
		$host 					= $this->_getHost();
		$accountDomainName 	 	= $this->_getAccountDomainName();
		$accountDomainNameShort = $this->_getAccountDomainNameShort();
		$baseDn 				= $this->getBaseDn();
		$data 					= array();
		$countErrors			= 0; # Кол-во вошибок. Если для всех серверов ошибки, то и продолжать дальнейшее выполнение не имеет смысла.
		
		if(is_array($host)){				
			foreach($host as $key => $h){
				if($key != 2){ continue; } # 1 - студенты. 3 - запасной. Для повышения скорости работы оставим 2 сервер.
				$data[$key] = array(
					'host' 					 => $h,
					'accountDomainName' 	 => $accountDomainName[$key],
					'accountDomainNameShort' => $accountDomainNameShort[$key],
					'baseDn' 				 => $baseDn[$key],
				);
			}
		} else {
			$data[2] = array(
				'host' 					 => $host,
				'accountDomainName' 	 => $accountDomainName,
				'accountDomainNameShort' => $accountDomainNameShort,
				'baseDn' 				 => $baseDn,
			);
		}
		if(empty($data)){ return false; }
		

		foreach($data as $k => $v){
				
			$this->setHost($v['host']);			
			$this->setAccountDomainName($v['accountDomainName']);
			$this->setAccountDomainNameShort($v['accountDomainNameShort']);
			$this->setBaseDn($v['baseDn']);
			
			if(isset($configSpecial['username'][$k])){
				$this->setUsername($configSpecial['username'][$k]);
			}
				
			if(isset($configSpecial['password'][$k])){
				$this->setPassword($configSpecial['password'][$k]);
			}				
				
			try {
				$user = $this->findUserByLogin($login);					
			} catch (Exception $e) {
				$countErrors++;
				$this->disconnect();
				if($countErrors == count($data)){					
					return $e;
				}
				continue;					
			}
				
			if(empty($user)){ return false; }
				
			if(in_array($user['useraccountcontrol'][0], $this->_blockedCodes)){	
				return true;
			}
			
		}
		return false;
	}
	
	
	
	
	
	
	
	
	
}