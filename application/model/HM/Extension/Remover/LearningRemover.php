<?php
class HM_Extension_Remover_LearningRemover extends HM_Extension_Remover_Abstract implements HM_Service_Extension_Remover_Interface
{
    // эти параметры кэшируются в data/cache
    protected $_itemsToHide = array(
        'menus' => array(
            'm06', // обучение
            'm11', // заявки
            'm0202', // менеджеры по обуч.
        ),
        'contextMenus' => array(
            'cm:user:page4', // история обучения
            'cm:user:page5', // назначения
        ),
        'roles' => array(
            HM_Role_RoleModelAbstract::ROLE_DEAN,
            HM_Role_RoleModelAbstract::ROLE_TEACHER,
        ),
        'classifierTypes' => array(
            HM_Classifier_Link_LinkModel::TYPE_SUBJECT
        ),
        'columns' => array(
            'courses', 
        ),
        'elements' => array(
            'regDeny', 
            'contractOfferText', 
        ),
        'domains' => array(
            'StudyGeneral', 
            'StudyDetailed', 
            'StudyTests', 
        ),
        'infoblocks' => array(
            'courses', 
            'claimsBlock', 
            'topsubjectsBlock', 
            'progressBlock', 
            'feedback', 
        ),
        'massActions' => array(
            array('module' => 'orgstructure', 'controller' => 'list', 'action' => 'assign-programm'), 
            array('module' => 'assign', 'controller' => 'student', 'action' => 'do-soids'), 
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
                HM_Extension_ExtensionService::EVENT_FILTER_CONTEXT_MENU,
                array($this, 'callFilterContextMenu')
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
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_CONTRACT,
            array($this, 'callFilterForm')
        );
        
        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_REPORT_DOMAINS,
            array($this, 'callFilterReportDomains')
        );
        
        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_INFOBLOCKS,
            array($this, 'callFilterInfoblocks')
        );
        
        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_GRID_MASSACTIONS,
            array($this, 'callFilterMassActions')
        );
    }
    
}