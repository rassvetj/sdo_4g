<?php
class HM_Files_Marksheet_MarksheetService extends HM_Service_Abstract
{
	/**
	 * список всех ранее созданных ведомостей
	*/
	public function getSubjectMarksheets($subject_id, $marksheet_external_id = false){
		if(!empty($marksheet_external_id)){
			$criteria = $this->quoteInto(array('subject_id = ?', ' AND author_id = ?', ' AND marksheet_external_id = ?'), 
										 array($subject_id, $this->getService('User')->getCurrentUserId(), $marksheet_external_id));
		} else {
			$criteria = $this->quoteInto(array('subject_id = ?', ' AND author_id = ?'), array($subject_id, $this->getService('User')->getCurrentUserId()));
		}
		
		return $this->fetchAll($criteria)->getList('file_id', 'name');
	}
	
	public static function getPath($fileId)
    {
        $dest = realpath(Zend_Registry::get('config')->path->upload->files_marksheets);
        $glob = glob($dest . '/' . $fileId . '.*');

        return realpath($glob[0]);
    }
	
	public function getFile($file_id, $author_id){		
		return $this->getOne(	$this->fetchAll(	$this->quoteInto(array('file_id = ?', ' AND author_id = ?'), array($file_id, $author_id))	)	);
	}
	
	public function getById($file_id){		
		return $this->getOne($this->fetchAll($this->quoteInto('file_id = ?', $file_id)));
	}
	
	
	
	public function addFile($filePath, $fileNameString, $subject_id, $params){
        $dest = realpath(Zend_Registry::get('config')->path->upload->files_marksheets);
        $fileName = basename($filePath);
		
		if(!empty($params['ext'])){
			$ext = $params['ext'];
		} else {		
			$temp = explode('.', $fileName);
			$ext = $temp[count($temp) - 1];
		}
		$author_id = (int)$params['author_id'];
		
		$marksheet_external_id 	= !empty($params['marksheet_external_id']) 	? $params['marksheet_external_id']	: NULL;
		$student_id 			= !empty($params['student_id']) 			? (int)$params['student_id'] 		: NULL;
		$group_id 				= !empty($params['group_id']) 				? (int)$params['group_id'] 			: NULL;
		
		
		
        $fileData = $this->insert(
            array(
            	'name'      			=> $fileNameString,
                #'path'      			=> 'none',
                'file_size' 			=> filesize($filePath),
				'subject_id'			=> (int)$subject_id,
				'author_id'				=> $author_id,
				'student_id'			=> $student_id,
				'group_id'				=> $group_id,
				'marksheet_external_id'	=> $marksheet_external_id,
				'date_created'			=> new Zend_Db_Expr('NOW()'),
            )
        );
        if(!$fileData)
            return false;
        
        $destFile = ( !empty($ext) )? $dest . '/' . $fileData->file_id . '.' . $ext : $dest . '/' . $fileData->file_id . '.tmp';
        copy($filePath, $destFile);
        return $fileData;
    }
	
	public function addFileFromBinary($binaryData, $fileNameString, $subject_id, $params){
        $tmpfname = tempnam(sys_get_temp_dir(), "Binary_");
		
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $binaryData);
        fclose($handle);
        $file = $this->addFile($tmpfname, $fileNameString, $subject_id, $params);
        unlink($tmpfname);
        return $file;
    }
	
	
	/**
	 * Открепляем автора от ведомости.
	 * Сама ведомость сохраняется, но автору уже не выводистя.
	*/
	public function unAttachAuthor($file_id){
		if(empty($file_id)){ return false; }
		return $this->updateWhere(array('author_id' => NULL), array('file_id = ?' => $file_id));
	}
	
	
  
}