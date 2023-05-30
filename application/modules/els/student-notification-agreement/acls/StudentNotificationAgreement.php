<?php
class HM_Acl_StudentNotificationAgreement
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'student-notification-agreement', 'index', 'confirm');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		//$acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
    }
}