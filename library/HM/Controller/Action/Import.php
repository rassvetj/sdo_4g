<?php
class HM_Controller_Action_Import extends HM_Controller_Action
{
    protected $_importManagerClass = null;
    protected $_importManager = null;
    protected $_importService = null;
    protected $_source = null;
    protected $_valid = true;

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index');
    }

    public function init()
    {
        parent::init();

        if ($this->_request->getActionName() == 'classifier') return true;

        $source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

        $this->_source = $source;

    }

    public function indexAction()
    {
        $this->view->form = false;
        if ($this->_importService->needToUploadFile()) {
            $this->_valid = false;
            $form = $this->_importService->getForm();
            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $this->_importService->setFileName(realpath($form->file->getFileName()));
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
        $this->view->source = $this->_source;
    }
}