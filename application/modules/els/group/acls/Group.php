<?php
class HM_Acl_Group
{

    public function __construct(Zend_Acl $acl)
    {

        // $acl->addRole(new Zend_Acl_Role('test'));
        //ROLE_ADMIN
        //ROLE_MANAGER

        // Просмотр списка подгрупп
        $resource = sprintf('mca:%s:%s:%s', 'group', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

        // Создание подгруппы
        $resource = sprintf('mca:%s:%s:%s', 'group', 'index', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // редактирование подгруппы
        $resource = sprintf('mca:%s:%s:%s', 'group', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // редактирование пиплов подгруппы
        $resource = sprintf('mca:%s:%s:%s', 'group', 'index', 'edit-members');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
    }
}