<?php
class HM_Extension_ExtensionService extends HM_Service_Standalone_Abstract
{
    const EVENT_AFTER_INIT_EXTENSIONS = 'event.after.init.extensions';
    const EVENT_FILTER_BASIC_ROLES = 'event.filter.basic.roles';
    const EVENT_FILTER_GRID_COLUMNS = 'event.filter.grid.columns';
    const EVENT_FILTER_CLASSIFIER_LINK_TYPES = 'event.filter.classifier.link.types';
    const EVENT_FILTER_USER_CARD_UNIT_INFO = 'event.filter.user.card.unit.info';
    const EVENT_FILTER_CONTEXT_BLOCK = 'event.filter.context.block';
    const EVENT_FILTER_CONTEXT_MENU = 'event.filter.context.menu';
    const EVENT_FILTER_LESSON_TYPES = 'event.filter.lesson.types';
    const EVENT_FILTER_REPORT_DOMAINS = 'event.filter.report.domains';
    const EVENT_FILTER_INFOBLOCKS = 'event.filter.infoblocks';

    const EVENT_FILTER_FORM_USER = 'event.filter.form_user';
    const EVENT_FILTER_FORM_CONTRACT = 'event.filter.form_contract';

    const EVENT_FILTER_GRID_MASSACTIONS = 'event.filter.grid.massactions';
    const EVENT_FILTER_GRID_SWITCHER = 'event.filter.gridswitcher';
    
    const MODULES_XML =  'modules.xml';

    private $_removers = array();
    private $_removersMustBeRestoredFromCache = true;
    private $_removersRestoredFromCache = false;

    public function init()
    {
        $this->_getRemovers();
        if ($this->_removersRestoredFromCache && count($this->_removers)) {
            foreach($this->_removers as $remover) {
                $remover->init();
                $remover->setServiceContainer($this->getServiceContainer());
                $remover->registerEventsCallbacks();
            }
        }
    }

    private function _getRemovers()
    {
        $modulesXml = APPLICATION_PATH . '/settings/modules.xml';
        if (file_exists($modulesXml) && is_readable($modulesXml)) {
            $modulesCache = 'modules_xml_'.filemtime($modulesXml);

            $removers = Zend_Registry::get('cache')->load($modulesCache);

            if ($this->_removersMustBeRestoredFromCache && ($removers !== false)) {
                $this->_removers = $removers;
                $this->_removersRestoredFromCache = true;
            } else {
                $this->_removers = array();
                if ($xml = simplexml_load_file($modulesXml)) {
                    foreach($xml->module as $module) {

                        $moduleDisabled = strtolower((string) $module['enable']) === 'false';

                        if ($moduleDisabled && isset($module['remover'])) {
                            $removerClass = (string) $module['remover'];
                            $remover = new $removerClass();
                            $remover->setServiceContainer($this->getServiceContainer());
                            $remover->init();
                            $remover->registerEventsCallbacks();

                            $this->addRemover($remover);
                        }

                    }

                    Zend_Registry::get('cache')->save($this->_removers, $modulesCache);
                }
            }
        }
    }

    public function addRemover(HM_Service_Extension_Remover_Interface $remover)
    {
        $this->_removers[get_class($remover)] = $remover;
    }

}