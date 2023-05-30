<?php
class Orgstructure_ImportController extends HM_Controller_Action_Import
{
    public $_importManagerClass = 'HM_Orgstructure_Import_Manager';

    public function csv()
    {
        $this->getService('Unmanaged')->setHeader(_('Импортировать оргструктуру из CSV'));
        $this->_importService = $this->getService('OrgstructureCsv');
    }

    public function processAction()
    {
        //try {
 

		
		$importManager = new HM_Orgstructure_Import_Manager();
        if ($importManager->restoreFromCache()) {
            $importManager->init(array());
        } else {
            $importManager->init($this->importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Изменения структуры организации не найдены'));
            $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
        }

        $importManager->import();

        $this->_flashMessenger->addMessage(sprintf(
            _('Были добавлены %d элемента(ов), обновлены %d элемента(ов), удалены %d элемента(ов), обновлены %d штатных едениц'),
            $importManager->getInsertsCount(),
            $importManager->getUpdatesCount(),
            $importManager->getDeletesCount(),
            $importManager->getPositionsCount()
        ));
        $this->_redirector->gotoSimple('index', 'list', 'orgstructure');
		
		
		
		//} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		//}
		
    }

}