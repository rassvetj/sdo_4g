<?php
class HM_Acl_Bookinna
{
    public function __construct (Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'bookinna', 'promo', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
    }
}