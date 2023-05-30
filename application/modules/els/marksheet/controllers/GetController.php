<?php
class Marksheet_GetController extends HM_Controller_Action
{
  
	
	public function indexAction()
    {
		if(!$this->getService('User')->getCurrentUserId()){
			$this->_flashMessenger->addMessage(_('Необходимо авторизоваться'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}
		
		$file_id = (int) $this->_getParam('file_id', 0);
		
		
		$filePath = HM_Files_Marksheet_MarksheetService::getPath($file_id);
		
		if (file_exists($filePath) && is_file($filePath)) {
			$file = $this->getService('FilesMarksheet')->getFile( $file_id, $this->getService('User')->getCurrentUserId() );
			
			if($file){			
				$options = array('filename' => $file->name);
				if(!$this->_getParam('download')) $options['disposition'] = 'inline';

				$this->_helper->SendFile(
					$filePath,
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
	 * Метод получения файла ведомости для роли орг. обучения.
	*/
	public function managerAction()
    {
		if(!$this->getService('User')->getCurrentUserId()){
			$this->_flashMessenger->addMessage(_('Необходимо авторизоваться'));
			$this->_redirector->gotoSimple('index', 'index', 'default');
		}
		
		$file_id 	= (int) $this->_getParam('file_id', 0);
		$author_id 	= (int) $this->_getParam('author_id', 0);
		
		
		$filePath = HM_Files_Marksheet_MarksheetService::getPath($file_id);
		
		if (file_exists($filePath) && is_file($filePath)) {
			$file = $this->getService('FilesMarksheet')->getFile( $file_id, $author_id );
			
			if($file){			
				$options = array('filename' => $file->name);
				if(!$this->_getParam('download')) $options['disposition'] = 'inline';

				$this->_helper->SendFile(
					$filePath,
					'application/unknown',
					$options
				);
				die();			
			}
		}
		
		$this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
	}
	
	
	
	

}

