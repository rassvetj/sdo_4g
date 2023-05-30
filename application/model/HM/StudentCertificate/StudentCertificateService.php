<?php
class HM_StudentCertificate_StudentCertificateService extends HM_Service_Abstract
{
	public function getIndexSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                'cs' => 'CertStud'
            ),
            array(
                'CertID'        => 'cs.CertID',
                //'StudyID'		=> 'cs.StudyID',
                'StudyCode'		=> 'cs.StudyCode',
                'Type'          => 'cs.Type',
                'Number'        => 'cs.Number',
                'Destination'   => 'cs.Destination',
                'Status'        => 'cs.Status',
                'DateCreate'	=> 'cs.DateCreate',
                'Faculty'		=> 'cs.Faculty',
                'GroupName'		=> 'cs.GroupName',
				'Address'		=> new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(cs.City, ', ') , cs.Street), ', '), cs.Postcode)"),
				'Additional'	=> 'cs.CertID',		
				
				'Employer' 		=> 'cs.Employer',
				'Direction' 	=> 'cs.Direction',
				'Course' 		=> 'cs.Course',
				'Year' 			=> 'cs.Year',
				'Submission' 	=> 'cs.Submission',
				'date_from' 	=> 'cs.date_from',
				'date_to' 		=> 'cs.date_to',
				'place_work' 	=> 'cs.place_work',
				'period' 		=> 'cs.period',
				
				'document_series' 		=> 'cs.document_series',
				'document_number' 		=> 'cs.document_number',
				'document_issue_date' 	=> 'cs.document_issue_date',
				'document_issue_by' 	=> 'cs.document_issue_by',
				'privilege_type' 		=> 'cs.privilege_type',
				'privilege_date' 		=> 'cs.privilege_date',
				'document_status' 		=> 'cs.document_status',
				'date_update' 			=> 'cs.date_update',
				
                //'DateCreate'	=> 'CONVERT(VARCHAR, cs.DateCreate, 104)',
				
				
            )
        );
		
		//$select->order('cs.DateCreate DESC');
		
        return $select;
    }


	public function getManagerSelect() {		
        try {
			$select = $this->getSelect();
			$select->from(
				array(
					'cs' => 'CertStud'
				),
				array(
					'CertID'        => 'cs.CertID',
					//'StudyID'		=> 'cs.StudyID',
					'StudyCode'		=> 'cs.StudyCode',
					'Type'          => 'cs.Type',
					'Number'        => 'cs.Number',
					'Destination'   => 'cs.Destination',
					'Status'        => 'cs.Status',
					'DateCreate'	=> 'cs.DateCreate',
					'Faculty'		=> 'cs.Faculty',
					'GroupName'		=> 'cs.GroupName',
					'City'			=> 'cs.City',
					'Street'		=> 'cs.Street',
					'Postcode'		=> 'cs.Postcode',
					'Employer' 		=> 'cs.Employer',
					'Direction' 	=> 'cs.Direction',
					'Submission' 	=> 'cs.Submission',
					'Course' 		=> 'cs.Course',
					'Year' 			=> 'cs.Year',
					'author' 		=> 'CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, \' \') , p.FirstName), \' \'), p.Patronymic)',
					'group_name' 	=> new Zend_Db_Expr("GROUP_CONCAT(distinct sg.name)"),
					'programm_name'	=> new Zend_Db_Expr("GROUP_CONCAT(distinct pr.name)"),
					'EMail'			=> 'p.EMail',					
					//'DateCreate'	=> 'CONVERT(VARCHAR, cs.DateCreate, 104)',
				)
			);
			
			$select->join(array('p' => 'People'), 'p.mid_external = cs.StudyCode', array() );
			
			$select->joinLeft(array('sgu' => 'study_groups_users'), 'sgu.user_id = p.MID', array());
			$select->joinLeft(array('sg' => 'study_groups'), 'sg.group_id = sgu.group_id', array());
			$select->joinLeft(array('pu' => 'programm_users'), 'pu.user_id = p.MID', array());
			$select->joinLeft(array('pr' => 'programm'), 'pr.programm_id = pu.programm_id', array());
			
		
			$select->group(array(
				'cs.CertID', 'cs.StudyCode', 'cs.Type', 'cs.Number', 'cs.Destination', 'cs.Status', 'cs.DateCreate', 'cs.Faculty', 'cs.GroupName', 'cs.City', 'cs.Street',
				'cs.Postcode', 'cs.Employer', 'cs.Direction', 'cs.Submission', 'cs.Course', 'cs.Year', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'p.EMail', 'pr.name',
			));			
		} catch (Exception $e) {
			#echo $e->getMessage(), "\n";			
			return false;
		}
		$select->where("cs.StudyCode IS NOT NULL AND cs.StudyCode != ''");
		//$select->order('cs.DateCreate DESC');		
        return $select;
    }

	
	
	
	public function addStudentCertificate($dataCertificate){
		if(empty($dataCertificate)) { return false; }
		
		$dataCertificate['DateCreate'] = new Zend_Db_Expr("NOW()");
		
        try {
			$certificate = $this->insert($dataCertificate);
		} catch (Exception $e) {
			return false;			
		}
        
		if ( !$certificate ) {
			return false;
		}
		
		return $certificate;
	}
	
	
	public function deleteStudentCertificate($id){
		if(!$id) { return false; }
		
		return $this->delete($id);		
	}
	
	
	public function renameFile($file, $newName){
		$path_parts = pathinfo($file);
		$new_name = $path_parts['dirname'].'/'.$newName.'.'.$path_parts['extension'];		
		if(rename($file, $new_name)){
			return $new_name;
		}		
		return $file;
	}
	
	
	
	public function uploadRemoteFtp($file)
	{
		$config      = Zend_Registry::get('config')->ftp;
		$ftpHost     = $config->host;
		$ftpLogin    = $config->login;
		$ftpPassword = $config->password;
		$ftpDir      = $config->dir->photo;
		
		$connect = ftp_connect($ftpHost);		
		if(!$connect){		
			return false;
		}

		
		$result = ftp_login($connect, $ftpLogin, $ftpPassword);				
		if(!$result) {
			ftp_quit($connect);
			return false;
		}

		if (!ftp_chdir($connect, $ftpDir)) {
			ftp_quit($connect);
			return false;
		}
		ftp_pasv($connect, true);

		$subDir = date('Y_m_d',time()); //--каждый день новая папка с файлами
				
		//$contents = ftp_nlist($connect, "."); //--ишщем директорию даты в списке
		
		if (!ftp_chdir($connect, $subDir)) { //--Если есть папка, переходим в нее
			if (!ftp_mkdir($connect, $subDir)) { //-Если нет, создаем новую.
				ftp_quit($connect);
				return false;
			}		
			if (!ftp_chdir($connect, $subDir)) { //--Переходим в новую папку
				ftp_quit($connect);
				return false;
			}			
		}

		$local_file = $file;
		
		if (!file_exists($local_file)) {			
			ftp_quit($connect);
			return false;
		}

		$remote_file = pathinfo($file, PATHINFO_BASENAME);
		
		if(!ftp_put($connect, $remote_file, $local_file, FTP_BINARY)){ # если на удаленном ftp файл с таким именем уже есть, повторно он не загрузится.			
			ftp_quit($connect);
			return false;
		}
				;
		ftp_quit($connect);
		return true;		
	}
	
	
	public function getDirectionList(){
		$select = $this->getSelect();
		$select->from(array('d' => 'directions'), array('direction_id', 'name', 'code'));
		$select->where("name != ''");
		$select->order(array('name', 'code'));
		
		$data = array();
		$res = $select->query()->fetchAll();
		if(empty($res)){ return $data; }
		
		foreach($res as $i){
			$data[$i['direction_id']] = $i['name'] . ' (' . $i['code'] . ')';
		}
		return $data;		
	}
	
	public function getDirectionById($id){
		$select = $this->getSelect();
		$select->from(array('d' => 'directions'), array('*'));
		$select->where('direction_id = ?', intval($id));
		
		$item = $select->query()->fetchObject();
		if(empty($item)){ return false; }
		
		return $item;
	}
	
	public function saveDocumentFile($user_mid_external, $tmpFilePath, $type)
	{
		$config          = Zend_Registry::get('config')->ftp;
		$ftpHost         = $config->host;
		$ftpLogin        = $config->login;
		$ftpPassword     = $config->password;
		$ftpDir_document = $config->dir->doc;
		
		$user_mid_external 	= ereg_replace("[^-a-zA-Zа-яА-ЯёЁ0-9]", "_", $user_mid_external);
		$serviceFilesFtp	= $this->getService('FilesFtp');
		$ftpPath 			= '/' . $user_mid_external . '/' . intval($type) . '/' . basename($tmpFilePath);
		$fileData 			= $serviceFilesFtp->addFile($tmpFilePath, $ftpPath);
		if(!$fileData){ return false; }
		
		if(!$serviceFilesFtp->setConnected($ftpHost, $ftpLogin, $ftpPassword)){ return false; }
		if(!$serviceFilesFtp->createDir('/'.$ftpDir_document.''.pathinfo($ftpPath, PATHINFO_DIRNAME ))){ return false; }
		if(!$serviceFilesFtp->uploadRemoteFtp($tmpFilePath, $fileData->file_id)){ return false; }
		return $fileData->file_id;
	}
	
}