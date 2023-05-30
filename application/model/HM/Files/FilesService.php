<?php
class HM_Files_FilesService extends HM_Service_Abstract
{
    public function addFile($filePath, $fileNameString)
    {
        $dest = realpath(Zend_Registry::get('config')->path->upload->files);
        $fileName = basename($filePath);
        $temp = explode('.', $fileName);
        $ext = $temp[count($temp) - 1];
        $fileData = $this->insert(
            array(
            	'name'      => $fileNameString,
                'path'      => 'none',
                'file_size' => filesize($filePath)
            )
        );
        if(!$fileData)
            return false;

        $destFile = ( count ($temp) > 1 )? $dest . '/' . $fileData->file_id . '.' . $ext : $dest . '/' . $fileData->file_id . '.tmp';
        copy($filePath, $destFile);
        return $fileData;
    }

    public function addClip($filePath, $fileNameString)
    {
        $fileData = $this->insert(
            array(
                'name'      => $fileNameString,
                'path'      => $filePath,
                'file_size' => 0
            )
        );
        if(!$fileData)
            return false;
        return $fileData;
    }
    
    public function addFileFromBinary($binaryData, $fileNameString)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "Binary_");
        $handle = fopen($tmpfname, "w");
        fwrite($handle, $binaryData);
        fclose($handle);
        $file = $this->addFile($tmpfname, $fileNameString);
        unlink($tmpfname);
        return $file;
    }
       
    public static function getPath($fileId)
    {
        $filePath = self::getPathArchive($fileId);
		return $filePath;
		
		#$dest = realpath(Zend_Registry::get('config')->path->upload->files);
        #$glob = glob($dest . '/' . $fileId . '.*');
        #return realpath($glob[0]);
    }

    public function delete($id)
    {
        $path = self::getPath($id);
        if (file_exists($path)) {
            @unlink($path);
        }
        return parent::delete($id);
    }
    // $file->getMimeType() возвращает application/octet-stream
    // поэтому приходится вычислять вручную
	
	
	/**
	 * Ищем файл в других архивных хранилищах
	*/
	private static function getPathArchive($fileId)
	{
		if(empty($fileId)){ return false; }
		$path_list = Zend_Registry::get('config')->path->upload->files_archive;
		if(empty($path_list)){ return false; }
		
		$services  = Zend_Registry::get('serviceContainer');
		$file      = $services->getService('Files')->getOne($services->getService('Files')->find($fileId));
		$extention = pathinfo($file->name, PATHINFO_EXTENSION);
		
		foreach($path_list as $path){
			$dest 		= realpath($path);
			#$glob 		= glob($dest . '/' . $fileId . '.*');
			#$filePath 	= realpath($glob[0]);
			
			$dest		= $dest . '/' . $fileId . '.' . $extention;
			$filePath   = realpath($dest);
			
			if (file_exists($filePath) && is_file($filePath)) {
				return $filePath;
			}
		}
		return false;		
	}
	
	
	

}