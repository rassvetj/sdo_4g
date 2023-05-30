<?php
class Webinar_Service extends Task_Service {
	static protected $_instance;
	
	const INDEXLIST_FILENAME = 'index.list';
	
	/**
	 * @return Webinar_Service
	 */
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getFiles($filelist) {
    	$files = array();
    	//if (file_exists($filelist)) {
    		if ($lines = file($filelist)) {
    			foreach($lines as $line) {
    				if (strlen($line)) {
    					$file = trim(str_replace(Webinar_Service::INDEXLIST_FILENAME, $line, $filelist));
    					$files[$file] = $file;
    				}
    			}
    		}
    	//}    	
    	return $files;
    }
	/**
	 * возвращ€ет путь где наход€тс€ файлы на сервере ped5
	 * @param $indexFile
	 * @return $pathToFiles
	 */
    public function getPathToFiles($indexFile) {
		$pathToFiles = str_replace(Webinar_Service::INDEXLIST_FILENAME, '', $indexFile);
    	return $pathToFiles;
    }

    /**
     * 
     * @param $pointId
     * @return HM_User
     */
    public function getLeader($pointId) 
    {
    	return User_Service::getInstance()->get($this->getTeacherId($pointId));
    }
    
    public function getRecordFiles($pointId) {
        return Webinar_History_Service::getInstance()->getFiles($pointId);
    }
}