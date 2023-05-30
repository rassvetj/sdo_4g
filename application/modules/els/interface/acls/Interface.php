<?php
class HM_Acl_Interface
{

    public function __construct(Zend_Acl $acl)
    {

        // Редактирование интерфейсов
        $resource = sprintf('mca:%s:%s:%s', 'interface', 'edit', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

//        // Сохранение интерфейсов
//        $resource = sprintf('mca:%s:%s:%s', 'interface', 'edit', 'update');
//        $acl->addResource(new Zend_Acl_Resource($resource));
//        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);

    }

}