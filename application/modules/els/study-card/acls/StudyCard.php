<?php
class HM_Acl_StudyCard
{
    public function __construct(Zend_Acl $acl)
    {
		$resource = sprintf('mca:%s:%s:%s', 'study-card', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);		
    }
}