<?php
class HM_Acl_Event extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        // Просмотр списка
        $resource = sprintf('mca:%s:%s:%s', 'event', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'event', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'event', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'event', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'event', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

    }

}