<?php
class HM_Acl_Survey
{

    public function __construct(Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'survey', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

		$resource = sprintf('mca:%s:%s:%s', 'survey', 'index', 'start');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

		$resource = sprintf('mca:%s:%s:%s', 'survey', 'ajax', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);		
		
		$resource = sprintf('mca:%s:%s:%s', 'survey', 'save', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);	
    }
}