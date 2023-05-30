<?php
class HM_Acl_Journal extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'journal', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'index', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'result', 'extended');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		#
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'laboratory', 'extended');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'laboratory', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		#
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'lecture', 'extended');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'lecture', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		#
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'practice', 'extended');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'practice', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'storage', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'journal', 'storage', 'delete-day');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);                
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		

    }
}
