<?php
class HM_Crontask_Task_OrgstructureSync extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface {

	const SYNC_FILE_PATH = '/../data/temp/orgstructure.csv';
	
    protected $_importService = null;

    public function getTaskId() {
        return 'orgstructureSync';
    }

    public function run() {
        $config = Zend_Registry::get('config');
        $importManager = new HM_Orgstructure_Import_Manager();
        $this->_importService = $this->getServiceLayer('OrgstructureCsv');
        $this->_importService->setFileName(APPLICATION_PATH . self::SYNC_FILE_PATH);
        $importManager->init($this->_importService->fetchAll());
        $importManager->import();
    }
    
    /*
    *
    * @return HM_Service_Abstract
    */
    protected function  getServiceLayer($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}
