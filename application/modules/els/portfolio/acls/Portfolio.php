<?php
class HM_Acl_Portfolio
{

    public function __construct(Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'portfolio', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'portfolio', 'index', 'manager');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'portfolio', 'index', 'upload');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
			
		$resource = sprintf('mca:%s:%s:%s', 'portfolio', 'index', 'get-files');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'portfolio', 'index', 'delete-files');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
    }
}