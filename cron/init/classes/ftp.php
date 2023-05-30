<?php 
class Ftp_cCron {
	
	protected $_address 	= 'srv-fs-1';
	protected $_login 		= '1c'; 
	protected $_password 	= 'Z931am008';
	protected $_path 		= false;
	protected $_connection	= false;
	protected $_uploadPath	= false;
	
	
	public function setAddress($address) {		
		$this->_address = $address;
	}
	
	public function setLogin($login) {
		$this->_login = $login;
	}
	
	public function setPassword($password) {
		$this->_password = $password;
	}	
	
	public function setPath($path) {
		$this->_path = $path;
	}
	
	public function setUploadPath($path){
		$this->_uploadPath = $path;
	}
	
	public function connect() {
		$conn_id = ftp_connect($this->_address); 
		if(!$conn_id) {			
			return 'Error. Can not connect to '.$this->_address;			
		}
		
		if (!@ftp_login($conn_id, $this->_login, $this->_password)) {
			return 'Error. Can not login to '.$this->_address;			
		}
		
		if($this->_path){
			$this->_path = trim($this->_path, '/');
			if (!ftp_chdir($conn_id, $this->_path)) {
				return 'Error. Can not change dir to "'.$this->_path.'"';
			}
		}
		
		ftp_pasv($conn_id, true);
		$this->_connection = $conn_id;
		return true;		
	}				
	
	public function closeConnection() {
		if($this->_connection){
			ftp_close($this->_connection);
		}
		$this->_connection = false;
	}
	
	
	public function uploadFiles($files){
		if(!$this->_uploadPath){ return 'Error: Upload path not set'; }
		$files = (array) $files;
		if(!count($files)){
			return 'Error: File list is empty';
		}
		
		if(!is_writable($this->_uploadPath)){
			return 'Error: Upload path not found or not writable';
		}
		
		foreach($files as $file){
			$last_date_update_file = ftp_mdtm($this->_connection, $file);
			if ($last_date_update_file == -1) {
				return 'Error: Info about file "'.$file.'" is not obtained';
			}
			
			if(date("Y-m-d", $last_date_update_file) != date('Y-m-d', time())){ //--если файла не сегодняшний
				return 'Error. Remoute file "'.$file.'" is to old';
			}
			
			if (!ftp_get($this->_connection, $this->_uploadPath.'/'.$file, $file, FTP_BINARY)) {
				return 'Error. Remoute file "'.$file.'" is not uploaded';
			}
		}
		return true;		
	}
	
	
}