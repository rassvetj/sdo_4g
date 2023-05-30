<?php
class HM_Extension_Remover_KnowledgeBaseRemover extends HM_Extension_Remover_Abstract implements HM_Service_Extension_Remover_Interface
{
    protected $_itemsToHide = array(
        'menus' => array(
            'm10'
        ),
        'roles' => array(
            HM_Role_RoleModelAbstract::ROLE_DEVELOPER,
            HM_Role_RoleModelAbstract::ROLE_MANAGER,
        ),
        'classifierTypes' => array(
            HM_Classifier_Link_LinkModel::TYPE_RESOURCE
        ),
        'columns' => array(
            'location', 
            'chain'
        ),
        'domains' => array(
            'Materials', 
        ),
        'infoblocks' => array(
            'resourcesBlock', 
        ),
    );
    
        
    public function init()
    {
    }
    
    public function registerEventsCallbacks()
    {
        $this->getService('EventDispatcher')->connect(
                HM_Extension_ExtensionService::EVENT_AFTER_INIT_EXTENSIONS,
                array($this, 'callAfterInitExtensions')
        );
        
        $this->getService('EventDispatcher')->connect(
                HM_Extension_ExtensionService::EVENT_FILTER_BASIC_ROLES,
                array($this, 'callFilterBasicRoles')
        );
        
        $this->getService('EventDispatcher')->connect(
                HM_Extension_ExtensionService::EVENT_FILTER_GRID_COLUMNS,
                array($this, 'callFilterGridColumns')
        );
        
        $this->getService('EventDispatcher')->connect(
                HM_Extension_ExtensionService::EVENT_FILTER_CLASSIFIER_LINK_TYPES,
                array($this, 'callFilterClassifierLinkTypes')
        );
        
        $this->getService('EventDispatcher')->connect(
                HM_Extension_ExtensionService::EVENT_FILTER_GRID_SWITCHER,
                array($this, 'callFilterGridSwitcher')
        );
        
        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_REPORT_DOMAINS,
            array($this, 'callFilterReportDomains')
        );        
    }
    
    public function callAfterInitExtensions($event)
    {
        $this->getService('Unmanaged')->removeSearch();
        parent::callAfterInitExtensions($event);
    }    
    
    public function callFilterGridSwitcher($event, $options)
    {
        unset($options['global']);
        return $options;
    }
    
}