<?php
class HM_Acl_User
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'block');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'unblock');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'generate');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'list', 'login-as');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'edit', 'card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        // Доступность панели управления пользователя
        $resource = HM_Acl::RESOURCE_USER_CONTROL_PANEL;
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource, HM_Acl::PRIVILEGE_EDIT);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource, HM_Acl::PRIVILEGE_VIEW);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource, HM_Acl::PRIVILEGE_VIEW);


        //Доступность назначения ролей

        $resource = sprintf('mca:%s:%s:%s', 'user', 'dean', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'teacher', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'student', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'user', 'import', 'process');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'email-ext', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'email-ext', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'export', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'export', 'get-csv');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'export-new', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'export-new', 'get-csv');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'ajax', 'students-list');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'user', 'my-student', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		

    }
}