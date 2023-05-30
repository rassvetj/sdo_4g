<?php
class HM_Military_Info_InfoService extends HM_Service_Abstract
{
	public function getInfo($mid_external)
	{
		return	$this->getOne($this->getInfoAll($mid_external));
	}
	
	public function getInfoAll($mid_external)
	{
		return	$this->fetchAll($this->quoteInto(
					'mid_external = ?', $mid_external
				));
	}
	
	public function getFile($item)
	{	
		$server_file_name		= $item->file_path;
		$tmpfname_local_file	= tempnam(sys_get_temp_dir(), 'military');		
		$config                 = Zend_Registry::get('config')->ftp;
		
		$conn_id  = ftp_connect($config->host);
		if(!$conn_id){ return false; }
		
		$is_login = @ftp_login($conn_id, $config->login_2, $config->password_2);
		if(!$is_login){ 
			ftp_close($conn_id);
			return false; 
		}
		ftp_pasv($conn_id, true);
		
		$is_get_file = @ftp_get($conn_id, $tmpfname_local_file, $server_file_name, FTP_BINARY);
		if(!$is_get_file){ 
			ftp_close($conn_id);
			return false; 
		}
		ftp_close($conn_id);
		
		$content = file_get_contents($tmpfname_local_file);
		
		@unlink($tmpfname_local_file);
		
		return $content;
	}
	
	public function getById($id)
	{
		return	$this->getOne($this->fetchAll($this->quoteInto(
					'info_id = ?', $id
				)));
	}

	
}