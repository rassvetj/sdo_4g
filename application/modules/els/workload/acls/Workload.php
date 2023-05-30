<?php
class HM_Acl_Workload
{

    public function __construct(Zend_Acl $acl)
    {        
        $resource = sprintf('mca:%s:%s:%s', 'workload', 'index', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'index', 'set-ball');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_ADMIN, $resource);
				
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'sheet', 'index');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
				
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'sheet', 'close');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'modify');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'end-modify');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);				
		
		// - отчеты
		//-- Отчет 1. О приветственном сообщении.
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'welcome');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-welcome-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		//-- Отчет 2 "О просрочках проверки заданий"
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'subject-assessment');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-subject-assessment-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		//-- Отчет 3 "О просрочках ответа на форуме"
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'forum');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-forum-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		//-- Отчет 4 "Промежуточный". Расчет нагрузки
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'current-workload');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-current-workload-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		//-- Отчет 5 "Окончательный". Расчет нагрузки
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'end-workload');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		//$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-end-workload-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		//$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);	

		//-- Отчет 6 "Качество работы".
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'quality-work');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-quality-work-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		//-- Отчет 5 "Окончательный". Расчет нагрузки c фиксацией в БД
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'end-workload-fix');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		//$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-end-workload-fix-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);		
		//$acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);

		//-- Отчет  "расчет нагрузки". Для орг. обучения
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'workload-manager');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);				
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-workload-manager');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		//-- Отчет  "расчет нагрузки" окончательный. Для орг. обучения
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'workload-manager-end');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);				
		
		//-- Отчет  "расчет нагрузки". Для тьютора
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'workload-tutor');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-workload-tutor');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'violations-tutor');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-violations-tutor-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'violations-dean');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-violations-dean-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'violations-supervisor');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);	
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-violations-supervisor-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);

			
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'workload');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-workload-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'violations');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'get-violations-report');
        $acl->addResource(new Zend_Acl_Resource($resource));
        $acl->allow(HM_Role_RoleModelAbstract::ROLE_TUTOR, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_SUPERVISOR, $resource);
		
		
		$resource = sprintf('mca:%s:%s:%s', 'workload', 'report', 'workload-end');
        $acl->addResource(new Zend_Acl_Resource($resource));        
		$acl->allow(HM_Role_RoleModelAbstract::ROLE_DEAN, $resource);
		
		
    }
}