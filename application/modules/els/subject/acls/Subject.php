<?php
class HM_Acl_Subject extends HM_Acl
{
    public function __construct (Zend_Acl $acl)
    {
        $this->_indexAction($acl);
        $this->_listAction($acl);
		
		// Мотивированное сообщение
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'message', 'motivation');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		#диалог
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'interview', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'interview', 'create');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'interview', 'list');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource); # доступ есть, но сразу перенаправляет на index
		
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'extended', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'by-programm', 'preview');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'by-programm', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'subject', 'ajax', 'get-subject-list');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
    }

    private function _indexAction(Zend_Acl $acl)
    {
        // Просмотре курса
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

        // Просмотр курсов
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'courses');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

        // Просмотр смена режима прохождения
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'changemode');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Cмена состояния курса
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'change-state');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Просмотр смена режима прохождения
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'edit-services');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
    }

    private function _listAction(Zend_Acl $acl)
    {
        // Просмотре курсов
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);


        // Просмотр карточки
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'view');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Редактирование курса
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'edit');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        // Создание курса
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'new');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        // Удаление курса
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'delete');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'delete-by');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);


/*
        // Импорт эл.курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'subject');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        */

        $resource = sprintf('mca:%s:%s:%s', 'course', 'import', 'subject');
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
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        }

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        }


        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'assign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'unassign');
        $acl->addResource(new Zend_Acl_Resource($resource));
        if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
            $acl->deny(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        }


        // Редактирование курса
        $resource = sprintf('mca:%s:%s:%s', 'course', 'list', 'edit');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);


        // Удаление курса
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'course-delete');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'subject', 'index', 'course-delete-by');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_MANAGER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        //Calendar
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'calendar');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        
        $resource = sprintf('mca:%s:%s:%s', 'subject', 'list', 'copy-from-base');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
//        $acl->deny();
    }
}