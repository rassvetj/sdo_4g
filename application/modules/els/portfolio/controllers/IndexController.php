<?php
class Portfolio_IndexController extends HM_Controller_Action
{
	
	public function init(){
		$this->_redirect('http://portfolio.rgsu.net');
		die;		
		parent::init();
	}
	
	
    public function indexAction()
    {        
		$this->getService('Unmanaged')->setHeader(_('Портфолио'));
		$urls = $this->getUserPortfolioFiles($this->getService('User')->getCurrentUserId());
		
		$this->view->urls = $urls;		
    }
	
	
	public function managerAction()
    {        
		$this->getService('Unmanaged')->setHeader(_('Портфолио'));		
		$form = new HM_Form_Portfolio();
		$this->view->form = $form;
    }
	
	
	public function uploadAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		
        $form = new HM_Form_Portfolio();
		
		$request = $this->getRequest();
		
		
		
		if ($request->isPost() || $request->isGet()) {
		
			$studentIDs = $request->getParam('student_id');
			$student_id = $studentIDs[0]; # берем первый элемент. Т.к. одиночный выбор
			if((int)$student_id < 1){
				$return['code']    = 0;
				$return['message'] = _('Не выбран пользователь');
			}
			else {
				if ($form->isValid($request->getParams())) {
					$file_obj = $form->u_document;
					if ($file_obj->isUploaded()) {					
						
						$path = realpath(APPLICATION_PATH . '/../public/upload/portfolio/');
						if(file_exists($path)){
							if($this->createPath($path.'/'.$student_id)){								
								$file_name = $file_obj->getFileName();
								if(is_array($file_name)){
									foreach($file_name as $fn){
										$extension = pathinfo($fn, PATHINFO_EXTENSION);									
										$newPath = ($path.'/'.$student_id.'/'.time().rand(5, 100).'.'.$extension);										
										rename($fn, $newPath);								
									}
								} else {
									$extension = pathinfo($file_name, PATHINFO_EXTENSION);																		
									$newPath = ($path.'/'.$student_id.'/'.time().rand(5, 100).'.'.$extension);										
									rename($file_name, $newPath);								
								}								
								$return['code']    = 1;
								$return['message'] = _('файлы загружены');							
							} else {							
								$return['code']    = 0;
								$return['message'] = _('не удалось создать папку');
							}							
						} else {						
							$return['code']    = 0;
							$return['message'] = _('папка загрузок недоступна');
						}
					}
				}
			}
			$form->reset();
			echo $this->view->notifications(array(array(
				'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' => $return['message']
			)), array('html' => true));
			echo $form->render();	
		}
	}
	
	
	/**
	 * получаем список файлов студента
	*/
	public function getFilesAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();
		
		if ($request->isPost() || $request->isGet()) {
			$student_id = $request->getParam('student_id');			
			$urls = $this->getUserPortfolioFiles((int)$student_id[0]);
			echo Zend_Json::encode(array( 'urls' => $urls));
		}
	}
	
	
	public function createPath($path){
		if(realpath($path)){
			return true;
		}
		
		if (!mkdir($path, 0770, true)) {
			return false;
		}
		return true;		
	}
	
	
	public function getUserPortfolioFiles($user_id){
		$urls = array();		
		$dir = $path = realpath(APPLICATION_PATH . '/../public/upload/portfolio/'.$user_id);
		if(is_dir($dir)) {   //проверяем наличие директории			 
			$files = scandir($dir);    //сканируем (получаем массив файлов)
			$valid_types = array('jpg', 'png', 'gif', 'jpeg');
			array_shift($files); // удаляем из массива '.'
			array_shift($files); // удаляем из массива '..'
			for($i=0; $i<count($files); $i++) 
			{				
				$path=$dir."/".$files[$i];
				$type_song=mb_strtolower(substr($files[$i],1 + strrpos($files[$i],'.')));
				if(in_array($type_song, $valid_types))
				{						   
					$urls[] = 'upload/portfolio/'.$user_id.'/'.$files[$i];					
				}
			} 
		} 
		return $urls;
	}

	
	/**
	 * удаляет фото из портфолио студента
	*/
	public function deleteFilesAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		$request = $this->getRequest();
		
		if ($request->isPost() || $request->isGet()) {
			$studentIDs = $request->getParam('student_id');				
			$student_id = (int)$studentIDs[0];
			
			$file_path = $request->getParam('file_path');	
			
			$path_parts = pathinfo($file_path);
			
			$dekletedPath = 'upload/portfolio/'.$student_id.'/'.$path_parts['basename'];	
			
			if($file_path == $dekletedPath){
				if (in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN))) { # только орг. обучения может удалить файл
					if(file_exists($dekletedPath)){						
						unlink($dekletedPath);
					}
				}
			}
		}
	}
	
	
}