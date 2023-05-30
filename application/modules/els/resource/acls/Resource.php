<?php
class HM_Acl_Resource extends HM_Acl
{
    public function __construct(Zend_Acl $acl)
    {

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'catalog', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource); // всем
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'search', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(null, $resource);
        $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        } else {
            $acl->allow(null, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'index', 'card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        } else {
            $acl->allow(null, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'index', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'index', 'edit-content');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        } else {
            $acl->allow(null, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
        }


        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(null, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_GUEST, $resource);
        }


        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }
        
        
        $resource = sprintf('privileges:%s', 'gridswitcher');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
        }
        
        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        }
        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
        }
        

        $resource = sprintf('mca:%s:%s:%s', 'resource', 'list', 'assign-to-course');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

    }

}