<?php
class HM_Acl_Learningsubjects
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'learningsubjects', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'learningsubjects', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'learningsubjects', 'import', 'process');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'learningsubjects', 'list', 'assign-subject');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'learningsubjects', 'list', 'un-assign-subject');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
    }
}