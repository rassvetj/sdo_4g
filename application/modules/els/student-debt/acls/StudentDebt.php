<?php
class HM_Acl_StudentDebt
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'student-debt', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-debt', 'timetable', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
				
		$resource = sprintf('mca:%s:%s:%s', 'student-debt', 'timetable', 'manager');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-debt', 'timetable', 'export');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-debt', 'timetable', 'get-example');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-debt', 'timetable', 'delete-one');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-debt', 'timetable', 'delete-mass');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
    }
}