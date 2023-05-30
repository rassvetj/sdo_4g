<?php
class Classifier_ImportController extends HM_Controller_Action_Import
{
    protected $_importManagerClass = 'HM_Classifier_Import_Manager';

    public function indexAction()
    {
        $type = (int) $this->_getParam('type', 0);
        parent::indexAction(); // required

        if (!$this->_valid && $this->_importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые рубрики не найдены'));
            $this->_redirector->gotoSimple('index', 'list', 'classifier', array('type' => $type));
        }

        $this->view->type = $type;

    }

    public function csv()
    {
        $this->getService('Unmanaged')->setHeader(_('Импортировать рубрики из csv'));
        $this->_importService = $this->getService('ClassifierCsv');
    }

    public function processAction()
    {
        $type = (int) $this->_getParam('type', 0);

        $importManager = new HM_Classifier_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые рубрики не найдены'));
            $this->_redirector->gotoSimple('index', 'list', 'classifier');
        }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d рубрик и удалены %d рубрик'), $importManager->getInsertsCount(), $importManager->getDeletesCount()));
        $this->_redirector->gotoSimple('index', 'list', 'classifier', array('type' => $type));
    }
}