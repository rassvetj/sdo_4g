<?php
class HM_Acl_Military extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'military', 'file', 'get');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
      
   }
}