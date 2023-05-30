<?php
class HM_Acl_Certificates
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'certificates', 'export', 'confirming-student-pdf');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'certificates', 'index', 'confirming-student');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
    }
}