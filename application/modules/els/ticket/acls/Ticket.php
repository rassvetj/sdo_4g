<?php
class HM_Acl_Ticket
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'ticket', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'send', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'pay', 'fail');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'pay', 'ready');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'order', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'order', 'get-file');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'manager', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'manager', 'get-tree');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'ticket', 'manager', 'refresh-tree');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		
		
		
		
		
		
		
    }
}