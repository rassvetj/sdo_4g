<?php
class Ticket_OrderController extends HM_Controller_Action
{
    protected $_ticketService = null;
    protected $_ticketID  = 0;    
    
	protected $_host  		= 'SRV-FS-1';    
	protected $_login  		= 'webit';    
	protected $_password  	= 'SqN1Xs';  
	protected $_baseDir  	= 'квитанции';  
    
    public function init()
    {	
        parent::init();
    }    
    
    public function indexAction()
    {
		
	}
	
	public function saveAction(){
		
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		$form 	= new HM_Form_Order();
		$result	= array('error' => 1, 'message' => 'Некорректные данные');
		
		$request = $this->getRequest();
		$postfix = $request->getParam('postfix');
		$params  = $request->getParams();
		foreach($params as $name => $value){
			if(strpos($name, $postfix) !== false){
				$newName = str_replace($postfix, '', $name);
				$request->setParam($newName, $value);
			}			
		}
		#$form_messages = $form->getMessages();
		if ($request->isPost() || $request->isGet()) {
			if ($form->isValid($request->getParams())) {
				$user 			= $this->getService('User')->getCurrentUser();				
				$year 			= strip_tags($request->getParam('period'));
				$date_payment	= date('Y-m-d', strtotime($request->getParam('date_payment')));				
				$sum			= floatval($request->getParam('sum'));		
				$file_id		= 0;
				
				
				$file_obj = $form->file;	
				if ($file_obj->isUploaded()) {															
					$file_id = $this->getFileId($file_obj->getFileName(), $user->mid_external, $year);
				}
				if(!empty($file_id)){				
					$data 	= array(
						'mid_external'	=> $user->mid_external,
						'year' 			=> $year,
						'date_payment' 	=> $date_payment,
						'date_created' 	=> date('Y-m-d H:i:s'),
						'sum'			=> $sum,
						'file_id'		=> $file_id,					
					);				
					$isInsert = $this->getService('TicketPayment')->insert($data);				
					if($isInsert){
						$result = array(
									'error'			=> 0,
									'message' 		=> 'Данные успешно сохранены',
									'sum'			=> $sum,
									'date_payment'	=> $date_payment,
									'file_id'		=> $file_id,
								);
					} else {
						$result['message'] = 'Не удалось сохранить данные.';
					}											
				} else {
					$result['message'] = 'Не удалось загрузить квитанцию.';
				}
			} else {
				$result['message'] = 'Необходимо заполнить поля';
			}
		} 
		echo Zend_Json::encode($result);		
	}
	
	public function getFileId($tmpFilePath, $dirLevel_1, $dirLevel_2){
		$dirLevel_1 = ereg_replace("[^-a-zA-Zа-яА-ЯёЁ0-9]", "_", $dirLevel_1);
		$dirLevel_2 = ereg_replace("[^-a-zA-Zа-яА-ЯёЁ0-9]", "_", $dirLevel_2);
		$ftpPath = '/'.$dirLevel_1.'/'.$dirLevel_2.'/'.basename($tmpFilePath);
		
		$service = $this->getService('FilesFtp');
		
		$fileData = $service->addFile($tmpFilePath, $ftpPath);
		if(!$fileData)																			{ return false; }
		if(!$service->setConnected($this->_host, $this->_login, $this->_password))				{ return false; }		
		if(!$service->createDir('/'.$this->_baseDir.''.pathinfo($ftpPath, PATHINFO_DIRNAME )))	{ return false; }		
		if(!$service->uploadRemoteFtp($tmpFilePath, $fileData->file_id))						{ return false; }
		return $fileData->file_id;		
	}
	
	
	public function getFileAction(){
		$request = $this->getRequest();
		$file_id = $request->getParam('id');
		$user_id = $this->getService('User')->getCurrentUserId();			
		$service = $this->getService('FilesFtp');
		
		# наблюдатель может скачивать любые файлы
		# но только те, информация по которым есть в БД
        if($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_SUPERVISOR){
			$user_id = (int)$request->getParam('user_id');
        }
		
		
		if(!$service->isAuthor($file_id, $user_id)){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Файл недоступен для скачивания'))
			);
			$this->_redirect('/');
		};
			
		
		
		if(!$service->setConnected($this->_host, $this->_login, $this->_password)) {			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось подключиться к хранилищу файлов'))
			);
			$this->_redirect('/');
		}
		
		$content = $service->getFile($file_id, $this->_baseDir);
		if(empty($content)){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось получить файл'))
			);
			$this->_redirect('/');
		}
		
		$fileInfo = $service->getFileInfo($file_id);
		
		$sizeFtp  = mb_strlen($content, '8bit');
		$sizeDb	  = $fileInfo->file_size;
		
		if($sizeFtp != $sizeDb){			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Размер файла не соответсвет исходному'))
			);
			$this->_redirect('/');
		}
		
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$fileInfo->name.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $sizeDb);		
		echo $content;
		die;
	}
	
}