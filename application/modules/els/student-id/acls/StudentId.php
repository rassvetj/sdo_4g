<?php
class HM_Acl_StudentId
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'student-id', 'export', 'pdf');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-id', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
    }
}