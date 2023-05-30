<?php
class File_GetController extends HM_Controller_Action
{

    public function resourceAction()
    {
        $resourceId = (int) $this->_getParam('resource_id', 0);
        if ($resourceId) {
            $resource = $this->getService('Resource')->getOne($this->getService('Resource')->find($resourceId));

            $options = array('filename' => $resource->filename);
            if(!$this->_getParam('download', false)) $options['disposition'] = 'inline';
            $fileName = Zend_Registry::get('config')->path->upload->resource.'/'.$resourceId;

            if ( file_exists($fileName) )
            {
    			$this->_helper->SendFile(
                    $fileName,
                    'application/unknown',
                    $options
                );
                die();
            }
        }

        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    }

	/**
	 * Экшен скачивания сертификата о прохождении курса
	 */
	public function certificateAction()
    {
        $certificate_id = (int) $this->_getParam('certificate_id', 0);
        $file_full_path = Zend_Registry::get('config')->path->upload->cetrificates . $certificate_id . '.pdf';

        if ( $certificate_id &&
        	 file_exists($file_full_path)) {

            $options = array('filename' => $certificate_id.'.pdf');
            if(!$this->_getParam('download')) $options['disposition'] = 'inline';

			$this->_helper->SendFile(
                $file_full_path,
                'application/unknown',
                $options
            );
            die();
        }

        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    }

	/**
	 * Экшен скачивания сертификата о прохождении курса
	 */
	public function fileAction()
    {
        
		if(!$this->getService('User')->getCurrentUserId()){
			$this->_flashMessenger->addMessage(_('Необходимо авторизоваться'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}
		
		$file_id = (int) $this->_getParam('file_id', 0);
		
		if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER))) {
            # Если это файл, прикрепленный студентом, то он обязательно должен быть файлом текущего пользователя.
            $lesson_id = (int) $this->_getParam('lesson_id', 0);            
            $user_id   = $this->getService('User')->getCurrentUserId();

            if(empty($lesson_id)){
                $this->_flashMessenger->addMessage(_('Файл не найден'));
                $this->_redirector->gotoSimple('index', 'index', 'default');                
            }

            $select = $this->getService('User')->getSelect();
            $select->from(array('i' => 'interview'), array('user_id', 'to_whom'));
            $select->join(array('f' => 'interview_files'), 'f.interview_id = i.interview_id', array());
            $select->where($this->quoteInto(array('i.lesson_id = ?', ' AND f.file_id = ?'), array($lesson_id, $file_id)));
            $select->limit(1);
            $obj = $select->query()->fetchObject();
            if(!empty($obj)){            
                # Это чужой файл
                if($user_id != $obj->user_id && $user_id != $obj->to_whom){
                    $this->_flashMessenger->addMessage(_('Файл не найден'));
                    $this->_redirector->gotoSimple('index', 'index', 'default');                    
                }
            }
        }
		

        $file = $this->getOne($this->getService('Files')->find($file_id));

        $filePath = HM_Files_FilesService::getPath($file_id);

