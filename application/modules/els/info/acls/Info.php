<?php
class HM_Acl_Info extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'info', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'info', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'info', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'info', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'info', 'list', 'visrevers');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
   }
}