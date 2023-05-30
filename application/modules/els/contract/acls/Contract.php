<?php
class HM_Acl_Contract
{
    public function __construct (Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'contract', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'contract', 'index', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'contract', 'index', 'print');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        }
}