<?php
class HM_Ppt2swf_Ppt2swfService extends HM_Service_Abstract
{
	
	public function requestUpdate(){
	    
	    $select = $this->getSelect();
	    
        $select->from(array('p' => 'ppt2swf'), array('pool_id', 'status', 'process', 'success_date', 'url', 'webinar_id'));
        
        $select->where('status != ?', HM_Ppt2swf_Ppt2swfModel::STATUS_READY);
        
        $res = $select->query();
        
        $fetch = $res->fetchAll();
            
        $requestIds = array();
        $forLaterUse = array();
        
        foreach($fetch as $value){
            $requestIds[] = $value['pool_id'];
            $forLaterUse[$value['pool_id']] = $value;
        }
        //pr($requestIds);
        $countIds = count($requestIds);
        if($countIds == 0){
            return false;
        }
        
        $config = Zend_Registry::get('config');
        $client = new Zend_Rest_Client($config->ppt2swf->server);
        $res = $client->getFiles($config->ppt2swf->api_key, $requestIds)->post();
        $result = array();

        for($i = 0; $i < $countIds; $i++){
            if(!empty($res->getFiles->{'key_'.$i})){
                $result[] = $res->getFiles->{'key_'.$i};
            
            }
        }
        
        foreach($result as $value){
            
            if($value->process_status == HM_Ppt2swf_Ppt2swfModel::STATUS_READY && !empty($value->url)){

                
                
                $client = new Zend_Http_Client($config->ppt2swf->server . $value->url);
                $response = $client->request();
                
                $tmpDir = sys_get_temp_dir();
                $tempFile = tempnam($tmpDir, 'PPTUPLOAD_');
                unlink($tempFile);
                mkdir($tempFile);
                file_put_contents($tempFile . '/zip.zip', $response->getBody());
                
                $zip = new ZipArchive;
                $res = $zip->open($tempFile . '/zip.zip');
                
                if ($res === TRUE) {
                    $zip->extractTo($tempFile);
                    $zip->close();
                } 

                $glob = glob($tempFile . '/*.swf');
                natsort($glob);                
                $i = 1;
                foreach($glob as $val){
                    
                    $fileData = $this->getService('Files')->addFile($val, "Слайд " . $i);
                    $i++;
                    $filePath = HM_Files_FilesService::getPath($fileData->file_id);
                    
                    $this->getService('Files')->update(
                        array(
                            'file_id' => $fileData->file_id,
                            'path'	  => realpath($filePath)
                        )
                    ); 
                    $this->getService('WebinarFiles')->insert(
                        array(
                            'webinar_id' => $forLaterUse[(string)$value->id_pull]['webinar_id'],
                            'file_id'    => $fileData->file_id  
                        )
                    );
                    
                    unlink($val);
                }

                unlink($tempFile . '/zip.zip');
                rmdir($tempFile);
                
            }
            
            $this->updateWhere(
                array('status'       => (string)$value->process_status,
                      'process'      => (string)$value->process,
                      'url'          => (string)$value->url,
                      'success_date' => (string)$value->success_date
                      ), 
                      array('pool_id = ?' => (int)$value->id_pull)
           );
        }


	}

	/**
	 * @param unknown_type $filePath путь к файлу
	 * @param unknown_type $webinarId ИД вебинара
	 * @param unknown_type $params Параметры для конвертации
	 * @return string|string
	 */
	public function sendRequest($filePath, $webinarId, $params){
	    
	    // Здесь потом переделать, чтобы просто отправлялся запрос планировщику
	    
	    $client = new Zend_Rest_Client(Zend_Registry::get('config')->ppt2swf->server);

        $res = $client->uploadPresentation(Zend_Registry::get('config')->ppt2swf->api_key, file_get_contents($filePath), $params)->post();
	    

        if($res->uploadPresentation->status == 'success'){

            if(isset($res->uploadPresentation->error)){
                    return $res->uploadPresentation->error[0];
                //$this->_flashMessenger->addMessage(_(HM_Ppt2swf_Errors::getMessage((string)$res->uploadPresentation->error[0])));
            }
            $this->getService('Ppt2Swf')->insert(
                array(
                	'pool_id' => (int) $res->uploadPresentation->response,
                    'webinar_id' => $webinarId
                )
            );
           return true;
        }
        return false;
	}
	
	

}