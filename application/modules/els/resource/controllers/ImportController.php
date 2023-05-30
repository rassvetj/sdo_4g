<?php
class Resource_ImportController extends HM_Controller_Action_Import
{
    public $_importManagerClass = 'HM_Resource_Import_Manager';

/*    public function init()
    {
        parent::init();

        $config = Zend_Registry::get('config');
        $this->importServiceName = "ResourceCsv";
        $this->importOptions = $config->resource->import->options->toArray();
        $this->importService = $this->getService($this->importServiceName);

        $this->importService->setOptions($this->importOptions);

    }*/

    public function csv()
    {
        $this->getService('Unmanaged')->setHeader(_('Импортировать информационные ресурсы из CSV'));
        $this->_importService = $this->getService('ResourceCsv');
    }

    public function processAction()
    {
        $importManager = new HM_Resource_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->importService->fetchAll());
        }

//         if (!$importManager->getCount()) {
//             $this->_flashMessenger->addMessage(_('Изменения структуры организации не найдены'));
//             $this->_redirector->gotoSimple('index', 'list', 'resource');
//         }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(_('Были добавлены: %d ресурс(ов)'), $importManager->getInsertsCount()));
//         $this->_flashMessenger->addMessage(sprintf(_('Были добавлены %d ресурс(ов), обновлены %d ресурс(ов), удалены %d ресурс(ов)'), $importManager->getInsertsCount(), $importManager->getInsertsPeopleCount(), $importManager->getUpdatesCount(), $importManager->getUpdatesPeopleCount(), $importManager->getDeletesCount()));
        $this->_redirector->gotoSimple('index', 'list', 'resource');

    }
}