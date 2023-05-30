<?php
class HM_Acl_Volunteer
{

    public function __construct(Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'volunteer', 'cabinet', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);        
		
		$resource = sprintf('mca:%s:%s:%s', 'volunteer', 'cabinet', 'send-member-reqest');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);  
		
		$resource = sprintf('mca:%s:%s:%s', 'volunteer', 'cabinet', 'send-event-reqest');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource); 
    }	
}