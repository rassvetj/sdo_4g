<?php
class HM_Acl_News extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        //$acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST,$resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'news', 'index', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        //$acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST,$resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'news', 'report', 'check');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);        
    }
}