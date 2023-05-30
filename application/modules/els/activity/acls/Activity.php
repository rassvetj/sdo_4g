<?php
class HM_Acl_Activity
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'activity', 'edit', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'activity', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
    }

}