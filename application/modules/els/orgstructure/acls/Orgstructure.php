<?php
class HM_Acl_Orgstructure
{

    public function __construct(Zend_Acl $acl)
    {

        // $acl->addRole(new Zend_Acl_Role('test'));
        //ROLE_DEAN
        //ROLE_MANAGER

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'classifier');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'move');
        $acl->addResource(new Zend_Acl_Resource($resource));
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'get-tree-branch');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'list', 'card');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'import', 'process');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'orgstructure', 'result', 'poll');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
    }
}