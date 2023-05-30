<?php
/**
 * Description of EventService
 *
 * @author slava
 */
class HM_Acl_EventService extends HM_Acl {
    
    public function __construct(Zend_Acl $acl) {
        
        $resource = 'mca:default:index:notifies';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
		
		$resource = 'mca:default:list:index';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
		$resource = 'mca:default:list:current';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
		$resource = 'mca:default:list:deleted';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
		$resource = 'mca:default:list:to-trash';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
		$resource = 'mca:default:list:restore';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
		$resource = 'mca:default:list:set-views';
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
		
        
    }
    
}

?>