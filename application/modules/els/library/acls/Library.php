<?php
class HM_Acl_Library
{
    public function __construct(Zend_Acl $acl)
    {
		$resource = sprintf('mca:%s:%s:%s', 'library', 'urait', 'create-auth-link');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'library', 'biblioclub', 'create-auth-link');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
    }
}


