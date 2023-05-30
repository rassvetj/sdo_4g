<?php
class HM_Acl_Materials extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'materials', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'materials', 'delete-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'materials', 'edit-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'materials', 'order-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

    }
}