<?php
class HM_Acl_StudyGroups
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'study-groups', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'study-groups', 'users', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $resource = sprintf('mca:%s:%s:%s', 'study-groups', 'users', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
//
//        $resource = sprintf('mca:%s:%s:%s', 'room', 'list', 'edit');
//        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
//
//        $resource = sprintf('mca:%s:%s:%s', 'room', 'list', 'delete');
//        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
//
//        $resource = sprintf('mca:%s:%s:%s', 'room', 'list', 'delete-by');
//        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

    }
}