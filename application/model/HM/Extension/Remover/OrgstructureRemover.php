<?php
class HM_Extension_Remover_OrgstructureRemover extends HM_Extension_Remover_Abstract implements HM_Service_Extension_Remover_Interface
{
    protected $_itemsToHide = array(
        'menus' => array(
            'm08'
        ),
        'roles' => array(
            HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, // сейчас область ответственности супервайзера строится по оргструктуре
        ),
        'classifierTypes' => array(
            HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
        ),
        'columns' => array(
            'departments', 
            'positions', 
            'position', 
            'orgStruct',
        ),
        'elements' => array(
            'position_id', 
            'position_name', 
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
            HM_Extension_ExtensionService::EVENT_FILTER_USER_CARD_UNIT_INFO,
            array($this, 'callFilterUserCardUnitInfo')
        );

        $this->getService('EventDispatcher')->connect(
            HM_Extension_ExtensionService::EVENT_FILTER_FORM_USER,
            array($this, 'callFilterForm')
        );
        
        // @todo: фильтровать содержимое report.yml
        
    }

    public function callFilterUserCardUnitInfo($event, $unitInfo)
    {
        return null;
    }

}