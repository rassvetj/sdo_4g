<?php
class HM_Acl_Feedback
{

    public function __construct(Zend_Acl $acl)
    {
        // Просмотр списка занятий
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Создание занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'new');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'modules-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'students-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Редактирование занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Удаление занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'delete');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        
    }
}