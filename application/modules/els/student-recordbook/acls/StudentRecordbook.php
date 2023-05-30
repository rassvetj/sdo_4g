<?php
class HM_Acl_StudentRecordbook
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'student-recordbook', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);


		$resource = sprintf('mca:%s:%s:%s', 'student-recordbook', 'export', 'pdf');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
    }
}