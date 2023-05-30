<?php
class HM_Acl_QualificationWork
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'qualification-work', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'qualification-work', 'index', 'render-notification');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'qualification-work', 'index', 'save-agreement');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'qualification-work', 'index', 'send-correct-data');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		
    }
}