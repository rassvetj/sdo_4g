<?php
class HM_Acl_Template
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'template', 'order', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'template', 'certificate', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'template', 'report', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
    }
}