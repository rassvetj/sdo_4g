<?php
class HM_Files_Zip_ZipService extends HM_Service_Abstract
{
    protected $_zip	= NULL; # ZipArchive class
	
	/**
	 * @param HM_Files_FilesModel 
	*/
	protected function getFile($file){
		$filePath = HM_Files_FilesService::getPath($file->file_id);
		if (!file_exists($filePath) || !is_file($filePath)) { return false; }		
		return file_get_contents($filePath);
	}
	

	public function addFileToZip($file_name, $file){
		if(!$this->_zip){ return false; }		
		
		$content = $this->getFile($file);
		if(!$content){ return false; }
		
		$file_name = iconv(mb_detect_encoding($file_name), 'cp866', $file_name); # Fix для windows сервера и файлов с русским названием		
		$this->_zip->addFromString($file_name, $content);
		return true;
	}
	
	
	public function createZip(){		
		
		$name 		= time();
		$tmpFolder 	= realpath(Zend_Registry::get('config')->path->upload->tmp);		
		if(!$tmpFolder)	{ $tmpFolder = sys_get_temp_dir(); }
		
		$this->_zip = new ZipArchive;
		if($this->_zip->open($tmpFolder.'/'.$name.'.zip', ZipArchive::CREATE) === TRUE){ return true; }
		return false;
	}
	
	public function getZipPath(){
		return $this->_zip->filename;		
	}
	
	public function close(){		
		$this->_zip->close();	
	}
	
	public function sendZip($file, $zipName){
		
		if (file_exists($file)) {		
		
			if (ob_get_level()) {
				ob_end_clean();
			}
		
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . $zipName.'.zip');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			
			if ($fd = fopen($file, 'rb')) {
				while (!feof($fd)) {
					print fread($fd, 1024);
				}
				fclose($fd);
			}
			exit;
		}
	}
	
	public function addFileFromContentToZip($file_name, $file_content){
		if(!$this->_zip){ return false; }		
		
		if(empty($file_content)){ return false; }
		
		$file_name = iconv(mb_detect_encoding($file_name), 'cp866', $file_name); # Fix для windows сервера и файлов с русским названием		
		$this->_zip->addFromString($file_name, $file_content);
		return true;
	}
	
	
}