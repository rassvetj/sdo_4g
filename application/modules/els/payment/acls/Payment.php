<?php
class HM_Acl_Payment
{

    public function __construct(Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'payment', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'payment', 'ask', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'payment', 'ask', 'question');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
    }
}







