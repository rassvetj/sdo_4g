<?php
class HM_Acl_Task extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {

        // $acl->addRole(new Zend_Acl_Role('test'));
        //ROLE_ADMIN
        //ROLE_MANAGER

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        }

        // ----------------------------------------------------------------

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'list', 'publish');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'task', 'question', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'question', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'task', 'question', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

    }
}