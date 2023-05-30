<?php 
class Mail_cCron extends Zend_Mail {
	
	protected $host = 'mail.rgsu.net';
	protected $port = 587;
	protected $auth = 'login';
	
	protected $username = false;
	protected $password = false;
	
	
	public function changeTransport(){		
		
		$config = array(
				'host' 		=> $this->host,
				'auth' 		=> $this->auth,                
                'username' 	=> $this->username,                
                'password' 	=> $this->password,
				'port' 		=> $this->port,
			);	
	
		$transport = new Zend_Mail_Transport_Smtp($config);
		
		if($transport){
			$this->setDefaultTransport($transport);						
			return true;
		}
		return false;
	
	}
	
	public function setLogin($login){
		if(!$login){
			return false;
		}
		$this->username = $login;
		return true;		
	}
	
	public function setPassword($password){
		if(!$password){
			return false;
		}
		$this->password = $password;
		return true;		
	}
	
	public function clearAttachment(){
		$parts = $this->getParts();
		foreach ($parts as $k => $v) {
			if ($v->disposition == "attachment") {
				unset($parts[$k]);
			}
		}
		$this->setParts($parts);
		$this->hasAttachments = false;
	}
	
	public function createAttachment($path_to_file){
		if(realpath($path_to_file)){
			$content 				= file_get_contents($path_to_file); 
			$finfo 					= new finfo(FILEINFO_MIME_TYPE);
			$mime_type 				= $finfo->buffer($content);
			$path_info 				= pathinfo($path_to_file);				
			$file_name 				= $path_info['basename'];

			$attachment 			= new Zend_Mime_Part($content);					
			$attachment->type 		= $mime_type;
			$attachment->disposition= Zend_Mime::DISPOSITION_ATTACHMENT;
			$attachment->encoding 	= Zend_Mime::ENCODING_BASE64;						
			$attachment->filename 	= $file_name;
			$this->addAttachment($attachment);	
		}		
	}
	
	
	
}