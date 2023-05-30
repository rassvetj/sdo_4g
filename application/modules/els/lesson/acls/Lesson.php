<?php
class HM_Acl_Lesson
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
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        //$acl->allow(HM_Role_RoleModelAbstract::ROLE_METODIST, $resource);

        // Просмотр списка занятий
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'my');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);

        // Просмотр прогресса изучения курса с накопительной системой баллов
        // мб стоит проверять что он все таки студент на этом курсе...
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'my-progress');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        //if ($this->isSubjectContext()) {
            $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
        //}



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
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'students-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'ajax', 'groups-list');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);


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
        
        // Удаление занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'delete-by');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);

        // Генерация занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'generate');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

        // подробные рез-ты
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

        // удаление попыток
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'delete-attempt');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

        // запуск занятия
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'execute', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource); // ENDUSER'у нельзя запускать занятия на оценку, но эта проверка на уровне контроллера
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);

        // просмотр результата
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'test-mini');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);

        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'edit-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);


        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'list', 'order-section');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

        //расширенный интерфейс для занятий с типом "Задание"
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'extended');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_STUDENT, $resource);
		
		//список всех занятий с типом "Задание"
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'result', 'all');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		
        $resource = sprintf('mca:%s:%s:%s', 'lesson', 'file', 'get');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TEACHER, $resource);
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		
		$resource = sprintf('mca:%s:%s:%s', 'lesson', 'past', 'index');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }      
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'lesson', 'past', 'task');
        if (!$acl->has($resource)) {
            $acl->addResource(new Zend_Acl_Resource($resource));
        }      
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ENDUSER, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'lesson', 'test', 'result');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'lesson', 'test', 'change-mark');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
        
    }
}
