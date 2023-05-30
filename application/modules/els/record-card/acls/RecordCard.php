<?php
class HM_Acl_RecordCard
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'record-card', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'record-card', 'index', 'print');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
    }
}