        if (file_exists($filePath) && is_file($filePath)) {

            $options = array('filename' => $file->name);
            if(!$this->_getParam('download')) $options['disposition'] = 'inline';

			$this->_helper->SendFile(
                $filePath,
                'application/unknown',
                $options
            );
            die();
        }

        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    }


    public function questionAttachAction()
    {
        /*$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();*/

        $kod   = $this->_getParam('kod',0);
        $fnum  = $this->_getParam('fnum',0);
        $file = $this->getOne($this->getService('QuestionFile')->fetchAll(array('kod = ?'  => $kod,
                                                                                'fnum = ?' => $fnum)));
        if ( !$file ) {
            $this->_flashMessenger->addMessage(_('Файл не найден'));
		    $this->_redirector->gotoSimple('index', 'index', 'default');
        }
        $sender      = $this->_helper->getHelper('SendFile');
        $oldEncoding = mb_internal_encoding();
		mb_internal_encoding("Windows-1251");

        $sender->SendData(
                $file->fdata,
                'application/unknown',
                $file->fname
           );
        mb_internal_encoding($oldEncoding);
        die();
    }

	public function logAction()
    {
        if (!Zend_Registry::get('serviceContainer')->getService('User')->isRoleExists(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), HM_Role_RoleModelAbstract::ROLE_ADMIN)) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }

        $type = $this->_getParam('type', 'system');
        if ($type != 'zlog') {
            $fileName = date('Y-m') . '.' . (($type == 'mail') ? 'html' : 'txt');
            $filePath = Zend_Registry::get('config')->path->log->$type . $fileName;
        } else {
            $filePath = Zend_Registry::get('config')->path->log->system . '../zlog/php-error.log';
        }

        if (file_exists($filePath) && is_file($filePath)) {

			$this->_helper->SendFile(
                $filePath,
                'application/unknown',
                array('filename' => $fileName)
            );
            die();
        }

        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    }
    
	public function tableAction()
    {
        if (!Zend_Registry::get('serviceContainer')->getService('User')->isRoleExists(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), HM_Role_RoleModelAbstract::ROLE_ADMIN)) {
            throw new HM_Permission_Exception(_('Не хватает прав доступа.'));
        }

        if ($service = ucfirst($this->_getParam('service'))) {
            $collection = $this->getService($service)->fetchAll()->asArray();
            if (count($collection)) {
            	
            	$filePath = APPLICATION_PATH . '/../data/temp/table.csv';
            	@unlink($filePath);
            	$file = fopen($filePath, 'w');
            	
            	foreach ($collection as $item) {
            		fputcsv($file, $item, ';', '"');
            	}
            	fclose ($file);
            }
        }

        if (isset($filePath) && file_exists($filePath) && is_file($filePath)) {

			$this->_helper->SendFile(
                $filePath,
                'application/csv',
                array('filename' => "{$service}.csv")
            );
            die();
        }

        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    }
    
    public function resourcesImportSampleAction()
    {
        $fileNames = $resources = array();
        $fileNames[] = $fileResourcesName = Zend_Registry::get('config')->path->samples . 'resources.csv';
        $fileNameDefault = Zend_Registry::get('config')->path->samples . 'resourcesDefault.csv';
    
        if (($fileResources = fopen($fileNameDefault, 'r')) && count($classifierTypes = $this->getService('ClassifierType')->getClassifierTypesNames(HM_Classifier_Link_LinkModel::TYPE_RESOURCE))) {
            
            while(($data = fgetcsv($fileResources, 0, ';', '"')) !== false) {
                $resources[] = $data;                
            }
            fclose($fileResources);
            unlink($fileResourcesName);
            
            foreach ($classifierTypes as $classifierTypeId => $classifierTypeName) {
                
                $classifiers = $this->getService('Classifier')->fetchAll(array('type = ?' => $classifierTypeId), array('type', 'lft'));
                    
                $fileNames[] = Zend_Registry::get('config')->path->samples . 'readme.txt';
                $fileNames[] = $fileClassifierName = Zend_Registry::get('config')->path->samples . "classifier_{$classifierTypeId}.csv";
                unlink($fileClassifierName);
                
                copy(Zend_Registry::get('config')->path->samples . "empty.csv", $fileClassifierName);
                $fileClassifier = fopen($fileClassifierName, 'a');
                fputcsv($fileClassifier, array(_('ID'), _('Название')), ';', '"');
                foreach ($classifiers as $classifier) {
                    fputcsv($fileClassifier, array($classifier->classifier_id, $classifier->name), ';', '"');
                }
                //fclose($fileClassifier);
                
                $classifiers = $classifiers->getList('classifier_id');
                sort($classifiers); // flush keys
                foreach ($resources as $i => &$resource) {
                    if (!$i) {
                        $resource[] = $classifierTypeName;
                    } else {
                        $resource[] = ($i == 1) ? implode(',', array_slice($classifiers, 0, 3)) : $classifiers[rand(0, count($classifiers)-1)];
                    }                    
                }
            }
            // Создаем пустой файл с нужной кодировкой.
            copy(Zend_Registry::get('config')->path->samples . "empty.csv", $fileResourcesName);
            
            $fileResources = fopen($fileResourcesName, 'a');
            foreach ($resources as $resource) {
                fputcsv($fileResources, $resource, ';', '"');
            }

            $zip = new ZipArchive();
            $res = $zip->open($fileZip = Zend_Registry::get('config')->path->samples . 'resources.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($res) {
                foreach($fileNames as $filename) {
                    $zip->addFile($filename, basename($filename));
                }
            }
            $zip->close();
            
			$this->_helper->SendFile(
                $fileZip,
                'application/zip',
                array('filename' => 'resources.zip')
            );
        } else {
			
            $this->_helper->SendFile(
                $fileResourcesName,
                'text/csv',
                array('filename' => 'resources.csv')
            );            
        }
        die();
    }    
}