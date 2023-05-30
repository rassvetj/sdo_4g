<?php
class HM_Acl_Video extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'video', 'list', 'get-video');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
   }
}