<?php
class HM_Acl_Marksheet
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'set-score');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'set-comment');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'graduate-students');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'clear-schedule');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'word');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'excel');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'marksheet', 'index', 'print');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'get', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'get', 'manager');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'graduate', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);        
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'graduate', 'individual-vedomost');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);  
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'graduate', 'vedomost');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);  
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'set', 'blocked-task');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);  
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'manager', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'manager', 'to-active');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'manager', 'to-active-mass');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'list', 'assign-unassigned');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'external', 'list');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);  
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'external', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);  
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'external', 'set-mark');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'external', 'graduate');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'marksheet', 'external', 'confirm-mark');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		
    }
}