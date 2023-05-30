<?php
class HM_Acl_Cache
{

    public function __construct(Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'cache', 'manager', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'cache', 'manager', 'clear');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        
    }
}







