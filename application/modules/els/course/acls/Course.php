<?php
class HM_Acl_Course extends HM_Acl
{

    public function __construct(Zend_Acl $acl)
    {

        // Импорт эл.курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Импорт эл.курса внутри учебного (не в базу знаний)
        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'subject');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Недокументированные функции - всем запретить от греха подальше
        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'multiple');
        $acl->addResource(new Zend_Acl_Resource($resource));

        // Просмотр эл.курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        } else {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        }
        // Просмотр модуля эл.курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'item', 'view');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);


          // Просмотр активных курсов
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'active');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Просмотр активных курсов
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

          // Просмотр разрабатываемых курсов
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'developed');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

          // Просмотр архивных курсов
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'archived');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Создание курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'new');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        // Редактирование курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Редактирование конфига Skillsoft курсов
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'editconfig');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Удаление курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Удаление курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Публикация курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'public');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        // Отправка в архив
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'archive');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Отправка в разработку
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'develop');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);

        // Редактор учебного модуля
        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        // Создать раздел учебного модуля
        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'new-section');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        // Редактирование раздела учебного модуля
        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'section');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        // Удалить раздел учебного модуля
        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        // Удалить раздел учебного модуля
        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'delete-force');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'delete-force-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'course', 'structure', 'move');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, $resource);

    }
}