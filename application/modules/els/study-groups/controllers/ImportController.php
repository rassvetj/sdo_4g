<?php
class StudyGroups_ImportController extends HM_Controller_Action
{
    private $_importService = null;
    protected $_importManagerClass = 'HM_StudyGroup_Import_Manager';

    public function indexAction()
    {
        $source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));
        
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
                $importManager->init($this->_importService->fetchAll());
            }
        } catch(HM_Exception $e) {
            $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }
        $this->view->importManager = $importManager;
        $this->view->source = $source;

    }
    
    public function csv()
    {
        $this->getService('Unmanaged')->setHeader(_('Импортировать записи из CSV'));
        $this->_importService = $this->getService('StudyGroupCsv');
    }

    public function processAction()
    {
        $source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

        $importManager = new HM_StudyGroup_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
        }

        $importManager->import();

        if ($importManager->getUpdatesCount() || $importManager->getInsertsCount()) {
            $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d записи(ей) и обновлены %d записи(ей)'), $importManager->getInsertsCount(), $importManager->getUpdatesCount()));
        }
        
        $this->_redirector->gotoSimple('index', 'list', 'study-groups');
        

    }
}