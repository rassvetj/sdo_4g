<?php
class HM_Acl_Test extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {
        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'view');
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

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        }

        // ----------------------------------------------------------------

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }
        
        
        $resource = sprintf('mca:%s:%s:%s', 'test', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }
        

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'test', 'abstract', 'publish');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'test', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->deny(null, $resource);
        
        $resource = sprintf('privileges:%s', 'gridswitcher');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        }
    }
}