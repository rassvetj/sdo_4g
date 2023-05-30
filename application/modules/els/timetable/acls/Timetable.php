<?php
class HM_Acl_Timetable
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'timetable', 'students', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);	
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);	
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'timetable', 'teachers', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);	
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);	
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);		
		
		$resource = sprintf('mca:%s:%s:%s', 'timetable', 'debtors', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);	
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);	
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

		$resource = sprintf('mca:%s:%s:%s', 'timetable', 'student', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'timetable', 'teacher', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'timetable', 'teacher', 'save-link');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
    }
}