<?php
class User_ImportController extends HM_Controller_Action
{
    private $_importService = null;
    protected $_importManagerClass = 'HM_User_Import_Manager';

    public function indexAction()
    {
        $source = $this->_getParam('source', false);
        $role1c = $this->_getParam('role1c', false);
            
        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source), $role1c);

        $this->view->form = false;
        if ($this->_importService->needToUploadFile()) {
            $this->_valid = false;
            $form = $this->_importService->getForm();
            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $this->_importService->setFileName($form->file->getFileName());
                        $this->_valid = true;
                    }
                }
            } else {
                $this->view->form = $form;
            }
        }

        try {
            $class = $this->_importManagerClass;
            $this->_importManager = $importManager = new $class();
            if ($this->_valid) {
                $importManager->init($this->_importService->fetchAll(), $role1c);
            }
        } catch(HM_Exception $e) {
            $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }
        $this->view->importManager = $importManager;
        $this->view->source = $source;

    }

    public function csv($role1c)
    {
        
        if($role1c == HM_User_UserModel::ROLE_1C_STUDENT){
            $this->getService('Unmanaged')->setHeader(_('Импортировать учетные записи слушателей из CSV'));
            $this->_importService = $this->getService('UserStudentCsv');
        } else if ($role1c == HM_User_UserModel::ROLE_1C_TUTOR){
            $this->getService('Unmanaged')->setHeader(_('Импортировать учетные записи тьютеров из CSV'));
            $this->_importService = $this->getService('UserTutorCsv');
        }
    }

    public function processAction()
    {
       // if($this->getService('User')->getCurrentUserId() == '5829') : 
			//$this->_helper->viewRenderer->setNoRender(true);			
		//endif;
		
		$source = $this->_getParam('source', false);
        $role1c = $this->_getParam('role1c', false);
        
        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source), $role1c);

        $importManager = new HM_User_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array(), $role1c);
        } else {
            $importManager->init($this->_importService->fetchAll(), $role1c);
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые пользователи не найдены'));
           // $this->_redirector->gotoSimple('index', 'list', 'user');
        }
		
		//if($this->getService('User')->getCurrentUserId() != '5829') :
			$importManager->import();
		//endif;	
		//var_dump(222);
		//return false;
		//exit();

        if ($importManager->getUpdatesCount() || $importManager->getInsertsCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d пользователя(ей) и обновлены %d пользователя(ей)'), $importManager->getInsertsCount(), $importManager->getUpdatesCount()));
        }

        if ($importManager->getUserTagsCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Были обновлены метки у %d пользователя(ей)'), $importManager->getUserTagsCount()));
        }
        if ($importManager->getNotProcessedCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Не были обработаны %d записи(ей)'), $importManager->getNotProcessedCount()));
        }

        switch ($this->getService('User')->getCurrentUserRole()) {
        case HM_Role_RoleModelAbstract::ROLE_DEAN :
			//if($this->getService('User')->getCurrentUserId() != '5829') : 				
				$this->_redirector->gotoSimple('index', 'list', 'study-groups');
			//endif;
			
			break;
        default :
			//if($this->getService('User')->getCurrentUserId() != '5829') : 				
				$this->_redirector->gotoSimple('index', 'list', 'user');				
			//endif;
        }

    }
}
