<?php
class HM_Files_Ftp_FtpService extends HM_Service_Abstract
{
    protected $_ftpConnect	= NULL;
	
	
	public function addFile($locatFilePath, $fileNameString, $folder = false){        
		$path_parts = pathinfo($fileNameString);
		$ext 		= $path_parts['extension'];
		$fileName 	= $path_parts['basename'];
		$filePath 	= $path_parts['dirname'];
		
        $fileData = $this->insert(
            array(
            	'name'      	=> $fileName,                
                'file_size' 	=> filesize($locatFilePath),
				'date_uploaded' => date('Y-m-d H:i:s'),
				'author'      	=> $this->getService('User')->getCurrentUserId(),
				'folder'        => $folder,
            )
        );
        if(!$fileData){ return false; }
        
		$fileData->path = $filePath.'/'.$fileData->file_id.'.'.$ext;
		
		$isUpdate = $this->update(array(
				'file_id' 	=> $fileData->file_id,
				'path'		=> $fileData->path,
		));		
		if($isUpdate){ return $fileData; }
		
        return false;
    }
	
	
	public function setConnected($host, $login, $password){
		$connect = ftp_connect($host);		
		if(!$connect){		
			return false;
		}
		
		$result = ftp_login($connect, $login, $password);				
		if(!$result) {
			ftp_quit($connect);
			return false;
		}
		ftp_pasv($connect, true);
		
		$this->_ftpConnect = $connect;
		return true;		
	}
	
	/**
	 * @param string, path format
	 * @return bool
	*/	
	public function createDir($path){
		if(!$path){ return false; }
		if(!$this->_ftpConnect){ return false; }
		
		$path 			= strip_tags($path);
		$path 			= preg_replace("/('|\"|\r?\n)/", '', $path);
		$path 			= str_replace(array('\\', ' '), '/', $path);		
		$foldersList 	= explode('/', $path);
		$foldersList 	= array_filter($foldersList);
		
		if(empty($foldersList)){ return false; }
		foreach($foldersList as $folderName){			
			if (ftp_chdir($this->_ftpConnect, $folderName)){ continue; } //--Если есть папка, переходим в нее
			
			if (ftp_mkdir($this->_ftpConnect, $folderName)) { //-Если нет, создаем новую.
				if (ftp_chdir($this->_ftpConnect, $folderName)){ continue; } # и переходим в нее
			} 
			
			ftp_quit($this->_ftpConnect);
			return false;
		}		
		return true;
	}
	
	/**
	 * @param path to local file
	*/	
	public function uploadRemoteFtp($local_file, $file_id, $file_ext = false){
		$file_id = intval($file_id);		
		if(empty($file_id)){ return false; }

		if (!file_exists($local_file)) {			
			ftp_quit($this->_ftpConnect);
			return false;
		}
		$file_ext    = $file_ext ? $file_ext : pathinfo($local_file, PATHINFO_EXTENSION);		
		$remote_file = $file_id . '.' . $file_ext;
		
		if(!ftp_put($this->_ftpConnect, $remote_file, $local_file, FTP_BINARY)){ # если на удаленном ftp файл с таким именем уже есть, повторно он не загрузится.			
			ftp_quit($this->_ftpConnect);
			return false;
		}
				
		ftp_quit($this->_ftpConnect);
		return true;		
	}
	
	public function getFile($file_id, $baseDir = false){		
		if(!$this->_ftpConnect){ return false; }
		
		$filePath = $this->getFilePath($file_id);
		$filePath = ($baseDir) ? ($baseDir.'/'.$filePath) : ($filePath);		
		$parts	  = pathinfo($filePath);
		$path 	  = $parts['dirname'];
		$name	  = $parts['basename'];
		
		$this->createDir($path); 
		
		# стоит ли проверять на совпадение по размеру файла?
		ob_start();
		$result = ftp_get($this->_ftpConnect, "php://output", $name, FTP_BINARY);
		$data = ob_get_contents();
		ob_end_clean();		
		return $data;
	}
	
	public function getById($id){
        return $this->getOne($this->fetchAll($this->quoteInto('file_id = ?', $id)));	
    }
	
	public function isAuthor($id, $user_id){
		if(empty($id) || empty($user_id)){ return false; }
		$row = $this->getById($id);
		if($row->author == $user_id){ return true; }
		return false;
	}
	
	/**
	 * полный путь до файла от корня директории ftp
	*/	
	protected function getFilePath($file_id){
		$row = $this->getById($file_id);
		if(!$row){ return false; }
		return $row->path;
	}

	
	public function getFileInfo($file_id){
		$row = $this->getById($file_id);
		if(!$row){ return false; }
		return $row;
	}
	
	private function setDir($folderName)
	{
		if(empty($folderName)){ return false; }
		if(!$this->_ftpConnect){ return false; }
		
		if (@ftp_chdir($this->_ftpConnect, $folderName)){ return true; } # переходим в папку
		return false;		
	}
	
	private function setParentDir()
	{
		if(!$this->_ftpConnect){ return false; }
		
		if (@ftp_cdup($this->_ftpConnect)){ return true; } # переходим в родительскую директорию
		return false;			
	}
	

	/**
	 * получаем список файлов и папок кроме . и ..
	 * @params - папка, в которую нужно зайти
	 * Если папку пуста или это файл, то возвращает информацию об элементе. В данном случае, дата изменения
	*/
	public function getList($folder = false)
	{
		if(!$this->_ftpConnect){ return false; }
		
		if(!$this->setDir($folder)){ return false; }
		
		$contents = ftp_nlist($this->_ftpConnect, '');
		
		# Если пусто, значит это файл или пустая папка. Получаем 
		if(empty($contents)){
			$this->setParentDir();
			return false;
		}
		
		$data 		 = array();
		$count 		 = 0;
		$count_limit = 1000000;
		foreach($contents as $item_name){
			$count++;
			$folder_content = $this->getList($item_name);
			
			if(empty($folder_content)){
				$last_mod = ftp_mdtm($this->_ftpConnect, $item_name); 
				
				$data[$item_name] = array('last_mod' => $last_mod);
			} else {			
				$data[$item_name] = $folder_content;
			}
			if($count > $count_limit){ break; }			
		}
		$this->setParentDir();
		
		return $data;	
	}
	
	
	public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)	{ return number_format($bytes / 1073741824, 2) . ' GB'; }
        if ($bytes >= 1048576)	 	{ return number_format($bytes / 1048576, 2) . ' MB'; 	}
        if ($bytes >= 1024)			{ return number_format($bytes / 1024, 2) . ' KB'; 		}
        if ($bytes > 1)				{ return $bytes . ' bytes';								} 
        if ($bytes == 1)			{ return $bytes . ' byte';								}        
		return '0 bytes';
}
	
  
}