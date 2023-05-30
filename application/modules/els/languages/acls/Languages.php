<?php
class HM_Acl_Languages extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'languages', 'survey', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));   
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'languages', 'survey', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));   
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'languages', 'survey', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));   
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'languages', 'assign', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));   
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'languages', 'assign', 'save');
        $acl->addResource(new Zend_Acl_Resource($resource));   
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

    }
}
