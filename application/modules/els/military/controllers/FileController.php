<?php
class Military_FileController extends HM_Controller_Action_Crud
{
    
    public function getAction()
    {
        $item_id = $this->_getParam('id', false);
		
		if(empty($item_id)){		
			$this->_flashMessenger->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message'	=> _('Файл не найден')
			));		
			$this->_redirect('/');
		}
		
		$user = $this->getService('User')->getCurrentUser();
		#$item = $this->getService('MilitaryInfo')->getInfo($user->mid_external);
		$item = $this->getService('MilitaryInfo')->getById(intval($item_id));
		
		if(!$item){
			$this->_flashMessenger->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message'	=> _('Файл не найден')
			));		
			$this->_redirect('/');
		}
		
		if($item->mid_external != $user->mid_external){
			$this->_flashMessenger->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message'	=> _('Неверно указан параметр')
			));		
			$this->_redirect('/');
		}
		
		$file_content	= $this->getService('MilitaryInfo')->getFile($item);
		
		if(empty($file_content)){
			$this->_flashMessenger->addMessage(array(
				'type'		=> HM_Notification_NotificationModel::TYPE_ERROR,
				'message'	=> _('Файл не найден')
			));		
			$this->_redirect('/');
		}
		
		
		$options 		= array('filename' => $item->file_name);
		$sender      	= $this->_helper->getHelper('SendFile');
        
        $sender->SendData(
            $file_content,
            'application/unknown',
            $item->file_name
        );	
		die;
    }
    
}
