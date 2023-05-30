<?php
class HM_Acl_Notice
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'notice', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'notice', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		
		//--настройка системных уведомлений внутри лк по умолчанию для тьюторов
		$resource = sprintf('mca:%s:%s:%s', 'notice', 'notifie', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'notice', 'notifie', 'set-tutor-default');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
    }
}