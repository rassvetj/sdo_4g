<?php
class HM_Acl_StudentCertificate
{

    public function __construct(Zend_Acl $acl)
    {

        // Просмотр списка
		
        $resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'index', 'send');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'manager', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'manager', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'setwidget', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'userchange', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);	
		
		
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'statement', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'statement', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'statement', 'get-grid');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'certificate', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'certificate', 'get-form');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'certificate', 'create');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'certificate', 'generate-order');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'certificate', 'get-order');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'file', 'get-statement');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		
		
		
		
		/*
        $resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'list', 'reject');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'student-certificate', 'list', 'reject-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		*/
    }
}