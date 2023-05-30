<?php
class HM_Acl_Bookshelf
{

    public function __construct(Zend_Acl $acl)
    {	
        $resource = sprintf('mca:%s:%s:%s', 'bookshelf', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'bookshelf', 'manager', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'bookshelf', 'manager', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'bookshelf', 'manager', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
    }
}