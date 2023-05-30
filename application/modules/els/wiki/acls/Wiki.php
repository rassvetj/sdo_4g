<?php
class HM_Acl_Wiki extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'wiki', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // разрешить всем, кто прошел проверку isActivityUser()? т.е. точно имеет отношение к данному курсу (dean|teacher|student|graduated)
		
		$resource = sprintf('mca:%s:%s:%s', 'wiki', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource); 
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER,$resource);

        $resource = sprintf('mca:%s:%s:%s', 'wiki', 'index', 'comment');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR,$resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER,$resource);
        // не просто разрешить всем студентам, а убедиться что он именно студент на этом курсе
        $this->allowForSubject($acl, HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
    }
}